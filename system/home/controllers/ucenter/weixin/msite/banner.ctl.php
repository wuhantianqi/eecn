<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Msite_Banner extends Ctl_Ucenter 
{
    
    public function index()
    {
        $weixin = $this->ucenter_weixin();
        $count = 0;
        if($items = K::M('weixin/msite/banner')->items_by_weixin($weixin['wx_id'])){
            $this->pagedata['items'] = $items;
            $count = count($items);
        }
        $pager = array('count'=>$count);
        $this->pagedata['pager'] = $pager;        
        $this->tmpl = 'ucenter/weixin/msite/banners.html';             
    }

    public function upload()
    {
        $weixin = $this->ucenter_weixin();
         if($this->MEMBER['group']['priv']['allow_msite'] < 1){
            $this->err->add('您是【'.$this->MEMBER['group_name'].'】不能使用微网站', 333);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else if(K::M('weixin/msite/banner')->count(array('wx_id'=>$weixin['wx_id'])) > 5){
            $this->err->add('轮转广告最多只可上传5张图片', 214);
        }else{
            if($a = K::M('weixin/msite/banner')->upload($weixin['wx_id'], $attach)){
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('thumb', $cfg['attachurl'].'/'.$a['photo']);
                $this->err->add('上传图片成功');
            }
        }
        $this->err->json(); 
    }

    public function update()
    {
        $weixin = $this->ucenter_weixin();
        if($data = $this->checksubmit('data')){
            if($banners = K::M('weixin/msite/banner')->items_by_weixin($weixin['wx_id'])){
                foreach($banners as $k=>$v){
                    if($a = $data[$k]){
                        if($a['title'] != $v['title'] || $a['link'] != $v['link'] || $a['orderby'] != $v['orderby']){
                            K::M('weixin/msite/banner')->update($k, $a);
                        }
                    }
                }
            }
            $this->err->add('更新轮转广告成功');
        }
    }

    public function delete($photo_id=null)
    {
        $weixin = $this->ucenter_weixin();
        if(!$photo_id = (int)$photo_id){
            $this->err->add('未定义操作', 211);
        }else if(!$banner = K::M('weixin/msite/banner')->detail($photo_id)){
            $this->err->add('您要删除的内容不存在或已经删除', 212);
        }else if($banner['wx_id'] != $weixin['wx_id']){
            $this->err->add('非法的数据提交', 213);
        }else if(K::M('weixin/msite/banner')->delete($photo_id)){
            $this->err->add('删除轮转广告成功');
        }       
    }

}