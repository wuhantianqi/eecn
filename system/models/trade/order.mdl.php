<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: order.mdl.php 6303 2014-09-17 11:01:41Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Trade_Order extends Mdl_Table
{   
  
    protected $_table = 'order';
    protected $_pk = 'order_id';
    protected $_cols = 'order_id,order_no,uid,shop_id,city_id,product_count,product_number,product_amount,freight,amount,contact,mobile,addr,note,pay_status,order_status,pay_time,audit,closed,clientip,dateline';
    protected $_orderby = array('order_id'=>'DESC');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function create_by_cart($cart, $data)
    {
        $orders = array();
        if(empty($cart['items'])){
            $this->err->add('购物车不能为空', 211);
            return false;
        }
        foreach($cart['items'] as $item){
            $orders[$item['shop_id']][$item['cart_key']] = $item;
        }
        foreach($orders as $shop_id=>$items){
            $product_amount = $shop_fee = $product_count = $product_number = 0;
            $products = $sale_counts = $product_names = array();
			$shop = K::M('shop/shop')->detail($shop_id);
            foreach($items as $item){
                if($item['spec_id']){
                    if($item['spec']['sale_sku'] < $item['num']){
                        $this->err->add('商品库存足', 321);
                        //$this->err->set_data('')
                        return false;
                    }
                }else if($item['spec_id'] == 1 && $item['sale_sku'] < $item['num']){
                    $this->err->add('商品库存足', 321);
                    return false;
                }
                $product = array();
                $product_names[$item['product_id']] = $item['name'];
                $product_count += $item['num'];
                $product_number ++;
                $product_amount += $item['product_price'] * $item['num'];
                $freight += $item['freight'];
                $product['product_id'] = $item['product_id'];
                $product['spec_id'] = $item['spec_id'];                
                if($item['spec_id']){
                    $product['spec_name'] = $item['spec_name'];
                }
                $product['product_name'] = $item['name'];
                $product['product_price'] = $item['product_price'];
                $product['number'] = $item['num'];
                $prodcut['freight'] =$item['freight'];
                $product['amount'] = $product['product_price'] * $item['num'] + $product['freight'];
                $products[$item['cart_key']] = $product;
                $sale_counts[$item['cart_key']] = $item['num'];
            }
            $order = array();
            $order['uid'] = $data['uid'];
            $order['shop_id'] = $shop_id;
            $order['contact'] = $data['contact'];
            $order['mobile'] = $data['mobile'];
            $order['addr'] = $data['addr'];
            $order['note'] = $data['note'];
            $order['product_count'] = $product_count;
            $order['product_number'] = $product_number;
            $order['product_amount'] = $product_amount;
            $order['freight'] = $freight;
            $order['amount'] = $product_amount + $freight;
            $order['order_no'] = $this->create_order_no();
			$order['city_id'] = $shop['city_id'];
            if(!$order_id = $this->create($order)){
                return false;
            }
            foreach($products as $product){
                $product['order_id'] = $order_id;
                K::M('trade/product')->create($product, true);
                /*
                K::M('product/product')->update_count($product['product_id'], 'sale_count', $product['number']);
                if($product['spec_id']){
				    K::M('product/spec')->update_count($product['spec_id'], 'sale_count', $product['number']);
                }*/
            }
			foreach($sale_counts as $sk=>$count){
                list($pid, $spid) = explode('-', $sk);
				K::M('product/product')->update_count($pid, 'sale_count', $count);
                if($spid = (int)$spid){
                    K::M('product/spec')->update_count($spid, 'sale_count', $count);
                }
			}
            K::M('shop/shop')->update_count($shop_id, 'sales', $product_count);
            $maildata = array('order_no'=>$order['order_no'], 'order_amount'=>$order['amount'], 'contact'=>$order['contact']);
            $maildata['product_name'] = implode(',', $product_names);
            $maildata['link'] = K::M('helper/link')->mklink('trade/order:detail', array($order['order_no']), array(), true);
            //send mail to buyer
            K::M('helper/mail')->send($member['mail'], 'order_buyer', $maildata);
            //send mail to seller
            if($shop = K::M('shop/shop')->detail($shop_id)){
                K::M('helper/mail')->send('order_seller', $maildata, $shop);
            }
        }
        //print_r($this->db->SQLLOG());
        //echo 'FILE:',__FILE__,'LINE:',__LINE__;exit();
        return $order;
    }

    public function create_order_no()
    {
        $i = rand(0, 9999);
        do {
            if (9999 == $i) {
                $i = 0;
            } 
            ++$i;
            $no = date("ymd") . str_pad($i, 4, "0", STR_PAD_LEFT);
            $order_no = $this->db->GetRow("SELECT order_no FROM ".$this->table($this->_table)." WHERE order_no='{$no}'");
        } while ($order_no);
        return $no;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function detail($order_id, $closed=false)
    {
        if($row = parent::detail($order_id, $closed)){
            $row['products'] = K::M('trade/product')->order_products($row['order_id']);
        }
        return $row;
    }

    public function detail_by_no($no)
    {
        if($no = (int)$no){
            $where = "order_no=$no";
        }else{
            return false;
        }
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE order_no='$no' AND closed=0";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
            $row['products'] = K::M('trade/product')->order_products($row['order_id']);
        }
        return $row;
    }

    public function set_payed($no, $trade=array())
    {
        if(!$order = $this->detail_by_no($no)){
            return false;
        }
        $a = array('pay_status'=>1, 'pay_time'=>__CFG::TIME, 'audit'=>1);
        if($ret = $this->update($order['order_id'], $a, true)){
            if($shop_id = (int)$order['shop_id']){
                K::M('shop/shop')->update_count($shop_id, 'credit', $order['product_count']);
				$shop = K::M('shop/shop')->detail($shop_id);
                $smsdata = $maildata = array('order_no'=>$order['order_no'], 'order_amount'=>$order['amount'], 'contact'=>$order['contact'], 'shop_name'=>$shop['name'], 'shop_phone'=>$shop['phone']);
                $maildata['link'] = K::M('helper/link')->mklink('trade/order:detail', array($order['order_no']), array(), true);
                if($mobile = K::M('verify/check')->mobile($order['mobile'])){
                    K::M('sms/sms')->send($mobile, 'order_payment_buyer', $smsdata);
                }
                if($shop = K::M('shop/shop')->detail($shop_id)){
                    $maildata['shop_name'] = $shop['name'];
                    $maildata['shop_phone'] = $shop['phone'];
                    K::M('sms/sms')->shop($shop, 'order_payment_seller', $smsdata);
                    K::M('helper/mail')->sendshop($shop, 'order_payment_seller', $maildata);
                }
                if($member = K::M('member/member')->member($order['uid'])){
                    K::M('helper/mail')->send($member['mail'], 'order_payment_buyer', $maildata);
                }
            }
        }
        return $ret;
    }

    public function count_by_shop($shop_id)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $sql = "SELECT order_status, COUNT(1) as C FROM ".$this->table($this->_table)." WHERE shop_id='$shop_id' AND closed=0 GROUP BY order_status";
        $order_count = array('count'=>0, 'cancel'=>0,'member_cancel'=>0,'shop_cancel'=>0,'unship'=>0,'new'=>0, 'unpay'=>0,'finish'=>0);
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                if($row['order_status'] == -1){
                    $order_count['member_cancel'] = $row['C'];
                    $order_count['cancel'] += $row['C'];
                }else if($row['order_status'] == -2){
                    $order_count['shop_cancel'] = $row['C'];
                    $order_count['cancel'] += $row['C'];
                }else if($row['order_status'] == 1){
                    $order_count['unship'] = $row['C'];
                }else if($row['order_status'] == 2){
                    $order_count['finish'] = $row['C'];
                }else if($row['order_status'] == 0){
                    $order_count['new'] = $row['C'];
                }
                $order_count['count'] += $row['C'];
            }
        }
        $sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." WHERE shop_id='$shop_id' AND closed=0 AND pay_status=0";
        $order_count['unpay'] = $this->db->GetOne($sql);        
        return $order_count;
    }

    public function count_by_uid($uid)
    {
        if(!$uid = (int)$uid){
            return false;
        }
        $sql = "SELECT order_status, COUNT(1) as C FROM ".$this->table($this->_table)." WHERE uid='$uid' AND closed=0 GROUP BY order_status";
        $order_count = array('count'=>0, 'cancel'=>0,'member_cancel'=>0,'shop_cancel'=>0,'unship'=>0,'new'=>0, 'unpay'=>0,'finish'=>0);
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                if($row['order_status'] == -1){
                    $order_count['member_cancel'] = $row['C'];
                    $order_count['cancel'] += $row['C'];
                }else if($row['order_status'] == -2){
                    $order_count['shop_cancel'] = $row['C'];
                    $order_count['cancel'] += $row['C'];
                }else if($row['order_status'] == 1){
                    $order_count['unship'] = $row['C'];
                }else if($row['order_status'] == 2){
                    $order_count['finish'] = $row['C'];
                }else if($row['order_status'] == 0){
                    $order_count['new'] = $row['C'];
                }
                $order_count['count'] += $row['C'];
            }
        }
        $sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." WHERE uid='$uid' AND closed=0 AND pay_status=0";
        $order_count['unpay'] = $this->db->GetOne($sql);               
        return $order_count;
    }

}