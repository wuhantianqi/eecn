<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: youhui.mdl.php 5702 2014-06-27 10:55:04Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_Youhui extends Mdl_Table
{   
  
    protected $_table = 'company_youhui';
    protected $_pk = 'youhui_id';
    protected $_cols = 'youhui_id,city_id,area_id,company_id,title,stime,ltime,photo,content,dateline,clientip,audit,sign_num,flushtime';
    protected $_orderby = array('flushtime'=>'DESC','youhui_id'=>'DESC');

    protected $_hot_orderby = array('sign_num'=>'DESC','youhui_id'=>'DESC');
    protected $_hot_filter = array('audit'=>'1');
    protected $_new_orderby = array('youhui_id'=>'DESC');
    protected $_new_filter = array('audit'=>'1');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }
    

    public function youhui_count($val)
    {
        $count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT company_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('company_id', $val)." GROUP BY company_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['company_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('company/company')->update($k, array('youhui_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }

    public function items_by_company($company_id, $page=1, $limit=50, &$count=0)
    {
        return $this->items(array('company_id'=>$company_id, 'audit'=>1), null, $page, $limit, $count);
    }

    public function get_last_by_company_id($company_id)
    {
        $company_id = (int)$company_id;
        
        $where =" company_id = {$company_id} AND audit = 1 ";
        
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE $where order by youhui_id desc limit 1 ";
        if($detail = $this->db->GetRow($sql)){
            $detail = $this->_format_row($detail);
        }
        return $detail;
    }    
    
    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function items_by_new($filter=array(), $limit=20)
    {
        $date = date('Y-m-d', __CFG::TIME);
        $filter['end_date'] = ">:$date";
        return parent::items_by_new($filter, $limit);
    }

    public function items_by_hot($filter=array(), $limit=20)
    {
        $date = date('Y-m-d', __CFG::TIME);
        $filter['bg_date'] = "<:$date";
        $filter['end_date'] = ">:$date";
        return parent::items_by_hot($filter, $limit);
    }

    protected function _format_row($row)
    {
        if($row['stime'] > __TIME){
            $row['status'] = 'wait';
            $row['status_title'] = '未开始';
            $row['last_day'] = ceil(($row['ltime'] - __TIME)/86400);
        }else if($row['ltime'] < __TIME){
            $row['status'] = 'finish';
            $row['status_title'] = '已结束';
        }else{
            $row['status'] = 'process';
            $row['status_title'] = '进行中';
            $row['last_day'] = ceil(($row['ltime']+86399 - __TIME)/86400);
        }
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        return $row;
    }
}