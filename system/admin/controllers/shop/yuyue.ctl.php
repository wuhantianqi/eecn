<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: yuyue.ctl.php 5702 2014-06-27 10:55:04Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Yuyue extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['yuyue_id']){
                $filter['yuyue_id'] = $SO['yuyue_id'];
            }else{
                if($SO['uid']){$filter['uid'] = $SO['uid'];}
                if($SO['product_id']){
                    $filter['product_id'] = $SO['product_id'];
                }else if($SO['shop_id']){
                    $filter['shop_id'] = $SO['shop_id'];
                }
                if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
        }
        if($items = K::M('shop/yuyue')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $uids = $shop_ids = $product_ids = array();
            foreach($items as $k=>$v){
                if($v['uid']){
                    $uids[$v['uid']] = $v['uid'];
                }
                if($v['shop_id']){
                    $shop_ids[$v['shop_id']] = $v['shop_id'];
                }
                if($v['product_id']){
                    $product_ids[$v['product_id']] = $v['product_id'];
                }                                
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
            if($product_ids){
                $this->pagedata['product_list'] = K::M('product/product')->items_by_ids($product_ids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/yuyue/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/yuyue/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($yuyue_id = K::M('shop/yuyue')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/yuyue-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/yuyue/create.html';
        }
    }

    public function edit($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/yuyue')->detail($yuyue_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('shop/yuyue')->update($yuyue_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($product_id = $detail['product_id']){
                $this->pagedata['product'] = K::M('product/product')->detail($product_id);
            }
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }            
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }             
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:shop/yuyue/edit.html';
        }
    }

    public function detail($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$detail = K::M('shop/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            if($product_id = $detail['product_id']){
                $this->pagedata['product'] = K::M('product/product')->detail($product_id);
            }
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }            
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:shop/yuyue/detail.html';
        }        
    }

    public function delete($yuyue_id=null)
    {
        if($yuyue_id = (int)$yuyue_id){
            if(K::M('shop/yuyue')->delete($yuyue_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('yuyue_id')){
            if(K::M('shop/yuyue')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}