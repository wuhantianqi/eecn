<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Coupon extends Ctl_Mobile
{
    public function index($coupon_id)
	{
		$this->detail($coupon_id);
	}

	public function detail($coupon_id)
    {
        if (!($coupon_id = (int) $coupon_id) && !($coupon_id = (int) $this->GP('coupon_id'))) {
            $this->err->add('参数错误', 211);
        }else if (!$detail = K::M('shop/coupon')->detail($coupon_id)) {
            $this->err->add('该优惠券不存在', 212);
        }else if (!$detail['audit']) {
            $this->err->add('尊敬的用户优惠券正在审核中！', 215);
        }else{
			$this->pagedata['detail'] = $detail;
			$pager['backurl'] = $this->mklink('mobile');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/coupon/detail.html'; 
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
        }else{
			if($data = $this->checksubmit('data')){
				$shop = $this->check_shop($coupon['shop_id']);
				if(K::M('shop/coupon')->download($coupon_id, $data['mobile'], $data['contact'], $this->uid)){
					$this->err->add('优惠券下载成功');
					$this->system->cookie->set('LAST_Mobile', $data['mobile']);
					$this->system->cookie->set('LAST_Contact', $data['contact']);
					$this->err->set_data('forward', $this->mklink('mobile/ucenter/sign:coupon'));
				}
			}else{
				
				$pager['tender_hide'] = 1;
				$shop = $this->check_shop($coupon['shop_id']);
				if(!$mobile = $this->system->cookie->get('LAST_Mobile')){
					$mobile = $this->MEMBER['mobile'];
				}
				$pager['mobile'] = $mobile;
				if($contact = $this->system->cookie->get('LAST_Contact')){
					$pager['contact'] = $contact;
				}
				$pager['backurl'] = $this->mklink('mobile/coupon-detail',array($coupon_id));
				$this->pagedata['pager'] = $pager;
				$this->pagedata['coupon_id'] = $coupon_id;
				$this->tmpl = 'mobile/coupon/download.html';
			}
		}
    }
}