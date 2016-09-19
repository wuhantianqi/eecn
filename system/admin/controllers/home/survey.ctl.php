<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: survey.ctl.php 2335 2013-12-18 17:15:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_Survey extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if ($SO['area_id']) {
                $filter['area_id'] = $SO['area_id'];
            }
            if ($SO['home_name']) {
                $filter['home_name'] = "LIKE:%" . $SO['home_name'] . "%";
            }
            if ($SO['name']) {
                $filter['name'] = "LIKE:%" . $SO['name'] . "%";
            }
            if ($SO['tel']) {
                $filter['tel'] = "LIKE:%" . $SO['tel'] . "%";
            }
        }
        if($items = K::M('home/survey')->items($filter, array('survey_id'=>'DESC'), $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->tmpl = 'admin:home/survey/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:home/survey/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($survey_id = K::M('home/survey')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?home/survey-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:home/survey/create.html';
        }
    }

    public function edit($survey_id=null)
    {
        if(!($survey_id = (int)$survey_id) && !($survey_id = $this->GP('survey_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/survey')->detail($survey_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('home/survey')->update($survey_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:home/survey/edit.html';
        }
    }



    public function delete($survey_id=null)
    {
        if($survey_id = (int)$survey_id){
            if(K::M('home/survey')->delete($survey_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('survey_id')){
            if(K::M('home/survey')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}