<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: file.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

Import::I('cache');
class Mdl_Cache_File implements Cache_Interface
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
		$data[] = '<?php exit("Access Denied");?>';
		$data[] = "note:System cache file, DO NOT modify me!";
		$data[] = "hash:{$hash}:{$key}";
		$data[] = "time:$time";
		$data[] = "data:".$this->serialize($val);
		file_put_contents($this->cache_dir."cache_{$hash}.php", implode("\n",$data));
	}

	public function get($key)
	{
		$file = $this->cache_dir.'cache_'.md5($key).'.php';
		if(file_exists($file)){
			$data = file($file);
			$time = substr($data[3],5);
			if(($time == 0) || (__CFG::TIME<$time)){
				$count = count($data);
				$content = implode("\n", array_slice($data, 4, $count-4));
				return $this->unserialize(substr($content,5));
			}
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

	public function serialize($data)
	{
		return serialize($data);
		return base64_encode(serialize($data));
		//return bin2hex(serialize($data));
	}

	public function unserialize($content)
	{
		return unserialize($data);
		if($data = base64_decode($content)){
			return unserialize($data);
		}
		return false;
		//return unserialize(base64_decode($content));
		//return unserialize(hex2bin($content));
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