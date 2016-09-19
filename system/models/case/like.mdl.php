<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: like.mdl.php 2335 2013-12-18 17:15:56Z youyi $
 */


class Mdl_Case_Like extends Mdl_Table
{   
    
    protected $_table = 'case_like';
    protected $_pk = 'like_id';
    protected $_cols = 'like_id,case_id,uid,create_ip,dateline';
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }
    
    public function is_like($case_id,$create_ip){
        
        $case_id = (int) $case_id;
        $create_ip = $this->_quote($create_ip);
        
        return $this->count(" case_id={$case_id} AND create_ip={$create_ip} ");
    }
    
}