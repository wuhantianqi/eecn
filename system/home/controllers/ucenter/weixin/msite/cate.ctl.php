<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Msite_Cate extends Ctl_Ucenter
{
    protected $_allow_fields = 'title,link,orderby';

    public function index($page=1)
    {
        $weixin = $this->ucenter_weixin();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['page'] = $limit = 30;
        $pager['page'] = $count = 0;
        if($items = K::M('weixin/msite/cate')->items(array('wx_id'=>$weixin['wx_id']), null, $page, $limit, $count)){
            $pgaer['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->tmpl = 'ucenter/weixin/msite/cate/items.html';         
    }

    public function create()
    {
        $weixin = $this->ucenter_weixin();
        if($data = $this->checksubmit('data')){
            if($this->MEMBER['group']['priv']['allow_msite'] < 0){
                $this->err->add('您是【'.$this->MEMBER['group_name'].'】没有权限使用微网站', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['cate_icon']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'msite')){
                            $data['icon'] = $a['photo'];
                        }
                    }
                }
                $data['wx_id'] = $weixin['wx_id'];
                if($article_id = K::M('weixin/msite/cate')->create($data)){
                    $this->err->add('添加分类成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/weixin/msite/cate:index'));
                }
            }
        }else{
            $this->tmpl = 'ucenter/weixin/msite/cate/create.html';
        }
    }

    public function edit($cat_id=null)
    {
        $weixin = $this->ucenter_weixin();
        if(!($cat_id = (int)$cat_id) && !($cat_id = (int)$this->GP('cat_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$detail = K::M('weixin/msite/cate')->detail($cat_id)){
            $this->err->add('您要修改的分类不存在或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
             $this->err->add('您没有权限修改该内容', 213);
        }else if($data = $this->checksubmit('data')){
            if($this->MEMBER['group']['priv']['allow_msite'] < 1){
                $this->err->add('您是【'.$this->MEMBER['group_name'].'】没有权限使用微网站', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['cate_icon']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'msite')){
                            $data['icon'] = $a['photo'];
                        }
                    }
                }
                if($article_id = K::M('weixin/msite/cate')->update($cat_id, $data)){
                    $this->err->add('修改分类成功');
                }
            }
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/weixin/msite/cate/edit.html';
        }   
    }

    public function delete($cat_id)
    {
        $weixin = $this->ucenter_weixin();
        if(!($cat_id = (int)$cat_id) && !($cat_id = $this->GP('cat_id'))){
            $this->err->add('未指要删除的分类', 211);
        }else if(!$detail = K::M('weixin/msite/cate')->detail($cat_id)){
            $this->err->add('你要删除的分类或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
            $this->err->add('您没有权限删除该分类', 213);
        }else{
            K::M('weixin/msite/cate')->delete($cat_id);
            $this->err->add('删除分类成功');
        }          
    }

}