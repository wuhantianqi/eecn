<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: links.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Market_Links extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;            
        }
        $filter['closed'] = 0;
		$filter['city_id'] = CITY_ID;
        if($items = K::M('market/links')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'fenzhan:links/items.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				$data['city_id'] = CITY_ID;
                if($link_id = K::M('market/links')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?market/links-index.html');
                }
            } 
        }else{
           $this->tmpl = 'fenzhan:links/create.html';
        }
    }

    public function edit($link_id=null)
    {
        if(!($link_id = (int)$link_id) && !($link_id = $this->GP('link_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('market/links')->detail($link_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				unset($data['city_id']);
                if(K::M('market/links')->update($link_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'fenzhan:links/edit.html';
        }
    }

    public function delete($link_id=null)
    {
        if($link_id = (int)$link_id){
            if(K::M('market/links')->delete($link_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('link_id')){
            if(K::M('market/links')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}