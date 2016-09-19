<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: file.bk.mdl.php 9343 2015-03-24 07:07:00Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

Import::I('cache');
class Mdl_Cache_MFile implements Cache_Interface
{

	public function __construct(&$system)
	{
		$this->system = &$system;
		$this->cache_dir = __CFG::DIR.'data/cache/';
	}

	public function set($key, $val, $ttl=0)
	{
		$time = $ttl==0 ? 0 : (__CFG::TIME + $ttl);
		$hash = md5($key);
		$data = is_array($val) ? var_export($val, true) : $val;
$tmpl = '<?php
/**
 * Copy Right IJH.CC
 * note:System cache file, DO NOT modify me!
 * hash:{hash}:{key};
 * time:{time}
 */
if(!defined("__CORE_DIR")){	exit("Access Denied");}if({time}===0 || __TIME<{time}){return {data};}return false;';
		$content = str_replace(array('{hash}','{time}', '{data}', '{key}'), array($hash, $time, $data, $key), $tmpl);
		file_put_contents($this->cache_dir."cache_{$hash}.php", $content);
	}

	public function get($key)
	{
		$fun = 'cache_'.md5($key);
		$file = $this->cache_dir.'cache_'.md5($key).'.php';
		if(file_exists($file)){
			return @include($file);
		}
		return false;
	}

	public function delete($key)
	{
		K::M('io/file')->remove($this->cache_dir.'cache_'.md5($key).'.php');
	}

	public function flush()
	{
		$this->clean();
	}

	public function clean()
	{
		$handler = dir($this->cache_dir);
		while (false !== ($file = $handler->read())) {
			if('.' == $file || '..' == $file){
				continue;
			}
			@unlink($this->cache_dir.$file);
		}
		$handler->close();
		return true;
	}
	
}