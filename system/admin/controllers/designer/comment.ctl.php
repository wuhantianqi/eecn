<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Designer_Comment extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['designer_id']){$filter['designer_id'] = $SO['designer_id'];}
            if($SO['content']){$filter['content'] = "LIKE:%".$SO['content']."%";}
            if($SO['reply']){$filter['reply'] = "LIKE:%".$SO['reply']."%";}
            if(is_array($SO['reply_time'])){if($SO['reply_time'][0] && $SO['reply_time'][1]){$a = strtotime($SO['reply_time'][0]); $b = strtotime($SO['reply_time'][1])+86400;$filter['reply_time'] = $a."~".$b;}}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('designer/comment')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $designer_ids = array();
            foreach($items as $v){
                $designer_ids[$v['designer_id']] = $v['designer_id'];
            }
            if($designer_ids){
                $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:designer/comment/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:designer/comment/so.html';
    }

    public function reply($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = (int)$this->GP('comment_id'))){
            $this->err->add('非法的数据提交', 201);
        }else if(!$detail = K::M('designer/comment')->detail($comment_id)){
            $this->err->add('原评论内容已经不存在', 202);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                unset($data['designer_id'], $data['city_id']);
				$data['replytime'] = __TIME;
                if(K::M('designer/comment')->update($comment_id, $data)){
                    $this->err->add('回复评论内容成功');
                }
            } 
        }else{
            if($designer_id = $detail['designer_id']){
                $this->pagedata['designer'] = K::M('designer/designer')->detail($designer_id);
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }              
            $this->pagedata['detail'] = $detail;          
            $this->tmpl = 'admin:designer/comment/reply.html';
        }      
    }

    public function create()
    {
        if($data = $this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$designer_id = (int)$data['designer_id']){
                $this->err->add('未指定要预约设计师', 212);
            }else if(!$designer = K::M('designer/designer')->detail($designer_id)){
                $this->err->add('设计师不存在或已经删除', 213);
            }else if(!$this->check_city($designer['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else{
                $data['city_id'] = $designer['city_id'];
                if($comment_id = K::M('designer/comment')->create($data)){
					K::M('designer/comment')->comment($data);
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?designer/comment-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:designer/comment/create.html';
        }
    }

    public function edit($comment_id=null)
    {
        if(!($comment_id = (int)$comment_id) && !($comment_id = $this->GP('comment_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('designer/comment')->detail($comment_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(CITY_ID){
                    unset($data['designer_id'], $data['city_id']);
                }
                if(K::M('designer/comment')->update($comment_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
            $this->pagedata['designer'] = K::M('designer/designer')->detail($detail['designer_id']);     
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:designer/comment/edit.html';
        }
    }

    public function doaudit($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(K::M('designer/comment')->batch($comment_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('comment_id')){
            if(K::M('designer/comment')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($comment_id=null)
    {
        if($comment_id = (int)$comment_id){
            if(!$detail = K::M('designer/comment')->detail($comment_id)){
                $this->err->add('你要删除的文件不存在或已经删除', 211);
            }else{
                if(K::M('designer/comment')->delete($comment_id)){
                    K::M('designer/comment')->comment_count($detail['designer_id']);
                    $this->err->add('删除评论成功');
                }
            }
        }else if($ids = $this->GP('comment_id')){            
            if($items = K::M('designer/comment')->items_by_ids($ids)){
                $aids = $designer_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['comment_id']] = $v['comment_id'];
                    $designer_ids[$v['designer_id']] = $v['designer_id'];
                }
                if(K::M('designer/comment')->delete($aids)){
                    K::M('designer/comment')->comment_count($designer_ids);
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}