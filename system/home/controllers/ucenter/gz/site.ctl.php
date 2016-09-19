<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Gz_Site extends Ctl_Ucenter 
{
    protected $_allow_fields = 'area_id,home_id,case_id,house_mj,price,title,addr,intro,lng,lat';
    
    public function index($page=1)
    {
        $gz = $this->ucenter_gz();
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['uid'] = $gz['uid'];
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
        $this->tmpl = 'ucenter/gz/site/items.html';
    }

    public function create()
    {
        $gz = $this->ucenter_gz();
        if(K::M('system/integral')->check('site',  $this->MEMBER) === false){
            $this->err->add('很抱歉您的账户余额不足！', 201);
        }else if($data = $this->checksubmit('data')) {
			$allow_site = K::M('member/group')->check_priv($gz['group_id'], 'allow_site');
            if($allow_site < 0){
                $this->err->add('您是【'.$gz['group_name'].'】没有权限添加在建工地', 333);
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
                $data['uid'] = $gz['uid'];
                $data['city_id'] = $gz['city_id'];
                $data['audit'] = $allow_site;
				$data['lat'] = trim($data['lat']);
                if ($site_id = K::M('home/site')->create($data)) {
                    if ($uid = (int) $data['uid']) {
                        K::M('gz/gz')->update_count($uid, 'site_num', 1);
                        K::M('gz/gz')->update($uid, array('lasttime'=>__TIME));
                    }
                    if ($home_id = (int) $data['home_id']) {
                        K::M('home/home')->update_count($home_id, 'site_num', 1);
                    }                    
                    K::M('system/integral')->commit('site',  $this->MEMBER, '发布工地');
                    $this->err->add('添加工地成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/gz/site:index'));
                }                
            }
        } else {
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/gz/site/create.html';
        }        
    }

    public function edit($site_id=null)
    {
        $gz = $this->ucenter_gz();
        if(!($site_id = (int)$site_id) && !($site_id = (int)$this->GP('site_id'))){
            $this->error(404);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('工地不存在或已经删除', 211);
        }else if($data = $this->checksubmit('data')){
            $allow_site = K::M('member/group')->check_priv($gz['group_id'], 'allow_site');
            if($allow_site < 0){
                $this->err->add('您是【'.$gz['group_name'].'】没有权限修改在建工地', 333);
            }elseif(!$data = $this->check_fields($data, $this->_allow_fields)){
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
                if ($site_id = K::M('home/site')->update($site_id, $data)) {
                    if($detail['home_id'] != $data['home_id']){
                        if($home_id = (int) $data['home_id']){
                            K::M('home/home')->update_count($home_id, 'site_num', 1);
                        }
                        if($home_id = (int)$detail['home_id']){
                             K::M('home/home')->update_count($home_id, 'site_num', -1);
                        }
                    }                    
                    $this->err->add('修改工地成功');
                }                
            }
        }else{
            if ($home_id = (int) $detail['home_id']) {
                $this->pagedata['home'] = K::M('home/home')->detail($home_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/gz/site/edit.html';            
        }
    }

    public function delete($site_id=null)
    {
        $gz = $this->ucenter_gz();
        if(!($site_id = (int)$site_id) && !($site_id = $this->GP('site_id'))){
            $this->err->add('未指要删除的在建工地', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('你要删除的工地不存或已经删除', 212);
        }else if($gz['uid'] != $detail['uid']){
            $this->err->add('您没有权限删除该工地', 213);
        }else{
            K::M('home/site')->delete($site_id);
			if ($uid = (int) $detail['uid']) {
				K::M('gz/gz')->update_count($uid, 'site_num', -1);
				K::M('gz/gz')->update($uid, array('lasttime'=>__TIME));
			}
			if ($home_id = (int) $detail['home_id']) {
				K::M('home/home')->update_count($home_id, 'site_num', -1);
			}
            $this->err->add('删除在建工地成功');
        }
    }

}