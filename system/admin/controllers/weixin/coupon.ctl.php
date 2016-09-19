<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Coupon extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
                if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['coupon_id']){$filter['coupon_id'] = $SO['coupon_id'];}
if($SO['keyword']){$filter['keyword'] = "LIKE:%".$SO['keyword']."%";}
if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
        }
        if($items = K::M('weixin/coupon')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:weixin/coupon/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:weixin/coupon/so.html';
    }

    public function detail($coupon_id = null)
    {
        if(!$coupon_id = (int)$coupon_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:weixin/coupon/detail.html';
        }
    }

    public function create()
    {
        if($data = $this->checksubmit('data')){
			if($_FILES['data']){
				foreach($_FILES['data'] as $k=>$v){
					foreach($v as $kk=>$vv){
						$attachs[$kk][$k] = $vv;
					}
				}
				$upload = K::M('magic/upload');
				foreach($attachs as $k=>$attach){
					if($attach['error'] == UPLOAD_ERR_OK){
						if($a = $upload->upload($attach, 'weixin')){
							$data[$k] = $a['photo'];
						}
					}
				}
			}
			$wenxin = K::M('weixin/weixin')->items(array('wx_sid'=>$data['wx_id']));
			if(!$items = K::M('weixin/keyword')->items(array('keyword'=>$data['keyword'],'wx_id'=>$data['wx_id']))){
				if($coupon_id = K::M('weixin/coupon')->create($data)){
					$keyword = array();
					foreach($wenxin as $k => $v){
						$keyword['wx_id'] = $v['wx_id'];	
					}
					$keyword['wx_sid'] = $data['wx_id'];
					$keyword['keyword'] = $data['keyword'];
					$keyword['plugin'] = 'coupon:'.$coupon_id;
					K::M('weixin/keyword')->create($keyword);
					$this->err->add('添加内容成功');
					$this->err->set_data('forward', '?weixin/coupon-index.html');
				} 
			}else{
				$this->err->add('该关键字已经被使用，请修改关键字', 212);
			}
        }else{
           $this->tmpl = 'admin:weixin/coupon/create.html';
        }
    }

    public function edit($coupon_id=null)
    {
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
                    if($_FILES['data']){
            foreach($_FILES['data'] as $k=>$v){
                foreach($v as $kk=>$vv){
                    $attachs[$kk][$k] = $vv;
                }
            }
            $upload = K::M('magic/upload');
            foreach($attachs as $k=>$attach){
                if($attach['error'] == UPLOAD_ERR_OK){
                    if($a = $upload->upload($attach, 'weixin')){
                        $data[$k] = $a['photo'];
                    }
                }
            }
        }

            if(K::M('weixin/coupon')->update($coupon_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:weixin/coupon/edit.html';
        }
    }

    public function doaudit($coupon_id=null)
    {
        if($coupon_id = (int)$coupon_id){
            if(K::M('weixin/coupon')->batch($coupon_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('coupon_id')){
            if(K::M('weixin/coupon')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($coupon_id=null)
    {
        if($coupon_id = (int)$coupon_id){
            if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
				$keyword = array();
				$wenxin = K::M('weixin/weixin')->items(array('wx_sid'=>$detail['wx_id']));
				foreach($wenxin as $k => $v){
					$keyword['wx_id']=$v['wx_id'];
				}
				$keyword['keyword'] = $detail['keyword'];
                if($items = K::M('weixin/keyword')->items($keyword)){
					if(K::M('weixin/coupon')->delete($coupon_id)){
						foreach($items as $k => $v){
							K::M('weixin/keyword')->delete($v['kw_id']);
						}
						$this->err->add('删除内容成功');
					}
				}else{
					$this->err->add('非法操作');
				}
            }
        }
    }
}