<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.ctl.php 2335 2013-12-18 17:15:56Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Case_Comment extends Ctl {

    public function index($page = 1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['case_id']) {
                $filter['case_id'] = $SO['case_id'];
            }
            if ($SO['uid']) {
                $filter['uid'] = $SO['uid'];
            }
            if ($SO['content']) {
                $filter['content'] = "LIKE:%" . $SO['content'] . "%";
            }
            if ($SO['audit']) {
                $filter['audit'] = $SO['audit'];
            }
        }
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if ($items = K::M('case/comment')->items($filter, null, $page, $limit, $count)) {
            $uids = $caseids = array();
            foreach ($items as $k => $v) {
                $uids[$v['uid']] = $v['uid'];
                $caseids[$v['case_id']] = $v['case_id'];
                $items[$k]['clientip'] = $v['clientip'] . '(' . K::M("misc/location")->location($v['clientip']) . ')';
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
        }
        $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        $this->pagedata['caseList'] = K::M('case/case')->items_by_ids($caseids);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:case/comment/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:case/comment/so.html';
    }

    public function create()
    {
        if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }else if(!$case_id = (int)$data['case_id']){
                $this->err->add('未选要发布到的案例', 212);
            }else if(!$case = K::M('case/case')->detail($case_id)){
                $this->err->add('您选的案例不存在或已经删除', 213);
            }else if(!$this->check_city($case['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else {

                if ($comment_id = K::M('case/comment')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?case/comment-index.html');
                }
            }
        }
        else {
            $this->tmpl = 'admin:case/comment/create.html';
        }

	
    }

    public function edit($comment_id = null)
    {
        if (!($comment_id = (int) $comment_id) && !($comment_id = $this->GP('comment_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }else if (!$detail = K::M('case/comment')->detail($comment_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {
				unset($data['case_id'],$data['city_id']);
                if (K::M('case/comment')->update($comment_id, $data)) {
                    $this->err->add('修改内容成功');
                }
            }
        }
        else {
            if ($uid = (int) $detail['uid']) {
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:case/comment/edit.html';
        }
    }

    public function doaudit($comment_id = null)
    {
		if($comment_id = (int)$comment_id){
            if($comment = K::M('case/comment')->detail($comment_id)){
                if(!$this->check_city($comment['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('case/comment')->batch($comment_id, array('audit' => 1))){
                    $this->err->add('审核内容成功');
                }
            }
        }else if($ids = $this->GP('comment_id')){
            if($items = K::M('case/comment')->items_by_ids($ids)){
                $aids  = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['comment_id']] = $v['comment_id']; 
                }
                if($aids && K::M('case/comment')->batch($ids, array('audit' => 1))){
                    $this->err->add('批量审核内容成功');
                }
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($comment_id = null)
    {
       
		if($comment_id = (int)$comment_id){
            if($comment = K::M('case/comment')->detail($comment_id)){
                if(!$this->check_city($comment['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('case/comment')->delete($comment_id)){
                    $this->err->add('删除评论成功');
                }
            }
        }else if($ids = $this->GP('comment_id')){
            if($items = K::M('case/comment')->items_by_ids($ids)){
                $aids  = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['comment_id']] = $v['comment_id']; 
                }
                if($aids && K::M('case/comment')->delete($aids)){
                    $this->err->add('批量删除评论成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
