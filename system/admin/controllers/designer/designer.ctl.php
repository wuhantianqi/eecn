<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: designer.ctl.php 3304 2014-02-14 11:01:43Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Designer_Designer extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uname']){$filter['member.uname'] = $SO['uname'];}
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['area_id']){$filter['area_id'] = $SO['area_id'];}
            if($SO['qq']){$filter['qq'] = "LIKE:%".$SO['qq']."%";}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
        }
        $filter['closed'] = 0;
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('designer/designer')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $company_ids= array();
            foreach($items as $k=>$v){
                $company_ids[$v['company_id']] = $v['company_id'];
            }
            $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['city_list'] = K::M("data/city")->fetch_all();
        $this->pagedata['area_list'] = K::M("data/area")->fetch_all();       
        $this->tmpl = 'admin:designer/designer/items.html';
    }

    public function so($target=null, $multi=null)
    {
        if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
        }
        $this->pagedata['pager'] = $pager;  
        $this->tmpl = 'admin:designer/designer/so.html';
    }

    public function dialog($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uname']){$filter['member.uname'] = $SO['uname'];}
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}            
            if($SO['area_id']){$filter['area_id'] = $SO['area_id'];}
            if(!isset($SO['area_id'])){
                if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            }
            if($SO['qq']){$filter['qq'] = "LIKE:%".$SO['qq']."%";}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
        }
        $filter['closed'] = 0;
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('designer/designer')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO, 'multi'=>$multi));
            $company_ids= array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
                if($v['company_id']){
                    $company_ids[$v['company_id']] = $v['company_id'];
                }
            }
            $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            $this->pagedata['city_list'] = K::M("data/city")->fetch_all();
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:designer/designer/dialog.html';      
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$account = $this->GP('account')){
                $this->err->add('非法的数据提交', 201);
            }else{
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if($designer_id = K::M('designer/designer')->create($data, $account)){
					if($designer_id && isset($data['group_id'])){
                        K::M('member/member')->update($designer_id, array('group_id'=>(int)$data['group_id']), true);
                    }
                    if($attr=  $this->GP('attr')){
                        K::M('designer/attr')->update($designer_id,$attr);       
                    }
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?designer/designer-index.html');
                }
            } 
        }else{
            $this->system->config->load('score');
            $this->tmpl = 'admin:designer/designer/create.html';
        }
    }

    public function edit($uid=null)
    {
        if(!($uid = (int)$uid) && !($uid = $this->GP('uid'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('designer/designer')->detail($uid)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('designer/designer')->update($uid, $data)){
                    if(isset($data['group_id'])){
                        K::M('member/member')->update($uid, array('group_id'=>(int)$data['group_id']), true);
                    }                    
                    if($attr=  $this->GP('attr')){
                        K::M('designer/attr')->update($uid,$attr);       
                    }
                    $this->err->add('修改内容成功');
                }  
            }
        }else{
            $this->pagedata['attr'] = K::M('designer/attr')->attrs_ids_by_designer($uid);
            $this->pagedata['detail'] = $detail;
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            if($designer_id = $detail['designer_id']){
                $this->pagedata['designer'] = K::M('designer/designer')->detail($designer_id);
            }
            $this->pagedata['member'] = K::M('member/member')->detail($detail['uid']);
            $this->tmpl = 'admin:designer/designer/edit.html';
        }
    }

    public function delete($uid=null)
    {
        if($uid = (int)$uid){
            if(K::M('designer/designer')->delete($uid)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('uid')){
            if(K::M('designer/designer')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function doaudit($uid=null)
    {
        if($uid = (int)$uid){
            if(K::M('designer/designer')->batch($uid, array('audit'=>1))){
                $this->err->add('审核商铺成功');
            }
        }else if($uids = $this->GP('uid')){
            if(K::M('designer/designer')->batch($uids, array('audit'=>1))){
                $this->err->add('批量审核成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

}