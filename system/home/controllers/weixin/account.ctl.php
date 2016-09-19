<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Account extends Ctl_Weixin
{

    
    public function index()
    {
        
    }

    //授权登录
    public function login($scene_id)
    {

        if($openid = $this->access_openid()){
            if(!$row = K::M('weixin/authcode')->detail($scene_id)){
                //
            }else if($m = K::M('member/weixin')->detail_by_openid($openid)){
                //授权登录成功
                K::M('weixin/authcode')->update($scene_id, array('status'=>2, 'uid'=>$m['uid']));
                $pager = array('page_title'=>'授权登录成功', 'message'=>'授权网页登录成功');
                $this->pagedata['pager'] = $pager;
                $this->tmpl = 'weixin/page/success.html';
            }else if($client = $this->wechat_client()){
                //未绑定帐号的情况下,直接创建帐号
                if($wx_info = $client->getUserInfoById($openid)){
                    if($m = K::M('member/weixin')->create_account($wx_info)){
                        K::M('weixin/authcode')->update($scene_id, array('status'=>2, 'uid'=>$m['uid']));
                    }
                }
                $pager = array('page_title'=>'授权登录成功', 'message'=>'授权网页登录成功');
                $this->pagedata['pager'] = $pager;
                $this->tmpl = 'weixin/page/success.html';
            }
        }
    }

    public function bind()
    {

    }

    public function reg()
    {

    }

}