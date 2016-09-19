<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin extends Ctl_Ucenter 
{

    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->wechatCfg = $system->config->get('wechat');
    }
    
    public function index()
    {
        $this->info();
    }    

    public function info()
    {
        $weixin = $this->ucenter_weixin();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'wx_name,type,weixin,wx_sid,type,appid,secret')){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['weixin_face']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'weixin')){
                            $data['face'] = $a['photo'];
                        }
                    }
                }
                if($weixin){
                    if(K::M('weixin/weixin')->update($weixin['wx_id'], $data)){
                        $this->err->add('修改微信公众号成功');
                    }
                }else{
                    $data['uid'] = $this->uid;
                    $data['city_id'] = $this->ucenter_city_id;
                    if($wx_id = K::M('weixin/weixin')->create($data)){
                        $this->err->add('设置微信公众号成功');
                    }
                }
            }
        }else{
            $this->tmpl = 'ucenter/weixin/info.html';
        }
    }

    public function config()
    {
       $weixin = $this->ucenter_weixin();
       $this->pagedata['wechat_token'] = md5(__CFG::SECRET_KEY.__CFG::Authorize);
       $this->tmpl = 'ucenter/weixin/config.html'; 
    }

    public function welcome()
    {
        $weixin = $this->ucenter_weixin();
        $addon = $weixin['addon'];
        if($data = $this->checksubmit('data')){
            $data = $this->check_fields($data, 'type,reply_id,content');
            if($data){
                $addon['welcome'] = $data;
                if(K::M('weixin/weixin')->update($weixin['wx_id'], array('addon'=>$addon))){
                    $this->err->add('设置微信关注欢迎信息成功');
                }
            }
        }else{
            if($reply_id = (int)$addon['welcome']['reply_id']){
                if($reply = K::M('weixin/reply')->detail($reply_id)){
                    if($reply['wx_id'] == $weixin['wx_id']){
                        $this->pagedata['reply'] = $reply;
                    }
                }
            }
            $this->pagedata['welcome'] = $addon['welcome'];
            $this->tmpl = 'ucenter/weixin/welcome.html';
        }        
    }

    public function leaflets()
    {
        $weixin = $this->ucenter_weixin();
        $addon = $weixin['addon'];
        if($data = $this->checksubmit('data')){
            $data = $this->check_fields($data, 'title,content,copyright');
            if($attach = $_FILES['weixin_photo']){
                if(UPLOAD_ERR_OK == $attach['error']){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin', $addon['leaflets']['photo'])){
                        $data['photo'] = $a['photo'];
                    }
                }
            }
            if($data){
                $addon['leaflets'] = $data;
                if(K::M('weixin/weixin')->update($weixin['wx_id'], array('addon'=>$addon))){
                    $this->err->add('设置微信推广页成功');
                }
            }
        }else{
            $this->pagedata['leaflets'] = $addon['leaflets'];
            $this->tmpl = 'ucenter/weixin/leaflets.html';
        }
    }
}