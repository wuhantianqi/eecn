<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Data_Cate extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;

        if($items = K::M('data/cate')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:data/cate/items.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($names = preg_replace("/[\r\n]+/", "\n", $data['title'])){
                    $count = 0;
                    foreach(explode("\n", $names) as $title){
                        $data['title'] = $title;
                        if(K::M('data/cate')->create($data)){
                            $count ++;
                        }
                    }
                    if($count){
                        $this->err->add('成功添加'.$count.'个分类');
                    }
                    $this->err->set_data('forward', '?data/cate-index.html');
                }else{
                    $this->err->add('分类不能为空', 211);
                }
            } 
        }else{
           $this->tmpl = 'admin:data/cate/create.html';
        }
    }

    public function update()
    {
        if($this->checksubmit()){
            if($data = $this->GP('data')){
                $obj = K::M('data/cate');
                foreach($data as $k=>$v){
                    if($v['title'] && $v['orderby']){
                        $a = array('title'=>$v['title'], 'orderby'=>(int)$v['orderby']);
                        $obj->update($k, $a);
                    }
                }
            }
        }
        $this->err->add('批量更新内容成功');
    }

    public function delete($cate_id=null)
    {
        if($cate_id = (int)$cate_id){
            if(K::M('data/cate')->delete($cate_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('cate_id')){
            if(K::M('data/cate')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}