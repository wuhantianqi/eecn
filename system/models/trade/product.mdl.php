<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product.mdl.php 2621 2013-12-30 01:10:05Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Trade_Product extends Mdl_Table
{   
  
    protected $_table = 'order_product';
    protected $_pk = 'order_pid';
    protected $_cols = 'order_pid,order_id,product_id,spec_id,product_name,spec_name,number,product_price,freight,amount';
    protected $_orderby = array('order_pid'=>'DESC');
   
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

    public function order_products($order_id)
    {
        if(!$order_id = (int)$order_id){
            return false;
        }
        $products = array();
        $sql = "SELECT op.*,p.photo FROM ".$this->table($this->_table)." op LEFT JOIN ".$this->table('product')." p ON op.product_id=p.product_id WHERE op.order_id=$order_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $products[$row['order_pid']] = $row;
            }
        }
        return $products;
    }
}