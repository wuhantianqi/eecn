<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: adv.ctl.php 6080 2014-08-13 15:20:01Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Adv_Adv extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['adv_id']){
                $filter['adv_id'] = $SO['adv_id'];
            }else{
                if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
                if($SO['key']){$filter['key'] = "LIKE:%".$SO['key']."%";}
                if(is_array($SO['dateline'])){
                    if($SO['dateline'][0] && $SO['dateline'][1]){
                        $a = strtotime($SO['dateline'][0]); 
                        $b = strtotime($SO['dateline'][1]);
                        $filter['dateline'] = $a."~".$b;
                    }
                }
                if(is_numeric($SO['audit'])){
                    $filter['audit'] = $SO['audit'] ? 1 : 0;
                }
            }
        }
        $filter['closed'] = '0';
        if($items = K::M('adv/adv')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $this->pagedata['theme_list'] = K::M('system/theme')->fetch_all();
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['from_list'] = K::M('adv/adv')->from_list();
        $this->tmpl = 'fenzhan:adv/adv/items.html';
    }

    public function so()
    {
        $this->tmpl = 'fenzhan:adv/adv/so.html';
    }

    public function detail($adv_id=null, $page=1)
    {
        if(!$adv_id = intval($adv_id)){
            $this->err->add('未指定广告位的ID', 211);
        }else if(!$detail = K::M('adv/adv')->adv($adv_id)){
            $this->err->add('你要管理的广告位不存在', 212);
        }else{

            $pager = array();
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 30;
            $pager['count'] = $count = 0;        	
            if($items = K::M('adv/item')->items(array('adv_id'=>$adv_id, 'closed'=>0, 'city_id'=>CITY_ID), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($adv_id, '{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['pager'] = $pager;
            $this->pagedata['detail'] = $detail;
        	$this->tmpl = 'fenzhan:adv/adv/detail.html';
        }
    }
}