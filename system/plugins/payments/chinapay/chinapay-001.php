<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: chinapay.php 5379 2014-05-30 10:17:21Z youyi $
 */

include(dirname(__FILE__).'/netpayclient.php');
class Payment_Chinapay
{
	CONST _PKEY  = "E:\htdocs\ghzx\system\plugins\payments\chinapay\PgPubk.key";
	CONST _MKEY  = "E:\htdocs\ghzx\system\plugins\payments\chinapay\MerPrK_808080111899942_20101118162854.key";
	CONST _VERSION		= "20070129";	//当前使用的银联接口版本号
	CONST _CURY_ID		= "156";		//订单交易币种，3位长度，固定为人民币156,必填
	CONST _TRANS_TYPE	= "0001";		//交易类型，4位长度，必填{0001:消费,0003:退款}
	CONST _GATEWAY		= "http://payment-test.chinapay.com/pay/TransGet";
	CONST _QUERY		= 'http://console.chinapay.com/QueryWeb/processQuery.jsp'; //查询接口地址
	
    
    public function __construct($cfg)
    {	
        $this->config = $cfg;
        $this->config['_input_charset'] = strtolower('utf-8');        
        $this->_parameter = array();
        $this->_parameter['_input_charset'] = $this->config['_input_charset'];
        $this->_parameter['MerId'] = $cfg['chinapay_account'];
		$this->_parameter['OrdId'] = ''; //订单号
		$this->_parameter['TransAmt'] = ''; //金额
		$this->_parameter['CuryId'] = self::_CURY_ID; //货币代码
		$this->_parameter['TransDate'] = date('Ymd');
		$this->_parameter['TransType'] = self::_TRANS_TYPE; //交易类型  0001 消费 0002退货
		$this->_parameter['PageRetUrl'] = $cfg['notify_url'];
		$this->_parameter['BgRetUrl'] = $cfg['return_url'];
		$this->_parameter['Version'] = self::_VERSION;
		


		//$retm = buildKey(self::_MKEY);
		//$retp = buildKey(self::_PKEY);

		
        if (!extension_loaded('openssl')){
            $this->transport = 'http';
        }
        $this->cacert_url = dirname(__FILE__).DIRECTORY_SEPARATOR.'cacert.pem'; 
		
    }

    public function build_url($params)
    {	
	
        $parameter = $this->build_parameter($params);
		$url = self::_GATEWAY ."?". $this->_build_query($parameter);
        return $url;
    }

	

    public function build_form($params)
    {	
        //待请求参数数组
        $parameter = $this->build_parameter($params);        
		$_html  = '<form id="payment_from" action="'.self::_GATEWAY .'" method="'.$m.'">';
		while(list($key, $val) = each($this->_parameter)){
            $_html .= '<input type="hidden" name="'.$key.'" value="'.$val.'"/>';
        }
		$_html .= '<input type="submit" value="用银联账户付款" class="user_list_bg" /></form>';
		
		return $_html; 
        
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
            $log .= "return_url_log:sign={$_GET[sign]}&mysign={$mysign}&".$this->_build_query($notify);
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
        }else if($_POST['status'] != '1001'){
            return false;
        }else{
			 $notify['merid']		= trim($_POST['merid']);
			 $notify['orderno']		= trim($_POST['orderno']);
			 $notify['transdate']	= trim($_POST['transdate']);
			 $notify['amount']		= trim($_POST['amount']);
			 $notify['currencycode']	= trim($_POST['currencycode']);
			 $notify['transtype']	= trim($_POST['transtype']);
			 $notify['status']		= trim($_POST['status']);
			 $notify['checkvalue']	= trim($_POST['checkvalue']);
			 $notify['GateId']		= trim($_POST['GateId']);
			 $notify['Priv1']		= trim($_POST['Priv1']);
			 $plain = $notify['merid'] . $notify['orderno'] . $notify['amount'] . $notify['currencycode'] . $notify['transdate'] . $notify['transtype'] . $notify['status'] . $notify['checkvalue']; 
			print_r($notify);
			 $flag = verifyTransResponse($notify['merid'], $notify['orderno'], $notify['amount'], $notify['currencycode'], $notify['transdate'], $notify['transtype'], $notify['status'], $notify['checkvalue']);
			 var_dump($flag);
			 echo "File:", __FILE__, ',Line:',__LINE__;exit;
			 buildKey(self::_PKEY);
			 $flag = verify($plain, $notify['checkvalue']);
			 var_dump($flag);
			 echo "File:", __FILE__, ',Line:',__LINE__;exit;
			 if(!$flag) { 
				 echo "<h2>验证签名失败！</h2>"; 
				 return false;
			 }

			return true;
		
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
            $veryfy_url = $this->https_verify_url. "partner=" .$this->config['chinapay_partner']. "&notify_id=".$notify_id;
        } else {
            $veryfy_url = $this->http_verify_url. "partner=".$this->config['chinapay_partner']."&notify_id=".$notify_id;
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
     * 生成要请求给支付宝的参数数组
     * @param $params 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function build_parameter($params)
    {
        $parameter = $this->_parameter;
		$len = '0000000000000000';
        $parameter['OrdId'] = substr($len,0,-strlen($params['trade_no'])).$params['trade_no'];
		
		$merid = buildKey(self::_MKEY); 
		 if(!$merid) { 
			 echo "导入私钥文件失败！"; 
			 exit; 
		 } 
		

        if ($this->payment['payment_config']['chinapay_service'] == 'create_direct_pay_by_user'){
            $parameter['total_fee'] = sprintf("%01.2f", $params['amount']);
        } else {
			
			$str = '000000000000';
            $price = $params['amount'].'00';
			$parameter['TransAmt'] = substr($str,0,-strlen($price)). $price;
                    
        }
		
		
		$priv1 = $this->_encode_params($notify['extra_common_param']);
		
		$plain = $merid . $parameter['OrdId'] . $parameter['TransAmt'] . $parameter['CuryId'] . $parameter['TransDate'] . $parameter['TransType'];
		
		$chkvalue = sign($merid,$parameter['OrdId'],$parameter['TransAmt'],$parameter['CuryId'],$parameter['TransDate'],$parameter['TransType']); 
		$chkvalue = sign($plain); 

		//echo '<br>'."File:", __FILE__, ',Line:',__LINE__;exit;
		 if (!$chkvalue) { 
			 echo "签名失败！"; 
			 exit; 
		 } 
        
        $parameter['ChkValue'] = $chkvalue;
        return $parameter;
    }


    protected function _build_query($params, $urlencode=true)
    {
        $query_string = "";
        while (list ($key, $val) = each ($params)) {
            if($urlencode){
                $query_string .= $key."=".urlencode($val)."&";
            }else{
                $query_string .= $key."=".$val."&";  
            }
        }
        $query_string = substr($query_string, 0, count($query_string)-2);
        if(get_magic_quotes_gpc()){$query_string = stripslashes($query_string);}
        return $query_string;
    }

    private function _filter_params($params)
    {
        $para = array();
        while (list ($key, $val) = each ($params)) {
            if($key == "sign" || $key == "sign_type" || $val == "") continue;
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



    protected function _encode_params($param)
    {
        //$param = serialize($param);
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
        $key = 'payment-chinapay-'.date('Ymd');
        K::M('system/logs')->log($key, $log);
    }
}