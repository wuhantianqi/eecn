<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Diary_Comment extends Ctl {

    public function index($page = 1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['diary_id']) {
                $filter['diary_id'] = $SO['diary_id'];
            }
            if ($SO['uid']) {
                $filter['uid'] = $SO['uid'];
            }
            if ($SO['content']) {
                $filter['content'] = "LIKE:%" . $SO['content'] . "%";
            }
        }
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if ($items = K::M('diary/comment')->items($filter, null, $page, $limit, $count)) {
            $uids = $diaryids = array();
            foreach ($items as $k => $v) {
                $uids[$v['uid']] = $v['uid'];
                $diaryids[$v['diary_id']] = $v['diary_id'];
                $items[$k]['create_ip'] = $v['create_ip'] . '(' . K::M("misc/location")->location($v['create_ip']) . ')';
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
        }
		$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        $this->pagedata['diaryList'] = K::M('diary/diary')->items_by_ids($diaryids);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:diary/comment/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:diary/comment/so.html';
    }

    public function create()
    {
        if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }else if(!$diary_id = (int)$data['diary_id']){
                $this->err->add('未选要发布到的日记', 212);
            }else if(!$diary = K::M('diary/diary')->detail($diary_id)){
                $this->err->add('您选的日记不存在或已经删除', 213);
            }else if(!$this->check_city($diary['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else {
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if ($comment_id = K::M('diary/comment')->create($data)) {
					K::M('diary/comment')->comment_count($diary['diary_id']);
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?diary/comment-index.html');
                }
            }
        }
        else {
            $this->tmpl = 'admin:diary/comment/create.html';
        }
    }

    public function edit($comment_id = null)
    {
        if (!($comment_id = (int) $comment_id) && !($comment_id = $this->GP('comment_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }
        else if (!$detail = K::M('diary/comment')->detail($comment_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }
		else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }
        else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {

                unset($data['city_id'],$data['comment_id'],$data['diary_id']);
				if (K::M('diary/comment')->update($comment_id, $data)) {
                    $this->err->add('修改内容成功');
					 $this->err->set_data('forward', '?diary/comment-index.html');
                }
            }
        }
        else {
            if ($uid = (int) $detail['uid']) {
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
			$this->pagedata['diary'] = K::M('diary/diary')->detail($detail['diary_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:diary/comment/edit.html';
        }
    }

    public function doaudit($comment_id = null)
    {
        if ($comment_id = (int) $comment_id) {
            if (K::M('diary/comment')->batch($comment_id, array('audit' => 1))) {
                $this->err->add('审核内容成功');
            }
        }
        else if ($ids = $this->GP('comment_id')) {
            if (K::M('diary/comment')->batch($ids, array('audit' => 1))) {
                $this->err->add('批量审核内容成功');
            }
        }
        else {
            $this->err->add('未指定要审核的内容', 401);
        }

    }

    public function delete($comment_id = null)
    {
		if($comment_id = (int)$comment_id){
            if($comment = K::M('diary/comment')->detail($comment_id)){
                if(!$this->check_city($comment['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('diary/comment')->delete($comment_id)){
					K::M('diary/comment')->comment_count($comment['diary_id']);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('comment_id')){
            if($items = K::M('diary/comment')->items_by_ids($ids)){
                $aids = $company_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['comment_id']] = $v['comment_id'];
                    $company_ids[$v['diary_id']] = $v['diary_id'];
                }
                if($aids && K::M('diary/comment')->delete($aids)){
					K::M('diary/comment')->comment_count($company_ids);
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
