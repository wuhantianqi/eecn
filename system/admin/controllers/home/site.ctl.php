<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_Site extends Ctl
{

    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['site_id']){$filter['site_id'] = $SO['site_id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['area_id']){$filter['area_id'] = $SO['area_id'];}
            if($SO['home_id']){$filter['home_id'] = $SO['home_id'];}
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
            if($SO['status']){$filter['status'] = $SO['status'];}
        }
        if($items = K::M('home/site')->items($filter, null, $page, $limit, $count)){
            $home_ids = $company_ids = array();
             foreach($items as $k=>$v){
                if($v['home_id']){
                    $home_ids[$v['home_id']] = $v['home_id'];        
                }
                if($v['company_id']){
                    $company_ids[$v['company_id']] = $v['company_id'];
                }

				if($v['uid']){
                    $uids[$v['uid']] = $v['uid'];        
                }
               
            }
            if(!empty($home_ids)){
                $this->pagedata['home_list'] = K::M('home/home')->items_by_ids($home_ids);
            }              
            if(!empty($company_ids)){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            }
			if(!empty($uids)){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
       
		$this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['status'] = K::M('home/site')->get_status();
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->tmpl = 'admin:home/site/items.html';
    }

    public function so()
    {
		$this->pagedata['status'] = K::M('home/site')->get_status();
        $this->tmpl = 'admin:home/site/so.html';
    }

    public function create()
    {	
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$company_id = (int)$data['company_id']){
                $this->err->add('未选要发布到的公司', 212);
            }else if(!$member = K::M('member/member')->detail($data['uid'])){
                $this->err->add('该会员不存在', 212);
            }else if($member['from'] != 'company' && $member['from'] != 'gz'){
                $this->err->add('会员类型错误', 218);
            }else if(!$company = K::M('company/company')->detail($company_id)){
                $this->err->add('您选的公司不存在或已经删除', 213);
            }else if(!$this->check_city($company['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
				if($_FILES['data']){
					foreach($_FILES['data'] as $k=>$v){
						foreach($v as $kk=>$vv){
							$attachs[$kk][$k] = $vv;
						}
					}
					$upload = K::M('magic/upload');
					foreach($attachs as $k=>$attach){
						if($attach['error'] == UPLOAD_ERR_OK){
							if($a = $upload->upload($attach, 'home')){
								$data[$k] = $a['photo'];
							}
						}
					}
				}
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
				$data['lat'] = trim($data['lat']);
                if($site_id = K::M('home/site')->create($data)){
					K::M('home/site')->site_count($site_id);
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?home/site-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:home/site/create.html';
        }
    }

    public function edit($site_id=null)
    {
        if(!($site_id = (int)$site_id) && !($site_id = $this->GP('site_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				if($_FILES['data']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'home')){
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }
				unset($data['uid'], $data['city_id']);
				$data['lat'] = trim($data['lat']);
                if(K::M('home/site')->update($site_id,$data)){
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?home/site-index.html');
                }  
            } 
        }else{
			if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
			if($case_id = $detail['case_id']){
                $this->pagedata['case'] = K::M('case/case')->detail($case_id);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:home/site/edit.html';
        }
    }

    public function doaudit($site_id=null)
    {
        if($site_id = (int)$site_id){
            if(K::M('home/site')->batch($site_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('site_id')){
            if(K::M('home/site')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($site_id=null)
    {
		if($site_id = (int)$site_id){
            if($site = K::M('home/site')->detail($site_id)){
                if(!$this->check_city($site['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('home/site')->delete($site_id)){
                    K::M('home/site')->down_count($site);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('site_id')){
            if($items = K::M('home/site')->items_by_ids($ids)){
                $aids  = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['site_id']] = $v['site_id'];
                }
                if($aids && K::M('home/site')->delete($aids)){
                    K::M('home/site')->downs_count($items);
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}