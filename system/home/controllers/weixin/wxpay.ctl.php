<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

require_once __CFG::DIR."plugins/payments/wxpay//WxPay.JsApiPay.php";
require_once __CFG::DIR."plugins/payments/wxpay/lib/WxPay.Api.php";

class Ctl_Weixin_Wxpay extends Ctl_Weixin
{
    
    public function index()
    {
		$tools = new JsApiPay();
        if(empty($openid)){
           $openid = $this->access_openid();
        }
		$input = new WxPayUnifiedOrder();
		$input->SetBody("test");
		$input->SetAttach("test");
		$input->SetOut_trade_no('6554322324234');
		$input->SetTotal_fee("1");
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://fz.jhcms.cn/trade/payment/notify-wxpay.html");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openid);
		$order = WxPayApi::unifiedOrder($input);
		echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';

		$this->printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		$this->pagedata['jsApiParameters'] = $jsApiParameters;
		//获取共享收货地址js函数参数
		$editAddress = $tools->GetEditAddressParameters();
		$this->pagedata['editAddress'] = $editAddress;
		$this->tmpl = 'weixin/wxpay/index.html';
    }

	public function printf_info($data)
	{
		foreach($data as $key=>$value){
			echo "<font color='#00ff55;'>$key</font> : $value <br/>";
		}
	}

    public function mytenders($openid=null)
    {
        if(empty($openid)){
            $openid = $this->access_openid();
        }
        if($client = $this->wechat_client()){
            if(!$wx_info = $client->getUserInfoById($openid)){
                exit('您没有绑定,不能查看招标');
            }
            $this->pagedata['wx_info'] = $wx_info;
            if($items = K::M('weixin/tenders')->items_by_openid($openid)){
                $this->pagedata['items'] = $items;                
            }
            $this->tmpl = 'weixin/tenders/mytenders.html';
        }else{
            exit('参数错误');
        }
    }

    public function detail($tenders_id)
    {
        if(!$tenders_id = (int)$tenders_id){
            exit('参数错误');
        }else if(!$tenders = K::M('weixin/tenders')->detail($tenders_id)){
            exit('您要查看的招标不存在');
        }else if(!$openid = $this->access_openid()){
            exit('您没有邦定微信帐号');
        }else if($tenders['openid'] != $openid){
            exit('您没有邦定微信帐号');
        }else if($client = $this->wechat_client()){
            if(!$wx_info = $client->getUserInfoById($openid)){
                exit('您没有绑定,不能查看招标');
            }
            $this->pagedata['wx_info'] = $wx_info;
            $this->pagedata['tenders'] = $tenders;
            $this->tmpl = 'weixin/tenders/detail.html';
        }else{
            exit('您没有绑定招标信息');
        }
    }

}