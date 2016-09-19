<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: ex.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Activity_Ex extends Ctl
{
    
    public function index($activityId,$page=1)
    {
       
    	$filter = $pager = array();
        
        if(empty($activityId))  $this->err->add('非法的数据提交', 201)->show();
        $activity = K::M('activity/main')->detail($activityId);
        if(empty($activity)) $this->err->add('非法的数据提交', 201)->show('?activity/main.html');
        
        //搜索的时候必备
        $filter['activity_id'] = $activityId;
        
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;

        if($items = K::M('activity/ex')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['activityid'] = $activityId;
        $this->pagedata['activity'] = $activity;
        $this->tmpl = 'admin:activity/ex/items.html';
    }





    public function create($activityId)
    {
                
        if(empty($activityId))  $this->err->add('非法的数据提交', 201)->show();
        $activity = K::M('activity/main')->detail($activityId);
        if(empty($activity)) $this->err->add('非法的数据提交', 201)->show('?activity/main.html');
        
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{ 
                $data['activity_id']     = $activityId;
                if($ex_id = K::M('activity/ex')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?activity/ex-index-'.$activityId.'.html');
                }
            } 
        }else{
           $this->pagedata['activityid'] = $activityId;
           $this->pagedata['activity'] = $activity;
           $this->tmpl = 'admin:activity/ex/create.html';
        }
    }

    public function edit($ex_id=null)
    {
        if(!($ex_id = (int)$ex_id) && !($ex_id = $this->GP('ex_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('activity/ex')->detail($ex_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('activity/ex')->update($ex_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:activity/ex/edit.html';
        }
    }

    public function delete($ex_id=null)
    {
        if($ex_id = (int)$ex_id){
            if(K::M('activity/ex')->delete($ex_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('ex_id')){
            if(K::M('activity/ex')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}