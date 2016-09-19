<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Zxb_Hetong extends Mdl_Table
{   
  
    protected $_table = 'zxb_hetong';
    protected $_pk = 'hetong_id';
    protected $_cols = 'hetong_id,zxb_id,uid,company_id,total_price,hetong,yezhu,yezhu_phone,yezhu_bank,yezhu_status,yezhu_time,company,company_phone,company_bank,company_status,company_time,status,dateline';

    
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