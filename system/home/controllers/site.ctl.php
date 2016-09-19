<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: site.ctl.php 10304 2015-05-16 13:06:14Z xiaorui $
 */

class Ctl_Site extends Ctl
{
	public function index()
    { 
        $this->items();
    }

	public function items($area_id=0, $order=0, $page=null)
	{
        $pager = $filter = array();
        if($page === null){
            $page = (int)$area_id;
            $area_id = $order = 0;
        }
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $pager['order'] = $order;
        $pager['area_id'] = $area_id;
        if ($area_id = (int)$area_id) {
            $filter['area_id'] = $area_id;
        } else {
            $filter['city_id'] = $this->request['city_id'];
        }
		if($order == 1){
			$orderby = array('house_mj'=>'DESC');
		}else if($order == 2){
			$orderby = array('price'=>'ASC');
		}else{
			$orderby = null;
		}
		$filter['closed'] = 0;
        $filter['audit'] = 1;
        if ($items = K::M('home/site')->items($filter, $orderby, $page, $limit, $count)) {
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
                $designer_ids  = array();
                foreach($member_list as $v){
                    if($v['from'] == 'designer'){
                        $designer_ids[$v['uid']] = $v['uid'];
                    }
                }
                if($designer_ids){
                    $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
                }
          
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit,$page,$this->mklink('site:items',array($area_id, $order, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $area_list = $this->request['area_list'];
		$order_list = K::M('home/site')->get_order();
		foreach ($order_list as $k => $v) {
            if ($k == $order){
                $order_list[$k]['checked'] = true;
			}
			if(!$order_list){
				$order_list['1']['checked'] = true;
			}
			$order_list[$k]['link'] = $this->mklink('site:items', array($area_id, $k, $page));
        }
        $this->pagedata['order_list'] = $order_list;
		$this->pagedata['area_list'] = $area_list;
        
        $this->pagedata['pager'] = $pager;
        $seo = array('area_name'=>'', 'page'=>($page > 1) ? $page : '');
		if($area_id){
            $seo['area_name'] = $area_list[$area_id]['area_name'];
        }
        $this->seo->init('site_items', $seo);
        $this->tmpl = 'site/items.html';
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
            $this->pagedata['site'] = $site;
            if($site['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($site['company_id']);
            }else if($site['uid']){
                if($member = K::M('member/member')->member($site['uid'])){
                    if($member['from'] == 'designer'){
                        $this->pagedata['designer'] = K::M('designer/designer')->detail($site['uid']);
                    }
                    $this->pagedata['member'] = $member;
                }
            }
            if($site['case_id']){
            	$this->pagedata['case'] = K::M('case/case')->detail($site['case_id']);
            	$this->pagedata['case_photo'] = K::M('case/photo')->items_by_case($site['case_id']);
            }
            $status = K::M('home/site')->get_status();
            if($items = K::M('home/item')->items(array('site_id'=>$site_id))){
                foreach($items as $k => $v){
                    $items[$k]['statu_name'] = $status[$v['status']];
                }
            }
			$this->pagedata['status'] = $status;
            $this->pagedata['items'] = $items;
			$this->seo->init('site_detail',array('title'=>$site['title'], 'home_name'=>$detail['home_name'], 'company_name'=>$this->pagedata['company']['name']));
            $this->tmpl = 'site/detail.html';  
        }        
    }

	public function map()
	{
		$area_list = K::M('data/area')->areas_by_city($this->request['city_id']);
		$this->pagedata['area_list'] = $area_list;
		$this->seo->init('site_maps');
        $this->tmpl = 'site/maps.html';
	}

	 public function result()
    {
        $SO = $this->GP('SO');
        if (!empty($SO['lng_start']) && !empty($SO['lng_end']) && !empty($SO['lat_start']) && !empty($SO['lat_end'])) {
            if (is_numeric($SO['lng_start']) && is_numeric($SO['lng_end']) && is_numeric($SO['lat_start']) && is_numeric($SO['lat_end'])) {
                $filter['lng'] = $SO['lng_start'] . '~' . $SO['lng_end'];
                $filter['lat'] = $SO['lat_start'] . '~' . $SO['lat_end'];
				if($SO['name']){
					$filter['title'] = "LIKE:%".$SO['name']."%";
				}
				if($SO['area_id']){
					$filter['area_id'] = $SO['area_id'];
				}
                $filter['closed'] = 0;
                $items = K::M('home/site')->items($filter, null, 1, 100, $count);
				$status = K::M('home/site')->get_status();
                $data = array();
                foreach ($items as $val) {
                    $data[$val['site_id']] = array(
                        'site_id' => $val['site_id'],
                        'link' => $this->mklink('site:detail', array($val['site_id']), array(), true),
                        'title' => $val['title'],
						'home_name' => $val['home_name'],
                        'thumb' => $val['thumb'],
						'house_mj' => $val['house_mj'],
						'price' => $val['price'],
                        'lng' => $val['lng'],
                        'lat' => $val['lat'],
						'status' => $status[$val['status']]
                    );
                }				
                $this->err->set_data('total', $count);
                $this->err->set_data('result', $data);
            }
        }
        $this->err->json();
        die;
    }

	public function yuyue($site_id)
    {
		if(!($site_id = (int)$site_id) && !($site_id = (int)$this->GP('site_id'))){
            $this->err->add('没有该内容', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('没有该内容', 212);
        }else if(!$company = K::M('company/company')->detail($detail['company_id'])){
			$member = K::M('member/member')->detail($detail['uid']);
			if($member['from'] == 'designer'){
				if($this->checksubmit('data')){
					if(!$data = $this->GP('data')){
						$this->err->add('非法的数据提交', 201);
					}else{
						$verifycode_success = true;
						$access = $this->system->config->get('access');
						if($access['verifycode']['yuyue']){
							if(!$verifycode = $this->GP('verifycode')){
								$verifycode_success = false;
								$this->err->add('验证码不正确', 212);
							}else if(!K::M('magic/verify')->check($verifycode)){
								$verifycode_success = false;
								$this->err->add('验证码不正确', 212);
							}
						}
						if($verifycode_success){
							$data['designer_id'] = $detail['uid'];
							$data['company_id'] = $detail['company_id'];
							$data['uid'] = (int)$this->uid;
							$data['city_id'] =  $this->request['city_id'];
							$data['content'] = "预约参观工地：".$detail['title'];
							if($yuyue_id = K::M('designer/yuyue')->create($data)){
								K::M('designer/designer')->update($detail['uid'],array('yuyue_num'=>$detail['yuyue_num']+1));
								$smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'site'=>$site['title'],'designer'=>$detail['name']);
								K::M('sms/sms')->send($data['mobile'], 'designer_yuyue', $smsdata);
								if($detail['verify_mobile'] && K::M('verify/check')->mobile($detail['mobile'])){
									K::M('sms/sms')->send($detail['mobile'], 'designer_tongzhi', $smsdata);
								}
								K::M('helper/mail')->sendmail($from['mail'], 'designer_yuyue', $maildata);
								$this->err->add('预约设计师成功');
							}
						}
					}
				}else{
					$access = $this->system->config->get('access');
					$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
					$this->pagedata['site_id'] = $site_id;
					$this->pagedata['detail'] = $detail;
					$this->tmpl = 'site/yuyue.html';
				}
			}
		}else{
			if($this->checksubmit('data')){
				if(!$data = $this->GP('data')){
					$this->err->add('非法的数据提交', 201);
				}else{
					$verifycode_success = true;
					$access = $this->system->config->get('access');
					if($access['verifycode']['yuyue']){
						if(!$verifycode = $this->GP('verifycode')){
							$verifycode_success = false;
							$this->err->add('验证码不正确', 212);
						}else if(!K::M('magic/verify')->check($verifycode)){
							$verifycode_success = false;
							$this->err->add('验证码不正确', 212);
						}
					}
					if($verifycode_success){
						$data['uid'] = (int)$this->uid;
						$data['company_id'] = $detail['company_id'];
						$data['content'] = "预约参观工地：".$detail['title'];
						$data['city_id'] = $this->request['city_id'];
						$data['site_id'] = $site_id;
						if($yuyue_id = K::M('company/yuyue')->create($data)){
							K::M('company/company')->update_count($detail['company_id'],'yuyue_num',1);
							$smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'site'=>$site['title'],'company_name'=>$company['name']);
							K::M('sms/sms')->send($data['mobile'], 'site_yuyue', $smsdata);
							K::M('sms/sms')->company($company, 'company_site', $smsdata);
							K::M('helper/mail')->sendcompany($company, 'company_site', $maildata);
							$this->err->add('预约参观成功！');  
						}
					}
				} 
			}else{
				$access = $this->system->config->get('access');
				$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
				$this->pagedata['site_id'] = $site_id;
				$this->pagedata['detail'] = $detail;
				$this->tmpl = 'site/yuyue.html';
			}
		}
	}

}