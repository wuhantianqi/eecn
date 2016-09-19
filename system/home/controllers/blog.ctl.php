<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Blog extends Ctl
{
	
	//public $_call = 'index';
	//private $_action = array('items', 'about', 'attention', 'cases', 'article', 'article_info','comment' ,'check_designer');
   

    public function __construct(&$system)
    {
    	parent::__construct($system);
    	if(preg_match('/blog-(\d+)(\.html)?/i', $this->request['uri'], $m)){
    		$this->request['act'] = 'detail';
    		$this->request['args'] = array($m[1]);
    	}
    }

	public function detail($uid)
	{
		$designer = $this->check_designer($uid);
		K::M('designer/designer')->update_count($uid, 'views', 1);
        if($designer['company_id']){
	       $this->pagedata['company'] = K::M('company/company')->detail($designer['company_id']);
        }
		$comment_info = K::M("designer/comment")->items(array('designer_id'=>$uid),array('comment_id'=>'desc'),1,3);
		foreach($comment_info as $k => $v){
			$uids[$v['uid']] = $v['uid'];
		}
		if(!empty($uids)){
			$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
		}
		$this->pagedata['comment_list'] = $comment_info;
		$this->pagedata['designer'] = $designer;
		$this->tmpl = 'blog/detail.html';
	}

	public function attention($uid)
	{	
		$designer = $this->check_designer($uid);
		if (!$designer['audit']) {
            $this->err->add('您关注的内容还在审核中，暂不可评论', 213);
        }else {
            K::M('designer/designer')->update($uid,array('attention_num'=>$detail['attention_num']+1));
            $this->err->add('关注成功！');
        }
	}

	public function about($uid)
	{
		$designer = $this->check_designer($uid);
		$this->pagedata['company'] = K::M('company/company')->detail($designer['company_id']);
		$this->tmpl = 'blog/about.html';
	}

	public function cases($uid, $page=1)
	{
		$designer = $this->check_designer($uid);
        $pager = $fitler = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
        $pager['count'] = $count = 0;
        $filter = array('uid'=>$uid, 'closed'=>0, 'audit'=>1);
        if($items = K::M('case/case')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('blog:cases', array($uid, '{page}')));
            $this->pagedata['items'] = $items;
        }
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'blog/cases.html';

	}

	public function article($uid, $page=1)
	{
		$designer = $this->check_designer($uid);
        $pager = $fitler = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
		$pager['count'] = $count = 0;
        $filter = array('audit'=>1,'uid'=>$uid);
		if($items = K::M("designer/article")->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('blog:article', array($uid,'{page}')));
            $this->pagedata['items'] = $items;
        }		
		$this->pagedata['pager'] = $pager;		
		$this->tmpl = 'blog/article.html';
	}

    public function article_info($article_id)
    {
        $this->showinfo($article_id);
    }

	public function showinfo($article_id)
	{
		if(!($article_id = (int)$article_id) && !($article_id = $this->GP('article_id'))){
            $this->error(404);
        }else if(!$detail = K::M('designer/article')->detail($article_id)){
            $this->error(404);
        }
        $designer = $this->check_designer($detail['uid']);
        K::M('designer/article')->update_count($article_id, 'views');
        $pager['prev'] = K::M('designer/article')->item_prev($article_id, $detail['uid']);
        $pager['next'] = K::M('designer/article')->item_next($article_id, $detail['uid']);
        $this->pagedata['detail'] = $detail;
        $this->tmpl = 'blog/showinfo.html';
	}

	public function comments($uid, $page=1)
	{
		$designer = $this->check_designer($uid);
        $pager = $fitler = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter = array('designer_id'=>$uid, 'closed'=>0);
		$order = array('comment_id'=>'desc');
		if($items = K::M("designer/comment")->items($filter, $order, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('blog:comment',array($uid, '{page}')));
            $this->pagedata['items'] = $items;
            $uids = array();
            foreach($items as $k => $v){
                $uids[$v['uid']] = $v['uid'];
            }
            if(!empty($uids)){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        }
		$this->pagedata['comment_info'] = $items;	
		$this->pagedata['pager'] = $pager;		
		$this->tmpl = 'blog/comments.html';
	}

	public function comment($uid)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能评论', 101);
		}elseif (($audit = K::M('member/group')->check_priv($this->MEMBER['group_id'],'allow_score')) == -1) {
			$this->err->add('很抱歉您所在的用户组没有权限操作', 201);
		}elseif(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('没有您要的数据', 211);
        }else if(!$detail = K::M('designer/designer')->detail($uid)){
            $this->err->add('没有您要的数据', 212);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else{
			if(!$data = $this->GP('data')){
				$this->err->add('非法的数据提交', 201);
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
					$data['uid'] = $this->uid;
					$data['designer_id'] = $uid;
					$data['city_id'] = $this->request['city_id'];
					$data['audit'] = $audit;
					if($comment = K::M('designer/comment')->create($data)){
						K::M('designer/comment')->comment($data);
						$this->err->add('评论成功！');
					}
				}
			}
		}
	}

	protected function check_designer($uid)
    {
        if(!$uid = (int)$uid){
            $this->error(404);
        }else if(!$designer = K::M('designer/designer')->detail($uid)){
            $this->error(404);
        }
        $this->pagedata['designer'] = $designer;
        $seo = array('designer_name'=>$designer['name'], 'designer_school'=>$designer['school'], 'designer_slogan'=>$designer['slogan'], 'designer_desc'=>'');
        $seo['designer_desc'] = K::M('content/text')->substr(K::M('content/html')->text($designer['about'], true), 0, 200);
        $this->seo->init('designer_detail', $seo);
        return $designer;
    }
}