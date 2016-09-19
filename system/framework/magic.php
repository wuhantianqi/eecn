<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: magic.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class K
{
	
	public static $system = null;

	public static function &M($mdl)
	{
		return K::$system->load_model($mdl);
	}

	public static function L($lang)
	{
	
	}

	public static function C($ctl)
	{
		
	}

	public static function W($wgt)
	{
		return K::$system->load_widget($wgt);
	}

	public static function DB($db=null)
	{
		return K::$system->LoadDB($db);
	}
    
    public static function IDS($ids)
    {
        if(is_array($ids)){
            $ids = implode("','",$ids);
        }else if(strpos($ids,"'")===false){
            $ids = str_replace(',',"','",trim($ids,','));
        }else{
            return trim($ids,',');
        }
        return "'{$ids}'";
    }

	public static function GUID($key='')
	{
        return strtoupper(md5(uniqid(mt_rand(), true).$key));
	}
    
    //判断是否为GUID
    public static function IS_GUID($guid)
    {
        return preg_match("/[0-9A-F]{32}/",$guid);
    }
}

function GUID($key='')
{
    $charid = strtoupper(md5(uniqid(mt_rand(), true).$key));
    $hyphen = chr(45); // "-"
    $GUID =  substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
    return $GUID;
}