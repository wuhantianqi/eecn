<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: answer.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Ask_Answer extends Ctl
{

    public function index($ask_id = 0, $page = 1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['ask_id']) {
               $ask_id = $SO['ask_id'];
            }
            if ($SO['uid']) {
                $filter['uid'] = $SO['uid'];
            }
            if (is_array($SO['dateline'])) {
                if ($SO['dateline'][0] && $SO['dateline'][1]) {
                    $a = strtotime($SO['dateline'][0]);
                    $b = strtotime($SO['dateline'][1]) + 86400;
                    $filter['dateline'] = $a . "~" . $b;
                }
            }
        }
        
        if($ask_id = (int)$ask_id){
             $filter['ask_id'] = $ask_id;
			 $this->pagedata['detail'] = K::M('ask/ask')->detail($ask_id);
        }
		
        if ($items = K::M('ask/answer')->items($filter, null, $page, $limit, $count)) {
            $askids = $uids =  array();
            foreach($items as $v){
                $askids[$v['ask_id']] = $v['ask_id'];
                if ($v['uid']) {
                    $uids[$v['uid']] = $v['uid'];
                }
            }
            if(!empty($askids)){
                $this->pagedata['asks'] = K::M('ask/ask')->items_by_ids($askids);
            }
            if (!empty($uids)) {
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($ask_id, '{page}')), array('SO' => $SO));
        }
		
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['ask_id'] = $ask_id;
        $this->tmpl = 'admin:ask/answer/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:ask/answer/so.html';
    }

    public function create($ask_id = 0)
    {
		if (!($ask_id = (int) $ask_id) && !($ask_id = $this->GP('ask_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
		}else if (!$detail = K::M('ask/ask')->detail($ask_id)) {
            $this->err->add('问题不存在或已经删除', 212);
        }else{
			if ($this->checksubmit()) {
				if (!$data = $this->GP('data')) {
					$this->err->add('非法的数据提交', 201);
				}
				else {
					$data['ask_id'] = $ask_id;
					if ($answer_id = K::M('ask/answer')->create($data)) {
						$this->err->add('添加内容成功');
						$this->err->set_data('forward', '?ask/answer-index-'.$ask_id.'.html');
					}
				}
			}
			else {
				$this->pagedata['detail'] = $detail;
				$this->pagedata['ask_id'] = (int)$ask_id;
				$this->tmpl = 'admin:ask/answer/create.html';
			}
		}
    }

    public function edit($answer_id = null)
    {
        if (!($answer_id = (int) $answer_id) && !($answer_id = $this->GP('answer_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }else if (!$detail = K::M('ask/answer')->detail($answer_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if (!$ask = K::M('ask/ask')->detail($detail['ask_id'])) {
            $this->err->add('该问题不存在或已经删除', 212);
        }else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {
				unset($data['ask_id']);
                if (K::M('ask/answer')->update($answer_id, $data)) {
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?ask/answer-index-'.$detail['ask_id'].'.html');
                }
            }
        }
        else {
            if ($uid = (int) $detail['uid']) {
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['detail'] = $detail;
			 $this->pagedata['ask'] = $ask;
            $this->tmpl = 'admin:ask/answer/edit.html';
        }
    }

	public function good($ask_id,$answer_id){
		if (!($ask_id = (int) $ask_id) && !($ask_id = $this->GP('ask_id'))) {
            $this->err->add('未指定要问题ID', 211);
        }else if (!$detail = K::M('ask/ask')->detail($ask_id)) {
            $this->err->add('问题不存在或已经删除', 212);
        }else if (!($answer_id = (int) $answer_id) && !($answer_id = $this->GP('answer_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }else if (!$answer = K::M('ask/answer')->detail($answer_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else{
			if(K::M('ask/ask')->update($ask_id, array('answer_id' => $answer_id))){
				 $this->err->add('设为满意答案成功！');
			}
		}
	}

    public function doaudit($answer_id = null)
    {
        if ($answer_id = (int) $answer_id) {
            if (K::M('ask/answer')->batch($answer_id, array('audit' => 1))) {
                $this->err->add('审核内容成功');
            }
        }
        else if ($ids = $this->GP('answer_id')) {
            if (K::M('ask/answer')->batch($ids, array('audit' => 1))) {
                $this->err->add('批量审核内容成功');
            }
        }
        else {
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($answer_id = null)
    {
        if ($answer_id = (int) $answer_id) {
            if (K::M('ask/answer')->delete($answer_id)) {
                $this->err->add('删除成功');
            }
        }
        else if ($ids = $this->GP('answer_id')) {
            if (K::M('ask/answer')->delete($ids)) {
                $this->err->add('批量删除成功');
            }
        }
        else {
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
