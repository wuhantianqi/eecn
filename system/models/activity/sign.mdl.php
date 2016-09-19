<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Activity_Sign extends Mdl_Table
{   
  
    protected $_table = 'activity_sign';
    protected $_pk = 'sign_id';
    protected $_cols = 'sign_id,activity_id,city_id,uid,contact,mobile,mail,qq,address,num,note,status,remark,clientip,dateline';
    protected $_orderby = array('sign_id'=>'DESC');

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($sign_id = $this->db->insert($this->_table, $data, true)){
            if($data['activity_id']){
                K::M('activity/activity')->update_count($data['activity_id'], 'sign_num', 1);
            }
        }
        return $sign_id;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function items_by_activity($activity_id, $p=1, $l=50, &$count=0)
    {
        if(!$activity_id = (int)$activity_id){
            return false;
        }
        return $this->items(array('activity_id'=>$activity_id), null, $p, $l, $count);
    }
    
    protected function _format_row($row)
    {
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = K::M('misc/data')->yuyue($row['status']);
        return $row;        
    }

	function sign_count($val)
	{
	   
		$count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT activity_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('activity_id', $val)." GROUP BY activity_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['activity_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('activity/activity')->update($k, array('sign_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
	}

    protected function _check($data, $sign_id=null)
    {
        if(isset($data['contact'])){
            $data['contact'] = K::M('content/html')->text($data['contact']);
        }
        return parent::_check($data, $sign_id);
    }

}