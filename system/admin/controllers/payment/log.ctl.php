<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: log.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Payment_Log extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['from']){$filter['from'] = "LIKE:%".$SO['from']."%";}
            if($SO['payment']){$filter['payment'] = "LIKE:%".$SO['payment']."%";}
            if($SO['trade_no']){$filter['trade_no'] = "LIKE:%".$SO['trade_no']."%";}
            if(is_numeric($SO['payed'])){$filter['payed'] = $SO['payed'];}
            if(is_array($SO['payedtime'])){if($SO['payedtime'][0] && $SO['payedtime'][1]){$a = strtotime($SO['payedtime'][0]); $b = strtotime($SO['payedtime'][1])+86400;$filter['payedtime'] = $a."~".$b;}}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        if($items = K::M('payment/log')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:payment/logs/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:payment/logs/so.html';
    }



    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($log_id = K::M('payment/log')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?payment/log-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:payment/logs/create.html';
        }
    }

    public function edit($log_id=null)
    {
        if(!($log_id = (int)$log_id) && !($log_id = $this->GP('log_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('payment/log')->detail($log_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('payment/log')->update($log_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:payment/logs/edit.html';
        }
    }





}