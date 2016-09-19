<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Site extends Ctl_Mobile
{
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/site-([\d\-]+).html/i', $uri, $match)){
            $system->request['act'] = 'index';
            $system->request['args'] = array($match[2]);
        }      
    }
    
    public function index($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count;
        $filter['city_id'] = $this->request['city_id'];
		$filter['closed'] = 0;
        $filter['audit'] = 1;
		if ($items = K::M('home/site')->items($filter, null, $page, $limit, $count)) {
			$company_ids = $uids = array();
            foreach ($items as $val) {
				$uids[$val['uid']] = $val['uid'];
                if ($val['company_id']){
                    $company_ids[$val['company_id']] = $val['company_id'];
				}
            }
            if (!empty($company_ids)){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
			}
			if($member_list = K::M('member/member')->items_by_ids($uids)){
                $designer_ids = $gz_ids = array();
                foreach($member_list as $v){
                    if($v['from'] == 'designer'){
                        $designer_ids[$v['uid']] = $v['uid'];
                    }
                    if($v['from'] == 'gz'){
                        $gz_ids[$v['uid']] = $v['uid'];
                    }
                }
                if($designer_ids){
                    $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
                }				
                if($gz_ids){
                    $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
                } 				
                $pager['pagebar'] = $this->mkpage($count, $limit,$page,$this->mklink('mobile/site:index',array('{page}')));
            }
		}
		$this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/site/items.html';
        
    }

	 public function detail($site_id)
    {
        if(!$site_id = (int)$site_id){
            $this->error(404);
        }else if(!$site = K::M('home/site')->detail($site_id)){
            $this->error(404);
        }else if(empty($site['audit'])){
            $this->err->add('工地还未发布，暂不可访问', 211);
        }else{            
            if($site['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($site['company_id']);
            }else if($site['uid']){
                if($member = K::M('member/member')->member($site['uid'])){
                    if($member['from'] == 'gz'){
                        $this->pagedata['gz'] = K::M('gz/gz')->detail($site['uid']);
                    }else if($member['from'] == 'designer'){
                        $this->pagedata['designer'] = K::M('designer/designer')->detail($site['uid']);
                    }
                    $this->pagedata['member'] = $member;
                }
            }
            $status = K::M('home/site')->get_status();
            if($items = K::M('home/item')->items(array('site_id'=>$site_id))){
                foreach($items as $k => $v){
                    $items[$k]['statu_name'] = $status[$v['status']];
                }
            }
			$this->pagedata['status'] = $status;
            $this->pagedata['items'] = $items;
            $this->pagedata['site'] = $site;
			$pager['backurl'] = $this->mklink('mobile/site');
			$this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/site/detail.html';  
        }        
    }

	public function yuyue($site_id=null)
    {
        if(!($site_id = (int)$site_id) && !($site_id = (int)$this->GP('site_id'))){
           $this->error(404);
        }else if(!$site = K::M('home/site')->detail($site_id)){
            $this->error(404);
        }else if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'contact,mobile')){
                $this->err->add('非法的数据提交', 211);
            }else{
                $yuyue_result = false;
                $data['uid'] = (int)$this->uid;               
                $data['content'] = "预约参观工地：".$site['title'].'，来自移动端';
                $data['city_id'] =  $site['city_id'];
                $data['site_id'] = $site_id;
                $smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'site'=>$site['title'],'company_name'=>$company['name']);
                if(($site['company_id']) && ($company = K::M('company/company')->detail($site['company_id']))){
                    $data['company_id'] = $site['company_id'];            
                    if($yuyue_id = K::M('company/yuyue')->create($data)){
                        $yuyue_result = true;
                        K::M('company/company')->update_count($site['company_id'], 'yuyue_num');
                        K::M('sms/sms')->send($data['mobile'], 'site_yuyue', $smsdata);
                        K::M('sms/sms')->company($company, 'company_site', $smsdata);
                        K::M('helper/mail')->sendcompany($company, 'company_site', $maildata);
                    }
                }else if($member = K::M('member/member')->detail($site['uid'])){
                    if($member['from'] == 'gz'){
                        if($gz = K::M('gz/gz')->detail($member['uid'])){
                            $data['gz_id'] = $member['uid'];
                            if($yuyue_id = K::M('gz/yuyue')->create($data)){
                                $yuyue_result = true;
                                K::M('gz/gz')->update_count($site['uid'], 'yuyue_num');
                                K::M('sms/sms')->send($data['mobile'], 'site_yuyue', $smsdata);
                                if($gz['verify_mobile'] && K::M('verify/check')->mobile($gz['mobile'])){
                                    K::M('sms/sms')->send($site['mobile'], 'designer_tongzhi', $smsdata);
                                }
                                if($gz['verify_mail'] && K::M('verify/check')->mail($gz['mail'])){
                                    K::M('helper/mail')->sendmail($member['mail'], 'gz_site', $maildata); 
                                }                               
                            }
                        }                        
                    }else if($member['from'] == 'designer'){
                        if($designer = K::M('designer/designer')->detail($member['uid'])){
                            $data['designer_id'] = $member['uid'];
                            $data['company_id'] = $designer['company_id'];
                            if($yuyue_id = K::M('designer/yuyue')->create($data)){
                                $yuyue_result = true;
                                K::M('designer/designer')->update($site['uid'], 'yuyue_num');
                                K::M('sms/sms')->send($data['mobile'], 'designer_yuyue', $smsdata);
                                if($designer['verify_mobile'] && K::M('verify/check')->mobile($designer['mobile'])){
                                    K::M('sms/sms')->send($designer['mobile'], 'designer_tongzhi', $smsdata);
                                }
                                if($designer['verify_mail'] && K::M('verify/check')->mail($designer['mail'])){
                                    K::M('helper/mail')->sendmail($designer['mail'], 'designer_yuyue', $maildata);
                                }
                            }
                        }                        
                    }
                }
                if($yuyue_result){
                    $this->err->add('预约参观成功！');
                    $this->err->set_data('forward', $this->mklink('mobile/site:detail', array($site_id)));
                }
            }
        }else{
			
			$pager['tender_hide'] = 1;
            $pager['backurl'] = $this->mklink('mobile/site:detail',array($site_id));
            $this->pagedata['pager'] = $pager;
            $this->pagedata['site'] = $site;
            $this->tmpl = 'mobile/site/yuyue.html';
        }
    }
}