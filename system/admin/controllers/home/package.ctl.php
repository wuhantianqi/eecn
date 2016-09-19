<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: package.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_Package extends Ctl
{
    
    public function index($tuan_id=0,$page=1)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = $this->GP('tuan_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            $filter['tuan_id'] = $tuan_id;
            if($items = K::M('home/package')->items($filter, null, $page, $limit, $count)){
                $huxingIds = array();
                foreach($items as $k=>$v){
                    if($v['huxing_id']) $huxingIds[$v['huxing_id']] = $v['huxing_id'];
                }
                if(!empty($huxingIds)){
                    $this->pagedata['huxing'] = K::M('home/photo')->items_by_ids($huxingIds);
                }
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            }
            $this->pagedata['items'] = $items;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['tuan_id'] = $tuan_id;
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:home/package/items.html';
        }
    }
	
    public function create($tuan_id)
    {
		
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }elseif($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['tuan_id'] = $tuan_id;
                if($package_id = K::M('home/package')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?home/package-index-'.$tuan_id.'.html');
                }
            } 
        }else{
           $this->pagedata['tuan_id'] = $tuan_id;
           $this->pagedata['detail'] = $detail;
           $this->tmpl = 'admin:home/package/create.html';
        }
    }

    public function edit($package_id=null)
    {
        if(!($package_id = (int)$package_id) && !($package_id = $this->GP('package_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/package')->detail($package_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('home/package')->update($package_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['home_tuan'] =  K::M('home/tuan')->detail($detail['tuan_id']);
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:home/package/edit.html';
        }
    }

	public function delete($package_id=null)
    {
		if($package_id = (int)$package_id){
            if($package = K::M('home/package')->detail($package_id)){
                if(K::M('home/package')->delete($package_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('package_id')){
            if($items = K::M('home/package')->items_by_ids($ids)){
                $aids  = array();
                foreach($items as $v){
                    $aids[$v['package_id']] = $v['package_id'];
                }
                if($aids && K::M('home/package')->delete($aids)){
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}