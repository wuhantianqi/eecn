<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Msite extends Ctl_Weixin
{

    public function __construct(&$system)
    {
        parent::__construct($system);
        if(preg_match('/msite-(\d+).html/i', $this->request['uri'], $m)){
            $this->request['act'] = 'index';
            $this->request['args'] = array($m[1]);
        }
    }
    
    public function index($wx_id)
    {
        if(!$wx_id = (int)$wx_id){
            $this->error(404);
        }else if(!$msite = K::M('weixin/msite')->detail($wx_id)){
            $this->error(404);
        }else{
            $this->pagedata['MSITE'] = $msite;
            $this->pagedata['cate_list'] = K::M('weixin/msite/cate')->items_by_weixin($msite['wx_id']);
            $this->pagedata['banner_list'] = K::M('weixin/msite/banner')->items_by_weixin($msite['wx_id']);
            $tpl = $msite['tmpl_index'] ? $msite['tmpl_index'] : 'V1';
            $this->tmpl = 'weixin/msite/tmpl/index/'.$tpl.'/index.html';       
        }
    }

    public function article($article_id)
    {
        if(!$article_id = (int)$article_id){
            $this->error(404);
        }else if(!$detail = K::M('weixin/msite/article')->detail($article_id)){
            $this->error(404);
        }else if(!$msite = K::M('weixin/msite')->detail($detail['wx_id'])){
            $this->error(404);
        }else if($detail['link'] && strpos($detail['link'],'http') !== false){
            K::M('weixin/msite/article')->update_count($article_id, 'views');
            header("Location:".$detail['link']);
            exit();
        }else{
            K::M('weixin/msite/article')->update_count($article_id, 'views');
            $this->pagedata['MSITE'] = $msite;
            $this->pagedata['detail'] = $detail;
            $tpl = $msite['tmpl_detail'] ? $msite['tmpl_detail'] : 'V1';
            $this->tmpl = 'weixin/msite/tmpl/detail/'.$tpl.'/detail.html';;
        }
    }

    public function cate($cat_id, $page=1)
    {
        if(!$cat_id = (int)$cat_id){
            $this->error(404);
        }else if(!$cate = K::M('weixin/msite/cate')->detail($cat_id)){
            $this->error(404);
        }else if(!$msite = K::M('weixin/msite')->detail($cate['wx_id'])){
            $this->error(404);
        }else if(K::M('verify/check')->url($cate['link'])){
            header("Location:".$cate['link']);
            exit();
        }else{
            $pager = array();
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 10;
            $pager['count'] = $count = 0;
            if($items = K::M('weixin/msite/article')->items(array('cat_id'=>$cat_id), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($this->mklink(null, array($cat_id, '{page}')));
                $this->pagedata['items'] = $items;
            }
            $tpl = 'weixin/msite/list/'.$msite['tmpl_lists'].'/lists.html';
            $output = K::M('system/frontend');
            if(!$output->templateExists($tpl)){
                $tpl = '/weixin/msite/tmpl/lists/V1/lists.html';
            }
            $this->pagedata['MSITE'] = $msite;
            $this->pagedata['cate'] = $cate;
            $tpl = $msite['tmpl_lists'] ? $msite['tmpl_lists'] : 'V1';
            $this->tmpl = 'weixin/msite/tmpl/lists/'.$tpl.'/lists.html';
        }
    }

}