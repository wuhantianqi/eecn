<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Gz extends Ctl_Mobile
{
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/gz-([\d]+)\.html/i', $uri, $match)){
            $system->request['act'] = 'detail';
            $system->request['args'] = array($match[1]);
        }      
    }

    public function index()
    {
        $this->items();
    }

    public function items($area_id=0, $group_id=0, $order=0, $page=1)
    {
        $pager = $filter =$orderby = $params = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['area_id'] = $area_id = (int)$area_id;
        $pager['group_id'] = $group_id = (int)$group_id;
        $pager['order'] = $order = (int)$order;
        $area_list = $this->request['area_list'];
        $group_list = K::M('member/group')->options('gz');
        $order_list = array(0=>'默认排序',1=>'热门排序', 2=>'口碑排序', 3=>'最新排序');
        $filter['city_id'] = $this->request['city_id'];
		$filter['closed'] = 0;$filter['audit'] = 1;
        if($area_id && $area_list[$area_id]){
            $filter['area_id'] = $area_id;
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
        if($items = K::M('gz/gz')->items($filter, $orderby, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/gz:items', array($area_id, $group_id, $order,'{page}'), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['area_list'] = $area_list;
        $this->pagedata['group_list'] = $group_list;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/gz/items.html';
    }

	public function detail($uid)
	{
		$gz = $this->check_gz($uid);
		$this->pagedata['type'] = 'about';
		$pager['backurl'] = $this->mklink('mobile/gz:items');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/gz/detail.html';
	}

	public function cases($uid)
	{
		$gz = $this->check_gz($uid);
        $pager = array('type'=>'cases');
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 5;
        $pager['count'] = $count = 0;
        $filter = array('closed'=>'0','audit'=>'1','uid'=>$uid);
        if($items = K::M('case/case')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/gz:case', array($uid, '{page}')));
            $this->pagedata['items'] = $items;
        }
		$this->pagedata['type'] = 'cases';
    	$pager['backurl'] = $this->mklink('mobile/gz:',array('uid'=>$uid));
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/gz/cases.html';
	}

	public function yuyue($gz_id)
	{
		if(!($gz_id = (int)$gz_id) && !($gz_id = (int)$this->GP('gz_id'))){
            $this->err->add('没有您要的数据', 211);
        }else if(!$gz = K::M('gz/gz')->detail($gz_id)){
            $this->err->add('没有您要的数据', 212);
        }else if(empty($gz['audit'])){
            $this->err->add("工长审核中，不能预约", 211);
        }else if($data = $this->checksubmit('data')){
            $data['uid'] = (int)$this->uid;
            $data['gz_id'] = $gz_id;
            $data['content'] = "预约工长:".$detail['uname'].'，来自移动端';
            $data['city_id'] =  $gz['city_id'];
            if($yuyue_id = K::M('gz/yuyue')->create($data)){
                K::M('gz/gz')->update_count($uid,'yuyue_num');
                $this->err->add('预约工长成功');
                $this->err->set_data('forward', $this->mklink('mobile/gz', array($gz_id)));
            }
        }else{
            $pager['backurl'] = $this->mklink('mobile/gz',array('uid'=>$gz_id));
			$pager['tender_hide'] = 1;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['gz'] = $gz;
            $this->tmpl = 'mobile/gz/yuyue.html';  
		}
	}
}