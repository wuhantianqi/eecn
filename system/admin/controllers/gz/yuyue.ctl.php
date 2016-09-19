<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: yuyue.ctl.php 5702 2014-06-27 10:55:04Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Gz_Yuyue extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['gz_id']){$filter['gz_id'] = $SO['gz_id'];}
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
        }
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('gz/yuyue')->items($filter, null, $page, $limit, $count)){
            $uids = $gz_ids = array();
            foreach($items as $k=>$v){
               if(!empty($v['uid'])){
                   $uids[$v['uid']] = $v['uid'];
               }
               $gz_ids[$v['gz_id']] = $v['gz_id'];
            } 
            if(!empty($uids)){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            if(!empty($gz_ids)){
                $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
            }            
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $this->pagedata['items'] = $items;
        }       
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:gz/yuyue/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:gz/yuyue/so.html';
    }

    public function create()
    {
        if($data = $this->checksubmit('data')){
            if(!$gz_id = (int)$data['gz_id']){
                $this->err->add('非法的数据提交', 211);
            }else if(!$gz = K::M('gz/gz')->detail($gz_id)){
                $this->err->add('预约的工长不存在或已经删除', 212);
            }else if(!$this->check_city($gz['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{            
                $data['city_id'] = $gz['city_id'];
                if($yuyue_id = K::M('gz/yuyue')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?gz/yuyue-index.html');
                }
            }
        }else{
           $this->tmpl = 'admin:gz/yuyue/create.html';
        }
    }

    public function edit($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('gz/yuyue')->detail($yuyue_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('gz/yuyue')->update($yuyue_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            if($gz_id = $detail['gz_id']){
                $this->pagedata['gz'] = K::M('gz/gz')->detail($gz_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:gz/yuyue/edit.html';
        }
    }

    public function detail($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$detail = K::M('gz/yuyue')->detail($yuyue_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            if($gz_id = $detail['gz_id']){
                $this->pagedata['gz'] = K::M('gz/gz')->detail($gz_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:gz/yuyue/detail.html';
        }        
    }

    public function delete($yuyue_id=null)
    {
        if($yuyue_id = (int)$yuyue_id){
            if(K::M('gz/yuyue')->delete($yuyue_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('yuyue_id')){
            if(K::M('gz/yuyue')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}