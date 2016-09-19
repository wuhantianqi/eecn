<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company_Team extends Ctl_Ucenter 
{
    
    public function index($page=1)
    {
        $company = $this->ucenter_company();
        $filter = $pager = array();
        $pager['page'] = $page = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['closed'] = 0;
        $filter['company_id'] = $company['company_id'];
        if($items = K::M('designer/designer')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }        
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/company/team/items.html';
    }

    public function bind()
    {
        $company = $this->ucenter_company();
        if($data = $this->checksubmit('data')){
            if(!$member = K::M('member/member')->member($data['uname'], 'uname')){
                $this->err->add('用户名密码错误', 201);               
            }else if($member['passwd'] != md5($data['passwd'])){
                $this->err->add('用户名密码错误', 201);          
            }else if($member['from'] != 'designer'){
                $this->err->add('用户名密码错误', 201);          
            }else if(!$designer = K::M('designer/designer')->detail($member['uid'])){
                $this->err->add('用户名密码错误', 201); 
            }else if(!empty($designer['company_id'])) {
                $this->err->add('该设计师已经被绑定过了', 201); 
            }else{
                if(K::M('designer/designer')->update($member['uid'],array('company_id'=>$company['company_id']),true)){
                    $this->err->add('操作成功'); 
                }else{
                    $this->err->add('更新失败', 201); 
                }
            }
        }else{
            $this->tmpl = 'ucenter/company/team/bind.html';
        }
    }

    public function unbind($uid=null)
    {
        $company = $this->ucenter_company();
        if(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->error(404);
        }else if(!$detail = K::M('designer/designer')->detail($uid)){
            $this->err->add('设计师不存在或已经删除', 212);
        }elseif($detail['company_id'] != $company['company_id']){
            $this->err->add('您无权解雇该童鞋', 212);
        }else{
            if(K::M('designer/designer')->update($uid,array('company_id'=>0),true)){
                $this->err->add('操作成功'); 
            }else{
                $this->err->add('更新失败', 201); 
            }
        }        
    }
}