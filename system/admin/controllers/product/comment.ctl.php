<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.ctl.php 5994 2014-08-02 12:35:30Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Product_Comment extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['comment_id']){
                $filter['comment_id'] = $SO['comment_id'];
            }else{
                if($SO['product_id']){
                    $filter['product_id'] = $SO['product_id'];
                }else if($SO['shop_id']){
                    $filter['shop_id'] = $SO['shop_id'];
                }
                if($SO['uid']){$filter['uid'] = $SO['uid'];}
                if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
                if(is_array($SO['replytime'])){if($SO['replytime'][0] && $SO['replytime'][1]){$a = strtotime($SO['replytime'][0]); $b = strtotime($SO['replytime'][1])+86400;$filter['replytime'] = $a."~".$b;}}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
        }
        $filter['closed'] = 0;
        if($items = K::M('product/comment')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $uids = $sids = $product_ids = array();
            foreach($items as $k=>$v){
                if($v['uid']){
                    $uids[$v['uid']] = $v['uid'];
                }
                if($v['shop_id']){
                    $sids[$v['shop_id']] = $v['shop_id'];
                }                
                if($v['product_id']){
                    $product_ids[$v['product_id']] = $v['product_id'];
                }                
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            if($sids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($sids);
            }
            if($product_ids){
                $this->pagedata['product_list'] = K::M('product/product')->items_by_ids($product_ids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:product/comment/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:product/comment/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($comment_id = K::M('product/comment')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?product/comment-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:product/comment/create.html';
        }
    }

    public function edit($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = $this->GP('comment_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('product/comment')->detail($comment_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('product/comment')->update($comment_id, $data)){
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
        	$this->tmpl = 'admin:product/comment/edit.html';
        }
    }

    public function reply($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = (int)$this->GP('comment_id'))){
            $this->err->add('非法的数据提交', 201);
        }else if(!$detail = K::M('product/comment')->detail($comment_id)){
            $this->err->add('原评论内容已经不存在', 202);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('product/comment')->update($comment_id, $data)){
                    $this->err->add('回复评论内容成功');
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
            $this->tmpl = 'admin:product/comment/reply.html';
        }      
    }

    public function doaudit($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('product/comment')->batch($comment_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('product/comment')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('product/comment')->delete($comment_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('product/comment')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}