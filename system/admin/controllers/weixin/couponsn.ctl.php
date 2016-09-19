<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Couponsn extends Ctl
{
    
    public function items($coupon_id,$page=1)
    {
		if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('没有指定优惠券ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('该优惠券不存在或已经删除', 212);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
			$filter['coupon'] = $coupon_id;
			if($items = K::M('weixin/couponsn')->items($filter, null, $page, $limit, $count)){
				$uids = '';
				foreach($items as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
				$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
			}
			$this->pagedata['items'] = $items;
			$this->pagedata['detail'] = $detail;
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'admin:weixin/couponsn/items.html';
		}
    }

    public function create($coupon_id)
    {
		if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('没有指定优惠券ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('该优惠券不存在或已经删除', 212);
        }else{
			if($data = $this->checksubmit('data')){
				$data['coupon'] = $coupon_id;
				if($sn_id = K::M('weixin/couponsn')->create($data)){
					$this->err->add('添加内容成功');
					$this->err->set_data('forward', '?weixin/couponsn-items-'.$coupon_id.'.html');
				} 
			}else{
				$this->pagedata['coupon_id'] = $coupon_id;
			   $this->tmpl = 'admin:weixin/couponsn/create.html';
			}
		}
    }

    public function edit($sn_id=null)
    {
        if(!($sn_id = (int)$sn_id) && !($sn_id = $this->GP('sn_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/couponsn')->detail($sn_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            
            if(K::M('weixin/couponsn')->update($sn_id, $data)){

                $this->err->add('修改内容成功');
            }  
        }else{
			$member = K::M('member/member')->detail($detail['uid']);
			$detail['member'] = $member['uname'];
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:weixin/couponsn/edit.html';
        }
    }



    public function delete($sn_id=null)
    {
        if($sn_id = (int)$sn_id){
            if(!$detail = K::M('weixin/couponsn')->detail($sn_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/couponsn')->delete($sn_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('sn_id')){
            if(K::M('weixin/couponsn')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}