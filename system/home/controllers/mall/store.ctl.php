<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mall_Store extends Ctl 
{
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/store-([\d\-]+).html/i', $uri, $match)){
            $system->request['act'] = 'index';
            $system->request['args'] = array($match[2]);
        }      
    }

    public function index()
    {
        $filter = $pager = $SO = array();
        $page = $cat_id = $order = 0;
        $uri = $this->request['uri'];
        if(preg_match('/store-([\d\-]+).html/i', $uri, $match)){
            $a = explode('-', trim($match[1], '-'));            
            if(isset($a[2])){
                $page = $a[2];
                $order = $a[1];
                $cat_id = $a[0];
            }else if(isset($a[1])){
                $page = $a[1];
                $cat_id = $a[0];
            }else{
                $page = $a[0];
            }
            if($cat_id = (int)$cat_id){
                $filter['cat_id'] = $cat_id;
            }
        }
        if($cat_id){
            $cate = K::M('shop/cate')->cate($cat_id);
        }
        $orderby = null;
        if($order == 1){
            $orderby = array('credit'=>'DESC');
        }else if($order == 2){
            $orderby = array('score'=>'ASC');
        }else{
            $order = 0;
        }
        $filter['city_id'] = $this->request['city_id'];
        $filter['audit'] = 1;
        $filter['closed'] = 0;
        if($sokw = trim($this->GP('kw'))){
            $pager['sokw'] = $sokw = htmlspecialchars($sokw);
            $SO['kw'] = $sokw;
            $filter['title'] = "LIKE:%{$sokw}%";
        }
        $pager['order'] = $order;
        $pager['cat_id'] = $cat_id;        
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        if($items = K::M('shop/shop')->items($filter, $orderby, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mall/store', array($cat_id, $order, '{page}'), $SO));
			foreach($items as $k => $v){
				$items[$k] = K::M('shop/shop')->detail($v['shop_id']);
			}
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cate_list'] = K::M('shop/cate')->fetch_all();
        $this->seo->init('mall_store',array('cate_name'=>$cate['title'], 
            'cate_seo_title'=>$cate['seo_title'],
            'cate_seo_keywords'=>$cate['seo_keywords'],
            'cate_seo_description'=>$cate['seo_description'],
            'page'=>($page > 1) ? $page : ''));
        $this->tmpl = 'mall/store/items.html';
    }

}