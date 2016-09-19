<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Designer extends Ctl_Mobile
{

    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/designer-([\d]+).html/i', $uri, $match)){
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
        $group_list = K::M('member/group')->options('designer');
        $order_list = array(0=>'默认排序',1=>'热门排序', 2=>'口碑排序', 3=>'最新排序');
        $filter['city_id'] = $this->request['city_id'];
        if($area_id){
            $filter['area_id'] = $area_id;
        }
        if($group_id){
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
        $filter['audit'] = 1;
        $filter['closed'] = 0;
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        if($items = K::M('designer/designer')->items($filter, $orderby, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/designer:items', array($area_id, $group_id, $order,'{page}'), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['area_list'] = $this->request['area_list'];
        $this->pagedata['group_list'] = $group_list;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/designer/items.html';
    }

	public function detail($uid)
	{
		$designer = $this->check_designer($uid);
        if($designer['company_id']){
            $this->pagedata['company'] = K::M('company/company')->detail($detail['company_id']);
        }
        $pager = array('type'=>'about');
		$pager['backurl'] = $this->mklink('mobile/designer');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/designer/detail.html';
	}

	public function cases($uid, $page=1)
	{
		$designer = $this->check_designer($uid);
        $pager = array('type'=>'cases');
        $pager['page'] = max((int)$page, 1);
        $pager['limit'] = $limit = 5;
        if($items = K::M('case/case')->items(array('uid'=>$uid, 'audit'=>1, 'closed'=>0), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/designer:cases', array($uid, '{page}')));
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/designer',array('designer_id'=>$designer_id));
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/designer/cases.html';
	}

	public function article($uid, $page=1)
	{
		$designer = $this->check_designer($uid);
        $pager = array('type'=>'article');
        $pager['page'] = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        if($items = K::M('designer/article')->items(array('uid'=>$uid, 'audit'=>1), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(array('mobile/designer:article'), array($uid, '{page}')));
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/designer',array($designer_id));
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/designer/article.html';
	}

    public function articleinfo($article_id)
    {
        if(!$article_id = (int)$article_id){
            $this->error(404);
        }else if(!$detail = K::M('designer/article')->detail($article_id)){
            $this->error(404);
        }
        $designer = $this->check_designer($detail['uid']);
        K::M('designer/article')->update_count($article_id, 'views');
        $pager = array('type'=>'article');
        $pager['backurl'] = $this->mklink('mobile/designer',  array($detail['uid']));
        $this->pagedata['pager'] = $pager;
        $this->pagedata['detail'] = $detail;
        $this->tmpl = 'mobile/designer/articleinfo.html';     
    }

	public function yuyue($uid=null)
	{
		if(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('没有您要的数据', 211);
        }else if(!$designer = K::M('designer/designer')->detail($uid)){
            $this->err->add('没有您要的数据', 212);
        }else if(empty($designer['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else if($data = $this->checksubmit('data')){
            $data['designer_id'] = $uid;
            $data['company_id'] = $designer['company_id'];
            $data['uid'] = (int)$this->uid;
            $data['content'] = "预约设计师:".$detail['uname'].',来自移动端';
            $data['city_id'] =  $designer['city_id'];
            if($yuyue_id = K::M('designer/yuyue')->create($data)){
                K::M('designer/designer')->update_count($uid, 'yuyue_num');
                $smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'designer'=>$detail['realname']);
                K::M('sms/sms')->send($data['mobile'], 'designer_yuyue', $smsdata);
                if($company_id = $detail['company_id']){
                    if($company = K::M('company/company')->detail($company_id)){
                        $company['member'] = $detail;
                        K::M('sms/sms')->company('designer_tongzhi', $smsdata);
                        K::M('helper/mail')->sendcompany($company, 'designer_yuyue', $maildata);
                    }
                }else{
                    if($detail['verify_mobile'] && K::M('verify/check')->mobile($detail['mobile'])){
                        K::M('sms/sms')->send($detail['mobile'], 'designer_tongzhi', $smsdata);
                    }
                    K::M('helper/mail')->sendmail($detail['mail'], 'designer_yuyue', $maildata);
                }
                $this->err->add('预约设计师成功');
                $this->err->set_data('forward', $this->mklink('mobile/designer:detail', array('uid'=>$uid)));
            }
        }else{
			$pager['tender_hide'] = 1;
            $pager['backurl'] = $this->mklink('mobile/designer',array('designer_id'=>$designer_id));
            $this->pagedata['pager'] = $pager;
            $this->pagedata['designer'] = $designer;
            $this->tmpl = 'mobile/designer/yuyue.html'; 
        }
	}

}
