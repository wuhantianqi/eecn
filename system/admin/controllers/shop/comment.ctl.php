<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.ctl.php 6203 2014-09-01 15:20:54Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Comment extends Ctl
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
                if($SO['shop_id']){$filter['shop_id'] = $SO['shop_id'];}
                if($SO['uid']){$filter['uid'] = $SO['uid'];}
                if($SO['content']){$filter['content'] = "LIKE:%".$SO['content']."%";}
                if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
                if(is_array($SO['replytime'])){if($SO['replytime'][0] && $SO['replytime'][1]){$a = strtotime($SO['replytime'][0]); $b = strtotime($SO['replytime'][1])+86400;$filter['replytime'] = $a."~".$b;}}                
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
        }
        $filter['closed'] = 0;
        if($items = K::M('shop/comment')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $uids = $sids = array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
                $sids[$v['shop_id']] = $v['shop_id'];
            }
            $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($sids);        
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/comment/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/comment/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($comment_id = K::M('shop/comment')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/comment-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/comment/create.html';
        }
    }

    public function edit($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = $this->GP('comment_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/comment')->detail($comment_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('shop/comment')->update($comment_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }              
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:shop/comment/edit.html';
        }
    }

    public function reply($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = (int)$this->GP('comment_id'))){
            $this->err->add('非法的数据提交', 201);
        }else if(!$detail = K::M('shop/comment')->detail($comment_id)){
            $this->err->add('原评论内容已经不存在', 202);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('shop/comment')->update($comment_id, $data)){
                    $this->err->add('回复评论内容成功');
                }
            } 
        }else{
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }              
            $this->pagedata['detail'] = $detail;          
            $this->tmpl = 'admin:shop/comment/reply.html';
        }      
    }

    public function doaudit($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('shop/comment')->batch($comment_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('shop/comment')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('shop/comment')->delete($comment_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('shop/comment')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}