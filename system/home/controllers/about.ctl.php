<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: help.ctl.php 5400 2014-06-03 09:49:17Z $
 */

class Ctl_About extends Ctl 
{
    
     public $_call = 'index';     
     
     public function index($page)
     {         
        $page = htmlspecialchars($page);
        //$city_id = $this->request['city_id'];
        $this->pagedata['info'] = $detail = K::M('article/article')->item_by_page($page);
        if($detail['linkurl']){
            header("Location:".$detail['linkurl']);
            exit;
        }
        if(empty($this->pagedata['info'])){
            $this->err->add('没有您要查看的内容', 211);
        }else{
			$city_id = $this->system->request['city_id'];
            $items =  K::M('article/article')->items(array('from'=>'about','closed'=>0,'city_id'=>$city_id),array('article_id'=>'ASC'),1,50); 
            $article_id = $this->pagedata['info']['article_id'];            
            $detail = K::M('article/article')->detail($article_id,$city_id,false);
            $this->pagedata['cate_list'] = K::M('article/cate')->fetch_all();
            $this->pagedata['page'] = $page;
            $this->pagedata['items'] = $items;
            $this->pagedata['detail'] = $detail;
            $cate = K::M('article/cate')->cate($this->pagedata['info']['cat_id']);
            $this->seo->init('article_detail',array(
                'title'         =>   $this->pagedata['info']['title'],
                'cate_title'        =>$cate['title'],
                'cate_seo_title'    => $cate['seo_title'],
                'cate_seo_keywords' => $cate['seo_keywords'],
                'cate_seo_description' => $cate['seo_description']               
            ));
            $this->tmpl = 'about/about.html';
        }
     }    
}