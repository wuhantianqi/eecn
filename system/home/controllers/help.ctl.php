<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: help.ctl.php 5400 2014-06-03 09:49:17Z $
 */

class Ctl_Help extends Ctl 
{
    
     public $_call = 'index';     
     
     public function index($page)
     {
        $page = htmlspecialchars($page);
        $city_id = $this->request['city_id'];
        if(!$detail = K::M('article/article')->item_by_page($page,null)){
            $this->err->add('没有您要查看的内容', 211);
        }else{
            if($detail['linkurl']){
                header("Location:".$detail['linkurl']);
                exit;
            }
            $items =  K::M('article/article')->items(array('from'=>'help','closed'=>0, 'audit'=>1, 'hidden'=>0), null,1,50); 
            $this->pagedata['cate_list'] = K::M('article/cate')->fetch_all();
            $this->pagedata['page'] = $page;
            $this->pagedata['items'] = $items;
            $this->pagedata['detail'] = $detail;
            $cate = K::M('article/cate')->cate($detail['cat_id']);
            $this->seo->init('article_detail',array(
                'title'         =>   $detail['title'],
                'cate_title'        =>$cate['title'],
                'cate_seo_title'    => $cate['seo_title'],
                'cate_seo_keywords' => $cate['seo_keywords'],
                'cate_seo_description' => $cate['seo_description']               
            ));
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'help/help.html';
        }
     }    
}