<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: mechanic.ctl.php 3304 2014-02-14 11:01:43Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mechanic_Mechanic extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uname']){$filter['member.uname'] = $SO['uname'];}
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['qq']){$filter['qq'] = "LIKE:%".$SO['qq']."%";}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
        }
        $filter['closed'] = 0;
        $filter['city_id'] = CITY_ID;    
        if($items = K::M('mechanic/mechanic')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
           
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
		if($this->fenzhan->role['priv']['430']){
			$this->pagedata['czjb_show'] = 1;
		}
        $this->pagedata['city_list'] = K::M("data/city")->fetch_all();
        $this->pagedata['area_list'] = K::M("data/area")->fetch_all();       
        $this->tmpl = 'fenzhan:mechanic/mechanic/items.html';
    }

    public function so($target=null, $multi=null)
    {
        if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
        }
        $this->pagedata['pager'] = $pager;  
        $this->tmpl = 'fenzhan:mechanic/mechanic/so.html';
    }

    public function edit($uid=null)
    {
        if(!($uid = (int)$uid) && !($uid = $this->GP('uid'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('mechanic/mechanic')->detail($uid)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                unset($data['city_id']); 
                if(K::M('mechanic/mechanic')->update($uid, $data)){
                    if($attr=  $this->GP('attr')){
                        K::M('mechanic/attr')->update($uid,$attr);       
                    }
                    $this->err->add('修改内容成功');
                }  
            }
        }else{
            $this->pagedata['attr'] = K::M('mechanic/attr')->attrs_ids_by_mechanic($uid);
            $this->pagedata['detail'] = $detail;
            $this->pagedata['member'] = K::M('member/member')->detail($detail['uid']);
            $this->tmpl = 'fenzhan:mechanic/mechanic/edit.html';
        }
    }

    public function delete($uid=null)
    {
         if($uid = (int)$uid){
            if (!$detail = K::M('mechanic/mechanic')->detail($uid)) {
                $this->err->add('您要修改的内容不存在或已经删除', 212);
            }elseif(K::M('mechanic/mechanic')->delete($uid)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('uid')){
          
            if (K::M('mechanic/mechanic')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function doaudit($uid=null)
    {
        if($uid = (int)$uid){
            if (!$detail = K::M('mechanic/mechanic')->detail($uid)) {
                $this->err->add('您要修改的内容不存在或已经删除', 212);
            }  elseif(K::M('mechanic/mechanic')->batch($uid, array('audit'=>1))){
                $this->err->add('审核成功');
            }
        }else if($uids = $this->GP('uid')){
            
            if (K::M('mechanic/mechanic')->batch($uids, array('audit'=>1))){
                $this->err->add('批量审核成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

}