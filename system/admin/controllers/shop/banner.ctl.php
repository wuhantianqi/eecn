<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: banner.ctl.php 3304 2014-02-14 11:01:43Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Banner extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;

        if($items = K::M('shop/banner')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/banner/items.html';
    }

    public function shop($shop_id=null)
    {
        if(!$shop_id = (int)$shop_id){
            $this->err->add('未指定商铺ID', 201);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id, true)){
            $this->err->add('商铺不存在或已经删除', 202);
        }else{
            $pager['shop_id'] = $shop_id;
            $pager['count'] = 0;
            $this->pagedata['shop'] = $shop;
            if($items = K::M('shop/banner')->items_by_shop($shop_id)){
                $this->pagedata['items'] = $items;
                $pager['count'] = count($items);
            }
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'admin:shop/banner/shop.html';
        }        
    }

    public function upload()
    {
        if(!$shop_id = (int)$this->GP('shop_id')){
            $this->err->add('非法的参数请求', 201);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id)){
            $this->err->add('商铺不存在或已经删除', 202);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else{
            if($a = K::M('shop/banner')->upload($shop_id, $attach)){
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('thumb', $cfg['attachurl'].'/'.$a['photo']);
                $this->err->set_data('item', $a);
                $this->err->add('上传图片成功');
            }
        }
        $this->err->json();
    }

    public function update()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的参数请求', 201);
            }
            $obj = K::M('shop/banner');
            foreach((array)$data as $k=>$v){
                $obj->update($k, $v);
            }
            $this->err->add('指更新图片信息成功');
        }else{
            $this->err->add('非法的参数请求', 201);
        }
    }


    public function delete($banner_id=null)
    {
        if($banner_id = (int)$banner_id){
            if(K::M('shop/banner')->delete($banner_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('banner_id')){
            if(K::M('shop/banner')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}