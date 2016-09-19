<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */
class Mdl_Member_Qqlogin extends Model
{
    
    protected $_type_id = 1;//QQ互联是1
    
    public function qqloign_url()
    {
        $cfg = K::$system->config->get('connect');
        if(defined('IN_MOBILE')){
            $site = K::$system->config->get('site');
            $callback = $site['siteurl'] . '/' . K::M('helper/link')->mklink('mobile/passport:qqcallback');
        }else{
            $site = K::$system->config->get('site');
            $callback = $site['siteurl'] . '/' . K::M('helper/link')->mklink('passport:qqcallback');
        }
        if (empty($cfg['qq_is_open'])) {
            $this->err->add('很抱歉网站管理员还未开启QQ登录功能', 201);
        }
        else {
            $id = md5(uniqid(rand(), TRUE));
            K::$system->session->start();
            K::$system->session->set('QQ_LOGIN', $id, null);
            $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
                    . trim($cfg['qq_app_id']) . "&redirect_uri=" . urlencode($callback)
                    . "&state=" . $id
                    . "&scope=get_user_info";
            return $login_url;
        }
        return false;
    }

    public function qqcallback($code, $state)
    {
        $cfg = K::$system->config->get('connect');
        if(defined('IN_MOBILE')){
            $site = K::$system->config->get('site');
            $callback = K::M('helper/link')->mklink('mobile/passport:qqcallback', array(), array(), 'www');
        }else{
            $site = K::$system->config->get('site');
            $callback = K::M('helper/link')->mklink('passport:qqcallback', array(), array(), 'www');
        }
        if (empty($cfg['qq_is_open'])) {
            $this->err->add('很抱歉网站管理员还未开启QQ登录功能', 201);
            return false;
        }
        else {
            K::$system->session->start();
            $id = K::$system->session->get('QQ_LOGIN');
            if ($id != $state) {
                $this->err->add('很抱歉请重新登录', 201);
                return false;
            }
            $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
                    . "client_id=" . trim($cfg['qq_app_id']) . "&redirect_uri=" . urlencode($callback)
                    . "&client_secret=" . trim($cfg['qq_app_key']) . "&code=" . $code;

            $response = K::M('net/http')->http($token_url, array(), 'GET');
            if (strpos($response, "callback") !== false) {
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
                $params = json_decode($response, true);
            }
            else {
                $local = explode('&', $response);
                foreach ($local as $val) {
                    if (strpos($val, "access_token") !== false) {
                        $local2 = explode('=', $val);
                        $params['access_token'] = trim($local2[1]);
                        break;
                    }
                }
            }
            if (empty($params['access_token'])) {
                 $this->err->add('授权过期请重试', 201);
                 return false;
            }            
            $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=". $params["access_token"];
            $str = K::M('net/http')->http($graph_url, array(), 'GET');
            if (strpos($str, "callback") !== false) {
                $lpos = strpos($str, "(");
                $rpos = strrpos($str, ")");
                $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
            }
            $user = json_decode($str,true);
            if(empty($user)){
                 $this->err->add('授权过期请重试', 201);
                 return false;
            }
            $user['qq_app_id'] = $cfg['qq_app_id'];
            $user['access_token'] = $params['access_token'];
            return $this->login($user);
        }
    }

    public function login($user)
    {
        if(!$connect = K::M('connect/connect')->detail_by_openid($this->_type_id,$user['openid'])){
            $connect['connect_id'] = K::M('connect/connect')->create(array('type'=> $this->_type_id,'open_id'=> $user['openid']));
        }
        if($connect['uid'] && $m = K::M('member/member')->member($connect['uid'])){
            K::M('member/auth')->manager($m['uid']);
            return true;
        }else if(K::$system->uid){
            if($connect['connect_id']){
                K::M('connect/connect')->update($connect['connect_id'], array('uid'=>K::$system->uid, 'dateline'=>__TIME), true);
            }else{
                K::M('connect/connect')->create(array('uid'=>K::$system->uid, 'type'=> $this->_type_id,'open_id'=> $user['openid'], 'dateline'=>__TIME), true); 
            }
            return true; 
        }else{
            $graph_url = 'https://graph.qq.com/user/get_user_info?access_token='.$user["access_token"].'&oauth_consumer_key='.$user['qq_app_id'].'&openid='.$user['openid'];
            $str = K::M('net/http')->http($graph_url, array(), 'GET');
            $info = json_decode($str,true);
            if((int)$info['ret'] !== 0){
                $this->err->add($info['msg'], 501);
                return false;
            }
            $uinqid = 'QQ'.rand(10000000,99999999);
            if(!$uname = K::M('member/account')->check_uname($info['nickname'])){
                if(!$uname = K::M('member/account')->check_uname('QQ'.$info['nickname'])){
                    $uname = $uinqid;
                }
                $this->err->clean();
            }
            $realname = trim($info['nickname']);
            $a = array(
                'uname'       => $uname,
                'mail'        => $uinqid.'@qq.com',
                'passwd'      => substr(md5($uinqid),rand(5, 20),7),
                'realname'    => $realname
            );
            if($uid = K::M('member/account')->create($a)){
                K::M('connect/connect')->update($connect['connect_id'],array('uid'=>$uid), true);
                K::M('member/member')->update($uid, array('realname'=>$info['nickname']), true);
                if($face = file_get_contents($info['figureurl_qq_2'])){
                    K::M('member/face')->update_face($uid, '', $face);
                }
                K::M('member/auth')->manager($uid);
                return true;
            }
        }
        return false;
    }
}