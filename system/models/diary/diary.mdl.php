<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: diary.mdl.php 5531 2014-06-19 10:26:25Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Diary_Diary extends Mdl_Table
{   
  
    protected $_table = 'diary';
    protected $_pk = 'diary_id';
    protected $_cols = 'diary_id,uid,title,home_id,home_name,city_id,thumb,company_id,type_id,way_id,total_price,start_date,end_date,content_num,views,comments,status,clientip,dateline,audit,closed';
    
    protected $_orderby = array('diary_id'=>'DESC');
    
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

    protected function _format_row($row)
    {
        static $status_list = null;
        static $setting = null;
        static $type_list = null;
        if($status_list === null){
            $status_list = K::M('home/site')->get_status();
            $setting = K::M('tenders/setting')->fetch_all();
        }
        $row['type_title'] = $setting[$row['type_id']]['name'];
        $row['way_title'] = $setting[$row['way_id']]['name'];
        $row['status_title'] = $status_list[$row['status']];
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }
        return $row;        
    }    
}