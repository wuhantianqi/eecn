<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Authocde extends Mdl_Table
{   
  
    protected $_table = 'weixin_authcode';
    protected $_pk = 'SSID';
    protected $_cols = 'SSID,code,uid,type,dateline';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function detail_by_code($code)
    {
        if(!$code = (int)$code){
            return false;
        }
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE code='$code'";
        return $this->db->GetRow($sql);
    }

    public function create_code()
    {
        $code = 0;
        $i = 0;
        do {
            $code = rand(1000000, 9999999);
            $codeexists = $this->detail_by_code($code);
            $i++;
        } while($codeexists && $i < 10);
        return $code;       
    }
}