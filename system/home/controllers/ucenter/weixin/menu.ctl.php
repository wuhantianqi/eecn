<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Menu extends Ctl_Ucenter
{
    
    public function index()
    {
        $weixin = $this->ucenter_weixin();
        $this->pagedata['items'] = K::M('weixin/menu')->items_by_weixin($weixin['wx_id']);
        $this->tmpl = 'ucenter/weixin/menu/items.html';       
    }

    //同步到微信
    public function towechat()
    {
        $weixin = $this->ucenter_weixin();
        $buttons = array();
        $buttons = K::M('weixin/menu')->wechat_buttons($weixin['wx_id']);
        Import::L('weixin/wechat.class.php');
        $client = new WechatClient($weixin['appid'], $weixin['secret']);
        if($client->setMenu(array('button'=>$buttons))){
            $this->err->add('同步公众号菜单成功');
        }else{
            $this->err->add($client->error(), 211);
        }
    }

    public function create()
    {
        $weixin = $this->ucenter_weixin();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'title,parent_id,type,reply_id,content,link,orderby')){
                $this->err->add('非法的数据提交', 211);
            }else{
                $data['wx_id'] = $weixin['wx_id'];
                if($menu_id = K::M('weixin/menu')->create($data)){
                    $this->err->set_data('forward', $this->mklink('ucenter/weixin/menu:index'));
                    $this->err->add('添加微信菜单成功');
                }
            }
        }else{
            $this->pagedata['wx_menu_list'] = K::M('weixin/menu')->items_by_weixin($weixin['wx_id']);
            $this->tmpl = 'ucenter/weixin/menu/create.html';
        }
    }

    public function edit($menu_id=null)
    {
        $weixin = $this->ucenter_weixin();
        if(!($menu_id = (int)$menu_id) && !($menu_id = (int)$this->GP('menu_id'))){    
            $this->error(404);
        }else if(!$detail = K::M('weixin/menu')->detail($menu_id)){
            $this->err->add('您要修改的菜单不存在或已经删除', 211);
        }else if($detail['wx_id'] != $weixin['wx_id']){
            $this->err->add('非法的数据提交', 212);
        }else if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'title,parent_id,type,reply_id,content,link,orderby')){
                $this->err->add('非法的数据提交', 211);
            }else{
                if(K::M('weixin/menu')->update($menu_id, $data)){
                    $this->err->add('修改微信菜单成功');
                }
            }
        }else{
            if($reply_id = (int)$detail['reply_id']){
                if($reply = K::M('weixin/reply')->detail($reply_id)){
                    if($weixin['wx_id'] == $reply['wx_id']){
                        $this->pagedata['reply'] = $reply;
                    }
                }                
            }
            $this->pagedata['detail'] = $detail;
            $this->pagedata['wx_menu_list'] = K::M('weixin/menu')->items_by_weixin($weixin['wx_id']);
            $this->tmpl = 'ucenter/weixin/menu/edit.html';
        }
    }

    public function delete($menu_id)
    {
        $weixin = $this->ucenter_weixin();
        if(!$menu_id = (int)$menu_id){
            $this->error(404);
        }else if(!$detail = K::M('weixin/menu')->detail($menu_id)){
            $this->err->add('你要删除的菜单不存或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
            $this->err->add('您没有权限删除该菜单', 213);
        }else{
            K::M('weixin/menu')->delete($menu_id);
            $this->err->add('删除菜单成功');
        }        
    }
}