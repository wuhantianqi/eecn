<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Passport extends Ctl_Mobile
{
    
    public function  index()
	{
		$pager['backurl'] = $this->mklink('mobile');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/passport/login.html';
    }


    public function byphone($from)
    {
        if($from == 'member'){
            if($data = $this->checksubmit('data')){
                $session =K::M('system/session')->start();
                if($code = $session->get('code_'.$data['phone'])){
                    if($data['code'] == $code){
                        if($items = K::M('member/member')->items(array('uname'=>$data['phone']),array('uid'=>'desc'),1,1)){
                            foreach($items as $k => $v){
                                $passwd = $v['passwd'];
                            }
                            if($member = $this->auth->login($data['phone'], $passwd, 'uname', true, false)){
                                $this->err->add("{$member[uname]}，欢迎您回来!");
                                if(!$forward = $this->request['forward']){
                                    $forward = K::M('helper/link')->mklink('index', array(), array(), 'base');
                                }else if(strpos($forward, 'passport') !== false){
                                    $forward = K::M('helper/link')->mklink('ucenter/member:index', array(), array(), 'base');
                                }
                                if(substr($forward, 0, 7) != 'http://'){
                                    $forward = '/'.trim($forward, '/');
                                }
                                $this->err->set_data('forward', $forward);
                            }
                    
                        }else{
                            $data['uname'] = $data['phone'];
                            $data['mail'] = $data['phone'].'@qq.com';
                            $data['passwd'] = '123456';
                            $data['mobile'] = $data['phone'];
                            $data['verify'] = '2';
                            if($detail = K::M('member/member')->items(array('mobile'=>$data['phone']))){
                                $this->err->add('该手机号码已经被注册', 213);
                            }else if($uid = K::M('member/account')->create($data)){
                                $this->err->add('恭喜您，注册会员成功');
                                $from_list = K::M('member/member')->from_list();
                                $account_from = $account['from'];
                                if(!$from_list[$account_from]){
                                    $account_from = 'member';
                                }
                                $forward = K::M('helper/link')->mklink('ucenter/'.$account_from.':index', array(), array(), 'base');
                                $this->err->set_data('forward', $forward);
                            }
                        }
                    }else{
                        $this->err->add('验证码错误或者已经过期', 212);
                    }
                    
                }
            }else{
                $this->tmpl = 'passport/byphone.html';
            }
        }else{
            header('Location:'.K::M('helper/link')->mklink('passport-signup:'.$from));
        }
    }

    public function sendsms($phone)
    {
        if(!$a = K::M('verify/check')->phone($phone)){
            $this->err->add('电话号码有误', 212);
        }else{
            $code = rand(100000,999999);
            $session =K::M('system/session')->start();
            $session->set('code_'.$phone, $code,900); //15分钟缓存
            $smsdata =  array('code'=>$code);
            $this->err->add('恭喜您，注册会员成功');
            if(K::M('sms/sms')->send($phone, 'login', $smsdata)){
                $this->err->add('信息发送成功');
            }
        }
    }

    public function login()
	{
        if(!$this->checksubmit('data')){
            $this->err->add('非法的数据提交', 211);
        }else if(!$data = $this->GP('data')){
            $this->err->add('非法的数据提交', 212);
        }else if(!$uname = $data['uname']){
             $this->err->add('用户名不正确', 213);
        }else if(!$passwd = $data['passwd']){
            $this->err->add('登录密码不正确', 214);
        }else{
			$verifycode_success = true;
			$access = $this->system->config->get('access');
            if($access['verifycode']['login']){
                if(!$verifycode = $this->GP('verifycode')){
                    $verifycode_success = false;
                    $this->err->add('验证码不正确', 212);
                }else if(!K::M('magic/verify')->check($verifycode)){
                    $verifycode_success = false;
                    $this->err->add('验证码不正确', 212);
                }
            }
            if($verifycode_success){
				$keep = $this->GP('keep') ? true : false;
				$a = K::M('verify/check')->mail($uname) ? 'mail' : 'uname';
				if($member = $this->auth->login($uname, $passwd, $a, false, $keep)){
					$this->err->add("{$member['uname']}，欢迎您回来!");
				}
			}
        } 
    }
     //QQ 联合登录
    public function qqlogin()
	{
        if($url = K::M('member/qqlogin')->qqloign_url()){
            header("Location: {$url}");
            die;
        }        
    }
    
    public function qqcallback()
	{
        if(!$code = $this->GP('code')){
            die('回传地址有问题2');
        }elseif(!$state = $this->GP('state')){
            die('回传地址有问题1');
        }elseif(true == K::M('member/qqlogin')->qqcallback($code,$state)){        
            $forward = K::M('helper/link')->mklink('mobile/ucenter:index', array(), array(), 'base');
            header("Location: {$forward}");
           die;
        }     
    }
    
    public function weibo()
	{
        if($url = K::M('member/weibo')->weibo_url()){
            header("Location: {$url}");
            die;
        }        
    }

    public function weibocallback()
	{
        if(!$code = $this->GP('code')){
            die('回传地址有问题2');
        }if(true == K::M('member/weibo')->weibocallback($code)){        
            $forward = K::M('helper/link')->mklink('mobile/ucenter:index', array(), array(), 'base');
            header("Location: {$forward}");
           die;
        }     
    }
    
    public function loginout()
    {
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
        $this->auth->loginout();
        header("Location: ".$this->mklink('mobile/index',array(),array(),'base'));
        die;
    }

    public function signup()
    {   
		$pager['backurl'] = $this->mklink('mobile/passport');
        $this->pagedata['pager'] = $pager;
        $this->pagedata['fromlist'] = K::M('member/view')->from_list();
    	$this->tmpl = 'mobile/passport/signup.html';
    }
    public function create()
    {	
    	if(!$this->checksubmit('data')){
    		$this->err->add('非法的数据提交', 211);
    	}else if(!$data = $this->GP('data')){
            $this->err->add('非法的数据提交', 212);
        }else if($data['passwd'] != $this->GP('confirmpasswd')){
            $this->err->add('两次输入的密码不相同', 213);
        }else{
			$access = $this->system->config->get('access');
			$verifycode_success = true;
            if($access['verifycode']['signup']){
                if(!$verifycode = $this->GP('verifycode')){
                    $verifycode_success = false;
                    $this->err->add('验证码不正确', 212);
                }else if(!K::M('magic/verify')->check($verifycode)){
                    $verifycode_success = false;
                    $this->err->add('验证码不正确', 212);
                }
            }
			if($verifycode_success){
				if($uid = K::M('member/account')->create($data)){
                    $this->err->set_data('forward', $this->mklink('mobile/ucenter'));
					$this->err->add('恭喜您，注册会成功');
				}
			}
        }
    }
}