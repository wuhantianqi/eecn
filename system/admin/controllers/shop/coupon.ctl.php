<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: coupon.ctl.php 6097 2014-08-15 12:55:34Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Coupon extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['coupon_id']){$filter['coupon_id'] = $SO['coupon_id'];}
            if($SO['shop_id']){$filter['shop_id'] = $SO['shop_id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
            if(is_array($SO['stime'])){if($SO['stime'][0] && $SO['stime'][1]){$a = strtotime($SO['stime'][0]); $b = strtotime($SO['stime'][1])+86400;$filter['stime'] = $a."~".$b;}}
            if(is_array($SO['ltime'])){if($SO['ltime'][0] && $SO['ltime'][1]){$a = strtotime($SO['ltime'][0]); $b = strtotime($SO['ltime'][1])+86400;$filter['ltime'] = $a."~".$b;}}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        $filter['closed'] = 0;
        if($items = K::M('shop/coupon')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $shop_ids = array();
            foreach($items as $k=>$v){
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
            $this->pagedata['city_list'] = K::M('data/city')->fetch_all();
            $this->pagedata['items'] = $items;
        }        
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/coupon/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:shop/coupon/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'shop')){
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }
				
				if($shop = K::M('shop/shop')->detail($data['shop_id'])){
					$data['city_id'] = $shop['city_id'];
				}
                if($coupon_id = K::M('shop/coupon')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/coupon-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/coupon/create.html';
        }
    }

    public function edit($coupon_id=null)
    {
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/coupon')->detail($coupon_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'shop')){
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }
				if($shop = K::M('shop/shop')->detail($data['shop_id'])){
					$data['city_id'] = $shop['city_id'];
				}
                if(K::M('shop/coupon')->update($coupon_id, $data)){
                    $this->err->add('修改内容成功');
                }
            } 
        }else{
            if($shop_id = $detail['shop_id']){
                $this->pagedata['shop'] = K::M('shop/shop')->detail($shop_id);
            }
            $this->pagedata['detail'] = $detail;
			$this->pagedata['city_list'] = K::M("data/city")->fetch_all();
            $this->tmpl = 'admin:shop/coupon/edit.html';
        }
    }

    public function doaudit($coupon_id=null)
    {
        if($coupon_id = (int)$coupon_id){
            if(K::M('shop/coupon')->batch($coupon_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('coupon_id')){
            if(K::M('shop/coupon')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($coupon_id=null)
    {
        if($coupon_id = (int)$coupon_id){
            if(K::M('shop/coupon')->delete($coupon_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('coupon_id')){
            if(K::M('shop/coupon')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function downloadSo()
    {
        $this->tmpl = 'admin:shop/coupon/download/so.html';
    }

    public function downloads($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['download_id']){
                $filter['download_id'] = $SO['download_id'];
            }else{
                if($SO['coupon_id']){
                    $filter['coupon_id'] = $SO['coupon_id'];
                }else if($SO['shop_id']){
                    $filter['shop_id'] = $SO['shop_id'];
                }
                if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
                if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
                if($SO['number']){$filter['number'] = "LIKE:%".$SO['number']."%";}
                if(is_numeric($SO['used'])){$filter['used'] = $SO['used'];}
                if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
        }
        if($items = K::M('shop/couponDownload')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $coupon_ids = $shop_ids = array();
            foreach($items as $k=>$v){
                $coupon_ids[$v['coupon_id']] = $v['coupon_id'];
                if($v['shop_id']){
                    $shop_ids[$v['shop_id']] = $v['shop_id'];
                }
            }
            if($coupon_ids){
                $this->pagedata['coupon_list'] = K::M('shop/coupon')->items_by_ids($coupon_ids);
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }            
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/coupon/download/items.html';
    }

    public function downloadCoupon($coupon_id=null)
    {
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('未指定要查看的内容ID', 211);
        }else if(!$coupon = K::M('shop/coupon')->detail($coupon_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            if($items = K::M('shop/couponDownload')->items_by_coupon($coupon_id, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['coupon'] = $coupon;            
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'admin:shop/coupon/download/coupon.html';
        }
    }

    public function downloadEdit($download_id=null)
    {
        if(!($download_id = (int)$download_id) && !($download_id = $this->GP('download_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/couponDownload')->detail($download_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('shop/couponDownload')->update($download_id, $data)){
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['coupon'] = K::M('shop/coupon')->detail($detail['coupon_id']);
            $this->pagedata['shop'] = K::M('shop/shop')->detail($detail['shop_id']);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:shop/coupon/download/edit.html';
        }
    }

    public function downloadDelete($download_id=null)
    {
        if($download_id = (int)$download_id){
            if(K::M('shop/couponDownload')->delete($download_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('download_id')){
            if(K::M('shop/couponDownload')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }        
    }

}