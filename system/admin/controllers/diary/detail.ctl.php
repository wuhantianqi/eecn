<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: detail.ctl.php 2341 2013-12-19 01:53:02Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Diary_Detail extends Ctl
{
    
    public function index($diary_id =null,$page=1)
    {
        if (!($diary_id = (int) $diary_id) && !($diary_id = $this->GP('diary_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('diary/diary')->detail($diary_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else {
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            $filter['diary_id'] = $diary_id;
			if(CITY_ID){
				$filter['city_id'] = CITY_ID;
			}
            if($items = K::M('diary/detail')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($diary_id, '{page}')), array('SO'=>$SO));
            }
            $this->pagedata['items'] = $items;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['diary_id'] = $diary_id;
            $this->pagedata['diary'] = $detail;
            $this->pagedata['status'] = K::M('home/site')->get_status();
            $this->tmpl = 'admin:diary/detail/items.html';
        }
    }





    public function create($diary_id = null)
    {
        if (!($diary_id = (int) $diary_id) && !($diary_id = $this->GP('diary_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('diary/diary')->detail($diary_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['diary_id'] = $diary_id;
                $data['clientip'] = __IP;
                $data['dateline']  = __TIME;
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if($detail_id = K::M('diary/detail')->create($data)){
                    K::M('diary/diary')->update($diary_id, array('status' => $data['status'],'content_num'=>$detail['content_num']+1));
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?diary/detail-index-'.$diary_id.'.html');
                }
            } 
        }else{
           $this->pagedata['diary_id'] = $diary_id;
           $this->pagedata['diary'] = $detail;
           $this->pagedata['status'] = K::M('home/site')->get_status();
           $this->tmpl = 'admin:diary/detail/create.html';
        }
    }

    public function edit($detail_id=null)
    {
        if(!($detail_id = (int)$detail_id) && !($detail_id = $this->GP('detail_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('diary/detail')->detail($detail_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('diary/detail')->update($detail_id, $data)){
                    K::M('diary/diary')->update($diary_id,array('status'=>$data['status']));
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['status'] = K::M('home/site')->get_status();
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:diary/detail/edit.html';
        }
    }



    public function delete($detail_id=null)
    {
        if($detail_id = (int)$detail_id){
            if(K::M('diary/detail')->delete($detail_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('detail_id')){
            if(K::M('diary/detail')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}