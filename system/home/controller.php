<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: controller.php 5759 2014-07-01 08:22:20Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl extends Factory
{
    protected $_allow_fields = '';
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->cookie = $system->cookie;
        $this->InitializeApp();
        //register_shutdown_function(array(&$this,'shutdown'));
    }

    //初始化当前应用程序控制器
    protected function InitializeApp()
    {   
        $this->err->template('view:page/notice.html');
        $this->system->objctl = &$this;
        $this->auth = &$this->system->auth;
        $this->MEMBER = &$this->system->MEMBER;
        $this->uid = $this->MEMBER['uid'];
        $this->uname = $this->MEMBER['uname'];  
        $this->seo = K::M('helper/seo');
    }

    protected function _init_pagedata()
    {
        $CONFIG = $this->system->config->load(array('site','comment','score'));
        $site = $CONFIG['site'];
        parent::_init_pagedata();
        $theme = $this->default_theme();
        $this->pagedata['MEMBER'] = $this->MEMBER;        
        $this->pagedata['pager']['url'] = $site['url'];
        $this->pagedata['pager']['res'] = __CFG::RES_URL;
        $this->pagedata['COUNT'] = K::M('magic/magic')->sitecount();
        $this->pagedata['pager']['dateline'] = $this->request['city_id'];
        $this->pagedata['pager']['theme'] = $site['siteurl'].'/themes';
        $this->pagedata['SEO'] = $this->seo->_SEO;
        $this->pagedata['nowtime'] = $this->pagedata['pager']['dateline'] = __TIME;
        $this->pagedata['VER'] = JH_RELEASE;
        $output = K::M('system/frontend');
        $output->setCompileDir(__CFG::DIR.'data/tplcache');
    }

    //数组键值过滤。通常用户过滤不允许前台修改的表字段
    public function check_fields($data, $fields=null)
    {
        if($fields === null){
            $fields = $this->_allow_fields;
        }
        if(!is_array($fields)){
            $fields = explode(',', $fields);
        }
        foreach((array)$data as $k=>$v){
            if(!in_array($k, $fields)){
                unset($data[$k]);
            }
        }       
        return $data;
    }

    public function check_login()
    {
        if(!$this->uid){
            if($this->request['XREQ'] || $this->request['MINI']){
                $this->err->add('很抱歉，你还没有登录不能访问', 101);
            }else{
                $this->tmpl = 'passport/login.html';
            }
            $this->err->response();
            exit();
        }
        return true;
    }
    
    protected function set_resource_view(&$output)
    {
        $theme = $this->default_theme();
        $output->setTemplateDir(__CFG::TMPL_DIR.$theme['theme']);
        $output->registerFilter('pre', array($this, 'smarty_pre_filter'));
        $output->registerFilter('post', array($this, 'smarty_post_filter'));
        $output->default_template_handler_func = array($this, 'theme_default_handler');
    }

    public function smarty_pre_filter($source, $smarty)
    {
        //$s = array('/(<\{(KT|AD|calldata)[^\}]*\}>)/', '/(<\{\/(KT|AD|calldata)\}>)/');
        //$r = array('\1<{literal}>', '<{/literal}>\1');
        $s = array(
                '/(<\{KT[^\}]*\}>)/', '/(<\{\/KT\}>)/', 
                '/(<\{AD[^\}]*\}>)/', '/(<\{\/AD\}>)/',
                '/(<\{calldata[^\}]*\}>)/', '/(<\{\/calldata\}>)/'
                );
        $r = array('\1<{literal}>', '<{/literal}>\1','\1<{literal}>', '<{/literal}>\1','\1<{literal}>', '<{/literal}>\1');
        return preg_replace($s, $r, $source);
    }

    public function smarty_post_filter($source, $smarty)
    {
        if($file_dependency = $smarty->properties['file_dependency']){
            list($hash, $info) = each($file_dependency);
            $tmpl = $smarty->template_resource;
            //__CFG::TMPL_DIR
            if($info[2] == 'file'){
                $theme = substr($info[0], strlen(__CFG::TMPL_DIR), -strlen($tmpl));
                $theme = str_replace('\\', '/', $theme);
                $theme = str_replace('/', '', $theme);
                $site = $this->system->config->get('site');
                $theme_url = trim($site['url'], '/').'/themes/'.$theme;
                return preg_replace('/%THEME%/', $theme_url, $source); 
            }
        }
        return $source;
    }

    public function theme_default_handler($type, $name, &$content, &$modified, Smarty $smarty)
    {
        if($type == 'file'){
            $file = __CFG::TMPL_DIR.'default'.DIRECTORY_SEPARATOR.$name;
            return $file;
        }
        return false;
    }   

    public function error($error)
    {
        if(is_numeric($error)){
            $this->system->response_code($error);
        }
        if(defined('IN_MOBILE')){
            $this->tmpl = "mobile/page/{$error}.html";
        }else{
            $this->tmpl = "page/{$error}.html";
        }
        $this->output();
    }

    public function shutdown()
    {
        //system logs
    }

    protected function default_theme()
    {
        static $theme = null;
        if($theme === null){
            if($city_theme_id = (int)$this->request['city']['theme_id']){
                $theme = K::M('system/theme')->theme(null, $city_theme_id);
            }
            if(empty($theme)){
                $theme = K::M('system/theme')->default_theme();
            }
        }
        return $theme;  
    }

    protected function check_company(&$company_id=null)
    {

        if($company_id = (int)$company_id){
            if($this->request['company_domain'] && ($company_id == $this->request['company']['company_id'])){
                $company = $this->request['company'];
            }else if(!$company = K::M('company/company')->detail($company_id)){
                $this->error(404);
            }
        }else if($this->request['company_domain']){            
            $company = $this->request['company'];
            $company_id = $company['company_id'];
        }
        if(empty($company['audit']) && (empty($this->uid) || ($this->uid != $company['uid']))){
            $this->err->add('公司正在审核中，不能访问', 212);
            $this->err->response();
        }
        if($uid = $company['uid']){
            $company['member'] = K::M('member/member')->detail($uid);
        }
        $theme = $this->default_theme();
        $skin_cfg = __CFG::TMPL_DIR.$theme['theme'].'/company/config.php';
        if(!file_exists($skin_cfg)){
            $skin_cfg = __CFG::TMPL_DIR.'default/company/config.php';
        }
        $skins = include($skin_cfg);
        if(!$skin = $company['skin']){
            $skin = 'default';
        }
        $company['skin_cfg'] = $skins[$skin];
        $this->pagedata['company'] = $company;
        K::M('company/company')->update_count($company_id, 'views', 1);
        return $company;               
    }

    protected function check_shop(&$shop_id=null)
    {
        if($shop_id = (int)$shop_id){
            if($this->request['shop_domain'] && $shop_id == $this->request['shop']['shop_id']){
                $shop = $this->request['shop'];
            }else if(!$shop = K::M('shop/shop')->detail($shop_id)){
                $this->error(404);
            }
        }else if($this->request['shop_domain']){            
            $shop = $this->request['shop'];
            $shop_id = $shop['shop_id'];
        }
        if(empty($shop['audit']) && (empty($this->uid) || ($this->uid != $shop['uid']))){
            $this->err->add('商铺审核中不能访问', 212);
            $this->err->response();
        }
        if($uid = $shop['uid']){
            $shop['member'] = K::M('member/member')->detail($uid);
        }
        $theme = $this->default_theme();
        $skin_cfg = __CFG::TMPL_DIR.$theme['theme'].'/shop/config.php';
        if(!file_exists($skin_cfg)){
            $skin_cfg = __CFG::TMPL_DIR.'default/shop/config.php';
        }
        $skins = include($skin_cfg);
        if(!$skin = $shop['skin']){
            $skin = 'default';
        }
        $shop['skin_cfg'] = $skins[$skin];
        $this->pagedata['shop'] = $shop;
        K::M('shop/shop')->update_count($shop_id, 'views', 1);
        return $shop;
    }       
}