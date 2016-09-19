<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Activity_Sign extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['sign_id']){$filter['sign_id'] = $SO['sign_id'];}
            if($SO['activity_id']){$filter['activity_id'] = $SO['activity_id'];}
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('activity/sign')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $activity_ids = $uids = array();
            foreach($items as $k=>$v){
                $activity_ids[$v['activity_id']] = $v['activity_id'];
                if($v['uid']){
                    $uids[$v['uid']] = $v['uid'];
                }
            }
            if($activity_ids){
                $this->pagedata['activity_list'] = K::M('activity/activity')->items_by_ids($activity_ids);
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:activity/sign/items.html';
    }

    public function so($activity_id=null)
    {
        $pager['activity_id'] = (int)$activity_id;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:activity/sign/so.html';
    }

    public function activity($activity_id=null, $page=1)
    {
        if(!($activity_id = (int)$activity_id) && !($activity_id = (int)$this->GP('activity_id'))){
            $this->err->add('未指定活动ID', 211);
        }else if(!$activity = K::M('activity/activity')->detail($activity_id)){
            $this->err->add('活动不存在或已经删除', 212);
        }else{
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 50;
            $filter['activity_id'] = $activity_id;
            if($SO = $this->GP('SO')){
                $pager['SO'] = $SO;
                if($SO['sign_id']){$filter['sign_id'] = $SO['sign_id'];}
                if($SO['uid']){$filter['uid'] = $SO['uid'];}
                if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
                if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
			if(CITY_ID){
				$filter['city_id'] = CITY_ID;
			}
            if($items = K::M('activity/sign')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));                
                $this->pagedata['items'] = $items;
                $uids = array();
                foreach($items as $k=>$v){
                    if($v['uid']){
                        $uids[$v['uid']] = $v['uid'];
                    }
                }                
                if($uids){
                    $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
                }                
            }
            $this->pagedata['activity'] = $activity;
            $this->tmpl = 'admin:activity/sign/activity.html';
        }         
    }

    public function download($activity_id=null)
    {
        if(!($activity_id = (int)$activity_id) && !($activity_id = (int)$this->GP('activity_id'))){
            $this->err->add('未指定活动ID', 211);
        }else if(!$activity = K::M('activity/activity')->detail($activity_id)){
            $this->err->add('活动不存在或已经删除', 212);
        }else if(!$this->check_city($activity['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else{
            //限制最多下载5000条
            if($items = K::M('activity/sign')->items_by_activity($activity_id, 1, 5000)){
                $uids = $auids = array();
                $oLocation = K::M('misc/location');
                foreach($items as $k=>$v){
                    unset($v['activity_id']);
                    if($v['uid']){
                        $uids[$v['uid']] = $v['uid'];
                        $auids[$v['uid']] = $k;
                    }else{
                        $v['uid'] = '访客(0)';
                    }
                    $v['clientip'] = $v['clientip'].'('.$oLocation->location($v['clientip']).')';
                    $v['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
                    $items[$k] = $v;
                }
                if($uids){
                    if($member_list = K::M('member/member')->items_by_ids($uids)){
                        foreach($member_list as $v){
                            if($id = $auids[$v['uid']]){
                                if($items[$id]){
                                    $items[$k]['uid'] = $v['uname']."({$v['uid']})";
                                }
                            }
                        }
                    }
                }
            }
            $keys = array('ID','会员','联系人','电话','EMAIL','QQ','地址','参加人数','备注','IP来源','报名时间');
            K::M('dataio/xls')->export($keys, $items, $activity['title'].'报名表格');
        }        
    }

    public function create($activity_id=null)
    {
        if(!($activity_id = (int)$activity_id) && !($activity_id = (int)$this->GP('activity_id'))){
            $this->err->add('未指定活动ID', 211);
        }else if(!$activity = K::M('activity/activity')->detail($activity_id)){
            $this->err->add('活动不存在或已经删除', 212);
        }else if(!$this->check_city($activity['city_id'])){
                $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['activity_id'] = $activity_id;
                $data['city_id'] = $activity['city_id'];
                if($sign_id = K::M('activity/sign')->create($data)){
					K::M('activity/activity')->update($activity_id, array('sign_num'=>$activity['sign_num']+1));
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?activity/sign-activity-'.$activity_id.'.html');
                }
            } 
        }else{
            $this->pagedata['activity'] = $activity;
            $this->tmpl = 'admin:activity/sign/create.html';
        }
    }

    public function edit($sign_id=null)
    {
        if(!($sign_id = (int)$sign_id) && !($sign_id = $this->GP('sign_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('activity/sign')->detail($sign_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				unset($data['sign_id'],$data['city_id']);
				if(K::M('activity/sign')->update($sign_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            if($activity_id = $detail['activity_id']){
                $this->pagedata['activity'] = K::M('activity/activity')->detail($activity_id);;
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:activity/sign/edit.html';
        }
    }



    public function delete($sign_id=null)
    {
		if($sign_id = (int)$sign_id){
            if($sign = K::M('activity/sign')->detail($sign_id)){
                if(!$this->check_city($sign['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('activity/sign')->delete($sign_id)){
					//K::M('activity/sign')->sign_count($sign['activity_id']);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('sign_id')){
            if($items = K::M('activity/sign')->items_by_ids($ids)){
                $aids = $activity_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['sign_id']] = $v['sign_id'];
                    $activity_ids[$v['activity_id']] = $v['activity_id'];
                }
                if($aids && K::M('activity/sign')->delete($aids)){
					//K::M('activity/sign')->sign_count($activity_ids);
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}