<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
class Ctl_Mobile_Ucenter_Index extends Ctl_Mobile_Ucenter
{
	public function index()
	{
		$pager['backurl'] = $this->mklink('mobile');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/index.html';
	}

	public function info()
	{
		if ($account = $this->checksubmit('account')) {
            if ($this->MEMBER['verify_mobile']) {
                unset($account['mobile']); //认证后不允许修改手机
            }
            if ($this->MEMBER['verify_mail']) {
                unset($account['mail']); //认证后不允许修改邮箱
            }     
            if($this->MEMBER['from'] != 'member' || $account['from'] == 'mechanic'){
                unset($account['from']);
            }
			if (K::M('member/member')->update($this->uid, $account)) {
                if($account['from'] == 'designer'){
                    K::M('designer/designer')->create(array('uid'=>  $this->uid,'city_id'=>$this->MEMBER['city_id']),null,true);
                }else if($account['from'] == 'gz'){
					K::M('gz/gz')->create(array('uid'=>$this->uid,'city_id'=>$this->MEMBER['city_id']),null,true);
				}
                $this->err->add('更新个人资料成功');
            }
        }
        else {
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
            $this->pagedata['fromlist'] = K::M('member/member')->from_list();
            $this->tmpl = 'mobile/ucenter/info.html';
        }
	}

	public function passwd()
    {   
        if($account = $this->checksubmit('account')){
            if(md5($account['old_passwd']) != $this->MEMBER['passwd']){
                $this->err->add('原密码不正确', 211);
            }else if($account['passwd'] != $account['confirm_passwd']){
                $this->err->add('两次输入的密码不相同', 212);
            }else if($this->auth->update_passwd($account['passwd'], false)){
                $this->err->add('修改密码成功');
            }
        }else{
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/ucenter/passwd.html';
        }        
    }

	public function mail()
    {    
        if($account = $this->checksubmit('account')){
            if(md5($account['passwd']) != $this->MEMBER['passwd']){
                $this->err->add('登录密码不正确', 211);
            }else if($account['new_mail'] == $this->MEMBER['mail']){
                $this->err->add('新老邮箱一致不用修改', 211);
            }else if($mail = K::M('member/account')->check_mail($account['new_mail'])){
                if($this->auth->update_mail($mail, false)){
                    $this->err->add('修改邮箱成功');
                }
            }
        }else{
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/ucenter/mail.html';
        }          
    }

	public function appointment()
	{
		$form = $this->MEMBER['from'];
		if($form  == 'member'){
			$this->err->add('权限错误', 211);
		}else{
			$temp = 'ucenter_'.$form;
			$form_list = $this->$temp();
			$filter = array('uid'=>$form_list['uid'], 'closed'=>0);
			if($items = K::M($form.'/yuyue')->items($filter)){
				$this->pagedata['items'] = $items;
			}
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/ucenter/appointment.html';
		}
        
	}
}
