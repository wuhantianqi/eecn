<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: memcache.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

Import::I('cache');
class Mdl_Cache_Memcache extends Memcache implements Cache_Interface
{

	protected static $memcache = null;
	
	public function __construct(&$system)
	{
		//self::$memcache= new Memcache();
		//$cfg = explode(':', __CFG::MEMCACHE);
		//self::$memcache->connect($cfg[0], $cfg[1]);
		$cfg = explode(':', __CFG::MEMCACHE);
		$this->connect($cfg[0], $cfg[1]);
	}
	
	public function set($key, $val, $ttl=0)
	{	
		//压缩
		//$compress = is_bool($v) || is_int($v) || is_float($v) ? false : MEMCACHE_COMPRESSED;
		//return self::$memcache->set($key,$val,false,$ttl);
		return parent::set($key, $val, false, $ttl);
	}
/*
	public function get($key)
	{
		//return self::$memcache->get($key);
		return parent::get($key);
	}

	public function delete($key)
	{
		return self::$memcache->delete($k,0);
	}

	public function flush()
	{
		return self::$memcache->flush();
	}
*/	
}