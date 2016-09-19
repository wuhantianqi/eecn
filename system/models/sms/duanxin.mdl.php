<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: 56dxw.mdl.php 3350 2014-02-18 01:33:22Z youyi $
 */

Import::I('sms');
class Mdl_Sms_Duanxin implements Sms_Interface
{   
    protected $gateway = 'http://smsapi.duanxin.cm?ac=send';
    protected $_cfg = array();

    public $lastmsg = '';
	public $lastcode = 1;

    public function __construct($system)
    {
    	$this->_cfg = $system->config->get('sms');
    }
    
    public function send($mobile, $content)
    {
    	$params = array('comid'=>$this->_cfg['comid'], 'smsnumber'=>$this->_cfg['smsnumber']);
    	$params['username'] = $this->_cfg['uname'];
    	$params['userpwd'] = $this->_cfg['passwd'];
    	$params['sendtime'] = '';
    	$params['handtel'] = $mobile;
    	$params['sendcontent'] = iconv('UTF-8', 'GB2312//IGNORE', $content);
        $http = K::M('net/http');
    	if($ret = $http->http($this->gateway, $params, 'GET')){
    		if($ret == 1){
    			return true;
    		}else{
                switch($ret){
				   case -1:$error='手机号码不正确';break;
				   case -2:$error='除时间外，所有参数不能为空';break;
				   case -3:$error='用户名密码不正确';break;
				   case -4:$error='平台不存在';break;
				   case -5:$error='客户短信数量为0';break;
				   case -6:$error='客户账户余额小于要发送的条数';break;
				   case -8:$error='非法短信内容';break;
				   case -9:$error='未知系统故障';break;
				   case -10:$error='网络性错误';break;
				   case -21:$error = '未添加短信签名';break;
				   default:$error='未知的错误';
				}
				$this->lastcode = $ret;
				$this->lastmsg = $error;
    		}
    	}else{
    		$this->lastmsg = '未知的错误';
    	}
    	return false;
    }
}