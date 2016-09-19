<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: plugin.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Plugin
{
	
	public $_ctl = null;

	public $request = null;

	public function __construct(&$system)
	{
		$this->system = &$system;
		$this->gpc = &$system->gpc;
	}
	
	public function output()
	{
		$this->pagedata['_PLUGIN_'] = $this->request['app'].'/view/'.$this->pagedata['_OO_'];
		if($this->request['app']){
			$this->pagedata['_PLUGIN_'] = 'apps/'.$this->pagedata['_PLUGIN_'];
		}
		unset($this->pagedata['_OO_']);
		$this->_ctl->pagedata = array_merge($this->_ctl->pagedata,$this->pagedata);
		$this->_ctl->Output();
	}
}