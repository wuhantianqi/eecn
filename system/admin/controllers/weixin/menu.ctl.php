<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Menu extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        
        if($items = K::M('weixin/menu')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:weixin/menu/items.html';
    }

    public function create($wx_id=null)
    {
        if(!($wx_id = (int)$wx_id) && !($wx_id = (int)$this->GP('wx_id'))){
            $this->err->add('非法的数据提交', 211);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('公众号不存在或已经删除人', 212);
        }else if($data = $this->checksubmit('data')){
            $data['wx_id'] = $weixin['wx_id'];      
            if($menu_id = K::M('weixin/menu')->create($data)){
                $this->err->add('添加微信菜单成功');
                $this->err->set_data('forward', $this->mklink('weixin/weixin:menu', array($wx_id)));
            }
        }else{
            $this->pagedata['weixin'] = $weixin;
            $this->pagedata['menu_list'] = K::M('weixin/menu')->items_by_weixin($weixin['wx_id']);
            $this->tmpl = 'admin:weixin/menu/create.html';
        }
    }

    public function edit($menu_id=null)
    {
        if(!($menu_id = (int)$menu_id) && !($menu_id = $this->GP('menu_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/menu')->detail($menu_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){            
            if(K::M('weixin/menu')->update($menu_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
            $this->pagedata['weixin'] = K::M('weixin/weixin')->detail($detail['wx_id']);
            $this->pagedata['menu_list'] = K::M('weixin/menu')->items_by_weixin($weixin['wx_id']);            
        	$this->tmpl = 'admin:weixin/menu/edit.html';
        }
    }

    public function delete($menu_id=null)
    {
        if($menu_id = (int)$menu_id){
            if(!$detail = K::M('weixin/menu')->detail($menu_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/menu')->delete($menu_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('menu_id')){
            if(K::M('weixin/menu')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function towechat($wx_id)
    {
        if($weixin = K::M('weixin/weixin')->detail($wx_id)){
            $buttons = array();
            $buttons = K::M('weixin/menu')->wechat_buttons($weixin['wx_id']);
            Import::L('weixin/wechat.class.php');
            $client = new WechatClient($weixin['appid'], $weixin['secret']);
            if($client->setMenu(array('button'=>$buttons))){
                $this->err->add('同步公众号菜单成功');
            }else{
                $this->err->add($client->error(), 211);
            }
        }else{
            $this->err->add('未指定要同步的公众号');
        }      
    } 

}