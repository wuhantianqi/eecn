<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Designer_article extends Ctl
{
    public function items($page=1)
    {
		
		$filter = $pager = array();
		$pager['page'] = max(intval($page), 1);
		$pager['limit'] = $limit = 50;
		if(CITY_ID){
			$filter['city_id'] = CITY_ID;
		}
		if($items = K::M('designer/article')->items($filter, null, $page, $limit, $count)){
			$pager['count'] = $count;
			$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
		}
		$this->pagedata['items'] = $items;
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'admin:designer/article/items.html';
		
    }
    public function index($uid,$page=1)
    {
		if(!$detail = K::M('designer/designer')->detail($uid)){
            $this->err->add('该设计师不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
			$filter['uid'] = $uid;
			if(CITY_ID){
				$filter['city_id'] = CITY_ID;
			}
			if($items = K::M('designer/article')->items($filter, null, $page, $limit, $count)){
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
			}
			$this->pagedata['uid'] = $uid;
			$this->pagedata['items'] = $items;
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'admin:designer/article/items.html';
		}
    }

    public function create($uid)
    {
		if(!$uid && !$uid = $this->GP('uid')){
			$this->err->add('您没有指定设计师', 212);
		}elseif(!$detail = K::M('designer/designer')->detail($uid)){
            $this->err->add('该设计师不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else{
			if($data = $this->checksubmit('data')){
				$data['city_id'] = $detail['city_id'];
				$data['uid'] = $uid;
				if($article_id = K::M('designer/article')->create($data)){
					K::M('designer/designer')->blog_count($uid);
					$this->err->add('添加内容成功');
					
				} 
			}else{
				$this->pagedata['uid'] = $uid;
			    $this->tmpl = 'admin:designer/article/create.html';
			}
		}
    }

    public function edit($article_id=null)
    {
        if(!($article_id = (int)$article_id) && !($article_id = $this->GP('article_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('designer/article')->detail($article_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else if($data = $this->checksubmit('data')){
			unset($data['city_id'],$data['uid']);
            if(K::M('designer/article')->update($article_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:designer/article/edit.html';
        }
    }

    public function doaudit($article_id=null)
    {
        if($article_id = (int)$article_id){
            if(K::M('designer/article')->batch($article_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('article_id')){
            if(K::M('designer/article')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($article_id=null)
    {
		if($article_id = (int)$article_id){
            if($detail = K::M('designer/article')->detail($article_id)){
                if(!$this->check_city($detail['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('designer/article')->delete($article_id)){
                    K::M('designer/designer')->blog_count($detail['uid']);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('article_id')){
            if($items = K::M('designer/article')->items_by_ids($ids)){
                $aids = $designer_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['article_id']] = $v['article_id'];
                    $designer_ids[$v['uid']] = $v['uid'];
                }
                if($aids && K::M('designer/article')->delete($aids)){
                    K::M('designer/designer')->blog_count($designer_ids);
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}