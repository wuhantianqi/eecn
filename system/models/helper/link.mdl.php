<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: link.mdl.php 10470 2015-05-26 01:29:55Z xiaorui $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Helper_Link extends Model
{
    private  $_rootctl = array('article', 'ask', 'tools', 'page', 'help', 'about');
    
    public function mklink($ctl, $args=array(), $params=array(), $http=false, $rewrite=true, $ext='.html')
    {
        static $_CFG = null;
        if($_CFG === null){
            $_CFG = K::$system->_CFG;
        }
        $link = '';
        $request = K::$system->request;
        if(strpos($ctl,':')){
            $a = explode(':',$ctl);
            $ctl = $a[0];
            $act = $a[1];
        }else{
            $act = null;
        }
        if(!$route_ctl = $_CFG['routeurl'][$ctl]){
            $route_ctl = $ctl;
        }
        $ctl_domain = null;
        if($ctl == 'mall/shop'){
            unset($params['shop']);
        }else if($ctl == 'company'){         
            unset($params['company']);
        }
        $rewrite = $rewrite && $_CFG['site']['rewrite'];
        $link = $this->_parse_rewrite($route_ctl, $act, $args, $rewrite, $ext);
        if(in_array($ctl, array('article', 'home', 'ask', 'case'))){
            if($domain = $_CFG['domain'][$ctl]){
                $link = in_array($link, array("{$ctl}", "{$ctl}/", "{$ctl}/index.html", "{$ctl}.html", "{$ctl}.html")) ? '' : $link;         
                return 'http://'.$domain.'/'.$link;
            }
        }else if($ctl == 'mall/product'){
            if($domain = $_CFG['domain']['product']){
                $link = in_array($link, array('mall/product/', 'mall/product/index.html', 'mall/product.html', 'mall/product-index.html')) ? '' : $link;
                return 'http://'.$domain.'/'.$link;
            }
        }else if(substr($ctl, 0, 5) != 'mall/'){
            if($domain = $_CFG['domain']['mall']){
                $link = in_array($link, array('mall/index.html', 'mall/', 'index.html')) ? '' : $link;
                return 'http://'.$domain.'/'.$link;
            }            
        }
        if($link == 'index/'){
            $link = '';
        }
        if(is_array($params)){
            //$link = vsprintf($link, $params);
            $params = http_build_query($params);
        }else if(!is_string($params)){
            $params = '';
        }
        if(empty($rewrite) || 'ajax' === $http || defined('IN_ADMIN') || defined('IN_FENZHAN')){
            $link = "index.php?{$link}";
        }
        if($params){
            if(strpos('?', $link) === false){
                $link .= '?'.$params;
            }else{
                $link .= '&'.$params;
            }
        }
        if($link == 'index.php' || $link == 'index.php?'){
            $link = '';
        }        
        $prefix = '';
        if($http !== false){
            $prefix = $_CFG['site']['siteurl'];
            if((substr($link, 0, 7) == 'mobile/') || ('mobile' === $http)){                
                $mobile = K::$system->config->get('mobile');
                if($mobile['url']){
                    if(substr($link, 0, 7) == 'mobile/'){
                        $link = substr($link, 7);
                    }
                    $prefix = $mobile['url'];
                }
            }else if($_CFG['site']['multi_city'] && $http && is_numeric($http)){
                if($city_id = (int)$http){
                    $city = K::M('data/city')->city($city_id);
                }
                if(empty($city)){
                    $city = $request['city'];
                }
                $prefix = $city['siteurl'];
            }else if($_CFG['site']['multi_city'] && preg_match('/city:(\w+)/', $http, $match)){
                if(is_numeric($match[1])){
                    $city = K::M('data/city')->city($match[1]);
                }else{
                    $city = K::M('data/city')->city_by_py($match[1]);
                }
                if(empty($city)){
                    $city = $request['city'];
                }
                $prefix = $city['siteurl'];
            }else if($_CFG['site']['multi_city']  && $http === 'city'){
                $city = $request['city'];
                $prefix = $city['siteurl'];
            }else if(strpos((string)$http, 'http://') === 0){
                $prefix = $http;
            }else if('www' === $http){
                $prefix = $_CFG['site']['siteurl'];
            }else if('mobile' === $http){
                $mobile = K::$system->config->get('mobile');
                $prefix = $mobile['url'];
            }else if('base' === $http || 'empty' === $http || 'ajax' === $http){
                $prefix = '';
            }else if($ctl_domain){
                $prefix = 'http://'.$ctl_domain;
            }else if($_CFG['site']['multi_city'] && !in_array($ctl, $this->_rootctl)){
                $city = $request['city'];
                $prefix = $city['siteurl'];
            }
            $link = $prefix.'/'.$link;
        }        
        return $link;
    }

    public function mkctl($ctl, $type='button', $args=null, $extname='.html', $attrs=array())
    {
        if(strpos($ctl,':')){
            $a = explode(':',$ctl);
            $ctl = $a[0];
            $act = $a[1];
        }else{
            $act = 'index';
        }
        $link = 'javascript:;'; $attr = ''; $nopriv = false;
        if($type == 'button' || $type == 'submit'){
            $attrs['class'] =  $attrs['class'] ? $attrs['class'] : 'bt-big';
        }
        if(!$mod =K::M('module/view')->ctlmap($ctl,$act)){
            $nopriv = true;
            $attrs['tips'] = '模块不存在';
            $attrs['disabled'] = 'disabled';
            $attrs["class"] = $attrs['class'] ? ($attrs['class'].' disabled') : 'disabled';
        }else if(!$this->check_priv($mod['mod_id'])){
            $nopriv = true;
            $attrs['tips'] = '没有权限';
            $attrs['disabled'] = 'disabled';
            $attrs["class"] = $attrs['class'] ? ($attrs['class'].' disabled') : 'disabled';
        }else{
            if($args === null){
                $args = '';
            }else if(is_array($args)){
                $a = '';
                foreach($args as $k=>$v){
                    $a .= "-{$v}";
                }
                $args = $a;
            }else if(!$args){
                $args = '';
            }
            $args = trim($args,'-');
            $args = $args ? "-{$args}" : '';
            $link = "?{$ctl}-{$act}{$args}{$extname}";
            if($type == 'submit'){
                $attr = 'action="'.$link.'" '.$attr;
            }else if($type == 'button'){
                $attr = 'action="'.$link.'" '.$attr;
            }else{
                $attr = 'href="'.$link.'" '.$attr;
            }
        }
        foreach((array)$attrs as $k=>$v){
            if(strlen($v)>5 && substr($v, 0, 5) == 'none:'){ //不显示的属性
                $attrs[$k] = substr($v, 5);
                continue;
            }else if(strlen($v)>5 && substr($v, 0, 5) == 'mini:'){
                if($nopriv){ //没有权限指令忽略
                    continue;
                }
                $k = "mini-{$k}";
                $v = substr($v,5);
            }else if(strlen($v)>4 && substr($v, 0, 4) == 'win:'){
                if($nopriv){ //没有权限指令忽略
                    continue;
                }
                $k = "win-{$k}";
                $v = substr($v,4);
            }
            $attr .= $k.'="'.$v.'" ';
        }
        $title = $attrs['title'] ? $attrs['title'] : $mod['title']; 
        if($nopriv && $attrs['priv'] == 'hide'){
            return '';
        }else if($type == 'submit'){
            $title = isset($attrs['value']) ? $attrs['value'] : $title;
            $attr = $value.' '.$attr;
            $attr = $nopriv ? "type='submit' {$attr}" : $attr;
            //$attr =  $attrs['class'] ? $attr : "{$attr} class='bt-big'";
            return "<button {$attr}>{$title}</button>";            
        }else if($type == 'button'){
            $title = isset($attrs['value']) ? $attrs['value'] : $title;
            $attr = $value.' '.$attr;
            $attr = $nopriv ? "type='button' {$attr}" : $attr;
            //$attr =  $attrs['class'] ? $attr : "{$attr} class='bt-big'";
            return "<button {$attr}>{$title}</button>";
        }else if($nopriv){
            return "<label {$attr}>{$title}</label>";
        }else{
            return "<a {$attr}>{$title}</a>";
        }
    }

	protected function check_priv($mod_id)
	{
		if(defined('IN_FENZHAN')){
			return K::$system->fenzhan->check_priv($mod_id);
		}else{
			return K::$system->admin->check_priv($mod_id);
		}
	}

    protected function _parse_rewrite($ctl, $act=null, $args=array(), $rewrite=true, $ext='.html')
    {
        if(substr($ctl, 0, 8) == 'ucenter/' || substr($ctl, 0, 7) == 'weixin/' || substr($ctl, 0, 7) == 'mobile/'){
            $route_type = 0;
        }else{
            $route_type = K::$system->_CFG['routeurl']['route_type'];
        }       
        if($route_type){
            $link = $ctl ? $ctl.'/' : '';
            if((!empty($act) && $act != 'index') || !empty($args)){
                $link .= $act;
            }
        }else{
            $link = "{$ctl}";
            $link .= $act ? "-{$act}" : '';
        }
        if(!empty($args)){
            if(is_array($args)){
                $link .= '-'.implode('-', $args);
            }else if(is_string($args)){
                $link .= '-'.trim($args, '-');
                if(strpos($link, '.html')){
                    $ext = '';
                }
            }            
        }
        if(empty($route_type) || !empty($args)){
            $link .= $ext;
        }
        return str_replace('/-', '-', $link);
    }

}