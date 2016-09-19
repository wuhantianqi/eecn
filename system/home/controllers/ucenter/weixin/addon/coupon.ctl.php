<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Addon_Coupon extends Ctl_Ucenter
{
    protected $_allow_fields = 'coupon_id,wx_id,keyword,title,intro,photo,stime,ltime,use_tips,end_tips,end_photo,num,max_count,down_count,use_count,views,follower_condtion,clientip,dateline';

    public function index($page=1)
    {
        $weixin = $this->ucenter_weixin();
		$pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['page'] = $limit = 30;
        $pager['page'] = $count = 0;
        if($items = K::M('weixin/coupon')->items(array('wx_id'=>$weixin['wx_sid']), null, $page, $limit, $count)){
            $pgaer['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->tmpl = 'ucenter/weixin/addon/index.html';
    }

	public function create()
	{
		$weixin = $this->ucenter_weixin();
		if($data = $this->checksubmit('data')){
            if($_FILES['data']){
				foreach($_FILES['data'] as $k=>$v){
					foreach($v as $kk=>$vv){
						$attachs[$kk][$k] = $vv;
					}
				}
				$upload = K::M('magic/upload');
				foreach($attachs as $k=>$attach){
					if($attach['error'] == UPLOAD_ERR_OK){
						if($a = $upload->upload($attach, 'weixin')){
							$data[$k] = $a['photo'];
						}
					}
				}
			}
			$data['wx_id'] = $weixin['wx_sid'];
			if(!$items = K::M('weixin/keyword')->items(array('keyword'=>$data['keyword'],'wx_id'=>$weixin['wx_id']))){
				if($coupon_id = K::M('weixin/coupon')->create($data)){
					$keyword = array();
					$keyword['wx_id'] = $weixin['wx_id'];
					$keyword['wx_sid'] = $weixin['wx_sid'];
					$keyword['keyword'] = $data['keyword'];
					$keyword['plugin'] = 'coupon:'.$coupon_id;
					K::M('weixin/keyword')->create($keyword);
					$this->err->add('添加内容成功');
					$this->err->set_data('forward', 'coupon/index.html');
				} 
			}else{
				$this->err->add('该关键字已经被使用，请修改关键字', 212);
			}
            
        }else{
           $this->tmpl = 'ucenter/weixin/addon/create.html';
        }
	}

	public function edit($coupon_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
			if($_FILES['data']){
				foreach($_FILES['data'] as $k=>$v){
					foreach($v as $kk=>$vv){
						$attachs[$kk][$k] = $vv;
					}
				}
				$upload = K::M('magic/upload');
				foreach($attachs as $k=>$attach){
					if($attach['error'] == UPLOAD_ERR_OK){
						if($a = $upload->upload($attach, 'weixin')){
							$data[$k] = $a['photo'];
						}
					}
				}
			}
            if(K::M('weixin/coupon')->update($coupon_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	 $this->tmpl = 'ucenter/weixin/addon/edit.html';
        }
	}

	 public function delete($coupon_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if($coupon_id = (int)$coupon_id){
            if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
				if($items = K::M('weixin/keyword')->items(array('keyword'=>$detail['keyword'],'wx_id'=>$weixin['wx_id']))){
					if(K::M('weixin/coupon')->delete($coupon_id)){
						foreach($items as $k => $v){
							K::M('weixin/keyword')->delete($v['kw_id']);
						}
						$this->err->add('删除内容成功');
					}
				}else{
					$this->err->add('非法操作');
				}	
            }
        }
    }  

	public function preview($coupon_id=null)
	{
		$url = $this->request['city']['siteurl'].'/weixin/coupon-preview-'.$coupon_id.'.html';
		echo '<img alt="模式一扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url).'" style="width:150px;height:150px;"/>';
		exit;
	}

	public function sign($coupon_id=null)
	{
		$weixin = $this->ucenter_weixin();
		if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('该优惠券不存在或已经删除', 212);
        }else{
			if(empty($openid)){
				$openid = $this->access_openid();
			}
			$client = $this->wechat_client();
			$wx_info = $client->getUserInfoById($openid);

			$member =  K::M('member/weixin')->detail_by_openid($openid);

			$list = K::M('weixin/couponsn')->items(array('coupon_id'=>$coupon_id,'openid'=>$openid));
			
			if (! empty ( $detail ['ltime'] ) && $detail ['ltime'] <= time ()) {
				$error = '您来晚啦';
			} else if ($detail ['max_count'] > 0 && $detail ['max_count'] <= count($list)) {
				$error = '您的领取名额已用完啦';
			} else if ($detail ['num']<=$detail['down_count']) {
				$error = '优惠券已经领取光啦';
			}else if ($detail ['follower_condtion'] && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$error = '用户注册后才能领取';
			}else{
				$data ['sn'] = uniqid ();
				$data ['uid'] = $this->uid;
				$data['wx_id'] = $weixin['wx_sid'];
				$data['coupon'] = $coupon_id;
				$data['openid'] = $openid;
				$data['nickname'] = $wx_info['nickname'];
				if($sn = K::M('weixin/couponsn')->create($data)){
					K::M('weixin/coupon')->update_count($coupon_id, 'down_count', 1);
					header('Location: coupon-show-'.$sn);
				}else {
					$error = '领取会员卡后才能领取';
				}
			}
			if($error){
				$this->tmpl = 'ucenter/weixin/addon/over.html';
			}
		}
	}

	public function sn($coupon_id,$page_id)
	{
		if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->GP('coupon_id'))){
            $this->err->add('没有指定优惠券ID', 211);
        }else if(!$detail = K::M('weixin/coupon')->detail($coupon_id)){
            $this->err->add('该优惠券不存在或已经删除', 212);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
			$filter['coupon'] = $coupon_id;
			if($items = K::M('weixin/couponsn')->items($filter, null, $page, $limit, $count)){
				$uids = '';
				foreach($items as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
				$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
			}
			$this->pagedata['items'] = $items;
			$this->pagedata['detail'] = $detail;
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'ucenter/weixin/addon/sn.html';
		}
	} 

	public function show($sn)
	{
		$weixin = $this->ucenter_weixin();
		if(!($sn = (int)$sn) && !($sn = $this->GP('sn'))){
            $this->err->add('非法访问', 211);
        }else if(!$detail = K::M('weixin/couponsn')->detail($sn)){
            $this->err->add('非法访问', 212);
        }else if(!$coupon = K::M('weixin/coupon')->detail($detail['coupon'])){
            $this->err->add('非法访问', 212);
        }else{
			$this->pagedata['detail'] = $detail;
			$condition = array ();
			$coupon ['max_count'] > 0 && $condition [] = '每人最多可领取' . $coupon ['max_count'] . '张';
			$coupon ['follower_condtion'] == 1 && $condition [] = '必须微信关注后才能领取';
			$coupon ['member_condtion'] == 1 && $condition [] = '必须是平台会员才能领取';
			$this->pagedata['coupon'] = $coupon;
			$this->pagedata['condition'] = $condition;
			$this->pagedata['detail'] = $detail;
			$this->tmpl = 'ucenter/weixin/addon/show.html';
		}
	}

	public function sndelete($sn_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if($sn_id = (int)$sn_id){
            if(!$detail = K::M('weixin/couponsn')->detail($sn_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/couponsn')->delete($sn_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('sn_id')){
            if(K::M('weixin/couponsn')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

	public function snedit($sn_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if($sn_id = (int)$sn_id){
            if(!$detail = K::M('weixin/couponsn')->detail($sn_id)){
                $this->err->add('你要修改的内容不存在或已经删除', 211);
            }else{
				if($detail['is_use'] == '1'){
					$data['is_use'] = 0;
					$data['use_time'] = '';
				}else{
					$data['is_use'] = 1;
					$data['use_time'] = __TIME;
				}
                if(K::M('weixin/couponsn')->update($sn_id, $data)){
                    $this->err->add('改变状态成功');
                }
            }
        }
    }  

	protected function wechat_client()
    {
        static $client = null;
        if($client === null){
            if(!$client = K::M('weixin/weixin')->admin_wechat_client()){
                exit('网站公众号设置错误');
            }
        }
        return $client;
    }

    protected function access_openid($force = false)
    {
        static $openid = null;
        if($force || $openid === null){
            if($code = $this->GP('code')){
                $client = $this->wechat_client();
                $ret = $client->getAccessTokenByCode($code);
                $openid = $ret['openid'];
            }else{
                if(!$openid = $this->cookie->get('wx_openid')){
                    $client = $this->wechat_client();
                    $url = $this->request['url'].'/'.$this->request['uri'];
                    $authurl = $client->getOAuthConnectUri($url, $state, 'snsapi_userinfo');
                    header('Location:'.$authurl);
                    exit();
                }
            }
            $this->cookie->set('wx_openid', $openid);
        }
        if(empty($openid)){
            exit('获取授权失败');
        }
        return $openid;
    }
}