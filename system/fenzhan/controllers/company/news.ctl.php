<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: news.ctl.php 6077 2014-08-13 13:48:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Company_News extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
        }
        $companyIds = array();
        $filter['city_id'] = CITY_ID;
        if($items = K::M('company/news')->items($filter, null, $page, $limit, $count)){
            foreach($items as $k=>$v){
            
               if(!empty($v['company_id']))  $companyIds[$v['company_id']] = $v['company_id'];
                $items[$k]['clientip'] = $v['clientip'].'('. K::M("misc/location")->location($v['clientip']) .')';
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($companyIds);
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'fenzhan:company/news/items.html';
    }

    public function so()
    {
        $this->tmpl = 'fenzhan:company/news/so.html';
    }

    
    public function edit($news_id=null)
    {
        if(!($news_id = (int)$news_id) && !($news_id = $this->GP('news_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('company/news')->detail($news_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                unset($data['company_id'], $data['city_id']);
                if(K::M('company/news')->update($news_id, $data)){
                    $this->err->add('修改内容成功');
                    $this->err->set_data('forward', '?company/news-index.html');
                }  
            } 
        }else{
            if($company_id = $detail['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'fenzhan:company/news/edit.html';
        }
    }

    public function doaudit($news_id=null)
    {
        if($news_id = (int)$news_id){
            if(K::M('company/news')->batch($news_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('news_id')){
            if(K::M('company/news')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($news_id=null)
    {
        if($news_id = (int)$news_id){
            if($news = K::M('company/news')->detail($news_id)){
                if(!$this->check_city($news['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('company/news')->delete($news_id)){
                    K::M('company/news')->news_count($news['company_id']);
                    $this->err->add('删除公司新闻成功');
                }
            }
        }else if($ids = $this->GP('news_id')){
            if($items = K::M('company/news')->items_by_ids($ids)){
                $aids = $company_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['news_id']] = $v['news_id'];
                    $company_ids[$v['company_id']] = $v['company_id'];
                }
                if($aids && K::M('company/news')->delete($aids)){
                    K::M('company/news')->news_count($company_ids);
                    $this->err->add('批量删除公司新闻成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}