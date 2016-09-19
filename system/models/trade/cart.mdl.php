<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cart.mdl.php 5608 2014-06-23 11:36:07Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Trade_Cart extends Model 
{
    
    public $cart = array();

    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->uid = $system->uid;
        $this->MEMBER = $system->MEMBER;
        if($this->uid){
            $cart = $system->MEMBER['cart'];
        }else{
            $cart = $system->cookie->get('CART');
        }
        $this->cart = $this->unpack($cart);
        if($this->uid){
            $this->merge_cart();
        }
        register_shutdown_function(array(&$this, 'save_cart'));
    }

    //添加到购车
    public function add($pid, $spid=0, $quantity=1)
    {
        $key = $pid.'-'.$spid;
        if(!$pid = (int)$pid){
            $this->err->add('非法的数据提交', 311);
            return false;
        }
        $quantity = max((int)$quantity, 1);
        $cart_num = (int)$this->cart[$key];
        $quantity += $cart_num;
        $spid = (int)$spid;
		
        if($spid && $spec = K::M('product/spec')->detail($spid)){
            if($spec['product_id'] != $pid){
                $this->err->add('非法的数据提交', 312);
                return false;
            }else if($spec['sale_sku'] < $quantity){
                $this->err->add('该商品库存不足', 313);
                return false;
            }
        }else if(!$product = K::M('product/product')->detail($pid)){
            $this->err->add('非法的数据提交', 314);
            return false;
        }
        if(empty($spec) && $spec['sale_sku'] < $spec['sale_count']){
            $this->err->add('该商品库存不足', 313);
            return false;
        }
        $this->cart[$key] = $quantity;
        return $quantity;
    }

    public function update($pid, $spid=0, $quantity=1)
    {
        $key = $pid.'-'.$spid;
        if(!$pid = (int)$pid){
            $this->err->add('非法的数据提交', 311);
            return false;
        }
        $quantity = max((int)$quantity, 1);
        $spid = (int)$spid;
        if($spid && $spec = K::M('product/spec')->detail($spid)){
            if($spec['product_id'] != $pid){
                $this->err->add('非法的数据提交', 312);
                return false;
            }else if($spec['sale_sku'] < $quantity){
                $this->err->add('该商品库存不足', 313);
                return false;
            }
        }else if(!$product = K::M('product/product')->detail($pid)){
            $this->err->add('非法的数据提交', 314);
        }else if($product['sale_sku'] < $quantity){
            $this->err->add('该商品库存不足', 313);
        }
        if(isset($this->cart[$key])){
            $this->cart[$key] = $quantity;
        }
        return $quantity;
    }

    //删除
    public function delete($pid, $spid=0)
    {
        $key = $pid.'-'.$spid;
        unset($this->cart[$key], $this->cart[$pid]);
        return true;
    }

    //清空购物车
    public function clean()
    {
        $this->cart= array();
        return true;
    }

    public function merge_cart()
    {
        if($cart = $this->cookie->get('CART')){
            $cart = $this->unpack($cart);
            $pids = array();
            foreach($cart as $k=>$v){
                if($v = intval($v)){
                    $pids[$k] = $k;
                    if(isset($this->cart[$k])){
                        $this->cart[$k] += intval($v);
                    }else{
                        $this->cart[$k] = intval($v);
                    }
                }
            }
            if($this->uid){
                if($pids && K::$system->MEMBER['from'] == 'shop'){
                    if($shop = K::M('shop/shop')->shop_by_uid($this->uid)){
                        if($product_list = K::M('product/product')->items_by_ids($pids)){
                            foreach($product_list as $v){
                                if($v['shop_id'] == $shop['shop_id']){
                                    unset($this->cart[$v['product_id']]);
                                }
                            }
                        }
                    }
                }
                $this->cookie->delete('CART');
            }
            //$this->save_cart();
        }
    }

    public function pack($cart)
    {
        $ars = array();
        foreach($this->cart as $k=>$v){
            if($v = intval($v)){
                $ars[] = "{$k}:{$v}";
            }
        }
        $ars = $ars ? implode(';', $ars) : '';
        return $ars;
    }

    public function unpack($cart)
    {
        $ars = array();
        if($a = explode(';', $cart)){
            foreach($a as $v){
                $b = explode(':', $v);
                if($v = intval($v)){
                    $ars[$b[0]] = $b[1];
                }
            }
        }
        return $ars;
    }   

    public function save_cart()
    {
        $ars = $pids = array();
        $number = $count = 0;
        foreach($this->cart as $k=>$v){
            if($v = intval($v)){
                $pids[$k] = $k;
                $ars[] = "{$k}:{$v}";
                $count += $v;
            }
            $number ++;
        }
        $ars = $ars ? implode(';', $ars) : '';
        if($this->uid){
            K::M('member/member')->update($this->uid, array('cart'=>$ars), true);
        }else{
            $this->cookie->set('CART', $ars);
        }
        $this->cookie->set('CART_NUMBER', $number);
        $this->cookie->set('CART_COUNT', $count);
    }

    public function detail()
    {
        $shop_ids = $product_ids = $spec_ids = $items = array();
        $total_amount = $product_amount = $shop_fee = $product_count = $product_number = 0;
        foreach($this->cart as $key=>$num){
            list($pid, $spid) = explode('-', $key);
            $product_ids[$pid] = $pid;
            if($spid){
                $spec_ids[$spid] = $spid;
            }
        }
        if($product_ids){
            if($product_list = K::M('product/product')->items_by_ids($product_ids)){
                if($spec_ids){
                    $spec_list = K::M('product/spec')->items_by_ids($spec_ids);
                }
                $unpids = array();
                $cart = $this->cart;
                foreach($cart as $sk=>$num){
                    list($pid, $spid) = explode('-', $sk);
                    $spid = (int)$spid;
                    if(($num = (int)$num) && ($item = $product_list[$pid])){
                        $product_count += $num;
                        $product_number ++;                       
                        $ship_fee += $item['freight'];
                        $shop_ids[$item['shop_id']] = $item['shop_id'];
                        $item['cart_key'] = $sk;
                        $item['num'] = $num;
                        $item['spec_id'] = $spid;
                        $item['product_price'] = $item['price'];
                        if($spec = $spec_list[$spid]){
                            if($spec['price']){
                                $item['product_price'] = $spec['price'];
                            }
                            $item['spec_name'] = $spec['spec_name'];
                            $item['spec'] = $spec;
                        }
                        $product_amount += $item['product_price'] * $num;
                        $items[$sk] = $item;
                    }else{
                        $this->delete($pid, $spid);
                    }
                }
            }
        }
        $detail = array('items'=>$items, 'shop_ids'=>$shop_ids, 'product_ids'=>$product_ids);
        $detail['total_amount'] = $product_amount + $ship_fee;
        $detail['product_amount'] = $product_amount;
        $detail['ship_fee'] = $ship_fee;
        $detail['product_count'] = $product_count;
        $detail['product_number'] = $product_number;
        return $detail;
    }
       
}