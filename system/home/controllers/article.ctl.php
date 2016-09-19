<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Article extends Ctl 
{
    
    public function index()
    {   
        $this->seo->init('article');
        $this->tmpl = 'article/index.html';
    }

    public function items($cat_id, $page=1)
    {
        $sokw = trim($this->GP('kw'));
        if(!($cat_id = (int)$cat_id) && empty($sokw)){
            $this->error(404);
        }
        $pager = $filter = array();
        $filter = array('audit'=>1,'hidden'=>'0', 'closed'=>0, 'ontime'=>'>:'.__TIME);
        $filter['city_id'] = array(0, $this->request['city_id']);
        if($cat_id){
            if(!$cate = K::M('article/cate')->cate($cat_id)){
                $this->error(404);
            }else if('article' != $cate['from']){
                $this->error(404);
            }
            $top_cate = $cate;
            $filter['cat_id'] = $cat_id;
            if($cate['level'] == 3){
                $this->pagedata['childrens'] = K::M('article/cate')->childrens($cate['parent_id']);
            }else{
                if($cat_ids = K::M('article/cate')->children_ids($cat_id)){
                    $filter['cat_id'] = explode(',', $cat_ids);
                }
                if(!$childrens = K::M('article/cate')->childrens($cat_id)){
                    if($cate['level']>1){
                        $childrens = K::M('article/cate')->childrens($cate['parent_id']);
                        $top_cate = K::M('article/cate')->cate($cate['parent_id']);
                    }                  
                }
                $this->pagedata['childrens'] = $childrens;                
            }
            $this->pagedata['top_cate'] = $top_cate;
            $this->pagedata['cate'] = $cate;
        }
        if($sokw){
            $pager['sokw'] = $sokw = htmlspecialchars($sokw);
            $filter['title'] = "LIKE:%{$sokw}%";
        }
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        if($items = K::M('article/article')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page,$this->mklink(null, array($cat_id, '{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $seo = array('cate_name'=>'','cate_seo_title'=>'', 'cate_seo_keywords'=>'', 'cate_seo_description'=>'', 'page'=>(($page > 1) ? $page : ''));
        if($cate){
            $seo['cate_title'] = $seo['cate_name'] = $cate['title'];
            $seo['cate_seo_title'] = $cate['seo_title'];
            $seo['cate_seo_keywords'] = $cate['seo_keywords'];
            $seo['cate_seo_description'] = $cate['seo_description'];
        }else if($sokw){
            $seo['cate_name'] = $sokw;
        }
        $this->seo->init('article_items', $seo);
        $this->tmpl = 'article/items.html'; 
    }

    public function detail($article_id, $page=1)
    {
        if(!$article_id = (int)$article_id){
            $this->error(404);
        }else if(!$detail = K::M('article/article')->detail($article_id)){
            $this->error(404);
        }else if(!$cate = K::M('article/cate')->cate($detail['cat_id'])){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add('文章审核中，暂时不可访问', 211);
        }else if($detail['ontime'] && $detail['ontime'] > __TIME){
            $this->err->add('文章还未发布，暂时不可访问', 212);
        }else{
            K::M('article/article')->update_count($article_id, 'views', 1);
            if($detail['linkurl']){
                header("Location:".$detail['linkurl']);
                exit;
            }else if($detail['from'] != 'article'){
                $this->error(404);
            }else if($detail['city_id'] && ($detail['city_id'] != $this->request['city_id'])){
                $cfg = $this->system->config->get('site');
                if($cfg['multi_city'] && ($city = K::M('data/city')->city($detail['city_id']))){
                    $this->system->response_code(301);
                    header("Location:".$this->mklink("article:detail", array($article_id), null, $detail['city_id']));
                    exit();
                }
            }
            if($page == 'all'){
                $curr_content = $detail['content'];
            }else{
                $page = max((int)$page, 1);
                $offset = $page - 1;
                if(!$curr_content = $detail['content_list'][$offset]){
                    $this->error(404);
                }
                $detail['curr_content'] = $curr_content;
            }
            $pager = array('page'=>$page);
            $pager['count'] = $detail['content_count'];
            $pager['prev'] = K::M('article/article')->prev_item($article_id);
            $pager['next'] = K::M('article/article')->next_item($article_id);
            if($comment_list = K::M('article/comment')->items(array('article_id'=>$article_id, 'closed'=>0), array('comment_id'=>'DESC'), 1, 5)){
                $uids = array();
                foreach($comment_list as $k=>$v){
                    $uids[$v['uid']] = $v['uid'];
                }
                if($uids){
                    $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
                }
                $this->pagedata['comment_list'] = $comment_list;
            }
			$access = $this->system->config->get('access');
			$this->pagedata['comment_yz'] = $access['verifycode']['comment'];
            $this->pagedata['pager'] = $pager;
            $this->pagedata['cate'] = $cate;
            $this->pagedata['detail'] = $detail;
            $seo = array('title'=>$detail['title'], 'article_desc'=>$detail['desc'], 'cate_title'=>$cate['title'], 'cate_name'=>$cate['title'], 'page'=>($page > 1) ? $page : '');
            $this->seo->init('article_detail', $seo);
            if($seo_title = $detail['seo_title']){
                $this->seo->set_title($seo_title);
            }
            if($seo_description = $detail['seo_description']){
                $this->seo->set_description($seo_description);
            }
            if($seo_keywords = $detail['seo_keywords']){
                $this->seo->set_keywords($seo_keywords);
            }
            $this->tmpl = 'article/detail.html';
        }        
    }

    public function  comments($article_id, $page=1)
    {
		
    }

    public function savecomment()
    {
        $this->check_login();
        if (!$article_id = (int)$this->GP('article_id')){
            $this->error(404);
        }else if (!$detail = K::M('article/article')->detail($article_id)) {
            $this->err->add('文章不存在或已经删除', 212);
        }else if (!$content = $this->GP('content')) {
            $this->err->add('至少说点什么吧！', 212);
        } else {
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
				$data = array(
					'article_id' => $article_id,
					'uid' => $this->uid,
					'content' => $content
				);
				if(K::M('article/comment')->create($data)){
					K::M('article/article')->update_count($article_id, 'comments', 1);
					$this->err->add('评论发表成功！');
				}
			}
        }        
    }
}
