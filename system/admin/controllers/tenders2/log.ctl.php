<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Tenders_Log extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['log_id']){$filter['log_id'] = $SO['log_id'];}
if($SO['tenders_id']){$filter['tenders_id'] = $SO['tenders_id'];}
if(is_array($SO['tx_time'])){if($SO['tx_time'][0] && $SO['tx_time'][1]){$a = strtotime($SO['tx_time'][0]); $b = strtotime($SO['tx_time'][1])+86400;$filter['tx_time'] = $a."~".$b;}}
if($SO['status']){$filter['status'] = $SO['status'];}
if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        if($items = K::M('tenders/log')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:tenders/log/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:tenders/log/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($log_id = K::M('tenders/log')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?tenders/log-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:tenders/log/create.html';
        }
    }

    public function edit($log_id=null)
    {
        if(!($log_id = (int)$log_id) && !($log_id = $this->GP('log_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('tenders/log')->detail($log_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('tenders/log')->update($log_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:tenders/log/edit.html';
        }
    }



    public function delete($log_id=null)
    {
        if($log_id = (int)$log_id){
            if(K::M('tenders/log')->delete($log_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('log_id')){
            if(K::M('tenders/log')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}