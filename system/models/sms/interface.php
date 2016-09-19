<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: interface.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

interface Sms_Interface
{
	//. public $gatewary = '';
	public function send($mobile, $content);
}