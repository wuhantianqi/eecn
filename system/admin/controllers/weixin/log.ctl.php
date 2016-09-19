<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Log extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
                if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['weixin']){$filter['weixin'] = "LIKE:%".$SO['weixin']."%";}
        }
        if($items = K::M('weixin/log')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:weixin/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:weixin/so.html';
    }



    public function create()
    {
        if($data = $this->checksubmit('data')){
            
            if($log_id = K::M('weixin/log')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?weixin/log-index.html');
            } 
        }else{
           $this->tmpl = 'admin:weixin/create.html';
        }
    }





    public function delete($log_id=null)
    {
        if($log_id = (int)$log_id){
            if(!$detail = K::M('weixin/log')->detail($log_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/log')->delete($log_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('log_id')){
            if(K::M('weixin/log')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}