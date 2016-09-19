<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: censor.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Data_Censor extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['type']){$filter['type'] = $SO['type'];}
            if($SO['find']){$filter['find'] = "LIKE:%".$SO['find']."%";}            
            if(is_array($SO['dateline'])){
                if($SO['dateline'][0] && $SO['dateline'][1]){
                    $a = strtotime($SO['dateline'][0]); 
                    $b = strtotime($SO['dateline'][1])+86400;
                    $filter['dateline'] = $a."~".$b;
                }
            }
        }
        if($items = K::M('data/censor')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));;
        }
        $this->pagedata['type_list'] = K::M('data/censor')->type_list();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:data/censor/items.html';
    }

    public function so()
    {
        $this->pagedata['type_list'] = K::M('data/censor')->type_list();        
        $this->tmpl = 'admin:data/censor/so.html';
    }

    public function create()
    {
        if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['admin'] = $this->system->admin_name;
                if($ID = K::M('data/censor')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?data/censor-index.html');
                }
            } 
        }else{
            $this->pagedata['type_list'] = K::M('data/censor')->type_list();
            $this->tmpl = 'admin:data/censor/create.html';
        }
    }

    public function edit($id=null)
    {
        if(!($id = (int)$id) && !($id = (int)$this->GP('ID'))){
            $this->err->add('非制定要修改内容的ID', 211);
        }else if(!$detail = K::M('data/censor')->detail($id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(K::M('data/censor')->update($id, $data)){
                $this->err->add('修改内容成功');
            }
        }else{
            $this->pagedata['type_list'] = K::M('data/censor')->type_list();            
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:data/censor/edit.html';
        }
    }

    public function delete($pk=null)
    {
        if(!empty($pk)){
            if(K::M('data/censor')->delete($pk)){
                $this->err->add('删除成功');
            }
        }else if($pks = $this->GP('ID')){
            if(K::M('data/censor')->delete($pks)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}