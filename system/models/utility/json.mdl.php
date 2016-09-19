<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: json.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Utility_Json
{
	public function encode($data)
	{
		return json_encode($data);
	}

	public function decode($data)
	{
		return json_decode($data);
	}
}