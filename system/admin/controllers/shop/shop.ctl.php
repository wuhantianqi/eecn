<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: shop.ctl.php 5867 2014-07-12 02:04:39Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Shop extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['shop_id']){
                $filter['shop_id'] = $SO['shop_id'];
            }else if($SO['uid']){
                $filter['uid'] = $SO['uid'];
            }else{                
                if($SO['area_id']){
                    $filter['area_id'] = $SO['area_id'];
                }else if($SO['city_id']){
                    $filter['city_id'] = $SO['city_id'];
                }
                if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
                if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
        }
        $filter['closed'] = 0;
		if(CITY_ID){
			$filter['city_id'] = CITY_ID;
		}
        if($items = K::M('shop/shop')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
            $uids = array();
            foreach($items as $k=>$v){
                if($v['uid']){
                    $uids[$v['uid']] = $v['uid'];
                }
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $this->pagedata['city_list'] = K::M('data/city')->fetch_all();
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/shop/items.html';
    }

    public function so($target=null, $multi=null)
    {
        if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
        }
        $this->pagedata['pager'] = $pager;          
        $this->tmpl = 'admin:shop/shop/so.html';
    }

    public function dialog($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['shop_id']){
                $filter['shop_id'] = $SO['shop_id'];
            }else if($SO['uid']){
                $filter['uid'] = $SO['uid'];
            }else{
                if($SO['area_id']){
                    $filter['area_id'] = (int)$SO['area_id'];
                }else if($SO['city_id']){
                    $filter['city_id'] = (int)$SO['city_id'];
                }
                if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
                if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
        }
        $filter['closed'] = 0;
		if(CITY_ID){
			$filter['city_id'] = CITY_ID;
		}
        if($items = K::M('shop/shop')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO, 'multi'=>$multi));
            $uids = array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
            }
            $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['city_list'] = K::M("data/city")->fetch_all();
        $this->tmpl = 'admin:shop/shop/dialog.html';           
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']){
                    if($photos = $this->__upload($_FILES['data'])){
                        $data = array_merge($data, $photos);
                    }
                }
				if(CITY_ID){
					$data['city_id'] = CITY_ID;
				}
                if($shop_id = K::M('shop/shop')->create($data)){
                    if($data['uid'] && isset($data['group_id'])){
                        K::M('member/member')->update($data['uid'], array('group_id'=>(int)$data['group_id']), true);
                    }
                    if($fields = $this->GP('fields')){
                        if($_FILES['fields']){
                            if($photos = $this->__upload($_FILES['fields'])){
                                $fields = array_merge($fields, $photos);
                            }
                        }
                        K::M('shop/fields')->update($shop_id, $fields);
                    }
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/shop-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/shop/create.html';
        }
    }

    public function edit($shop_id=null)
    {
        if(!($shop_id = (int)$shop_id) && !($shop_id = $this->GP('shop_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('shop/shop')->detail($shop_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']){
                    if($photos = $this->__upload($_FILES['data'], $detail)){
                        $data = array_merge($data, $photos);
                    }
                }
				unset($data['city_id']);
                if(K::M('shop/shop')->update($shop_id, $data)){
                    if($data['uid'] && isset($data['group_id'])){
                        K::M('member/member')->update($data['uid'], array('group_id'=>(int)$data['group_id']), true);
                    }
                    if($fields = $this->GP('fields')){
                        if($_FILES['fields']){
                            if($photos = $this->__upload($_FILES['fields'], $detail)){
                                $fields = array_merge($fields, $photos);
                            }
                        }
                        K::M('shop/fields')->update($shop_id, $fields);
                    }                    
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
            if($uid = (int)$detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
        	$this->tmpl = 'admin:shop/shop/edit.html';
        }
    }

    public function doaudit($shop_id=null)
    {
        if($shop_id = (int)$shop_id){
            if(K::M('shop/shop')->batch($shop_id, array('audit'=>1))){
                $this->err->add('审核商铺成功');
            }
        }else if($ids = $this->GP('shop_id')){
            if(K::M('shop/shop')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核商铺成功');
            }
        }else{
            $this->err->add('未指定要审核商铺', 401);
        }
    }

     public function vip($shop_id = null){
         if ($shop_id = (int) $shop_id) {
             if($detail = K::M('shop/shop')->detail($shop_id)){
                $data['is_vip'] = empty($detail['is_vip']) ? 1 : 0;
                if (K::M('shop/shop')->update($shop_id,$data)) {
                    $this->err->add('设置成功');
                }else{
                     $this->err->add('设置失败');
                }
             }else{
                  $this->err->add('未指定要设为旗舰的ID', 401);
             }             
         }else{
              $this->err->add('未指定要设为旗舰的ID', 401);
         }  
    }    
    
    public function delete($shop_id=null)
    {
        if($shop_id = (int)$shop_id){
            if(K::M('shop/shop')->delete($shop_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('shop_id')){
            if(K::M('shop/shop')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    protected function __upload($data, $detail=null)
    {
        $attachs = $photos = array();
        foreach($data as $k=>$v){
            foreach($v as $kk=>$vv){
                $attachs[$kk][$k] = $vv;
            }
        }
        $upload = K::M('magic/upload');
        $cfg = K::$system->config->get('attach');
        foreach($attachs as $k=>$attach){
            if($attach['error'] == UPLOAD_ERR_OK){
                $source = null;
                if($detail && isset($detail[$k]) && substr($data[$k], 0, 5) == 'shop/'){
                    $source = $detail[$k];
                }
                if($a = $upload->upload($attach, 'shop', $source)){
                    $photos[$k] = $a['photo'];
                    if ($k === 'logo') {
                        $size['photo'] = $cfg['shop']['logo'] ? $cfg['shop']['logo'] : '200X100';
                    } else if($k === 'thumb') {
                        $size['photo'] = $cfg['shop']['thumb'] ? $cfg['shop']['thumb'] : '200X200';
                    }
                    if($size){
                        K::M('image/gd')->thumbs($a['file'], array($size['photo'] => $a['file']), false);
                    }
                }
            }
        }
        return $photos;
    }

}