<?php


class Mdl_Gz_Attr extends Mdl_Table
{   
  
    protected $_table = 'gz_attr';
    protected $_pk = 'uid,attr_id,attr_value_id';
    protected $_cols = 'uid,attr_id,attr_value_id';
    
    
    public function update($uid, $data, $checked=false)
    {
        if(!$uid = intval($uid)){
            return false;
        }
        $sql = array();
        foreach((array)$data as $k=>$v){
            foreach((array)$v as $kk=>$vv){
                if(is_numeric($kk) && is_numeric($vv)){
                    $sql[] = "('{$uid}', '{$k}', '{$vv}')";
                }
            }
        }
        $this->db->Execute("DELETE FROM ".$this->table($this->_table)." WHERE uid='$uid'");
        if($sql){
            $sql = "INSERT INTO ".$this->table($this->_table)." VALUES".implode(',', $sql);
            $this->db->Execute($sql);
        }
    }

    public function attrs_by_designer($uid)
    {
     
        if(!$uid = intval($uid)){
            return false;
        }
        $attrs = array();
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE uid='$uid'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $attrs[$row['attr_value_id']] = $row;
            }
        }
        return $attrs;
    } 
    
    public function attrs_ids_by_gz($uid)
    {
     
        if(!$uid = intval($uid)){
            return false;
        }
        $attrs = array();
        $sql = "SELECT attr_value_id FROM ".$this->table($this->_table)." WHERE uid='$uid'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $attrs[] = $row['attr_value_id'];
            }
        }
        return $attrs;
    }
    
}