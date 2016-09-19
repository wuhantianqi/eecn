<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Activity_Lanmu extends Mdl_Table
{   
  
    protected $_table = 'activity_lanmu';
    protected $_pk = 'lanmu_id';
    protected $_cols = 'lanmu_id,activity_id,title,content,orderby,dateline';
    protected $_orderby = array('orderby'=>'DESC', 'lanmu_id'=>'ASC');
    
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

    public function items_by_activity($activity_id)
    {
        if(!$activity_id = (int)$activity_id){
            return false;
        }
        return $this->items(array('activity_id'=>$activity_id), null, 1, 50);
    }
}