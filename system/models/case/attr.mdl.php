<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: attr.mdl.php 2216 2013-12-16 06:39:13Z $
 */

class Mdl_Case_Attr extends Mdl_Table
{   
  
    protected $_table = 'case_attr';
    protected $_pk = 'case_id,attr_id,attr_value_id';
    protected $_cols = 'case_id,attr_id,attr_value_id';
   
    public function update($case_id, $data, $checked=false)
    {
        if(!$checked && !$case_id = intval($case_id)){
            return false;
        }
        $sql = array();
        foreach((array)$data as $k=>$v){
            if(is_numeric($k)){
                foreach((array)$v as $kk=>$vv){
                    if(is_numeric($vv)){
                        $sql[] = "('{$case_id}', '{$k}', '{$vv}')";
                    }
                }
            }
        }
        //print_r($sql);
        $this->db->Execute("DELETE FROM ".$this->table($this->_table)." WHERE case_id='$case_id'");
        if($sql){
            $sql = "INSERT INTO ".$this->table($this->_table)." VALUES".implode(',', $sql);
            $this->db->Execute($sql);
        }
    }

    public function attrs_by_case($case_id)
    {
        if(!$case_id = intval($case_id)){
            return false;
        }
        $attrs = array();
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE case_id='$case_id'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $attrs[$row['attr_value_id']] = $row;
            }
        }
        return $attrs;
    } 
}