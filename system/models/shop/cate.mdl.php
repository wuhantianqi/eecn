<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cate.mdl.php 3101 2014-01-17 10:05:53Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Cate extends Mdl_Table
{   
  
    protected $_table = 'shop_cate';
    protected $_pk = 'cat_id';
    protected $_cols = 'cat_id,title,parent_id,brand_ids,seo_title,seo_keywords,seo_description,orderby,audit,closed,dateline';
    protected $_orderby = array('parent_id'=>'ASC', 'orderby'=>'ASC');

    protected $_pre_cache_key = 'shop_cat_list';
    

    public function detail($cat_id, $closed=false)
    {
        if($detail = parent::detail($cat_id, $closed)){
            $detail['pids'] = $this->parent_ids($cat_id);
            $detail = $this->_format_row($detail);
        }
        return $detail;
    }

    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($cat_id = $this->db->insert($this->_table, $data, true)){
            $this->flush();
        }
        return $cat_id;
    }

    public function update($cat_id, $data, $checked=false)
    {
        if(!$cat_id = (int)$cat_id){
            return false;
        }else if(!$checked && !$data = $this->_check_schema($data,  $cat_id)){
            return false;
        }
        if($ret = $this->db->update($this->_table, $data, $this->field($this->_pk, $cat_id))){
            $this->flush();
        }
        return $ret;
    }

    public function cate($cat_id)
    {
        if(!$cat_id = (int)$cat_id){
            return false;
        }else if($cate_list = $this->fetch_all()){
            return $cate_list[$cat_id];
        }
        return false;
    }

    public function top_cate($cat_id)
    {
        if(!$cat_id = (int)$cat_id){
            return false;
        }
        if($cats = $this->fetch_all()){
            while($a = $cats[$cat_id]){
                if(empty($a['parent_id'])){
                    return $a;
                }
                $cat_id = $a['parent_id'];
            }
        }
        return false;               
    }

    public function parent_ids($cat_id, $glue=',', $append=false)
    {
        if(!$cat_id = (int)$cat_id){
            return false;
        }
        $pids = $append ? array($cat_id) : array();
        if($cats = $this->fetch_all()){
            while($a = $cats[$cat_id]){
                if($cat_id = $a['parent_id']){
                    array_unshift($pids, $cat_id);
                }
            }
        }
        return implode($glue, $pids);
    }

    public function parents($cat_id)
    {
        if(!$cat_id = (int)$cat_id){
            return false;
        }
        $parents = array();
        if($cats = $this->fetch_all()){
            while($a = $cats[$cat_id]){
                if($parents[$a['cat_id']]){
                    break;
                }
                $cat_id = $a['parent_id'];
                $parents[$a['cat_id']] = $a;
            }
        }
        return array_reverse($parents, true);
    }

    public function children_ids($cat_id, $glue=',')
    {
        if(!$cat_id = (int)$cat_id){
            return false;
        }
        $cat_ids = array($cat_id=>$cat_id);
        if($items = $this->fetch_all()){
            foreach((array)$items as $k=>$v){
                if(in_array($v['parent_id'], $cat_ids)){
                    $cat_ids[$v['cat_id']] = $v['cat_id'];
                }
            }
        }
        return implode($glue, $cat_ids);
    }

    public function options($pid=0)
    {
        $options = array();
        if($items = $this->fetch_all()){
            foreach($items as $k=>$v){
                if($v['parent_id'] == $pid){
                    $options[$k] = $v['title'];
                }
            }
        }
        return $options;        
    }

    public function childrens($pid=0)
    {
        $childrens = array();
        if($items = $this->fetch_all()){
            foreach((array)$items as $k=>$v){
                if($v['parent_id'] == $pid){
                    $childrens[$k] = $v;
                }
            }
        }
        return $childrens;        
    }

    protected function _format_row($row)
    {
        static $brand_list = null;
        if($brand_list === null){
            $brand_list = K::M('shop/brand')->fetch_all();
        }
        $brands = array();
        if($row['brand_ids']){            
            foreach(explode(',', $row['brand_ids']) as $id){
                if($a = $brand_list[$id]){
                    $brands[$id] = $a['title'];
                }
            }            
        }
        $row['brands'] = $brands;
        return $row;
    }
    protected function _check($data, $brand_id=null)
    {
        unset($data['cat_id'], $data['closed'], $data['dateline']);
        if(isset($data['brand_ids'])){
            if(!$data['brand_ids'] = K::M('verify/check')->ids($data['brand_ids'])){
                $data['brand_ids'] = '';
            }
        }
        return parent::_check($data, $brand_id);
    }     
}