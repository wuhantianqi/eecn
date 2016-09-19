<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Member_Yuyue extends Ctl_Ucenter 
{
    
    protected $_allow_tenders_fields = 'area_id,title,contact,mobile,home_id,home_name,way_id,type_id,style_id,budget_id,service_id,house_type_id,house_mj,addr,comment,zx_time';
    public function tenders($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('tenders/tenders')->items($filter, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('yuyue:tenders', array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/member/yuyue/tenders.html';
    }

    public function tendersDetail($tenders_id=null)
    {
        if(!$tenders_id = (int)$tenders_id){
            $this->error(404);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('查看的招标不存在或已经删除', 211);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该招标', 212);
        }else{
            if($look_list = K::M('tenders/look')->items_by_tenders($tenders_id)){
                $uids = array();
                foreach($look_list as $k=>$v){
                    $uids[$v['uid']] = $v['uid'];
                }
                $this->pagedata['look_list'] = $look_list;
                if($uids){
                    if($member_list = K::M('member/member')->items_by_ids($uids)){
                        $gz_uids = $designer_uids = $mechanic_uids = $company_uids = $shop_uids = array();
                        foreach($member_list as $v){
                            switch($v['from']){
                                case 'company'  : $company_uids[$v['uid']]  = $v['uid']; break;
                                case 'designer' : $designer_uids[$v['uid']] = $v['uid']; break;
                                case 'gz'       : $gz_uids[$v['uid']]       = $v['uid']; break;
                                case 'mechanic' : $mechanic_uids[$v['uid']] = $v['uid']; break;
                                case 'shop'     : $shop_uids[$v['uid']]     = $v['uid']; break;
                            }
                        }
                        if($gz_uids){
                            $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_uids);
                        }
                        if($designer_uids){
                            $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_uids);
                        }
                        if($mechanic_uids){
                            $this->pagedata['mechanic_list'] = K::M('mechanic/mechanic')->items_by_ids($mechanic_uids);
                        }
                        if($company_uids){
                            $this->pagedata['company_list'] = K::M('company/company')->items_by_uids($company_uids);
                        }
                        if($shop_uids){
                            $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_uids($shop_uids);
                        }
                        $this->pagedata['member_list'] = $member_list;
                    }
                }
            }
            $this->pagedata['pager'] = $pager;
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/tendersDetail.html';
        }
    }
    
    public function tendersEdit($tenders_id=null)
    {
        if(!($tenders_id = (int)$tenders_id) && !($tenders_id = (int)$this->GP('tenders_id'))){
            $this->error(404);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('查看的招标不存在或已经删除', 211);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限操作该招标', 212);
        }else if($data = $this->checksubmit('data')){
            if(!$this->check_fields($data, $this->_allow_tenders_fields)){
                $this->err->add('非法的数据提交', 213);
            }
            if($attach = $_FILES['huxing']){
                $upload = K::M('magic/upload');
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = $upload->upload($attach, 'tenders', $detail['hunxing'])){
                        $data['huxing'] = $a['photo'];
                    }
                }
            }
            if(K::M('tenders/tenders')->update($tenders_id, $data)){
                $this->err->add('完善招标信息成功');
            }
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/tendersEdit.html';
        }        
    }

    public function signLook($look_id)
    {
        if(!$look_id = (int)$look_id){
            $this->error(404);
        }else if(!$look = K::M('tenders/look')->detail($look_id)){
            $this->err->add('竞标不存在或已经删除', 211);
        }else if(!$tenders = K::M('tenders/tenders')->detail($look['tenders_id'])){
            $this->err->add('招标不存或已经删除', 212);
        }else if($tenders['uid'] != $this->uid){
            $this->err->add('你没有权限操作该招标信息', 213);
        }else if(empty($tenders['audit'])){
            $this->err->add('该招标还在审核中，不可操作', 214);
        }else if($tenders['sign_uid']){
            $this->err->add('已经有中标者，不可重复设置', 215);
        }else if(!$member = K::M('member/member')->detail($look['uid'])){
			$this->err->add('该投标用户不存在', 216);
		}else if(K::M('tenders/look')->sign($look_id)){
			 switch ($member['from']) {
				case 'gz':
					K::M('gz/gz')->update_count($look['uid'], 'tenders_sign'); break;
				case 'designer':
					K::M('designer/designer')->update_count($look['uid'], 'tenders_sign'); break;
				case 'mechanic':
					K::M('mechanic/mechanic')->update_count($look['uid'], 'tenders_sign'); break;
				case 'company':
					$company = K::M('company/company')->items(array('uid'=>$look['uid']));
					foreach($company as $k => $v){
						$this->company['company_id'] = $v['company_id'];
					}
					K::M('company/company')->update_count($this->company['company_id'], 'tenders_sign'); 
				case 'shop':
					$shop = K::M('shop/shop')->items(array('uid'=>$look['uid']));
					foreach($shop as $k => $v){
						$this->company['shop_id'] = $v['shop_id'];
					}
					K::M('shop/shop')->update_count($this->shop['shop_id'], 'tenders_sign'); break;
			}
            if($zxb_id = (int)$tenders['zxb_id']){
                if(!$company = K::M('company/company')->company_by_uid($look['uid'])){
                    $this->err->add('只装修公司才能参加装修保', 216);
                }else{
                    K::M('zxb/zxb')->sign_company($zxb_id, $company['company_id']);
                }
            }
            $this->err->add('设置中标成功');
        }
    }

    public function company($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('company/yuyue')->items($filter, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $company_ids = array();
            foreach($items as $k=>$v){
                $company_ids[$v['company_id']] = $v['company_id'];
            }
            if($company_ids){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/member/yuyue/company.html';
    }

    public function companyDetail($yuyue_id=null)
    {
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error(404);
        }else if(!$detail = K::M('company/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            if($detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($detail['company_id']);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/companyDetail.html';
        }
    }

    public function designer($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('designer/yuyue')->items($filter, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $designer_ids = array();
            foreach($items as $k=>$v){
                $designer_ids[$v['designer_id']] = $v['designer_id'];
            }
            if($designer_ids){
                $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/member/yuyue/designer.html';
    }

    public function designerDetail($yuyue_id=null)
    {
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error(404);
        }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            if($detail['designer_id']){
                $this->pagedata['designer'] = K::M('designer/designer')->detail($detail['designer_id']);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/designerDetail.html';
        }
    }

	public function gz($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('gz/yuyue')->items($filter, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $gz_ids = array();
            foreach($items as $k=>$v){
                $gz_ids[$v['gz_id']] = $v['gz_id'];
            }
            if($gz_ids){
                $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/member/yuyue/gz.html';
    }

    public function gzDetail($yuyue_id=null)
    {
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error(404);
        }else if(!$detail = K::M('gz/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            if($detail['gz_id']){
                $this->pagedata['gz'] = K::M('gz/gz')->detail($detail['gz_id']);
            }
			
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/gzDetail.html';
        }
    }

    public function mechanic($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('mechanic/yuyue')->items($filter, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $mechanic_ids = array();
            foreach($items as $k=>$v){
                $mechanic_ids[$v['mechanic_id']] = $v['mechanic_id'];
            }
            if($mechanic_ids){
                $this->pagedata['mechanic_list'] = K::M('mechanic/mechanic')->items_by_ids($mechanic_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/member/yuyue/mechanic.html';
    }

    public function mechanicDetail($yuyue_id=null)
    {
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error(404);
        }else if(!$detail = K::M('mechanic/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            if($detail['mechanic_id']){
                $mechanic = K::M('mechanic/mechanic')->detail($detail['mechanic_id']);
                $mechanic['attrvalues'] = K::M('mechanic/attr')->attrs_ids_by_mechanic($mechanic['mechanic_id']);
                $this->pagedata['mechanic'] = $mechanic;
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/mechanicDetail.html';
        }
    }

    public function shop($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('shop/yuyue')->items($filter, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
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
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/member/yuyue/shop.html';
    }

    public function shopDetail($yuyue_id=null)
    {
        if(!$yuyue_id = (int)$yuyue_id){
            $this->err->add('未指定要查看的预约', 211);
        }else if(!$detail = K::M('shop/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            if($detail['product_id']){
                $this->pagedata['product'] = K::M('product/product')->detail($detail['product_id']);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/member/yuyue/shopDetail.html';
        }        
    }

}