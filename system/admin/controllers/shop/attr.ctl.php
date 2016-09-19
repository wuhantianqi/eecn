<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: attr.ctl.php 2335 2013-12-18 17:15:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Attr extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
            if($SO['cat_id']){$filter['cat_id'] = $SO['cat_id'];}
        }
        if($items = K::M('shop/attr')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/attr/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/attr/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($attr_id = K::M('shop/attr')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/attr-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/attr/create.html';
        }
    }

    public function edit($attr_id=null)
    {
        if(!($attr_id = (int)$attr_id) && !($attr_id = $this->GP('attr_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/attr')->detail($attr_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('shop/attr')->update($attr_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:shop/attr/edit.html';
        }
    }

    public function update()
    {
        if($this->checksubmit()){
            if($data = $this->GP('data')){
                $obj = K::M('shop/attr');
                foreach($data as $k=>$v){
                    if($v['title'] && $v['orderby']){
                        $a = array('title'=>$v['title'], 'orderby'=>$v['orderby']);
                        $a['multi'] = $v['multi'] ? 'Y' : 'N';
                        $a['filter'] = $v['filter'] ? 'Y' : 'N';
                        $obj->update($k, $a);
                    }
                }
            }
        }
    }

    public function delete($attr_id=null)
    {
        if($attr_id = (int)$attr_id){
            if(K::M('shop/attr')->delete($attr_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('attr_id')){
            if(K::M('shop/attr')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容', 401);
        }
    }

    public function detail($attr_id=null)
    {
        if(!$attr_id = intval($attr_id)){
             $this->err->add('非法的参数请求', 201);
        }else if(!$attr = K::M('shop/attr')->attr($attr_id)){
            $this->err->add('属性不存在或已经删除', 202);
        }else{
            $pager = array('attr_id'=>$attr_id);
            $this->pagedata['attr'] = $attr;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['items'] = K::M('shop/attrvalue')->value_by_attr($attr_id);
            $this->tmpl = 'admin:shop/attr/detail.html';
        }
    }

    public function updatevalue()
    {
        if(!$attr_id = $this->GP('attr_id')){
            $this->err->add('未指定属性ID', 201);
        }else if(!$attr = K::M('shop/attr')->attr($attr_id)){
            $this->err->add('属性不存或已经删除', 202);
        }else if($this->checksubmit()){
            $obj = K::M('shop/attrvalue');
            if($data = $this->GP('data')){
                foreach($data as $k=>$v){
                    $a = array('title'=>$v['title'], 'orderby'=>$v['orderby']);
                    $obj->update($k, $a);
                }
            }
            if($value = $this->GP('value')){
                foreach($value as $v){
                    if($v['title']){
                        $a = array('title'=>$v['title'], 'orderby'=>$v['orderby']);
                        $obj->create($attr_id, $a);
                    }
                }
            }
        }
    }

    public function delvalue($vid = null)
    {
        if(!empty($vid)){
            if(K::M('shop/attrvalue')->delete($vid)){
                $this->err->add('删除选项成功');
            }
        }else if($vids = $this->GP('attr_value_id')){
            if(K::M('shop/attrvalue')->delete($vids)){
                $this->err->add('批量删除选项成功');
            }
        }else{
            $this->err->add('未指定要删除的选项', 401);
        }     
    }

}