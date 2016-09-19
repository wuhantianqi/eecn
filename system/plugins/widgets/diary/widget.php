<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 2468 2013-12-24 02:04:32Z $
 */

class Widget_Diary extends Model
{
    
    public function newitems(&$params){
        $data['cfg_status'] = K::M('home/site')->get_status();
        $params['tpl'] = 'newitems.html'; 
        return $data;
    }
    
    public function index(&$params)
    {
		$data['limit'] = $params['limit'] ? $params['limit'] : 5;
     
        $filter = array('audit'=>1);
        if($params['city_id']){
            $filter['city_id'] = (int)$params['city_id'];
        }
        
        $diary = K::M('diary/diary')->items($filter,array('diary_id'=>'DESC') , 1,$data['limit']);
        $company_ids = array();
        foreach($diary as $val){
            if(!empty($val['company_id'])) $company_ids[$val['company_id']] = $val['company_id'];
        }
        if(!empty($company_ids)) $data['diary_company_list'] = K::M('company/company')->items_by_ids($company_ids);
        $data['diary'] = $diary;
        $params['tpl'] = 'index.html'; 
        return $data;
        
    }

	 public function photo(&$params)
    {
		$filter['diary_id'] = $params['diary_id'];
		if($items = K::M('diary/item')->items($filter)){
			$count = 1;
			foreach($items as $k => $v){
				if(strpos($v['content'],'img src')){
					$arr  = array();
					preg_match_all('/<img src\=\"[a-zA-Z0-9\.\/\:]*\/\.\/attachs([\=\/\.\_\"\'\?\.\ 0-9a-zA-Z]+)\" alt\=\"\" \//i',$v['content'],$arr);
					foreach($arr[1] as $key => $val){
						$data['count'] = $count++;
						$data['photo'][] = $val;
					}
				}
			}
		}
		$detail = end($items);
		$data['diary_id'] = $detail['diary_id'];
		$data['dateline'] = $detail['dateline'];
        return $data;
        
    }
    
    public function right(&$params){
        $data['limit'] = $params['limit'] ? $params['limit'] : 5;
     
        $filter = array('audit'=>1);
        if($params['city_id']){
            $filter['city_id'] = (int)$params['city_id'];
        }
        
        $diary = K::M('diary/diary')->items($filter,array('diary_id'=>'DESC') , 1,$data['limit']);
        $company_ids = array();
        foreach($diary as $val){
            if(!empty($val['company_id'])) $company_ids[$val['company_id']] = $val['company_id'];
        }
        if(!empty($company_ids)) $data['diary_company_list'] = K::M('company/company')->items_by_ids($company_ids);
        $data['diary'] = $diary;
        $params['tpl'] = 'right.html'; 
        return $data;
    }

}