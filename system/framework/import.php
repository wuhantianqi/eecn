<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: import.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Import
{	
	static public $_GFILES = array();

	//Interface 
	static public function I($i)
	{
		$i = str_replace('.','/',$i);
		$path = '';
		if($pos = strpos($i,':')){
			$path = substr($i,0,$pos);
		}
		return self::_import(__CFG::DIR."{$path}models/{$i}/interface.php");
	}
	
	//Exception
	static public function E($e)
	{
		$e = str_replace('.','/',$e);
        $path = '';
		if($pos = strpos($e,':')){
			$path = substr($e,0,$pos);
			$e = substr($e,$pos+1);
		}
		if(!self::_import(__CFG::DIR."{$path}models/{$e}/exception.php")){
			self::_import(__CFG::DIR."models/{$e}/exception.php");
		}
	}
	
	//Model
	static public function M($mdl)
	{
		$mdl = str_replace('.','/',$mdl);
        $path = '';
		if($pos = strpos($mdl,':')){
			$path = substr($mdl,0,$pos).'/';
			$mdl = substr($mdl,$pos+1);
		}
        if($path == 'plugin/'){
			$file = __CFG::DIR.preg_replace("/^([\w\/]+)\/(\w+)$/i","plugins/\\1/models/\\2.mdl.php",$mdl);
			self::_import($file);
			$mdl = "plugin/{$mdl}";
		}else{
			$file = __CFG::DIR."{$path}models/{$mdl}.mdl.php";
			if(!self::_import($file) && !$path){
				self::_import(__APP_DIR."models/{$mdl}.mdl.php");
			}
		}
		$mdl = 'Mdl_'.str_replace(' ','_',(ucwords(str_replace('/',' ',$mdl))));
		return $mdl;	
	}
	
	//Controller
	static public function C($ctl)
	{
		$ctl = str_replace('.','/',$ctl);
		if($pos = strpos($ctl,':')){
			$path = substr($ctl,0,$pos);
			$ctl = substr($ctl,$pos+1);
		}
		if(!self::_import(__APP_DIR."controllers/{$ctl}.ctl.php")){
			return false;
		}
		return 'Ctl_'.str_replace(' ','_',ucwords(str_replace('/',' ',$ctl)));
	}
	
	//Widget
	static public function W($w)
	{
		self::_import(__CFG::DIR.'plugins/widgets/'.$w.'/widget.php');
		return "Widget_".ucfirst($w);
	}

	static public function P($plugin)
	{
		self::_import(__CFG::DIR."plugins/{$plugin}.php");
		return "Plugin_".str_replace(' ','_',ucfirst(str_replace('/',' ',$plugin)));	
	}
	
	//libs
	static public function L($lib)
	{
		self::_import(__CFG::DIR.'libs/'.$lib);
	}

	static private function _import($file)
	{
		if(!file_exists($file)){
			return false;
		}
		$hash = md5($file);
		if(!isset(self::$_GFILES[$hash])){
			self::$_GFILES[$hash] = $file;
			require($file);			
		}
		return true;
	}
}
//using import的别名类
class Using extends Import{};