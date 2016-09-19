<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: news.ctl.php 10464 2015-05-25 12:10:41Z fengnannan $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_News extends Ctl 
{
    
    public function index()
    {
        $this->items();   
    }

    public function items($page=1)
    {
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter['city_id'] = $this->request['city_id'];
        $filter['audit'] = 1;
        if($items = K::M('company/news')->items($filter, $orderby, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page,$this->mklink('news:items', array('{page}')));
            foreach($items as $k=>$v){
                $v['desc'] = K::M('content/html')->text($v['content'], true);
                $items[$k] = $v;
            }
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->seo->init('news_items', array('page'=>($page > 1) ? $page : ''));
        $this->tmpl = 'news/items.html';        
    }

    public function detail($news_id)
    {
        if(!$news_id = (int)$news_id){
            $this->error(404);
        }else if(!$detail = K::M('company/news')->detail($news_id)){
            $this->error(404);
        }else{
			K::M('company/news')->update_count($news_id, 'views', 1);
            $this->check_company($detail['company_id']);
            $this->pagedata['detail'] = $detail;
            $seo = array('title'=>$detail['title'], 'company_name'=>$company['name'], 'news_desc'=>'');
            $seo['news_desc'] = K::M('content/text')->substr(K::M('content/html')->text($detail['content'], true), 0, 200);
            $this->seo->init('news_detail', $seo);
            $this->tmpl = 'news/detail.html';
        }
    }
}