<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: area.mdl.php 2034 2013-12-07 03:08:33Z $
 */

class Mdl_Data_Area extends Mdl_Table
{   
  
    protected $_table = 'data_area';
    protected $_pk = 'area_id';
    protected $_cols = 'area_id,city_id,area_name,orderby';
    protected $_orderby = array('orderby'=>'ASC', 'city_id'=>'ASC');
    protected $_pre_cache_key = 'data-area-list';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($area_id = $this->db->insert($this->_table, $data, true)){
            $this->flush();
        }
        return $area_id;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        if($this->db->update($this->_table, $data, $this->field($this->_pk, $pk))){
            $this->flush();
        }
        return true;
    }
	
    public function areas_by_city($city_id)
    {
        $areas = array();
        if($items = $this->fetch_all()){
            foreach($items as $k=>$v){
                if($v['city_id'] == $city_id){
                    $areas[$k] = $v;
                }
            }
        }
        return $areas;        
    }
    
    
    public function options($city_id=null)
    {
    	$options = array();
    	if($areas = $this->fetch_all()){
            if($city_id === null){
                $citys = K::M('data/city')->fetch_all();          
                foreach($citys as $k=>$v){
                    foreach($areas as $kk=>$vv){
                        if($vv['city_id'] == $k){
                            $options[$v['city_name']][$kk] = $vv['area_name'];
                        }
                    }
                }
            }else{
        		foreach($areas as $k=>$v){
                    if($v['city_id'] == $city_id){
            			$options[$k] = $v['area_name'];
                    }
        		}
            }
    	}
    	return $options;
    }

    public function area($area_id)
    {
        if(!$area_id = intval($area_id)){
            return false;
        }else if($items = $this->fetch_all()){
            return $items[$area_id];
        }
        return false;
    }    

    protected function _format_row($row)
    {
        static $citys = null;
        if($citys === null){
            $citys = K::M('data/city')->fetch_all();
        }
        if($citys[$row['city_id']]){
            $row['city_name'] = $citys[$row['city_id']]['city_name'];
        }else{
            $row['city_name'] = $row['city_id'];
        }
        return $row;
    }
}