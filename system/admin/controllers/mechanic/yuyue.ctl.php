<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mechanic_Yuyue extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['yuyue_id']){$filter['yuyue_id'] = $SO['yuyue_id'];}
            if($SO['mechanic_id']){$filter['mechanic_id'] = $SO['mechanic_id'];}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('mechanic/yuyue')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $uids = array();
            foreach($items as $k=>$v){
                $uids[$v['mechanic_id']] = $v['mechanic_id'];
                if($v['uid']){
                    $uids[$v['uid']] = $v['uid'];
                }
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $this->pagedata['items'] = $items;
        }
        
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:mechanic/yuyue/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:mechanic/yuyue/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 211);
            }else if($mechanic_id = (int)$data['mechanic_id']){
                $this->err->add('未指定要预约的工人', 212);
            }else if(!$mechanic = K::M('mechanic/mechanic')->detail($mechanic_id)){
                $this->err->add('预约的工人不存在或已经删除', 212);
            }else if(!$this->check_city($mechanic['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
                $data['city_id'] = $mechanic['city_id'];
                if($yuyue_id = K::M('mechanic/yuyue')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?mechanic/yuyue-index.html');
                }
            } 
        }else{
            $this->tmpl = 'admin:mechanic/yuyue/create.html';
        }
    }

    public function edit($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('mechanic/yuyue')->detail($yuyue_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('mechanic/yuyue')->update($yuyue_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['mechanic'] = K::M('member/member')->member($detail['mechanic_id']);
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:mechanic/yuyue/edit.html';
        }
    }

    public function detail($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$detail = K::M('mechanic/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['mechanic'] = K::M('member/member')->member($detail['mechanic_id']);
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:mechanic/yuyue/detail.html';
        }        
    }

    public function delete($yuyue_id=null)
    {
        if($yuyue_id = (int)$yuyue_id){
            if(K::M('mechanic/yuyue')->delete($yuyue_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('yuyue_id')){
            if(K::M('mechanic/yuyue')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}