<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: member.ctl.php 5937 2014-07-28 01:04:06Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Member extends Ctl_Ucenter 
{
    protected $_allow_fields = 'mail,gender,from,mobile,Y,M,D,city_id,realname';
    public function index()
    {
        $this->pagedata['order_count'] = K::M('trade/order')->count_by_uid($this->uid);
        $this->pagedata['yuyue_company_count'] = K::M('company/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['yuyue_designer_count'] = K::M('designer/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['yuyue_mechanic_count'] = K::M('mechanic/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['yuyue_shop_count'] = K::M('shop/yuyue')->count(array('uid'=>$this->uid));
        $this->pagedata['tenders_count'] = K::M('tenders/tenders')->count(array('uid'=>$this->uid));
        $this->tmpl = 'ucenter/member/index.html';
    }

    public function info()
    {
        if($account = $this->checksubmit('account')) {
            if ($this->MEMBER['verify_mobile']) {
                unset($account['mobile']); //认证后不允许修改手机
            }
            if ($this->MEMBER['verify_mail']) {
                unset($account['mail']); //认证后不允许修改邮箱
            }
            if($this->MEMBER['from'] != 'member'){
                unset($account['from']);
            }
            if (!$account = $this->check_fields($account, $this->_allow_fields)) {
                $this->err->add('非法的数据提交', 201);
                if ($this->MEMBER['verify_mobile']) {
                    unset($account['mobile']); //认证后不允许修改手机
                }
            }else if(K::M('member/member')->update($this->uid, $account)) {
                $this->err->add('更新个人资料成功');
            }
        }else{
            $this->pagedata['from_list'] = K::M('member/member')->from_list();
            $this->tmpl = 'ucenter/member/info.html';
        }        
    }

    public function passwd()
    {
        if($account = $this->checksubmit('account')){
            if(md5($account['old_passwd']) != $this->MEMBER['passwd']){
                $this->err->add('原密码不正确', 211);
            }else if($account['passwd'] != $account['confirm_passwd']){
                $this->err->add('两次输入的密码不相同', 212);
            }else if(K::M('member/account')->check_passwd($account['passwd'])){
                if($this->auth->update_passwd($account['passwd'], false)){
                    $this->err->add('修改密码成功');
                }
            }
        }else{
           $this->tmpl = 'ucenter/member/passwd.html';
        }        
    }

    public function mail()
    {
        if($account = $this->checksubmit('account')){
            if($this->MEMBER['verify_mail'] && (md5($account['passwd']) != $this->MEMBER['passwd'])){
                $this->err->add('原密码不正确', 211);
            }else if(K::M('member/account')->check_mail($account['newmail'])){
                if($this->auth->update_mail($account['newmail'])){
                    $this->err->add('更换邮箱成功');
                }
            }
        }else{
           $this->tmpl = 'ucenter/member/mail.html';
        }  
    }

    public function face()
    {
        $this->tmpl = 'ucenter/member/face.html';
    }

    public function upload()
    {
        if(!$data = file_get_contents("php://input")){
            $this->err->add('图片数据上传失败',201);
        }else if(K::M('member/face')->update_face($this->uid, null, $data)){
            $this->err->add('更新会员头像成功');
        }
        $this->err->json();
    }

    public function bindaccount()
    {
        $this->system->config->get('connect');
        $bind_list = K::M('connect/connect')->items(array('uid'=>$this->uid), null, 1, 10);
        if($wechatCfg = $this->system->config->get('wechat')){
            if($member_wechat = K::M('member/weixin')->detail($this->uid)){
                $this->pagedata['member_wechat'] = $member_wechat;
            }else{
                if($admin_weixin = K::M('weixin/weixin')->admin()){
                    if($admin_weixin['type'] == 1){
                        $data = array('uid'=>$uid, 'type'=>'bind');
                        if($scene_id = K::M('weixin/authcode')->create($data)){
                            Import::L('weixin/wechat.class.php');
                            $client = new WechatClient($admin_weixin['appid'], $admin_weixin['secret']);
                            if($ticket = $client->getQrcodeTicket(array('scene_id'=>$scene_id, 'expire'=>1800))){
                                $wechat_bind_qr = $client->getQrcodeImgUrlByTicket($ticket);
                                $this->pagedata['wechat_bind_qr'] = $wechat_bind_qr;
                            }
                        }
                    }
                    $this->pagedata['admin_weixin'] = $admin_weixin;
                }
            }            
        }
        if($bind_list){
            foreach($bind_list as $v){
                if($v['type'] == 1){
                    $this->pagedata['qq_bind'] = true;
                }else if($v['type'] == 2){
                    $this->pagedata['wb_bind'] = true;
                }
            }
        }
        $this->pagedata['bind_list'] = $bind_list;
        $this->tmpl = 'ucenter/member/bindaccount.html';
    }

    public function gold()
    {
        $this->system->config->get('gold');
        $this->pagedata['pay_list'] = K::M('payment/payment')->fetch_all();
        $this->tmpl = 'ucenter/member/gold.html';
    }

    public function logs($type=null, $page=0)
    {
        $filter = $pager = array();
        if(is_numeric($type)){
            $page = $type;
            $type = null;
        }else if($type == 'in'){
            $filter['number'] = ">:0";
        }else if($type == 'out'){
            $filter['number'] = "<:0";
        }
        $pager['type'] = $type;
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['uid'] = $this->uid;
        $filter['from'] = 'gold';
        if ($items = K::M('member/log')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('ucenter/member:logs', array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/member/logs.html';
    }

    public function coupon($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter = array('uid'=>$this->uid);
        if($items = K::M('shop/couponDownload')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
            $coupon_ids = array();
            foreach($items as $k=>$v){
                $coupon_ids[$v['coupon_id']] = $v['coupon_id'];
                $shop_ids[$v['shop_id']] = $v['shop_id'];
            }
            if($coupon_ids){
                $this->pagedata['coupon_list'] = K::M('shop/coupon')->items_by_ids($coupon_ids);
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/member/coupon.html';
    }

    public function group()
    {
        $this->pagedata['group_list'] = K::M('member/group')->items_by_from($this->MEMBER['from']);
        $this->tmpl = 'ucenter/member/group/group.html';
    }

    public function home($uid)
    {
        if($this->MEMBER['from'] == 'shop'){
            $shop = $this->ucenter_shop();
            $url = $shop['shop_url'];
        }else if($this->MEMBER['from'] == 'company'){
            $company = $this->ucenter_company();
            $url = $company['company_url'];
        }else if($this->MEMBER['from'] == 'gz'){
            $gz = $this->ucenter_gz();
            $url = $this->mklink('gz:detail', $this->uid);
        }else if($this->MEMBER['from'] == 'designer'){
            $designer = $this->ucenter_designer();
            $url = $this->mklink('blog', $this->uid);            
        }else if($this->MEMBER['from'] == 'mechanic'){
            $mechanic = $this->ucenter_mechanic();
            $url = $this->mklink('mechanic:detail', $this->uid);
        }else{
            $this->error(404);
        }
        header("Location:{$url}");
        exit;
    }

}