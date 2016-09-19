<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Shop_Yuyue extends Ctl_Ucenter 
{
    
    public function shop($page=1)
    {
        $shop = $this->ucenter_shop();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('shop_id'=>$shop['shop_id'], 'closed'=>0);
        if($items = K::M('shop/yuyue')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $product_ids = array();
            foreach($items as $k=>$v){
                if($v['product_id']){
                    $product_ids[$v['product_id']] = $v['product_id'];
                }
            }
            if($product_ids){
                $this->pagedata['product_list'] = K::M('product/product')->items_by_ids($product_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/shop/yuyue/items.html';
    }

    public function detail($yuyue_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!$yuyue_id = (int)$yuyue_id){
            $this->err->add('未指定要查看的预约', 211);
        }else if(!$detail = K::M('shop/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['shop_id'] != $shop['shop_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            if($detail['product_id']){
                $this->pagedata['product'] = K::M('product/product')->detail($detail['product_id']);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/shop/yuyue/detail.html';
        }
    }

    public function save()
    {
        $shop = $this->ucenter_shop();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$yuyue_id = (int)$this->GP('yuyue_id')){
                $this->err->add('未指定要保存的预约', 211);
            }else if(!$detail = K::M('shop/yuyue')->detail($yuyue_id)){
                $this->err->add('预约不存在或已经删除', 212);
            }else if($detail['shop_id'] != $shop['shop_id']){
                $this->err->add('您没有权限操作该预约', 213);
            }else if(K::M('shop/yuyue')->update($yuyue_id, $data)){
                $this->err->add('更新预约状态成功');
            }
        }
    }
}