<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: sign.mdl.php 5695 2014-06-27 04:38:29Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_Sign extends Mdl_Table
{   
  
    protected $_table = 'company_youhui_sign';
    protected $_pk = 'sign_id';
    protected $_cols = 'sign_id,city_id,company_id,youhui_id,uid,mobile,contact,status,remark,dateline,clientip';

    
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

	public function youhui_count($val)
    {
       $count = 0;
       $sql = "SELECT youhui_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('youhui_id', $val)." GROUP BY youhui_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                K::M('company/youhui')->update($row['youhui_id'], array('sign_num'=>$row['c']), true);
                $count ++;
            }
        }
        return $count;
    }

    protected function _format_row($row)
    {
		if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = K::M('misc/data')->yuyue($row['status']);
        return $row;
    }
}