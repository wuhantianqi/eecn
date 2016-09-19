<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Keyword extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['keyword']){$filter['keyword'] = "LIKE:%".$SO['keyword']."%";}
        }
        if($items = K::M('weixin/keyword')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $wxids = array();
            foreach($items as $k=>$v){
                $wxids[$v['wx_id']] = $v['wx_id'];
            }
            if($wxids){
                $this->pagedata['weixin_list'] = K::M('weixin/weixin')->items_by_ids($wxids);
            }
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:weixin/keyword/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:weixin/keyword/so.html';
    }

    public function create($wx_id=null)
    {
        if(!($wx_id = (int)$wx_id) && !($wx_id = (int)$this->GP('wx_id'))){
            $this->err->add('未指定添加到的公众号', 211);
        }else if(!$weixin = K::M('weixin/weixin')->detail($wx_id)){
            $this->err->add('指定的公众号不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            $data['wx_id'] = $wx_id;
            if($id = K::M('weixin/keyword')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?weixin/keyword-index.html');
            } 
        }else{
            $this->pagedata['weixin'] = $weixin;
            $this->tmpl = 'admin:weixin/keyword/create.html';
        }
    }

    public function edit($kw_id=null)
    {
        if(!($kw_id = (int)$kw_id) && !($kw_id = $this->GP('kw_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/keyword')->detail($kw_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$weixin = K::M('weixin/weixin')->detail($detail['wx_id'])){
            $this->err->add('关键字隶属的公众号不存在或已经删除', 213);
        }else if($data = $this->checksubmit('data')){
            if(K::M('weixin/keyword')->update($kw_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
            if($reply_id = (int)$detail['reply_id']){
                if($reply = K::M('weixin/reply')->detail($reply_id)){
                    if($reply['wx_id'] == $detail['wx_id']){
                        $this->pagedata['reply'] = $reply;
                    }
                }
            }
            $this->pagedata['weixin'] = $weixin;
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:weixin/keyword/edit.html';
        }
    }

    public function delete($kw_id=null)
    {
        if($kw_id = (int)$kw_id){
            if(!$detail = K::M('weixin/keyword')->detail($kw_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/keyword')->delete($kw_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('kw_id')){
            if(K::M('weixin/keyword')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}