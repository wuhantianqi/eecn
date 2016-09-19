<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Youhui extends Ctl_Mobile
{
    
    public function index($page = 1)
    {   
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 6;
        $filter['city_id'] = $this->request['city_id'];
        $filter['audit'] = 1;
        if($company_id){
            $filter['company_id'] =$company_id;
        }else{
            $filter['company_id'] = ">:0";
        }
        if ($items = K::M('company/youhui')->items($filter, array('youhui_id'=>'DESC'), $page, $limit, $count)) {
            $counts = 0; 
            foreach ($items as $k => $v) {
                $counts++;
                $items[$k]['count'] = $counts;
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
        }

        $this->pagedata['items'] = $items;
        $pager['backurl'] = $this->mklink('mobile');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/youhui/items.html';
    }

    public function detail($youhui_id)
    {
        if(!$youhui_id = (int)$youhui_id){
            $this->error(404);
        }else if(!$detail = K::M('company/youhui')->detail($youhui_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else if(!$company = K::M('company/company')->detail($detail['company_id'])){
            $this->err->add("没有找到该优惠信息", 211)->response();
        }else{      
            $this->pagedata['detail'] = $detail;
            $pager['backurl'] = $this->mklink('mobile/company:youhui', array($detail['company_id']));
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'mobile/youhui/detail.html';
        }
    }

    public function sign($youhui_id=null)
    {
        if(!($youhui_id = (int)$youhui_id) && !($youhui_id = $this->GP('youhui_id'))){
            $this->error(404);
        }else if(!$detail = K::M('company/youhui')->detail($youhui_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }elseif($data= $this->checksubmit('data')){
            if(!$data = $this->check_fields($data,'contact,mobile')){
               $this->error(404);
            }else{
                $data['uid'] = (int)$this->uid;
                $data['company_id'] = $detail['company_id'];
                $data['youhui_id'] = $youhui_id;
                $data['city_id'] = $this->request['city_id'];
                if($id = K::M('company/sign')->create($data)){
                    K::M('company/youhui')->update_count($youhui_id, 'sign_num', 1);
                    $smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'youhui'=>$detail['title']);
                    K::M('sms/sms')->send($detail['mobile'], 'youhui_tongzhi', $smsdata);
                    K::M('helper/mail')->sendmail($detail['mail'], 'youhui_yuyue', $maildata);
                    $this->err->add('恭喜您报名成功！');
                    $this->err->set_data('forward', $this->mklink('mobile/youhui/detail', array('youhui_id'=>$youhui_id)));
                }
            } 
        }else{
			
			$pager['tender_hide'] = 1;
            $pager['backurl'] = $this->mklink('mobile/youhui/detail',array('youhui_id'=>$youhui_id));
            $this->pagedata['pager'] = $pager;
            $this->pagedata['youhui_id'] = $youhui_id;
            $this->tmpl = 'mobile/youhui/sign.html'; 
        }  
    }    

}