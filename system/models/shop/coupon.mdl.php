<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: coupon.mdl.php 5748 2014-07-01 04:29:08Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Coupon extends Mdl_Table
{   
  
    protected $_table = 'shop_coupon';
    protected $_pk = 'coupon_id';
    protected $_cols = 'coupon_id,shop_id,city_id,area_id,title,photo,money,min_amount,content,stime,ltime,views,downloads,orderby,audit,closed,dateline';
    protected $_orderby = array('orderby'=>'ASC', 'coupon_id'=>'DESC');
    
    protected $_hot_orderby = array('downloads'=>'DESC','orderby'=>'ASC');
    protected $_hot_filter = array('audit'=>'1', 'closed'=>0);
    protected $_new_orderby = array('coupon_id'=>'DESC');
    protected $_new_filter = array('audit'=>'1', 'closed'=>0);

    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function items_by_shop($shop_id, $p=1, $l=50, &$count=0)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $filter = array('shop_id'=>$shop_id, 'audit'=>1, 'closed'=>0);
        return $this->items($filter, null, $p, $l, $count);
    }

    public function format_items_ext($items)
    {
        if(empty($items)){
            return false;
        }
        $shop_ids = $shop_list = array();
        foreach((array)$items as $k=>$v){
            if($v['shop_id']){
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
        }
        if($shop_ids){
            $shop_list = K::M('shop/shop')->items_by_ids($shop_ids);
        }
        foreach((array)$items as $k=>$v){
            if(!$shop = $shop_list[$v['shop_id']]){
                $shop = array();
            }
            $v['ext'] = array('shop'=>$shop);
            $items[$k] = $v;            
        }
        return $items;
    }    

    public function download($coupon_id, $mobile, $contact='',$city_id, $uid=0)
    {
        if(!$coupon_id = (int)$coupon_id){
            return false;
        }else if(!K::M('verify/check')->mobile($mobile)){
            $this->err->add('手机号码不正确', 452);
        }else if(!$coupon = $this->detail($coupon_id)){
            $this->err->add('优惠券不存在', 453);
        }else if($coupon['ltime'] && $coupon['ltime'] < __CFG::TIME){
            $this->err->add('优惠券已经过期', 454);
        }else if(!$shop = K::M('shop/shop')->detail($coupon['shop_id'], true)){
            $this->err->add('商铺不存在或已经删除', 454);
        }else{
            $data = array('coupon_id'=>$coupon_id, 'shop_id'=>$coupon['shop_id']);
            $data['number'] = $number = rand(100000, 999999);
            $data['mobile'] = $mobile;
            $data['contact'] = $contact;
            $data['uid'] = (int)$uid;
			$data['city_id'] = (int)$city_id;
            if(K::M('shop/couponDownload')->create($data)){
                $this->update_count($coupon_id, 'downloads', 1);
                $smsdata = array();
                $smsdata['number'] = $number;
                $smsdata['coupon'] = $coupon['title'];
                $smsdata['shop_title'] = $shop['title'];
                $smsdata['shop_name'] = $shop['shop_name'];
                $smsdata['expire_time'] = $coupon['ltime'] ? date('Y-m-d', $coupon['ltime']) : '不限时间';;
                $smsdata['contact'] = $contact;
                $smsdata['mobile'] = $mobile;                
                K::M('sms/sms')->send($mobile, 'yezhu_coupon', $smsdata);
                K::M('sms/sms')->shop($shop, 'shop_coupon', $smsdata);
                $maildata = $smsdata;
                K::M('helper/mail')->sendshop($shop, 'shop_coupon', $maildata);
                return true;                
            }            
        }
        return false;
        
    }

    protected function _format_row($row)
    {
        $row['expire'] = false;
        if($row['stime'] > __CFG::TIME){
            $row['expire_label'] = date('Y-m-d', $row['stime']).'开始';
        }else if($row['ltime'] > __CFG::TIME){
            $row['expire_label'] = date('Y-m-d', $row['ltime']).'结束';
        }else if(empty($row['ltime'])){
            $row['expire_label'] = '永久有效';
        }else{
            $row['expire'] = true;
            $row['expire_label'] = '已经过期'; 
        }
        return $row;
    }

    protected function _check($data, $coupon_id=null)
    {
        if(isset($data['stime'])){
            $data['stime'] = $data['stime'] ? strtotime($data['stime']) : 0;
        }
        if(isset($data['ltime'])){
            $data['ltime'] = $data['ltime'] ? strtotime($data['ltime']) + 86399 : 0;
        }
        return parent::_check($data, $coupon_id);
    }
}