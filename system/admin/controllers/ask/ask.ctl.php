<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: ask.ctl.php 5649 2014-06-25 11:13:56Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Ask_Ask extends Ctl {

    public function index($page = 1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if($SO['title']) {$filter['title'] = "LIKE:%" . $SO['title'] . "%";}
            if($SO['cat_id']) {$filter['cat_id'] = $SO['cat_id'];}
            if($SO['uid']) {$filter['uid'] = $SO['uid'];}
            if($SO['answer_id']) {$filter['answer_id'] = $SO['answer_id'];}
            if(is_numeric($SO['audit'])) {$filter['audit'] = $SO['audit'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        if ($items = K::M('ask/ask')->items($filter, array('ask_id'=>'desc'), $page, $limit, $count)) {
            foreach ($items as $k => $v) {
                if ($v['uid']) {
                    $uids[$v['uid']] = $v['uid'];
                }
                $items[$k]['create_ip'] = $v['create_ip'] . '(' . K::M("misc/location")->location($v['create_ip']) . ')';
            }
            if (!empty($uids)) {
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
        }
        $this->pagedata['cates'] = K::M('ask/cate')->fetch_all();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:ask/ask/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:ask/ask/so.html';
    }

    public function create()
    {
        if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {
				if($_FILES['data']){
					foreach($_FILES['data'] as $k=>$v){
						foreach($v as $kk=>$vv){
							$attachs[$kk][$k] = $vv;
						}
					}
					$upload = K::M('magic/upload');
					foreach($attachs as $k=>$attach){
						if($attach['error'] == UPLOAD_ERR_OK){
							if($a = $upload->upload($attach, 'home')){
								$data[$k] = $a['photo'];
							}
						}
					}
				}
				
                if ($ask_id = K::M('ask/ask')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?ask/ask-index.html');
                }
            }
        }
        else {
            $this->tmpl = 'admin:ask/ask/create.html';
        }
    }

    public function edit($ask_id = null)
    {
        if (!($ask_id = (int) $ask_id) && !($ask_id = $this->GP('ask_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }
        else if (!$detail = K::M('ask/ask')->detail($ask_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }
        else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {
				if($_FILES['data']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'home')){
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }

                if (K::M('ask/ask')->update($ask_id, $data)) {
                    $this->err->add('修改内容成功');
                }
            }
        }
        else {
            $detail['cat_pids'] = K::M('ask/cate')->parent_ids($detail['cat_id'], ',', true);
            if ($uid = (int) $detail['uid']) {
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:ask/ask/edit.html';
        }
    }

    public function doaudit($ask_id = null)
    {
        if ($ask_id = (int) $ask_id) {
            if (K::M('ask/ask')->batch($ask_id, array('audit' => 1))) {
                $this->err->add('审核内容成功');
            }
        }
        else if ($ids = $this->GP('ask_id')) {
            if (K::M('ask/ask')->batch($ids, array('audit' => 1))) {
                $this->err->add('批量审核内容成功');
            }
        }
        else {
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($ask_id = null)
    {
        if ($ask_id = (int) $ask_id) {
            if (K::M('ask/ask')->delete($ask_id)) {
                $this->err->add('删除成功');
            }
        }
        else if ($ids = $this->GP('ask_id')) {
            if (K::M('ask/ask')->delete($ids)) {
                $this->err->add('批量删除成功');
            }
        }
        else {
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
