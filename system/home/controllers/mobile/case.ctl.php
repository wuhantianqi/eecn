<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Case extends Ctl_Mobile
{

    public function index()
    {
        $this->items();
    }

    public function items($page=1)
    {
        $pager = $filter = $attrs = $attr_ids = $attr_vids = $attr_value_ids= array();
        $attr_values = K::M('data/attr')->attrs_by_from('zx:case', true);
        $uri = $this->request['uri'];
        if(preg_match('/case-items-([\d\-]+).html/i', $uri, $m)){
            if($m[1]){
                if($attr_vids = explode('-', trim($m[1], '-'))){
                    $page = array_pop($attr_vids);
                }
            }
        }
        foreach($attr_values as $k=>$v){
            $attr_value_ids[$k] = 0;
            foreach($attr_vids as $vv){
                if($v['values'][$vv]){
                    $attr_value_ids[$k] = $vv;
                    $attr_ids[$k] = $vv;
                    $attrs[$k] = $v['values'][$vv];
                }
            }
        }
        $attr_vids = $attr_ids;    
        foreach($attr_values as $k=>$v){
            $vids = $attr_value_ids;
            $vids[$k] = 0;
            $vids['page'] = 1;
            $v['link'] = $this->mklink('mobile/case:items', $vids);
            foreach($v['values'] as $kk=>$vv){
                $vids[$k] = $kk;
                $vv['link'] = $this->mklink('mobile/case:items', $vids);
                $v['values'][$kk] = $vv;
            }
            $attr_values[$k] = $v;
        }
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['city_id'] = array($this->request['city_id'], '0');
        $filter['closed'] = 0;
        $filter['audit'] = 1;
        if($attr_ids){
            $filter['attrs'] = $attr_ids;
        }
        if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $params['kw'] = $kw;
            $filter['title'] = "LIKE:%{$kw}%";            
        }
        if($items = K::M('case/case')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/case:items', array_merge((array)$attr_ids, array('page'=>'{page}')), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['attr_ids'] = $attr_ids;
        $this->pagedata['attr_values'] = $attr_values;
        $this->pagedata['attrs'] = $attrs;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/case/items.html';
    }

	public function detail($case_id)
	{
		if (!$case_id = (int) $case_id) {
            $this->error(404);
        }else if (!$case = K::M('case/case')->detail($case_id)) {
            $this->error(404);
        }elseif (!$case['audit']) {
           $this->err->add("内容审核中，暂不可访问", 211)->response();
        }
		$this->pagedata['photos'] = K::M('case/photo')->items_by_case($case_id, 1, 50);
		$this->pagedata['case'] = $case;
		$pager['backurl'] = $this->mklink('mobile/case');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/case/detail.html';
	}
}