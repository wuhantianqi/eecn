<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Mobile_Trade_Payment extends Ctl
{
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/(return|notify)-(\w+)\.html/i', $uri, $match)){
            $system->request['act'] = $match[1].'_verify';
            $system->request['args'] = array($match[2]);
        }
    }

    public function return_verify($code)
    {
        $forward = $this->mklink('mobile/ucenter/index');
        if($obj = K::M('trade/payment')->loadPayment($code)){
            if($trade = $obj->return_verify()){
                if(!$log = K::M('payment/log')->log_by_no($trade['trade_no'])){
                    $this->err->add('支付的订单不存在', 211);
                }else if(K::M('payment/log')->set_payed($trade['trade_no'])){
                    if($log['from'] == 'order'){ //订单支付
                        K::M('trade/payment')->payed_order($log, $trade);
                        $this->pagedata['order'] = K::M('trade/order')->detail_by_no($trade['trade_no']);
                        $this->tmpl = 'mobile/trade/payment/success.html';
                        //$this->err->add('支付订单成功');                      
                        //$forward = $this->mklink('mall/order:detail', array($trade['trade_no']));
                    }else if($log['from'] == 'gold'){ //金币充值
                        K::M('trade/payment')->payed_gold($log, $trade);
                        $this->err->add('充值金币成功');
                    }
                }else{
                    if($log['from'] == 'order'){
                        $this->err->add('该订单已经支付过了', 213);
                    }else if($log['from'] == 'gold'){
                        $this->err->add('已经充值成功，请不要重复提交', 214);
                    }                   
                }
            }else{
                $this->err->add('支付验证签名失败', 215);
            }
            $this->err->set_data('forward', $forward);
        }
    }

    public function notify_verify($code)
    {
        $success = false;
        if($obj = K::M('trade/payment')->loadPayment($code)){
            if($trade = $obj->notify_verify()){
                if(!$log = K::M('payment/log')->log_by_no($trade['trade_no'])){
                    $this->err->add('支付的订单不存在', 211);
                }else if(K::M('payment/log')->set_payed($trade['trade_no'])){
                    if($log['from'] == 'order'){ //订单支付
                        if(K::M('trade/payment')->payed_order($log, $trade)){
                            $success = true;
                        }
                    }else if($log['from'] == 'gold'){ //金币充值
                        if(K::M('trade/payment')->payed_gold($log, $trade)){
                            $success = true;
                        }
                    }
                }
            }
            $obj->notify_success($success);
        }
    }

    public function order($code=null, $order_no=null)
    {
        if(!is_numeric($order_no) && !($order_no = (int)$this->GP('order_no'))){
            $this->error(404);
        }else if(empty($code) && !($code = $this->GP('code'))){
            $this->error(404);
        }else if($this->check_login()){
            if(!$order = K::M('trade/order')->detail_by_no($order_no)){
                $this->err->add('您的订单不存在或已经删除', 211);
            }else if($order['order_status'] < 0){
                $this->err->add('订单已经取消不可支付', 212);
            }else if($order['order_status'] == 2){
                $this->err->add('订单已经完成不可支付', 213);
            }else if($order['pay_status']){
                $this->err->add('该订单已经支付过了,不需要重复支付', 212);
            }else if($url = K::M('trade/payment')->order($code, $order)){
                if(strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger') === false && strpos($url,'wxpay')){
					echo '<img alt="模式一扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url).'" style="width:150px;height:150px;"/>';
				}else{
					header("Location:{$url}");
				}
                exit;
            }
        }
    }

    public function gold($code=null, $amount=null)
    {
        if(empty($code) && !($code = $this->GP('code'))){
            $this->error(404);
        }else if(!($amount = (int)$amount) && !($amount = (int)$this->GP('amount'))){
            $this->err->add('充值金额不合法', 211);
        }else if($this->check_login()){
            if($url = K::M('trade/payment')->gold($this->uid, $code, $amount)){
                if(strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger') === false && strpos($url,'wxpay')){
					echo '<img alt="模式一扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url).'" style="width:150px;height:150px;"/>';
				}else{
					header("Location:{$url}");
				}
                exit;
            }
        }
    }
}