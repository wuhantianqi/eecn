<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Zxb extends Ctl_Mobile
{
    public function index()
	{
		$config_zxb = $this->system->config->get('zxb');
		$company = K::M('company/company')->items(array('verify_name'=>'1'),null,null,null,$count);
		$this->pagedata['company_verify'] = $config_zxb['zxb_company']+$count;
		$yezhu = K::M('zxb/zxb')->items(null,null,null,null,$count_yezhu);
		$yezhu_last = K::M('zxb/zxb')->items(array('status'=>'8'),null,null,null,$last_yezhu);
		$this->pagedata['zxb_yezhu'] = $config_zxb['zxb_yezhu']+$count_yezhu;
		$price = K::M('zxb/hetong')->items(null);
		foreach($price as $k => $v){
			$total_price += $v['total_price'];
		}
		$this->pagedata['zxb_price'] = $config_zxb['zxb_yezhu']+$total_price;
		$pager['backurl'] = $this->mklink('mobile');
		
		$pager['tender_hide'] = 1;
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/zxb/index.html';
	}

	public function yuyue()
    {
		if($data = $this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if($data = $this->check_fields($data, 'contact,mobile,comment,city_id,area_id')){                
                $data['uid'] = $this->uid;
                $data['city_id'] = empty($data['city_id']) ? $this->request['city_id'] : $data['city_id'];
                if($zxb_id = K::M('zxb/zxb')->create($data)){
                    $tenders = $data;
                    $tenders['from'] = 'ZXB';
                    $tenders['zxb_id'] = $zxb_id;
                    if($tenders_id = K::M('tenders/tenders')->create($tenders)){
                        K::M('zxb/zxb')->update($zxb_id, array('tenders_id'=>$tenders_id));
                        $this->err->add('申请装修保成功！');
                        if($this->uid){
					       $this->err->set_data('forward', $this->mklink('mobile/ucenter/member:zxb'));
                        }else{
                           $this->err->set_data('forward', $this->mklink('mobile/index')); 
                        }
                    }
                }
            }
        }
    }
}