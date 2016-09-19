<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Activity extends Ctl_Mobile 
{

	public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/activity-([\d]+)\.html/i', $uri, $match)){
            $system->request['act'] = 'detail';
            $system->request['args'] = array($match[1]);
        }      
    }

    public function index($page=1)
    {
		$filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 5;
        $filter['city_id'] = $this->request['city_id'];
		$filter['audit'] = 1;
        if ($items = K::M('activity/activity')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/activity:index', array('{page}')));
        }
		$pager['backurl'] = $this->mklink('mobile/index');
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/activity/items.html';
    }

    public function detail($activity_id)
    {
		if(!($activity_id = (int) $activity_id) && !($activity_id = (int)$this->GP('activity_id'))) {
            $this->err->add('未指定活动的内容ID', 211);
        }else if(!$detail = K::M('activity/activity')->detail($activity_id)) {
            $this->err->add('未找到该活动或者活动已删除', 212);
        }else {
            $this->pagedata['detail'] = $detail;
			$pager['backurl'] = $this->mklink('mobile/activity');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/activity/detail.html';
		}
	}

	public function yuyue($activity_id)
    {
        if(!($activity_id = (int) $activity_id) && !($activity_id = (int)$this->GP('activity_id'))) {
			$this->error(404);
		}else if(!$detail = K::M('activity/activity')->detail($activity_id)) {
			$this->error(404);
		}elseif (!$detail['audit']) {
			$this->err->add('该活动还在审核中，暂不可评论', 213);
		}else {
			if($data = $this->checksubmit('data')){
				$data['activity_id'] = $activity_id;
				if(!$data = $this->GP('data')){
					$this->err->add('非法的数据提交', 201);
				}else{
					$data['city_id'] = $isdata['city_id'] = $this->request['city_id'];
					$data['activity_id'] = $isdata['activity_id'] = $activity_id;
					$data['uid'] = $isdata['uid'] = $this->uid;
					$isdata['mobile'] = $data['mobile'];
					if($is_sign = K::M('activity/sign')->items($isdata)){
						$this->err->add('该用户已经报名完成',215);
					}elseif($sign_id = K::M('activity/sign')->create($data)){
						$this->err->add('优惠活动报名成功！');
					}
				}
			}else{
				
				$pager['tender_hide'] = 1;
				$access = $this->system->config->get('access');
				$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
				$this->pagedata['activity_id'] = $activity_id;
				$this->pagedata['detail'] = $detail;
				$pager['backurl'] = $this->mklink('mobile/activity',array('activity_id'=>$activity_id));
				$this->pagedata['pager'] = $pager;
				$this->tmpl = 'mobile/activity/yuyue.html';
			}
		}
	}
}