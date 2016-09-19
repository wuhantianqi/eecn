<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Index extends Ctl
{
    
    public function index()
    {
        
    }

    public function news($reply_id)
    {
        if(!$reply_id = (int)$reply_id){
            $this->error(404);
        }else if(!$reply = K::M('weixin/reply')->detail($reply_id)){
            $this->error(404);
        }else if($reply['jumpurl'] && strpos($reply['jumpurl'],'http') !== false){
            K::M('weixin/reply')->update_count($reply_id, 'views');
            header("Location:".$reply['jumpurl']);
            exit();
        }else{
            K::M('weixin/reply')->update_count($reply_id, 'views');
            $this->pagedata['weixin'] = K::M('weixin/weixin')->detail($reply['wx_id']);
            $this->pagedata['reply'] = $reply;
            $this->tmpl = 'weixin/reply/detail.html';
        }
    }

    public function coupon()
    {

    }


}