<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product.ctl.php 5867 2014-07-12 02:04:39Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Product_Product extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['product_id']){
                $filter['product_id'] = $SO['product_id'];
            }else{
                if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
                if($SO['shop_id']){$filter['shop_id'] = $SO['shop_id'];}
                if($SO['cat_id']){$filter['cat_id'] = $SO['cat_id'];}
                if($SO['brand_id']){$filter['brand_id'] = $SO['brand_id'];}
                if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
                if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
                if(is_array($SO['lastupdate'])){if($SO['lastupdate'][0] && $SO['lastupdate'][1]){$a = strtotime($SO['lastupdate'][0]); $b = strtotime($SO['lastupdate'][1])+86400;$filter['lastupdate'] = $a."~".$b;}}
            }
        }
        $filter['closed'] = 0;
		if(CITY_ID){
			$filter['city_id'] = CITY_ID;
		}
        if($items = K::M('product/product')->items($filter, null, $page, $limit, $count)){
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
        $this->tmpl = 'admin:product/product/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:product/product/so.html';
    }

    public function shop($shop_id=null)
    {
        if(!$shop_id = (int)$shop_id){
            $this->err->add('未指定隶属商铺', 211);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id, true)){
            $this->err->add('指定的商铺不存在或删除', 212);
        }else{
            $filter = array('shop_id'=>$shop_id, 'closed'=>0);
            if($items = K::M('product/product')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($shop_id, '{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['shop'] = $shop;
            $this->tmpl = 'admin:product/product/shop.html';
        } 
    }

    public function create($shop_id=null, $cat_id=null)
    {
        if(!($shop_id = (int)$shop_id) && !($shop_id = (int)$this->GP('shop_id'))){
            $this->err->add('未指定隶属商铺', 211);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id, true)){
            $this->err->add('指定的商铺不存在或删除', 212);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$fields = $this->GP('fields')){
                $this->err->add('非法的数据提交', 202);
            }else{
                if($_FILES['data']){
                    if($photos = $this->__upload($_FILES['data'])){
                        $data = array_merge($data, $photos);
                    }
                }
				if(CITY_ID){
					$data['city_id'] = CITY_ID;
				}
                $data['shop_id'] = $shop_id;
                $data['city_id'] = $shop['city_id'];
                if($fields = K::M('product/fields')->check_fields($fields)){
                    $data['sale_remai'] = $data['sale_remai'] ? 1 : 0;
                    $data['sale_youhui'] = $data['sale_youhui'] ? 1 : 0;
                    $data['sale_tuijian'] = $data['sale_tuijian'] ? 1 : 0;                  
                    if($product_id = K::M('product/product')->create($data)){
                        if($attr = $this->GP('attr')){
                            K::M('product/attr')->update($product_id, $attr);
                        }
                        K::M('product/fields')->update($product_id, $fields, true);
                        $this->err->add('添加内容成功');
                        $this->err->set_data('forward', '?product/product-shop-'.$shop_id.'.html');
                    }
                }
            }
        }else if(!($cat_id = (int)$cat_id) && !($cat_id = (int)$this->GP('cat_id'))){
            $pager['shop_id'] = $shop_id;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['shop'] = $shop;
            $this->tmpl = 'admin:product/product/select_cate.html';
        }else{
            $pager['shop_id'] = $shop_id;
            $pager['cat_id'] = $cat_id;
            $this->pagedata['pager'] = $pager;            
            $this->pagedata['shop'] = $shop;
            $this->pagedata['cate'] = K::M('shop/cate')->cate($cat_id);
            $this->pagedata['top_cate'] = K::M('shop/cate')->top_cate($cat_id);
            $this->pagedata['parents'] = K::M('shop/cate')->parents($cat_id);
            $this->cookie->set('LAST_product_pids', K::M('shop/cate')->parent_ids($cat_id, ',', true));
            $this->tmpl = 'admin:product/product/create.html';
        }
    }

    public function edit($product_id=null, $cat_id=null)
    {
        if(!($product_id = (int)$product_id) && !($product_id = $this->GP('product_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('product/product')->detail($product_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']){
                    if($photos = $this->__upload($_FILES['data'])){
                        $data = array_merge($data, $photos);
                    }
                }
				unset($data['city_id']);
                $data['sale_remai'] = $data['sale_remai'] ? 1 : 0;
                $data['sale_youhui'] = $data['sale_youhui'] ? 1 : 0;
                $data['sale_tuijian'] = $data['sale_tuijian'] ? 1 : 0;
                if(K::M('product/product')->update($product_id, $data)){
                    if(!$attr = $this->GP('attr')){
                        $attr = array();
                    }
                    K::M('product/attr')->update($product_id, $attr);              
                    if($fields = $this->GP('fields')){
                        K::M('product/fields')->update($product_id, $fields);
                    }
                    $this->err->add('修改内容成功');
                }
            }
        }else if($cat_id == 'edit'){
            $pager['shop_id'] = $shop_id;
            $pager['cat_id'] = $cat_id;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['shop'] = $shop;
            $pager['product_id'] = $product_id;
            $pager['cat_pids'] = K::M('shop/cate')->parent_ids($detail['cat_id'], ',', true);
            $pager['type'] = 'edit';
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'admin:product/product/select_cate.html';            
        }else{
            if(!$cat_id = (int)$cat_id){
                $cat_id = $detail['cat_id'];
            }
            $pager['product_id'] = $product_id;
            $pager['cat_id'] = $cat_id;
            $this->pagedata['top_cate'] = K::M('shop/cate')->top_cate($cat_id); 
            if($attrs = K::M('product/attr')->attrs_by_product($product_id)){
                $this->pagedata['attrs'] = $attrs;
                $detail['attrvalues'] = array_keys($attrs);
            }
            $this->pagedata['shop'] = K::M('shop/shop')->detail($detail['shop_id']);
            $detail['cat_pids'] = K::M('shop/cate')->parent_ids($detail['cat_id'], ',', true);
            $this->pagedata['cate'] = K::M('shop/cate')->cate($cat_id);
            $this->pagedata['parents'] = K::M('shop/cate')->parents($cat_id);
            $this->pagedata['pager'] = $pager;         
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:product/product/edit.html';
        }
    }

    public function doaudit($product_id=null)
    {
        if($product_id = (int)$product_id){
            if(K::M('product/product')->batch($product_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('product_id')){
            if(K::M('product/product')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($product_id=null)
    {
        if($product_id = (int)$product_id){
            if(K::M('product/product')->delete($product_id)){
                if($product = K::M('product/product')->detail($product_id)){
                    K::M('shop/shop')->update_count($product['shop_id'], 'products','-1');
                }
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('product_id')){
            if(K::M('product/product')->delete($ids)){
                foreach($ids as $id){
                    if($product = K::M('product/product')->detail($id)){
                        K::M('shop/shop')->update_count($product['shop_id'], 'products','-1');
                    }
                }
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    protected function __upload($data, $product_id=0)
    {
        $photos = array();        
        foreach($data as $k=>$v){
            foreach($v as $kk=>$vv){
                $attachs[$kk][$k] = $vv;
            }
        }
        foreach($attachs as $k=>$attach){
            if($attach['error'] == UPLOAD_ERR_OK){
                if($a = K::M('product/photo')->upload($product_id, $attach)){
                    $photos[$k] = $a['photo'];
                }
            }
        }
        return $photos;
    }
}