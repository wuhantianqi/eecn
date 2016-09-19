<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Log extends Mdl_Table
{   
  
    protected $_table = 'weixin_log';
    protected $_pk = 'log_id';
    protected $_cols = 'log_id,weixin,data,post,dateline';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function log($wx_sid, $data, $post='')
    {
        $log = array('weixin'=>$wx_sid);
        $log['data'] = is_array($data) ? serialize($data) : $data;
        $log['post'] = is_array($post) ? serialize($post) : $post;
        $log['dateline'] = __TIME;
        return $this->create($log);
    }
}