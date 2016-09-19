<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Shop_Coupon extends Ctl_Ucenter 
{
    
    protected $_allow_fields = 'area_id,title,photo,money,min_amount,content,stime,ltime';

    public function index($page=1)
    {
        $shop = $this->ucenter_shop();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('shop_id'=>$shop['shop_id'], 'closed'=>0);
        if($items = K::M('shop/coupon')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/shop/coupon/items.html';           
    }

    public function create()
    {
        $shop = $this->ucenter_shop();        
        if(K::M('member/integral')->check('coupon',  $this->MEMBER) === false){
            $this->err->add('很抱歉您的账户余额不足！', 201);
        }else if($data = $this->checksubmit('data')){
            $allow_coupon = K::M('member/group')->check_priv($shop['group_id'], 'allow_coupon');
            if($allow_coupon < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限添加优惠券', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['coupon_photo']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'shop')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                $data['shop_id'] = $shop['shop_id'];
                $data['city_id'] = $shop['city_id'];
                $data['area_id'] = $data['area_id'] ? $data['area_id'] : $shop['area_id'];
                $data['audit'] = $allow_coupon;
                if($coupon_id = K::M('shop/coupon')->create($data)){                    
                    K::M('system/integral')->commit('coupon',  $this->MEMBER, '添加优惠券');
                    $this->err->add('添加优惠券成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/shop/coupon:index'));
                }
            } 
        }else{
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/shop/coupon/create.html';
        }
    }

    public function edit($coupon_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = (int)$this->GP('coupon_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/coupon')->detail($coupon_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($detail['shop_id'] != $shop['shop_id']){
            $this->err->add('您没有权限修改该内容', 212);
        }else if($data = $this->checksubmit('data')){
            if(K::M('member/group')->check_priv($shop['group_id'], 'allow_coupon') < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限修改优惠券', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($attach = $_FILES['coupon_photo']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'shop')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                if(K::M('shop/coupon')->update($coupon_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/shop/coupon/edit.html';
        }       
    }

    public function delete($coupon_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('未指要删除的优惠券', 211);
        }else if(!$detail = K::M('shop/coupon')->detail($coupon_id)){
            $this->err->add('你要删除的优惠券不存或已经删除', 212);
        }else if($shop['shop_id'] != $detail['shop_id']){
            $this->err->add('您没有权限删除该优惠券', 213);
        }else{
            K::M('shop/coupon')->delete($coupon_id);
            $this->err->add('删除优惠券成功');
        }
    } 

    public function downloads($page=1)
    {
        $shop = $this->ucenter_shop();
        $filter = $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        if($items = K::M('shop/couponDownload')->items_by_shop($shop['shop_id'], $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
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
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/shop/coupon/downloads.html';             
    }

    public function downloadDetail($download_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!$download_id = (int)$download_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('shop/couponDownload')->detail($download_id)){
            $this->err->add('内容不存在或已经删除', 212);
        }else if($detail['shop_id'] != $shop['shop_id']){
            $this->err->add('您没有权限操作该条信息', 213);
        }else{
            $this->pagedata['coupon'] = K::M('shop/coupon')->detail($detail['coupon_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/shop/coupon/downloadDetail.html';
        }     
    }

    public function downloadSave()
    {
        $shop = $this->ucenter_shop();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'used,status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$download_id = (int)$this->GP('download_id')){
                $this->err->add('未指定要保存的ID', 211);
            }else if(!$detail = K::M('shop/couponDownload')->detail($download_id)){
                $this->err->add('内容不存在或已经删除', 212);
            }else if($detail['shop_id'] != $shop['shop_id']){
                $this->err->add('您没有权限操作该条信息', 213);
            }else if(K::M('shop/couponDownload')->update($download_id, $data)){
                $this->err->add('更新状态成功');
            }
        }
    }     
}