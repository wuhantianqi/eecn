<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: main.ctl.php 2034 2013-12-07 03:08:33Z $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Activity_Main extends Ctl {

    public function index($page = 1) {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        $params = array();
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['id']) {
                $filter['id'] = $SO['id'];
            }
            if ($SO['title']) {
                $filter['title'] = "LIKE:%" . $SO['title'] . "%";
            }
            if ($SO['cate_id']) {
                $filter['cate_id'] = $SO['cate_id'];
            }
            if ($SO['city_id']) {
                $filter['city_id'] = $SO['city_id'];
            }
            if ($SO['area_id']) {
                $filter['area_id'] = $SO['area_id'];
            }
            if ($SO['reg_time']) {
                $filter['reg_time'] = "LIKE:%" . $SO['reg_time'] . "%";
            }
            if ($SO['bg_time']) {
                $filter['bg_time'] = "LIKE:%" . $SO['bg_time'] . "%";
            }
            if ($SO['end_time']) {
                $filter['end_time'] = "LIKE:%" . $SO['end_time'] . "%";
            }
            if ($SO['lng']) {
                $filter['lng'] = ">=:" . $SO['lng'];
            }
            if ($SO['lat']) {
                $filter['lat'] = "<=:" . $SO['lat'];
            }
            $params['SO'] = $SO;
        }
        if (isset($_GET['closed'])) {
            $params['closed'] = $pager['closed'] = $filter['closed'] = (int) $_GET['closed'];
        }
        if ($items = K::M('activity/main')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), $params);
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->pagedata['cateList'] = K::M("activity/cate")->fetch_all();
        $this->tmpl = 'admin:activity/main/items.html';
    }

    public function so() {
        $this->pagedata['cates'] = K::M('activity/cate')->fetch_all();
        $this->tmpl = 'admin:activity/main/so.html';
    }

    public function create() {
        if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            } else {
                if ($_FILES['data']) {
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach ($attachs as $k => $attach) {
                        if ($attach['error'] == UPLOAD_ERR_OK) {
                            if ($a = $upload->upload($attach, 'activity')) {
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }

                if ($id = K::M('activity/main')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?activity/main-index.html');
                }
            }
        } else {
            $this->pagedata['cates'] = K::M('activity/cate')->fetch_all();

            $this->tmpl = 'admin:activity/main/create.html';
        }
    }

    public function edit($id = null) {
        if (!($id = (int) $id) && !($id = $this->GP('id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('activity/main')->detail($id)) {
            $this->err->add('您要修改的内容不存在或已经操作成功', 212);
        } else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            } else {
                if ($_FILES['data']) {
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach ($attachs as $k => $attach) {
                        if ($attach['error'] == UPLOAD_ERR_OK) {
                            if ($a = $upload->upload($attach, 'activity')) {
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }

                if (K::M('activity/main')->update($id, $data)) {
                    $this->err->add('修改内容成功');
                }
            }
        } else {
            $this->pagedata['detail'] = $detail;
            $this->pagedata['cates'] = K::M('activity/cate')->fetch_all();
            $this->tmpl = 'admin:activity/main/edit.html';
        }
    }

    public function delete($id) {
        if ($id = (int) $id) {
            if (K::M('activity/main')->delete($id)) {
                $this->err->add('操作成功成功');
            }
        } else if ($ids = $this->GP('id')) {
            if (K::M('activity/main')->delete($ids)) {
                $this->err->add('批量操作成功成功');
            }
        } else {
            $this->err->add('未指定要操作成功的内容ID', 401);
        }
    }

    public function renew($id) {
        if ($id = (int) $id) {
            if (K::M('activity/main')->renew($id)) {
                $this->err->add('操作成功成功');
            }
        } else if ($ids = $this->GP('id')) {
            if (K::M('activity/main')->renew($ids)) {
                $this->err->add('批量操作成功成功');
            }
        } else {
            $this->err->add('未指定要操作成功的内容ID', 401);
        }
    }

}
