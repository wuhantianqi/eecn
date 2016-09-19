<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Product extends Ctl_Mobile 
{
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/product-([\d]+)(\.html)?/i', $uri, $match)){
            $system->request['act'] = 'index';
            $system->request['args'] = array($match[1]);
        }      
    }    

    public function index($cat_id=null,$page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 6;
        $filter['city_id'] = $this->request['city_id'];
        $filter['audit'] = 1;$filter['closed'] = 0;$filter['cat_id'] = $cat_id;
		if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $filter['name'] = "LIKE:%{$kw}%";            
        }
        if ($items = K::M('product/product')->items($filter, null, $page, $limit, $count)) {
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('cat_id'=>$cat_id,'page'=>'{page}'), array(), true),array('kw' => $pager['sokw']));
        }
		
		$this->pagedata['cate_list'] = $cate_list = K::M('shop/cate')->fetch_all();
		if($cat_id){
			foreach($cate_list as $k => $v){
				if($v['cat_id'] == $cat_id){
					if($v['parent_id'] == '0'){
						$this->pagedata['title1'] = $v['title'];
						$this->pagedata['cat_id1'] = $cat_id;
					}else if($v['parent_id'] != 0){
						if($cate_list[$v['parent_id']]['parent_id'] == 0){
							$this->pagedata['title1'] = $cate_list[$v['parent_id']]["title"];
							$this->pagedata['title2'] = $v["title"];
							$this->pagedata['cat_id1'] = $v['parent_id'];
							$this->pagedata['cat_id2'] = $cat_id;
						}else{
							$this->pagedata['title1'] = $cate_list[$cate_list[$v['parent_id']]["parent_id"]]["title"];
							$this->pagedata['title2'] = $cate_list[$v['parent_id']]["title"];
							$this->pagedata['title3'] = $v["title"];
							$this->pagedata['cat_id1'] = $cate_list[$v['parent_id']]["parent_id"];
							$this->pagedata['cat_id2'] =$v['parent_id'];
						}
					}
				}
				
			}
		}
        $this->pagedata['items'] = $items;
		$pager['backurl'] = $this->mklink('mobile');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/product/items.html';
    }

    public function detail($product_id)
    {
        if(!$product_id = (int)$product_id){
            $this->error(404);
        }else if(!$detail = K::M('product/product')->detail($product_id)){
            $this->error(404);
        }else if($detail['closed'] == 1){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else{
            $this->pagedata['detail'] = $detail;
			$this->pagedata['fields'] = K::M('product/fields')->detail($product_id);
            $this->pagedata['product_spec_list'] = K::M('product/spec')->items_by_product($product_id);
            $this->pagedata['product_photo_list'] = K::M('product/photo')->items_by_product($product_id);
			$pager['backurl'] = $this->mklink('mobile/product');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/product/detail.html';
		}
    }

    public function yuyue($product_id)
    {
        if(!($product_id = (int)$product_id) && !($product_id = (int)$this->GP('product_id'))){
            $this->error(404);
        }else if(!$product = K::M('product/product')->detail($product_id, true)){
            $this->error(404);
        }else if(empty($product['audit'])){
            $this->err->add('商品审核中', 211);   
        }else{
			
			$pager['tender_hide'] = 1;
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
            $this->tmpl = 'mobile/product/yuyue.html';
        }        
    }
}