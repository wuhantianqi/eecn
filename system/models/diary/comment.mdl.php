<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.mdl.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Diary_Comment extends Mdl_Table
{   
  
    protected $_table = 'diary_comment';
    protected $_pk = 'comment_id';
    protected $_cols = 'comment_id,diary_id,city_id,uid,content,clientip,dateline,audit';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

	protected function _format_row($row)
    {
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = $this->_status[$row['status']];
        return $row;
    }

	function comment_count($val)
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
                K::M('diary/diary')->update($k, array('comments'=>$v), true);
                $count ++;
            }            
        }
        return $count;
	}

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

}