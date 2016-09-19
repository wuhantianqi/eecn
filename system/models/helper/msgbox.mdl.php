<?php
/**
 * Copy	Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: msgbox.mdl.php 2034 2013-12-07 03:08:33Z $
 */

class Mdl_Helper_Msgbox
{
	
	public $error = 0;
	protected $_message = array();
	protected $_data = array();

    protected static $system = null;

	protected $tpl = 'view:page/notice.html';

	public function __construct(&$system)
	{
		self::$system = &$system;
	}
	
	public function template($tpl)
	{
		$this->tpl = $tpl;
	}

	public function add($msg,$error=0)
	{
		$this->error = $error;
		$this->_message[] = $msg;
        return $this;
	}

	public function last()
	{
		$last = end($this->_message);
		return $last;
	}

	public function set_data($k,$v=null)
	{
		if(is_array($k)){
			$this->_data = array_merge((array)$this->_data, (array)$k);
		}else{
			$this->_data[$k] = $v;
		}
	}

	public function show($url='',$t='HTML')
	{
		$t = strtoupper($t);
		$pager = $this->_data;
		$pager['error'] = $this->error;
		$pager['message'] = $this->_message;
		if('HTML' == $t){			
			if(isset(K::$system->resource_view)){
				//$output->default_resource_type = K::$system->resource_view;
			}else{
				//$output->default_resource_type = 'views';
			}
			if(is_array($url)){
				foreach($url as $k=>$v){
					$url_list[$k]['title'] = $v[0]; 
					$url_list[$k]['link'] = $v[1]; 
				}
				$pager['link'] = $url[0][1];
				$pager['url_list'] = $url_list;
			}else{
				$pager['link'] = $url;
			}
			$pager['res'] = __CFG::RES_URL;
			$attach = K::$system->config->get('attach');
			$pager['img'] = $attach['attachurl'];
            $pager['timer'] = 3; //3秒跳转
			$pager['message'] = implode('<br />',$pager['message']);
            $objctl = &K::$system->objctl;
			$objctl->pagedata['pager'] = $pager;
            $objctl->pagedata['_OO_'] = $this->tpl;
			$objctl->output();
		}else if('JSON' == $t){
            header("Content-Type:text/plain");
			echo K::M('utility/json')->encode($pager);
		}else if('JSONP' == $t){
            header("Content-Type:text/plain");
            echo $url.'('.K::M('utility/json')->encode($pager).');';
        }else if('XML' == $t){
             header("Content-Type:text/xml");
			echo K::M('utility/xml')->xml($pager);
		}else if('JS'){
			$pager['message'] = implode("\n",$pager['message']);
			$output = &K::M('system/frontend');
			$output->assign('pager', $pager);
			$output->display($this->tpl);
		}
		exit();
	}

	public function alert($url)
	{
		if(defined('IN_ADMIN')){
			$output->display('admin:page/alert.html');
		}else{
			$output->display('view:page/alert.html');
		}		
		$this->show($url,'JS');
	}

    public function jsonp($data=array())
    {
		if(!empty($data)){
			$this->set_data($data);
		}
        if($callback = trim($_GET['jsonpcallback'])){
            if(!preg_match("/^(\w+)$/i",$callback)){
                $callback = 'jsonpcallback';
            }
        }else{
            $callback = 'jsonpcallback';
        }
        $this->show($callback, 'JSONP');
    }

	public function json($data=array())
	{
		if(!empty($data)){
			$this->set_data($data);
		}
		$this->show('', 'JSON');
	}

	public function response($url='')
	{
		$request = K::$system->request;
		$objctl = &K::$system->objctl;
		if(!$tmpl = $objctl->tmpl){
			$tmpl = $objctl->pagedata['_OO_'];
		}
		if($request['XREQ']){
			if($tmpl){
				$this->_data['html'] = $objctl->output(false);
			}else if($request['MINI'] === 'load'){
				$this->miniload($url);
			}
			$this->show('', 'JSON');
		}else if($request['MINI'] === 'iframe'){
			$this->miniframe($url);
		}else if($tmpl){
			$objctl->output();
		}else if($url){
			$this->show($url, 'HTML');
		}else if($forward = $this->_data['forward']){
			$this->show($forward, 'HTML');
		}else if($forward = $request['forward']){
			$this->show($forward, 'HTML');
		}else{
			$this->show(K::M('helper/link')->mklink('index'), 'HTML');
		}
	}

	public function miniload($url='')
	{
		$pager = $this->_data;
		$pager['error'] = $this->error;
		$pager['message'] = $this->_message;
		if(is_array($url)){
			foreach($url as $k=>$v){
				$url_list[$k]['title'] = $v[0]; 
				$url_list[$k]['link'] = $v[1]; 
			}
			$pager['link'] = $url[0][1];
			$pager['url_list'] = $url_list;
		}else if($url){
			$pager['link'] = $url;
		}else if($pager['forward']){
			$pager['link'] = $pager['forward'];
		}
		$pager['message'] = implode(",",$pager['message']);
		$output = K::M('system/frontend');
		$output->assign('pager', $pager);
		if(defined('IN_ADMIN')){
			$output->display('admin:page/miniload.html');
		}else{
			$output->display('view:page/miniload.html');
		}
		exit();		
	}

	public function miniframe($url='')
	{
		$pager = $this->_data;
		$pager['error'] = $this->error;
		$pager['message'] = $this->_message;
		if(is_array($url)){
			foreach($url as $k=>$v){
				$url_list[$k]['title'] = $v[0]; 
				$url_list[$k]['link'] = $v[1]; 
			}
			$pager['link'] = $url[0][1];
			$pager['url_list'] = $url_list;
		}else if($url){
			$pager['link'] = $url;
		}else if($pager['forward']){
			$pager['link'] = $pager['forward'];
		}
		$pager['message'] = implode(",",$pager['message']);
		$output = K::M('system/frontend');
		$output->assign('pager', $pager);
		if(defined('IN_ADMIN')){
			$output->display('admin:page/miniframe.html');
		}else{
			$output->display('view:page/miniframe.html');
		}
		exit();
	}

	public function redirect($url)
	{
		header("location:{$url}");
		exit();
	}
}