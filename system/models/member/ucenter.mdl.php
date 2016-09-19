<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: ucenter.mdl.php 5969 2014-07-30 13:04:57Z youyi $
 */

class Mdl_Member_Ucenter extends Model
{   

    public function __construct(&$system)
    {
        parent::__construct($system);
        $system->config->ucenter();
        if(!defined('UC_API')){
            K::M('system/logs')->error('Ucenter已经开启，但未能正确配置');
        }
        Import::L('uc_client/client.php');
    }

    public function member($uname, $isuid=false)
    {
        if($data = uc_get_user(addslashes($this->iconv($uname)), (bool)$isuid)){
            list($uid, $uname, $mail) = $data;
            return array('uid'=>$uid, 'uname'=>$this->iconv($uname, true), 'mail'=>$mail);
        }
        return false;
    }
    
    /**
     * $l {1:uid,2:email,3:uname}
     */
    public function login($uname, $passwd, $l=3)
    {
        list($m['uid'], $m['uname'], $m['passwd'], $m['mail']) = uc_user_login($this->iconv($uname), $passwd, (int)$l);
        $m['uname'] = $this->iconv($m['uname'], true);
        return $m;
        if($m['uid'] > 0) {
            $m['uname'] = $uname;
            return $m;
        }else if($m['uid'] == -1) {
            $this->err->add('用户不存在,或者被删除', 421);
        }else if($m['uid'] == -2) {
            $this->err->add('登录不正确', 422);
        }else{
            $this->err->add('未定义操作', 423);
        }
        return false;
    }

    public function synlogin($uid)
    {
        return uc_user_synlogin((int)$uid);
    }

    public function synlogout($uid)
    {
        return uc_user_synlogout();
    }

    public function create($uname, $passwd, $mail)
    {
        $uid = (int)uc_user_register($this->iconv($uname), $passwd, $mail);
        if($uid > 0){
            return $uid;
        }else if($uid == -1) {
            $this->err->add('用户名不合法', 431);
        } elseif($uid == -2) {
            $this->err->add('包含要允许注册的词语', 431);
        } elseif($uid == -3) {
            $this->err->add('用户名已经存在', 431);
        } elseif($uid == -4) {
            $this->err->add('Email 格式有误', 431);
        } elseif($uid == -5) {
            $this->err->add('不允许注册', 431);
        } elseif($uid == -6) {
            $this->err->add('该 Email 已经被注册', 431);
        } else {
            $this->err->add('未定义操作', 431);
        }
        return $uid;
    }

    public function delete($uname)
    {
        return uc_user_delete($this->iconv($uname));
    }

    /**
     * 本接口函数用于更新用户资料。更新资料需验证用户的原密码是否正确，除非指定 ignoreoldpw 为 1。
     * 如果只修改 Email 不修改密码，可让 newpw 为空；
     * 同理如果只修改密码不修改 Email，可让 email 为空。
     */
    public function update($uname, $pwd, $newpwd='', $mail='', $ignoreoldpw=0)
    {
        $ret = uc_user_edit($this->iconv($uname), $pwd, $newpwd, $mail, $ignoreoldpw);
        if($ret == 1){
            return true;
        }else if($ret == -1) {
            $this->err->add('旧密码不正确', 281);
        }else if($ret == -4) {
            $this->err->add('Email 格式有误', 282);
        }else if($ret == -5) {
            $this->err->add('Email 不允许注册', 283);
        }else if($ret == -6) {
            $this->err->add('该 Email 已经被注册', 284);
        }
        return false;
    }

    public function check_mail($mail)
    {
        $ret = uc_user_checkemail($mail);
        if($ret > 0) {
            return $mail;
        }else if($ret == -4) {
            $this->err->add('Email 格式有误', 282);
        }else if($ret == -5) {
            $this->err->add('Email 不允许注册', 283);
        }else if($ret == -6) {
            $this->err->add('该 Email 已经被注册', 284);
        }else{
            $this->err->add('服务器内部错误，稍后重试', 285);
        }
        return false;   
    }

    public function check_uname($uname)
    {
        $ret = uc_user_checkname($this->iconv($uname));
        if($ret > 0) {
            return $uname;
        }else if($ret == -1) {
            $this->err->add('用户名不合法', 281);
        } else if($ret == -2) {
            $this->err->add('包含要不允许注册的词语', 282);
        }else if($ret == -3) {
            $this->err->add('用户名已经存在', 283);
        }
        return false;   
    }

    public function mager_member($uname, $newuname, $uid, $passwd, $mail)
    {
        $uid = uc_user_merge($this->iconv($uname), $newuname, $uid, $passwd, $mail);
        if($uid > 0) {
            return $uid;
        } elseif($uid == -1) {
            $this->err->add('用户名不合法', 281);
        } elseif($uid == -2) {
            $this->err->add('包含不允许注册的词语', 281);
        } elseif($uid == -3) {
            $this->err->add('用户名已经存在', 281);
        }
    }

    public function merge_remove($uname)
    {
        return uc_user_merge_remove($this->iconv($uname));
    }

    public function iconv($str, $uc=false)
    {
        
        if(strtoupper(UC_CHARSET) != 'UTF-8'){
            if($uc){
                return iconv('GBK', 'UTF-8//TRANSLIT', $str);
            }else{
                return iconv('UTF-8', 'GBK//TRANSLIT', $str);
            }
        }
        return $str;
    }
}