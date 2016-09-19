<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: index.ctl.php 6068 2014-08-11 07:39:50Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Index extends Ctl
{
    

    public function index()
    {
        $this->tmpl = 'fenzhan:page/index.html';
        $this->output();
    }

    public function welcome()
    {

        $sysinfo = array(
            'version' => JH_VERSION . ' RELEASE '. JH_RELEASE .' [<a href="http://www.ijh.cc/" class="blue" target="_blank">查看最新版本</a>]',
            'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]',
            'server_os' => PHP_OS,
            'web_server' => $_SERVER["SERVER_SOFTWARE"],
            'php_version' => PHP_VERSION,
            'mysql_version' => mysql_get_server_info(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . '秒',
            'memory_limit' => ini_get('memory_limit'),
            'safe_mode' => (boolean) ini_get('safe_mode') ?  'YES' : 'NO',
            'zlib' => function_exists('gzclose') ?  'YES' : 'NO',
            'curl' => function_exists("curl_getinfo") ? 'YES' : 'NO',
            'timezone' => function_exists("date_default_timezone_get") ? date_default_timezone_get() : 'NO'
        );
        if(function_exists('gd_info')){
            $gd_info = @gd_info();
            $sysinfo['gd_version'] = $gd_info["GD Version"];
        }else{
            $sysinfo['gd_version'] = '<span class="red">NO</span>';
        }
        $this->pagedata['sysinfo'] = $sysinfo;
        $sdaytime = $this->system->sdaytime;
		$city_id = CITY_ID;
		
        $this->pagedata['count']= array(
            'tenders' => K::M('tenders/tenders')->count(" audit=0  AND city_id = '{$city_id}'"),
            'tenders2' => K::M('tenders/tenders')->count(" audit=0 AND city_id = '{$city_id}' AND dateline >='{$sdaytime}' "),
            'company' => K::M('company/company')->count(" audit=0 AND city_id = '{$city_id}' AND closed=0"),
            'designer' => K::M('designer/designer')->count(" audit=0 AND city_id = '{$city_id}' AND closed=0"),
            'shop' => K::M('shop/shop')->count(" audit=0 AND city_id = '{$city_id}' AND closed=0"),
            'mechanic' => K::M('mechanic/mechanic')->count(" audit=0 AND city_id = '{$city_id}' AND closed=0"),
            'designer_yuyue' =>        K::M('designer/yuyue')->count(" dateline >='{$sdaytime}' AND city_id = '{$city_id}' "), 
            'company_yuyue' =>        K::M('company/yuyue')->count(" dateline >='{$sdaytime}' AND city_id = '{$city_id}' "), 
            'shop_yuyue' =>        K::M('shop/yuyue')->count("dateline>='{$sdaytime}' AND city_id = '{$city_id}'"),
            'order' =>        K::M('trade/order')->count(" dateline >='{$sdaytime}' AND closed=0 AND city_id = '{$city_id}'"),       
            'order2' =>        K::M('trade/order')->count("audit=0 AND closed=0 AND order_status>=0 AND city_id = '{$city_id}'"),      
            'verify' =>        K::M('member/verify')->count(" verify = 0 AND city_id = '{$city_id}'"),   
            'tracking' =>  K::M('tenders/track')->count(" dateline >='{$sdaytime}' AND city_id = '{$city_id}'")
        );
        $content = file_get_contents(__CORE_DIR.'admin/view/page/load.html');
        $content = K::M('content/html')->encode($content);
        $this->pagedata['tmpl_content'] = $content;
        $this->tmpl = 'fenzhan:page/welcome.html';
    }

    public function login()
    {
		$request = $this->system->request;
        if($_POST['admin_name']){
            if(!$name = $this->GP('admin_name')){
                $this->err->add('登录名不能为空',401);
            }else if(!$passwd = $this->GP('admin_pwd')){
                $this->err->add('登录密码不能为空',402);
            }else{
				$verifycode_success = true;
                $access = $this->system->config->get('access');
                if($access['verifycode']['admin']){
                    if(!$code = $this->GP('verify_code')){
                         $verifycode_success = false;
                        $this->err->add('验证码不能为空',403);
                    }else if(!K::M('magic/verify')->check($code)){
                        $verifycode_success = false;
                        $this->err->add('验证码不正确',403);
                    }
                    if(!$verify_code = $this->GP('verify_code')){
                        $verifycode_success = false;
                        $this->err->add('验证码不正确', 212);
                    }else if(!K::M('magic/verify')->check($verify_code)){
                        $verifycode_success = false;
                        $this->err->add('验证码不正确', 212);
                    }
                }
                if($verifycode_success){
                    if(K::M('fenzhan/auth')->login($name, $passwd, $request['city_id'])){
                        header("Location:?index.html");
                        exit();
                    }
                }
            }
        }else{
			$this->pagedata['admin'] = $access['verifycode']['admin'];
            $this->tmpl = 'fenzhan:page/login.html';
            $this->output();
        }
        $this->err->show("?index/login.html");
    }

    public function loginout()
    {
        K::M('fenzhan/auth')->loginout();
        $this->err->add("帐户已经安全退出!!");
    }

    public function modifypasswd()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 211);
            }else if(empty($data['oldpasswd'])){
                $this->err->add('旧密码不能为空', 212);
            }else if($data['newpasswd'] != $data['confirmpasswd']){
                $this->err->add('两次密码不相同', 213);
            }else if($this->fenzhan->modifypasswd($data['oldpasswd'], $data['newpasswd'])){
                $this->err->add('修改密码成功,需重新登录');
            }
        }else{
            $this->tmpl = 'fenzhan:admin/admin/modifypasswd.html';
        }
    }

    public function verify()
    {
        K::M('magic/verify')->output();
    }

    public function page($page)
    {
        $uri = $this->request['uri'];
        if(preg_match('/page-(\w+).html/i', $uri, $match)){
            $page = $match[1];
            $this->tmpl = "fenzhan:page/{$page}.html";
        }
    }

    public function top()
    {
        $this->pagedata['top_menu'] = K::M('fenzhan/auth')->tree();
        $this->tmpl = 'fenzhan:context/top.html'; 
    }

    public function ijh()
    {
        $cfg = $this->system->config->get('site_config');
        header("Content-type: image/png");
        echo base64_decode(K::M('secure/crypt')->hexstr($cfg['hash']));
        exit();
    }

    public function context($mid=null)
    {
        $menu_tree = K::M('fenzhan/auth')->tree();
        if(!$mid = intval($mid)){
            $tree = array_shift($menu_tree);
        }else{
            $tree = $menu_tree[$mid];
        }
        $this->pagedata['menu_tree'] = $tree['menu'];
        $this->tmpl = 'fenzhan:context/menu.html';
    }
}