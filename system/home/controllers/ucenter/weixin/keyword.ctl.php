<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Weixin_Keyword extends Ctl_Ucenter
{
    
    public function index($page=1)
    {
        $weixin = $this->ucenter_weixin();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $this->pagedata['weixin'] = $weixin;
        if($items = K::M('weixin/keyword')->items(array('wx_id'=>$weixin['wx_id']), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));                
            $this->pagedata['items'] = $items;
        }
        $this->tmpl = 'ucenter/weixin/keyword/items.html';    
    }

    public function create()
    {
        $weixin = $this->ucenter_weixin();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'keyword,reply_id,content')){
                $this->err->add('非法的数据提交', 211);
            }else{                              
                $data['wx_id'] = $weixin['wx_id'];
                if($kw_id = K::M('weixin/keyword')->create($data)){
                    $this->err->set_data('forward', $this->mklink('ucenter/weixin/keyword:index'));
                    $this->err->add('添加关键字成功');
                }
            }
        }else{
            $this->tmpl = 'ucenter/weixin/keyword/create.html';
        }
    }

    public function edit($kw_id=null)
    {
        $weixin = $this->ucenter_weixin();
        if(!($kw_id = (int)$kw_id) && !($kw_id = (int)$this->GP('kw_id'))){
            $this->error(404);
        }else if(!$detail = K::M('weixin/keyword')->detail($kw_id)){
            $this->err->add('您要修改的关键字不存在或已经删除', 211);
        }else if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'keyword,reply_id,content')){
                $this->err->add('非法的数据提交', 212);
            }else{
                if($kw_id = K::M('weixin/keyword')->update($kw_id, $data)){
                    $this->err->add('修改关键字成功');
                }
            }
        }else{
            if($reply_id = $detail['reply_id']){
                if($reply = K::M('weixin/reply')->detail($reply_id)){
                    if($reply['wx_id'] == $weixin['wx_id']){
                        $this->pagedata['reply'] = $reply;
                    }
                }
            }
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/weixin/keyword/edit.html';
        }
    }

    public function delete($kw_id)
    {
        $weixin = $this->ucenter_weixin();
        if(!$kw_id = (int)$kw_id){
            $this->error(404);
        }else if(!$detail = K::M('weixin/keyword')->detail($kw_id)){
            $this->err->add('你要删除的关键字不存或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
            $this->err->add('您没有权限删除该关键字', 213);
        }else{
            K::M('weixin/reply')->delete($kw_id);
            $this->err->add('删除关键字成功');
        }   
    }
}