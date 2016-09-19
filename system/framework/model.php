<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: model.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
abstract class Model
{
	
	public $__MDL = 'Model';
	
	public $succeed = false;

	public $error = 0;

	public $message = '';
	
	public static $system = null;
	
	private $_reback_params = array();
	

	public function __construct(&$system)
	{
		if(self::$system === null){
			self::$system = &$system;
		}
		$this->_G = &$system->_G;
		$this->db = &$system->db;
		$this->err = &$system->err;
		$this->cookie = &$system->cookie;
		$this->cache = &$system->cache;
	}
	

	public function set_reback($k, $v)
	{
		$this->_reback_params[$k] = $v;
	}

	public function get_reback($k)
	{
		return $this->_reback_params[$k];
	}
/*
	public function __set()
	{
	
	}

	public function __get()
	{
	
	}
*/
	public function __call($name, $args)
	{
		trigger_error(get_class($this)."->{$name} Not Found!!");
	}

}