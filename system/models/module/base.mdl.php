<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: base.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Module_Base extends Mdl_Table
{

	protected $_table = 'system_module';
	protected $_pk = 'mod_id';
	protected $_cols = 'mod_id,module,level,ctl,act,title,visible,parent_id,orderby,dateline';

	static protected $modules = null;

	public function flush()
	{
		self::$modules = null;
		return $this->cache->delete('admin/module');
	}
}