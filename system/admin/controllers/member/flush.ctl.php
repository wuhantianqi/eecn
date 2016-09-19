<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Member_Flush extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        
        if($items = K::M('member/flush')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:member/flush/items.html';
    }





    public function create()
    {
        if($data = $this->checksubmit('data')){
            
            if($flush_id = K::M('member/flush')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?member/flush-index.html');
            } 
        }else{
           $this->tmpl = 'admin:member/flush/create.html';
        }
    }

    public function edit($flush_id=null)
    {
        if(!($flush_id = (int)$flush_id) && !($flush_id = $this->GP('flush_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('member/flush')->detail($flush_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            
            if(K::M('member/flush')->update($flush_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:member/flush/edit.html';
        }
    }



    public function delete($flush_id=null)
    {
        if($flush_id = (int)$flush_id){
            if(!$detail = K::M('member/flush')->detail($flush_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('member/flush')->delete($flush_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('flush_id')){
            if(K::M('member/flush')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}