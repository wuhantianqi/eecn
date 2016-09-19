<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Msite extends Ctl_Ucenter
{
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->weixin = $this->ucenter_weixin();
        if(!$this->msite = K::M('weixin/msite')->detail($this->weixin['wx_id'])){
            $this->request['ctl'] = 'weixin/msite';
            $this->request['act'] = 'index';
        }
        $this->pagedata['msite'] = $this->msite;
    }

    public function access()
    {
        $this->tmpl = 'ucenter/weixin/msite/access.html';
    }

    public function index()
    {
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, 'title,intro')){
                $this->err->add('非法的数据提交', 211);
            }else{
                if($attach = $_FILES['msite_photo']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'msite')){
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                if($attach = $_FILES['msite_background']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'msite')){
                            $data['background'] = $a['photo'];
                        }
                    }
                }                
                if($this->msite){
                    K::M('weixin/msite')->update($this->msite['wx_id'], $data);
                    $this->err->add('修改微网站成功');
                }else{
                    $data['wx_id'] = $this->weixin['wx_id'];
                    $data['tmpl_index'] = 'V1';
                    $data['tmpl_lists'] = 'V1';
                    $data['tmpl_detail'] = 'V1';
                    if(K::M('weixin/msite')->create($data)){
                        $this->err->add('创建微网站成功');
                    }
                }
            }
        }else{
            $this->tmpl = 'ucenter/weixin/msite/index.html';
        }
    }

    public function tmpl($from='index')
    {
        if(!in_array($from, array('index', 'lists', 'detail'))){
            $this->error(404);
        }
        if($tmpl = $this->checksubmit('template')){
            K::M('weixin/msite')->update($this->msite['wx_id'], array("tmpl_{$from}"=>$tmpl));
            $this->err->add('更换模板成功');
        }else{
            if($tmpl_list = $this->_load_tmpl($from)){
                $this->pagedata['tmpl_list'] = $tmpl_list;
            }
            $pager = array('from'=>$from);
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/weixin/msite/tmpl.html';
        }
    }

    protected function _load_tmpl($from)
    {
        $tmpl_list = array();
        $tmpl_dir = __TPL_DIR.'default/weixin/msite/tmpl/'.$from;
        $d = dir($tmpl_dir);
        while (false !== ($entry = $d->read())) {
            if($entry != '.' && $entry != '..' && is_dir($tmpl_dir.'/'.$entry)){
                if(file_exists($tmpl_dir.'/'.$entry.'/info.php')){
                    $tmpl_list[$entry] = @include($tmpl_dir.'/'.$entry.'/info.php');
                }
            }
        }
        $d->close();
        return $tmpl_list;  
    }
}