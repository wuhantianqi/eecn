<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product.ctl.php 10476 2015-05-26 02:43:20Z wanglei $
 */

class Ctl_Ucenter_Product extends Ctl_Ucenter
{
    protected $_allow_fields = 'title,name,cat_id,vcat_id,brand_id,area_id,market_price,price,danwei,freight,photo,onsale,onpayment,sale_type,sale_time,sale_sku,sale_virtual,info,seo_title,seo_keywords,seo_description';
    public function index($page=1)
    {
        $shop = $this->ucenter_shop();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('shop_id'=>$shop['shop_id'], 'closed'=>0);
        if($items = K::M('product/product')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/product/items.html';
    }

    public function create($cat_id=null)
    {
        $shop = $this->ucenter_shop();
        if($data = $this->checksubmit('data')){
            $allow_product = K::M('member/group')->check_priv($shop['group_id'], 'allow_product');
            $product_payment = K::M('member/group')->check_priv($shop['group_id'], 'product_payment');
            if($allow_product < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限添加商品', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                $data['shop_id'] = $shop['shop_id'];
                $data['city_id'] = $shop['city_id'];
                if($data['onpayment']){
                    if($product_payment < 0){  //不可发布在线销售商品
                        $data['onpayment'] = 0;
                    }else if($data['audit']){
                        $data['audit'] = (int)$product_payment;
                    }
                }
                if($attach = $_FILES['product_photo']){
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = K::M('product/photo')->upload(0, $attach)){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                if($product_id = K::M('product/product')->create($data)){
                    K::M('shop/shop')->update_count($shop['shop_id'], 'products');
                    $fields = $this->GP('fields');
                    if($fields = $this->check_fields($fields, $this->_allow_fields)){
                        K::M('product/fields')->update($product_id, $fields);
                    }
                    if($attr = $this->GP('attr')){
                        K::M('product/attr')->update($product_id, $attr);
                    }
                    $this->err->set_data('forward', $this->mklink('ucenter/product'));
                    $this->err->add('添加商品成功');
                }
            }
        }else if($cat_id = (int)$cat_id){
            if($cate = K::M('shop/cate')->cate($cat_id)){                
                $pager['cat_id'] = $cat_id;
                $this->pagedata['pager'] = $pager;
                $this->pagedata['cate'] = $cate;         
                $this->pagedata['parents'] = K::M('shop/cate')->parents($cat_id); 
                $this->cookie->set('LAST_product_pids', K::M('shop/cate')->parent_ids($cat_id, ',', true));
                $this->tmpl = 'ucenter/product/create.html';
            }else{
                $this->err->add('选择的分类不存在或已经删除', 211);
                $this->err->set_data('forward', $this->mklink(null, array($product_id)));
            }
        }else{
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/product/select_cate.html';
        }
    }

    public function edit($product_id=null, $cat_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$detail = K::M('product/product')->detail($product_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($detail['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 213);
        }else if($data = $this->checksubmit('data')){
            $allow_product = K::M('member/group')->check_priv($shop['group_id'], 'allow_product');
            $product_payment = K::M('member/group')->check_priv($shop['group_id'], 'product_payment');     
			
            if($attach = $_FILES['product_photo']){
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = K::M('product/photo')->upload(0, $attach)){
                        $data['photo'] = $a['photo'];
                    }
                }
            }
            if($allow_product < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限修改商品', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
               $this->err->add('非法的数据提交', 214);
            }else{
                $data['city_id'] = $shop['city_id'];
                if($detail['audit']){
                    $data['audit'] = (int)$allow_product;
                }
                if($data['onpayment'] && empty($detail['onpayment'])){
                    if($product_payment < 0){ //不可发布在线销售商品
                        $data['onpayment'] = 0;
                    }else if($data['audit']){
                        $data['audit'] = (int)$product_payment;
                    }
                }
                if(K::M('product/product')->update($product_id, $data)){
                    if($fields = $this->GP('fields')){
                        if($fields = $this->check_fields($fields, $this->_allow_fields)){
                            K::M('product/fields')->update($product_id, $fields);
                        }
                        if(!$attr = $this->GP('attr')){
                            $attr = array();
                        }
                        K::M('product/attr')->update($product_id, $attr);
                    }
                    $this->err->add('修改商品成功');
                }
            }
        }else if($cat_id == 'edit'){
            $pager['cat_id'] = $cat_id;
            $pager['product_id'] = $product_id;
            $pager['type'] = 'edit';
            $pager['cat_pids'] = K::M('shop/cate')->parent_ids($detail['cat_id'], ',', true);
            $this->pagedata['pager'] = $pager;
            $this->pagedata['product'] = $detail;
            $this->tmpl = 'ucenter/product/select_cate.html';            
        }else{
            if(!$cat_id = (int)$cat_id){
                $cat_id = $detail['cat_id'];
            }
            $pager['product_id'] = $product_id;
            $pager['cat_id'] = $cat_id;            
            if($attrs = K::M('product/attr')->attrs_by_product($product_id)){
                $this->pagedata['attrs'] = $attrs;
                $detail['attrvalues'] = array_keys($attrs);
            }
            $this->pagedata['parents'] = K::M('shop/cate')->parents($cat_id);
            $this->pagedata['detail'] = $detail;
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/product/edit.html';
        }
    }

    public function delete($product_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->err->add('未指定要删除的商品', 211);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($product['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 212);
        }else if(K::M('product/product')->delete($product_id)){
            K::M('shop/shop')->update_count($shop['shop_id'], 'products','-1');
            $this->err->add('删除商品成功');
        }
    }

    public function upload()
    {
        $shop = $this->ucenter_shop();
        if(!$product_id = (int)$this->GP('product_id')){
            $this->err->add('非法的参数请求', 211);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('商品不存在或已经删除', 212);
        }else if($product['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 213);
        }else if(K::M('product/photo')->count_by_product($product_id) >= 5){
            $this->err->add('每个商品最多只能传5张图片', 214);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else{
            if($a  = K::M('product/photo')->upload($product_id, $attach)){
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('thumb', $cfg['attachurl'].'/'.$a['photo']);
                $this->err->set_data('item', $a);
                $this->err->add('上传图片成功');
            }
        }
        $this->err->json();            
    }

    public function spec($product_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('商品不存在或已经删除', 212);
        }else if($product['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 212);
        }else{
            $count = 0;
            if($items = K::M('product/spec')->items_by_product($product_id)){
                $count = count($items);
                $this->pagedata['items'] = $items;
            }
            $pager['count'] = $count;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['product'] = $product;
            $this->tmpl = 'ucenter/product/spec.html';
        }
    }

    public function updatespec()
    {
        $shop = $this->ucenter_shop();
        if(!$product_id = (int)$this->GP('product_id')){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('商品不存在或已经删除', 212);
        }else if($product['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 212);
        }else if($this->checksubmit()){
            if($spec = $this->checksubmit('spec')){
                if($items = K::M('product/spec')->items_by_product($product_id)){
                    foreach($items as $k=>$v){
                        if($a = $spec[$k]){
                            if(K::M('product/spec')->update($k, $a)){
                                if($attach = $_FILES['spec_photo_'.$k]){
                                    K::M('product/spec')->upload($k, $attach);
                                }
                            }
                        }
                    }
                }
            }
            if($data = $this->checksubmit('data')){
                foreach($data as $k=>$v){
                    if($v['spec_name'] && $v['price']){
                        $v['product_id'] = $product_id;
                        if($spec_id = K::M('product/spec')->create($v)){
                            if($attach = $_FILES['spec_photo_'.$k]){
                               K::M('product/spec')->upload($spec_id, $attach);
                            }
                        }
                    }
                }
            }
			$items = K::M('product/spec')->items(array('product_id'=>$product_id));
			if($items){
				foreach($items as $k => $v){
					$sku += $v['sale_sku'];
				}
				K::M('product/product')->update($product_id, array('sale_sku'=>$sku));
			}
            $this->err->add('更新商品规格成功');
        }        
    }

    public function deletespec($spec_id=null)
    {
		$shop = $this->ucenter_shop();
        if(!$spec_id = (int)$spec_id){
            $this->err->add('未定义操作', 211);
        }else if(!$spec = K::M('product/spec')->detail($spec_id)){
            $this->err->add('规格不存在或已经删除', 212);
        }else if(!$product = K::M('product/product')->detail($spec['product_id'])){
            $this->err->add('你没有权限删除', 213);
        }else if(K::M('product/spec')->delete($spec_id)){
			$items = K::M('product/spec')->items(array('product_id'=>$product['product_id']));
			if($items){
				foreach($items as $k => $v){
					$sku += $v['sale_sku'];
				}
				K::M('product/product')->update($product['product_id'], array('sale_sku'=>$sku));
			}
            $this->err->add('规格删除成功');
        }
    }

    public function photo($product_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('商品不存在或已经删除', 212);
        }else if($product['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 212);
        }else{
            $count = 0;
            if($items = K::M('product/photo')->items_by_product($product_id)){
                $count = count($items);
                $this->pagedata['items'] = $items;
            }
            $pager['count'] = $count;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['product'] = $product;
            $this->tmpl = 'ucenter/product/photo.html';
        }
    }

    public function updatephoto()
    {
        $shop = $this->ucenter_shop();
        if(!$product_id = (int)$this->GP('product_id')){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id)){
            $this->err->add('商品不存在或已经删除', 212);
        }else if($product['shop_id'] != $shop['shop_id']){
            $this->err->add('非法的数据提交', 212);
        }else if($data = $this->checksubmit('data')){
            if($items = K::M('product/photo')->items_by_product($product_id)){
                foreach($items as $k=>$v){
                    if($a = $data[$k]){
                        if($a['title'] != $v['title'] || $a['orderby'] != $v['orderby']){
                            K::M('product/photo')->update($k, array('title'=>$a['title'], 'orderby'=>$a['orderby']));
                        }
                    }
                }
            }
            $this->err->add('更新商品图片成功');
        }
    }

    public function deletephoto($photo_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!$photo_id = (int)$photo_id){
            $this->err->add('未定义操作', 211);
        }else if(!$photo = K::M('product/photo')->detail($photo_id)){
            $this->err->add('图片不存在或已经删除', 212);
        }else if(!$product = K::M('product/product')->detail($photo['product_id'])){
            $this->err->add('你没有权限删除', 213);
        }else if($shop['shop_id'] != $product['shop_id']){
            $this->err->add('你没有权限删除', 213);
        }else if(K::M('product/photo')->delete($photo_id)){
            $this->err->add('图片删除成功');
        }
    }

    public function order($page=1)
    {
        $shop = $this->ucenter_shop();
        
    }

}