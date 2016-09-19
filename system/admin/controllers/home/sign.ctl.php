<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: sign.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Ctl_Home_Sign extends Ctl {

    public function index($tuan_id,$page = 1)
    {
        $tuan_id = (int) $tuan_id;
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if ($SO = $this->GP('SO')) {
            $pager['SO'] = $SO;
            if ($SO['uid']) {
                $filter['uid'] = $SO['uid'];
            }
            if ($SO['mobile']) {
                $filter['mobile'] = "LIKE:%" . $SO['mobile'] . "%";
            }
            if ($SO['contact']) {
                $filter['contact'] = "LIKE:%" . $SO['contact'] . "%";
            }
        }
        $filter['tuan_id'] = $tuan_id;
        $uids = array();
        
        if ($items = K::M('home/sign')->items($filter, null, $page, $limit, $count)) {
            foreach($items as $k=>$v){
                $uids[] = $v['uid'];
                $items[$k]['clientip'] = $v['clientip'].'('. K::M("misc/location")->location($v['clientip']) .')';
            }
            $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO' => $SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['tuan_id'] = $tuan_id;
        $this->pagedata['tuan'] = K::M('home/tuan')->detail($tuan_id);
        $this->pagedata['package'] = K::M('home/package')->items(array('tuan_id'=>$tuan_id));
        $this->tmpl = 'admin:home/sign/items.html';
    }

    public function so($tuan_id)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$tuan = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['tuan_id'] = $tuan_id;
            $this->pagedata['tuan'] = $tuan;
            
            $this->tmpl = 'admin:home/sign/so.html';
        }
    }

    public function create($tuan_id)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$tuan = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('您要添加的团装小区不存在或已经删除', 212);
        }else if(!$this->check_city($tuan['city_id'])){
            $this->err->add('不可越权操作', 403);
        }elseif ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {
                $data['tuan_id'] = $tuan_id;
                if ($sign_id = K::M('home/sign')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?home/sign-index-'.$tuan_id.'.html');
                }
            }
        }
        else {
            $this->pagedata['package'] = K::M('home/package')->items(array('tuan_id'=>$tuan_id));
            $this->pagedata['tuan_id'] = $tuan_id;
            $this->pagedata['tuan'] = $tuan;
            $this->tmpl = 'admin:home/sign/create.html';
        }
    }
    
    
    public function download($tuan_id){
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$tuan = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else{
            
            $keys = array('ID','团装ID','套餐', 'UID','电话','姓名','IP来源','报名时间');
            $items = K::M('home/sign')->items_by_tuan_id($tuan_id);
            $package = K::M('home/package')->items(array('tuan_id'=>$tuan_id));
            foreach($items as $k=>$v){
                $items[$k]['package_id'] = $package[$v['package_id']]['title'];
            }
            K::M('dataio/xls')->export($keys,$items,$tuan['title']);
           die;
        }
        
    }
    
    public function edit($sign_id = null)
    {
        if (!($sign_id = (int) $sign_id) && !($sign_id = $this->GP('sign_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        }else if (!$detail = K::M('home/sign')->detail($sign_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }elseif(!$tuan = K::M('home/tuan')->detail($detail['tuan_id'])){
			$this->err->add('您要修改的团装小区不存在或已经删除', 213);
		}else if(!$this->check_city($tuan['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if ($this->checksubmit('data')) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }
            else {
				unset($data['tuan_id']);
                if (K::M('home/sign')->update($sign_id, $data)) {
                    $this->err->add('修改内容成功');
                }
            }
        }
        else {
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->pagedata['package'] = K::M('home/package')->items(array('tuan_id'=>$detail['tuan_id']));
            $this->tmpl = 'admin:home/sign/edit.html';
        }
    }

    public function delete($sign_id = null)
    {
		if($sign_id = (int)$sign_id){
            if($sign = K::M('home/sign')->detail($sign_id)){
                if(!$tuan = K::M('home/tuan')->detail($sign['tuan_id'])){
                     $this->err->add('该团装小区不存在或已经删除', 403);
                }else if(!$this->check_city($tuan['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else if(K::M('home/sign')->delete($sign_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('sign_id')){
            if($items = K::M('home/tuan')->items_by_ids($ids)){
                $aids = $tuan =  array();
                foreach($items as $k => $v){
                    if($tuan_id = $v['tuan_id']){
                        break;
                    }
                }
                if(!$tuan = K::M('home/tuan')->detail($tuan_id)){
                    $this->err->add('该团装小区不存在或已经删除', 403);
                }else if(!$this->check_city($tuan['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['tuan_id'] == $tuan_id){
                            $aids[$val['sign_id']] = $val['sign_id'];
                        }
                    }
                    if($aids && K::M('home/sign')->delete($aids)){
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}
