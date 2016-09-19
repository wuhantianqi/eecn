<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Weixin extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        
        if($items = K::M('weixin/weixin')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:weixin/weixin/items.html';
    }

    //站点公众号
    public function admin()
    {
        $weixin = K::M('weixin/weixin')->admin();
        if($data = $this->checksubmit('data')){
            if($attach = $_FILES['weixin_face']){
               if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                        $data['face'] = $a['photo'];
                    }
                }
            }            
            if($weixin['wx_id']){
                $ret = K::M('weixin/weixin')->update($weixin['wx_id'], $data);
            }else{
                $data['admin'] = 1;
                $data['city_id'] = 0;
                $ret = K::M('weixin/weixin')->create($data);
            }
            if($ret){
                $this->err->add('设置平台公众号成功');
            }
        }else{
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'admin:weixin/weixin/admin.html';
        }
    }

    public function leaflets($wx_id = null)
    {
        if(!($wx_id = (int)$wx_id) && !($wx_id = (int)$this->GP('wx_id'))){
            $this->err->add('非法的数据请求', 211);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('公众号不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            $addon = $weixin['addon'];
            if($attach = $_FILES['weixin_photo']){
                if(UPLOAD_ERR_OK == $attach['error']){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin', $addon['leaflets']['photo'])){
                        $data['photo'] = $a['photo'];
                    }
                }
            }
            if($data){
                $addon['leaflets'] = $data;
                if(K::M('weixin/weixin')->update($weixin['wx_id'], array('addon'=>$addon))){
                    $this->err->add('设置微信推广页成功');
                }
            }
        }else{
            $this->pagedata['leaflets'] = $weixin['addon']['leaflets'];
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'admin:weixin/weixin/leaflets.html';
        }
    }

    public function welcome($wx_id = null)
    {
        if(!($wx_id = (int)$wx_id) && !($wx_id = (int)$this->GP('wx_id'))){
            $this->err->add('非法的数据请求', 211);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('公众号不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            $addon = $weixin['addon'];
            if($attach = $_FILES['weixin_photo']){
                if(UPLOAD_ERR_OK == $attach['error']){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin', $addon['welcome']['photo'])){
                        $data['photo'] = $a['photo'];
                    }
                }
            }
            if($data){
                $addon['welcome'] = $data;
                if(K::M('weixin/weixin')->update($weixin['wx_id'], array('addon'=>$addon))){
                    $this->err->add('设置微信欢迎信息成功');
                }
            }
        }else{
            if($reply_id = (int)$weixin['addon']['welcome']['reply_id']){
                if($reply = K::M('weixin/reply')->detail($reply_id)){
                    if($reply['wx_id'] == $weixin['wx_id']){
                        $this->pagedata['reply'] = $reply;
                    }
                }
            }
            $this->pagedata['welcome'] = $weixin['addon']['welcome'];
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'admin:weixin/weixin/welcome.html';
        }
    }

    public function menu($wx_id)
    {
        if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('非法的数据请求', 211);
        }else{
            $this->pagedata['menu_list'] = K::M('weixin/menu')->items_by_weixin($weixin['wx_id']);
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'admin:weixin/menu/items.html';
        }
    }

    public function reply($wx_id, $page=1)
    {
        if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('非法的数据请求', 211);
        }else{
            $pager = array();
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 30;
            $pager['count'] = $count = 0;
            if($items = K::M('weixin/reply')->items(array('wx_id'=>$wx_id), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($wx_id, '{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['weixin'] = $weixin;
            $this->pagedata['weixin_list'][$weixin['wx_id']] = $weixin;
            $this->tmpl = 'admin:weixin/reply/items.html';
        }
    }

    public function keyword($wx_id, $page=1)
    {
        if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('非法的数据请求', 211);
        }else{
            $pager = array();
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 30;
            $pager['count'] = $count = 0;
            if($items = K::M('weixin/keyword')->items(array('wx_id'=>$wx_id), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($wx_id, '{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['weixin'] = $weixin;
            $this->pagedata['weixin_list'][$weixin['wx_id']] = $weixin;
            $this->tmpl = 'admin:weixin/keyword/items.html';
        }
    }

    public function create()
    {
        if($data = $this->checksubmit('data')){
            if($attach = $_FILES['weixin_face']){
               if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                        $data['face'] = $a['photo'];
                    }
                }
            }
            if($id = K::M('weixin/weixin')->create($data)){
                $this->err->add('添加公众帐号成功');
                $this->err->set_data('forward', '?weixin/weixin-index.html');
            }
        }else{
           $this->tmpl = 'admin:weixin/weixin/create.html';
        }
    }

    public function edit($wx_id=null)
    {
        if(!($wx_id = (int)$wx_id) && !($wx_id = $this->GP('wx_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            if($attach = $_FILES['weixin_face']){
               if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                        $data['face'] = $a['photo'];
                    }
                }
            }
            if(K::M('weixin/weixin')->update($wx_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:weixin/weixin/edit.html';
        }
    }

    public function delete($wx_id=null)
    {
        if($wx_id = (int)$wx_id){
            if(!$detail = K::M('weixin/weixin')->detail($wx_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/weixin')->delete($wx_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('wx_id')){
            if(K::M('weixin/weixin')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}