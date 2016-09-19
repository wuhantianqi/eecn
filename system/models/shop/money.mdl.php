<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: money.mdl.php 6177 2014-08-28 02:57:24Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Money extends Mdl_Table
{   
  
    protected $_table = 'shop_money';
    protected $_pk = 'id';
    protected $_cols = 'id,shop_id,money,log,audit,clientip,dateline';
    protected $_orderby = array('id'=>'DESC');

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

    public function total_money()
    {
        $sql = "SELECT SUM(`money`) as total_money FROM ".$this->table('shop');
        return $this->db->GetOne($sql);
    }

    //申请提现
    public function request_tixian($shop_id, $money, $log='')
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $a = array('shop_id'=>$shop_id, 'money'=>$money, 'log'=>$log, 'audit'=>0);
        $a['dateline'] = __CFG::TIME;
        $a['clientip'] = __IP;
        return $this->db->insert($this->_table, $a, true);
    }

    public function agree_tixian($id)
    {
        if(!$id = (int)$id){
            $this->err->add('未指定要提现申请记录', 411);
        }else if(!$log = $this->detail($id)){
            $this->err->add('体现记录不存在', 412);
        }else if($shop = K::M('shop/shop')->detail($log['shop_id'])){
            $this->err->add('商家不存在无法提现', 412);
        }else if($log['money'] < 0){
            $this->err->add('提现金额非法', 412);
        }
    }
}