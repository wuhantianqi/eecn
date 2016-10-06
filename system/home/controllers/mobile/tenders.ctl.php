<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Mobile_Tenders extends Ctl_Mobile
{
    private  $_tenders_allow_fields ='from,city_id,area_id,contact,mobile,home_name,way_id,style_id,budget_id,service_id,house_type_id,house_mj,addr,comment,zx_time';
    public function index()
    {
		$pager['tender_hide'] = 1;
		$this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/tenders.html';    
    }
    public function ntenders()
    {
        $pager['tender_hide'] = 1;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/newpage/m-tenders.html';    
    }
    public function onlinebj(){
        $this->tmpl = 'mobile/newpage/m-onlinebj.html';    
    }
    public function minutes(){
        $this->tmpl = 'mobile/newpage/three-minutes.html';
    }
    public function daikuan(){
        $this->tmpl = 'mobile/newpage/m-daikuan.html';
    }
    // 管家管家页面
    public function tubehome()
    {
        $this->tmpl = 'mobile/newpage/m-house-keeper.html';
    }
    public function save()
    {
        if($data= $this->checksubmit('data')){
            if(!$data = $this->check_fields($data,$this->_tenders_allow_fields)){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['uid'] = (int)$this->uid;
                if(empty($data['city_id'])){
                    $data['city_id'] = $this->request['city_id'];
                }
                if(empty($data['name']) && ($this->uid)){
                    $data['name'] = $this->MEMBER['uname'];
                }
                $data['city_id'] = empty($data['city_id']) ? $this->request['city_id'] : $data['city_id'];
                if($tenders_id = K::M('tenders/tenders')->create($data)){
                    if($attr = $this->GP('attr')){
                        K::M('tenders/attr')->update($tenders_id, $attr);
                    }
                    $smsdata = $maildata = array('contact'=>$data['name'] ? $data['name'] : '业主','mobile'=>$data['mobile']);
                    K::M('sms/sms')->send($data['mobile'], 'tenders', $smsdata);
                    K::M('sms/sms')->admin('admin_tenders', $smsdata);
                    K::M('helper/mail')->sendadmin('admin_tenders',$maildata);
                    if($this->uid){
                        $this->err->set_data('forward',  $this->mklink('mobile/ucenter/member:tendersDetail',array($tenders_id)));
                    }
                    $this->err->add('恭喜您发布招标成功！');
                }
            }            
        }else{
            $this->err->add('非法的数据提交', 201); 
        }          
    }

}