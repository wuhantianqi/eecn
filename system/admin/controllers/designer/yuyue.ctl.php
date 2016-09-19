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
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
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
        $this->tmpl = 'admin:designer/yuyue/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:designer/yuyue/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$designer_id = (int)$data['designer_id']){
                $this->err('未指预约的设计师', 212);
            }else if(!$designer = K::M('designer/designer')->detail($designer_id)){
                $this->err('指定的设计师不在或已经删除', 213);
            }else if(!$this->check_city($designer['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
                $data['city_id'] = $designer['city_id'];
                $data['company_id'] = $designer['company_id'];
                if($yuyue_id = K::M('designer/yuyue')->create($data)){
                    K::M('designer/yuyue')->news_count($yuyue['designer_id']);
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?designer/yuyue-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:designer/yuyue/create.html';
        }
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
        	$this->tmpl = 'admin:designer/yuyue/edit.html';
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
            $this->tmpl = 'admin:designer/yuyue/detail.html';
        }        
    }

   public function delete($yuyue_id=null)
    {
        if($yuyue_id = (int)$yuyue_id){
            if($yuyue = K::M('designer/yuyue')->detail($yuyue_id)){
                if(!$this->check_city($yuyue['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('designer/yuyue')->delete($yuyue_id)){
                    $this->err->add('删除预约成功');
                }
            }
        }else if($ids = $this->GP('yuyue_id')){
            if($items = K::M('designer/yuyue')->items_by_ids($ids)){
                $aids = $designer_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['yuyue_id']] = $aids['yuyue_id'];
                    $designer_ids[$v['designer_id']] = $v['designer_id'];
                }
                if($aids && K::M('designer/yuyue')->delete($ids)){
                    $this->err->add('批量删除预约成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}