<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Product_Comment extends Ctl_Ucenter 
{
    
    public function shop($page=1)
    {
        $shop = $this->ucenter_shop();
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('shop_id'=>$shop['shop_id'], 'closed'=>0);
        if($items = K::M('product/comment')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $product_ids = $uids = array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
                $product_ids[$v['product_id']] = $v['product_id'];
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/view')->items_by_ids($uids);
            }
            if($product_ids){
                $this->pagedata['product_list'] = K::M('product/product')->items_by_ids($product_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/product/comment/items.html';      
    }

    public function detail($comment_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($comment_id = (int)$comment_id) && !($comment_id = (int)$this->GP('comment_id'))){
            $this->err->add('未指定要查看的评论', 211);
        }else if(!$detail = K::M('product/comment')->detail($comment_id)){
            $this->err->add('评论不存在或已经删除', 212);
        }else if($shop['shop_id'] != $detail['shop_id']){
            $this->err->add('你没有权限查看该评论', 212);
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            if($product_id = $detail['product_id']){
                $this->pagedata['product'] = K::M('product/product')->detail($product_id);
            }
            $this->pagedata['pager'] = $pager;
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/product/comment/detail.html';
        }
    }

    public function reply($comment_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($comment_id = (int)$comment_id) && !($comment_id = (int)$this->GP('comment_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$comment = K::M('product/comment')->detail($comment_id)){
            $this->err->add('评论不存在或已经删除', 212);
        }else if(K::M('member/group')->check_priv($shop['group_id'], 'allow_reply') < 0){
            $this->err->add('您是【'.$audit_title.'】没有权限回复点评', 333);
        }else if($shop['shop_id'] != $comment['shop_id']){
            $this->err->add('你没有权限回复该评论', 212);
        }else if($comment['replytime'] && $comment['reply']){
            $this->err->add('您已经回复过该评论了', 212);
        }else if($this->checksubmit()){
            if(!$reply_content = $this->GP('reply_content')){
                $this->err->add('回复内容不能空', 213);
            }else if(K::M('product/comment')->reply($comment_id, $reply_content)){
                $this->err->add('回复评论成功');
            }
        }else{
            if($uid = $comment['uid']){
                $this->pagedata['member'] = K::M('member/view')->detail($uid);
            }
            if($product_id = $detail['product_id']){
                $this->pagedata['product'] = K::M('product/product')->detail($product_id);
            }            
            $this->pagedata['comment'] = $comment;
            $this->tmpl = 'ucenter/shop/comment/reply.html';
        }
    }
}