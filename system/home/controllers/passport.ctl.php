<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Passport extends Ctl 
{
    
    public function index()
    {
        $this->seo->init('index');
        $this->tmpl = 'passport/login.html';
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
			if(K::M('sms/sms')->send($phone, 'login', $smsdata)){
				$this->err->add('信息发送成功');
			}
		}
	}

    public function login()
    {
        if(!$this->checksubmit('account')){
            header('Location:'.K::M('helper/link')->mklink('passport'));
            exit();
        }else if(!$account = $this->GP('account')){
            $this->err->add('非法的数据提交', 212);
        }else if(!$uname = $account['uname']){
             $this->err->add('用户名不正确', 213);
        }else if(!$passwd = $account['passwd']){
             $this->err->add('登录密码不下确', 214);
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
            }
        }
    }

    public function minilogin()
    {
        $this->tmpl = 'view:passport/minilogin.html';
    }

    public function check()
    {
        if($clientid = $this->GP('clientid')){
            $account = $this->GP('account');
            $obj = K::M('member/account');
            $oString = K::M('content/string');
            if($clientid == 'uname' && isset($account['uname'])){
                if($obj->check_uname($oString->unescape($account['uname']))){
                    $this->err->add("用户可以使用");
                }
            }else if($clientid == 'mail' && isset($account['mail'])){
                if($obj->check_mail($oString->unescape($account['mail']))){
                    $this->err->add('邮箱可以使用');
                }
            }else{
                $this->err->add('非法的数据提交', 211);
            }
        }else{
            $this->err->add('非法的数据提交', 211);
        }
    }


    public function signup($from='member')
    {   
        $from_list = K::M('member/member')->from_list();
        if(!$from_title = $from_list[$from]){
            $from_title = $from_list['member'];
            $from = 'member';
        }
        $pager = array('from'=>$from, 'from_title'=>$from_title);
        $this->pagedata['pager'] = $pager;
        $this->seo->init('index');
        $this->tmpl = 'passport/signup.html';
    }

    public function reg()
    {
        $this->seo->init('index');
        $this->tmpl = 'passport/reg.html';
    }    

    public function loginout()
    {
        @header("Expires: -1");
        @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        $this->auth->loginout();
        $url = $this->request['forward'];
        if(empty($url) || strpos($url,'loginout') !== false){
            $cfg = $this->system->config->get('site');
            $url = $cfg['siteurl'];
        }
        //$this->err->redirect($url, 200);
        $this->err->add('您已成功退出');   
        $this->err->set_data('forward', $url);
    }

    public function forgot($loc=null)
    {
        if('success' == $loc){
            if($forgotmail = $this->cookie->get('forgotmail')){
                if(K::M('verify/check')->mail($forgotmail)){
                    $this->pagedata['forgotmail'] = $forgotmail;
                    $this->pagedata['mailLogin'] = K::M('mail/view')->weblogin($forgotmail);
                    $this->tmpl = 'passport/forgot-mail.html';
                    $this->output();
                }
            }
            header("Location:".$this->mklink('passport:forgot'));
            exit();
        }else if('reset' == $loc){
            $uri = $this->request['uri'];
            if(preg_match('/reset-(\d+)-([0-9A-Z]+)(\.html)?$/', $uri, $match)){
                if($member = K::M('member/magic')->verify_forgot($match[1], $match[2])){
                    if($this->checksubmit()){
                        $passwd = $this->GP('passwd');
                        $confirmpwd = $this->GP('confirmpwd');
                        if($passwd != $confirmpwd){
                            $this->err->add('两次输入的密码不相同',212);
                        }else if(K::M('member/account')->update_passwd($member['uid'], $passwd)){
                            $this->err->set_data('forward', $this->mklink('passport'));
                            $this->err->add("重新设置密码成功");
                        }
                    }else{
                        $this->pagedata['token'] = "{$match[1]}-{$match[2]}";
                        $this->tmpl = 'passport/forgot-passwd.html';
                    }
                }
            }else{
                $this->error(404);
            }
			
        }else if($this->checksubmit()){
            if(!$mail = $this->GP('forgotmail')){
                $this->err->add('找回密码邮箱不正确', 211);
            }else if(K::M('member/magic')->send_forgot($mail)){
                $this->cookie->set('forgotmail', $mail);
                $this->err->set_data('forward', $this->mklink('passport:forgot', array('success')));
                $this->err->add('验证邮件发送成功');
            }
        }else{
            $this->seo->init('index');
            $this->tmpl = 'passport/forgot.html';
        }
    }

    public function create()
    {
        if(!$this->checksubmit('account')){
            $this->err->add('非法的数据提交', 211);
        }else if(!$account = $this->GP('account')){
            $this->err->add('非法的数据提交', 212);
        }else if($account['passwd'] != $this->GP('confirmpasswd')){
            $this->err->add('两次输入的密码不相同', 213);
        }else{
            $access = $this->system->config->get('access');
            if(!in_array($account['from'], $access['signup_type'])){
                $this->err->add('不允许注册的用户类型', 213);
            }else{
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
                    if($uid = K::M('member/account')->create($account)){
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
            }

        }
        K::M('magic/verify')->reset();
    }

    /**
     * 第三方登录
     */
    public function sso($from='qq')
    {
        echo "success";
        exit;
    }    
    
    //QQ 联合登录
    public function qqlogin($type=null)
    {
        if($url = K::M('member/qqlogin')->qqloign_url($type)){
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
        }elseif(true == K::M('member/qqlogin')->qqcallback($code, $state)){        
            $forward = K::M('helper/link')->mklink('ucenter/member:index', array(), array(), 'base');
            header("Location: {$forward}");
            die;
        }     
    }    
    
    public function weibo($type=null)
    {
        if($url = K::M('member/weibo')->weibo_url($type)){
            header("Location: {$url}");
            die;
        }

    }
    
    public function weibocallback()
    {
        if(!$code = $this->GP('code')){
            die('回传地址有问题2');
        }if(true == K::M('member/weibo')->weibocallback($code)){        
            $forward = K::M('helper/link')->mklink('ucenter/member:index', array(), array(), 'base');
            header("Location: {$forward}");
           die;
        }     
    }

    public function weixin()
    {
        if($wechatCfg = $this->system->config->get('wechat')){
            if($client = K::M('weixin/weixin')->admin_wechat_client()){
                if($client->weixin_type == 1){
                    $data = array('uid'=>$uid, 'type'=>'login', 'addon'=>array('tenders_id'=>$tenders_id));
                    if($scene_id = K::M('weixin/authcode')->create($data)){
                        if($ticket = $client->getQrcodeTicket(array('scene_id'=>$scene_id, 'expire'=>1800))){
                            $wx_login_qr = $client->getQrcodeImgUrlByTicket($ticket);
                            $this->pagedata['wx_login_qr'] = $wx_login_qr;
                        }
                        $this->pagedata['qrcode_id'] = $scene_id;
                    }
                }
            }
            $this->tmpl = 'passport/weixin.html';
        }        
    }

    public function wxscanqr($scene_id)
    {
        $status = 0;
        if($row = K::M('weixin/authcode')->detail($scene_id)){
            if($row['status'] == 1){
                $status = 'scanqr';
            }else if($row['status'] == 2){
                if(K::M('member/auth')->manager($row['uid'])){

                }
                $status = 'login';
            }
        }
        echo '{"status":"'.$status.'"}';
        exit;
    }

    public function verfiy($type = 'mail',$uid = 0 ,$token = null)
    {
  
        if(!($uid=(int)$uid) || empty($token)){
            $this->err->add('参数有误', 211);
        }else{
            if(!$member = K::M('member/view')->member($uid)){
                $this->err->add('用户ID不存在！', 201);
            }else if (K::M('system/integral')->check('email',$member) === false) {
                $this->err->add('很抱歉您的账户余额不足！', 201);
            }else{   
                if(K::M('member/magic')->verify_mail($uid,$token)){
                    K::M('system/integral')->commit('email', $member,'用户邮箱认证');
                    $this->err->add('邮箱验证成功！');
                    $this->err->set_data('forward',  K::M('helper/link')->mklink('ucenter/member:index', array(), array(), 'base'));
                }else{
                    $this->err->add('邮箱验证失败！', 201);
                    $this->err->set_data('forward',  K::M('helper/link')->mklink('ucenter/member:index', array(), array(), 'base'));
                }
            }            
        }        
    }

}