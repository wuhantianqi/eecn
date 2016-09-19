<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: weixin.mdl.php 5531 2014-06-19 10:26:25Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Member_Weixin extends Mdl_Table
{   
    
    protected $_table = 'member_weixin';
    protected $_pk = 'uid';
    protected $_cols = 'uid,openid,info,status,dateline';

    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        $data['dateline'] = $data['dateline'] ? $data['dateline'] : __TIME;
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($uid, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check($data,  $uid)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $uid));
    }

    public function detail_by_openid($openid)
    {
        if($row = $this->db->GetRow("SELECT w.*,m.* FROM ".$this->table($this->_table)." w LEFT JOIN ".$this->table('member')." m ON m.uid=w.uid WHERE w.openid='$openid'")){
            return $this->_format_row($row);
        }
        return false;
    }

    public function create_account($info)
    {
        $uinqid = 'wx'.rand(10000000,99999999);
        if(!$uname = K::M('member/account')->check_uname($info['nickname'])){
            if(!$uname = K::M('member/account')->check_uname('wx'.$info['nickname'])){
                $uname = $uinqid;
            }
            $this->err->clean();
        }
        $a = array(
            'uname'       => $uname,
            'mail'        => $uinqid.'@qq.com',
            'passwd'      => substr(md5($uinqid),rand(5, 20),7)
        ); 
        if($uid = K::M('member/account')->create($a)){
            $this->create(array('uid'=>$uid, 'openid'=>$info['openid'], 'status'=>1), true);
            K::M('member/member')->update($uid, array('realname'=>$info['nickname']), true);
            if($face = file_get_contents($info['headimgurl'])){
                K::M('member/face')->update_face($uid, '', $face);
            }
            $a['uid'] = $uid;
            return $a;
        }
        return false;
    }
}