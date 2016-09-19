<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Weixin_Addon_Goldegg extends Ctl_Ucenter
{

    public function index($page=1)
    {
        $weixin = $this->ucenter_weixin();
		$pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['page'] = $limit = 30;
        $pager['page'] = $count = 0;
        if($items = K::M('weixin/goldegg')->items(array('wx_id'=>$weixin['wx_sid']), null, $page, $limit, $count)){
            $pgaer['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->tmpl = 'ucenter/weixin/addon/goldegg/index.html';
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
				if($goldegg_id = K::M('weixin/goldegg')->create($data)){
					$keyword = array();
					$keyword['wx_id'] = $weixin['wx_id'];
					$keyword['wx_sid'] = $weixin['wx_sid'];
					$keyword['keyword'] = $data['keyword'];
					$keyword['plugin'] = 'goldegg:'.$goldegg_id;
					K::M('weixin/keyword')->create($keyword);
					$this->err->add('添加内容成功');
					$this->err->set_data('forward', 'goldegg/index.html');
				} 
			}else{
				$this->err->add('该关键字已经被使用，请修改关键字', 212);
			}
           
        }else{
           $this->tmpl = 'ucenter/weixin/addon/goldegg/create.html';
        }
	}

	public function preview($goldegg_id=null)
	{
		$url = $this->request['city']['siteurl'].'/weixin/goldegg-show-'.$goldegg_id.'.html';
		echo '<img alt="模式一扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url).'" style="width:150px;height:150px;"/>';
		exit;
	}

	public function edit($goldegg_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if(!($goldegg_id = (int)$goldegg_id) && !($goldegg_id = $this->GP('goldegg_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/goldegg')->detail($goldegg_id)){
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
            if(K::M('weixin/goldegg')->update($goldegg_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	 $this->tmpl = 'ucenter/weixin/addon/goldegg/edit.html';
        }
	}

	 public function delete($goldegg_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if($goldegg_id = (int)$goldegg_id){
            if(!$detail = K::M('weixin/goldegg')->detail($goldegg_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
				if($items = K::M('weixin/keyword')->items(array('keyword'=>$detail['keyword'],'wx_id'=>$weixin['wx_id']))){
					if(K::M('weixin/goldegg')->delete($goldegg_id)){
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

	

	public function sn($goldegg_id,$page_id)
	{
		if(!($goldegg_id = (int)$goldegg_id) && !($goldegg_id = $this->GP('goldegg_id'))){
            $this->err->add('没有指定优惠券ID', 211);
        }else if(!$detail = K::M('weixin/goldegg')->detail($goldegg_id)){
            $this->err->add('该优惠券不存在或已经删除', 212);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
			$filter['goldegg'] = $goldegg_id;
			$filter['prize'] = '<:6';
			if($items = K::M('weixin/goldeggsn')->items($filter, null, $page, $limit, $count)){
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
			$this->tmpl = 'ucenter/weixin/addon/goldegg/sn.html';
		}
	} 

	
	public function sndelete($sn_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if($sn_id = (int)$sn_id){
            if(!$detail = K::M('weixin/goldeggsn')->detail($sn_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/goldeggsn')->delete($sn_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('sn_id')){
            if(K::M('weixin/goldeggsn')->delete($ids)){
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
            if(!$detail = K::M('weixin/goldeggsn')->detail($sn_id)){
                $this->err->add('你要修改的内容不存在或已经删除', 211);
            }else{
				if($detail['is_use'] == '1'){
					$data['is_use'] = 0;
					$data['use_time'] = '';
				}else{
					$data['is_use'] = 1;
					$data['use_time'] = __TIME;
				}
                if(K::M('weixin/goldeggsn')->update($sn_id, $data)){
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