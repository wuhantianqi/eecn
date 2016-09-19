<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mall_Shop extends Ctl 
{

    public function __construct(&$system)
    {
        parent::__construct($system);
        $uri = $this->request['uri'];
        if(preg_match('/shop(-index)?-(\d+).html/i', $uri, $match)){
            $system->request['act'] = 'detail';
            $system->request['args'] = array($match[2]);
        }
    }

    public function index($shop_id)
    {
        $this->detail($shop_id);
    }
    
    public function detail($shop_id)
    {
        $shop = $this->check_shop($shop_id);
        $cate = K::M('shop/cate')->cate($shop['cat_id']);        
        $this->seo->set_shop($shop);
        $this->seo->init('shop', array('cate_name'=>$cate['title']));
        $this->tmpl = 'shop/index.html';
    }

    public function info($shop_id)
    {
        $shop = $this->check_shop($shop_id);
        $cate = K::M('shop/cate')->cate($shop['cat_id']);        
        $this->seo->set_shop($shop);
        $this->seo->init('shop', array('cate_name'=>$cate['title']));        
        $this->tmpl = 'shop/info.html';
    }

    public function news($shop_id, $page=1)
    {
        $shop = $this->check_shop($shop_id);
        if($items = K::M('shop/news')->items_by_shop($shop_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mall/shop:news', array($shop_id, '{page}'), array('shop'=>$shop)));
            $this->pagedata['items'] = $items;
        }
        $cate = K::M('shop/cate')->cate($shop['cat_id']);        
        $this->seo->set_shop($shop);
        $this->seo->init('shop', array('cate_name'=>$cate['title']));        
        $this->tmpl = 'shop/news.html';
    }

    public function mendian($shop_id, $page=1)
    {
        $shop = $this->check_shop($shop_id);
        if($items = K::M('shop/mendian')->items_by_shop($shop_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mall/shop:mendian', array($shop_id, '{page}'), array('shop'=>$shop)));
            $this->pagedata['items'] = $items;
        }
        $this->tmpl = 'shop/mendian.html';
    }

    public function newsdetail($news_id)
    {
        if(!$news_id = (int)$news_id){
            $this->error(404);
        }else if(!$detail = K::M('shop/news')->detail($news_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add('内容还未发布，暂不能访问', 211);
        }else if($shop = $this->check_shop($detail['shop_id'])){
            K::M('shop/news')->update_count($news_id, 'views', 1);
            $this->pagedata['detail'] = $detail;
            if($prev = K::M('shop/news')->items(array('shop_id'=>$shop_id, 'audit'=>1, 'news_id'=>'<:'.$news_id), array('news_id'=>'DESC'), 1, 1)){
                $pager['prev'] = array_shift($prev);
            }
            if($next = K::M('shop/news')->items(array('shop_id'=>$shop_id, 'audit'=>1, 'news_id'=>'>:'.$news_id), array('news_id'=>'ASC'), 1, 1)){
                $pager['next'] = array_shift($next);
            }
            $this->pagedata['pager'] = $pager;
            $cate = K::M('shop/cate')->cate($shop['cat_id']);        
            $this->seo->set_shop($shop);
            $this->seo->init('shop', array('cate_name'=>$cate['title']));            
            $this->tmpl = 'shop/newsdetail.html';
        }
    }

    public function mendiandetail($mendian_id)
    {
        if(!$mendian_id = (int)$mendian_id){
            $this->error(404);
        }else if(!$detail = K::M('shop/mendian')->detail($mendian_id)){
            $this->error(404);
        }else if(empty($detail['audit'])){
            $this->err->add('内容还未发布，暂不能访问', 211);
        }else if($shop = $this->check_shop($detail['shop_id'])){
            K::M('shop/mendian')->update_count($mendian_id, 'views', 1);
            $this->pagedata['detail'] = $detail;
            if($prev = K::M('shop/mendian')->items(array('shop_id'=>$shop_id, 'audit'=>1, 'mendian_id'=>'<:'.$mendian_id), array('mendian_id'=>'DESC'), 1, 1)){
                $pager['prev'] = array_shift($prev);
            }
            if($next = K::M('shop/mendian')->items(array('shop_id'=>$shop_id, 'audit'=>1, 'mendian_id'=>'>:'.$mendian_id), array('mendian_id'=>'ASC'), 1, 1)){
                $pager['next'] = array_shift($next);
            }
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'shop/mendiandetail.html';
        }
    }

    public function product($shop_id, $vcat_id=0, $page=null)
    {
        $shop = $this->check_shop($shop_id);
        $filter = $pager = array();
        $vcat_id = (int)$vcat_id;
        if($page === null && $vcat_id){
            $pager = $vcat_id;
            $vcat_id = 0;
        }
        $pager['vcat_id'] = $vcat_id;
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 12;
        $pager['count'] = $count = 0;
        $filter['shop_id'] = $shop_id;
        if($vcat_id){
            $filter['vcat_id'] = $vcat_id;
        }
        $filter['audit'] = 1;
        $filter['closed'] = 0;        
        if($items = K::M('product/product')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mall/shop:product', array($shop_id, $vcat_id, '{page}'), array('shop'=>$shop)));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $cate = K::M('shop/cate')->cate($shop['cat_id']);        
        $this->seo->set_shop($shop);
        $this->seo->init('shop', array('cate_name'=>$cate['title']));        
        $this->tmpl = 'shop/product.html';
    }

    public function coupon($shop_id, $page=1)
    {
        $shop = $this->check_shop($shop_id);
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;        
        if($items = K::M('shop/coupon')->items_by_shop($shop_id, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mall/shop:coupon', array($shop_id, '{page}'), array('shop'=>$shop)));
            $this->pagedata['items'] = $items;
        }
        $cate = K::M('shop/cate')->cate($shop['cat_id']);        
        $this->seo->set_shop($shop);
        $this->seo->init('shop', array('cate_name'=>$cate['title']));             
        $this->tmpl = 'shop/coupon.html';
    }

    public function comment($shop_id, $page=1)
    {
        $shop = $this->check_shop($shop_id);
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter['shop_id'] = $shop['shop_id'];
        $filter['closed'] = 0;
        if($items = K::M('shop/comment')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($shop_id, '{page}'), array('shop'=>$shop)));
            $uids = array();
            foreach($items as $v){
                $uids[$v['uid']] = $v['uid'];
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/view')->items_by_ids($uids);
            }
            $this->pagedata['items'] = $items;
        }
		$access = $this->system->config->get('access');
		$this->pagedata['comment_yz'] = $access['verifycode']['comment'];
        $this->pagedata['pager'] = $pager;
        $cate = K::M('shop/cate')->cate($shop['cat_id']);        
        $this->seo->set_shop($shop);
        $this->seo->init('shop', array('cate_name'=>$cate['title']));      
        $this->tmpl = 'shop/comments.html';
    }

    public function savecomment($shop_id)
    {
        $shop = $this->check_shop($shop_id);
        if($this->check_login()){
            $allow_comment = K::M('member/group')->check_priv($this->MEMBER['group_id'], 'allow_comment');
            if($allow_comment < 0){
                $this->err->add('您是【'.$this->MEMBER['group_name'].'】没有权限发表点评', 333);
            }else if($this->uid == $shop['uid']){
                $this->err->add('不能点评自己的店铺', 101);
            }else if(!$data = $this->checksubmit('data')){
                $this->err->add('非法的数据提交', 214);
            }else if(!$data = $this->check_fields($data, 'score,content')){
                $this->err->add('非法的数据提交', 214);
            }else {
				$verifycode_success = true;
				$access = $this->system->config->get('access');
				if($access['verifycode']['comment']){
					if(!$verifycode = $this->GP('verifycode')){
						$verifycode_success = false;
						$this->err->add('验证码不正确', 212);
					}else if(!K::M('magic/verify')->check($verifycode)){
						$verifycode_success = false;
						$this->err->add('验证码不正确', 212);
					}
				}
				if($verifycode_success){
					$data['shop_id'] = $shop_id;
					$data['uid'] = $this->uid;
					$data['audit'] = $allow_comment;
					$data['city_id'] = $shop['city_id'];
					if($comment_id = K::M('shop/comment')->create($data)){
                        
						$this->err->add('发表评价成功');
					}
				}
            }
        }
    }
    public function book($shop_id=null)
    {
        if(!($shop_id = (int)$shop_id) && !($shop_id = (int)$this->GP('shop_id'))){
            $this->error(404);
        }else if($data = $this->checksubmit('data')){
            $shop = $this->check_shop($shop_id);
            $product = array();
            if($product_id = (int)$data['product_id']){
                if($product = K::M('product/product')->detail($product_id)){
                    if($product['shop_id'] = $shop_id){
                        $data['product_id'] = $product_id;
                    }
                }
            }
            if(empty($product)){
                unset($data['product_id']);
            }
            $data['shop_id'] = $shop_id;
            $data['uid'] = (int)$this->uid;
			$data['city_id'] = $shop['city_id'];
            if(K::M('shop/yuyue')->create($data)){
                $smsdata = $maildata = array('contact'=>$data['contact'], 'mobile'=>$data['mobile'], 'shop_phone'=>$shop['phone'], 'shop_name'=>$shop['name'], 'shop_url'=>$shop['shop_url']);
                if($product){
                    $smsdata['product_name'] = $product['name'];
                    $maildata['product_name'] = $product['name'];
                    K::M('sms/sms')->send($data['mobile'], 'product_yuyue', $smsdata);
                    K::M('sms/sms')->shop($shop, 'product_tongzhi', $smsdata);
                    K::M('helper/mail')->sendshop($shop, 'product_yuyue', $maildata);
                }else{
                    K::M('sms/sms')->send($data['mobile'], 'shop_yuyue', $smsdata);
                    K::M('sms/sms')->shop($shop, 'shop_tongzhi', $smsdata);
                    K::M('helper/mail')->sendshop($shop, 'shop_yuyue', $maildata);
                }
                $this->err->add('预约成功，稍后商家会与您取得联系');
                $this->system->cookie->set('LAST_Mobile', $data['mobile']);
                $this->system->cookie->set('LAST_Contact', $data['contact']);
            }
        }else{
            $shop = $this->check_shop($product['shop_id']);
            if(!$mobile = $this->system->cookie->get('LAST_Mobile')){
                $mobile = $this->MEMBER['mobile'];
            }
            $pager['mobile'] = $mobile;
            if($contact = $this->system->cookie->get('LAST_Contact')){
                $pager['contact'] = $contact;
            }
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'view:book/shop.html';
        }        
    }    

}