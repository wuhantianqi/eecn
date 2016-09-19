<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Mobile_Home extends Ctl_Mobile
{
	public function tuan($page = 1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 6;
        $filter['audit'] = 1;
		$filter['city_id'] = $this->request['city_id'];
		if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $filter['title'] = "LIKE:%{$kw}%";            
        }
        if ($items = K::M('home/tuan')->items($filter,null, $page, $limit, $count)) {
            $home_ids = array();
            foreach ($items as $k => $v) {
                if ($v['home_id']) {
                    $home_ids[$v['home_id']] = $v['home_id'];
                }
            }
            if (!empty($home_ids)) {
                $this->pagedata['home_list'] = K::M('home/home')->items_by_ids($home_ids);
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/home:tuan', array('{page}'), array(), true),array('kw' => $pager['sokw']));
        }
        $this->pagedata['items'] = $items;
		$pager['backurl'] = $this->mklink('mobile');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/home/tuan.html';
    }

	public function tuandetail($tuan_id)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('没有您要查看的团装', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('没有您要查看的团装', 212);
        }else{
            $package = K::M('home/package')->items(array('tuan_id'=>$tuan_id));
            $huxingIds = array();
            foreach($package as $v){
               if($v['huxing_id']){
				$huxingIds[$v['huxing_id']] = $v['huxing_id']; 
			   }
            }
            if(!empty($huxingIds)){
                $this->pagedata['huxing'] = K::M('home/photo')->items_by_ids($huxingIds);
            }
            $this->pagedata['tuan'] = $detail;
            $this->pagedata['package'] = $package;
            $this->pagedata['home'] = K::M('home/home')->detail($detail['home_id']);
			$pager['backurl'] = $this->mklink('mobile/home:tuan');
			$this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/home/tuandetail.html';
        }
    }

	public function tuanSign($tuan_id, $package_id=null)
    {
		if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('没有您要查看的团装', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('没有您要查看的团装', 212);
        }else if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }else {
                $data['uid'] = (int)$this->uid;
                $data['tuan_id'] = $tuan_id;
				if($package_id){
					$data['package_id'] = $package_id;
				}else{
					$data['package_id'] = 0;
				}
                if ($sign_id = K::M('home/sign')->create($data)) {
                    K::M('home/tuan')->update_count($tuan_id,'sign_num', 1);
                    $home = K::M('home/home')->detail($detail['home_id']);
                    $company = K::M('company/company')->detail($detail['company_id']);
                    $smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'home_tuan'=>$home['name'],'tuan_name'=>$detail['title']);
                    K::M('sms/sms')->send($data['mobile'], 'home_tuan', $smsdata);
                    K::M('sms/sms')->company($company, 'home_tuan_company', $smsdata);
                    K::M('helper/mail')->sendcompany($company, 'home_tuan', $maildata);
                    $this->err->add('恭喜您报名成功');
					$this->err->set_data('forward', $this->mklink('mobile/home:tuandetail', array($tuan_id)));
                }
            }
        }else{
			
			$pager['tender_hide'] = 1;
            $this->pagedata['tuan'] = $detail;
			$this->pagedata['package_id'] = $package_id;
			$pager['backurl'] = $this->mklink('mobile/home:tuandetail',array($tuan_id));
			$this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/home/tuanSign.html';
        }      
    }
}