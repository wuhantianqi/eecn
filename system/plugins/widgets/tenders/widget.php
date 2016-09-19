<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 3464 2014-10-25 09:09:38Z youyi $
 */

class Widget_Tenders extends Model
{
    
    public function index(&$params)
    {
		$data['limit'] = $params['limit'] ? $params['limit'] : 4;
       
        $filter = array('audit'=>1);
        if($params['city_id']){
            $filter['city_id'] = (int)$params['city_id'];
        }
        
        $tuan = K::M('home/tuan')->items($filter,array('tuan_id'=>'DESC') , 1,$data['limit']);
        $home_ids = array();
        foreach($tuan as $key=>$val){
            if(!empty($val['home_id'])) $home_ids[$val['home_id']] = $val['home_id'];
            $tuan[$key]['end_time'] = strtotime($val['end_time']) - __TIME + rand(0,99);
        }
        if(!empty($home_ids)) $data['home_list'] = K::M('home/main')->items_by_ids($home_ids);
        $data['tuan'] = $tuan;
        $params['tpl'] = $params['tpl'] ? $params['tpl']  : 'index.html'; 
        return $data;        
    }

	public function from(&$params)
	{
		if(!$params['tpl']){
			if(!in_array($params['type'], array('label', 'checkbox', 'radio', 'option'))){
				$params['type'] = 'option';
			}
			$params['tpl'] = 'widget:default/'.$params['type'].'.html';
		}
		$data['value'] = $params['value'] ? $params['value'] : 0;
    	$data['options'] = K::M('tenders/tenders')->from_list();
    	return $data;			
	}
    
	public function setting(&$params)
	{
		if(!$params['tpl']){
			$params['tpl'] = 'widget:default/option.html';
		}
		$type_list = K::M('tenders/setting')->get_type();
		if(!$type = $params['type']){
			return false;
		}else if(!$type_id = (int)$type_list[$type]){
			return false;
		}
		$data['value'] = $params['value'] ? $params['value'] : 0;
    	$data['options'] = K::M('tenders/setting')->options($type_id);
    	return $data;
	}

	

    public function fast(&$params)
    {
        $data = $params;
        if(!$params['tpl']){
            $params['tpl'] = 'widget:tenders/fast.html';
        }
        return $data;
    }
   
}