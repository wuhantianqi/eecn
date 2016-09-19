<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tenpay.php 3053 2014-01-15 02:00:13Z youyi $
 */

class Payment_Tenpay
{
    
	private $gateway = 'https://gw.tenpay.com/gateway/pay.htm';
	private $transport = 'https';
	private $sign_type = 'MD5';

	//支付接口配置信息
    private $config = array();
    //订单信息
    private $order = array();

	public function __construct($cfg)
    {
        $this->config = $cfg;
        $this->_parameter = array();
       
        $this->_parameter['partner'] = $cfg['tenpay_account'];
        $this->_parameter['return_url'] = $cfg['return_url'];
        $this->_parameter['notify_url'] = $cfg['notify_url']; 

		//业务系统可选参数
		$this->_parameter['fee_type'] = '1';
		$this->config['input_charset'] = strtoupper('UTF-8');
		$this->_parameter['input_charset'] = $this->config['input_charset'];
		$this->_parameter['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
		$this->_parameter['bank_type'] = '0';
		$this->_parameter['service_version'] = '1.0';
		$this->_parameter['sign_key_index'] = '1';
		$this->_parameter['sign_type'] = 'MD5';
		$this->_parameter['time_start'] = date("YmdHis");
		$this->_parameter['trade_mode'] = '1';
		$this->_parameter['trans_type'] = '1';
		$this->_parameter['transport_fee'] = '0';

        if (!extension_loaded('openssl')){
            $this->transport = 'http';
        }
        
    }
	
	public function build_url($params)
    {	
        $parameter = $this->build_parameter($params);
		$parameter['partner'] = $this->_parameter['partner'];
        $url = $this->gateway ."?". $this->_build_query($parameter);
        return $url;
    }

	public function build_form($params)
    {      
        //待请求参数数组
        $parameter = $this->build_parameter($params);        
        $html = "<form id='tenpaysubmit' name='tenpaysubmit' action='".$this->gateway."input_charset=".trim(strtolower($this->config['input_charset']))."' method='".$method."'>";
        while (list ($key, $val) = each ($parameter)) {
            $html.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        //submit按钮控件请不要含有name属性
        $html .= "<input type='submit' value='立即支付'></form>";        
        $html .= "<script>document.forms['tenpaysubmit'].submit();</script>";
        return $html;
    }


	public function return_verify()
    {
        if(empty($_GET)){   //判断GET来的数组是否为空
            return false;
        }else if($_GET['trade_status'] != 'TRADE_FINISHED' && $_GET['trade_status'] != 'TRADE_SUCCESS'){
            return false;
        }else{
            //判断veryfy_result是否为ture，生成的签名结果mysign与获得的签名结果sign是否一致
            //$veryfy_result的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关            
            $notify = $this->_filter_params($_GET);
            $mysign = $this->create_sign($notify);
            $veryfy_result = $this->verify_notify($_GET["notify_id"]);
            //写日志记录
            $log  = "veryfy_result:{$veryfy_result}\n\n";
            $log .= "return_url_log:sign={$_GET[_filter_params]}&mysign={$mysign}&".$this->_build_query($notify);
            $this->_logs($log);
            if (preg_match("/true$/i",$veryfy_result) && $mysign == $_GET["sign"]){
                return array('trade_no'=>$notify['out_trade_no'], 'pay_trade_no'=>$notify['trade_no'], 'amount'=>$notify['total_fee'], 'extra_param'=>$this->_decode_params($notify['extra_common_param']));
            }
            return false;
        }
        return true;
    }

	public function notify_verify()
    {
        if(empty($_POST)){//判断POST来的数组是否为空
            return false;
        }else if($_POST['trade_status'] != 'TRADE_FINISHED' && $_POST['trade_status'] != 'TRADE_SUCCESS'){
            return false;
        }else{
            //判断veryfy_result是否为ture，生成的签名结果mysign与获得的签名结果sign是否一致
            //$veryfy_result的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关            
            $notify = $this->_filter_params($_POST);
            $mysign = $this->create_sign($notify);
            $veryfy_result = $this->verify_notify($_POST["notify_id"]); 
            //写日志记录
            $log  = "veryfy_result:{$veryfy_result}\n\n";
            $log .= "notify_url_log:sign={$_POST[sign]}&mysign={$mysign}&".$this->_build_query($notify);
            $this->_logs($log);
            if (preg_match("/true$/i",$veryfy_result) && $mysign == $_POST["sign"]){
                return array('trade_no'=>$notify['out_trade_no'], 'amount'=>$notify['total_fee'], 'extra_param'=>$this->_decode_params($notify['extra_common_param']));
            }
            return false;
        }
    }

    public function notify_success($success=true)
    {
        if($success){
            echo "success";exit;
        }else{
            echo "fail";exit;
        }
    }

    public function verify_notify($notify_id)
    {
        //获取远程服务器ATN结果，验证是否是支付宝服务器发来的请求
        if($this->transport == "https") {
            $veryfy_url = $this->https_verify_url. "partner=" .$this->config['tenpay_partner']. "&notify_id=".$notify_id;
        } else {
            $veryfy_url = $this->http_verify_url. "partner=".$this->config['tenpay_partner']."&notify_id=".$notify_id;
        }
        return $this->http($veryfy_url, null, 'GET');
    }

    public function http($url, $params=array(), $mothed='POST')
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果          
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($ci, CURLOPT_CAINFO,$this->cacert_url);//证书地址
        if(strtoupper($mothed) == 'POST'){// post传输数据
            curl_setopt($ci, CURLOPT_POST, true); 
            if (!empty($params)) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
            }            
        }else if(!empty($params)){ // get传输数据
            $url .= $this->build_query($params);
        }
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $res = curl_exec($ci);
        curl_close($ci);
        return $res;
    }

