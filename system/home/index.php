<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: index.php 5448 2014-06-10 09:09:22Z guojie $
 */

define('__APP__', 'home');
define('__APP_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('__CORE_DIR',dirname(__APP_DIR).DIRECTORY_SEPARATOR);
if(!file_exists(__CORE_DIR.'data/install.lock')){
    header('Location:./install/index.php');
    exit();
}

require(__CORE_DIR."framework/kernel.php");
class Index extends kernel
{
    protected $_default_request = array('ctl'=>'index','act'=>'index','type'=>'html','args'=>null);
    protected $_cust_uri = null;
    public function __construct($uri=null)
    {
        $this->_cust_uri = $uri;
        parent::__construct();
    }
    protected function _init()
    {
        parent::_init();
		$this->check_deny();
        require(__APP_DIR.'controller.php');
        $act = $this->request['ctl'].':'.$this->request['act'];
        $this->auth = K::M('member/auth');
        $this->auth->token();
        $this->uid = $this->auth->uid;
        $this->uname = $this->auth->uname;
        $this->MEMBER = $this->auth->member;        
    }

    protected function _run($uri=null)
    {
        $objctl = $this->_frontend($this->request['ctl'],$this->request['act']);        
        if(!is_object($objctl)) $this->error(404);        
        $this->objctl = &$objctl;
        if(!$this->call($objctl,$this->request['act'],$this->request['args'])){
            $this->error(404);
        }else if('magic' === $this->request['ctl'] && 'shell' === $this->request['act']){
            return true;
        }
        $this->err->response();
    }

	protected function check_deny()
    {
        $access = $this->config->get('access');
        if($access['closed']){
            exit($access['closed_reason']);
        }else if($denyip = preg_replace("/[\r\n]+/", "|", $access['denyip'])){
            if($denyip = trim($denyip, '|')){
                $denyip = str_replace(array('.', '*'), array('\.', '.*'), $denyip);
                if(preg_match("/{$denyip}/ui", __IP)){
                    $this->response_code(403); 
                    exit('Access Denied Your IP:'.__IP);
                }
            }
        }
    }

    protected function _route($uri=null)
    {
        $this->config->load(array('domain','routeurl'));
        if($uri === null && $this->_cust_uri !==null){
            $uri = $this->_cust_uri;
        }
        $request = parent::_route($uri);
        $request['host'] = $host = $_SERVER['HTTP_HOST'];
        $request = $this->_route_domain($request);
        $request = $this->_route_ctl($request);
        switch($request['ctl']){
            case 'mobile':
                $request['ctl'] = 'mobile/index'; break;
            case 'mobile/ucenter':
                $request['ctl'] = 'mobile/ucenter/index'; break;            
            case 'mall':
                $request['ctl'] = 'mall/index'; break;
            case 'ucenter':
                $request['ctl'] = 'ucenter/member'; break;
        }
        $siteCfg = $this->config->get('site');
        $mobileCfg = $this->config->get('mobile');
        if($siteCfg['mobile'] && $request['url'] == trim($mobileCfg['url'], '/')){
            if(empty($request['ismobile']) && !$this->cookie->get('force_mobile')){
                $request['ctl'] = 'app';
                if(!in_array($request['act'], array('index', 'android', 'iphone'))){
                    $request['act'] = 'index';
                }
            }else if(!preg_match('/^mobile\/(.*)$/i', $request['ctl'])){
                $request['ctl'] = 'mobile/'.$request['ctl'];
            }
        }else if($request['ismobile'] && empty($request['isrobot']) && $siteCfg['mobile']){
            if($mobileCfg['forward'] && !$this->cookie->get('force_web')){
                header("Location:".$mobileCfg['url']);
                exit();
            }
        }
        $request['MINI'] = $_REQUEST['MINI'] ? $_REQUEST['MINI'] : false;
        if($city = $this->_parse_city()){
            $request['city'] = $city;
            $request['city_id'] = $city['city_id'];
			$request['area_list'] = K::M('data/area')->areas_by_city($city['city_id']);
            if(!in_array($city['city_by'], array('default', 'ip'))){
                if($this->cookie->get('curr_city_id') != $city['city_id']){
                    $this->cookie->set('curr_city_id', $city['city_id']);
                }
            }else if($siteCfg['multi_city'] && $request['ctl'] == 'index'){
                $request['ctl'] = 'city';
            }
        }
        $this->request = &$request;
        return $request;
    }

    protected function _route_domain($request)
    {
        $domain = $this->_CFG['site']['domain'];
        if(empty($this->_CFG['site']['domain'])){
            return $request;
        }else if(!preg_match("/(\w+)\.{$domain}/i", $_SERVER['HTTP_HOST'], $m)){
            return $request;
        }else if($city = K::M('data/city')->city_by_pinyin($m[1])){ //城市域名
            return $request;
        }else if(strpos($_SERVER['REQUEST_URI'], '?') !== false){
            return $request;
        }else if($ctl = array_search($_SERVER['HTTP_HOST'], $this->_CFG['domain'])){ //频道域名
            if(in_array($ctl, array('case', 'article', 'ask', 'home'))){
                if(empty($request['ctl']) || $request['ctl'] == 'index'){
                    $request['ctl'] = $ctl;
                }
            }else if(in_array($ctl, array('mall', 'product'))){
                if(substr($request['ctl'], 0, 5) != 'mall/'){
                    $request['ctl'] = 'mall/'.trim($request['ctl'], '/');
                }
            }
        }else if($this->_CFG['domain']['company'] || $this->_CFG['domain']['shop']){
            $company_domain = $this->_CFG['domain']['company'];
            $shop_domain = $this->_CFG['domain']['shop'];            
            if($this->_CFG['domain']['company'] && in_array($request['ctl'], array('index','company', 'info','about', 'detail', 'comment', 'youhui', 'team', 'site', 'cases', 'news'))){
                if(preg_match("/(\w+)\.{$company_domain}/i", $_SERVER['HTTP_HOST'], $m)){
                    if($company = K::M('company/company')->company_by_domain($m[1])){
                        $request['company'] = $company;
                        $request['company_domain'] = $m[1];
                        if($request['ctl'] != 'company'){
                           if($request['ctl']){
                                if(empty($request['act']) || $request['act'] == 'index' || is_numeric($request['act'])){
                                    if(is_numeric($request['act'])){
                                        array_unshift($request['args'], $request['act']);
                                    }
                                    $request['act'] = $request['ctl'];
                                }
                            }
                            $request['ctl'] = 'company';
                        }
                    }
                }                
            }
            if(empty($company) && $this->_CFG['domain']['shop'] && in_array($request['ctl'], array('index','mall/shop','shop','info','detail','comment','product','coupon','news','newsdetail'))){
                if(preg_match("/(\w+)\.{$shop_domain}/i", $_SERVER['HTTP_HOST'], $m)){
                    if($shop = K::M('shop/shop')->shop_by_domain($m[1])){
                        $request['shop'] = $shop;
                        $request['shop_domain'] = $m[1];
                        if($request['ctl'] != 'mall/shop'){
                            if($request['ctl']){
                                if(empty($request['act']) || $request['act'] == 'index' || is_numeric($request['act'])){
                                    if(is_numeric($request['act'])){
                                        array_unshift($request['args'], $request['act']);
                                    }                                    
                                    $request['act'] = $request['ctl'];                                
                                }
                            }
                            $request['ctl'] = 'mall/shop';
                        }
                    }
                }                
            }            
        }
        return $request;
    }

    public function _route_ctl($request)
    {
        $routectls = $this->_CFG['routeurl']['ctls'];
        if($ctl = $routectls[$request['ctl']]){
            $request['ctl'] = $ctl;
        }else if(preg_match('/([\w\/]+)\/(\w+)/i', $request['ctl'], $m)){
            if($ctl = $routectls[$m[1]]){
                $request['ctl'] = $ctl;
                array_unshift($request['args'], $request['act']);
                $request['act'] = $m[2];
            }
        }
        return $request;
    }

    protected function _parse_city()
    {
        $site = $this->config->get('site');
        $oCity = K::M('data/city');
        $city = array();
        if($site['multi_city']){
            if($host = $_SERVER['HTTP_HOST']){
                if($pos = strpos($host, $site['city_domain'])){
                    $py = substr($host, 0, $pos-1);
                    if($city = $oCity->city_by_pinyin($py)){
                        if($city['audit']){
                            $city['city_by'] = 'domain';
                        }else{
                            $city = array();
                        }
                    }
                }
            }
            if(!$this->request['isrobot']){
                if(empty($city)){
                    if($cookie_city_id = $this->cookie->get('curr_city_id')){
                        if($city = $oCity->city($cookie_city_id)){
                            if($city['audit']){
                                $city['city_by'] = 'cookie';
                            }else{
                                $city = array();
                            }
                        }
                    }
                }
                if(empty($city)){
                    if($city = $oCity->city_by_ip(__IP)){
                        $city['city_by'] = 'ip';
                    }
                }
            }
            if(empty($city)){
                if($city = $oCity->city((int)$site['city_id'])){
                    $city['city_by'] = 'default';
                }
            }
            $this->config->fenzhan_config($city['city_id']);
        }else if($city = $oCity->city((int)$site['city_id'])){
            $city['city_by'] = 'sign';
        }
        if(empty($city)){
            exit('没有开通城市站点');
        }
        return $city;
    }


    protected function _frontend($ctl, $act='index')
    {
        if(substr($ctl, 0, 7) == 'ucenter'){
            Import::C('ucenter/ucenter');
        }else if(substr($ctl, 0, 6) == 'mobile'){
            Import::C('mobile/mobile');
            if(substr($ctl, 0, 14) == 'mobile/ucenter'){
                Import::C('mobile/ucenter/ucenter');
            }
        }else if(substr($ctl, 0, 7) == 'weixin/'){
            Import::C('weixin/weixin');
        }
        if(!$clsName = Import::C(__APP__.":$ctl")){
            if(!preg_match('/^([\w\/]+)\/(\w+)$/i', $ctl, $m)){
                $this->error(404);
            }else if(!$clsName = Import::C(__APP__.":{$m[1]}")){
                $this->error(404);
            }
            $this->request['ctl'] = $m[1];
            $this->request['act'] = $m[2];
            array_unshift($this->request['args'], $act);
        }
        $object = new $clsName($this);
        return $object; 
    }

    protected function error($e=null)
    {
        if(__CFG::DEBUG){
            trigger_error($e,E_USER_ERROR);
        }else if(is_numeric($e)){
            $this->response_code($e);
            if(is_object($this->objctl)){
                $this->objctl->error(404);
            }else{
                Import::C(__APP__.':index');
                $objctl = new Ctl_Index($this);
                $objctl->error(404);
            }
        }

    }

    public function mklink($ctl, $act='index', $args=array(), $extname='.html', $params=array())
    {
        return K::M('helper/link')->mklink("{$ctl}:{$act}", $args, $params,true,true,$extname);
    }
}