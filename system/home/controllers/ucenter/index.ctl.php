<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: member.ctl.php 5937 2014-07-28 01:04:06Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Index extends Ctl_Ucenter 
{
    
    protected $_allow_fields = 'mail,gender,from,mobile,Y,M,D,city_id,realname';
    public function index()
    {
        $this->pagedata['order_count'] = K::M('trade/order')->count_by_uid($this->uid);
        $this->pagedata['yuyue_company_count'] = K::M('company/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['yuyue_designer_count'] = K::M('designer/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['yuyue_mechanic_count'] = K::M('mechanic/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['yuyue_shop_count'] = K::M('shop/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['tenders_count'] = K::M('tenders/tenders')->count(array('uid'=>$this->uid));
        $this->tmpl = 'ucenter/member/index.html';
    }

}