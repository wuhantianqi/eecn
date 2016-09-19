<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company_Youhui extends Ctl_Ucenter 
{
    
    protected $_allow_fields = 'area_id,title,stime,ltime,content';
    
    public function index($page=1)
    {
        $company = $this->ucenter_company();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('company_id'=>$company['company_id'], 'closed'=>0);
        if($items = K::M('company/youhui')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/company/youhui/items.html';    
    }

    public function create()
    {
        $company = $this->ucenter_company();
        if(K::M('member/integral')->check('youhui',  $this->MEMBER) === false){
            $this->err->add('很抱歉您的账户余额不足！', 201);
        }else if($data = $this->checksubmit('data')){
            $allow_youhui = K::M('member/group')->check_priv($company['group_id'], 'allow_youhui');
            if($allow_youhui < 0){
                $this->err->add('您是【'.$company['group_name'].'】没有权限添加优惠信息', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['youhui_thumb']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'company')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                $data['company_id'] = $company['company_id'];
                $data['city_id'] = $company['city_id'];
                $data['area_id'] = $data['area_id'] ? $data['area_id'] : $company['area_id'];
                $data['audit'] = $allow_youhui;
                if($coupon_id = K::M('company/youhui')->create($data)){
                    K::M('company/youhui')->youhui_count($company['company_id']);
                    K::M('member/integral')->commit('youhui', $this->MEMBER, '添加优惠信息');
                    $this->err->add('添加优惠信息成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/company/youhui:index'));
                }
            } 
        }else{
            $this->tmpl = 'ucenter/company/youhui/create.html';
        }
    }

	public function refresh($youhui_id = null)
    {
		$company = $this->ucenter_company();
		$integral = K::$system->config->get('integral');
		$counts = K::M('member/flush')->flushs($this->uid);
		$is_gold = $integral['gold']*-1;
		if($counts >= $company["group"]["priv"]["day_free_count"]){
			$this->pagedata['gold'] = $is_gold;
		}
		$this->pagedata['is_refresh'] = $counts;
		$this->pagedata['counts'] = $company["group"]["priv"]["day_free_count"];
		if($fromid = $this->GP('fromid')){
			$isrefresh = true;
			if($counts >= $company["group"]["priv"]["day_free_count"]){
				if($this->MEMBER['gold']<$is_gold){
					$isrefresh = false;
					$this->err->add('您的金币余额不足，请先充值', 215);
				}
			}
			$data['gold'] = '0';
			if($isrefresh && $counts >= $company["group"]["priv"]["day_free_count"]){
				$data['gold'] = $is_gold;
				if($is_gold > 0){
                    if(!K::M('member/gold')->update($this->uid, $integral['gold'], "刷新公司")){
                        $this->err->add('扣费失败', 201)->response();
                    }
                }
			}
			$data['uid'] = $this->uid;$data['from'] = 'youhui';$data['itemId'] = $fromid;
			if(K::M('member/flush')->create($data)){
				K::M('company/youhui')->update($fromid, array('flushtime'=>__TIME));
				$this->err->add('刷新成功');
			}

		}else{
			$this->pagedata['fromid'] = $youhui_id;
			$this->pagedata['from'] = 'youhui';
			$this->tmpl = 'ucenter/company/refresh/look.html';
		}
	}

    public function edit($youhui_id=null)
    {
        $company = $this->ucenter_company();
        if(!($youhui_id = (int)$youhui_id) && !($youhui_id = (int)$this->GP('youhui_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('company/youhui')->detail($youhui_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($detail['company_id'] != $company['company_id']){
            $this->err->add('您没有权限修改该内容', 212);
        }else if($data = $this->checksubmit('data')){
            if(K::M('member/group')->check_priv($company['group_id'], 'allow_youhui') < 0){
                $this->err->add('您是【'.$company['group_name'].'】没有权限添加优惠信息', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($attach = $_FILES['youhui_thumb']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'company')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                if(K::M('company/youhui')->update($youhui_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/company/youhui/edit.html';
        } 
    }

    public function delete($youhui_id=null)
    {
        $company = $this->ucenter_company();
        if(!($youhui_id = (int)$youhui_id) && !($youhui_id = $this->GP('youhui_id'))){
            $this->err->add('未指要删除的优惠信息', 211);
        }else if(!$detail = K::M('company/youhui')->detail($youhui_id)){
            $this->err->add('你要删除的优惠信息不存或已经删除', 212);
        }else if($company['company_id'] != $detail['company_id']){
            $this->err->add('您没有权限删除该优惠信息', 213);
        }else{
            K::M('company/youhui')->delete($youhui_id);
            $this->err->add('删除优惠信息成功');
        }
    }

    public function sign($page=1)
    {
        $company = $this->ucenter_company();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        if($items = K::M('company/sign')->items(array('company_id'=>$company['company_id']), $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $youhui_ids = array();
            foreach($items as $k=>$v){
                $youhui_ids[$v['youhui_id']] = $v['youhui_id'];
            }
            if($youhui_ids){
                $this->pagedata['youhui_list'] = K::M('company/youhui')->items_by_ids($youhui_ids);
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['youhui'] = $youhui;
        $this->tmpl = 'ucenter/company/youhui/sign.html';
    }

    public function youhuiSign($youhui_id=null, $page=1)
    {
        $company = $this->ucenter_company();
        if(!$youhui_id = (int)$youhui_id){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$youhui = K::M('company/youhui')->detail($youhui_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else if($youhui['company_id'] != $company['company_id']){
            $this->err->add('您没有权限查看该内容', 212);
        }else{
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 20;
            $pager['count'] = $count = 0;
            if($items = K::M('company/sign')->items(array('youhui_id'=>$youhui_id), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['youhui'] = $youhui;
            $this->tmpl = 'ucenter/company/youhui/youhuiSign.html';
        }
    }

    public function signDetail($sign_id=null)
    {
        $company = $this->ucenter_company();
        if(!$sign_id = (int)$sign_id){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$detail = K::M('company/sign')->detail($sign_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else if($detail['company_id'] != $company['company_id']){
            $this->err->add('您没有权限查看该内容', 213);
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            $this->pagedata['youhui'] = K::M('company/youhui')->detail($detail['youhui_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/company/youhui/signDetail.html';
        }
    }

    public function signSave()
    {
        $company = $this->ucenter_company();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$sign_id = (int)$this->GP('sign_id')){
                $this->err->add('未指定要保存的预约', 211);
            }else if(!$detail = K::M('company/sign')->detail($sign_id)){
                $this->err->add('内容不存在或已经删除', 212);
            }else if($detail['company_id'] != $company['company_id']){
                $this->err->add('您没有权限操作该内容', 213);
            }else if(K::M('company/sign')->update($sign_id, $data)){
                $this->err->add('更新报名状态成功');
            }
        }
    }

}