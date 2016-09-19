<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author  shzhrui<anhuike@gmail.com>
 * $Id: controller.php 2034 2013-12-07 03:08:33Z langzhong $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class CTL extends Factory
{
    
    public $MOD = array();

    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->cookie = $system->cookie;
        $this->InitializeApp();
        register_shutdown_function(array(&$this,'shutdown'));
    }


    //初始化当前应用程序控制器
    protected function InitializeApp()
    {   
        $this->err->template('admin:page/notice.html');
        $this->system->objctl = &$this;
        //权限检测
        $this->admin = &$this->system->admin;
        if(!$this->check_priv()){
            $this->err->add('您没有权限操作',-1);
            $this->err->response();
        }
    }

    protected function _init_pagedata()
    {
        parent::_init_pagedata();
        $this->pagedata['MOD'] = $this->MOD;
        $this->pagedata['ADMIN'] = $this->admin->admin;
        $this->pagedata['OATOKEN'] = $this->system->OATOKEN;
        $this->pagedata['pager']['url'] = __APP_URL;
        $this->pagedata['pager']['res'] = __CFG::RES_URL;
        //$this->pagedata['pager']['request'] = $this->request;
        $this->pagedata['request'] = $this->request;
        $output = K::M('system/frontend');
        $output->setCompileDir(__CFG::DIR.'data/tpladmin');
    }
    
    
    /**
     * 权限检测
     * return bool
     */
    protected function check_priv($ctl=null, $act=null)
    {
        $ctl = $ctl ? $ctl : $this->request['ctl'];
        $act = $act ? $act : $this->request['act'];
        if($ctl == 'index'){ //通用控制器登录权限
            $this->MOD = array('mod_id'=>0,'module'=>'module','ctl'=>$ctl,'act'=>'act','title'=>'通用控制器');
            return true;
        }else if($this->MOD = K::M('module/view')->ctlmap($ctl, $act)){
            if($this->admin->check_priv($this->MOD['mod_id'])){
                return true;
            }
        }
        return false;     
    }

    protected function logs($title='',$data=array())
    {
        return false;
        $admin_id = $this->admin->admin_id;
        $admin_name = $this->admin->admin_name;
        $action = $this->request['ctl'].':'.$this->request['act'];
        $title = $title ? $title : $this->MOD['title'];
        $data = $data ? $data : $this->request['uri'];
        $admin = $this->admin;
        return K::M('magic/logs')->write($admin_id,$admin_name,$action,'管理日志',$title,$data);
    }

    public function city_id($city_id)
    {
        if(CITY_ID && CITY_ID != $city_id){
            $city_id = CITY_ID;
        }
        return $city_id;
    }

    public function check_city($city_id)
    {
        if(CITY_ID && CITY_ID != $city_id){
            $this->err->add('不可越权操作', 403)->response();
        }
        return true;
    }

    public function verify_city($city_id)
    {
        if(CITY_ID && CITY_ID != $city_id){
            $this->err->add('不可越权操作', 403)->response();
        }
        return true;        
    }

    public function shutdown()
    {
        return false;
        //system logs
        if($this->MOD['syslog']){
            $admin_id = $this->admin->admin_id;
            $admin_name = $this->admin->admin_name;
            $action = $this->MOD['ctl'].':'.$this->MOD['act'];
            $title = $this->MOD['title'];
            $data = array();
            $data['reqest'] = $this->request;
            K::M('magic/logs')->write($admin_id,$admin_name,$action,'系统日志',$title,$data);   
        }
    }
}