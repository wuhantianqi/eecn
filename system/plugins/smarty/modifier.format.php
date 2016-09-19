<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: modifier.format.php 2070 2013-12-09 09:04:47Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

function smarty_modifier_format($data, $format=null)
{
    static $mdl = null;
    if(!is_numeric($data)){
        return 'NULL';
    }else if($format == 'size'){
        if($mdl === null){
            $mdl = K::M('helper/format');
        }
        return $mdl->size($data);
    }else if($format == 'price'){
		
	}else if($format === null) {
        if(!defined('IN_ADMIN')){
            if($mdl === null){
                $mdl = K::M('helper/format');
            }
            return $mdl->time($data);
        }
        $format = "Y-m-d H:i:s";
    }else if(strpos($format,'%') !== false){
        $format = str_replace(array('%D','%T'),array('Y-m-d','H:i:s'), $format);
    }
    return date($format, $data);
}