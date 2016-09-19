<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: mendian.mdl.php 2414 2013-12-20 11:04:25Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Mendian extends Mdl_Table
{   
  
    protected $_table = 'shop_mendian';
    protected $_pk = 'mendian_id';
    protected $_cols = 'mendian_id,shop_id,title,desc,addr,phone,contact,thumb,content,views,audit,clientip,dateline';
    protected $_orderby = array('mendian_id'=>'DESC');
    
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

    public function items_by_shop($shop_id, $p=1, $l=50, &$count=0)
    {
        return $this->items(array('shop_id'=>$shop_id, 'audit'=>1), null, $p, $l, $count);
    }

    public function format_items_ext($items)
    {
        if(empty($items)){
            return false;
        }
        $shop_ids = array();
        foreach((array)$items as $k=>$v){
            $shop_ids[$v['shop_id']] = $v['shop_id'];
        }
        if($shop_ids){
            $shop_list = K::M('shop/shop')->items_by_ids($shop_ids);
        }
        foreach((array)$items as $k=>$v){
            $shop = array();
            if(!$shop = $shop_list[$v['shop_id']]){
                $member = array();
            }
            $v['ext'] = array('shop'=>$shop);
            $items[$k] = $v;            
        }
        return $items;
    }      
}