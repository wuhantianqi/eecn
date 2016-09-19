<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cate.ctl.php 5639 2014-06-25 06:37:14Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Cate extends Ctl
{
    
    public function index($page=1)
    {
		/*
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['cat_id']){$filter['cat_id'] = $SO['cat_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
            if($SO['parent_id']){$filter['parent_id'] = $SO['parent_id'];}
            if($SO['audit']){$filter['audit'] = $SO['audit'];}
        }
        if($items = K::M('shop/cate')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;

        <th class="w-100">ID</th><th>标题</th><th class="w-50">父分类</th><th>关联品牌</th>
        <th class="w-50">排序</th><th class="w-50">审核</th><th class="w-100">添加时间</th><th class="w-150">操作</th>
*/
        if($items = K::M('shop/cate')->fetch_all()){
            $brand_list = K::M('shop/brand')->fetch_all();
            foreach($items as $k=>$v){
                $a = array();
                if($v['brand_ids']){
                    foreach(explode(',', $v['brand_ids']) as $id){
                        $a[] = $brand_list[$id]['title'];
                    }                    
                }
                $v['brands'] = empty($a) ? '--' : implode(',&nbsp;&nbsp;', $a);
                $v['auditLabel'] = $v['audit'] ? '正常' : '<b class="red">待审</b>';
                $items[$k] = $v;
            }
            $oTree = K::M('helper/tree');
            $oTree->icon = array('|--- ','|--- ','|---');
            $oTree->nbsp = '&nbsp;&nbsp;&nbsp;';
            $oTree->init($items);
            $strtmpl = '<tr>
                    <td><label><input type="checkbox" value="{$cat_id}" name="cat_id[]" CK="PRI"/>{$cat_id}<label></td>
                    <td>{$spacer}{$title}</td><td>{$brands}</td><td>{$orderby}</td><td>{$auditLabel}</td>
                    <td><a href="?shop/cate-edit-{$cat_id}.html" title="修改" mini-load="修改分类" mini-width="550" class="button" >修改</a>
                    <a href="?shop/cate-delete-{$cat_id}.html" mini-act="删除" mini-confirm="确定要删除吗？" title="删除" class="button" >删除</a></td>
                </tr>';
            $this->pagedata['items'] = $items;
            $this->pagedata['treetable'] = $oTree->tree(0, $strtmpl);
        }
        $this->tmpl = 'admin:shop/cate/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/cate/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($cat_id = K::M('shop/cate')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/cate-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/cate/create.html';
        }
    }

    public function edit($cat_id=null)
    {
        if(!($cat_id = (int)$cat_id) && !($cat_id = $this->GP('cat_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/cate')->detail($cat_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(empty($data['brand_ids'])){
                    $data['brand_ids'] = '';
                }
                if(K::M('shop/cate')->update($cat_id, $data)){
                    $this->err->add('修改内容成功');
                }
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:shop/cate/edit.html';
        }
    }

    public function doaudit($cat_id=null)
    {
        if($cat_id = (int)$cat_id){
            if(K::M('shop/cate')->batch($cat_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('cat_id')){
            if(K::M('shop/cate')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($cat_id=null)
    {
        if($cat_id = (int)$cat_id){
            if(K::M('shop/cate')->delete($cat_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('cat_id')){
            if(K::M('shop/cate')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function children($pid=null)
    {
        if(!is_numeric($pid)){
            return false;
        }
        $cats = array();
        if($childrens = K::M('shop/cate')->childrens($pid)){
            foreach($childrens as $k=>$v){
                $cats[] = array('cat_id'=>$v['cat_id'], 'parent_id'=>$v['parent_id'], 'title'=>$v['title']);
            }
        }
        $this->err->set_data('cats', $cats);        
        $this->err->json();        
    }   
}