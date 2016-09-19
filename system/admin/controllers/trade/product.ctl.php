<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product.ctl.php 3903 2014-03-17 02:09:44Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Trade_Product extends Ctl
{
    
    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($order_pid = K::M('trade/product')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?trade/product-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:trade/order/product/create.html';
        }
    }

    public function edit($order_pid=null)
    {
        if(!($order_pid = (int)$order_pid) && !($order_pid = $this->GP('order_pid'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('trade/product')->detail($order_pid)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if(K::M('trade/product')->update($order_pid, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:trade/order/product/edit.html';
        }
    }


    public function delete($order_pid=null)
    {
        if($order_pid = (int)$order_pid){
            if(K::M('trade/product')->delete($order_pid)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('order_pid')){
            if(K::M('trade/product')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}