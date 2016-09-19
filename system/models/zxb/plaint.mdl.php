<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Zxb_Plaint extends Mdl_Table
{   
  
    protected $_table = 'zxb_plaint';
    protected $_pk = 'plaint_id';
    protected $_cols = 'plaint_id,zxb_id,uid,company_id,type,yezhu_photo,yezhu_content,yezhu_time,company_photo,company_content,company_time,content,status,time,clientip,dateline';

    
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
}