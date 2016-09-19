<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company_Yuyue extends Ctl_Ucenter 
{
    
    public function company($page=1)
    {
        $company = $this->ucenter_company();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('company_id'=>$company['company_id'], 'closed'=>0);
        if($items = K::M('company/yuyue')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/company/yuyue/items.html';
    }

    public function detail($yuyue_id=null)
    {
        $company = $this->ucenter_company();
        if(!$yuyue_id = (int)$yuyue_id){
            $this->err->add('未指定要查看的预约', 211);
        }else if(!$detail = K::M('company/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['company_id'] != $company['company_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/company/yuyue/detail.html';
        }
    }

    public function save()
    {
        $company = $this->ucenter_company();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$yuyue_id = (int)$this->GP('yuyue_id')){
                $this->err->add('未指定要保存的预约', 211);
            }else if(!$detail = K::M('company/yuyue')->detail($yuyue_id)){
                $this->err->add('预约不存在或已经删除', 212);
            }else if($detail['company_id'] != $company['company_id']){
                $this->err->add('您没有权限操作该预约', 213);
            }else if(K::M('company/yuyue')->update($yuyue_id, $data)){
                $this->err->add('更新预约状态成功');
            }
        }
    }

    public function designer($page=1)
    {
        $company = $this->ucenter_company();
        $filter = $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        if($items = K::M('designer/yuyue')->items(array('company_id'=>$company['company_id']), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
            foreach($items as $k=>$v){
                $uids[$v['designer_id']] = $v['designer_id'];
            }
            if($uids){
                $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($uids);
            }

        }
        $this->tmpl = 'ucenter/company/yuyue/designer.html';
    }

    public function designerDetail($yuyue_id=null)
    {
        $company = $this->ucenter_company();
        if(!$yuyue_id = (int)$yuyue_id){
            $this->err->add('未指定要查看的预约', 211);
        }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['company_id'] != $company['company_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            $this->pagedata['designer'] = K::M('designer/designer')->detail($detail['designer_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/company/yuyue/designerDetail.html';
        }
    }

    public function designerSave()
    {
        $company = $this->ucenter_company();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$yuyue_id = (int)$this->GP('yuyue_id')){
                $this->err->add('未指定要保存的预约', 211);
            }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
                $this->err->add('预约不存在或已经删除', 212);
            }else if($detail['company_id'] != $company['company_id']){
                $this->err->add('您没有权限操作该预约', 213);
            }else if(K::M('designer/yuyue')->update($yuyue_id, $data)){
                $this->err->add('更新预约状态成功');
            }
        }
    }
   
}