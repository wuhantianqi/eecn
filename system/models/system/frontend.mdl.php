<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: frontend.mdl.php 2132 2013-12-12 10:34:20Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

require(__CFG::DIR.'libs/smarty/Smarty.class.php');
class Mdl_System_Frontend extends Smarty
{
	
	//private $system = null;
    
    public $widgets_mdl = null;

	public $__MDL = 'Mdl_System_Frontend';

	public function __construct(&$system)
	{
		parent::__construct();
		$this->_init();
	}

	private function _init()
	{
	
		$this->left_delimiter='<{';
        $this->right_delimiter='}>';
        $this->setTemplateDir(__CFG::DIR.'themes/default')
               ->addPluginsDir(__CFG::DIR.'plugins/smarty')
               ->setCompileDir(__CFG::DIR.'data/tplcache')
               ->setCacheDir(__CFG::DIR.'data/cache');
		$this->compile_check = true;

		$this->registerResource('widget',new Smarty_Resource_Widget());
        $this->registerResource('view', new Smarty_Resource_App('home'));
        $this->registerResource('mobile', new Smarty_Resource_App('mobile'));        
        $this->registerResource('admin', new Smarty_Resource_App('admin'));
        $this->registerResource('fenzhan', new Smarty_Resource_App('fenzhan'));
		if(defined('IN_ADMIN')){
			K::$system->check_listion();
		}
	}
}

//Widget resource 
class Smarty_Resource_Widget extends Smarty_Resource_Custom
{
	
	protected function fetch($name, &$source, &$mtime)
	{
		$file = __CFG::DIR."plugins/widgets/{$name}";
		if(file_exists($file)){
			$source = file_get_contents($file);
			$mtime = filemtime($file);
		}else{
			$source = null;
			$mtime = null;
		}
	}

	protected function fetchTimestamp($name)
	{
		$file = __CFG::DIR."plugins/widgets/{$name}";
		if(file_exists($file)){
			return filemtime($file);
		}
		return null;
	}
}

//Plugin resource 
class Smarty_Resource_Plugin extends Smarty_Resource_Custom
{
	
	protected function fetch($name, &$source, &$mtime)
	{
		$file = __CFG::DIR."plugins/{$name}";
		if(file_exists($file)){
			$source = file_get_contents($file);
			$mtime = filemtime($file);
		}else{
			$source = null;
			$mtime = null;
		}
	}

	protected function fetchTimestamp($name)
	{
		$file = __CFG::DIR."plugins/{$name}";
		if(file_exists($file)){
			return filemtime($file);
		}
		return null;
	}
}

//Admin View resource 
class Smarty_Resource_App extends Smarty_Resource_Custom
{
	
	protected $_app = null;

	protected $_path = null;

	public function __construct($app)
	{
		$this->_app = $app;
		$this->_path = $path = __CFG::DIR.$this->_app."/view/";
	}

	protected function fetch($name, &$source, &$mtime)
	{
		$file = $this->_path.$name;
		if(file_exists($file)){
			$source = file_get_contents($file);
			$mtime = filemtime($file);
		}else{
			$source = null;
			$mtime = null;
		}
	}

	protected function fetchTimestamp($name)
	{
		$file = $this->_path.$name;
		if(file_exists($file)){
			return filemtime($file);
		}
		return null;
	}
}