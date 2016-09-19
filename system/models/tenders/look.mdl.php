<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: look.mdl.php 5402 2014-06-03 10:17:57Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Tenders_Look extends Mdl_Table
{   
  
    protected $_table = 'tenders_look';
    protected $_pk = 'look_id';
    protected $_cols = 'look_id,tenders_id,uid,content,clientip,dateline,is_signed,city_id,from,company_id,info';
    protected $_orderby = array('look_id'=>'DESC');
    
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
   
    public function items_by_tenders($tenders_id, $p=1, $l=200, &$count=0)
    {
        if(!$tenders_id = (int)$tenders_id){
            return false;
        }
        return $this->items(array('tenders_id'=>$tenders_id), null, $p, $l, $count);
    }

    public function is_looked($uid, $tenders_id)
    {
        if(!$tenders_id = (int)$tenders_id){
            return false;
        }else if(!$uid = (int)$uid){
            return false;
        }
        return $this->count("tenders_id='{$tenders_id}' AND uid='{$uid}'");
    }

    public function sign($look_id)
    {
        if($look = $this->detail($look_id)){
            $this->db->Execute("UPDATE ". $this->table($this->_table)." SET is_signed=0 WHERE tenders_id=".$look['tenders_id']);
            if($this->update($look_id, array('is_signed'=>1))){
                return K::M('tenders/tenders')->sign($look['tenders_id'], $look['uid']);
            }
        }
        return false;
    }

	public function getdata($uid)
	{
		
        if(!$uid = (int)$uid){
            return false;
        }else if(!$member = K::M('member/member')->member($uid)){
            return false;
        }
        $info = array();
        $company_id = 0;
        if($member['from'] == 'company'){
            if($company = K::M('company/company')->company_by_uid($uid)){
                $data['city_id'] = $company['city_id'];
                $data['company_id'] = $company['company_id'];
				$data['from'] = 'company';
				$info['name'] = $company['name'];
                $info['company_id'] = $company['company_id'];
            }
        }else if('gz' == $member['from']){
            if($gz = K::M('gz/gz')->detail($uid)){
                $data['city_id'] = $gz['city_id'];
                $data['from'] = 'gz';
				$info['name'] = $gz['name'];
                $info['gz_id'] = $gz['uid'];
            }
        }else if('designer' == $member['from']){
            if($designer = K::M('designer/designer')->detail($uid)){
                $data['city_id'] = $designer['city_id'];
                $data['from'] = 'designer';
				$info['name'] = $designer['name'];
                $info['designer_id'] = $designer['uid'];
            }
        }else if('mechanic' == $mmeber['from']){
            if($mechanic = K::M('mechanic/mechanic')->detail($uid)){
                $data['city_id'] = $mechanic['city_id'];
                $data['from'] = 'mechanic';
				$info['name'] = $mechanic['name'];
                $info['mechanic_id'] = $mechanic['uid'];
            }
        }else if('shop' == $member['from']){
            if($shop = K::M('shop/shop')->shop_by_uid($uid)){
                $data['city_id'] = $shop['city_id'];
                $data['from'] = 'shop';
				$info['name'] = $shop['name'];
                $info['shop_id'] = $shop['shop_id'];
            }
        }
		$data['info'] = serialize($info);
		return $data;
   
	}
    
    public function tongji($uid = 0,$bg_time = null,$end_time = null,$city_id = 0){
        $local = array();
        if(!empty($uid)){
            $uid = (int)$uid;
            $local[] = " uid = {$uid}";
        }
        if(!empty($bg_time)){
            $bg_time = (int)$bg_time;
            $local[] = " dateline >= {$bg_time}";
        }
        if(!empty($end_time)){
            $end_time = (int)$end_time;
            $local[] = " dateline <= {$end_time}";
        }
     
        $where  = '';
        if(!empty($local)){
            $where = " WHERE  ".join(' AND ',$local);
        }
        $where2 = '';
        if(!empty($city_id)){
            $city_id = (int)$city_id; 
            $where2 = " WHERE b.city_id = {$city_id} ";
        }
        
		$items = array();
        $sql = "SELECT a.num,b.uid,b.name,b.logo FROM  (SELECT count(1) as num , uid  FROM ".$this->table($this->_table)." {$where}  group by uid)a join  ".$this->table('company')." b  ON a.uid  = b.uid {$where2}";
        if($rs = $this->db->query($sql)){
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                $items[] = $row;
            }
        }
		
		return $items;
    }
    
}