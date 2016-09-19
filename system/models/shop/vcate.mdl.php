<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: vcate.mdl.php 2239 2013-12-16 10:49:48Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Vcate extends Mdl_Table
{   
  
    protected $_table = 'shop_vcate';
    protected $_pk = 'vcat_id';
    protected $_cols = 'vcat_id,shop_id,title,orderby';
    protected $_orderby = array('orderby'=>'ASC', 'vcat_id'=>'DESC');
    
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

    public function options($shop_id)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $options = array();
        if($items = $this->items(array('shop_id'=>$shop_id), null, 1, 50)){
            foreach($items as $k=>$v){
                $options[$k] = $v['title'];
            }
        }
        return $options;        
    }

    public function items_by_shop($shop_id)
    {
        return $this->items(array('shop_id'=>$shop_id), null, 1, 50);
    }
}