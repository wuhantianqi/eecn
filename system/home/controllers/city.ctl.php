<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: city.ctl.php 3407 2014-02-21 06:37:45Z $
 */
class Ctl_City extends Ctl
{
    public function  index()
    {
        $cfg = $this->system->config->get('site');
        if(!$cfg['multi_city']){
            header('Location:index.php');
            exit;
        }
        $city_list = K::M('data/city')->fetch_all(); 
        foreach($city_list as $k=>$v){
			if(!$v['audit']){
                unset($city_list[$k]);
            }
            if($v['pinyin']){
                $py = strtoupper(substr($v['pinyin'], 0, 1));
                $v['py'] = $py;
                $city_list[$k] = $v;
            }
        }
        $this->pagedata['city_list']  = $city_list;
        $this->pagedata['province_list']  =  K::M('data/province')->fetch_all();
        $this->seo->init('city');
        $this->tmpl = 'city/city.html';
    }    
}