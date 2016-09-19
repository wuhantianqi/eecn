<?php
 

require_once __CFG::DIR."plugins/payments/wxpay/lib/WxPay.Notify.php";
require_once __CFG::DIR."plugins/payments/wxpay/lib/WxPay.Api.php";

class Payment_Wxpay extends WxPayNotify
{
	 public function __construct($cfg)
    {
		$this->config = $cfg;
		
		$this->_parameter = array();
        $this->_parameter['APPID'] = $cfg['appid'];
        $this->_parameter['MCHID'] = $cfg['mch_id'];
        $this->_parameter['KEY'] = $cfg['key']; 
		$this->_parameter['APPSECRET'] = $cfg['appsecret'];
		require_once "WxPay.NativePay.php";
	}

	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		K::M('system/logs')->log('wxpay', "query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}

	public function build_url($input)
	{
		$inputObj = new WxPayUnifiedOrder();
		$inputObj->SetBody($input['title']);
		$inputObj->SetOut_trade_no($input['trade_no']);
		$inputObj->SetTotal_fee($input['amount']*100);
		$inputObj->SetNotify_url($this->config['notify_url']);
		$inputObj->SetTrade_type("NATIVE");
		$inputObj->SetProduct_id($input['trade_no']);

		if($inputObj->GetTrade_type() == "NATIVE")
		{
			$result = WxPayApi::unifiedOrder($inputObj);
			return $result["code_url"];
		}
	}

	public function NotifyProcess($data, &$msg)
	{
		K::M('system/logs')->log('wxpay', "call back:" . json_encode($data));
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		$success = false;
		if($obj = K::M('trade/payment')->loadPayment('wxpay')){
			$trade = $data;
			if(!$log = K::M('payment/log')->log_by_no($trade['out_trade_no'])){
				
				$this->err->add('支付的订单不存在', 211);
			}else if($trade['total_fee'] != $log['amount']*100){
				$this->err->add('支付金额非法', 212);
			}else if(K::M('payment/log')->set_payed($trade['out_trade_no'])){
				if($log['payed'] == 1){
					$success = true;
				}else{
					if($log['from'] == 'order'){ //订单支付
						$trade = array('payed'=>'1','payedip'=>__IP,'payedtime'=>__TIME);
						if(K::M('trade/payment')->payed_order($log, $trade)){
							$success = true;
						}
					}else if($log['from'] == 'gold'){ //金币充值
						$trade = array('payed'=>'1','payedip'=>__IP,'payedtime'=>__TIME);
						if(K::M('trade/payment')->payed_gold($log, $trade)){
							$success = true;
						}
					}
				}
				
			}
			$this->notify_success($success);
		}
	}

	public function notify_verify()
	{
		$handle = $this->Handle(true);
	}

	public function notify_success()
	{
		if($success){
            echo "success";exit;
        }else{
            echo "fail";exit;
        }
	}
}

?>