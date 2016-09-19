<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: banner.ctl.php 2034 2013-12-07 03:08:33Z $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Company_Banner extends Ctl 
{

    public function company($company_id,$page = 1) 
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
			if ($SO = $this->GP('SO')) {
				$pager['SO'] = $SO;
				if ($SO['company_id']) {
					$filter['company_id'] = $SO['company_id'];
				}
				if ($SO['title']) {
					$filter['title'] = "LIKE:%" . $SO['title'] . "%";
				}
			}
			$companyIds = array();
			$filter = array('company_id'=>$company_id);
			if ($items = K::M('company/banner')->items($filter, null, $page, $limit, $count)) {
				 foreach($items as $k=>$v){
				   if(!empty($v['company_id']))  $companyIds[$v['company_id']] = $v['company_id'];
				} 
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
			}
			$this->pagedata['company_list'] = K::M('company/company')->items_by_ids($companyIds);
			$this->pagedata['items'] = $items;
			$this->pagedata['pager'] = $pager;
			$this->pagedata['company_id'] = $company_id;
			$this->tmpl = 'admin:company/banner/company.html';
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
				
                if ($banner_id = K::M('company/banner')->create($data)) {
                    $this->err->add('添加内容成功');
					$this->err->set_data('forward','?company/banner-company-'.$company_id.'.html');
                }
            }
        } else {
			$this->pagedata['company_id'] = $company_id;
            $this->tmpl = 'admin:company/banner/create.html';
        }
    }

    public function edit($banner_id = null)
    {
        if (!($banner_id = (int) $banner_id) && !($banner_id = $this->GP('banner_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('company/banner')->detail($banner_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if (!$company = K::M('company/company')->detail($detail['company_id'])) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if(!$this->check_city($company['city_id'])){
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
                unset($data['company_id'],$data['banner_id']);
                if (K::M('company/banner')->update($banner_id, $data)) {
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward','?company/banner-company-'.$detail['company_id'].'.html');
                }
            }
        } else {
            if ($company_id = $detail['company_id']) {
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:company/banner/edit.html';
        }
    }

    public function delete($banner_id = null)
	{
		if($banner_id = (int)$banner_id){
            if($banner = K::M('company/banner')->detail($banner_id)){
				if(!$company = K::M('company/company')->detail($banner['company_id'])){
					 $this->err->add('该公司不存在或已经删除', 403);
				}else if(!$this->check_city($company['city_id'])){
					 $this->err->add('不可越权操作', 403);
				}else if(K::M('company/banner')->delete($banner_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('banner_id')){
            if($items = K::M('company/banner')->items_by_ids($ids)){
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
                            $aids[$val['banner_id']] = $val['banner_id'];
                        }
                    }
                    if($aids && K::M('company/banner')->delete($aids)){
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}
