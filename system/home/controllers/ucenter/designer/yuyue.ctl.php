<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Designer_Yuyue extends Ctl_Ucenter 
{
    
    public function designer($page=1)
    {
        $designer = $this->ucenter_designer();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('designer_id'=>$designer['designer_id'], 'closed'=>0);
        if($items = K::M('designer/yuyue')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/designer/yuyue/items.html';
    }

    public function detail($yuyue_id=null)
    {
        $designer = $this->ucenter_designer();        
        if(!$yuyue_id = (int)$yuyue_id){
            $this->err->add('未指定要查看的预约', 211);
        }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的预约不存在或已经删除', 212);
        }else if($detail['designer_id'] != $designer['designer_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/designer/yuyue/detail.html';
        }
    } 

    public function save()
    {
        $designer = $this->ucenter_designer();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'status,remark')){
                $this->err->add('非法的数据提交', 211);
            }else if(!$yuyue_id = (int)$this->GP('yuyue_id')){
                $this->err->add('未指定要保存的预约', 211);
            }else if(!$detail = K::M('designer/yuyue')->detail($yuyue_id)){
                $this->err->add('预约不存在或已经删除', 212);
            }else if($detail['designer_id'] != $designer['designer_id']){
                $this->err->add('您没有权限操作该预约', 213);
            }else if(K::M('designer/yuyue')->update($yuyue_id, $data)){
                $this->err->add('更新预约状态成功');
            }
        }
    }
}