<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Home_item extends Mdl_Table
{   
  
    protected $_table = 'home_site_item';
    protected $_pk = 'item_id';
    protected $_cols = 'item_id,site_id,status,title,content,photo_ids,clientip,dateline';
    protected $_orderby = array('status'=>'ASC');

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

    public function diary_status($site_id)
    {
        $status = K::M('home/site')->get_status();
        $status_list = array();
        foreach($status as $k=>$v){
            $status_list[$k] = array('title'=>$v);
        }
        if($site_id = (int)$site_id){
            if($items = $this->items(array('site_id'=>$site_id), null, 1, 50)){
                foreach($items as $k=>$v){
                    if($status_list[$v['status']]){
                        $status_list[$v['status']]['has_diary'] = true;
                    }
                }
            }
        }
        return $status_list;
    }

	public function get_status($site_id,$status)
    {
		$filter = array('site_id'=>$site_id);
		$detail = K::M('home/item')->items($filter);
		foreach($detail as $k => $v){
			$havestatus[$v['status']] = $status[$v['status']];
		}		
		if($havestatus){
			$intersection = array_diff($status, $havestatus);
		}else{
			$intersection = $status;
		}		
		return $intersection;
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

    protected function _check($data, $item_id=null)
    {
        if(isset($data['content'])){
            if(preg_match_all("/(photo\/\d+\/\d{8}_[\dA-F]{32}\.(jpg|gif|png|jpeg))\?PID(\d+)/i", $detail['content'], $matches)){
                $data['photo_ids'] = implode(',',$matches[3]);
            }
        }
        return parent::_check($data, $item_id);
    }
}