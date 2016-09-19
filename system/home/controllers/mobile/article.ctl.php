<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Article extends Ctl_Mobile
{
    public function index()
	{
		$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
		$filter['from'] = 'article';
        $tree = K::M('article/cate')->tree('article');
		foreach($tree as $k => $v){
			if($k == '8'){
				foreach($v as $kk => $vv){
					$article = $vv;
				}
			}
		}
		$this->pagedata['article'] = $article;
		$pager['backurl'] = $this->mklink('mobile');
		$this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/article/cate.html'; 
	
	}
    public function items($cate_id,$page = 1)
    {

        if(!$cate_id = (int)$cate_id){
            $this->error(404);
        }else if(!$cate = K::M('article/cate')->detail($cate_id)){
           $this->error(404);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 6;
            $pager['count'] = $count = 0;
            $filter['audit'] = 1;          
            $filter['closed'] = 0;
            $filter['cat_id'] = $cate_id;  
            if($items = K::M('article/article')->items($filter,null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($cate_id, '{page}')));
                $this->pagedata['items'] = $items;                
            }
            $pager['backurl'] = $this->mklink('mobile/article');
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/article/items.html'; 
        }
    }
    
	public function detail($article_id)
    {
        if (!($article_id = (int) $article_id) && !($article_id = (int) $this->GP('article_id'))) {
            $this->error(404);
        }else if (!$detail = K::M('article/article')->detail($article_id)) {
            $this->error(404);
        }else if(!$cate = K::M('article/cate')->cate($detail['cat_id'])){
           $this->error(404);
        }else if (!$detail['audit']) {
            $this->err->add('内容审核中,不能查看！', 212);
        }else if($detail['ontime'] && $detail['ontime'] > __TIME){
            $this->err->add('文章还未发布，暂时不可访问', 212);
        }else{
            K::M('article/article')->update_count($article_id, 'views', 1);
            if($detail['linkurl']){
                header("Location:".$detail['linkurl']);
                exit;
            }
			$filter['article_id'] = "<>:".$detail['article_id'];
            $filter['cat_id'] = $detail['cat_id'];
            $this->pagedata['items'] = K::M('article/article')->items($filter, null, 1, 5);
			$this->pagedata['detail'] = $detail;
			$this->pagedata['content'] = K::M('article/content')->detail($detail['article_id']);
            $pager = array();
			$pager['backurl'] = $this->mklink('mobile/article:items',array('cat_id'=>$detail['cat_id']));
			$this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/article/detail.html'; 
        }
    }

}