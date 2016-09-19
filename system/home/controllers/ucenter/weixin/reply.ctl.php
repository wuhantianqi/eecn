<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Reply extends Ctl_Ucenter 
{
    
    public function index($page=1)
    {
        $weixin = $this->ucenter_weixin();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $this->pagedata['weixin'] = $weixin;
        if($items = K::M('weixin/reply')->items(array('wx_id'=>$weixin['wx_id']), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));                
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/weixin/reply/items.html';
    }

    public function create()
    {
        $weixin = $this->ucenter_weixin();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'title,intro,content,jumpurl')){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['reply_photo']){
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }                
                $data['wx_id'] = $weixin['wx_id'];
                if($reply_id = K::M('weixin/reply')->create($data)){
                    $this->err->set_data('forward', $this->mklink('ucenter/weixin/reply:index'));
                    $this->err->add('添加微信素材成功');
                }
            }
        }else{
            $this->tmpl = 'ucenter/weixin/reply/create.html';
        }
    }

    public function edit($reply_id=null)
    {
        $weixin = $this->ucenter_weixin();
        if(!($reply_id = (int)$reply_id) && !($reply_id = (int)$this->GP('reply_id'))){
            $this->error(404);
        }else if(!$detail = K::M('weixin/reply')->detail($reply_id)){
            $this->err->add('您要修改的素材不存在或已经删除', 211);
        }else if($detail['wx_id'] != $weixin['wx_id']){
            $this->err->add('您没有权限修改该素材', 212);
        }else if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'title,intro,content,jumpurl')){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['reply_photo']){
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }                
                if(K::M('weixin/reply')->update($reply_id, $data)){
                    $this->err->add('修改微信素材成功');
                }
            }
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/weixin/reply/edit.html';
        }
    }

    public function delete($reply_id)
    {
        $weixin = $this->ucenter_weixin();
        if(!$reply_id = (int)$reply_id){
            $this->error(404);
        }else if(!$detail = K::M('weixin/reply')->detail($reply_id)){
            $this->err->add('你要删除的素材不存或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
            $this->err->add('您没有权限删除该素材', 213);
        }else{
            K::M('weixin/reply')->delete($reply_id);
            $this->err->add('删除素材成功');
        }   
    }

    public function dialog($page=1)
    {
        $weixin = $this->ucenter_weixin();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $this->pagedata['weixin'] = $weixin;
        if($items = K::M('weixin/reply')->items(array('wx_id'=>$weixin['wx_id']), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));                
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/weixin/reply/dialog.html';
    }

}