<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tuan.mdl.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Home_Tuan extends Mdl_Table
{   
    protected $_orderby = array('tuan_id'=>'DESC');
    protected $_table = 'home_tuan';
    protected $_pk = 'tuan_id';
    protected $_cols = 'tuan_id,title,city_id,area_id,home_id,company_id,sign_num,qy_num,jieyue,content,sg_num,stime,ltime,audit,dateline';
	
	protected $order_list = array(0=>array('title'=>'默认'),1=>array('title'=>'报名'),2=>array('title'=>'签约'));
	
	public function get_order(){
        
        return $this->order_list;
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
    
    public function detail_by_home_id($home_id){
       
        $home_id = (int) $home_id;
        $today = date('Y-m-d',__TIME);
		$sql = "SELECT * FROM ".$this->table($this->_table)." WHERE   home_id = '{$home_id}' AND audit=1 AND end_time > '{$today}' ";

		if($detail = $this->db->GetRow($sql)){
			$detail = $this->_format_row($detail);
		}
		return $detail;       
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
            if(empty($v['thumb']) && !empty($home['thumb'])){
                $v['thumb'] = $home['thumb'];
            }
            $v['ext'] = array('home'=>$home, 'company'=>$company);
            $items[$k] = $v;            
        }
        return $items;
    }
    
}