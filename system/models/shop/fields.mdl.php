<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: fields.mdl.php 2879 2014-01-08 03:15:45Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Fields extends Mdl_Table
{   
  
    protected $_table = 'shop_fields';
    protected $_pk = 'shop_id';
    protected $_cols = 'shop_id,banner,fox,mail,qq,hours,addr,jiaotong,bulletin,info,psaz,dgxz,skin,seo_title,seo_keywords,seo_description';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($shop_id, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $shop_id)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $shop_id));
    }
}