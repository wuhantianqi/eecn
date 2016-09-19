<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Tenders extends Ctl_Weixin
{
    
    public function index()
    {
        exit();
    }

    public function mytenders($openid=null)
    {
        if(empty($openid)){
            $openid = $this->access_openid();
        }
        if($client = $this->wechat_client()){
            if(!$wx_info = $client->getUserInfoById($openid)){
                exit('您没有绑定,不能查看招标');
            }
            $this->pagedata['wx_info'] = $wx_info;
            if($items = K::M('weixin/tenders')->items_by_openid($openid)){
                $this->pagedata['items'] = $items;                
            }
            $this->tmpl = 'weixin/tenders/mytenders.html';
        }else{
            exit('参数错误');
        }
    }

    public function detail($tenders_id)
    {
        if(!$tenders_id = (int)$tenders_id){
            exit('参数错误');
        }else if(!$tenders = K::M('weixin/tenders')->detail($tenders_id)){
            exit('您要查看的招标不存在');
        }else if(!$openid = $this->access_openid()){
            exit('您没有邦定微信帐号');
        }else if($tenders['openid'] != $openid){
            exit('您没有邦定微信帐号');
        }else if($client = $this->wechat_client()){
            if(!$wx_info = $client->getUserInfoById($openid)){
                exit('您没有绑定,不能查看招标');
            }
            $this->pagedata['wx_info'] = $wx_info;
            $this->pagedata['tenders'] = $tenders;
            $this->tmpl = 'weixin/tenders/detail.html';
        }else{
            exit('您没有绑定招标信息');
        }
    }

}