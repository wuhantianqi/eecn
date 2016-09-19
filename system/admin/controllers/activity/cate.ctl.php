<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: cate.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Activity_Cate extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['cat_id']){$filter['cat_id'] = $SO['cat_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
        }
        if($items = K::M('activity/cate')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:activity/cate/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:activity/cate/so.html';
    }

    public function detail($cat_id = null)
    {
        if(!$cat_id = (int)$cat_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('activity/cate')->detail($cat_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:activity/cate/detail.html';
        }
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($cat_id = K::M('activity/cate')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?activity/cate-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:activity/cate/create.html';
        }
    }

    public function edit($cat_id=null)
    {
        if(!($cat_id = (int)$cat_id) && !($cat_id = $this->GP('cat_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('activity/cate')->detail($cat_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('activity/cate')->update($cat_id, $data)){
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?activity/cate-index.html');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:activity/cate/edit.html';
        }
    }

    public function delete($cat_id)
    {
        if($cat_id = (int)$cat_id){
            if(K::M('activity/cate')->delete($cat_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('cat_id')){
            if(K::M('activity/cate')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}