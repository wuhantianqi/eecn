<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Tenders_Track extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
                if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            
        }
        if($items = K::M('tenders/track')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:tenders/track/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:tenders/track/so.html';
    }

    public function detail($track_id = null)
    {
        if(!$track_id = (int)$track_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('tenders/track')->detail($track_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:tenders/track/detail.html';
        }
    }

    public function create($look_id=null)
    {
        if($data = $this->checksubmit('data')){
            if($track_id = K::M('tenders/track')->create($data)){
                $this->err->add('添加内容成功');
            } 
        }else{
			$filter['look_id'] = $look_id;
			$items = K::M('tenders/track')->items($filter);
			$this->pagedata['items'] = $items;
			$this->pagedata['look_id'] = $look_id;
            $this->tmpl = 'admin:tenders/track/create.html';
        }
    }
	
	public function reply($track_id=null)
	{
		if(!($track_id = (int)$track_id) && !($track_id = $this->GP('track_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('tenders/track')->detail($track_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
			$data['replyip'] = __IP;
			$data['replytime'] = __TIME;
            if($track_id = K::M('tenders/track')->update($track_id,$data)){
                $this->err->add('添加回复成功成功');
            } 
        }else{
			$detail = K::M('tenders/track')->detail($track_id);
			$this->pagedata['detail'] = $detail;
			$this->pagedata['track_id'] = $track_id;
            $this->tmpl = 'admin:tenders/track/reply.html';
        }
	}

   

    public function doaudit($track_id=null)
    {
        if($track_id = (int)$track_id){
            if(K::M('tenders/track')->batch($track_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('track_id')){
            if(K::M('tenders/track')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($track_id=null)
    {
        if($track_id = (int)$track_id){
            if(!$detail = K::M('tenders/track')->detail($track_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('tenders/track')->delete($track_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('track_id')){
            if(K::M('tenders/track')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}