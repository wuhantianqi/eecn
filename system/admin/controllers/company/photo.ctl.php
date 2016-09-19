<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: photo.ctl.php 2034 2013-12-07 03:08:33Z $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Company_Photo extends Ctl
{


    public function company($company_id, $page=1)
    {
        if(!$company_id = (int)$company_id){
            $this->err->add('未指定公司', 212);
        }else if(!$company = K::M('company/company')->detail($company_id)){
            $this->err->add('您选的公司不存在或已经删除', 213);
        }else if(!$this->check_city($company['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            $pager['count'] = $count = 0;
            $filter = array('company_id'=>$company_id);
            if ($items = K::M('company/photo')->items($filter, null, $page, $limit, $count)) {
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
            }
			$this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_id);
			$this->pagedata['company_id'] = $company_id;
            $this->pagedata['company'] = $company;        
            $this->pagedata['pager'] = $pager;
			$this->pagedata['type'] = K::M('company/photo')->get_type_means();
            $this->pagedata['items'] = $items;
            $this->tmpl = 'admin:company/photo/company.html';
        }
    }

    public function create($company_id)
    {
        if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }else if(!$company_id){
                $this->err->add('未选要发布到的公司', 212);
            }else if(!$company = K::M('company/company')->detail($company_id)){
                $this->err->add('您选的公司不存在或已经删除', 213);
            }else if(!$this->check_city($company['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else {
                if ($_FILES['data']) {
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach ($attachs as $k => $attach) {
                        if ($attach['error'] == UPLOAD_ERR_OK) {
                            if ($a = $upload->upload($attach, 'company')) {
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }
                if ($photo_id = K::M('company/photo')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward','?company/photo-company-'.$company_id.'.html');
                }
            }
        } else {
			$this->pagedata['company_id'] = $company_id;
            $this->pagedata['type'] = K::M('company/photo')->get_type_means();
            $this->tmpl = 'admin:company/photo/create.html';
        }
    }

    public function edit($photo_id = null)
    {
        if (!($photo_id = (int) $photo_id) && !($photo_id = $this->GP('photo_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('company/photo')->detail($photo_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if(!$company = K::M('company/company')->detail($detail['company_id'])){
            $this->err->add('该公司不存在或已被删除', 403);
        }else if(!$this->check_city($company['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if ($this->checksubmit('data')) {
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
                            if ($a = $upload->upload($attach, 'company')) {
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }
                unset($data['company_id'],$data['photo_id']);
                if (K::M('company/photo')->update($photo_id, $data)) {
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward','?company/photo-company-'.$detail['company_id'].'.html');
                }
            }
        } else {
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->pagedata['type'] = K::M('company/photo')->get_type_means();
            $this->tmpl = 'admin:company/photo/edit.html';
        }
    }

    public function delete($photo_id = null)
    {

		if($photo_id = (int)$photo_id){
            if($photo = K::M('company/photo')->detail($photo_id)){
				if(!$company = K::M('company/company')->detail($photo['company_id'])){
					 $this->err->add('该公司不存在或已经删除', 403);
				}else if(!$this->check_city($company['city_id'])){
					 $this->err->add('不可越权操作', 403);
				}else if(K::M('company/photo')->delete($photo_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('photo_id')){
           if($items = K::M('company/photo')->items_by_ids($ids)){
                $aids  =  array();
                foreach($items as $k => $v){
                    if($company_id = $v['company_id']){
                        break;
                    }
                }
                if(!$company = K::M('company/company')->detail($company_id)){
                    $this->err->add('该公司不存在或已经删除', 403);
                }else if(!$this->check_city($company['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['company_id'] == $company_id){
                            $aids[$val['photo_id']] = $val['photo_id'];
                        }
                    }
                    if($aids && K::M('company/photo')->delete($aids)){
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
