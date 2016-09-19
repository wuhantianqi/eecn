<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
class Ctl_Mobile_Ucenter_Coupon extends Ctl_Mobile_Ucenter
{
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
        $pager['backurl'] = $this->mklink('mobile/ucenter');
		$this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/ucenter/sign/coupon_list.html';
	}
}
