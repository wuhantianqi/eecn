<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: attr.mdl.php 3090 2014-01-16 16:05:40Z youyi $
 */

class Mdl_Product_Attr extends Mdl_Table
{   
  
    protected $_table = 'product_attr';
    protected $_pk = 'product_id,attr_value_id';
    protected $_cols = 'product_id,attr_id,attr_value_id';

    
    public function update($product_id, $data, $checked=false)
    {
        if(!$checked && !$product_id = intval($product_id)){
            return false;
        }
        $sql = array();
        foreach((array)$data as $k=>$v){
            if(is_numeric($k)){
                foreach((array)$v as $kk=>$vv){
                    if(is_numeric($vv)){
                        $sql[] = "('{$product_id}', '{$k}', '{$vv}')";
                    }
                }
            }
        }
        $this->db->Execute("DELETE FROM ".$this->table($this->_table)." WHERE product_id='$product_id'");
        if($sql){
            $sql = "INSERT INTO ".$this->table($this->_table)." VALUES".implode(',', $sql);
            $this->db->Execute($sql);
        }
    }

    public function attrs_by_product($product_id)
    {
        if(!$product_id = intval($product_id)){
            return false;
        }
        $attrs = array();
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE product_id='$product_id'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $attrs[$row['attr_value_id']] = $row;
            }
        }
        return $attrs;
    } 
}