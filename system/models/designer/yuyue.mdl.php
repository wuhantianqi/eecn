<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: yuyue.mdl.php 5695 2014-06-27 04:38:29Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Designer_Yuyue extends Mdl_Table
{   
  
    protected $_table = 'designer_yuyue';
    protected $_pk = 'yuyue_id';
    protected $_cols = 'yuyue_id,city_id,uid,designer_id,company_id,mobile,contact,content,status,remark,dateline,create_ip';

    protected $_orderby = array('yuyue_id'=>'DESC');
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

    public function yuyue_count($val)
    {
        $count = 0;
        $sql = "SELECT designer_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('designer_id', $val)." GROUP BY designer_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                K::M('designer/designer')->update($row['designer_id'], array('yuyue_num'=>$row['c']), true);
                $count ++;
            }
        }
        return $count;
    }    

    protected function _format_row($row)
    {
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }
        $row['status_title'] = K::M('misc/data')->yuyue($row['status']);
        return $row;        
    }     
}