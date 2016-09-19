<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: news.mdl.php 5425 2014-06-05 11:33:31Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_News extends Mdl_Table
{   
  
    protected $_table = 'company_news';
    protected $_pk = 'news_id';
    protected $_cols = 'news_id,city_id,company_id,views,title,content,audit,dateline,clientip';
    protected $_orderby = array('news_id'=>'DESC');

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

    public function items_by_company($company_id, $page=1, $limit=50, &$count=0)
    {
        if(!$company_id = (int)$company_id){
            return false;
        }
        return $this->items(array('company_id'=>$company_id, 'audit'=>1), null, $page, $limit, $count);
    }

    public function news_count($val)
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
                K::M('company/company')->update($k, array('news_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }
    
    public function get_count_by_company_id($company_id)
    {
        $company_id = (int)$company_id;
        return $this->count(" company_id = {$company_id} ");
    }

    protected function _format_row($row)
    {
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = $this->_status[$row['status']];
        return $row;
    }        
}