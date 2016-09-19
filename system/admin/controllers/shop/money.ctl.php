<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: money.ctl.php 4168 2014-03-26 06:19:32Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Money extends Ctl
{
    
    public function index($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 30;
        $filter = array('closed'=>0);
        if($items = K::M('shop/shop')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $pager['total_money'] = K::M('shop/money')->total_money();
            $this->pagedata['items'] = $items;
            $uids = array();
            foreach($items as $k=>$v){
                if($uid = $v['uid']){
                    $uids[$uid] = $uid;
                }
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        }       
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/money/items.html';
    }

    public function log($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 30;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['log']){$filter['log'] = "LIKE:%".$SO['log']."%";}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        $filter['shop_id'] = $shop_id;
        if($items = K::M('shop/money')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            $this->pagedata['items'] = $items;
            $shop_ids = array();
            foreach($items as $k=>$v){
                if($shop_id = $v['shop_id']){
                    $shop_ids[$shop_id] = $shop_id;
                }
            }
            if($shop_ids){
                $this->pagedata['shop_list'] = K::M('shop/shop')->items_by_ids($shop_ids);
            }
        }            
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:shop/money/logs.html';
    }

    public function shop($shop_id, $page=1)
    {
        $filter = $pager = array();
        if(!$shop_id = (int)$shop_id){
            $this->err->add('未指定要查看的商铺', 211);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id)){
            $this->err->add('您要查看的商铺不存在或已经删除', 212);
        }else{
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 30;
            if($SO = $this->GP('SO')){
                $pager['SO'] = $SO;
                if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
            }
            $filter['shop_id'] = $shop_id;
            if($items = K::M('shop/money')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
            }
            $this->pagedata['shop'] = $shop;
            $this->pagedata['items'] = $items;
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'admin:shop/money/shop.html';
        }   
    }

    public function tixian($shop_id=null)
    {
        if(!($shop_id = (int)$shop_id) && !($shop_id = (int)$this->GP('shop_id'))){
            $this->err->add('未指定要查看的商铺', 211);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id)){
            $this->err->add('商铺不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            $money = round($data['money'], 2);
            if(empty($money) || ($money < 0)){
                $this->err->add('提现金额必须大于0', 213);
            }else if($money > $shop['money']){
                $this->err->add('提现金额不能大于现有余额', 214);
            }else if(!$log = trim($data['log'])){
                $this->err->add('没有填写提现备注说明', 215);
            }else{
                $log = '管理员('.$this->admin->admin['admin_name'].','.$this->admin->admin['admin_id'].')提现:'.$log;
                if(K::M('shop/shop')->update_money($shop_id, -$money, $log)){
                    $this->err->add('商铺提现成功');
                    //$this->err->set_data('foreard', $this->mklink('shop/money:shop', array($shop_id)));
                }
            }
        }else{
            $this->pagedata['shop'] = $shop;
            $this->tmpl = 'admin:shop/money/tixian.html';
        }
    }

    public function chongzhi($shop_id=null)
    {
        if(!($shop_id = (int)$shop_id) && !($shop_id = (int)$this->GP('shop_id'))){
            $this->err->add('未指定要查看的商铺', 211);
        }else if(!$shop = K::M('shop/shop')->detail($shop_id)){
            $this->err->add('商铺不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            $money = round($data['money'], 2);
            if(empty($money) || ($money < 0)){
                $this->err->add('充值金额必须大于0', 213);
            }else if(!$log = trim($data['log'])){
                $this->err->add('没有填写充值备注说明', 215);
            }else{
                $log = '管理员('.$this->admin->admin['admin_name'].
                    ','.$this->admin->admin['admin_id'].')充值:'.$log;
                if(K::M('shop/shop')->update_money($shop_id, $money, $log)){
                    $this->err->add('商铺充值成功');
                }
            }
        }else{
            $this->pagedata['shop'] = $shop;
            $this->tmpl = 'admin:shop/money/chongzhi.html';
        }
    }


    public function so()
    {
        $this->tmpl = 'admin:shop/money/so.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{

                if($id = K::M('shop/money')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?shop/money-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:shop/money/create.html';
        }
    }

    public function doaudit($id=null)
    {
        if($id = (int)$id){
            if(K::M('shop/money')->batch($id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('id')){
            if(K::M('shop/money')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

}