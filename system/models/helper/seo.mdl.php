<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: seo.mdl.php 6035 2014-08-06 15:29:10Z youyi $
 */

class Mdl_Helper_Seo
{   
    
	public $_SEO = array();

    protected $_shop_seo = array();

    protected $_company_seo = array();

    protected $_tmpl = array('title'=>'', 'keywords'=>'', 'description'=>'');

    public function __construct(&$system)
    {

    }

    public function init($ident='index', $params=array())
    {
        $ident = str_replace(':', '_', $ident);
        if(strpos($ident, 'seo_') === false){
            $ident = 'seo_'.$ident;
        }
        if($cfg = K::M('system/systmpl')->detail_by_key($ident)){
            $this->_tmpl = $cfg;
            $city = K::$system->request['city'];
            $site = K::$system->config->get('site');
            $params['sitename'] = $site['title'];
            $params['site_title'] = $site['title'];
            $params['site_desc'] = $site['intro'];
            $params['city_name'] = $city['city_name'];
            $params['site_phone'] = $params['phone'] = $site['phone'];
            $params['city_seo_title'] = $city['seo_title'];
            $params['city_seo_keywords'] = $city['seo_keywords'];
            $params['city_seo_description'] = $city['seo_description'];
            if($this->_shop_seo){
                $params = array_merge($params, $this->_shop_seo);
            }else if($this->_company_seo){
                $params = array_merge($params, $this->_company_seo);
            }
            $this->_parse($params);
        }
        return $this->_SEO;
    }

    public function set_shop($shop)
    {
        $this->_shop_seo = array('shop_title'=>$shop['title'], 'shop_name'=>$shop['name'], 'shop_seo_title'=>$shop['seo_title'], 'shop_seo_keywords'=>$shop['seo_keywords'], 'shop_seo_description'=>$shop['seo_description']);
    }

    public function set_company($company)
    {
        $this->_company_seo = array('company_title'=>$company['title'], 'company_name'=>$company['name'], 'company_seo_title'=>$company['seo_title'], 'company_seo_keywords'=>$company['seo_keywords'], 'company_seo_description'=>$company['seo_description']);
        if($company['info']){
            $this->_company_seo['company_desc'] = K::M('content/text')->substr(K::M('content/html')->text($company['info']), 0, 200);
        }    
    }

    public function set_title($title)
    {
    	$this->_SEO['title'] = $title;
    }

    public function set_keywords($keywords)
    {
    	$this->_SEO['keywords'] = $keywords;
    }

    public function set_description($description)
    {
    	$this->_SEO['description'] = $description;
    }

    protected function _parse($params)
    {
        $a = $b = array();
        foreach($params as $k=>$v){
            $a[] = '{'.$k.'}';
            $b[] = $v;
        }
        $this->_SEO['title'] = str_replace($a, $b, $this->_tmpl['tmpl']);
        $this->_SEO['keywords'] = str_replace($a, $b, $this->_tmpl['tmpl1']);
        $this->_SEO['description'] = str_replace($a, $b, $this->_tmpl['tmpl2']);
    }
}