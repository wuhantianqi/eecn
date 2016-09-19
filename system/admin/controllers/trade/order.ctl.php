<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: order.ctl.php 3903 2014-03-17 02:09:44Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Trade_Order extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['order_id']){$filter['order_id'] = $SO['order_id'];}
            if($SO['order_no']){$filter['order_no'] = "LIKE:%".$SO['order_no']."%";}
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['shop_id']){$filter['shop_id'] = $SO['shop_id'];}
            if(is_array($SO['amount'])){$a = intval($SO['amount'][0]);$b=intval($SO['amount'][1]);if($a && $b){$filter['amount'] = $a."~".$b;}}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if(is_numeric($SO['pay_status'])){$filter['pay_status'] = $SO['pay_status'];}
            if(is_numeric($SO['order_status'])){$filter['order_status'] = $SO['order_status'];}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        $filter['closed'] = 0;
        if($items = K::M('trade/order')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $uids = $shop_ids = array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:trade/order/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:trade/order/so.html';
    }

    public function detail($order_id = null)
    {
        if(!$order_id = (int)$order_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('trade/order')->detail($order_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:trade/order/detail.html';
        }
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($order_id = K::M('trade/order')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?trade/order-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:trade/order/create.html';
        }
    }

    public function edit($order_id=null)
    {
        if(!($order_id = (int)$order_id) && !($order_id = $this->GP('order_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('trade/order')->detail($order_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('trade/order')->update($order_id, $data)){
                    $this->err->add('修改内容成功');
                }
            } 
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:trade/order/edit.html';
        }
    }

    public function doaudit($order_id=null)
    {
        if($order_id = (int)$order_id){
            if(K::M('trade/order')->batch($order_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('order_id')){
            if(K::M('trade/order')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($order_id=null)
    {
        if($order_id = (int)$order_id){
            if(K::M('trade/order')->delete($order_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('order_id')){
            if(K::M('trade/order')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}