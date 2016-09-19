<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Activity_Activity extends Mdl_Table
{   
  
    protected $_table = 'activity';
    protected $_pk = 'activity_id';
    protected $_cols = 'activity_id,title,cate_id,city_id,thumb,banner,phone,qq,addr,tmpl,bg_time,end_time,end_sign,sign_num,views,lng,lat,jt,sj,intro,info,orderby,audit,clientip,dateline';

    protected function _format_row($row)
    {
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = $this->_status[$row['status']];
        return $row;
    }
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
}