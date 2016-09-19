<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mall_Coupon extends Ctl 
{
    
    public function index()
    {
        $this->error(404);
    }

    public function items($type=0, $page=null)
    {
        
		$pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 12;
		$pager['type'] = $type;
		$filter['audit'] = 1;
        $filter['closed'] = 0;
		if($type == 1){
			$filter[':OR'] = array('ltime'=>'>:'.__TIME,'ltime'=>'0');
		}else if($type == 2){
			$filter[':OR'] = array('ltime'=>'<:'.__TIME,'ltime'=>'>:0');
		}
		if($items = K::M('shop/coupon')->items($filter,null,$page,$limit,$count)){
			$pager['pagebar'] = $this->mkpage($count, $limit,$page,$this->mklink('mall/coupon:items',array($type, '{page}')));
            $this->pagedata['items'] = $items;
		}
		$this->pagedata['pager'] = $pager;
        $this->seo->init('coupon', array('page'=>($page > 1) ? $page : 1));
        $this->tmpl = 'mall/coupon/items.html';
    }

    public function detail($coupon_id=null)
    {
        if(!$coupon_id = (int)$coupon_id){
            $this->error(404);
        }else if(!$detail = K::M('shop/coupon')->detail($coupon_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add('商品审核中', 211);
        }else{
            $shop = $this->check_shop($detail['shop_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'mall/coupon/detail.html';
            $this->seo->set_shop($shop);
            $seo = array('title'=>$detail['title'], 'coupon_desc'=>'');
            $seo['coupon_desc'] = K::M('content/text')->substr(K::M('content/html')->text($detail['content'], true), 0, 200);
            $this->seo->init('coupon_detail', $seo);
        }
    }

    public function download($coupon_id=null)
    {
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = (int)$this->GP('coupon_id'))){
            $this->error(404);
        }else if(!$coupon = K::M('shop/coupon')->detail($coupon_id)){
            $this->error(404);
        }else if(empty($coupon['audit'])){
            $this->err->add('优惠券审核中', 211);
        }else if($data = $this->checksubmit('data')){
            $shop = $this->check_shop($coupon['shop_id']);
			$data['city_id'] = $shop['city_id'];
            if(K::M('shop/coupon')->download($coupon_id, $data['mobile'], $data['contact'],$shop['city_id'], $this->uid)){
                $this->err->add('优惠券下载成功，短息会在2分钟内到达');
                $this->system->cookie->set('LAST_Mobile', $data['mobile']);
                $this->system->cookie->set('LAST_Contact', $data['contact']);
            }
        }else{
            $shop = $this->check_shop($coupon['shop_id']);
            if(!$mobile = $this->system->cookie->get('LAST_Mobile')){
                $mobile = $this->MEMBER['mobile'];
            }
            $pager['mobile'] = $mobile;
            if($contact = $this->system->cookie->get('LAST_Contact')){
                $pager['contact'] = $contact;
            }
            $this->pagedata['pager'] = $pager;
            $this->pagedata['coupon'] = $coupon;
            $this->tmpl = 'view:coupon/download.html';
        }
    }
}