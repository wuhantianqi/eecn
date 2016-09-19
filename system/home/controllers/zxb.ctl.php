<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tools.ctl.php 3304 2014-02-14 11:01:43Z youyi $
 */
class Ctl_Zxb extends Ctl 
{
    public function index()
    {
		$config_zxb = $this->system->config->get('zxb');
		$company = K::M('company/company')->items(array('verify_name'=>'1'),null,null,null,$count);
		$this->pagedata['company_verify'] = $config_zxb['zxb_company']+$count;
		$yezhu = K::M('zxb/zxb')->items(null,null,null,null,$count_yezhu);
		$yezhu_last = K::M('zxb/zxb')->items(array('status'=>'8'),null,null,null,$last_yezhu);
		$this->pagedata['zxb_yezhu'] = $config_zxb['zxb_yezhu']+$count_yezhu;
		$this->pagedata['zxb_last'] = $config_zxb['zxb_yezhu']+$last_yezhu;
		$price = K::M('zxb/hetong')->items(null);
		foreach($price as $k => $v){
			$total_price += $v['total_price'];
		}
		$access = $this->system->config->get('access');
		$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
		$this->pagedata['satisfaction'] = $config_zxb['satisfaction'];
		$this->pagedata['zxb_price'] = $config_zxb['zxb_price']+$total_price;
        $this->seo->init('zxb');
		$this->tmpl = 'zxb/index.html';
    }

    public function yuyue()
    {
        if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}elseif($data = $this->checksubmit('data')){
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
					$data['city_id'] = $this->request['city_id'];
					$data['uid'] = $this->uid;
					unset($data['status']);
					$tenders = $data;
					$zxb_id = K::M('zxb/zxb')->create($data);
					$tenders['from'] = 'ZXB';
					$tenders['zxb_id'] = $zxb_id;                       
					if($tenders_id = K::M('tenders/tenders')->create($tenders)){
						if(K::M('zxb/zxb')->update($zxb_id, array('tenders_id'=>$tenders_id,'status'=>'1'))){
							$this->err->add('申请成功！');
							$this->err->set_data('forward', $this->mklink('ucenter/member/zxb:index'));
						}
					}
				}
            }
        }
    }
}
