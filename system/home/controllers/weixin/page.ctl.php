<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Page extends Ctl 
{
    
    public function index()
    {
        
    }

    public function leaflets($wx_id)
    {
        if(!$wx_id = (int)$wx_id){
            $this->error(404);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->error(404);
        }else{
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'weixin/weixin/leaflets.html';
        }
    }


}