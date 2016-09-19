<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
class Ctl_Mobile_Ucenter_Yuyue extends Ctl_Mobile_Ucenter
{
	
	
	public function yuyue()
	{
		$pager['backurl'] = $this->mklink('mobile/ucenter');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/yuyue/yuyue.html';
	}

	public function yuyueCompany()
	{
        $filter['uid'] = $this->uid;
        if($items = K::M('company/yuyue')->items($filter)){
            $company_ids = array();
            foreach($items as $k=>$v){
                $company_ids[$v['company_id']] = $v['company_id'];
            }
            if($company_ids){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            }
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/yuyue-yuyue');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/yuyue/yuyueCompany.html';
	}

	public function yuyueDesigner()
	{
        $filter['uid'] = $this->uid;
        if($items = K::M('designer/yuyue')->items($filter)){
            $designer_ids = array();
            foreach($items as $k=>$v){
                $designer_ids[$v['designer_id']] = $v['designer_id'];
            }
            if($designer_ids){
                $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
            }
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/yuyue-yuyue');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/yuyue/yuyueDesigner.html';
	}

	public function yuyueGz()
	{
        $filter['uid'] = $this->uid;
        if($items = K::M('gz/yuyue')->items($filter)){
            $gz_ids = array();
            foreach($items as $k=>$v){
                $gz_ids[$v['gz_id']] = $v['gz_id'];
            }
            if($gz_ids){
                $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
            }
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/yuyue-yuyue');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/yuyue/yuyueGz.html';
	}

	public function yuyueMechanic()
    {
        $filter['uid'] = $this->uid;
        if($items = K::M('mechanic/yuyue')->items($filter)){
            $mechanic_ids = array();
            foreach($items as $k=>$v){
                $mechanic_ids[$v['mechanic_id']] = $v['mechanic_id'];
            }
            if($mechanic_ids){
                $this->pagedata['mechanic_list'] = K::M('mechanic/mechanic')->items_by_ids($mechanic_ids);
            }
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/yuyue-yuyue');
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'mobile/ucenter/yuyue/yuyueMechanic.html';
    }

	public function yuyueShop()
    { 
        $filter['uid'] = $this->uid;
        if($items = K::M('shop/yuyue')->items($filter)){
            $shop_ids = $product_ids = array();
            foreach($items as $k=>$v){
                $shop_ids[$v['shop_id']] = $v['shop_id'];
                if($v['product_id']){
                    $product_ids[$v['product_id']] = $v['product_id'];
                }
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
            if($product_ids){
                $this->pagedata['product_list'] = K::M('product/product')->items_by_ids($product_ids);
            }
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/yuyue-yuyue');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/ucenter/yuyue/yuyueShop.html';
    }

}
