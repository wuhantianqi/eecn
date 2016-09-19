<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: attr.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Data_Attr extends Mdl_Table
{   
  
    protected $_table = 'data_attr';
    protected $_pk = 'attr_id';
    protected $_cols = 'attr_id,title,from_id,multi,filter,orderby';
    protected $_orderby = array('orderby'=>'ASC', 'attr_id'=>'ASC');

    protected $_pre_cache_key = 'data-attr-list';
    
    public function attr($attr_id)
    {
        if(!$attr_id = intval($attr_id)){
            return false;
        }
        if($attrs = $this->fetch_all()){
            if($attr = $attrs[$attr_id]){
                if($from = K::M('data/attrfrom')->from($attr['from_id'])){
                    $attr['from_title'] = $from['title'];
                }
                return $attr;
            }
        }
        return false;
    }

    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        if($attr_id = $this->db->insert($this->_table, $data, true)){
            $this->flush();
        }
        return $attr_id;
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

    public function attrs_by_from_id($from_id, $filter=false)
    {
        $attrs = array();
        if($from = K::M('data/attrfrom')->from($from_id)){
            if($items = $this->fetch_all()){
                $obj = K::M('data/attrvalue');
                foreach($items as $k=>$v){
                    if($from['from_id'] == $v['from_id'] && (empty($filter) || $v['filter'] == 'Y')){
                        $v['values'] = $obj->value_by_attr($v['attr_id']);
                        $attrs[$k] = $v;
                    }
                }
            }
        }
        return $attrs;
    }

    public function attrs_by_from($key, $filter=false)
    {
        $attrs = array();
        if($from = K::M('data/attrfrom')->from_by_key($key)){
            if($items = $this->fetch_all()){
                $obj = K::M('data/attrvalue');
                foreach($items as $k=>$v){
                    if($from['from_id'] == $v['from_id'] && (empty($filter) || $v['filter'] == 'Y')){
                        $v['values'] = $obj->value_by_attr($v['attr_id']);
                        $attrs[$k] = $v;
                    }
                }
            }
        }
        return $attrs;
    }

    public function from_list()
    {
        return K::M('data/attrfrom')->fetch_all();
    }

    protected function _check($data, $attr_id=null)
    {
        if(empty($data['title'])){
            $this->err->add('属性名不能为空', 451);
            return false;
        }
        $data['multi'] = strtoupper($data['multi']) == 'Y' ? 'Y' : 'N';
        $data['filter'] = strtoupper($data['filter']) == 'Y' ? 'Y' : 'N';
        if(isset($data['order'])){
            $data['orderby'] = (int) $data['orderby'];
        }
        return parent::_check($data);
    }
}