<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */
Import::L('weibo/SaeTOAuthV2.php');
class Mdl_Member_Weibo extends Model
{
    
    protected $_type_id = 2;//微博是2
        
    public function  weibo_url()
    {
        $cfg = K::$system->config->get('connect');
        if(defined('IN_MOBILE')){
            $callback = K::M('helper/link')->mklink('mobile/passport:weibocallback', array(), array(), 'www');
        }else{
            $site = K::$system->config->get('site');
            $callback = K::M('helper/link')->mklink('passport:weibocallback', array(), array(), 'www');
        }
        if (empty($cfg['weibo_is_open'])) {
            $this->err->add('很抱歉网站管理员还未开启微博登录功能', 201);
        }
        else {
           $o = new SaeTOAuthV2($cfg['weibo_app_id'] , $cfg['weibo_app_key'] ); 
           $code_url = $o->getAuthorizeURL($callback);
           return $code_url;
        }
        return false;
    }
    
    public function weibocallback($code)
    {
        $cfg = K::$system->config->get('connect');
        if(defined('IN_MOBILE')){
            $callback = K::M('helper/link')->mklink('mobile/passport:weibocallback', array(), array(), 'www');
        }else{
            $site = K::$system->config->get('site');
            $callback =  K::M('helper/link')->mklink('passport:weibocallback', array(), array(), 'www');
        }
        if (empty($cfg['weibo_is_open'])) {
            $this->err->add('很抱歉网站管理员还未开启微博登录功能', 201);
            return false;
        }
        $keys = array();
        $keys['code'] = $code;
        $keys['redirect_uri'] = $callback;
        $o = new SaeTOAuthV2($cfg['weibo_app_id'] , $cfg['weibo_app_key'] ); 
        try {
            $token = $o->getAccessToken( 'code', $keys ) ;
        } catch (OAuthException $e) {
            $this->err->add($e->getMessage(), 201);
            return false;
        }
        $c = new SaeTClientV2($cfg['weibo_app_id'] , $cfg['weibo_app_key']  , $token['access_token'] );
        $user = $c->get_uid();
        return $this->login($user['uid'], $c);
    }
    

    public function login($openid, $client)
    {
        if(!$connect = K::M('connect/connect')->detail_by_openid($this->_type_id,$openid)){
            $connect['connect_id'] = K::M('connect/connect')->create(array('type'=> $this->_type_id,'open_id'=> $openid));
        }
        if($connect['uid'] && $m = K::M('member/member')->member($connect['uid'])){
            K::M('member/auth')->manager($m['uid']);
            return true;
        }else if(K::$system->uid){
            if($connect['connect_id']){
                K::M('connect/connect')->update($connect['connect_id'], array('uid'=>K::$system->uid, 'dateline'=>__TIME), true);
            }else{
                K::M('connect/connect')->create(array('uid'=>K::$system->uid, 'type'=> $this->_type_id,'open_id'=> $openid, 'dateline'=>__TIME), true); 
            }
            return true; 
        }else{
            $info = $client->show_user_by_id($openid);
            if($info['error_code']){
                $this->err->add($info['error'], 501);
                return false;
            }
            $uinqid = 'WB'.rand(10000000,99999999);
            if(!$uname = K::M('member/account')->check_uname($info['name'])){
                if(!$uname = K::M('member/account')->check_uname('WB'.$info['name'])){
                    $uname = $uinqid;
                }
                $this->err->clean();
            }
            $realname = trim($info['screen_name']);
            $a = array(
                'uname'       => $uname,
                'mail'        => $uinqid.'@sina.com',
                'passwd'      => substr(md5($uinqid),rand(5, 20),7),
                'realname'    => $realname
            );
            if($uid = K::M('member/account')->create($a)){
                K::M('connect/connect')->update($connect['connect_id'],array('uid'=>$uid), true);
                K::M('member/member')->update($uid, array('realname'=>$info['nickname']), true);
                if($face = file_get_contents($info['avatar_large'])){
                    K::M('member/face')->update_face($uid, '', $face);
                }
                K::M('member/auth')->manager($uid);
                return true;
            }
        }
        return false;
    }
    
}