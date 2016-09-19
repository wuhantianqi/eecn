<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: main.mdl.php 2100 2013-12-11 05:42:10Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Activity_Main extends Mdl_Table
{   
  
    protected $_table = 'activity';
    protected $_pk = 'id';
    protected $_cols = 'id,title,cate_id,city_id,area_id,reg_time,bg_time,end_time,intro,face_pic,banner,sign_num,tel,addr,traffic,template,process,lng,lat,sj,closed';
    protected $_orderby = array('id'=>'DESC');

    protected $_hot_orderby = array('sign_num'=>'DESC');
    protected $_hot_filter = array('audit'=>'1', 'closed'=>'0');
    protected $_new_orderby = array('id'=>'DESC');
    protected $_new_filter = array('closed'=>'0');
    
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
    
    public function renew($val, $force=false)
    {
        $ret = false;
        if(!empty($val)) {
                $this->_checkpk();
                if(is_array($val)){
                        $val = implode(',', $val);
                }
                if(!K::M('verify/check')->ids($val)){
                        return false;
                }
                $val = explode(',', $val);
                $ret = $this->db->update($this->_table, array('closed'=>0), self::field($this->_pk, $val));
                $this->clear_cache($val);
        }
        return $ret;
    }    
}