<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: payment.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Payment_Payment extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;

        if($items = K::M('payment/payment')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:payment/items.html';
    }

    public function install()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($attach = $_FILES['payment_logo']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'payment')){
                            $data['logo'] = $a['photo'];
                        }
                    }
                }
                if($payment_id = K::M('payment/payment')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?payment/payment-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:payment/install.html';
        }
    }

    public function config($payment_id=null)
    {
        if(!($payment_id = (int)$payment_id) && !($payment_id = $this->GP('payment_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('payment/payment')->detail($payment_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($attach = $_FILES['payment_logo']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'payment')){
                            $data['logo'] = $a['photo'];
                        }
                    }
                }
                if(K::M('payment/payment')->update($payment_id, $data)){
                    $this->err->add('修改内容成功');
                }
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
            //print_r($detail);echo 'FILE:',__FILE__,'LINE:',__LINE__;exit();
            if($config = include(__CFG::DIR.'plugins/payments/'.$detail['payment'].'/config.php')){
                $this->pagedata['payment_config'] = $config;
            }
        	$this->tmpl = 'admin:payment/config.html';
        }
    }

    public function uninstall($payment_id=null)
    {
        if($payment_id = (int)$payment_id){
            if(K::M('payment/payment')->delete($payment_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('payment_id')){
            if(K::M('payment/payment')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}