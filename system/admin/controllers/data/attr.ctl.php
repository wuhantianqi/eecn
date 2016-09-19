<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: attr.ctl.php 2070 2013-12-09 09:04:47Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Data_Attr extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        $SO = $this->GP('SO');
        if($attr_id = intval($SO['attr_id'])){
            $items = array();
            $items[$attr_id] = K::M('data/attr')->attr($attr_id);
        }else if($from = $SO['from']){
            $items = K::M('data/attr')->attrs_by_from($from);
        }else{
            $items = K::M('data/attr')->fetch_all();
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['from_list'] = K::M('data/attrfrom')->fetch_all();
        $this->tmpl = 'admin:data/attr/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:data/attr/so.html';
    }

    public function create()
    {
        if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if($attr_id = K::M('data/attr')->create($data)){
                $this->err->add('修改内容成功');
                $this->err->set_data('forward', '?data/attr-index.html');
            }
        }else{
           $this->tmpl = 'admin:data/attr/create.html';
        }
    }

    public function update()
    {
        if($this->checksubmit()){
            if($data = $this->GP('data')){
                $obj = K::M('data/attr');
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

    public function delete($attr_id)
    {
        if(!empty($attr_id)){
            if($values = K::M('data/attrvalue')->value_by_attr($attr_id)){
                $this->err->add('先要删除属性下选项', 221);
            }else if(K::M('data/attr')->delete($attr_id)){
                $this->err->add('删除属性成功');
            }
        }else if($attr_ids = $this->GP('attr_id')){
            if(K::M('data/attr')->delete($attr_ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function detail($attr_id)
    {
        if(!$attr_id = intval($attr_id)){
             $this->err->add('非法的参数请求', 201);
        }else if(!$attr = K::M('data/attr')->attr($attr_id)){
            $this->err->add('属性不存在或已经删除', 202);
        }else{
            $pager = array('attr_id'=>$attr_id);
            $this->pagedata['attr'] = $attr;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['items'] = K::M('data/attrvalue')->value_by_attr($attr_id);
            $this->tmpl = 'admin:data/attr/detail.html';
        }
    }

    public function updatevalue()
    {
        if(!$attr_id = $this->GP('attr_id')){
            $this->err->add('未指定属性ID', 201);
        }else if(!$attr = K::M('data/attr')->attr($attr_id)){
            $this->err->add('属性不存或已经删除', 202);
        }else if($this->checksubmit()){
            $obj = K::M('data/attrvalue');
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
            if(K::M('data/attrvalue')->delete($vid)){
                $this->err->add('删除选项成功');
            }
        }else if($vids = $this->GP('attr_value_id')){
            if(K::M('data/attrvalue')->delete($vids)){
                $this->err->add('批量删除选项成功');
            }
        }else{
            $this->err->add('未指定要删除的选项ID', 401);
        }     
    }

	public function attrfrom()
	{
		$items = K::M('data/attrfrom')->fetch_all();
        $this->pagedata['items'] = $items;
        $this->tmpl = 'admin:data/attr/from/items.html';	
	}

	public function createfrom()
	{
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($from_id = K::M('data/attrfrom')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?data/attr-attrfrom.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:data/attr/from/create.html';
        }	
	}

	public function editfrom($from_id=null)
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
            $this->tmpl = 'admin:data/attr/from/edit.html';
        }	
	}

	public function deletefrom($from_id=null)
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