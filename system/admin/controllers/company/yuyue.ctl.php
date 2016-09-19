<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: yuyue.ctl.php 5681 2014-06-26 11:33:16Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Company_Yuyue extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
        }
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('company/yuyue')->items($filter, null, $page, $limit, $count)){
            foreach($items as $k=>$v){
               if(!empty($v['uid'])) $uids[$v['uid']] = $v['uid'];
               if(!empty($v['company_id']))  $companyIds[$v['company_id']] = $v['company_id'];
                  $items[$k]['clientip'] = $v['clientip'].'('. K::M("misc/location")->location($v['clientip']) .')';
            } 
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
		$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($companyIds);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:company/yuyue/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:company/yuyue/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$company_id = (int)$data['company_id']){
                $this->err->add('未选要发布到的公司', 212);
            }else if(!$company = K::M('company/company')->detail($company_id)){
                $this->err->add('您选的公司不存在或已经删除', 213);
            }else if(!$this->check_city($company['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if($yuyue_id = K::M('company/yuyue')->create($data)){
					//K::M('company/yuyue')->yuyue_count($company['company_id']);
					K::M('company/company')->update($company['company_id'], array('yuyue_num'=>$company['yuyue_num']+1));
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?company/yuyue-index.html');
                }
            } 
        }else{
            
           $this->tmpl = 'admin:company/yuyue/create.html';
        }
    }

    public function edit($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('company/yuyue')->detail($yuyue_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                unset($data['company_id'], $data['city_id']);
                if(K::M('company/yuyue')->update($yuyue_id, $data)){
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?company/yuyue-index.html');
                }  
            } 
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:company/yuyue/edit.html';
        }
    }

    public function detail($yuyue_id=null)
    {
        if(!($yuyue_id = (int)$yuyue_id) && !($yuyue_id = $this->GP('yuyue_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('company/yuyue')->detail($yuyue_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else{
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:company/yuyue/detail.html';
        }        
    }

    public function delete($yuyue_id=null)
    {
		if($yuyue_id = (int)$yuyue_id){
            if($yuyue = K::M('company/yuyue')->detail($yuyue_id)){
                if(!$this->check_city($yuyue['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('company/yuyue')->delete($yuyue_id)){
                    //K::M('company/yuyue')->yuyue_count($yuyue['company_id']);
                    $this->err->add('删除公司新闻成功');
                }
            }
        }else if($ids = $this->GP('yuyue_id')){
            if($items = K::M('company/yuyue')->items_by_ids($ids)){
                $aids = $company_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['yuyue_id']] = $v['yuyue_id'];
                    $company_ids[$v['company_id']] = $v['company_id'];
                }
                if($aids && K::M('company/yuyue')->delete($aids)){
                    //K::M('company/yuyue')->yuyue_count($company_ids);
                    $this->err->add('批量删除公司新闻成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}