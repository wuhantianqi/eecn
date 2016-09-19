<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: sign.ctl.php 5681 2014-06-26 11:33:16Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Company_Sign extends Ctl
{

    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if($SO['clientip']){$filter['clientip'] = "LIKE:%".$SO['clientip']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
        }
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('company/sign')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($youhui_id,'{page}')), array('SO'=>$SO));
            $this->pagedata['items'] = $items;
            $uids = $youhui_ids = array();
            foreach($items as $k=>$v){
                $youhui_ids[$v['youhui_id']] = $v['youhui_id'];
                if($v['uid']){
                    $uids[] = $v['uid'];
                }
            }
            if($youhui_ids){
                $this->pagedata['youhui_list'] = K::M('company/youhui')->items_by_ids($youhui_ids);
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        }
		$this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:company/sign/items.html';       
    }
    
    public function youhui($youhui_id,$page=1)
    {
        if(empty($youhui_id)){
             $this->err->add('未指定优惠信息的ID', 211);
        }elseif(!$youhui = K::M('company/youhui')->detail($youhui_id)){
             $this->err->add('优惠信息不存在或已经删除', 212);
        }elseif(!$this->check_city($youhui['city_id'])){
             $this->err->add('不可越权操作', 212);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            $filter['youhui_id'] = $youhui_id;
			if(CITY_ID){
				$filter['city_id'] = CITY_ID;
			}
            if($items = K::M('company/sign')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($youhui_id,'{page}')), array('SO'=>$SO));
                $this->pagedata['items'] = $items;
                $uids = array();
                foreach($items as $k=>$v){
                    $uids[] = $v['uid'];
                }
                if($uids){
                    $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
                }
            }
			$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
            $this->pagedata['pager'] = $pager;
            $this->pagedata['youhui'] =  $youhui;
            $this->pagedata['youhui_id'] = $youhui_id;
            $this->tmpl = 'admin:company/sign/youhui.html';
        }
    }

    public function so()
    {   
       
        $this->tmpl = 'admin:company/sign/so.html';
      
    }

    public function create($youhui_id)
    {   
        if(empty($youhui_id)){
             $this->err->add('未指定要修改的内容ID', 211);
        }elseif(!$youhui = K::M('company/youhui')->detail($youhui_id)){
             $this->err->add('您要修改的内容不存在或已经删除', 212);
        }elseif(!$this->check_city($youhui['city_id'])){
             $this->err->add('不可越权操作', 212);
        }else{
            if($this->checksubmit()){
                if(!$data = $this->GP('data')){
                    $this->err->add('非法的数据提交', 201);
                }else{
                    $data['company_id'] = $youhui['company_id'];
                    $data['youhui_id'] = $youhui_id;
                    $data['dateline'] = __TIME;
                    $data['clientip'] = __IP;
					if(CITY_ID){
						$data['city_id'] = CITY_ID;
					}
                    if($sign_id = K::M('company/sign')->create($data)){
						K::M('company/sign')->youhui_count($youhui['youhui_id']);
                        $this->err->add('添加内容成功');
                        $this->err->set_data('forward', '?company/sign-youhui-'.$youhui_id.'.html');
                    }
                } 
            }else{
               $this->pagedata['youhui_id'] = $youhui_id;
               $this->tmpl = 'admin:company/sign/create.html';
            }
        }
    }

    public function edit($sign_id=null)
    {
        if(!($sign_id = (int)$sign_id) && !($sign_id = $this->GP('sign_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('company/sign')->detail($sign_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				unset($data['youhui_id'], $data['city_id']);
                if(K::M('company/sign')->update($sign_id, $data)){
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?company/sign-youhui-'.$detail['youhui_id'].'.html');
                }  
            } 
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:company/sign/edit.html';
        }
    }

    public function detail($sign_id=null)
    {
        if(!($sign_id = (int)$sign_id) && !($sign_id = $this->GP('sign_id'))){
            $this->err->add('未指定要查的内容ID', 211);
        }else if(!$detail = K::M('company/sign')->detail($sign_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['youhui'] = K::M('company/youhui')->detail($detail['youhui_id']);
            $this->pagedata['company'] = K::M('company/company')->detail($detail['company_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:company/sign/detail.html';
        }        
    }

    public function delete($sign_id=null)
    {

		if($sign_id = (int)$sign_id){
            if($sign = K::M('company/sign')->detail($sign_id)){
                if(!$this->check_city($sign['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(!$youhui = K::M('company/youhui')->detail($sign['youhui_id'])){
					$this->err->add('该优惠活动不存在', 403);
				}else if(K::M('company/sign')->delete($sign_id)){
                    K::M('company/sign')->youhui_count($youhui['youhui_id']);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('sign_id')){
            if($sign = K::M('company/sign')->items_by_ids($ids)){
                $aids = $youhui_ids = array();
                foreach($sign as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['sign_id']] = $v['sign_id'];
                    $youhui_ids[$v['youhui_id']] = $v['youhui_id'];
                }
                if($aids && K::M('company/sign')->delete($aids)){
                    K::M('company/sign')->youhui_count($youhui_ids);
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }


		
    }

}