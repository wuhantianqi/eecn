<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Connect_Connect extends Ctl {

    public function index($page = 1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['type']) {
                $filter['type'] = $SO['type'];
            }
            if ($SO['uid']) {
                $filter['uid'] = $SO['uid'];
            }
        }
        if ($items = K::M('connect/connect')->items($filter, null, $page, $limit, $count)) {
            foreach($items as $k=>$v){
                $uids[] = $v['uid'];
                 $items[$k]['create_ip'] = $v['create_ip'].'('. K::M("misc/location")->location($v['create_ip']) .')';
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
        }
        $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['type_cfg'] = K::M('connect/connect')->get_type_cfg();
        $this->tmpl = 'admin:connect/connect/items.html';
    }

    public function so()
    {   
        $this->pagedata['type_cfg'] = K::M('connect/connect')->get_type_cfg();
        $this->tmpl = 'admin:connect/connect/so.html';
    }

    public function edit($connect_id = null)
    {
        if (!($connect_id = (int) $connect_id) && !($connect_id = $this->GP('connect_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }
        else if (!$detail = K::M('connect/connect')->detail($connect_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }
        else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {

                if (K::M('connect/connect')->update($connect_id, $data)) {
                    $this->err->add('修改内容成功');
                }
            }
        }
        else {
            $this->pagedata['detail'] = $detail;
            $this->pagedata['member'] = K::M('member/member')->detail($detail['uid']);
            $this->pagedata['type_cfg'] = K::M('connect/connect')->get_type_cfg();
            $this->tmpl = 'admin:connect/connect/edit.html';
        }
    }

    public function delete($connect_id = null)
    {
        if ($connect_id = (int) $connect_id) {
            if (K::M('connect/connect')->delete($connect_id)) {
                $this->err->add('删除成功');
            }
        }
        else if ($ids = $this->GP('connect_id')) {
            if (K::M('connect/connect')->delete($ids)) {
                $this->err->add('批量删除成功');
            }
        }
        else {
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
