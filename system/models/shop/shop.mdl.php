<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: shop.mdl.php 6243 2014-09-09 05:37:52Z maoge $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Shop extends Mdl_Table
{   
  
    protected $_table = 'shop';
    protected $_pk = 'shop_id';
    protected $_cols = 'shop_id,uid,group_id,money,cat_id,city_id,area_id,verify_name,is_vip,domain,title,name,logo,thumb,contact,phone,xiaobao,views,credit,score,comments,tenders_num,tenders_sign,products,lng,lat,orderby,audit,flushtime,closed,dateline';
    protected $_orderby = array('orderby'=>'ASC','flushtime'=>'DESC', 'shop_id'=>'DESC');

    protected $_hot_orderby = array('score'=>'DESC','views'=>'ASC');
    protected $_hot_filter = array('audit'=>'1', 'closed'=>0);
    protected $_new_orderby = array('shop_id'=>'DESC');
    protected $_new_filter = array('audit'=>'1', 'closed'=>0);    

    public function detail($shop_id, $closed=false)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $where = "s.shop_id=$shop_id";
        if(empty($closed)){
            $where .= " AND s.closed='0'";
        }        
        $sql = "SELECT f.*,s.* FROM ".$this->table($this->_table)." s LEFT JOIN ".$this->table('shop_fields')." f ON s.shop_id=f.shop_id WHERE $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    public function shop_by_uid($uid, $closed=false)
    {
        if(!$uid = (int)$uid){
            return false;
        }
        $where = "s.uid=$uid";
        if(empty($closed)){
            $where .= " AND s.closed='0'";
        }         
        $sql = "SELECT f.*,s.* FROM ".$this->table($this->_table)." s LEFT JOIN ".$this->table('shop_fields')." f ON s.shop_id=f.shop_id WHERE $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;        
    }

    public function items_by_uids($uids)
    {
        if(!$uids = K::M('verify/check')->ids($uids)){
            return false;
        }
        $order = $this->order();
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE uid IN($uids) AND closed=0 $order";
        $items = array();
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $items[$row['uid']] = $this->_format_row($row);
            }
        }
        return $items;
    }    

    public function update_domain($shop_id, $domain)
    {
        if(empty($domain)){
            $this->err->add('个性域名不能为空', 311);
        }else if(!preg_match('/^\w{3,10}$/i', $domain)){
            $this->err->add('个性域名只能为字母、数字、下划线，3~10长度', 311);
        }else{
            if($cfg['holddomain'] && preg_match("/{$cfg['holddomain']}/i", $domain)){
                 $this->err->add('系统保留域名不可注册', 311);
            }else if($shop = $this->shop_by_domain($domain)){
                if($shop['shop_id'] != $shop_id){
                    $this->err->add('您选域名太好已经被人抢注了', 311);
                    return false;
                }
            }
            return $this->update($shop_id, array('domain'=>$domain), true);
        }
        return false;
    } 

    public function shop_by_domain($domain, $closed=false)
    {
        if(!preg_match('/^\w{3,10}$/', $domain)){
            return false;
        }
        $where = "s.domain='$domain'";
        if(empty($closed)){
            $where .= " AND s.closed='0'";
        }         
        $sql = "SELECT f.*,s.* FROM ".$this->table($this->_table)." s LEFT JOIN ".$this->table('shop_fields')." f ON s.shop_id=f.shop_id WHERE $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;           
    }

    public function create($data, $checked=false)
    {
		$data['dateline'] = $data['flushtime'] = __TIME;
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($shop_id = $this->db->insert($this->_table, $data, true)){
            K::M('shop/fields')->create(array('shop_id'=>$shop_id), true);
        }
        return $shop_id;
    }

    public function update($shop_id, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $shop_id)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $shop_id));
    }

    public function update_money($shop_id, $money, $log)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }else if(!is_numeric($money)){
            $this->err->add('金额不合法', 213);
            return false;
        }else if(empty($log)){
            $this->err->add('日志不能空', 213);
            return false;
        }
        $sql = "UPDATE ".$this->table($this->_table)." SET `money`=`money`+{$money} WHERE shop_id='$shop_id'";
        if($res = $this->db->Execute($sql)){
            $audit = $money > 0 ? 1 : 0;
            $a = array('shop_id'=>$shop_id, 'money'=>$money, 'audit'=>$audit, 'log'=>$log);
            K::M('shop/money')->create($a);
        }
        return $res;        
    }

    public function update_score($shop_id, $score=3, $count=1)
    {
        $shop_id = (int)$shop_id;
        $score = (int)$score;
        $count = (int)$count;
        $this->update($shop_id, array('score'=>"`score`+{$score}", 'comments'=>'`comments`+1'), true);        
    }

    public function verify_name($uids, $verify=true)
    {
        if(!$uids = K::M('verify/check')->ids($uids)){
            return false;
        }
        $verify = $verify ? 1 : 0;
        return $this->db->update($this->_table, array('verify_name'=>$verify), "uid IN($uids)");
    }

    protected function _format_row($row)
    {
        static $group_list = null, $default_group = null, $site_cfg = null, $kfqq='';
        if($group_list === null){
            $group_list = K::M('member/group')->items_by_from('shop');
            reset($group_list);
            $default_group = current($group_list);
            $site_cfg = K::$system->config->get('site');
            if ($qqs = @explode(',', $site_cfg['kfqq'])){
                $kfqq = $qqs[0];
            }             
        }
        if(!$group = $group_list[$row['group_id']]){
            $group = $default_group;
        }
        $row['group_name'] = $group['group_name'];
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }        
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }
        if($area_id = $row['area_id']){
            if($city = K::M('data/area')->area($area_id)){
                $row['area_name'] = $city['area_name'];
            }
        }
        if($cat_id = $row['cat_id']){
            if($cate = K::M('shop/cate')->cate($cat_id)){
                $row['cat_title'] = $cate['title'];
            }
        }
        if(isset($row['logo']) && empty($row['logo'])){
            $row['logo'] = 'default/shop_logo.jpg';
        }
        if(isset($row['thumb']) && empty($row['thumb'])){
            $row['thumb'] = 'default/shop_thumb.jpg';
        }        
        if(isset($row['banner']) && empty($row['banner'])){
            $row['banner'] = 'default/shop_banner.jpg';
        }
        $row['avg_score'] = 0;
        if($row['comments'] && $row['score']){
            $row['avg_score'] = $row['score'] / $row['comments'];
        }

        if($group['priv']['show_phone'] < 0){
            $row['show_phone'] = $site_cfg['phone'];
            $row['show_qq'] = $site_cfg['kfqq'];
        }else{
            $row['show_phone'] = $row['phone'];
            $row['show_qq'] = $row['qq'];
        }
        if($site_cfg['site_400']){
            $row['show_phone'] = $site_cfg['site_400'];
            if($row['fenji_400']){
                $row['show_phone'] .= '-'.$row['fenji_400'];
            }
        }
        $row['group'] = $group;        
        $row['shop_url'] = $this->_shop_url($row);
        return $row;
    }

    protected function _shop_url($row)
    {
        static $site = null, $domain_shop = null;
        if($site === null){
            $site = K::$system->config->get('site');
            $domain_shop = K::$system->_CFG['domain']['shop'];
        }
        if($domain_shop && $row['domain']){            
            return 'http://'.$row['domain'].'.'.$domain_shop;
        }
        $link = K::M('helper/link')->mklink('mall/shop',array($row['shop_id']), array(), 'city:'.$row['city_id'], $site['rewrite']);
        return $link;
    }    

    protected function _check($data, $shop_id=null)
    {
        if($uid = (int)$data['uid']){
            if($member = K::M('member/member')->detail($uid)){
                if($member['from'] != 'shop'){
                    $this->err->add("所属会员必须为商家类型", 451);
                    return false;
                }else if($shop = $this->shop_by_uid($uid)){
                    if(empty($shop_id)){
                        $this->err->add("该用户已经隶属于:{$shop['title']}(ID:{$shop['shop_id']})，不能重复关联", 452);
                        return false;
                    }else if($shop_id != $shop['shop_id']){
                        $this->err->add("该用户已经隶属于:{$shop['title']}(ID:{$shop['shop_id']})，不能重复关联", 453);
                        return false;
                    }
                }
            }else{
                $data['uid'] = 0;
            }
        }
        return parent::_check($data, $shop_id);  
    }
}