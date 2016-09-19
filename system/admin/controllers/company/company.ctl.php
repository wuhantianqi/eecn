<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: company.ctl.php 3419 2014-02-21 09:42:53Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Company_Company extends Ctl
{

    public function index($page = 1) 
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['company_id']) {
                $filter['company_id'] = $SO['company_id'];
            }
            if ($SO['uid']) {
                $filter['uid'] = $SO['uid'];
            }
            if ($SO['city_id']) {
                $filter['city_id'] = $SO['city_id'];
            }
            if ($SO['area_id']) {
                $filter['area_id'] = $SO['area_id'];
            }
            if ($SO['name']) {
                $filter['name'] = "LIKE:%" . $SO['name'] . "%";
            }
            if ($SO['sort_name']) {
                $filter['sort_name'] = "LIKE:%" . $SO['sort_name'] . "%";
            }
            if ($SO['contact']) {
                $filter['contact'] = "LIKE:%" . $SO['contact'] . "%";
            }
            if ($SO['tel']) {
                $filter['tel'] = "LIKE:%" . $SO['tel'] . "%";
            }
            if (is_numeric($SO['audit'])) {
                $filter['audit'] = $SO['audit'];
            }
        }
        $uids = array();
        $filter['closed'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if ($items = K::M('company/company')->items($filter, null, $page, $limit, $count)) {
            foreach($items as $k=>$v){
                $uids[] = $v['uid'];
                 $items[$k]['clientip'] = $v['clientip'].'('. K::M("misc/location")->location($v['clientip']) .')';
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
               
        }
		
        $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->tmpl = 'admin:company/company/items.html';
    }

    
    public function audit($page = 1) 
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
    
        $uids = array();
        $filter['closed'] = 0;
        $filter['audit'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if ($items = K::M('company/company')->items($filter, null, $page, $limit, $count)) {
            foreach($items as $k=>$v){
                $uids[] = $v['uid'];
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
        }
        $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->tmpl = 'admin:company/company/audit.html';
    }
    
    
    
    public function so($target=null, $multi=null)
    {
        if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
        }
        $this->pagedata['pager'] = $pager;          
        $this->tmpl = 'admin:company/company/so.html';
    }

    public function dialog($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if ($SO['company_id']) {$filter['company_id'] = $SO['company_id'];}
            if ($SO['uid']) {$filter['uid'] = $SO['uid'];}
            if ($SO['area_id']) {$filter['area_id'] = $SO['area_id'];}
            if(!isset($filter['area_id'])){
                if ($SO['city_id']) {$filter['city_id'] = $SO['city_id'];}
            }
            if ($SO['name']) {$filter['name'] = "LIKE:%" . $SO['name'] . "%";}
            if ($SO['tel']) {$filter['tel'] = "LIKE:%" . $SO['tel'] . "%";}
            if (is_numeric($SO['audit'])) {$filter['audit'] = $SO['audit'];}
        }
        $filter['closed'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('company/company')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO, 'multi'=>$multi));
            $uids = array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
            }
            $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();        
        $this->tmpl = 'admin:company/company/dialog.html';            
    }

    public function create()
    {
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
                    $cfg = K::$system->config->get('attach');
                    $oImg = K::M('image/gd');
                    $upload = K::M('magic/upload');
                    foreach ($attachs as $k => $attach) {
                        if ($attach['error'] == UPLOAD_ERR_OK) {
                            if ($a = $upload->upload($attach, 'company')) {
                                $data[$k] = $a['photo'];
                                if ($k === 'logo') {
                                    $size['photo'] = $cfg['companydecorate1'] ? $cfg['companydecorate1'] : '200X100';
                                } else {
                                    $size['photo'] = $cfg['companydecorate2'] ? $cfg['companydecorate2'] : '300X300';
                                }
                                $oImg->thumbs($a['file'], array($size['photo'] => $a['file']), false);
                            }
                        }
                    }
                }
                if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                $data['lat'] = trim($data['lat']);
                if ($company_id = K::M('company/company')->create($data)) {
                    if($data['uid'] && isset($data['group_id'])){
                        K::M('member/member')->update($data['uid'], array('group_id'=>(int)$data['group_id']), true);
                    }
                    if($attr=  $this->GP('attr')){
                        K::M('company/attr')->update($company_id,$attr);       
                    }
                    if($fields = $this->GP('fields')){
                         K::M('company/fields')->update($company_id, $fields);   
                    }                    
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?company/company-index.html');
                }
            }
        } else {
			$skins = include(__CFG::TMPL_DIR.'default/company/config.php');
			$this->pagedata['skins'] = $skins;
            $this->tmpl = 'admin:company/company/create.html';
        }
    }

    public function edit($company_id = null) 
    {
        if (!($company_id = (int) $company_id) && !($company_id = $this->GP('company_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('company/company')->detail($company_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
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
                    $cfg = K::$system->config->get('attach');
                    $oImg = K::M('image/gd');
                    $upload = K::M('magic/upload');
                    foreach ($attachs as $k => $attach) {
                        if ($attach['error'] == UPLOAD_ERR_OK) {
                            if ($a = $upload->upload($attach, 'company')) {
                                $data[$k] = $a['photo'];
                                if ($k === 'logo') {
                                    $size['photo'] = $cfg['companydecorate1'] ? $cfg['companydecorate1'] : '200X100';
                                } else {
                                    $size['photo'] = $cfg['companydecorate2'] ? $cfg['companydecorate2'] : '300X300';
                                }
                                $oImg->thumbs($a['file'], array($size['photo'] => $a['file']));
                            }
                        }
                    }                
                }
                unset($data['city_id'],$data['company_id']);
                if (K::M('company/company')->update($company_id, $data)) {
                    if($data['uid'] && isset($data['group_id'])){
                        K::M('member/member')->update($data['uid'], array('group_id'=>(int)$data['group_id']), true);
                    }
                    if($attr =  $this->GP('attr')){
                        K::M('company/attr')->update($company_id,$attr);       
                    }
                    if($fields = $this->GP('fields')){                     
                         K::M('company/fields')->update($company_id,$fields);     
                    }
                    $this->err->add('修改内容成功');
                    $this->err->set_data('forward', '?company/company-index.html');
                }

                
            }
        } else {
			$skins = include(__CFG::TMPL_DIR.'default/company/config.php');
			$this->pagedata['skins'] = $skins;
            $this->pagedata['attr'] = K::M('company/attr')->attrs_ids_by_company($company_id);
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            $this->pagedata['detailfields'] = K::M('company/fields')->detail($company_id);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:company/company/edit.html';
        }
    }
    
    
    public function auditup($company_id = null)
    {
        if ($company_id = (int) $company_id) {
            if (K::M('company/company')->update($company_id,array('audit'=>1))) {
                $this->err->add('审核成功');
            }
        } else if ($ids = $this->GP('company_id')) {
            if (K::M('company/company')->batch($ids,array('audit'=>1))) {
                $this->err->add('审核成功');
            }
        } else {
            $this->err->add('未指定要审核的ID', 401);
        }
    }
    
    public function vip($company_id = null){
         if ($company_id = (int) $company_id) {
             if($detail = K::M('company/company')->detail($company_id)){
                $data['is_vip'] = empty($detail['is_vip']) ? 1 : 0;
                if (K::M('company/company')->update($company_id,$data)) {
                    $this->err->add('设置成功');
                }else{
                     $this->err->add('设置失败');
                }
             }else{
                  $this->err->add('未指定要设为旗舰的ID', 401);
             }             
         }else{
              $this->err->add('未指定要设为旗舰的ID', 401);
         }  
    }
    
    
    public function delete($company_id = null)
    { 
        if($company_id = (int)$company_id){
            if($company = K::M('company/company')->detail($company_id)){
                if(!$this->check_city($company['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('company/company')->delete($company_id)){
                    $this->err->add('删除装修公司成功');
                }
            }
        }else if($ids = $this->GP('company_id')){
            if($items = K::M('company/company')->items_by_ids($ids)){
                $aids  = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['company_id']] = $v['company_id'];
                }
                if($aids && K::M('company/company')->delete($aids)){
                    $this->err->add('批量删除装修公司成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}