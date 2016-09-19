<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cate.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Activity_Cate extends Mdl_Table
{   
  
    protected $_table = 'activity_cate';
    protected $_pk = 'cat_id';
    protected $_cols = 'cat_id,title,seo_title,seo_keywords,seo_description,orderby';
    protected $_pre_cache_key = 'activity_cate';
    
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