<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: payment.mdl.php 5531 2014-06-19 10:26:25Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Trade_Payment extends Model 
{
    
    public function order($code, $order, $form=false)
    {
        if(!$oPayApi = $this->loadPayment($code)){
            return false;
        }
        if(!$log = K::M('payment/log')->log_by_no($order['order_no'])){
            $log = array('uid'=>$order['uid'], 'shop_id'=>$order['shop_id'], 'from'=>'order', 'trade_no'=>$order['order_no'], 'payment'=>$code, 'amount'=>$order['amount']);
            if(!$log_id = K::M('payment/log')->create($log)){
                return false;
            }
            $log['log_id'] = $log_id;
        }
        if($log['payed']){
            $this->err->add('该订单已经支付成功', 211);
            return false;
        }
        $a = array();
        if($log['amount'] != $order['amount']){
            $a['amount'] = $order['amount'];
        }
        if($log['payment'] != $code){
            $a['payment'] = $code;
        }
        if($a){
            K::M('payment/log')->update($log['log_id'], $a,  true);
        }
        $params = array();
        $params['trade_no'] = $order['order_no'];
        $site = K::$system->config->get('site');
        $params['title'] = $site['title'].'-商城订单';
        $params['body'] = $order['contact'].','.$order['mobile'].','.$order['address'];
        $params['amount'] = $order['amount'];
        $params['contact'] = $order['contact'];
        $params['mobile'] = $order['mobile'];
        $params['addr'] = $order['addr'];
        if($form){
            return $oPayApi->build_form($params);
        }
		
		if (strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger') && $code == 'wxpay') {
			return $oPayApi->build_url($params);
		}else{
			return $oPayApi->build_url($params);
		}
    }

    public function payed_order($log, $trade)
    {
        K::M('trade/order')->set_payed($log['trade_no'], $trade);
        if($log['shop_id']){
            K::M('shop/shop')->update_money($log['shop_id'], $trade['amount'], '订单在线支付:'.$log['trade_no']);
        }
        return true;        
    }

    public function gold($uid, $code, $amount)
    {
        $goldCfg = K::$system->config->get('gold');
        if(!$uid = (int)$uid){
            return false;
        }else if(!$member = K::M('member/member')->detail($uid)){
            return false;
        }else if(empty($goldCfg['onpay'])){
            $this->err->add('金币不支持在线支付', 211);
            return false;
        }else if(($amount = floatval($amount)) < $goldCfg['minpay']){
            $this->err->add('最小充值金额不能小于'.$goldCfg['minpay']."RMB", 212);
            return false;            
        }else if(!$oPayApi = $this->loadPayment($code)){
            return false;
        }
        $log = array('uid'=>$uid, 'from'=>'gold', 'trade_no'=>0, 'payment'=>$code, 'amount'=>$amount);
        if(!$log_id = K::M('payment/log')->create($log, true)){
            return false;
        }
        $log['trade_no'] = sprintf("2%09d", $log_id);
        K::M('payment/log')->update_trade_no($log_id, $log['trade_no']);
        $log['log_id'] = $log_id;
        $site = K::$system->config->get('site');
        $params = array();
        $params['trade_no'] = $log['trade_no'];
        $params['title'] = $site['title'].'-充值金币';
        $params['body'] = '会员:'.$member['uname'].'('.$uid.')';
        $params['amount'] = $amount;
        return $oPayApi->build_url($params);  
    }

    public function payed_gold($log, $trade)
    {
        $goldCfg = K::$system->config->get('gold');
        $gold = $trade['amount'] * $goldCfg['huilv'];
        return K::M('member/gold')->update($log['uid'], $gold, '在线充值:'.$trade['trade_no']);
    }

    public function loadPayment($code)
    {
        static $_PayApiObj = array();
        if(!is_object($_PayApiObj[$code])){
            $file = __CFG::DIR."plugins/payments/{$code}/{$code}.php";
            if(!file_exists($file)){
                $this->err->add('您选择的支付接口不存在', 311);
                return false;
            }else if(!$payment = K::M('payment/payment')->payment($code)){
                $this->err->add('您选择的支付接口不存在', 312);
                return false;
            }else if(empty($payment['status'])){
                $this->err->add('您选择的支付接口不可用', 313);
                return false;
            }
			if (strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger') && $code == 'wxpay') {
				$file = __CFG::DIR."plugins/payments/{$code}/jsapi.php";
				include($file);
				$clsName = "Weixin_".ucfirst('jsapi');
			}else{
				include($file);
				$clsName = "Payment_".ucfirst($code);
			}
            $config = $payment['config'];
            $site = K::$system->config->get('site');
			if(defined('IN_MOBILE')){
				$config['return_url'] = $site['siteurl'].'/'.K::M('helper/link')->mklink('mobile/trade/payment:return', array($code));
				$config['notify_url'] = $site['siteurl'].'/'.K::M('helper/link')->mklink('mobile/trade/payment:notify', array($code));
			}else{
				$config['return_url'] = $site['siteurl'].'/'.K::M('helper/link')->mklink('trade/payment:return', array($code));
				$config['notify_url'] = $site['siteurl'].'/'.K::M('helper/link')->mklink('trade/payment:notify', array($code));
			}
            $_PayApiObj[$code] = new $clsName($config);
        }
        return $_PayApiObj[$code];
    }
}