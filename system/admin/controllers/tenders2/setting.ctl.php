<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: setting.ctl.php 5681 2014-06-26 11:33:16Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Tenders_Setting extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['setting_id']){$filter['setting_id'] = $SO['setting_id'];}
            if($SO['type']){$filter['type'] = $SO['type'];}
            if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
        }
        if($items = K::M('tenders/setting')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['type'] = K::M('tenders/setting')->get_type_means();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:tenders/setting/items.html';
    }

    public function so()
    {   
        $this->pagedata['type'] = K::M('tenders/setting')->get_type_means();
        $this->tmpl = 'admin:tenders/setting/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($setting_id = K::M('tenders/setting')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?tenders/setting-index.html');
                }
            } 
        }else{
            $this->pagedata['type'] = K::M('tenders/setting')->get_type_means();
           $this->tmpl = 'admin:tenders/setting/create.html';
        }
    }

    public function edit($setting_id=null)
    {
        if(!($setting_id = (int)$setting_id) && !($setting_id = $this->GP('setting_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('tenders/setting')->detail($setting_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('tenders/setting')->update($setting_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['type'] = K::M('tenders/setting')->get_type_means();
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:tenders/setting/edit.html';
        }
    }

    public function delete($setting_id=null)
    {
        if($setting_id = (int)$setting_id){
            if(K::M('tenders/setting')->delete($setting_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('setting_id')){
            if(K::M('tenders/setting')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}