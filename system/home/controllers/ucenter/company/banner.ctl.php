<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company_Banner extends Ctl_Ucenter 
{
    
    public function index()
    {
        $company = $this->ucenter_company();
        $allow_banner = K::M('member/group')->check_priv($company['company_id'], 'allow_banner');
        $count = 0;
        if($items = K::M('company/banner')->items_by_company($company['company_id'])){
            $this->pagedata['items'] = $items;
            $count = count($items);
        }
        $pager = array('count'=>$count);
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/company/banners.html';
    }

    public function upload()
    {
        $company = $this->ucenter_company();
        $allow_banner = K::M('member/group')->check_priv($company['group_id'], 'allow_banner');
        if($allow_banner < 0){
            $this->err->add('您是【'.$company['group_name'].'】不能上传轮转广告', 333);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else if(K::M('company/banner')->count_by_company($company['company_id']) > 5){
            $this->err->add('轮转广告最多只可上传5张图片', 214);
        }else{
            if($a = K::M('company/banner')->upload($company['company_id'], $attach)){
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('thumb', $cfg['attachurl'].'/'.$a['pic']);
                $this->err->add('上传图片成功');          
            }
        }
        $this->err->json();
    }

    public function update()
    {
        $company = $this->ucenter_company();
        $allow_banner = K::M('member/group')->check_priv($company['group_id'], 'allow_banner');
        if($allow_banner < 0){
            $this->err->add('您是【'.$company['group_name'].'】不能上传轮转广告', 333);
        }else if($data = $this->checksubmit('data')){
            if($banners = K::M('company/banner')->items_by_company($company['company_id'])){
                foreach($banners as $k=>$v){
                    if($a = $data[$k]){
                        if($a['title'] != $v['title'] || $a['link'] != $v['link'] || $a['orderby'] != $v['orderby']){
                            K::M('company/banner')->update($k, $a);
                        }
                    }
                }
            }
            $this->err->add('更新轮转广告成功');
        }
    }

    public function delete($banner_id=null)
    {
        $company = $this->ucenter_company();
        if(!$banner_id = (int)$banner_id){
            $this->err->add('未定义操作', 211);
        }else if(!$banner = K::M('company/banner')->detail($banner_id)){
            $this->err->add('您要删除的内容不存在或已经删除', 212);
        }else if($banner['company_id'] != $company['company_id']){
            $this->err->add('非法的数据提交', 213);
        }else if(K::M('company/banner')->delete($banner_id)){
            $this->err->add('删除轮转广告成功');
        }       
    }
}