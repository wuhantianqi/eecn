<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: detail.mdl.php 5531 2014-06-19 10:26:25Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Diary_Detail extends Mdl_Table
{   
  
    protected $_table = 'diary_detail';
    protected $_pk = 'detail_id';
    protected $_cols = 'detail_id,diary_id,status,contents,create_ip,dateline';
    protected $_orderby = array('diary_id'=>'DESC', 'status'=>'ASC');
    
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

    protected function _format_row($row)
    {
        static $status_list = null;
        if($status_list === null){
            $status_list = K::M('home/site')->get_status();
        }
        $row['status_title'] = $status_list[$row['status']];
        return $row;        
    }
}