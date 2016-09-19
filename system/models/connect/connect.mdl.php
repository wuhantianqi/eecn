<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Connect_Connect extends Mdl_Table
{   
  
    protected $_table = 'connect';
    protected $_pk = 'connect_id';
    protected $_cols = 'connect_id,type,open_id,uid,dateline,create_ip';
    
    protected $_type_cfg = array(1=>'QQ联合登录',2=>'新浪微博登录');
    
    public function get_type_cfg(){
        return $this->_type_cfg;
    }
    
    public function detail_by_openid($type,$open_id){
        
        $filter = array(
            'type'      =>$type,
            'open_id'   => $open_id,
        );
        $where = $this->where($filter);
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE $where";
		if($detail = $this->db->GetRow($sql)){
			$detail = $this->_format_row($detail);
		}
		return $detail;
    }
    
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