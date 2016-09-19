<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: view.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Mail_View extends Model
{
	
	public static $mailsite = array(
		//腾讯
		'@qq.com' => 'http://mail.qq.com',
		'@vip.qq.com' => 'http://mail.qq.com',
		'@foxmail.com' => 'http://mail.qq.com/cgi-bin/loginpage?t=fox_loginpage',
		//网易
		'@126.com' => 'http://www.126.com',
		'@vip.126.com' => 'http://vip.126.com',		
		'@163.com' => 'http://mail.163.com',
		'@vip.163.com' => 'http://vip.163.com',
		'@yeah.net'	=> 'http://www.yeah.net',
		'@188.com'	=> 'http://www.188.com',

		//sina
		'@sina.com'	=> 'http://mail.sina.com.cn',
		'@sina.cn' => 'http://mail.sina.com.cn',
		'@2008.sina.com' => 'http://mail.sina.com.cn',
		'@51uc.com'	=> 'http://mail.sina.com.cn',
		//sohu
		'@sohu.com'	=> 'http://mail.sohu.com/',
		'@vip.sohu.com' => 'http://vip.sohu.com/',
		'@sogou.com' => 'http://mail.sogou.com',
		'@chinaren.com' => 'http://mail.chinaren.com',
		'@17174.com'	=> 'http://mail.17173.com',

		//微软
		'@hotmail'	=> 'http://www.hotmail.com',
		'@live.cn'	=> 'http://www.live.cn',
		'@live.com' => 'http://www.live.com',
		'@live.com.cn' => 'http://www.live.com.cn',
		'@msng.com' => 'http://www.live.com',
		'@live.com' => 'http://www.live.com',
		'@msn.com'	=> 'http://login.passport.net',
		'@outlook.com' => 'http://www.outlook.com',
		
		//yahoo
		'@yahoo.com'=> 'http://mail.yahoo.com',
		'@yahoo.cn' => 'http://mail.cn.yahoo.com',
		'@yahoo.com.cn' => 'http://mail.cn.yahoo.com',

		'@138.com' => 'http://mail.10086.cn',
		'@189.cn'	=> 'http://mail.189.cn',
		'@wo.com.cn' => 'http://mail.wo.com.cn',
		'@tom.com'	=> 'http://mail.tom.com',
		'@21cn.com' => 'http://mail.21cn.com',
		'@263.com'	=> 'http://www.263.net',
		'@eyou.com' => 'http://www.eyou.com'
		);

	public function weblogin($mail)
	{
		$k = strtolower(strstr($mail,'@'));
		if(!$login = self::$mailsite[$k]){
			return false;
		}
		return $login;
	}

	
}