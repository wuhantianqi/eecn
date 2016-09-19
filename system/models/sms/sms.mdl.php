<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: sms.mdl.php 5808 2014-07-05 07:00:20Z youyi $
 */

class Mdl_Sms_Sms extends Model
{   
    
    protected $sms = null;
    protected   $_sitetitle = null;
    protected   $_sitephone = null;
    protected   $_cityname = null;
    protected   $_dateline = null;
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->sms = K::M('sms/56dxw');
        $this->_config = $system->config->get('smsconfig');
        $cfg = K::$system->config->get('site');
        $this->_sitetitle = $cfg['title'];
        $this->_sitephone = $cfg['phone'];
        $this->_cityname = $system->request['city']['city_name'];
        if(K::M('verify/check')->mobile($system->request['city']['mobile'])){
            $cfg['mobile'] = $system->request['city']['mobile'];
        }
        $this->_dateline = date('Y-m-d H:i:s',__TIME);
        
    }
    
    //通知管理员的短信接口
    public function admin($key, $data=array())
    {
        if(empty($cfg['mobile'])){
            $cfg = K::$system->config->get('sms');
        }
        //一般接口都支持 ,分割的多个手机号 如果不支持的请在做逻辑处理！
        $mobiles = explode(',',$cfg['mobile']);
        foreach($mobiles  as $mobile){
            $this->send($mobile, $key, $data, true);
        }
        return  true;
    }

    protected function _send($mobile, $content)
    {
        if(!$this->sms->send($mobile, $content)){
            //$msg = $this->sms->lastmsg;
            //$this->err->add($msg, 450);
            if(__CFG::DEBUG){
                $this->err->add('短信接口错误:['.$this->sms->lastcode.':'.$this->lastmsg.']', 450);
            }else{
                $this->err->add('发送短信失败['.$this->sms->lastcode.']', 450);
            }
            K::M('sms/log')->create(array('mobile'=>$mobile, 'content'=>$content, 'sms'=>'56dx', 'status'=>0));
            return false;
        }
        K::M('sms/log')->create(array('mobile'=>$mobile, 'content'=>$content, 'sms'=>'56dx', 'status'=>1));
        return true;        
    }
    
    public function send($mobile, $tmpl, $data=array(), $checked=false)
    {   
        if(!$content = $this->_parse($tmpl, $data)){
            return false;
        }
		if(!$checked && !defined('IN_ADMIN')){
            if(!$this->check_sms(__IP, $title)){
                $this->err->add($title, 451);
                return false;
            }
        }
        $status = $this->_send($mobile, $content);
        return $status;
    }

	public function check_sms($clientip=null, &$title='')
    {
        $clientip = $clientip ? $clientip : __IP;
        $access = K::$system->config->get('access');
        if($mobile_time = (int)$access['mobile_time']){
            if((__TIME - $mobile_time*60) < K::M('sms/log')->lasttime_by_ip($clienip)){
                $title = '两次短信间隔不能少于'.$mobile_time.'分钟';
                return false;
            }
        }
        if($mobile_count = (int)$access['mobile_count']){
            $time = __TIME - 86400;
            if($mobile_count <= K::M('sms/log')->count("clientip='{$clientip}' AND dateline>$time")){
                $title = '同一IP24小时只能发送'.$mobile_count.'短信';
                return false;
            }
        }
        return true;
    }

    public function shop($shop, $tmpl, $data=array())
    {
        if(!($mobile = K::M('verify/check')->mobile($shop['phone'])) && $shop['uid']){
            if($member = K::M('member/member')->member($shop['uid'])){
                $mobile = $member['mobile'];
            }            
        }
        if(!$data['shop_name']){
            $data['shop_name'] = $shop['name'];
        }
        if(!$data['shop_phone']){
            $data['shop_phone'] = $shop['phone'];
        }
        if(!$data['shop_addr']){
            $data['shop_addr'] = $shop['addr'];
        } 
        if(!$data['shop_url']){
            $data['shop_url'] = $shop['shop_url'];
        }
        if(!$mobile = K::M('verify/check')->mobile($mobile)){
            return false;
        }
        return $this->send($mobile, $tmpl, $data, true);
    }

    public function company($company, $tmpl, $data=array())
    {
        if(!($mobile = $company['mobile']) && $company['uid']){
            if(!$member = $company['member']){
                $member = K::M('member/member')->member($company['uid']);
            }
            $mobile = $member['mobile'];
        }
        if(!$data['company_name']){
            $data['company_name'] = $company['title'];
        }
        if(!$data['company_phone']){
            $data['company_phone'] = $company['phone'];
        }
        if(!$data['company_addr']){
            $data['company_addr'] = $company['addr'];
        }
        if(!$data['company_url']){
            $data['company_url'] = $company['company_url'];
        }         
        if(!$mobile = K::M('verify/check')->mobile($mobile)){
            return false;
        }
        return $this->send($mobile, $tmpl, $data, true);   
    }

    protected function _parse($tmpl, $data=array())
    {
        if(preg_match('/^[\w\-\:]+$/i', $tmpl)){
            $ident = $tmpl;
            if(strpos($ident, 'sms_') === false){
                $ident = 'sms_'.$tmpl;
            }
            if($detail = K::M('system/systmpl')->detail_by_key($ident)){
                if(!$detail['is_open']){
                    return false;
                }
                $tmpl = $detail['tmpl'];
            }
        }
        $a = $b = array();
        foreach((array)$data as $k=>$v){
            $a[] = '{'.$k.'}';
            $b[] = $v;
        }
        $a[] = '{site_title}'; $a[] = '{site_phone}'; $a[] = '{city_name}'; $a[] = '{dateline}';$a[]='{site_name}';
        $b[] = $this->_sitetitle; $b[] = $this->_sitephone; $b[] = $this->_cityname; $b[] = $this->_dateline; $b[] = $this->_sitetitle;
        $content = str_replace($a, $b, $tmpl);
        return $content;
    }
}
