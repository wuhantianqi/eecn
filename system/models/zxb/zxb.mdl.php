<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Zxb_Zxb extends Mdl_Table
{   
  
    protected $_table = 'zxb';
    protected $_pk = 'zxb_id';
    protected $_cols = 'zxb_id,city_id,uid,tenders_id,comment,company_id,contact,mobile,status,remark,audit,clientip,dateline';
	protected $_status = array(1=>'发布装修保',2=>'确认公司',3=>'合同签订',4=>'水电验收',5=>'泥木验收',6=>'油漆验收',7=>'竣工后30天',8=>'交易结束');

	public function get_status(){
        
        return $this->_status;
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

    public function sign_company($zxb_id, $company_id)
    {
        if(!$zxb_id = (int)$zxb_id){
            return false;
        }else if(!$company_id = (int)$company_id){
            return false;
        }
        return $this->update($zxb_id, array('company_id'=>$company_id, 'status'=>2), true);
    }
}