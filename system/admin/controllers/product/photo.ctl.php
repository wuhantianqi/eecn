<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: photo.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Product_Photo extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['photo_id']){$filter['photo_id'] = $SO['photo_id'];}
            if($SO['product_id']){$filter['product_id'] = $SO['product_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
        }
        if($items = K::M('product/photo')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $product_ids = array();
            foreach ($items as $k => $v) {
                if($v['product_id']){
                    $product_ids[$v['product_id']] = $v['product_id'];
                }
            }
            $this->pagedata['product_list'] = K::M('product/product')->items_by_ids($product_ids);
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:product/photo/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:product/photo/so.html';
    }

    public function product($product_id=null)
    {
        if(!$product_id = (int)$product_id){
            $this->err->add('未指定商品ID', 201);
        }else if(!$product = K::M('product/product')->detail($product_id, true)){
            $this->err->add('商品不存在或已经删除', 202);
        }else{
            $pager['product_id'] = $product_id;
            $pager['count'] = 0;
            $this->pagedata['product'] = $product;
            if($items = K::M('product/photo')->items_by_product($product_id)){
                $this->pagedata['items'] = $items;
                $pager['count'] = count($items);
            }
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'admin:product/photo/product.html';
        }
    }

    public function upload()
    {
        if(!$product_id = (int)$this->GP('product_id')){
            $this->err->add('非法的参数请求', 201);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('商品不存在或已经删除', 202);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else{
            $attach['uname'] = $product['title'];
            if($a  = K::M('product/photo')->upload($product_id, $attach)){
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
            $obj = K::M('product/photo');
            foreach((array)$data as $k=>$v){
                $obj->update($k, $v);
            }
            $this->err->add('指更新图片信息成功');
        }else{
            $this->err->add('非法的参数请求', 201);
        }
    }

    public function delete($photo_id=null)
    {
        if($photo_id = (int)$photo_id){
            if(K::M('product/photo')->delete($photo_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('photo_id')){
            if(K::M('product/photo')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}