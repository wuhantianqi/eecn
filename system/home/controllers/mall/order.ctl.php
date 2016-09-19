<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: order.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */

class Ctl_Mall_Order extends Ctl 
{
    
    public function create()
    {
        $this->check_login();
        if(!$data = $this->checksubmit('data')){
            $this->err->add('非法的数据提交', 211);
        }else{
            $data['uid'] = $this->uid;
            $cart = K::M('trade/cart')->detail();
            if(empty($cart['product_count'])){
                $this->err->add('您的购物车中没有商品，请先添加要购买的商品', 212);
                $this->err->set_data('forward', $this->mklink('mall/index'));
            }else if($order = K::M('trade/order')->create_by_cart($cart, $data)){
                $this->system->cookie->set('Order-Buy', 1, 3600);
                $this->err->add('提交定单成功');
                K::M('trade/cart')->clean();
                $this->err->set_data('forward', $this->mklink('trade/order:success', array($order['order_no'])));
            }
        }
    }

    public function success($order_no)
    {
        if(!$this->system->cookie->get('Order-Buy')){
            header("Location:".$this->mklink('trade/order:detail', array($order_no)));
            exit();
        }else if($order = K::M('trade/order')->detail_by_no($order_no)){
            $this->pagedata['order'] = $order;
            $pager['order_no'] = $order_no;
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'trade/order/success.html';
        }
    }

    public function detail($order_no=null)
    {
        if(!is_numeric($order_no)){
            $this->error(404);
        }else if($this->check_login()){
            if(!$order = K::M('trade/order')->detail_by_no($order_no)){
                $this->err->add('您的订单不存在或已经删除', 211);
            }else if(!$shop = K::M('shop/shop')->detail($order['shop_id'])){
                $this->err->add('商家不存在或已经删除', 211);
            }else if(($order['uid'] != $this->uid) && ($shop['uid'] != $this->uid)){
                $this->err->add('您没有权限查看该订单', 212);
            }else{
                $this->pagedata['shop'] = $shop;
                $this->pagedata['order'] = $order;
                $this->tmpl = 'trade/order/detail.html';
            }
        }
    }

    public function payment($order_no=null)
    {
        if(!is_numeric($order_no) && !($order_no = (int)$this->GP('order_no'))){
            $this->error(404);
        }else if($this->check_login()){
            if(!$order = K::M('trade/order')->detail_by_no($order_no)){
                $this->err->add('您的订单不存在或已经删除', 211);
            }else if($order['order_status'] < 0){
                $this->err->add('订单已经取消不可支付', 212);
            }else if($order['order_status'] == 2){
                $this->err->add('订单已经完成不可支付', 213);
            }else if($order['pay_status']){
                $this->err->add('该订单已经支付过了,不需要重复支付', 212);
            }else{
                $this->pagedata['order'] = $order;
                $this->pagedata['pay_list'] = K::M('payment/payment')->fetch_all();
                $this->tmpl = 'trade/order/payment.html';
            }
        }
    }
}