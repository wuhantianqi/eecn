<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: config.php 3053 2014-01-15 02:00:13Z youyi $
 */
return  array(
	'code'=>'tenpay',
	'name'=>'财付通即时到帐',
	'content'=>'财付通(www.tenpay.com) - 本即时到账接口无需预付费，任何订单金额均即时到达您的账户，只收单笔 1% 手续费。',
	'is_online'=>'1',
	'website'   => 'http://www.tenpay.com',
	'version'   => '1.0',
	'currency'  => '人民币',
	'config'    => array(
        'tenpay_account'   => array(//帐号
            'text'  => '财付通商户号',
            'desc'  => '',
            'type'  => 'text',
        ),
        'tenpay_key'       => array(//密匙
            'text'  => '财付通密钥',
            'desc'  => '',
            'type'  => 'text',
        )
    ),
);