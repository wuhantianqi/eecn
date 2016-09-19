<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Member_Member extends Mdl_Table 
{    

    protected $_table = 'member';
    protected $_pk = 'uid';
    protected $_cols = 'uid,uname,passwd,from,mail,gender,city_id,group_id,mobile,credits,realname,face,face_80,face_32,Y,M,D,verify,uc_uid,cart,regip,closed,lastlogin,lasactive,dateline';
    protected $_orderby = array('uid'=>'DESC');

    CONST VERIFY_MAIL = 1; //邮箱认证
    CONST VERIFY_MOBILE = 2; //手机认证
    CONST VERIFY_NAME = 4;  //实名认证

    protected $_from_list = array('member'=>'业主','company'=>'公司',  'designer'=>'设计师', 'shop'=>'商家' ,'mechanic'=>'技工', 'gz'=>'工长');

    public function from_list()
    {
        return $this->_from_list;
    }

    public function member($u, $l='uid')
    {
        $l = strtolower($l);
        switch($l){
            case 'uid':
                $field = 'uid'; break;
            case 'name' : case 'uname':
                $field = 'uname'; break;
            case 'mail': case 'email':
                $field = 'mail'; break;
            case 'mobile':
                $field = 'mobile';
            default:
                return false;
        }
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE ".$this->field($field, $u);
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    public function detail($uid, $closed=fasle)
    {
        if(!$uid = (int)$uid){
            return false;
        }
        $where = $closed ? " AND m.closed !=3 " : "";
        $sql = "SELECT f.*,m.* FROM ".$this->table($this->_table)." m LEFT JOIN ".$this->table('member_fields')." f ON m.uid=f.uid WHERE m.uid='$uid' $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    public function max_uid()
    {
        $sql = "SELECT MAX(uid) FROM ".$this->table($this->_table);
        return $this->db->GetOne($sql);
    }

    public function member_by_uc($uid)
    {
        if(!$uid = (int)$uid){
            return false;
        }
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE uc_uid='$uid'";
        return $this->db->GetRow($sql);
    }

    protected function _format_row($row)
    {
        static $gender = array('man'=>'男','woman'=>'女');
        $row['format_regdate'] = date("Y-m-d",$row['regdate']);          
        $row['format_gender'] = $gender[$row['gender']] ? $gender[$row['gender']] : '保密';
        $row['face'] = !empty($row['face']) ? $row['face'] : 'face/face.jpg';
        $row['face_80'] = !empty($row['face_80']) ? $row['face_80'] : 'face/face_80.jpg';
        $row['face_32'] = !empty($row['face_32']) ? $row['face_32'] : 'face/face_32.jpg';
        $row['realname'] = empty($row['realname']) ? $row['uname'] : $row['realname'];
        $row['verify_mail'] = $row['verify'] & self::VERIFY_MAIL ? true :false;
        $row['verify_mobile'] = $row['verify'] & self::VERIFY_MOBILE ? true :false;
        $row['verify_name'] = $row['verify'] & self::VERIFY_NAME ? true :false;
        if($row['verify_name']){
            if($row['from'] == 'member' || $row['from'] == 'designer' ||  $row['from'] == 'mechanic'){
                $row['uname_v'] = $row['uname'].'<i class="rz_v_hy"></i>';
            }else{
                $row['uname_v'] = $row['uname'].'<i class="rz_v_qy"></i>';
            }
        }else{
            $row['uname_v'] = $row['uname'];
        }
        $row['from_title'] = $this->_from_list[$row['from']];
        return $row;
    }

    /**
     * 外部连表查询的时候用
     */
    public function format_row($row)
    {
        return $this->_format_row($row);
    }  

    public function guest()
    {
        static $guest = array('uid'=>0, 'uname'=>'');
        return $this->_format_row($guest);
    }

    public function create($data, $checked=false)
    {
        if(!$checked && !($data = $this->_check($data))){
            return false;
        }
        $data['regip'] = $data['regip'] ? $data['regip'] : __IP;
        $data['dateline'] = $data['dateline'] ? $data['dateline'] :  __CFG::TIME;
        if($uid = $this->db->insert($this->_table, $data, true)){
            $this->db->Execute("INSERT INTO ".$this->table('member_fields')."(uid) VALUES('$uid')");
        }
        return $uid;
    }

    public function update($pk, $data, $checked=false)
    {
        unset($data['uname']);
        if(!$checked && !($data = $this->_check($data,  $pk))){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function delete($val, $force=false)
    {
        $ret = false;
        if(!empty($val)) {
            $this->_checkpk();
            if(is_array($val)){
                $val = implode(',', $val);
            }
            if(!K::M('verify/check')->ids($val)){
                return false;
            }
            $val = explode(',', $val);
            if(!$force){
                $ret = $this->db->update($this->_table, array('closed'=>3), self::field($this->_pk, $val));
            }else{
                $sql = "DELETE FROM ".$this->table($this->_table)." WHERE " . self::field($this->_pk, $val);
                $ret = $this->db->Execute($sql);                
            }
            $this->clear_cache($val);
        }
        return $ret;
    }

    public function regain($val)
    {
        $ret = false;
        if(!empty($val)) {
            if(is_array($val)){
                $val = implode(',', $val);
            }
            if(!K::M('verify/check')->ids($val)){
                return false;
            }
            $val = explode(',', $val);
            $ret = $this->db->update($this->_table, array('closed'=>0), self::field($this->_pk, $val));
            $this->clear_cache($val);
        }
        return $ret;        
    }

    protected function _check($data, $uid=null)
    {
        unset($data['uid'], $data['gold'], $data['regip'], $data['dateline']);
        if($uid = (int)$uid){
            if($member = K::M('member/member')->detail($uid)){
                if(isset($data['uname']) && $data['uname'] == $member['uname']){
                    unset($data['uname']);
                }
                if(isset($data['mail']) && $data['mail'] == $member['mail']){
                    unset($data['mail']);
                }
            }
        }
        if(empty($uid) && isset($data['from'])){
            if(!$this->_from_list[$data['from']]){
                $data['from'] = 'member';
            }
        }
        if(empty($uid) || isset($data['uname'])){
            if(!$uname = K::M('member/account')->check_uname($data['uname'])){
                return false;
            }
        }
        if(empty($uid) || isset($data['mail'])){
            if(!$uname = K::M('member/account')->check_mail($data['mail'])){
                return false;
            }
        }
        if(empty($uid) || isset($data['passwd'])){
            if(!preg_match('/[0-9a-f]{32}/i', $data['passwd'])){
                if(!$passwd = K::M('member/account')->check_passwd($data['passwd'])){
                    return false;
                }
                $data['passwd'] = md5($passwd);
            }
        }        
        if($data['mobile']){
            if(!K::M('verify/check')->mobile($data['mobile'])){
                $this->err->add('很抱歉，手机格式不正确!', 454);
                return false;
            }
        }
        if(isset($data['uc_uid'])){
            $data['uc_uid'] = (int)$data['uc_uid'];
        }
        if(isset($data['gender'])){
            $data['gender'] = strtolower($data['gender']) == 'man' ? 'man' : 'woman';
        }
        if(isset($data['verify_mail']) || isset($data['verify_mobile']) || isset($data['verify_name'])){
            $verify = 0;
            if($uid && empty($member)){
                if($member = K::M('member/member')->detail($uid)){
                    $verify = $member['verify'];
                }
            }
            if(isset($data['verify_mail'])){
                $verify = $data['verify_mail'] ? ($verify | self::VERIFY_MAIL) : (($verify | self::VERIFY_MAIL) ^ self::VERIFY_MAIL);
            }
            if(isset($data['verify_mobile'])){
                $verify = $data['verify_mobile'] ? ($verify | self::VERIFY_MOBILE) : (($verify | self::VERIFY_MOBILE) ^ self::VERIFY_MOBILE);
            }
            if(isset($data['verify_name'])){
                $verify = $data['verify_name'] ? ($verify | self::VERIFY_NAME) : (($verify | self::VERIFY_NAME) ^ self::VERIFY_NAME);
            }
            $data['verify'] = $verify;
        }
        return parent::_check($data);
    }        
    
}