<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Msite_Article extends Ctl_Ucenter
{
    
    protected $_allow_fields = 'cat_id,title,intro,content,link,orderby';

    public function index($page=1)
    {
        $weixin = $this->ucenter_weixin();
        $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['page'] = $limit = 30;
        $pager['page'] = $count = 0;
        if($items = K::M('weixin/msite/article')->items(array('wx_id'=>$weixin['wx_id']), null, $page, $limit, $count)){
            $pgaer['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['cate_list'] = K::M('weixin/msite/cate')->items_by_weixin($weixin['wx_id']);
        $this->tmpl = 'ucenter/weixin/msite/article/items.html';        
    }

    public function create()
    {
        $weixin = $this->ucenter_weixin();
        $cate_list = K::M('weixin/msite/cate')->options_by_weixin($weixin['wx_id']);
        if($data = $this->checksubmit('data')){
            if($this->MEMBER['group']['allow_msite'] < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限使用微网站', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['article_thumb']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'msite')){
                            $data['thumb'] = $a['photo'];
                        }
                    }
                }
                if(isset($data['cat_id']) && !$cate_list[$data['cat_id']]){
                    $data['cat_id'] = 0;
                }
                $data['wx_id'] = $weixin['wx_id'];
                if($article_id = K::M('weixin/msite/article')->create($data)){
                    $this->err->add('添加文章成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/weixin/msite/article:index'));
                }
            }
        }else{
            $this->pagedata['cate_list'] = $cate_list;            
            $this->tmpl = 'ucenter/weixin/msite/article/create.html';
        }        
    }

    public function edit($article_id=null)
    {
        $weixin = $this->ucenter_weixin();
        $cate_list = K::M('weixin/msite/cate')->options_by_weixin($weixin['wx_id']);
        if(!($article_id = (int)$article_id) && !($article_id = (int)$this->GP('article_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$detail = K::M('weixin/msite/article')->detail($article_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
             $this->err->add('您没有权限修改该内容', 213);
        }else if($data = $this->checksubmit('data')){
            if($this->MEMBER['group']['priv']['allow_msite'] < 1){
                $this->err->add('您是【'.$this->MEMBER['group_name'].'】没有权限使用微网站', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['article_thumb']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'msite')){
                            $data['thumb'] = $a['photo'];
                        }
                    }
                }
                if(isset($data['cat_id']) && !$cate_list[$data['cat_id']]){
                    $data['cat_id'] = 0;
                }
                if(K::M('weixin/msite/article')->update($article_id, $data)){
                    $this->err->add('修改文章成功');
                }
            }
        }else{
            $this->pagedata['cate_list'] = $cate_list;
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/weixin/msite/article/edit.html';
        }          
    }

    public function delete($article_id=null)
    {
        $weixin = $this->ucenter_weixin();
        if(!($article_id = (int)$article_id) && !($article_id = $this->GP('article_id'))){
            $this->err->add('未指要删除的文章', 211);
        }else if(!$detail = K::M('weixin/msite/article')->detail($article_id)){
            $this->err->add('你要删除的文章不存或已经删除', 212);
        }else if($weixin['wx_id'] != $detail['wx_id']){
            $this->err->add('您没有权限删除该文章', 213);
        }else{
            K::M('weixin/msite/article')->delete($article_id);
            $this->err->add('删除文章成功');
        }         
    }

}