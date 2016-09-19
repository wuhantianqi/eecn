<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Misc_Select extends Ctl_Ucenter
{
    
    public function home($city_id=null, $page=1)
    {
        $filter = $pager = $params = array();
        if(!$city_id = (int)$city_id){
            $city_id = $this->ucenter_city_id;
        }
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['city_id'] = $city_id;
        $pager['multi'] = $params['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            if($SO['area_id']){
                $filter['area_id'] = $SO['area_id'];
            }else if($city_id){
                $filter['city_id'] = $city_id;
            }
            if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
            $params['SO'] = $pager['SO'] = $SO;
        }
        $filter['closed'] = 0;
        if($items = K::M('home/home')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($city_id, '{page}'), array(), 'base'), $params);
            $this->pagedata['items'] = $items;
        }
        if(!$city = K::M('data/city')->city($city_id)){
            $city = $this->request['city'];
        }
        $pager['city'] = $city;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'view:select/home.html'; 
    }

    public function company($page=null)
    {
        $filter = $pager = $params = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        if(!$city_id = (int)$this->GP('city_id')){
            $city_id = $this->ucenter_city_id;
        }
        $pager['city_id'] = $city_id;
        $pager['multi'] = $params['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            if($SO['area_id']){
                $filter['area_id'] = $SO['area_id'];
            }else if($city_id){
                $SO['city_id'] = $params['city_id'] = $filter['city_id'] = $pager['city_id'];
            }else if($SO['city_id']){
                $filter['city_id'] = $SO['city_id'];
            }
            if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
            $params['SO'] = $pager['SO'] = $SO;
        }
        $filter['closed'] = 0;
        if($items = K::M('company/company')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), $params);
        }        
        if(!$city = K::M('data/city')->city($city_id)){
            $city = $this->request['city'];
        }
        $pager['city'] = $city;
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'view:select/company.html'; 
    }

    /**
     * 需要传递小区ID
     */
    public function huxing($home_id, $page=1)
    {

        if(!($home_id = (int)$home_id) && !($home_id = (int)$this->GP('home_id'))){
            $this->err->add('未指定户型图的小区', 211);
        }else if(!$home = K::M('home/home')->detail($home_id)){
            $this->err->add('指定的小区不存在或已经删除', 211);
        }else{
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 3;
            $pager['multi'] = $params['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
            $filter['home_id'] = $home_id;
            $filter['type'] = 1;
            if($items = K::M('home/photo')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($home_id, '{page}')), $params);
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['home'] = $home;
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'view:select/huxing.html';
        }
    }

    public function mycase($page=1)
    {
        $this->check_login();
        $filter = $pager = array();
        $pager['page'] = $page = max(intval($page), 1);
        $pager['limit'] = $limit = 10;    
        $pager['count'] = $count = 0;
        if($this->MEMBER['from'] == 'company' && ($company = K::M('company/company')->company_by_uid($this->MEMBER['uid']))){
            $filter = array('audit'=>1, 'closed'=>0);
            $filter[':OR'] = array('uid'=>$this->MEMBER['uid'],'company_id'=>$company['company_id']);
        }else{
            $filter = array('uid'=>$this->MEMBER['uid'], 'audit'=>1, 'closed'=>0);
         }       
        if($items = K::M('case/case')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;            
        }
        $this->tmpl = 'view:select/mycase.html';
    }
}