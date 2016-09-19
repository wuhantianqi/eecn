<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: config.php 3053 2014-01-15 02:00:13Z youyi $
 */
return array(
	'code'=>'wxpay',
	'name'=>'微信',
	'content'=>'微信支付(pay.weixin.qq.com) 是国内先进的网上支付平台。<a href="http://pay.weixin.qq.com" target="_blank" style="color:red; font-weight:bold;">立即在线申请</a>（<a href="http://pay.weixin.qq.com" style="color:red; font-weight:bold;" target="_blank">如何启用微信收款</a>）',
	'website'   => 'https://pay.weixin.qq.com',
	'version'   => '1.0',
	'currency'  => '人民币',
	'config'    => array(
        'appid'   => array(
            'text'  => 'APPID',
			'desc'	=>'appid是微信公众账号或开放平台APP的唯一标识，在公众平台申请公众账号或者在开放平台申请APP账号后，微信会自动分配对应的appid，用于标识该应用。商户的微信支付审核通过邮件中也会包含该字段值。',
            'type'  => 'text',
        ),
        'mch_id'   => array(
            'text'  => '微信支付商户号',
       		'desc'	=>'商户申请微信支付后，由微信支付分配的商户收款账号。',
            'type'  => 'text',
        ),        
        'key' => array(
            'text'  => 'API密钥',
            'desc'  => '交易过程生成签名的密钥，仅保留在商户系统和微信支付后台，不会在网络中传播。商户妥善保管该Key，切勿在网络中传输，不能在其他客户端中存储，保证key不会被泄漏。商户可根据邮件提示登录微信商户平台进行设置。',
            'type'  => 'password',
        ),
		'appsecret' => array(
            'text'  => 'secret',
            'desc'  => 'AppSecret是APPID对应的接口密码，用于获取接口调用凭证access_token时使用。在微信支付中，先通过OAuth2.0接口获取用户openid，此openid用于微信内网页支付模式下单接口使用。在开发模式中获取AppSecret（成为开发者且帐号没有异常状态）。',
            'type'  => 'text',
        ),
    ),
);