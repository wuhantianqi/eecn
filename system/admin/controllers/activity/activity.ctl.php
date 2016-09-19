<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Activity_Activity extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['activity_id']){$filter['activity_id'] = $SO['activity_id'];}
			if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
			if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
        }
        $this->tmpl = 'admin:activity/activity/items.html';
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
		if($items = K::M('activity/activity')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['city_list'] = K::M('data/city')->fetch_all();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
		$this->pagedata['cateList'] = K::M("activity/cate")->fetch_all();
        $this->tmpl = 'admin:activity/activity/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:activity/activity/so.html';
    }

    public function detail($activity_id = null)
    {	
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
		$filter['activity_id'] = $activity_id;
        if(!$activity_id = (int)$activity_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('activity/activity')->items($filter)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:activity/activity/detail.html';
        }
    }

   public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				if($_FILES['data']){
					foreach($_FILES['data'] as $k=>$v){
						foreach($v as $kk=>$vv){
							$attachs[$kk][$k] = $vv;
						}
					}
					$upload = K::M('magic/upload');
					foreach($attachs as $k=>$attach){
						if($attach['error'] == UPLOAD_ERR_OK){
							if($a = $upload->upload($attach, 'activity')){
								$data[$k] = $a['photo'];
							}
						}
					}
				}
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if($activity_id = K::M('activity/activity')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?activity/activity-index.html');
                }
            } 
        }else{
			$this->pagedata['cates'] = K::M('activity/cate')->fetch_all();
            $this->tmpl = 'admin:activity/activity/create.html';
        }
    }

    public function edit($activity_id=null)
    {
        if(!($activity_id = (int)$activity_id) && !($activity_id = $this->GP('activity_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('activity/activity')->detail($activity_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				if($_FILES['data']){
					foreach($_FILES['data'] as $k=>$v){
						foreach($v as $kk=>$vv){
							$attachs[$kk][$k] = $vv;
						}
					}
					$upload = K::M('magic/upload');
					foreach($attachs as $k=>$attach){
						if($attach['error'] == UPLOAD_ERR_OK){
							if($a = $upload->upload($attach, 'activity')){
								$data[$k] = $a['photo'];
							}
						}
					}
				}
				unset($data['activity_id'],$data['city_id']);
                if(K::M('activity/activity')->update($activity_id, $data)){
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?activity/activity-index.html');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
			$this->pagedata['cates'] = K::M('activity/cate')->fetch_all();
        	$this->tmpl = 'admin:activity/activity/edit.html';
        }
    }

    public function doaudit($activity_id=null)
    {	
       
		if($activity_id = (int)$activity_id){
			if(!$activity = K::M('activity/activity')->detail($activity_id)){
				$this->err->add('该活动不存在或已经删除', 211);
			}else if(!$this->check_city($activity['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else if(K::M('activity/activity')->batch($activity_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('activity_id')){

			if($items = K::M('activity/activity')->items_by_ids($ids)){
                $aids = $activity_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['activity_id']] = $aids['activity_id'];
                    $home_ids[$v['activity_id']] = $v['activity_id'];
                }
                if($aids && K::M('activity/activity')->batch($ids, array('audit'=>1))){
                    $this->err->add('批量审核内容成功');
                }
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($activity_id=null)
    {
        
		if($activity_id = (int)$activity_id){
            if($activity = K::M('activity/activity')->detail($activity_id)){
                if(!$this->check_city($activity['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('activity/activity')->delete($activity_id)){
                    $this->err->add('删除活动成功');
                }
            }
        }else if($ids = $this->GP('activity_id')){
            if($items = K::M('activity/activity')->items_by_ids($ids)){
                $aids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['activity_id']] = $v['activity_id'];
                }
                if($aids && K::M('activity/activity')->delete($aids)){
                    $this->err->add('批量删除活动成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}