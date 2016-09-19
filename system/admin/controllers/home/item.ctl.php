<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_item extends Ctl
{
 
    public function index($site_id,$page=1)
    {   
        if(!$site_id){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            if($SO = $this->GP('SO')){
                $pager['SO'] = $SO;
                if($SO['status']){$filter['status'] = $SO['status'];}
            }
            $filter['site_id'] = $site_id;
            if($items = K::M('home/item')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($site_id, '{page}')), array('SO'=>$SO));
            }
            $this->pagedata['status'] = K::M('home/site')->get_status();
            $this->pagedata['items'] = $items;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['site_id'] = $site_id;
            $this->pagedata['site'] = $detail;
            $this->tmpl = 'admin:home/item/items.html';
        }
    }

    public function so($site_id)
    {
        if(!$site_id){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权搜索', 403);
        }else{
            $this->pagedata['status'] = K::M('home/site')->get_status();
            $this->pagedata['site_id'] = $site_id;
            $this->tmpl = 'admin:home/item/so.html';
        }
    }


    public function create($site_id)
    {
        if(!($site_id = (int)$site_id) && !($site_id = (int)$this->GP('site_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/site')->detail($site_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else{
            if($this->checksubmit()){
                if(!$data = $this->GP('data')){
                    $this->err->add('非法的数据提交', 201);
                }else{
                   $data['site_id'] = $site_id;
                    if($item_id = K::M('home/item')->create($data)){
                        if($detail['status'] < $data['status']){
                            K::M('home/site')->update($detail['site_id'],array('status'=>$data['status']), true);
                        }
                        $this->err->add('添加内容成功');
                        $this->err->set_data('forward', '?home/item-index-'.$site_id.'.html');
                    }
                } 
            }else{
               $status = K::M('home/site')->get_status();
               $have_status = K::M('home/item')->get_status($site_id,$status);
               $this->pagedata['status'] = $have_status;
               $this->pagedata['site_id'] = $site_id; 
               $this->pagedata['site'] = $detail;
               $this->tmpl = 'admin:home/item/create.html';
            }
        }
    }

    public function edit($item_id=null)
    {
        if(!($item_id = (int)$item_id) && !($item_id = $this->GP('item_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/item')->detail($item_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$site = K::M('home/site')->detail($detail['site_id'])){
            $this->err->add('您要修改的工地不存在或已经删除', 212);
        }else if(!$this->check_city($site['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                unset($data['site_id']);
                if(K::M('home/item')->update($item_id, $data)){
                    if($detail['status'] < $data['status']){
                        K::M('home/site')->update($detail['site_id'],array('status'=>$data['status']), true);
                    }
                    $this->err->add('修改内容成功');
                    $this->err->set_data('forward', '?home/item-index-'.$detail['site_id'].'.html');
                }
            } 
        }else{
            $status= K::M('home/site')->get_status();
            $have_status = K::M('home/item')->get_status($detail['site_id'],$status);
            $have_status[$detail['status']] = $status[$detail['status']];
            ksort($have_status);            
            $this->pagedata['status'] = $have_status;
            $this->pagedata['detail'] = $detail;
            $this->pagedata['site'] =  K::M('home/site')->detail($detail['site_id']);
            $this->tmpl = 'admin:home/item/edit.html';
        }
    }

    public function delete($item_id=null)
    {
        if($item_id = (int)$item_id){
            if($item = K::M('home/item')->detail($item_id)){
                if(!$site = K::M('home/site')->detail($item['site_id'])){
                     $this->err->add('该工地不存在或已经删除', 403);
                }else if(!$this->check_city($site['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else if(K::M('home/item')->delete($item_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('item_id')){
            if($items = K::M('home/item')->items_by_ids($ids)){
                $aids = $site =  array();
                foreach($items as $k => $v){
                    if($site_id = $v['site_id']){
                        break;
                    }
                }
                if(!$site = K::M('home/site')->detail($site_id)){
                    $this->err->add('该工地不存在或已经删除', 403);
                }else if(!$this->check_city($site['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['site_id'] == $site_id){
                            $aids[$val['item_id']] = $val['item_id'];
                        }
                    }
                    if($aids && K::M('home/item')->delete($aids)){
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}