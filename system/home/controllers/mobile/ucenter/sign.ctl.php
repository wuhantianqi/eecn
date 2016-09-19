<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
class Ctl_Mobile_Ucenter_Sign extends Ctl_Mobile_Ucenter
{
	public function sign()
	{
		$pager['backurl'] = $this->mklink('mobile/ucenter');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/sign/sign.html';
	}

	public function signYouhui()
	{
		$filter['uid'] = $this->uid;
        if($items = K::M('company/sign')->items($filter)){
            $youhui_ids = array();
            foreach($items as $k=>$v){
                $youhui_ids[$v['youhui_id']] = $v['youhui_id'];
            }
            if($youhui_ids){
                $this->pagedata['youhui_list'] = K::M('company/youhui')->items_by_ids($youhui_ids);
            }
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/sign-sign');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/sign/signYouhui.html';
	}

	public function signActivity()
	{
		$filter['uid'] = $this->uid;
        if($items = K::M('activity/sign')->items($filter)){
            $activity_ids = array();
            foreach($items as $k=>$v){
                $activity_ids[$v['activity_id']] = $v['activity_id'];
            }
            if($activity_ids){
                $this->pagedata['activity_list'] = K::M('activity/activity')->items_by_ids($activity_ids);
            }
            $this->pagedata['items'] = $items;
        }
		
		$pager['backurl'] = $this->mklink('mobile/ucenter/sign-sign');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/sign/signActivity.html';
	}

	public function coupon()
	{
        $filter['uid'] = $this->uid;
        if($items = K::M('shop/couponDownload')->items($filter,array('download_id'=>'desc'))){
            $this->pagedata['items'] = $items;
            $coupon_ids = array();
            foreach($items as $k=>$v){
                $coupon_ids[$v['coupon_id']] = $v['coupon_id'];
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            if($coupon_ids){
                $this->pagedata['coupon_list'] = K::M('shop/coupon')->items_by_ids($coupon_ids);
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
        }
        $pager['backurl'] = $this->mklink('mobile/ucenter/sign-sign');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/sign/coupon.html';
	}

	public function coupon_list()
	{
		$shop = $this->ucenter_shop();
        if($items = K::M('shop/couponDownload')->items_by_shop($shop['shop_id'])){
            $uids = $coupon_ids = array();
            foreach($items as $k=>$v){
                $coupon_ids[$v['coupon_id']] = $v['coupon_id'];
                if($uid = $v['uid']){
                    $uids[$v['uid']] = $v['uid'];
                }
            }
            if($coupon_ids){
                $this->pagedata['coupon_list'] = K::M('shop/coupon')->items_by_ids($coupon_ids);
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $this->pagedata['items'] = $items;
        }
        $pager['backurl'] = $this->mklink('mobile/ucenter/sign-sign');
		$this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/ucenter/sign/coupon_list.html';
	}
}
