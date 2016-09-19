<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: attrfrom.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Data_Attrfrom extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($items = K::M('data/attrfrom')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:data/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:data/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($from_id = K::M('data/attrfrom')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?data/attrfrom-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:data/create.html';
        }
    }

    public function edit($from_id=null)
    {
        if(!($from_id = (int)$from_id) && !($from_id = $this->GP('from_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('data/attrfrom')->detail($from_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('data/attrfrom')->update($from_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:data/edit.html';
        }
    }

    public function delete($from_id)
    {
        if($from_id = (int)$from_id){
            if(K::M('data/attrfrom')->delete($from_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('from_id')){
            if(K::M('data/attrfrom')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}