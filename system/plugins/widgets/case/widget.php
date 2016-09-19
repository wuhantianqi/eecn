<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 6097 2014-08-15 12:55:34Z youyi $
 */

class Widget_Case extends Model
{

    public function index(&$params)
    {
		
        
    }
    
	public function indexcase(&$params)
    {
        $data['limit'] = $params['limit'] ? $params['limit'] : 5;
        
        $case = K::M('case/case')->items(array('audit'=>1,'closed'=>0),array('case_id'=>'DESC') , 1,$data['limit']);
        $company_ids = array();
        foreach($case as $val){
            if(!empty($val['company_id'])) $company_ids[$val['company_id']] = $val['company_id'];
        }
        if(!empty($company_ids)) $data['case_new_company_list'] = K::M('company/company')->items_by_ids($company_ids);
        $data['case_new'] = $case;
        $params['tpl'] = 'indexcase.html'; 
        return $data;
    }
	
    public function casenew(&$params)
    {
        $data['limit'] = $params['limit'] ? $params['limit'] : 5;        
        $case = K::M('case/case')->items(array('audit'=>1,'closed'=>0,'home_id'=>'>:0'),array('case_id'=>'DESC') , 1,$data['limit']);
        $company_ids = array();
        foreach($case as $val){
            if(!empty($val['company_id'])) $company_ids[$val['company_id']] = $val['company_id'];
        }
        if(!empty($company_ids)) $data['case_new_company_list'] = K::M('company/company')->items_by_ids($company_ids);
        $data['case_new'] = $case;
        $params['tpl'] = $params['tpl'] ? $params['tpl'] : 'casenew.html'; 
        return $data;
    }
	
	public function cate (&$params)
    {
		$attr_values = K::M('data/attr')->attrs_by_from('zx:case');
        $attr_keys = array();
        foreach ($attr_values as $key => $value) {
            $http_key['attr' . $key] = 0;
        }
        
        $url = array();
        foreach ($attr_values as $key => $value) {
            foreach ($value['values'] as $k => $v) {
                $link = K::M('helper/link')->mklink('case:items', array_merge($http_key, array('attr' . $key => $k)), array(), true);
                $url[] = array('title'=>$v['title'],'link'=>$link); 
            }
        }
		shuffle($url);
        $data['url'] = $url;
        $params['tpl'] = $params['tpl'] ? $params['tpl'] :  'cate.html'; 
        return $data;
	}

	public function caseindex()
	{
		$data = K::M('data/attr')->attrs_by_from('zx:case');
		return $data;
	}
		
	
    public function cloud(&$params)
    {
        $attr_values = K::M('data/attr')->attrs_by_from('zx:case');
        $attr_keys = array();
        foreach ($attr_values as $key => $value) {
            $http_key['attr' . $key] = 0;
        }
        
        $url = array();
        foreach ($attr_values as $key => $value) {
            foreach ($value['values'] as $k => $v) {
                $link = K::M('helper/link')->mklink('case:items', array_merge($http_key, array('attr' . $key => $k)), array(), true);
                $url[] = '{text: "'.$v['title'].'", weight: '.rand(1,10).', link: "'.$link.'"}';
            }
        }
        $data['cloud_words'] = join(',',$url);
        $params['tpl'] = $params['tpl'] ? $params['tpl'] :  'cloud.html'; 
        return $data;
        
    }
    
    
}