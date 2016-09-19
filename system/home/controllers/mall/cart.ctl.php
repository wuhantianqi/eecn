<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cart.ctl.php 6378 2014-09-28 02:25:09Z youyi $
 */

class Ctl_Mall_Cart extends Ctl 
{
    
    public function index()
    {
        $cart = K::M('trade/cart')->detail();
        if($shop_ids = $cart['shop_ids']){
            $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
        }
        $this->pagedata['cart'] = $cart;
        $this->tmpl = 'mall/order/cart.html';
    }

    public function checkout()
    {
        $this->check_login();
        $cart = K::M('trade/cart')->detail();
        if($shop_ids = $cart['shop_ids']){
            $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
        }
        $this->pagedata['cart'] = $cart;
        $this->tmpl = 'mall/order/checkout.html';
    }    

    public function add($product_id=0, $spec_id=0, $num=0)
    {
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!($num = (int)$num) && !($num = (int)$this->GP('num'))){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id, true)){
            $this->err->add('您要购买商品不存在或已经删除', 211);
        }else if(empty($product['audit'])){
            $this->err->add('商品还在审核中，暂不能购买', 212);
        }else{
            if($spid = (int)$this->GP('spec_id')){
                $spec_id = $spid;
            }else{
                $spec_id = (int)$spec_id;
            }
			
            $shop = $this->check_shop($product['shop_id']);
            if($this->uid && $this->uid == $shop['uid']){
                $this->err->add('不能购买自己店铺的商品', 221);
            }else if(K::M('trade/cart')->add($product_id, $spec_id, $num)){
                $this->err->add('添加到购物车成功');
            }
        }
    }

    public function update($product_id=0, $spec_id=0, $num=0)
    {
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!($num = (int)$num) && !($num = (int)$this->GP('num'))){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id, true)){
            $this->err->add('您要购买商品不存在或已经删除', 211);
        }else if(empty($product['audit'])){
            $this->err->add('商品还在审核中，暂不能购买', 212);
        }else{
            if($spid = (int)$this->GP('spec_id')){
                $spec_id = $spid;
            }else{
                $spec_id = (int)$spec_id;
            }
            $shop = $this->check_shop($product['shop_id']);
            if(K::M('trade/cart')->update($product_id, $spec_id, $num)){
                $this->err->add('更新到购物车成功');
            }
        }
    }

    public function delete($product_id=null, $spec_id=0)
    {
        $spec_id = (int)$spec_id;
        if(!$product_id = (int)$product_id){
            $this->err->add('未定义操作');
        }else if(K::M('trade/cart')->delete($product_id, $spec_id)){
            $this->err->add('从购物车移除商品成功');
        }
    }

    public function clean()
    {
        K::M('trade/cart')->clean();
    }
}