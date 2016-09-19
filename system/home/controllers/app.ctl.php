<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: app.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */
class Ctl_App extends Ctl {
    
    
    public function index(){
        
         $this->system->config->get('mobile');
         $this->tmpl = 'app/app.html';
    }
    
    public function android()
    {
        $cfg = $this->system->config->get('mobile');
        if($cfg['down_android']){
            $this->tmpl = 'mobile/page/android.html';
        }else{
            $this->tmpl = 'mobile/page/add2home.html';
        }
    }

    public function iphone()
    {
        $cfg = $this->system->config->get('mobile');
        if($cfg['down_iphone']){
            $this->tmpl = 'mobile/page/apple.html';
        }else{
            $this->tmpl = 'mobile/page/add2home.html';
        }
        $this->tmpl = 'mobile/page/add2home.html';
    }
  
}