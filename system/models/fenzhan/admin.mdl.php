<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: base.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Fenzhan_Admin extends Mdl_Table
{
    protected $_table = 'fz_admin';
    protected $_pk = 'fz_uid';
    protected $_cls = 'fz_uid,fz_name,fz_passwd,city_id,role,priv,contact,mail,phone,role_id,lastlogin,lastip,closed,dateline';
	protected $_orderby = array('fz_uid'=>'ASC');
	protected $_pre_cache_key = 'fenzhan-fenzhan-list';

    public function create($data)
    {
        if(!$data = $this->_check($data)){
            return false;
        }
        $data['dateline'] = __CFG::TIME;
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($ID, $data, $checked=false)
    {	
        if(!$checked && !($data = $this->_check($data, $ID))){
            return false;
        }
        $ID = intval($ID);
        return $this->db->update($this->_table, $data, "fz_uid='$ID'");
    }

    public function update_passwd($admin_id, $passwd)
    {
        if(!$admin_id = (int)$admin_id){
            return false;
        }else if(!preg_match('/^[0-9a-z]{32}$/i', $passwd)){
            $this->err->add('密码格式不正确', 451);
            return false;
        }
        return $this->db->update($this->_table, array('fz_passwd'=>$passwd), "fz_uid='$admin_id'");
    }

    public function update_login($uid)
    {
        $uid = intval($uid);
        $a = array('lastip'=>__IP, 'lastlogin'=>__CFG::TIME);
        return $this->db->update($this->_table, $a, "fz_uid='$uid'");
    }

    public function remove($IDS)
    {
        if(!K::M('verify/check')->ids($IDS)){
            return false;
        }
        $sql = "UPDATE ".$this->table($this->_table)." SET closed=3 WHERE role_id IN($IDS)";
        if($this->db->Execute($sql)){
            if($this->db->affected_rows){
                $this->flush();
            }
            return true;
        }
        return false;       
    }

    protected function _check($data, $ID=null)
    {
        if($ID = intval($ID)){
            if(!$admin = K::M('fenzhan/admin')->admin($ID)){
                $this->err->add('您要修改的管理员不存在',401);
            }
        }
        if(isset($data['fz_name']) || !$ID){
            if(!$data['fz_name']){
                $this->err->add('管理员名称不能为空',401);
                return false;
            }else if($admin = K::M('fenzhan/admin')->admin(0, $data['fz_name'])){
                if(!$ID || $ID!=$admin['fz_uid']){
                    $this->err->add('管理员名称已经存在',402);
                    return false;       
                }
            }
            $data['fz_name'] = K::M('content/html')->text($data['fz_name']);
        }
		if($_POST['priv']){
			$data['priv'] = implode(',',$_POST['priv']);
		}
		if(K::$system->admin->admin['role'] != 'system'){
			if(!$ID){
				if(K::$system->fenzhan->admin['role'] != 'admin' || $data['role'] != 'editor'){
					$this->err->add('你没有权限设置系统管理员',405);
					return false;       
				}else{
					$data['city_id'] = K::$system->fenzhan->admin['city_id'];
				}
			}else{
				unset($data['city_id']);
			}
		}
        if(isset($data['fz_passwd']) || !$ID){
            if(strlen($data['fz_passwd'])<6 || strlen($data['fz_passwd'])>20){
                $this->err->add('管理员登录密码长度为6~20个字符',406);
                return false;
            }
            $data['fz_passwd'] = md5($data['fz_passwd']);
        }
        if(isset($data['closed'])){
            $data['closed'] = intval($data['closed']);
        }
        unset($data['fz_uid'],$data['last_login'],$data['dateline']);
        return parent::_check($data);
    }

    public function admin($id=0, $name='')
    {
        if($id = intval($id)){
            $where = "fz_uid='$id'";
        }else if($name = trim($name)){
            $where = "fz_name='$name'";
        }else{
            return false;
        }
        if($row = $this->db->GetRow("SELECT * FROM ".$this->table($this->_table)." WHERE $where")){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    protected function _format_row($row)
    {
        if($row['role'] != 'admin'){
            $row['priv'] = explode(',', $row['priv']);
        }
        return $row;       
    }

}