<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Store extends Ctl_Mobile
{
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/store-([\d\-]+).html/i', $uri, $match)){
            $system->request['act'] = 'items';
            $system->request['args'] = explode('-', $match[1]);
        }      
    }

    public function index()
    {
        $this->items();
    }

    public function items($cat_id=0, $group_id=0, $order=0, $page=1)
    {
        $pager = $filter =$orderby = $cate_list = $params = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['cat_id'] = $cat_id = (int)$cat_id;
        $pager['group_id'] = $group_id = (int)$group_id;
        $pager['order'] = $order = (int)$order;
        if($cates = K::M('shop/cate')->fetch_all()){
            foreach($cates as $k=>$v){
                if(empty($v['parent_id'])){
                    $cate_list[$k] = $v;
                }
            }
        }
        $group_list = K::M('member/group')->options('shop');
        $order_list = array(0=>'默认排序',1=>'热门排序', 2=>'口碑排序', 3=>'最新排序');
        $filter['city_id'] = $this->request['city_id'];
        if($cat_id && $cate_list[$cat_id]){
            $filter['cat_id'] = $cat_id;
        }
        if($group_id && $group_list[$group_id]){
            $filter['group_id'] = $group_id;
        }
        switch ($order) {
            case 1:
                $orderby = array('views'=>'DESC');
                break;            
            case 2:
                $orderby = array('score'=>'DESC');
                break;
            case 3:
                $orderby = array('flushtime'=>'DESC');
                break;
            default:
                $orderby = array();
                break;
        }
        if($kw = $this->GP('kw')){
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $params['kw'] = $kw;
            $filter['name'] = "LIKE:%{$kw}%";
        }
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        if($items = K::M('shop/shop')->items($filter, $orderby, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/store', array($cat_id, $group_id, $order,'{page}'), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['cate_list'] = $cate_list;
        $this->pagedata['group_list'] = $group_list;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/store/items.html';
    }
}
