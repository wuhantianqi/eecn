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
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['qq']){$filter['qq'] = "LIKE:%".$SO['qq']."%";}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
        }
        $filter['closed'] = 0;
        if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }    
        if($items = K::M('mechanic/mechanic')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
           
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['city_list'] = K::M("data/city")->fetch_all();
        $this->pagedata['area_list'] = K::M("data/area")->fetch_all();       
        $this->tmpl = 'admin:mechanic/mechanic/items.html';
    }

    public function so($target=null, $multi=null)
    {
        if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
        }
        $this->pagedata['pager'] = $pager;  
        $this->tmpl = 'admin:mechanic/mechanic/so.html';
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
				$account['city_id'] = $data['city_id'];           
                if($mechanic_id = K::M('mechanic/mechanic')->create($data, $account)){
					if($mechanic_id && isset($data['group_id'])){
                        K::M('member/member')->update($mechanic_id, array('group_id'=>(int)$data['group_id']), true);
                    }
                    if($attr=  $this->GP('attr')){
                        K::M('mechanic/attr')->update($mechanic_id,$attr);
                    }
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?mechanic/mechanic-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:mechanic/mechanic/create.html';
        }
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
                if(CITY_ID){
                    unset($data['city_id']);
                }                
                if(K::M('mechanic/mechanic')->update($uid, $data)){
                    if(isset($data['group_id'])){
                        K::M('member/member')->update($uid, array('group_id'=>(int)$data['group_id']), true);
                    }
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
            $this->tmpl = 'admin:mechanic/mechanic/edit.html';
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