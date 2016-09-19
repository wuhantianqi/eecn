<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
/*
     * ****** 钩子列表 ******
     * receiveAllStart
     * receiveMsg::text
     * receiveMsg::location
     * receiveMsg::image
     * receiveMsg::video
     * receiveMsg::link
     * receiveMsg::voice
     * receiveEvent::subscribe
     * receiveEvent::unsubscribe
     * receiveEvent::scan
     * receiveEvent::location
     * receiveEvent::click
     * receiveAllEnd
     * accessCheckSuccess
     * 404
*/

class Mdl_Weixin_Response extends Model 
{    
    

    public function text()
    {
        list($data) = $param;
        self::_init();
        global $_G;
        $data['content'] = diconv($data['content'], 'UTF-8');
        $isloginkeyword = self::_custom('text', $data['content']);
        if(!$_G['wechat']['setting']['wsq_allow']) {
            return;
        }
        $authcode = C::t('#wechat#mobile_wechat_authcode')->fetch_by_code($data['content']);
        if(!$authcode || $authcode['status']) {
            if($isloginkeyword) {
                wsq::report('loginclick');
                self::_show('access', $data['from']);
            }
//          echo WeChatServer::getXml4Txt(lang('plugin/wechat', 'wechat_response_text_codeerror'));
        } else {
            wsq::report('sendnum');
            self::_show('sendnum', $data['from']."\t".$authcode['sid'], 60);
        }
    }

    public function msglocaction()
    {

    }

    public function image()
    {

    }

    public function video()
    {

    }

    public function link()
    {

    }

    public function voice()
    {

    }

    public function subscribe()
    {

    }

    public function unsubscribe()
    {

    }

    public function scan()
    {

    }

    public function location()
    {

    }

    //事件
    public function click()
    {

    }

}