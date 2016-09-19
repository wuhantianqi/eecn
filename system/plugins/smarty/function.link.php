<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: function.link.php 2680 2014-01-02 02:47:32Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

function smarty_function_link($params, &$smarty)
{   
    static $oLink = null;
    if($oLink === null){
         $oLink = K::M('helper/link');
    }
    $ctl = $params['ctl'] ? $params['ctl'] : null;
    $extname = isset($params['extname']) ? $params['extname'] : '.html';
    $args=isset($params['args']) ? $params['args'] : array();
    for($i=0; $i<10; $i++){
        if(isset($params["arg{$i}"])){
            $args[$i] = $params["arg{$i}"];
            unset($params["arg{$i}"]);
        }
    }
    unset($params['ctl'], $params['extname'], $params['args']);
    if(defined('IN_ADMIN') || defined('IN_FENZHAN')){
        $type = $params['type'];
        return $oLink->mkctl($ctl, $type, $args, $extname, $params);
    }else{
        if($params['city']){
            $http = 'city:'.$params['city'];
        }else if(isset($params['http'])){
            $http = $params['http'];
        }else if(defined('IN_MOBILE')){
            $http = 'mobile';
        }else{
            $http = 'true';
        }
        $http = $http === 'false' ? false : $http;
        $rewrite = isset($params['rewrite']) ? $params['rewrite'] : true;
        $rewrite = $rewrite === 'false' ? false : $rewrite;
        unset($params['http'], $params['rewrite'], $params['city']);
        return $oLink->mklink($ctl, $args, $params, $http, $rewrite, $extname);
    }
}