<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Shop_mendian extends Ctl_Ucenter
{
    protected $_allow_fields = 'title,desc,addr,phone,contact,thumb,content';
    public function index()
    {
        $shop = $this->ucenter_shop();
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter = array('shop_id'=>$shop['shop_id'], 'closed'=>0);
        if($items = K::M('shop/mendian')->items($filter, null, $page, $limit, $count)){
            $pgaer['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/shop/mendian/items.html';
    }

    public function create()
    {
        $shop = $this->ucenter_shop();
        if($data = $this->checksubmit('data')){
            $allow_mendian = K::M('member/group')->check_priv($shop['group_id'], 'allow_mendian');
            if($allow_mendian < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限添加商铺门店', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
				if($_FILES['data']){
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attach[$k] = $vv;
                        }
                    }
                    if($thumb = K::M('magic/upload')->upload($attach)){
                    $data['thumb'] = $thumb['photo'];
                    }
                } 
                $data['city_id'] = $shop['city_id'];
                $data['shop_id'] = $shop['shop_id'];
                $data['audit'] = $allow_mendian;
                if($mendian_id = K::M('shop/mendian')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/shop/mendian:index'));
                }
            }
        }else{
            $this->pagedata['pager'] = $pager;            
            $this->tmpl = 'ucenter/shop/mendian/create.html';
        }
    }

    public function edit($mendian_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($mendian_id = (int)$mendian_id) && !($mendian_id = (int)$this->GP('mendian_id'))){
            $this->err->add('未定义操作', 211);
        }else if(!$detail = K::M('shop/mendian')->detail($mendian_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($shop['shop_id'] != $detail['shop_id']){
             $this->err->add('您没有权限修改该内容', 213);
        }else if($data = $this->checksubmit('data')){
            if($_FILES['data']['name']['thumb']){
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attach[$k] = $vv;
                        }
                    }
                    if($thumb = K::M('magic/upload')->upload($attach)){
                    $data['thumb'] = $thumb['photo'];
                    }
                } 
            if(K::M('member/group')->check_priv($shop['group_id'], 'allow_mendian') < 0){
                $this->err->add('您是【'.$shop['group_name'].'】没有权限修改门店信息', 333);
            }else if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 201);
            }else if(K::M('shop/mendian')->update($mendian_id, $data)){
                $this->err->add('修改内容成功');
            } 
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/shop/mendian/edit.html';
        }
    }

    public function delete($mendian_id=null)
    {
        $shop = $this->ucenter_shop();
        if(!($mendian_id = (int)$mendian_id) && !($mendian_id = $this->GP('mendian_id'))){
            $this->err->add('未指要删除的资讯', 211);
        }else if(!$detail = K::M('shop/mendian')->detail($mendian_id)){
            $this->err->add('你要删除的门店不存或已经删除', 212);
        }else if($shop['shop_id'] != $detail['shop_id']){
            $this->err->add('您没有权限删除该门店', 213);
        }else{
            K::M('shop/mendian')->delete($mendian_id);
            $this->err->add('删除门店成功');
        }        
    }

}