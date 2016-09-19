<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Shop_News extends Ctl_Ucenter
{
    protected $_allow_fields = 'title,from,content';
    public function index()
    {
        $shop = $this->ucenter_shop();
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('shop_id'=>$shop['shop_id'], 'closed'=>0);
        if($items = K::M('shop/news')->items($filter, null, $page, $limit, $count)){
            $pgaer['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/shop/news/items.html';
    }

    public function create()
    {
        $shop = $this->ucenter_shop();
        if($data = $this->checksubmit('data')){
            $allow_news = K::M('member/group')->check_priv($shop['group_id'], 'allow_news');
            if($allow_news < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限添加店铺资讯', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
				
                $data['city_id'] = $shop['city_id'];
                $data['shop_id'] = $shop['shop_id'];
                $data['audit'] = $allow_news;
                if($news_id = K::M('shop/news')->create($data)){                    
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/shop/news:index'));
                }
            }
        }else{
            $this->pagedata['pager'] = $pager;            
            $this->tmpl = 'ucenter/shop/news/create.html';
        }
    }

    public function edit($news_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($news_id = (int)$news_id) && !($news_id = (int)$this->GP('news_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$detail = K::M('shop/news')->detail($news_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($shop['shop_id'] != $detail['shop_id']){
             $this->err->add('您没有权限修改该内容', 213);
        }else if($data = $this->checksubmit('data')){
            if(K::M('member/group')->check_priv($shop['group_id'], 'allow_news') < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限修改店铺资讯', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 201);
            }else if(K::M('shop/news')->update($news_id, $data)){
                $this->err->add('修改内容成功');
            } 
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/shop/news/edit.html';
        }
    }

    public function delete($news_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($news_id = (int)$news_id) && !($news_id = $this->GP('news_id'))){
            $this->err->add('未指要删除的资讯', 211);
        }else if(!$detail = K::M('shop/news')->detail($news_id)){
            $this->err->add('你要删除的资讯不存或已经删除', 212);
        }else if($shop['shop_id'] != $detail['shop_id']){
            $this->err->add('您没有权限删除该资讯', 213);
        }else{
            K::M('shop/news')->delete($news_id);
            $this->err->add('删除资讯成功');
        }        
    }

}