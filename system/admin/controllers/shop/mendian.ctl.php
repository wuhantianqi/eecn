<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: mendian.ctl.php 5867 2014-07-12 02:04:39Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_mendian extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['mendian_id']){$filter['mendian_id'] = $SO['mendian_id'];}
            if($SO['shop_id']){$filter['shop_id'] = $SO['shop_id'];}
            if($SO['from']){$filter['from'] = $SO['from'];}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
        }
		if(CITY_ID){
			$filter['city_id'] = CITY_ID;
		}
        if($items = K::M('shop/mendian')->items($filter, null, $page, $limit, $count)){
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
		$this->pagedata['city_list'] = K::M('data/city')->fetch_all();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/mendian/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/mendian/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				if(CITY_ID){
					$data['city_id'] = CITY_ID;
				}
                if($_FILES['data']){
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attach[$k] = $vv;
                        }
                    }
                    if($thumb = K::M('magic/upload')->upload($attach)){
                    $data['thumb'] = $thumb['photo'];
                    }
                }                
                if($mendian_id = K::M('shop/mendian')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/mendian-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/mendian/create.html';
        }
    }

    public function edit($mendian_id=null)
    {
        if(!($mendian_id = (int)$mendian_id) && !($mendian_id = $this->GP('mendian_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/mendian')->detail($mendian_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']['name']['thumb']){
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attach[$k] = $vv;
                        }
                    }
                    if($thumb = K::M('magic/upload')->upload($attach)){
                    $data['thumb'] = $thumb['photo'];
                    }
                } 
                if(K::M('shop/mendian')->update($mendian_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($shop_id = (int)$detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:shop/mendian/edit.html';
        }
    }

    public function doaudit($mendian_id=null)
    {
        if($mendian_id = (int)$mendian_id){
            if(K::M('shop/mendian')->batch($mendian_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('mendian_id')){
            if(K::M('shop/mendian')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($mendian_id=null)
    {
        if($mendian_id = (int)$mendian_id){
            if(K::M('shop/mendian')->delete($mendian_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('mendian_id')){
            if(K::M('shop/mendian')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}