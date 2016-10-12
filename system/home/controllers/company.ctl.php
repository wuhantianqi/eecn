<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: company.ctl.php 10340 2015-05-20 06:07:09Z maoge $
 */

class Ctl_Company extends Ctl
{

    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $system->request['uri'];
        if(preg_match('/company(-index)?-(\d+).html/i', $uri, $match)){
            $system->request['act'] = 'detail';
            $system->request['args'] = array($match[2]);
        }
    }

    public function index($company_id)
    {
        $this->detail($company_id);
    }

    public function detail($company_id)
    {
        $company = $this->check_company($company_id);
        $company['desc'] = K::M('content/html')->text($company['info']);
        $this->pagedata['company'] = $company;
		$comment_list = K::M('company/comment')->items(array('company_id'=>$company_id,'audit'=>'1'),null, 1, 4);
		
		$uids = array();
		foreach($comment_list as $k => $v){
			$uids[$v['uid']] = $v['uid'];
		}
		if($uids){
			$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
		}
        // 案例效果图
        $filter =array(
            'company_id'=>50,
            );
        $orderby = array('likes'=>'DESC');
        $page =1;
        $limit = 1;
        $items = K::M('case/case')->items($filter, $orderby, $page, $limit, $count);
        $lastphotos = array();
        foreach ($items as $k => $val) {
            if ($val['lastphotos']) {
                $lastphotos[] = $val['lastphotos'];
                $items[$k]['lastphotos'] = explode(',', $val['lastphotos']);
            }
        }
        if (!empty($lastphotos)) {
            $lastphotos = join(',', $lastphotos);
            $this->pagedata['photos'] = K::M('case/photo')->items_by_ids($lastphotos);
        }
        $this->pagedata['items'] = $items;


		$this->pagedata['comment_list'] = $comment_list;        
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/index.html';
    }

    public function about($company_id)
    {
        $company = $this->check_company($company_id);
        $this->pagedata['photo_list'] = K::M('company/photo')->items_by_company($company_id);
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/about.html';
    }

    public function youhui($company_id, $page=1)
    {
        $company = $this->check_company($company_id);
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
        $pager['count'] = $count = 0;
        $filter['company_id'] = $company_id;
        $filter['audit'] = 1;
        if($items = K::M('company/youhui')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($company_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/youhui.html';
    }

    public function news($company_id, $page=1)
    {
        $company = $this->check_company($company_id);
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
        $pager['count'] = $count = 0;
        if($items = K::M('company/news')->items_by_company($company_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($company_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->seo->set_company($company); 
        $this->seo->init('company');  
        $this->tmpl = 'company/news.html';
    }

    public function cases($company_id, $page=1)
    {
        $company = $this->check_company($company_id);
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 12;
        $pager['count'] = $count = 0;
        $filter = array('audit'=>1, 'closed'=>0);
        $filter['company_id'] = $company_id;
        if($items = K::M('case/case')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($company_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/cases.html';
    }

    public function site($company_id, $page=1)
    {
        $company = $this->check_company($company_id);
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
        $pager['count'] = $count = 0;
        $filter = array('audit'=>1);
        $filter['company_id'] = $company_id;     
        if($items = K::M('home/site')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($company_id, '{page}')));
            $this->pagedata['items'] = $items;
            $this->pagedata['pager'] = $pager;
        }
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/site.html';
    }

    public function team($company_id, $page=1)
    {
        $company = $this->check_company($company_id);
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
        $pager['count'] = $count = 0;
        $filter = array('company_id'=>$company_id, 'closed'=>0);
        if($items = K::M('designer/designer')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($company_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/team.html';
    }

    public function comment($company_id, $page=1)
    {
        $company = $this->check_company($company_id);
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
        $pager['count'] = $count = 0;
        if($items = K::M('company/comment')->items_by_company($company_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($company_id, '{page}')));
            $uids = array();
            foreach($items as $v){
                $uids[$v['uid']] = $v['uid'];
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $this->pagedata['items'] = $items;
        }
		
		$access = $this->system->config->get('access');
		$this->pagedata['comment_yz'] = $access['verifycode']['comment'];
        $this->seo->set_company($company);
        $this->seo->init('company');
        $this->tmpl = 'company/comment.html';
    }

    public function savecomment($company_id=null)
    {
        $this->check_login();
        if(!($company_id = (int)$company_id) && !($company_id = (int)$this->GP('company_id'))){
            $this->error(404);
        }
        $company = $this->check_company($company_id);
        $allow_comment = K::M('member/group')->check_priv($this->MEMBER['group_id'], 'allow_comment');
        if($allow_comment < 0){
            $this->err->add('您是【'.$this->MEMBER['group_name'].'】没有权限发表点评', 333);
        }else if(!$data = $this->checksubmit('data')){
            $this->err->add('非法的数据提交', 211);
        }else if(!$data = $this->check_fields($data, 'score1,score2,score3,score4,score5,content')){
            $this->err->add('非法的数据提交', 212);
        }else{
			$verifycode_success = true;
			$access = $this->system->config->get('access');
			if($access['verifycode']['comment']){
				if(!$verifycode = $this->GP('verifycode')){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}else if(!K::M('magic/verify')->check($verifycode)){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}
			}
			if($verifycode_success){
				$data['city_id'] = $company['city_id'];
				$data['company_id'] = $company_id;
				$data['uid'] = $this->uid;
				$data['audit'] = $allow_comment;
				if($comment_id = K::M('company/comment')->create($data)){
					K::M('company/comment')->comment_count($company_id);
					K::M('company/comment')->comment($data);
					$this->err->add('发表点评成功');
				}
			}
        }
    }

}