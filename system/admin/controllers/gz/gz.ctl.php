<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Gz_Gz extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if(is_array($SO['lasttime'])){if($SO['lasttime'][0] && $SO['lasttime'][1]){$a = strtotime($SO['lasttime'][0]); $b = strtotime($SO['lasttime'][1])+86400;$filter['lasttime'] = $a."~".$b;}}
            if($SO['audit']){$filter['audit'] = $SO['audit'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        $filter['closed'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('gz/gz')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:gz/gz/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:gz/gz/so.html';
    }

    public function dialog($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if ($SO['uid']) {$filter['uid'] = $SO['uid'];}
            if(!isset($filter['area_id'])){
                if ($SO['city_id']) {$filter['city_id'] = $SO['city_id'];}
            }
            if ($SO['name']) {$filter['name'] = "LIKE:%" . $SO['name'] . "%";}
            if ($SO['mobile']) {$filter['mobile'] = "LIKE:%" . $SO['mobile'] . "%";}
            if (is_numeric($SO['audit'])) {$filter['audit'] = $SO['audit'];}
        }
        $filter['closed'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('gz/gz')->items($filter, null, $page, $limit, $count)){
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
        $this->tmpl = 'admin:gz/gz/dialog.html';            
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else if(!$account = $this->GP('account')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
                if($uid = K::M('gz/gz')->create($data, $account)){
					if($uid && isset($data['group_id'])){
                        K::M('member/member')->update($uid, array('group_id'=>(int)$data['group_id']), true);
                    }
					if($attr=  $this->GP('attr')){
                        K::M('gz/attr')->update($uid,$attr);       
                    }
                    $this->err->add('添加工长成功');
                    $this->err->set_data('forward', '?gz/gz-index.html');
                }
            }
        }else{
            $this->system->config->load('score');
            $this->tmpl = 'admin:gz/gz/create.html';
        }
    }

    public function edit($uid=null)
    {
        if(!($uid = (int)$uid) && !($uid = $this->GP('uid'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('gz/gz')->detail($uid)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if(K::M('gz/gz')->update($uid, $data)){
                    if(isset($data['group_id'])){
                        K::M('member/member')->update($uid, array('group_id'=>(int)$data['group_id']), true);
                    }                    
					if($attr=  $this->GP('attr')){
                        K::M('gz/attr')->update($uid,$attr);       
                    }
                    $this->err->add('修改内容成功');
                }  
            }
        }else{
			$this->pagedata['attr'] = K::M('gz/attr')->attrs_ids_by_gz($uid);
            $this->system->config->load('score');
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:gz/gz/edit.html';
        }
    }

    public function doaudit($uid=null)
    {
        if($uid = (int)$uid){
            if(K::M('gz/gz')->batch($uid, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('uid')){
            if(K::M('gz/gz')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($uid=null)
    {
        if($uid = (int)$uid){
            if(K::M('gz/gz')->delete($uid)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('uid')){
            if(K::M('gz/gz')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
}