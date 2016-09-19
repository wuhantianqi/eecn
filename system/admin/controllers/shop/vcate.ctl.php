<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: vcate.ctl.php 2756 2014-01-05 16:14:17Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Vcate extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['vcat_id']){$filter['vcat_id'] = $SO['vcat_id'];}
            if($SO['shop_id']){$filter['shop_id'] = $SO['shop_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
        }
        if($items = K::M('shop/vcate')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $shop_ids = array();
            foreach($items as $k=>$v){
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/vcate/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/vcate/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($titles = preg_replace("/[\r\n]+/", "\n", $data['title'])){
                    $count = 0;
                    foreach(explode("\n", $titles) as $title){
                        $data['title'] = $title;
                        if(K::M('shop/vcate')->create($data)){
                            $count ++;
                        }
                    }
                    if($count){
                        $this->err->add('成功添加'.$count.'条店铺分类');
                    }
                }else{
                    $this->err->add('分类名称不能为空', 211);
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/vcate/create.html';
        }
    }

    public function edit($vcat_id=null)
    {
        if(!($vcat_id = (int)$vcat_id) && !($vcat_id = $this->GP('vcat_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/vcate')->detail($vcat_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('shop/vcate')->update($vcat_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
        	$this->tmpl = 'admin:shop/vcate/edit.html';
        }
    }



    public function delete($vcat_id=null)
    {
        if($vcat_id = (int)$vcat_id){
            if(K::M('shop/vcate')->delete($vcat_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('vcat_id')){
            if(K::M('shop/vcate')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}