<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: fields.mdl.php 2466 2013-12-23 11:13:30Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Product_Fields extends Mdl_Table
{   
  
    protected $_table = 'product_fields';
    protected $_pk = 'product_id';
    protected $_cols = 'product_id,seo_title,seo_keywords,seo_description,info,clientip';

    
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

    public function check_fields($data, $product_id=null)
    {
        return $this->_check_schema($data, $product_id);
    }
}