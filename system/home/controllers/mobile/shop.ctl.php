<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Shop extends Ctl_Mobile
{
  
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/shop-([\d]+).html/i', $uri, $match)){
            $system->request['act'] = 'detail';
            $system->request['args'] = array($match[1]);
        }      
    }

    public function index($shop_id)
    {
        $this->detail($shop_id);
    }

    public function detail($shop_id)
    {
        $this->check_shop($shop_id);
		$pager['backurl'] = $this->mklink('mobile/store');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/shop/detail.html';
    }

    public function product($shop_id, $vcat_id=0, $page=1)
    {
        $shop = $this->check_shop($shop_id);
        $filter = $pager = array();
        $vcat_id = (int)$vcat_id;
        if($page === null && $vcat_id){
            $pager = $vcat_id;
            $vcat_id = 0;
        }
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter['shop_id'] = $shop_id;
		if($vcat_id){
            $filter['vcat_id'] = $vcat_id;
        }
        $filter['audit'] = 1;
        $filter['closed'] = 0;
        if($items = K::M('product/product')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/shop:product', array($shop_id, $vcat_id,'{page}')));
            $this->pagedata['items'] = $items;
        }
		$pager['vcat_id'] = $vcat_id;
        $pager['backurl'] = $this->mklink('mobile/shop', array($shop_id));
		//var_dump($pager);echo "File:", __FILE__, ',Line:',__LINE__;exit;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/shop/product.html';
    }


    public function coupon($shop_id, $page)
    {
        $shop = $this->check_shop($shop_id);
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;        
        if($items = K::M('shop/coupon')->items_by_shop($shop_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/shop:coupon', array($shop_id, '{page}')));
            $this->pagedata['items'] = $items;
        }        
        $pager['backurl'] = $this->mklink('mobile/shop', array($shop_id));
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/shop/coupon.html';        
    }

    public function news($shop_id, $page)
    {
        $shop = $this->check_shop($shop_id);
        if($items = K::M('shop/news')->items_by_shop($shop_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/shop:news', array($shop_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $pager['backurl'] = $this->mklink('mobile/shop', array($shop_id));
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/shop/news.html';
    }

    public function newsdetail($news_id)
    {
        if(!$news_id = (int)$news_id){
            $this->error(404);
        }else if(!$detail = K::M('shop/news')->detail($news_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add('内容还未发布，暂不能访问', 211);
        }else if($shop = $this->check_shop($detail['shop_id'])){
            K::M('shop/news')->update_count($news_id, 'views', 1);
            $this->pagedata['detail'] = $detail;
            $pager['backurl'] = $this->mklink('mobile/shop:news', array($detail['shop_id'], 1));
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/shop/newsdetail.html';
        }
    }

    public function yuyue($shop_id=null)
    {
        if(!($shop_id = (int)$shop_id) && !($shop_id = (int)$this->GP('shop_id'))){
            $this->error(404);
        }else if($data = $this->checksubmit('data')){
            $shop = $this->check_shop($shop_id);
            $product = array();
            if($product_id = (int)$data['product_id']){
                if($product = K::M('product/product')->detail($product_id)){
                    if($product['shop_id'] = $shop_id){
                        $data['product_id'] = $product_id;
                    }
                }
            }
            if(empty($product)){
                unset($data['product_id']);
            }
            $data['shop_id'] = $shop_id;
            $data['uid'] = (int)$this->uid;
            if(K::M('shop/yuyue')->create($data)){
                $smsdata = $maildata = array('contact'=>$data['contact'], 'mobile'=>$data['mobile'], 'shop_phone'=>$shop['phone'], 'shop_name'=>$shop['name'], 'shop_url'=>$shop['shop_url']);
                if($product){
                    $smsdata['product_name'] = $product['name'];
                    $maildata['product_name'] = $product['name'];
                    K::M('sms/sms')->send($data['mobile'], 'product_yuyue', $smsdata);
                    K::M('sms/sms')->shop($shop, 'product_tongzhi', $smsdata);
                    K::M('helper/mail')->sendshop($shop, 'product_yuyue', $maildata);
                }else{
                    K::M('sms/sms')->send($data['mobile'], 'shop_yuyue', $smsdata);
                    K::M('sms/sms')->shop($shop, 'shop_tongzhi', $smsdata);
                    K::M('helper/mail')->sendshop($shop, 'shop_yuyue', $maildata);
                }
                $this->err->add('预约成功，稍后商家会与您取得联系');
                $this->err->set_data('forward', $this->mklink('mobile/shop', array($shop_id)));
                $this->system->cookie->set('LAST_Mobile', $data['mobile']);
                $this->system->cookie->set('LAST_Contact', $data['contact']);
            }
        }else{
			
			$pager['tender_hide'] = 1;
            $shop = $this->check_shop($shop_id);
            if(!$mobile = $this->system->cookie->get('LAST_Mobile')){
                $mobile = $this->MEMBER['mobile'];
            }
            $pager['mobile'] = $mobile;
            if($contact = $this->system->cookie->get('LAST_Contact')){
                $pager['contact'] = $contact;
            }
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/shop/yuyue.html';
        }        
    }
}