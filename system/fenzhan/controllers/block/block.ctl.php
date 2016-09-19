<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: block.ctl.php 6074 2014-08-12 17:10:33Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Block_Block extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = $page = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($items = K::M('block/block')->items($filter, null, $page, $limit)){
        	$pager['count'] = count($items);
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
            $this->pagedata['page_list'] = K::M('block/page')->fetch_all();           
        }
        $this->pagedata['from_list'] = K::M('block/block')->from_list();
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'fenzhan:block/block/items.html';
    }

    public function detail($block_id, $page=1)
    {
        if(!$block_id = (int)$block_id){
            $this->err->add('未定要管理的推荐位ID', 211);
        }else if(!$block = K::M('block/block')->detail($block_id)){
            $this->err->add('推荐位不存在或已经删除', 212);
        }else{
            $pager['page'] = $page = max(intval($page), 1);
            $pager['limit'] = $limit = 50;            
            $this->pagedata['block'] = $block;
        	if($items = K::M('block/item')->items_by_block($block_id,CITY_ID, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($block_id,'{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['pager'] = $pager;
        	$this->tmpl = 'fenzhan:block/block/detail.html';
        }
    }
}