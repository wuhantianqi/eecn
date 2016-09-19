<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Msite_Cate extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        
        if($items = K::M('msite/cate')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:msite/cate/items.html';
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
                    if($a = $upload->upload($attach, 'msite')){
                        $data[$k] = $a['photo'];
                    }
                }
            }
        }

            if($cat_id = K::M('msite/cate')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?msite/cate-index.html');
            } 
        }else{
           $this->tmpl = 'admin:msite/cate/create.html';
        }
    }

    public function edit($cat_id=null)
    {
        if(!($cat_id = (int)$cat_id) && !($cat_id = $this->GP('cat_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('msite/cate')->detail($cat_id)){
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
                    if($a = $upload->upload($attach, 'msite')){
                        $data[$k] = $a['photo'];
                    }
                }
            }
        }

            if(K::M('msite/cate')->update($cat_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:msite/cate/edit.html';
        }
    }



    public function delete($cat_id=null)
    {
        if($cat_id = (int)$cat_id){
            if(!$detail = K::M('msite/cate')->detail($cat_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('msite/cate')->delete($cat_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('cat_id')){
            if(K::M('msite/cate')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}