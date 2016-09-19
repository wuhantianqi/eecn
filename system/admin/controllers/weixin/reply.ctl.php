<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Reply extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
                if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
        }
        if($items = K::M('weixin/reply')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $wxids = array();
            foreach($items as $v){
                $wxids[$v['wx_id']] = $v['wx_id'];
            }
            if($wxids){
                $this->pagedata['weixin_list'] = K::M('weixin/weixin')->items_by_ids($wxids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:weixin/reply/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:weixin/reply/so.html';
    }

    public function dialog($wx_id, $page=1)
    {
        if(!$wx_id = (int)$wx_id){
            $this->err->add('非法的数据提交', 211);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('公众号不存在或已经删除', 212);
        }else{
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 10;
            $this->pagedata['weixin'] = $weixin;
            if($items = K::M('weixin/reply')->items(array('wx_id'=>$wx_id), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($wx_id, '{page}')), array('SO'=>$SO));                
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'admin:weixin/reply/dialog.html';
        }
    }

    public function create($wx_id=null)
    {
        if(!($wx_id = (int)$wx_id) && !($wx_id = (int)$this->GP('wx_id'))){
            $this->err->add('未指定添加到的公众号', 211);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('指定的公众号不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            if($attach = $_FILES['reply_photo']){
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                        $data['photo'] = $a['photo'];
                    }
                }
            }
            $data['wx_id'] = $weixin['wx_id'];
            if($reply_id = K::M('weixin/reply')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?weixin/reply-index.html');
            } 
        }else{
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'admin:weixin/reply/create.html';
        }
    }

    public function edit($reply_id=null)
    {
        if(!($reply_id = (int)$reply_id) && !($reply_id = $this->GP('reply_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/reply')->detail($reply_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            if($attach = $_FILES['reply_photo']){
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = K::M('magic/upload')->upload($attach, 'weixin')){
                        $data['photo'] = $a['photo'];
                    }
                }
            }
            if(K::M('weixin/reply')->update($reply_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
            $this->pagedata['weixin'] = $weixin;
        	$this->tmpl = 'admin:weixin/reply/edit.html';
        }
    }

    public function delete($reply_id=null)
    {
        if($reply_id = (int)$reply_id){
            if(!$detail = K::M('weixin/reply')->detail($reply_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/reply')->delete($reply_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('reply_id')){
            if(K::M('weixin/reply')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}