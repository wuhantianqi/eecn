<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Mdl_Member_Access extends Mdl_Table {

    protected $_table = 'member_access';
    protected $_pk = 'access_id';
    protected $_cols = 'access_id,myid,uid,dateline';

    public function create($data, $checked = false)
    {
        if (!$checked && !$data = $this->_check_schema($data)) {
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked = false)
    {
        $this->_checkpk();
        if (!$checked && !$data = $this->_check_schema($data, $pk)) {
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }
    
    public function check_by_myid($myid,$uid){
        $myid = (int)$myid;
        $uid  = (int)$uid;
        return $this->count(" myid='{$myid}' and uid='{$uid}' ");
    }
}