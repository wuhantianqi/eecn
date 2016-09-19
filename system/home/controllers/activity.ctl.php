<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Activity extends Ctl
{
    
    public function index($cat_id = 0, $page = 1)
    {
        $this->items();
    }
	public function items($cat_id = 0, $page = 1)
	{
		$filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 6;
        $pager['count'] = $count = 0;
        $filter['city_id'] = $this->request['city_id'];
		$filter['audit'] = 1;
         // 优惠信息
        if ($items = K::M('company/youhui')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['itemses'] = $items;
        } 
		$cat_id = empty($cat_id) ? 0 : (int) $cat_id;
        $cate_list = K::M("activity/cate")->fetch_all();
        if(!$cate = $cate_list[$cat_id]){
            foreach($cate_list as $k=>$v){
                $cate = $v;
                $cat_id = $k;
                break;
            }
        }
        $pager['cat_id'] = $cat_id;
        if ($cat_id = (int) $cat_id) {
            $filter['cate_id'] = $cat_id;
        }
        // 优惠活动
        if ($items = K::M('activity/activity')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('activity:items', array($cat_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
       
        $this->pagedata['cate'] = $cate;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cate_list'] = $cate_list = K::M("activity/cate")->fetch_all();
        $this->seo->init('activity', array('cate_name' => $cate['title'],
            'cate_seo_title' => $cate['seo_title'],
            'cate_seo_keywords' => $cate['seo_keywords'],
            'cate_seo_description' => $cate['seo_description'],
        ));
        $this->tmpl = 'activity/items.html';
	}

	public function detail($activity_id)
    {
        if(!($activity_id = (int) $activity_id) && !($activity_id = (int)$this->GP('activity_id'))) {
            $this->err->add('未指定活动的内容ID', 211);
        }else if(!$detail = K::M('activity/activity')->detail($activity_id)) {
            $this->err->add('未找到该活动或者活动已删除', 212);
        }else {
            $this->pagedata['detail'] = $detail;
            $detail_lanmu = K::M('activity/lanmu')->items(array('activity_id' => $activity_id));
			$num = 2;
			foreach($detail_lanmu as $k => $v){
				$detail_lanmu[$k]['num'] = $num+1;
				$num++;
			}
			$this->pagedata['sign'] = K::M('activity/sign')->items(array('activity_id'=>$activity_id),array('dateline'=>'desc'),1,10);
			$this->pagedata['detail_lanmu'] = $detail_lanmu;
            $seo = array('title' => $detail['title'], 'cate_name'=>$cate['title'], 'intro'=>'');
            $seo['intro'] = K::M('content/text')->substr(K::M('content/html')->text($detail['intro'], true), 0, 200);
            $this->seo->init('activity_detail', $seo);
            if($seo_title = $detail['seo_title']){
                $this->seo->set_title($seo_title);
            }
            if($seo_description = $detail['seo_description']){
                $this->seo->set_description($seo_description);
            }
            if($seo_keywords = $detail['seo_keywords']){
                $this->seo->set_keywords($seo_keywords);
            }
			$access = $this->system->config->get('access');
			$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
            if(!empty($detail['tmpl'])){
                $this->tmpl =$detail['tmpl'];
            }else{
                $this->tmpl = 'activity/detail.html';
            }
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
					$verifycode_success = true;
					$access = $this->system->config->get('access');
					if($access['verifycode']['yuyue']){
						if(!$verifycode = $this->GP('verifycode')){
							$verifycode_success = false;
							$this->err->add('验证码不正确', 212);
						}else if(!K::M('magic/verify')->check($verifycode)){
							$verifycode_success = false;
							$this->err->add('验证码不正确', 212);
						}
					}
					if($verifycode_success){
						$data['city_id'] = $this->request['city_id'];
						$data['activity_id'] = $activity_id;
						$data['uid'] = $this->uid;
						if($sign_id = K::M('activity/sign')->create($data)){
							K::M('activity/activity')->update($activity_id,array('sign_num'=>$detail['sign_num']+1));
							$this->err->add('报名成功！');
						}
					}
				}
			}else{
				$access = $this->system->config->get('access');
				$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
				$this->pagedata['activity_id'] = $activity_id;
				$this->pagedata['detail'] = $detail;
				$this->tmpl = 'activity/yuyue.html';
			}
		}	
    }
}