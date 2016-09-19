<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Home_Site extends Mdl_Table
{   
  
    protected $_table = 'home_site';
    protected $_pk = 'site_id';
    protected $_cols = 'site_id,city_id,area_id,uid,company_id,case_id,thumb,title,home_name,home_id,house_mj,lng,lat,price,addr,intro,status,audit,clientip,dateline';
    protected $_orderby = array('site_id'=>'DESC');

    protected $order_list = array(0=>array('title'=>'默认'),1=>array('title'=>'面积'),2=>array('title'=>'价格'));
    protected $_status = array(1=>'开工大吉',2=>'水电改造',3=>'泥瓦工阶段',4=>'木工阶段',5=>'油漆阶段',6=>'安装',7=>'验收完成');

    public function get_status(){
        
        return $this->_status;
    }
    public function get_order(){
        
        return $this->order_list;
    }
    
    public function create($data,$checked=false)
    {   
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        $data['dateline'] = __CFG::TIME;
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk,$data,$checked=false)
    {   
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }        
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    protected function _format_row($row)
    {
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        $row['status_title'] = $this->_status[$row['status']];
        return $row;
    }

    public function site_count($val)
    {
        if($site = K::M('home/site')->detail($val)){
            if($company_id = (int)$site['company_id']){
                $this->company_count($company_id);
            }
            if($uid = (int)$site['uid']){
                $member = K::M('member/member')->detail($uid);
                if($member['from'] == 'gz'){
                    $this->uid_count($uid,$member['from']);
                }
            }
            if ($home_id = (int) $site['home_id']) {
                $this->home_count($home_id);
            }
        }
    }

    public function down_count($site)
    {
        if($company_id = (int)$site['company_id']){
            $this->company_count($company_id);
        }
        if($uid = (int)$site['uid']){
            $member = K::M('member/member')->detail($uid);
            if($member['from'] == 'gz'){
                $this->uid_count($uid,$member['from']);
            }
        }
        if ($home_id = (int) $site['home_id']) {
            $this->home_count($home_id);
        }
    }

    public function downs_count($items)
    {
        $company_ids = $homes_id = $uids = array();
        foreach($items as $v){
            $company_ids[$v['company_id']] = $v['company_id'];
            $uids[$v['uid']] = $v['uid'];
            $homes_id[$v['home_id']] = $v['home_id'];
        }

        if($company_ids){
            $this->company_count($company_ids);
        }
        if($homes_id){
            $this->home_count($homes_id);
        }
        if($uids){
            $member = K::M('member/member')->items_by_ids($uids);
            $gzs  = array();
            foreach($member as $k => $v){
                if($v['from'] == 'gz'){
                    $gzs[$v['uid']] = $v['uid'];
                }
            }
            if(!empty($gzs)){
                $this->uid_count($gzs,'gz');
            }
            
            
        }
    }

    public function company_count($val)
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
                K::M('company/company')->update($k, array('site_num'=>$v), true);
                $count ++;
            }            
        }
        K::M('company/company')->update($val, array('last_site' => __TIME));
        return $count;
    }

    public function uid_count($val,$table)
    {
        $count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT uid, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('uid', $val)." GROUP BY uid";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['uid']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M($table.'/'.$table)->update($k, array('site_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }

    public function home_count($val)
    {
        $count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT home_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('home_id', $val)." GROUP BY home_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['home_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('home/home')->update($k, array('site_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }

    public function format_items_ext($items)
    {
        if(empty($items)){
            return false;
        }
        $home_ids = $company_ids = array();
        foreach((array)$items as $k=>$v){
            if($v['home_id']){
                $home_ids[$v['home_id']] = $v['home_id'];
            }
            if($v['company_id']){
                $company_ids[$v['company_id']] = $v['company_id'];
            }
        }
        if($home_ids){
            $home_list = K::M('home/home')->items_by_ids($home_ids);
        }
        if($company_ids){
            $company_list = K::M('company/company')->items_by_ids($company_ids);
        }
        foreach((array)$items as $k=>$v){
            if(!$home = $home_list[$v['home_id']]){
                $home = array();
            }
            if(!$company = $company_list[$v['company_id']]){
                $company = array();
            }
            $v['ext'] = array('home'=>$home, 'company'=>$company, 'status_list'=>$this->_status);
            $items[$k] = $v;            
        }
        return $items;
    }

    public function items_list($filter=array(), $orderby=null, $p=1, $l=20, &$count=0){
        
        $list = $this->items($filter, $orderby, $p, $l, $count);
        foreach($list as $k => $v){
            $company_id[$v['company_id']] = $v['company_id'];
        }
        $company_ids = implode(',',$company_id);
        $where ='company_id IN ('.$company_ids.')';
        $sql = "SELECT  company_id,name FROM ".$this->table('company')."  WHERE $where";
        if($rs = $this->db->query($sql)){
             while($row = $rs->fetch()){
                $rows[$row['company_id']] = $row['name'];
            }
        }
        foreach($list as $k => $v){
            foreach($rows as $key => $val){
                if($v['company_id'] == $key){
                    $list[$k]['company_name'] = $val;
                }
            }
        }
        return $list;
        
    }

/*public function items($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
        $where = $this->where($filter);
        if($orderby == '2'){
            $orderby = "order by house_mj desc";
        }else if($orderby == '3'){
            $orderby = "order by price asc";
        }else{
            $orderby = $this->order($orderby, null); 
        }
        
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$this->table($this->_table)."  WHERE $where $orderby $limit";
        if($rs = $this->db->query($sql)){
            while($row = $rs->fetch()){
                $items[] = $row;
            }
            
        }
        return $items;
    }  */  

}