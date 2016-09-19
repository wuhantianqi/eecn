<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: attr.mdl.php 2335 2013-12-18 17:15:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Attr extends Mdl_Table
{   
  
    protected $_table = 'shop_attr';
    protected $_pk = 'attr_id';
    protected $_cols = 'attr_id,title,cat_id,multi,filter,orderby';
    protected $_orderby = array('cat_id'=>'ASC','orderby'=>'ASC');

    protected $_pre_cache_key = 'shop-attr-list';
    
    public function attr($attr_id)
    {
        if(!$attr_id = intval($attr_id)){
            return false;
        }
        if($attrs = $this->fetch_all()){
            if($attr = $attrs[$attr_id]){
                $attr = $this->_format_row($attr);
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

    public function update($attr_id, $data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data,  $attr_id)){
            return false;
        }
        if($rs = $this->db->update($this->_table, $data, $this->field($this->_pk, $attr_id))){
            $this->flush();
        }
        return $rs;
    }

    public function attrs_by_cat($cat_id, $filter=false)
    {
        $attrs = array();
        if($cate = K::M('shop/cate')->cate($cat_id)){
            if($items = $this->fetch_all()){
                $obj = K::M('shop/attrvalue');
                foreach($items as $k=>$v){
                    if($cat_id == $v['cat_id'] && (empty($filter) || $v['filter'] == 'Y')){
                        $v['values'] = $obj->value_by_attr($v['attr_id']);
                        $attrs[$k] = $v;
                    }
                }
            }
        }
        return $attrs;
    }

    protected function _format_row($row)
    {
        if($cate = K::M('shop/cate')->cate($row['cat_id'])){
            $row['cat_title'] = $cate['title'];
        }
        return $row;  
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