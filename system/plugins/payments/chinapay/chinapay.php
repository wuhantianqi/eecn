<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: chinapay.php 5379 2014-05-30 10:17:21Z youyi $
 */

include(dirname(__FILE__).'/netpayclient.php');
class Payment_Chinapay
{
	CONST _VERSION		= "20070129";	//当前使用的银联接口版本号
	CONST _CURY_ID		= "156";		//订单交易币种，3位长度，固定为人民币156,必填
	CONST _TRANS_TYPE	= "0001";		//交易类型，4位长度，必填{0001:消费,0003:退款}
    CONST _GATEWAY      = "http://payment-test.chinapay.com/pay/TransGet"; //测试环境
	//CONST _GATEWAY		= "http://payment.chinapay.com/pay/TransGet"; //生产环境
	CONST _QUERY		= 'http://console.chinapay.com/QueryWeb/processQuery.jsp'; //查询接口地址
	
    
    public function __construct($cfg)
    {	
        $this->config = $cfg;
        if(!defined('CHINAPAY_PKEY')){
            define('CHINAPAY_PKEY', dirname(__FILE__).'/'.$cfg['chinapay_pkey']);
        }
        if(!defined('CHINAPAY_MKEY')){
            define('CHINAPAY_MKEY', dirname(__FILE__).'/'.$cfg['chinapay_mkey']);
        }
        $this->_parameter = array();
        $this->_parameter['MerId'] = $cfg['chinapay_account'];        
		$this->_parameter['OrdId'] = ''; //订单号
		$this->_parameter['TransAmt'] = ''; //金额
		$this->_parameter['CuryId'] = self::_CURY_ID; //货币代码
		$this->_parameter['TransDate'] = date('Ymd');
		$this->_parameter['TransType'] = self::_TRANS_TYPE; //交易类型  0001 消费 0002退货
		$this->_parameter['PageRetUrl'] = $cfg['return_url'];
		$this->_parameter['BgRetUrl'] = $cfg['notify_url'];
		$this->_parameter['Version'] = self::_VERSION;
        //echo CHINAPY_MKEY;echo 'FILE:',__FILE__,'LINE:',__LINE__;exit();
		//buildKey(CHINAPY_PKEY);
		//buildKey(CHINAPY_MKEY);		
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
        return $this->response_verify();
    }

    public function notify_verify()
    {	
        return $this->response_verify();
    }

    protected function response_verify()
    {
        if(empty($_POST)){
            return false;
        }else if($_POST['status'] != 1001){
            return false;
        }else{
            buildKey(CHINAPAY_PKEY);
            $notify = array();
            $notify['merid']        = trim($_POST['merid']);
            $notify['orderno']      = trim($_POST['orderno']);
            $notify['transdate']    = trim($_POST['transdate']);
            $notify['amount']       = trim($_POST['amount']);
            $notify['currencycode'] = trim($_POST['currencycode']);
            $notify['transtype']    = trim($_POST['transtype']);
            $notify['status']       = trim($_POST['status']);
            $notify['checkvalue']   = trim($_POST['checkvalue']);
            $notify['GateId']       = trim($_POST['GateId']);
            $notify['Priv1']        = trim($_POST['Priv1']);            
            $verify = verifyTransResponse($notify['merid'], $notify['orderno'], $notify['amount'], $notify['currencycode'], $notify['transdate'], $notify['transtype'], $notify['status'], $notify['checkvalue']);

            //写日志记录
            $log  = "verify:{$verify}\n\n";
            $log .= "notify:".http_build_query($notify);
            $this->_logs($log);
            if(!$verify){
                exit($verify);
            }else if($notify['status'] != '1001'){
                exit($notify['status']);
            }
            return array('trade_no'=>$notify['orderno']/1, 'pay_trade_no'=>$notify['orderno']/1, 'amount'=>$notify['amount']/100, 'extra_param'=>$this->_decode_params($notify['Priv1']));
        }        
    }

    public function notify_success($success=true)
    {
        if($success){
            echo "success";exit;
        }else{
            K::$system->response_code(403);
            echo "fail";exit;
        }
    }

	private function _chk_value($parameter)
	{
		//这里调用银联提供的密钥生成函数
		//-100 环境变量"NPCDIR"未设置  
		//-101 商户密钥文件不存在或无法打开  
		//-102 密钥文件格式错误 
		//-103 秘钥商户号和用于签名的商户号不一致 
		//-130 用于签名的字符串长度为空
		$this->_parameter = $parameter;
		$_key  = $this->_parameter['MerId'] . $this->_parameter['OrdId'];
		$_key .= $this->_parameter['TransAmt'] . $this->_parameter['CuryId'];
		$_key .= $this->_parameter['TransDate'] . $this->_parameter['TransType'];
		$_key .= $this->_parameter['Priv1'];
        $ret = buildKey(CHINAPAY_MKEY);
		$_chk_value = signOrder($this->_parameter['MerId'], $this->_parameter['OrdId'], $this->_parameter['TransAmt'], $this->_parameter['CuryId'], $this->_parameter['TransDate'], $this->_parameter['TransType']); 
		//$_chk_value = sign($_key);
		if($_chk_value<0){
			exit($_chk_value);
		}
		return $_chk_value;		
	}

	private function _conver_sn($sn)
	{
		if(strlen($sn)<16){
			$sn = sprintf("%016s",$sn);	
		}else{
			$sn = ltrim($sn,'0');
		}
		return $sn;	
	}

	//格式化交易金额，以分位单位的12位数字。
	private function _format_fee($fee)
	{
		if($fee){
			if(!strstr($fee, ".")){
				$fee = $fee.".00";
			}
			$fee = str_replace(".", "", $fee);
			$temp = $fee;
			for($i=0; $i< 12 - strlen($fee); $i++){
				$temp = "0" . $temp;
			}
			return $temp;
		}
	}


    /**
     * 生成要请求给支付宝的参数数组
     * @param $params 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function build_parameter($params)
    {
		$parameter = $this->_parameter;
		$parameter['OrdId']	= $this->_conver_sn($params['trade_no']);
		$parameter['TransAmt']	= $this->_format_fee($params['amount']);//订单金额,12位长度左补0,必填,单分
		$parameter['TransDate']	= date("Ymd"); //订单交易日期，8位长度{20080808}，必填
		$parameter['GateId']		= $_payment[0];		//支持网关{0001~00XX:表示不同的银行}
		$parameter['Priv1']		= $this->_encode_params($params['extra_param']); //附加参数
		$parameter['ChkValue']	= $this->_chk_value($parameter);	
		
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