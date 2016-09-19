<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: yuyue.ctl.php 5702 2014-06-27 10:55:04Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Designer_Yuyue extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['designer_id']){$filter['designer_id'] = $SO['designer_id'];}
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
        }
        $filter['city_id'] = CITY_ID;
        if($items = K::M('designer/yuyue')->items($filter, null, $page, $limit, $count)){
            foreach($items as $k=>$v){
				if(!empty($v['uid'])){
					$uids[$v['uid']] = $v['uid'];
				}
				if(!empty($v['company_id'])){
					$company_ids[$v['company_id']] = $v['company_id'];
				}
				if($v['designer_id']){
                    $designer_ids[$v['designer_id']] = $v['designer_id'];
                }
            } 
            if(!empty($uids)){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            if(!empty($company_ids)){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            }            
            if(!empty($designer_ids)){
                $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
            }
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
    
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'fenzhan:designer/yuyue/items.html';
    }

    public function so()
    {
        $this->tmpl = 'fenzhan:designer/yuyue/so.html';
    }

    public function edit($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 213);
        }else if($data = $this->checksubmit('data')){
            unset($data['city_id'], $data['company_id'], $data['designer_id']);
            if(K::M('designer/yuyue')->update($yuyue_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }
    }

    public function detail($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            if($designer_id = $detail['designer_id']){
                $this->pagedata['designer'] = K::M('designer/designer')->detail($designer_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'fenzhan:designer/yuyue/detail.html';
        }        
    }
}