<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mall_Product extends Ctl 
{
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/product-([\d\-]+).html/i', $uri, $match)){
            $system->request['act'] = 'index';
            $system->request['args'] = array($match[2]);
        }      
    }

    public function index()
    {     
        $cat_id = $brand_id = $order = $attr_ids = 0;
        $filter = $pager = $attr_values = array();
        //cat-attrs-order-page
        $uri = $this->request['uri'];
        if(preg_match('/product(-([\d\-]+)?)?-(\d+).html/i', $uri, $match)){
            $page = $match[3];
            if($vids = explode('-',trim($match[2], '-'))){
                $cat_id = array_shift($vids);
                $brand_id = $vids ? array_shift($vids) : 0;
                $order = $vids ? array_pop($vids) : 0;
                $attr_values = $vids;
                $attr_ids = implode('-', $vids);
            }
        }else if(preg_match('/product-(\d+).html/i', $uri, $match)){
            $page =$match[1];
        }
        $filter['city_id'] = $this->request['city_id'];
        $filter['audit'] = 1;
        $filter['closed'] = 0;
        if($cat_id = (int)$cat_id){
            if(!$cate = K::M('shop/cate')->cate($cat_id)){
                $this->error(404);
            }
            $this->pagedata['cate'] = $cate;
            $this->pagedata['top_cate'] = $top_cate = K::M('shop/cate')->top_cate($cat_id);
            $filter['cat_id'] = K::M('shop/cate')->children_ids($cat_id);
        }
        if(!$cate['brands'][$brand_id]){
            $brand_id = 0;
        }
        if($brand_id = (int)$brand_id){
            $filter['brand_id'] = $brand_id;
        }
        $this->pagedata['childrens'] = K::M('shop/cate')->childrens($cat_id);
        foreach($attr_values as $k => $v){
            if($v == 0){
                unset($attr_values[$k]);
            }
        }
        if($attr_values){
            $filter['attrs'] = $attr_values;
        }
        $params = array();
        if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $filter['name'] = "LIKE:%{$kw}%";            
        }
        //order {0:默认,1:价格, 2:销量}
        $orderby = null;
        if($order == 1){
            $check['buy'] = '1';
            $orderby = array('buys'=>'DESC', 'books'=>'DESC');
        }else if($order == 2){
            $check['price'] = '1';
            $orderby = array('price'=>'ASC');
        }else{
            $check['default'] = '1';
            $order = 0;
        }
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $pager['cat_id'] = $cat_id;
        $pager['brand_id'] = $brand_id;
        $pager['order'] = $order;
        if($attr_values){
            $pager['attr_values'] = $attr_values;
        }
        if($items = K::M('product/product')->items($filter, $orderby, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mall/product', array($cat_id, $attr_ids, $order, '{page}'), array(), true),array('kw' => $pager['sokw']));
            $this->pagedata['items'] = $items;
            $shop_ids = array();
            foreach($items as $k=>$v){
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }            
        }
        $order_link = array();
        $order_link['default'] = $this->mklink('mall/product', array($cat_id, $brand_id, $attr_ids, 0, 1));
        $order_link['buy'] = $this->mklink('mall/product', array($cat_id, $brand_id, $attr_ids, 1, 1));
        $order_link['price'] = $this->mklink('mall/product', array($cat_id, $brand_id, $attr_ids, 2, 1));
        $pager['order_list'] = $order_link;
        $this->pagedata['check'] = $check;
        $seo = array('cate_title'=>'', 'cate_seo_title'=>'', 'cate_seo_keywords'=>'', 'cate_seo_description'=>'');
        if($cate){
            $seo['cate_title'] = $cate['title'];
            $seo['cate_seo_title'] = $cate['seo_title'];
            $seo['cate_seo_keywords'] = $cate['seo_keywords'];
            $seo['cate_seo_description'] = $cate['seo_description'];
        }
        $this->seo->init('mall_product', $seo);
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mall/product/items.html';
    }

    public function detail($product_id, $page=1)
    {
        if(!$product_id = (int)$product_id){
            $this->error(404);
        }else if(!$detail = K::M('product/product')->detail($product_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add('商品审核中，暂不可访问', 211);
        }else if($shop = $this->check_shop($detail['shop_id'])){
            if($brand_id = $detail['brand_id']){
                $this->pagedata['brand'] = K::M('shop/brand')->brand($brand_id);
            }          
            $pager = array();
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 10;
            $pager['count'] = $count = 0;
            $filter = array('product_id'=>$product_id, 'closed'=>0,'audit'=>1);           
            if($comments = K::M('product/comment')->items($filter,null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($product_id, '{page}')));
                $uids = array();
                foreach($comments as $v){
                    $uids[$v['uid']] = $v['uid'];
                }
                if($uids){
                    $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
                }
                $this->pagedata['comments'] = $comments;
            }

			if($attrs = K::M('product/attr')->attrs_by_product($product_id)){
                $this->pagedata['attrs'] = $attrs;
                $detail['attrvalues'] = array_keys($attrs);
            }
			$access = $this->system->config->get('access');
			$this->pagedata['comment_yz'] = $access['verifycode']['comment'];
            $this->pagedata['product_spec_list'] = K::M('product/spec')->items_by_product($product_id);
            $this->pagedata['product_photo_list'] = K::M('product/photo')->items_by_product($product_id);
            $this->pagedata['detail'] = $detail;
            $seo = array('title'=>$detail['title'], 'cate_name'=>$detail['cate_name'], 'shop_name'=>$shop['name'],'shop_title'=>$shop['title']);
            $this->seo->init('product_detail', $seo);
            if($seo_title = $detail['seo_title']){
                $this->seo->set_title($seo_title);
            }
            if($seo_description = $detail['seo_description']){
                $this->seo->set_description($seo_description);
            }
            if($seo_keywords = $detail['seo_keywords']){
                $this->seo->set_keywords($seo_keywords);
            }
            $this->tmpl = 'mall/product/detail.html';
        }
    }

    public function comment()
    {
        $this->check_login();
        if(!$product_id = $this->GP('product_id')){
            $this->err->add('未定义操作', 211);
        }else if(!$product = K::M('product/product')->detail($product_id, true)){
            $this->err->add('商品不存在或已经删除', 212);
        }else if(empty($product['audit'])){
            $this->err->add('商品还在审核中', 213);
        }else if(!$data = $this->checksubmit('data')){
            $this->err->add('非法的数据提交', 214);
        }else if(!$data = $this->check_fields($data, array('score','content'))){
            $this->err->add('非法的数据提交', 214);
        }else if(($allow_comment = K::M('member/group')->check_priv($this->MEMBER['group_id'], 'allow_comment')) < 0) {
            $this->err->add('很抱歉您所在的用户组没有权限发表商品评论', 201);
        }else {
			$verifycode_success = true;
			$access = $this->system->config->get('access');
			if($access['verifycode']['comment']){
				if(!$verifycode = $this->GP('verifycode')){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}else if(!K::M('magic/verify')->check($verifycode)){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}
			}
			if($verifycode_success){
				$data['product_id'] = $product_id;
				$data['shop_id'] = $product['shop_id'];
				$data['uid'] = $this->uid;
				$data['audit'] = $allow_comment;
				$data['city_id'] = $product['city_id'];
				if($comment_id = K::M('product/comment')->create($data)){
					$this->err->add('发表评价成功');
				}
			}
        }
    }

    public function book($product_id=null)
    {
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->error(404);
        }else if(!$product = K::M('product/product')->detail($product_id, true)){
            $this->error(404);
        }else if(empty($product['audit'])){
            $this->err->add('商品审核中', 211);   
        }else{
            $shop = $this->check_shop($product['shop_id']);
            $this->pagedata['product'] = $product;
            if(!$mobile = $this->system->cookie->get('LAST_Mobile')){
                $mobile = $this->MEMBER['mobile'];
            }
            $pager['mobile'] = $mobile;
            if($contact = $this->system->cookie->get('LAST_Contact')){
                $pager['contact'] = $contact;
            }
            $this->pagedata['pager'] = $pager;            
            $this->tmpl = 'view:book/product.html';
        }
    }       

}