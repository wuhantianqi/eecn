<?php


class Mdl_Home_Attr extends Mdl_Table
{   
  
    protected $_table = 'home_attr';
    protected $_pk = 'home_id,attr_id,attr_value_id';
    protected $_cols = 'home_id,attr_id,attr_value_id';
    
    
    public function update($home_id, $data, $checked=false)
    {
        if(!$home_id = intval($home_id)){
            return false;
        }
        $sql = array();
        foreach((array)$data as $k=>$v){
            foreach((array)$v as $kk=>$vv){
                if(is_numeric($kk) && is_numeric($vv)){
                    $sql[] = "('{$home_id}', '{$k}', '{$vv}')";
                }
            }
        }
        $this->db->Execute("DELETE FROM ".$this->table($this->_table)." WHERE home_id='$home_id'");
        if($sql){
            $sql = "INSERT INTO ".$this->table($this->_table)." VALUES".implode(',', $sql);
            $this->db->Execute($sql);
        }
    }

    public function attrs_by_home($home_id)
    {
     
        if(!$home_id = intval($home_id)){
            return false;
        }
        $attrs = array();
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE home_id='$home_id'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $attrs[$row['attr_value_id']] = $row;
            }
        }
        return $attrs;
    } 
    
    public function attrs_ids_by_home($home_id)
    {
     
        if(!$home_id = intval($home_id)){
            return false;
        }
        $attrs = array();
        $sql = "SELECT attr_value_id FROM ".$this->table($this->_table)." WHERE home_id='$home_id'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $attrs[] = $row['attr_value_id'];
            }
        }
        return $attrs;
    }
    
}