	/**
     * 生成要请求给财付通的参数数组
     * @param $params 请求前的参数数组
     * @return 要请求的参数数组
     */
	 //
    public function build_parameter($params)
    {	
        $parameter = $this->_parameter;
        $parameter['out_trade_no'] = $params['trade_no'];
        $parameter['subject'] = $params['title'];
        $parameter['body'] = $params['body'];
        if($params['show_url']){
            $parameter['show_url'] = $params['show_url'];
        }
        if($params['extra_param']){
            $parameter['extra_common_param'] = $this->_encode_params($params['extra_param']);
        }
		
        if ($this->payment['payment_config']['tenpay_service'] == 'create_direct_pay_by_user'){
            $parameter['total_fee'] = sprintf("%01.2f", $params['amount'])*100;
        } else {
            $parameter['total_fee'] = sprintf("%01.2f", $params['amount'])*100;
            if($params['contact']){
                $parameter['receive_name'] = $params['contact'];
            }
            if($params['addr']){
                $parameter['receive_address'] = $params['addr'];
            }
            if($params['mobile']){
                $parameter['receive_phone'] = $params['mobile'];
            }            
        }
		
        $parameter = $this->_filter_params($parameter);
        $parameter = $this->_sort_params($parameter);
		
        $sign = $this->create_sign($parameter);
		
        $parameter['sign'] = $sign;        
		$parameter['partner'] = $this->_parameter['seller_email'];
		
        return $parameter;
    }

	private function _filter_params($params)
    {
        $para = array();
		
        while (list ($key, $val) = each ($params)) {
            if($key == "sign"  || $val == "") continue;
            else $para[$key] = $params[$key];
        }
        $this->_return_data['TRADENO'] = $para['trade_no']; //交易号
        $this->_return_data['IDCARD'] = $para['buyer_id']; //买家帐号
        return $para;
    }

	//对数组排序 用作生成签名
    private function _sort_params($params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

	 private function create_sign($params)
    {   
		$params = $this->_sort_params($params);

        $prestr = $this->_build_query($params, false);  //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串

		$mysgin = strtolower(md5($prestr.'&key='.$this->config['tenpay_key']));
		
        return $mysgin;
    }

	 protected function _build_query($params, $urlencode=true)
    {
        $query_string = "";
		
		foreach($params as $key => $val){
			if($key != 'logistics_payment' && $key != 'logistics_type'){
				if($urlencode){
					$query_string .= $key."=".urlencode($val)."&";
				}else{
					$query_string .= $key."=".$val."&";  
				}
			}
			
		}

        $query_string = substr($query_string, 0, count($query_string)-2);

        if(get_magic_quotes_gpc()){$query_string = stripslashes($query_string);}
		
        return $query_string;
    }

    protected function _encode_params($param)
    {
        $hex = '';
        foreach((array)$param as $k=>$v){
            $_K = $_V = '';
            $k = strval($k);
            $v = strval($v);
            for($i=0; $i<strlen($k); $i++)  
                $_K .= dechex(ord($k[$i]));
            for($i=0; $i<strlen($v); $i++)  
                $_V .= dechex(ord($v[$i]));
            $hex .= strtoupper($_K).'O'.strtoupper($_V).'I';
        }
        return trim($hex,'I');  
    }

    protected function _decode_params($hex)
    {
        $param = ''; 
        foreach(explode('I',$hex) as $h){
            list($_K,$_V) = explode('O',$h);
            $k = $v = '';
            for($i=0; $i<strlen($_K)-1; $i+=2)
                $k .= chr(hexdec($_K[$i].$_K[$i+1]));
            for($i=0; $i<strlen($_V)-1; $i+=2)
                $v .= chr(hexdec($_V[$i].$_V[$i+1]));
            $param[$k] = $v;
        }
        return  $param;         
    }

    protected function _logs($log)
    {
        $key = 'payment-tenpay-'.date('Ymd');
        K::M('system/logs')->log($key, $log);
    }       
}
