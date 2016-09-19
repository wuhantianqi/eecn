<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: log.mdl.php 2588 2013-12-27 16:54:19Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Member_Log extends Mdl_Table
{   
  
    protected $_table = 'member_log';
    protected $_pk = 'log_id';
    protected $_cols = 'log_id,uid,from,number,log,admin,clientip,dateline';
    protected $_orderby = array('log_id'=>'DESC');
    
    public function create($data)
    {
        if(!$data = $this->_check_schema($data)){
            return false;
        }
        $data['clientip'] = $data['clientip'] ? $data['clientip'] : __IP;
        $data['dateline'] = $data['dateline'] ? $data['dateline'] : __CFG::TIME;
        return $this->db->insert($this->_table, $data, true);
    }

    public function log($uid, $from='gold', $num=0, $log='')
    {
        $a = array();
        if(!$uid = (int)$uid){
            return false;
        }
        $a = array('uid'=>$uid, 'from'=>$from, 'number'=>$num, 'log'=>$log);
        if(defined('IN_ADMIN')){
            $admin = K::$system->admin->admin;
            $a['admin'] = "{$admin['admin_id']}:{$admin['admin_name']}";
        }
        $a['clientip'] = __IP;
        $a['dateline'] = __CFG::TIME;
        return $this->db->insert($this->_table, $a, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }    
}