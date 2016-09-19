<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: yuyue.mdl.php 5695 2014-06-27 04:38:29Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_Yuyue extends Mdl_Table
{   
  
    protected $_table = 'company_yuyue';
    protected $_pk = 'yuyue_id';
    protected $_cols = 'yuyue_id,uid,city_id,company_id,mobile,contact,content,status,remark,dateline,clientip';
    protected $_orderby = array('yuyue_id'=>'DESC');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        $data['dateline'] = $data['dateline'] ? $data['dateline'] : __CFG::TIME;
        $data['clientip'] = $data['clientip'] ? $data['clientip'] : __IP;
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

    protected function _format_row($row)
    {
		if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = K::M('misc/data')->yuyue($row['status']);
        return $row;
    } 

	public function yuyue_count($val)
    {
        $count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT company_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('company_id', $val)." GROUP BY company_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['company_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('company/company')->update($k, array('yuyue_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }
}