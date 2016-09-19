<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tuan.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_Tuan extends Ctl
{
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
			if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
			if($SO['home_id']){$filter['home_id'] = $SO['home_id'];}
			if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
        }
        if($items = K::M('home/tuan')->items($filter, null, $page, $limit, $count)){
            $home_ids = $company_ids  = array();
            foreach($items as $k=>$v){
                if($v['home_id']){
                    $home_ids[$v['home_id']] = $v['home_id'];        
                }
                if($v['company_id']){
                    $company_ids[$v['company_id']] = $v['company_id'];
                }
            }
            if(!empty($home_ids)){
                $this->pagedata['home_list'] = K::M('home/home')->items_by_ids($home_ids);
            }              
            if(!empty($company_ids)){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            }  
            
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->tmpl = 'admin:home/tuan/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:home/tuan/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$home_id = (int)$data['home_id']){
                $this->err->add('未选要你要发布的小区', 212);
            }else if(!$company = K::M('company/company')->detail($data['company_id'])){
                $this->err->add('您选的公司不存在或已经删除', 213);
            }else if(!$this->check_city($company['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if($tuan_id = K::M('home/tuan')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?home/tuan-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:home/tuan/create.html';
        }
    }

    public function edit($tuan_id=null)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = $this->GP('tuan_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				unset($data['city_id']);
                if(K::M('home/tuan')->update($tuan_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            if($home_id = (int)$detail['home_id']){
                $this->pagedata['home'] = K::M('home/home')->detail($home_id);
            }
        	$this->pagedata['detail'] = $detail;
			$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        	$this->tmpl = 'admin:home/tuan/edit.html';
        }
    }

    public function doaudit($tuan_id=null)
    {
        if($tuan_id = (int)$tuan_id){
            if(K::M('home/tuan')->batch($tuan_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('tuan_id')){
            if(K::M('home/tuan')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($tuan_id=null)
    {
		if($tuan_id = (int)$tuan_id){
            if($tuan = K::M('home/tuan')->detail($tuan_id)){
                if(!$this->check_city($tuan['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('home/tuan')->delete($tuan_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('tuan_id')){
            if($items = K::M('home/tuan')->items_by_ids($ids)){
                $aids  = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['tuan_id']] = $v['tuan_id'];
                }
                if($aids && K::M('home/tuan')->delete($aids)){
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}