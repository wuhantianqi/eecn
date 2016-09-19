<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: mcached.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

//源生memcached
Import::I('cache');
class Mdl_Cache_Mcached extends Memcached implements Cache_Interface
{
    

    public function __construct(&$system)
    {
        parent::__construct();
		$cfg = explode(':', __CFG::MEMCACHE);
		if($this->addServer($cfg[0], $cfg[1])){	
            trigger_error('Connect Mcache Server Fail!', E_USER_ERROR);
        }
    }

    //Memcached::set($key, $val, $ttl);
    //Memcached::get($key);
    //Memcached::delete($key)
    //Memcached::flush();
}