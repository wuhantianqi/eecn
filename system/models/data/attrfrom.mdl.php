<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: attrfrom.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Data_Attrfrom extends Mdl_Table
{   
  
    protected $_table = 'data_attr_from';
    protected $_pk = 'from_id';
    protected $_cols = 'from_id,from,title';
    protected $_pre_cache_key = 'data-attr-from';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($from_id = $this->db->insert($this->_table, $data, true)){
            $this->flush();
        }
        return $from_id;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        if($rs = $this->db->update($this->_table, $data, $this->field($this->_pk, $pk))){
            $this->flush();
        }
        return $rs;
    }

    public function from_by_key($key)
    {
        if($from_list = $this->fetch_all()){
            foreach($from_list as $from){
                if($from['from'] == $key){
                    return $from;
                }
            }
        }
        return false;
    }

    public function from($from_id)
    {
        if($from_id = (int)$from_id){
            if($from_list = $this->fetch_all()){
                if($from = $from_list[$from_id]){
                    return $from;
                }
            }
        }
        return false;        
    }

    public function options()
    {
        $options = array();
        if($from_list = K::M('data/attrfrom')->fetch_all()){
            foreach($from_list as $from){
                $options[$from['from_id']] = $from['title'];
            }
        }
        return $options;
    }
}