<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: config.php 3053 2014-01-15 02:00:13Z youyi $
 */
return array(
	'code'=>'alipay',
	'name'=>'支付宝',
	'content'=>'支付宝网站(www.alipay.com) 是国内先进的网上支付平台。<a href="http://act.life.alipay.com/systembiz/shopnc" target="_blank" style="color:red; font-weight:bold;">立即在线申请</a>（<a href="http://club.alipay.com/read-htm-tid-10077293.html" style="color:red; font-weight:bold;" target="_blank">如何启用支付宝收款</a>）',
	'website'   => 'http://www.alipay.com',
	'version'   => '1.0',
	'currency'  => '人民币',
	'config'    => array(
        'alipay_service'  => array(
            'text'      => '接口类型',
            'desc'  => '1.5%费率用户请选“担保交易接口”',
            'type'      => 'select',
            'items'     => array(
                'trade_create_by_buyer'   => '标准双接口',
                'create_partner_trade_by_buyer'   => '担保交易接口',
                'create_direct_pay_by_user'   => '即时到帐交易接口',
            ),
        ),	
        'alipay_account'   => array(
            'text'  => '支付宝账户',
            'desc'  => '您必须拥有支付宝账户，能才通过支付宝收款，<a href="https://memberprod.alipay.com/account/reg/index.htm" style="color:red; font-weight:bold;" target="_blank">点此注册</a>我还未签约支付宝，<a href="https://b.alipay.com/order/serviceIndex.htm" style="color:red; font-weight:bold;" target="_blank">点此获取PID、Key</a>',
            'type'  => 'text',
        ),
        'alipay_partner'   => array(
            'text'  => '合作者身份(PID)',
       		'desc'	=>'<a href="https://b.alipay.com/order/serviceIndex.htm" id="pidKey" style="color:red; font-weight:bold;" target="_blank">我已签约支付宝，获取PID、Key</a>',
            'type'  => 'text',
        ),        
        'alipay_key' => array(
            'text'  => '安全校验码(Key)',
            'desc'  => '',
            'type'  => 'password',
        ),
    ),
);