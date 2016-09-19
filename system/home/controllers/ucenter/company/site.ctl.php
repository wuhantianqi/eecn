<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company_Site extends Ctl_Ucenter 
{
    protected $_allow_fields = 'area_id,home_id,case_id,house_mj,price,title,addr,intro,lng,lat';
    
    public function index($page=1)
    {
        $company = $this->ucenter_company();
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['company_id'] = $this->company['company_id'];
        if ($items = K::M('home/site')->items($filter, null, $page, $limit, $count)) {
            $home_ids = array();
            foreach ($items as $k => $v) {
                if ($v['home_id']) {
                    $home_ids[$v['home_id']] = $v['home_id'];
                }
            }
            if (!empty($home_ids)) {
                $this->pagedata['home_list'] = K::M('home/home')->items_by_ids($home_ids);
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}'), array(), true), array());
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->tmpl = 'ucenter/company/site/items.html';
    }

    public function create()
    {
        $company = $this->ucenter_company();
        $allow_site = K::M('member/group')->check_priv($company['group_id'], 'site');
        if(K::M('member/integral')->check('site',  $this->MEMBER) === false){
            $this->err->add('很抱歉您的账户余额不足！', 201);
        }else if($data = $this->checksubmit('data')) {
            if($allow_site < 0){
                $this->err->add('您是【'.$company['group_name'].'】没有权限添加在建工地', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['thumb']){
                    if ($attach['error'] == UPLOAD_ERR_OK) {
                        if ($a = K::M('magic/upload')->upload($attach, 'home')) {
                            $data['thumb'] = $a['photo'];
                            $size['photo'] = $cfg['site']['photo'] ? $cfg['site']['photo'] : 200;
                            K::M('image/gd')->thumbs($a['file'], array($size['photo'] => $a['file']));
                        }
                    }                    
                }
                if($case_id = (int)$data['case_id']){
                    unset($data['case_id']);
                    if($case = K::M('case/case')->detail($case_id)){
                        if($case['uid'] == $company['uid'] || $case['company_id'] == $company['company_id']){
                            $data['case_id'] = $case_id;
                        }
                    }
                }else{
                    $case_id = (int)$data['case_id'];
                }
                if($home_id = (int)$data['home_id']){
                    unset($data['home_id']);
                    if($home = K::M('home/home')->detail($home_id)){
                        if($home['city_id'] == $company['city_id']){
                            $data['home_id'] = $home_id;
                        }
                    }
                }else{
                    unset($data['home_id']);
                }
                $data['uid'] = $company['uid'];
                $data['company_id'] = $company['company_id'];
                $data['city_id'] = $company['city_id'];
                $data['audit'] = $allow_site;
				$data['lat'] = trim($data['lat']);
                if ($site_id = K::M('home/site')->create($data)) {
                     if ($company_id = (int) $data['company_id']) {
                        K::M('company/company')->update_count($company_id, 'site_num', 1);
                    }
                    if ($home_id = (int) $data['home_id']) {
                        K::M('home/home')->update_count($home_id, 'site_num', 1);
                    }                    
                    K::M('member/integral')->commit('site', $this->MEMBER, '发布工地');
                    $this->err->add('添加工地成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/company/site:index'));
                }               
            }
        } else {
            $this->tmpl = 'ucenter/company/site/create.html';
        }        
    }

    public function edit($site_id=null)
    {
        $company = $this->ucenter_company();
        if(!($site_id = (int)$site_id) && !($site_id = (int)$this->GP('site_id'))){
            $this->error(404);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('工地不存在或已经删除', 211);
        }else if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['thumb']){
                    if ($attach['error'] == UPLOAD_ERR_OK) {
                        if ($a = K::M('magic/upload')->upload($attach, 'home')) {
                            $data['thumb'] = $a['photo'];
                            $size['photo'] = $cfg['site']['photo'] ? $cfg['site']['photo'] : 200;
                            K::M('image/gd')->thumbs($a['file'], array($size['photo'] => $a['file']));
                        }
                    }                    
                }
                if($case_id = (int)$data['case_id']){
                    unset($data['case_id']);
                    if($case = K::M('case/case')->detail($case_id)){
                        if($case['uid'] == $company['uid'] || $case['company_id'] == $company['company_id']){
                            $data['case_id'] = $case_id;
                        }
                    }
                }else{
                    $case_id = (int)$data['case_id'];
                }
                if($home_id = (int)$data['home_id']){
                    unset($data['home_id']);
                    if($home = K::M('home/home')->detail($home_id)){
                        if($home['city_id'] == $company['city_id']){
                            $data['home_id'] = $home_id;
                        }
                    }
                }else{
                    unset($data['home_id']);
                }
				$data['lat'] = trim($data['lat']);
                if ($site_id = K::M('home/site')->update($site_id, $data)) {
                    if($detail['home_id'] != $data['home_id']){
                        if($home_id = (int)$detail['home_id']){
                             K::M('home/home')->update_count($home_id, 'site_num', -1);
                        }
                        if($home_id = (int) $data['home_id']){
                            K::M('home/home')->update_count($home_id, 'site_num', 1);
                        }                        
                    }                    
                    $this->err->add('修改工地成功');
                }                
            }
        }else{
            if ($home_id = (int) $detail['home_id']) {
                $this->pagedata['home'] = K::M('home/home')->detail($home_id);
            }
            if($case_id = (int)$detail['case_id']){
                $this->pagedata['case'] = K::M('case/case')->detail($case_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/company/site/edit.html';            
        }
    }

    public function delete($site_id=null)
    {
        $company = $this->ucenter_company();
        if(!($site_id = (int)$site_id) && !($site_id = $this->GP('site_id'))){
            $this->err->add('未指要删除的在建工地', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('你要删除的工地不存或已经删除', 212);
        }else if($company['company_id'] != $detail['company_id']){
            $this->err->add('您没有权限删除该工地', 213);
        }else{
            K::M('home/site')->delete($site_id);
            $this->err->add('删除在建工地成功');
        }
    }

    public function detail($site_id=null)
    {
        $company = $this->ucenter_company();
        if(!$site_id = (int)$site_id){
            $this->error(404);
        }else if(!$site = K::M('home/site')->detail($site_id)){
            $this->err->add('工地不存在或已经删除', 211);
        }else{
            if($items = K::M('home/items')->items(array('site_id'=>$site_id))){
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['site'] = $site;
            $this->pagedata['status_list'] = K::M('home/site')->get_status();
            $this->tmpl = 'ucenter/company/site/diary/site.html';
        }        
    }

}