<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: attrvalue.mdl.php 2335 2013-12-18 17:15:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Attrvalue extends Mdl_Table
{   
  
    protected $_table = 'shop_attr_value';
    protected $_pk = 'attr_value_id';
    protected $_cols = 'attr_value_id,attr_id,title,orderby';
    protected $_pre_cache_key = 'shop-attrvalue-list';

    protected $_orderby = array('orderby'=>'ASC');
    
    public function create($attr_id, $data)
    {
        if(!$data = $this->_check($data)){
            return false;
        }
        $data['attr_id'] = $attr_id;
        if($attr_value_id = $this->db->insert($this->_table, $data, true)){
            $this->flush();
        }
        return $attr_value_id;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check($data,  $pk)){
            return false;
        }
        if($this->db->update($this->_table, $data, $this->field($this->_pk, $pk))){
            $this->flush();
            return true;
        }
        return false;
    }

    public function delete($vids, $force=false)
    {
        if(is_numeric($vids)){
            $where = "attr_value_id='$vids'";
        }else if(is_array($vids)){
            $where = "attr_value_id IN(".implode(',', $vids).")";
        }else if(K::M('verify/check')->ids($vids)){
            $where  = "attr_value_id IN($vids)";
        }else{
            return false;
        }
        $sql = "DELETE FROM ".$this->table($this->_table)." WHERE $where";
        if($this->db->Execute($sql)){
            $this->flush();
            return true;
        }
        return false;
    }

    public function value_by_attr($attr_id)
    {
        if(!$attr_id = intval($attr_id)){
            return false;
        }
        $items = array();
        if($values = $this->fetch_all()){
            foreach($values as $k=>$v){
                if($v['attr_id'] == $attr_id){
                    $items[$k] = $v;
                }
            }
        }
        return $items;
    }

    public function attrvalue($value_id)
    {
        if($values = $this->fetch_all()){
            return $values[$value_id];
        }
        return false;
    }

    protected function _check($data, $vid=null)
    {
        if(!$title = $data['title']){
            $this->err->add('选项标题不能为空');
            return false;
        }
        $orderby = $data['orderby'] ? intval($data['orderby']) : 50;
        return array('title'=>$title, 'orderby'=>$orderby);
    }
}