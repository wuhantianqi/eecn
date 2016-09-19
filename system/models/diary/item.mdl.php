<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Diary_Item extends Mdl_Table
{   
  
    protected $_table = 'diary_item';
    protected $_pk = 'item_id';
    protected $_cols = 'item_id,diary_id,status,content,clientip,dateline';

    
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

	function item_count($val)
	{
		$count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT diary_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('diary_id', $val)." GROUP BY diary_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['diary_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('diary/diary')->update($k, array('content_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
	}
	public function diary_status($diary_id)
    {
        $status = K::M('home/site')->get_status();
        $status_list = array();
        foreach($status as $k=>$v){
            $status_list[$k] = array('title'=>$v);
        }
        if($diary_id = (int)$diary_id){
            if($items = $this->items(array('diary_id'=>$diary_id), null, 1, 50)){
                foreach($items as $k=>$v){
                    if($status_list[$v['status']]){
                        $status_list[$v['status']]['has_diary'] = true;
                    }
                }
            }
        }
        return $status_list;
    }

    protected function _format_row($row)
    {
        static $status_list = null;
        if($status_list === null){
            $status_list = K::M('home/site')->get_status();
        }
        $row['status_title'] = $status_list[$row['status']];
        return $row;        
    }
}