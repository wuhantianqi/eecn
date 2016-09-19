<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Product_Spec extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        
        if($items = K::M('product/spec')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:product/spec/items.html';
    }





    public function create()
    {
        if($data = $this->checksubmit('data')){
                    if($_FILES['data']){
            foreach($_FILES['data'] as $k=>$v){
                foreach($v as $kk=>$vv){
                    $attachs[$kk][$k] = $vv;
                }
            }
            $upload = K::M('magic/upload');
            foreach($attachs as $k=>$attach){
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = $upload->upload($attach, 'product')){
                        $data[$k] = $a['photo'];
                    }
                }
            }
        }

            if($spec_id = K::M('product/spec')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?product/spec-index.html');
            } 
        }else{
           $this->tmpl = 'admin:product/spec/create.html';
        }
    }

    public function edit($spec_id=null)
    {
        if(!($spec_id = (int)$spec_id) && !($spec_id = $this->GP('spec_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('product/spec')->detail($spec_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
                    if($_FILES['data']){
            foreach($_FILES['data'] as $k=>$v){
                foreach($v as $kk=>$vv){
                    $attachs[$kk][$k] = $vv;
                }
            }
            $upload = K::M('magic/upload');
            foreach($attachs as $k=>$attach){
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = $upload->upload($attach, 'product')){
                        $data[$k] = $a['photo'];
                    }
                }
            }
        }

            if(K::M('product/spec')->update($spec_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:product/spec/edit.html';
        }
    }



    public function delete($spec_id=null)
    {
        if($spec_id = (int)$spec_id){
            if(!$detail = K::M('product/spec')->detail($spec_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('product/spec')->delete($spec_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('spec_id')){
            if(K::M('product/spec')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}