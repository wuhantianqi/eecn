<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company_Photo extends Ctl_Ucenter 
{
    protected $_allow_fields = 'type,title';
    public function index($page=1)
    {
        $company = $this->ucenter_company();
        $pager['page'] = $page = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        if($items = K::M('company/photo')->items_by_company($company['company_id'], $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->pagedata['type_list'] = K::M('company/photo')->get_type_means();
        $this->tmpl = 'ucenter/company/photo/items.html';
    }

    public function create()
    {
        $company = $this->ucenter_company();
        if ($data = $this->checksubmit('data')) {
            if (!$data = $this->check_fields($data, $this->_allow_fields)) {
                $this->err->add('非法的数据提交', 201);
            } else {
                if ($attach = $_FILES['pic_photo']) {
                    if ($attach['error'] == UPLOAD_ERR_OK) {
                        if ($a = K::M('magic/upload')->upload($attach, 'company')) {
                            $data['photo'] = $a['photo'];
                        }
                    }
                }
                $data['company_id'] = $company['company_id'];
                if ($pic_id = K::M('company/photo')->create($data)) {
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward',  $this->mklink('ucenter/company/photo:index'));
                }
            }
        } else {
            $this->pagedata['type_list'] = K::M('company/photo')->get_type_means();
            $this->tmpl = 'ucenter/company/photo/create.html';
        }
    }

    public function update()
    {
        $company = $this->ucenter_company();
        if($data = $this->checksubmit('data')){
            $pic_ids = array();
            foreach($data as $k=>$v){
                $pic_ids[$k] = $k;
            }
            if($items = K::M('company/photo')->items_by_ids($pic_ids)){
                foreach($items as $k=>$v){
                    if($a = $data[$k]){
                        if($v['company_id'] == $company['company_id']){
                            if($a['title'] != $v['title'] || $a['type'] != $v['type']){
                                K::M('company/photo')->update($k, array('type'=>$a['type'], 'title'=>$a['title']));
                            }
                        }
                    }
                }
            }           
        }
    }

    public function delete($pic_id=null)
    {
        $company = $this->ucenter_company();
        if(!$pic_id = (int)$pic_id){
            $this->err->add('未定义操作', 211);
        }else if(!$detail = K::M('company/photo')->detail($pic_id)){
            $this->err->add('您要删除的内容不存在或已经删除', 212);
        }else if($detail['company_id'] != $company['company_id']){
            $this->err->add('非法的数据提交', 213);
        }else if(K::M('company/photo')->delete($pic_id)){
            $this->err->add('删除成功');
        }
    }

}