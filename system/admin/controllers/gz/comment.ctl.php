<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Gz_Comment extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['comment_id']){$filter['comment_id'] = $SO['comment_id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['gz_id']){$filter['gz_id'] = $SO['gz_id'];}
            if($SO['content']){$filter['content'] = "LIKE:%".$SO['content']."%";}
            if($SO['reply']){$filter['reply'] = "LIKE:%".$SO['reply']."%";}
            if(is_array($SO['replytime'])){if($SO['replytime'][0] && $SO['replytime'][1]){$a = strtotime($SO['replytime'][0]); $b = strtotime($SO['replytime'][1])+86400;$filter['replytime'] = $a."~".$b;}}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
		$filter['closed'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('gz/comment')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $gz_ids = array();
            foreach($items as $v){
                $gz_ids[$v['gz_id']] = $v['gz_id'];
            }
            if($gz_ids){
                $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
            }            
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:gz/comment/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:gz/comment/so.html';
    }

    public function reply($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = (int)$this->GP('comment_id'))){
            $this->err->add('非法的数据提交', 201);
        }else if(!$detail = K::M('gz/comment')->detail($comment_id)){
            $this->err->add('原评论内容已经不存在', 202);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                unset($data['gz_id'], $data['city_id']);
				$data['replytime'] = __TIME;
                if(K::M('gz/comment')->update($comment_id, $data)){
                    $this->err->add('回复评论内容成功');
                }
            } 
        }else{
            if($gz_id = $detail['gz_id']){
                $this->pagedata['gz'] = K::M('gz/gz')->detail($gz_id);
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }              
            $this->pagedata['detail'] = $detail;          
            $this->tmpl = 'admin:gz/comment/reply.html';
        }      
    }

    public function create()
    {
        if($data = $this->checksubmit('data')){
            if(!$gz_id = (int)$data['gz_id']){
                $this->err->add('未指定要预约工长', 212);
            }else if(!$gz = K::M('gz/gz')->detail($gz_id)){
                $this->err->add('工长不存在或已经删除', 213);
            }else if(!$this->check_city($gz['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
                $data['city_id'] = $gz['city_id'];
                if($comment_id = K::M('gz/comment')->create($data)){
					K::M('gz/comment')->comment($data);
                    $this->err->add('添加评论内容成功');
                    $this->err->set_data('forward', '?gz/comment-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:gz/comment/create.html';
        }
    }

    public function edit($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = $this->GP('comment_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('gz/comment')->detail($comment_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($data = $this->checksubmit('data')){
            unset($data['gz_id'], $data['city_id']);
            if(K::M('gz/comment')->update($comment_id, $data)){
                $this->err->add('修改内容成功');
            } 
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['gz'] = K::M('gz/gz')->detail($detail['gz_id']);     
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:gz/comment/edit.html';
        }
    }

    public function doaudit($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('gz/comment')->batch($comment_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('gz/comment')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('gz/comment')->delete($comment_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('gz/comment')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}