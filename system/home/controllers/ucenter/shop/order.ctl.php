<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Shop_Order extends Ctl_Ucenter 
{
    
    public function index($type=null, $page=1)
    {
        $shop = $this->ucenter_shop();
        $pager = $filter = array();
        if(is_numeric($type)){
            $page = $type;
            $type = 'all';
        }else{
            switch($type){
                case 'payed':
                    $filter['pay_status'] = 1; $filter['order_status'] = array(0,1); break;
                case 'unpay':
                    $filter['pay_status'] = 0; $filter['order_status'] = array(0,1); break;
                case 'finish':
                    $filter['order_status'] = 2; break;
                case 'cancel':
                    $filter['order_status'] = '<:0'; break;
                case 'unship':
                    $filter['order_status'] = 0; break;
                case 'ship':
                    $filter['order_status'] = 1; break;
                default:
                    $type = 'all';
            }
        }
        $pager['type'] = $type;
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter['shop_id'] = $shop['shop_id'];
        $filter['closed'] = 0;
        if($items = K::M('trade/order')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($type, '{page}')));
            $this->pagedata['items'] = $items;
            $order_ids = $uids = array();
            foreach($items as $k=>$v){
                $order_ids[$v['order_id']] = $v['order_id'];
                $uids[$v['uid']] = $v['uid'];
            }
            if($order_ids){
                $product_ids = array();
                if($product_list = K::M('trade/product')->items(array('order_id'=>$order_ids), null, 1, 1000)){
                    foreach($product_list as $v){
                        $product_ids[$v['product_id']] = $v['product_id'];
                    }
                }
                if($product_ids){
                    if($org_products = K::M('product/product')->items_by_ids($product_ids)){
                        foreach($product_list as $k=>$v){
                            $product_list[$k] = array_merge((array)$org_products[$v['product_id']], $v);
                        }
                    }
                }
                $this->pagedata['product_list'] = $product_list;
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/shop/order/items.html';
    }

    public function update($status, $order_no=null)
    {
        $shop = $this->ucenter_shop();
        if(!in_array($status, array('ship', 'cancel'))){
            $this->error(404);
        }else if(!is_numeric($order_no)){
            $this->error(404);
        }else if(!$order = K::M('trade/order')->detail_by_no($order_no)){
            $this->err->add('订单不存在或已经删除', 211);
        }else if($order['shop_id'] != $shop['shop_id']){
            $this->err->add('您没有权限操作该订单', 211);
        }else if($order['order_status'] < 0){
            $this->err->add('订单已经取消，不可操作', 212);
        }else if($order['order_status'] == 2){
            $this->err->add('订单已完成，不可操作', 213);
        }else if('cancel' == $status){
            if($order['pay_status']){
                $this->err->add('订单已支付，不可取消', 214);
            }else if($order['order_status'] > 0){
                $this->err->add('订单已发货不可取消', 215);
            }else {
                if(K::M('trade/order')->update($order['order_id'], array('order_status'=>-2), true)){
                    $this->err->add('取消订单成功');
                }
            }
        }else if('ship' == $status){
            if($order['order_status'] == 1){
                $this->err->add('订单已经发货,不可重复发货', 214);
            }else if(empty($order['order_status'])){
                if(K::M('trade/order')->update($order['order_id'], array('order_status'=>1), true)){
                    $member = K::M('member/member')->member($order['uid']);
                    $smsdata = $maildata = array('order_no'=>$order['order_no'], 'order_amount'=>$order['amount'], 'contact'=>$order['contact'], 'shop_name'=>$shop['name'], 'shop_phone'=>$shop['phone']);
                    if($mobile = K::M('verify/check')->mobile($order['mobile'])){
                        K::M('sms/sms')->send($order['mobile'], 'order_ship_buyer', $smsdata);
                    }else if($member['verify_mobile'] && K::M('verify/check')->mobile($member['mobile'])){
                        K::M('sms/sms')->send($member['mobile'], 'order_ship_buyer', $smsdata);
                    }
                    $maildata['link'] = K::M('helper/link')->mklink('trade/order:detail', array($order['order_no']), array(), true);
                    if($member['mail']){
                        K::M('helper/mail')->send($member['mail'], 'order_ship_buyer', $maildata);
                    }
                    $this->err->add('订单发货成功');
                }
            }
        }
    }

}