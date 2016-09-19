<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Data_Cate extends Mdl_Table
{   
  
    protected $_table = 'data_cate';
    protected $_pk = 'cate_id';
    protected $_cols = 'cate_id,from,title,orderby';
    protected $_orderby = array('from'=>'ASC', 'orderby'=>'DESC');
     protected $_pre_cache_key = 'data-cate-list';
    protected $_from_list = array('company'=>'公司', 'gz'=>'工长', 'mechanic'=>'工人', 'designer'=>'设计师');

    public function from_list()
    {
        return $this->_from_list;
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

    public function cate($cate_id)
    {
        if($items = $this->fetch_all()){
            if($cate = $items[$cate_id]){
                return $cate;
            }
        }
        return false;
    }

    public function options($from)
    {
        $options = array();
        if($items = $this->fetch_all()){
            foreach($items as $v){
                if($v['from'] == $from){
                    $options[$v['cate_id']] = $v['title'];
                }
            }
        }
        return $options;
    }

    public function items_by_from($from)
    {
        $cates = array();
        if($items = $this->fetch_all()){
            foreach($items as $v){
                if($v['from'] == $from){
                    $cates[$v['cate_id']] = $v;
                }
            }
        }
        return $cates;
    }
}