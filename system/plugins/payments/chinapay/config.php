<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: config.php 3053 2014-01-15 02:00:13Z youyi $
 */
return  array(
	'code'=>'chinapay',
	'name'=>'中国银联',
	'content'=>'中国银联(www.chinapay.com) - 本即时到账接口无需预付费，任何订单金额均即时到达您的账户，只收单笔手续费。',
	'is_online'=>'1',
	'website'   => 'http://www.chinapay.com',
	'version'   => '1.0',	
	'currency'  => '人民币',
	'config'    => array(
		'chinapay_service'  => array(
            'text'      => '接口类型',
            'type'      => 'select',
            'items'     => array(
                'trade_create_by_buyer'   => '即时到帐模式', 
            ),
        ),
        'chinapay_account'   => array(//帐号
            'text'  => '银联商户号',
            'desc'  => '银联商户号',
            'type'  => 'text',
        ),
        'chinapay_mkey'       => array(//密匙
            'text'  => '商户私钥证书',
            'desc'  => '商户私钥证书名，文件需上传到银联支付接口目录下',
            'type'  => 'text',
        ),

		 'chinapay_pkey'       => array(//密匙
            'text'  => '银联公钥证书',
            'desc'  => '银联公钥证书，文件需上传到银联支付接口目录下',
            'type'  => 'text',
        ),

    ),
);
