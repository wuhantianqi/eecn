<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: supply.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Ask_Supply extends Ctl {

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
        }
        if ($items = K::M('ask/supply')->items($filter, null, $page, $limit, $count)) {
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
        $this->tmpl = 'admin:ask/supply/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:ask/supply/so.html';
    }

    public function create($ask_id=0)
    {
        if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {

                if ($supply_id = K::M('ask/supply')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?ask/supply-index.html');
                }
            }
        }
        else {
            $this->pagedata['ask_id'] = (int)$ask_id;
            $this->tmpl = 'admin:ask/supply/create.html';
        }
    }

    public function edit($supply_id = null)
    {
        if (!($supply_id = (int) $supply_id) && !($supply_id = $this->GP('supply_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }
        else if (!$detail = K::M('ask/supply')->detail($supply_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }
        else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {

                if (K::M('ask/supply')->update($supply_id, $data)) {
                    $this->err->add('修改内容成功');
                }
            }
        }
        else {
            if ($uid = (int) $detail['uid']) {
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:ask/supply/edit.html';
        }
    }

    public function doaudit($supply_id = null)
    {
        if ($supply_id = (int) $supply_id) {
            if (K::M('ask/supply')->batch($supply_id, array('audit' => 1))) {
                $this->err->add('审核内容成功');
            }
        }
        else if ($ids = $this->GP('supply_id')) {
            if (K::M('ask/supply')->batch($ids, array('audit' => 1))) {
                $this->err->add('批量审核内容成功');
            }
        }
        else {
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($supply_id = null)
    {
        if ($supply_id = (int) $supply_id) {
            if (K::M('ask/supply')->delete($supply_id)) {
                $this->err->add('删除成功');
            }
        }
        else if ($ids = $this->GP('supply_id')) {
            if (K::M('ask/supply')->delete($ids)) {
                $this->err->add('批量删除成功');
            }
        }
        else {
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
