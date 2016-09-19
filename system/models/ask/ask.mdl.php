<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: ask.mdl.php 5649 2014-06-25 11:13:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Ask_Ask extends Mdl_Table
{   
  
    protected $_table = 'ask';
    protected $_pk = 'ask_id';
    protected $_cols = 'ask_id,title,seo_title,seo_keyword,thumb,seo_description,cat_id,uid,intro,dateline,clientip,views,answer_num,audit,answer_id,orderby';
    protected $_orderby = array('ask_id'=>'desc');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        $data['orderby'] = isset($data['orderby']) ? $data['orderby'] : 50;
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
   
    public function items_count_by_ids()
    {
        $sql = "SELECT count(1) as num,cat_id FROM ".$this->table($this->_table)." group by cat_id";
        $items = array();   
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                if($row['cat_id']){
                    $items[$row['cat_id']] = $row['num'];
                }
            }
        }
        return $items;
    }
    
}