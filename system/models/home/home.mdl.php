<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Home_Home extends Mdl_Table
{   
  
    protected $_table = 'home';
    protected $_pk = 'home_id';
    protected $_cols = 'home_id,city_id,area_id,title,name,thumb,phone,kfs,qq_qun,price,kp_date,jf_date,lng,lat,addr,views,photos,case_num,site_num,content,orderby,audit,closed,clientip,dateline';
	protected $_orderby = array('orderby'=>'ASC','home_id'=>'DESC');
	protected $order_list = array(1=>array('title'=>'默认'),2=>array('title'=>'方案'),3=>array('title'=>'价格'));
	
	public function get_order(){
        
        return $this->order_list;
    }
    
	protected function _format_row($row)
    {
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        if($area = K::M('data/area')->area($row['area_id'])){
            $row['area_name'] = $area['area_name'];
        }
        $row['status_title'] = $this->_status[$row['status']];
        return $row;
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

    public function items($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
        if($attrs = $filter['attrs']){
            $attr_ids = join(',',$attrs);
            $attr_count = array_sum($attrs);
            $attr_sql = "SELECT home_id FROM ".$this->table('home_attr')." WHERE attr_value_id IN($attr_ids) GROUP BY home_id HAVING SUM(attr_value_id)=$attr_count";
        }
		$filter['closed'] = '0';
        unset($filter['attrs']);
        $where = $this->where($filter);
        if($attr_sql){
            $where .= " AND home_id IN($attr_sql)";
        }

		if($orderby == '2'){
			$orderby = "order by case_num desc";
		}else if($orderby == '3'){
			$orderby = "order by price asc";
		}else{
			$orderby = $this->order($orderby, null); 
		}
        
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$this->table($this->_table)."  WHERE $where $orderby $limit";
        if($rs = $this->db->query($sql)){
            $count = $this->db->GetOne("SELECT FOUND_ROWS()");
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                if($this->_pk){
                    $items[$row[$this->_pk]] = $row;
                }else{
                    $items[] = $row;
                }
            }
        }
        return $items;
    }    
}