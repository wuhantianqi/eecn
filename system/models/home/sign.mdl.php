<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: sign.mdl.php 5695 2014-06-27 04:38:29Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Home_Sign extends Mdl_Table
{   
  
    protected $_table = 'home_tuan_sign';
    protected $_pk = 'sign_id';
    protected $_cols = 'sign_id,tuan_id,package_id,uid,mobile,contact,status,remark,dateline,clientip';
    protected $_orderby = array('sign_id'=>'DESC');
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
    
    
    public function items_by_tuan_id($tuan_id,$num=2000)
    {
       $filter['tuan_id'] = (int)$tuan_id;
        
       if($items = $this->items($filter,null,1,$num)){
           foreach($items as $k=>$v){
               $items[$k]['clientip'] = $v['clientip'].'('. K::M("misc/location")->location($v['clientip']) .')';
               $items[$k]['dateline'] = date('Y-m-d H:i:s',$v['dateline']) ;            
           }         
       }
       return $items;
    }
}