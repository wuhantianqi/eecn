<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Gz_Yuyue extends Ctl_Ucenter 
{
    
    public function gz($page=1)
    {
        $gz = $this->ucenter_gz();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('gz_id'=>$gz['uid'], 'closed'=>0);
        if($items = K::M('gz/yuyue')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/gz/yuyue/items.html';
    }

    public function detail($yuyue_id=null)
    {
        $gz = $this->ucenter_gz();
		$audit_case = K::M('system/audit')->gz('case' ,$gz, $audit_title);
        if(!$yuyue_id = (int)$yuyue_id){
            $this->err->add('未指定要查看的预约', 211);
        }else if(!$detail = K::M('gz/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['gz_id'] != $gz['uid']){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            $this->pagedata['detail'] = $detail;
			$this->pagedata['audit_title'] = $audit_title;
            $this->tmpl = 'ucenter/gz/yuyue/detail.html';
        }
    } 

    public function save()
    {
        $gz = $this->ucenter_gz();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$yuyue_id = (int)$this->GP('yuyue_id')){
                $this->err->add('未指定要保存的预约', 211);
            }else if(!$detail = K::M('gz/yuyue')->detail($yuyue_id)){
                $this->err->add('预约不存在或已经删除', 212);
            }else if($detail['gz_id'] != $gz['uid']){
                $this->err->add('您没有权限操作该预约', 213);
            }else if(K::M('gz/yuyue')->update($yuyue_id, $data)){
                $this->err->add('更新预约状态成功');
            }
        }
    }
}