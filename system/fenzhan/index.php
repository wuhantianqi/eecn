<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: index.php 6177 2014-08-28 02:57:24Z youyi $
 */

define('__APP__', 'fenzhan');
define('__APP_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('__CORE_DIR',dirname(__APP_DIR).DIRECTORY_SEPARATOR);
//define('IN_ADMIN', true);
define('IN_FENZHAN', true);
if(!file_exists(__CORE_DIR.'data/install.lock')){
    header('Location:../install/index.php');
    exit();
}
require(__CORE_DIR."framework/kernel.php");
class Index extends kernel
{
    protected $_default_request = array('ctl'=>'index','act'=>'index','type'=>'html','args'=>null);
    protected function _init()
    {   
        $guest_allow = array('index:login','index:verify','index:loginout');
        if($OFZTOKEN = trim($_POST['OFZTOKEN'])){
            if($a = $this->load_model('secure/crypt')->hexarr($OFZTOKEN)){
                if($a['FZTOKEN'] && $a['AGENT']){
                    $_SERVER['HTTP_USER_AGENT'] = $a['AGENT'];
                    $_COOKIE[__CFG::C_PREFIX.'FZTOKEN'] = $a['FZTOKEN'];
                }
            }
        }        
        parent::_init();
        require(__APP_DIR.'controller.php');
        $act = $this->request['ctl'].':'.$this->request['act'];
        $this->fenzhan = K::M('fenzhan/auth');
        if(!$this->fenzhan->token()){
            if(!in_array($act,$guest_allow)){
                header("Location:?index-login");
                exit();
            }
        }else{
            if($this->fenzhan->admin['city_id'] != $this->request['city_id']){
                $city = K::M('data/city')->city($this->fenzhan->admin['city_id']);
                header("Location:".$city['siteurl'].'/fenzhan');
                exit();
            }            
        }
        $this->fz_uid = $this->fenzhan->fz_uid;
        $this->fz_name = $this->fenzhan->fz_name;
        define('CITY_ID',  $this->fenzhan->admin['city_id']);
    }

    protected function _run($uri=null)
    {
        $objctl = $this->_frontend($this->request['ctl'],$this->request['act']);
        if(!is_object($objctl)) $this->error(404);
        if($objctl->__call){
            array_unshift($this->request['args'], $this->request['act']);
            $this->request['act'] = $objctl->__call;        
        }
        if(!$this->call($objctl,$this->request['act'],$this->request['args'])){
            trigger_error("not find {$this->request[ctl]}:{$this->request[act]}");
            $this->error(404);
        }
        $this->err->response();
    }

    protected function _route($uri=null)
    {
        $request = parent::_route($uri);
        $request['host'] = $host = $_SERVER['HTTP_HOST'];
        $siteCfg = $this->config->get('site');
        $request['MINI'] = $_REQUEST['MINI'] ? $_REQUEST['MINI'] : false;
        if($city = $this->_parse_city()){
            $request['city'] = $city;
            $request['city_id'] = $city['city_id'];
            $request['area_list'] = K::M('data/area')->areas_by_city($city['city_id']);
        }else{
            $this->error(404);
        }
        $this->request = &$request;
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
                        $city['city_by'] = 'domain';
                    }
                }
            }     
        }else{
            exit('网站没有开通分站功能');
        }
        return $city;
    }    

    protected function _frontend($ctl, $act='index')
    {
        if(!$clsName = Import::C(__APP_APP.":$ctl")){
            $this->error("ctl:{$ctl} not find!!!");
        }
        $object = new $clsName($this);
        return $object; 
    }

    public function mklink($ctl,$act='index',$args=array(),$extends='.html',$gets=array())
    {

        if($args && is_array($args)){
            $args = '-'.implode('-', $args);
        }else{
            $args = '';
        }
        return __APP_URL."/?{$ctl}-{$act}{$args}{$extends}";
    }
}
new Index();