<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: company.mdl.php 6074 2014-08-12 17:10:33Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_Company extends Mdl_Table
{   
  
    protected $_table = 'company';
    protected $_pk = 'company_id';
    protected $_cols = 'company_id,uid,group_id,city_id,area_id,domain,title,name,thumb,logo,slogan,contact,phone,mobile,qq,addr,name,addr,xiaobao,lng,lat,score,score1,score2,score3,score4,score5,comments,tenders_num,tenders_sign,case_num,news_num,youhui_num,yuyue_num,last_case,site_num,last_site,views,verify_name,is_vip,orderby,audit,video,closed,flushtime,clientip,dateline';
    protected $_orderby = array('orderby'=>'ASC','flushtime'=>'DESC','company_id'=>'DESC');

    protected $_hot_orderby = array('score'=>'DESC','orderby'=>'ASC');
    protected $_hot_filter = array('audit'=>'1', 'closed'=>'0');
    protected $_new_orderby = array('company_id'=>'DESC');
    protected $_new_filter = array('audit'=>'1', 'closed'=>'0');
	
	protected $order_list = array(1=>array('title'=>'默认'),2=>array('title'=>'签单数'),3=>array('title'=>'信誉指数'));
	
	public function get_order(){
        
        return $this->order_list;
    }
    public function items($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
        if($attrs = $filter['attrs']){
            $attr_ids = join(',',$attrs);
            $attr_count = array_sum($attrs);
            $attr_sql = "SELECT company_id FROM ".$this->table('company_attr')." WHERE attr_value_id IN($attr_ids) GROUP BY company_id HAVING SUM(attr_value_id)=$attr_count";
        }
      
        unset($filter['attrs']);
        $where = $this->where($filter);
        if($attr_sql){
            $where .= " AND company_id IN($attr_sql)";
        }
        $orderby = $this->order($orderby, null);
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$this->table($this->_table)."  WHERE $where $orderby $limit";
        if($rs = $this->db->query($sql)){
            $count = $this->db->GetOne("SELECT FOUND_ROWS()");
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                $items[$row[$this->_pk]] = $row;
            }
        }
        return $items;
    }


    public function detail($company_id, $closed=false)
    {
        if(!$company_id = (int)$company_id){
            return false;
        }
        $where = "c.company_id='$company_id'";
        if(empty($closed)){
            $where .= " AND c.closed='0'";
        }
        $sql = "SELECT f.*,c.* FROM ".$this->table($this->_table)." c LEFT JOIN ".$this->table('company_fields')." f ON c.company_id=f.company_id WHERE $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }
    
    public function create($data, $checked=false)
    {
		
		$data['flushtime'] = __TIME;
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($company_id = $this->db->insert($this->_table, $data, true)){
            K::M('company/fields')->create(array('company_id'=>$company_id), true);
        }
        return $company_id;
    }

    public function update($pk, $data, $checked=false)
    {

        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }    

    /**
     * 根据用户UID取装修公司
     */
    public function company_by_uid($uid, $closed=false)
    {
        if(!$uid = (int)$uid){
            return false;
        }
        $where = "c.uid='$uid'";
        if(empty($closed)){
            $where .= " AND c.closed='0'";
        }
        $sql = "SELECT f.*,c.* FROM ".$this->table($this->_table)." c LEFT JOIN ".$this->table('company_fields')." f ON c.company_id=f.company_id WHERE $where";
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

    public function verify_name($uids, $verify=true)
    {
        if(!$uids = K::M('verify/check')->ids($uids)){
            return false;
        }
        $verify = $verify ? 1 : 0;
        return $this->db->update($this->_table, array('verify_name'=>$verify), "uid IN($uids)");
    }

    public function update_domain($company_id, $domain)
    {
        if(empty($domain)){
            $this->err->add('个性域名不能为空', 311);
        }else if(!preg_match('/^\w{3,10}$/i', $domain)){
            $this->err->add('个性域名只能为字母、数字、下划线，3~10长度', 311);
        }else{
            $cfg = K::$system->config->get('domain');
            if($cfg['holddomain'] && preg_match("/{$cfg['holddomain']}/i", $domain)){
                 $this->err->add('系统保留域名不可注册', 311);
            }else if($company = $this->company_by_domain($domain)){
                if($company['company_id'] != $company_id){
                    $this->err->add('您选域名太好已经被人抢注了', 3112);
                    return false;
                }
            }
            return $this->update($company_id, array('domain'=> $domain), true);
        }
        return false;
    } 

    public function company_by_domain($domain)
    {
        if(!preg_match('/^\w{3,10}$/', $domain)){
            return false;
        }
        $where = "c.domain='$domain'";
        if(empty($closed)){
            $where .= " AND c.closed='0'";
        }
        $sql = "SELECT f.*,c.* FROM ".$this->table($this->_table)." c LEFT JOIN ".$this->table('company_fields')." f ON c.company_id=f.company_id WHERE $where";
        if($detail = $this->db->GetRow($sql)){
            $detail = $this->_format_row($detail);
        }
        return $detail;
    }

    protected function _check($data, $company_id=null)
    {
        if($uid = (int)$data['uid']){
            if($member = K::M('member/member')->detail($uid)){
                if($member['from'] != 'company'){
                    $this->err->add("所属会员必须为装修公司类型", 451);
                    return false;
                }else if($company = $this->company_by_uid($uid)){
                    if(empty($company_id)){
                        $this->err->add("该用户已经隶属于:{$company['name']}(ID:{$company['company_id']})，不能重复关联", 452);
                        return false;
                    }else if($company_id != $company['company_id']){
                        $this->err->add("该用户已经隶属于:{$company['name']}(ID:{$company['company_id']})，不能重复关联", 453);
                        return false;
                    }
                }
            }else{
                $data['uid'] = 0;
            }
        }
        return parent::_check($data, $company_id);
    }

    protected function _format_row($row)
    {
        static $group_list = null, $default_group = null, $site_cfg = null, $kfqq='';
        if($group_list === null){
            $group_list = K::M('member/group')->items_by_from('company');
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
        if($area_id = $row['area_id']){
            if($area = K::M('data/area')->area($area_id)){
                $row['area_name'] = $area['area_name'];
            }
        }
        if(empty($row['logo'])){
            $row['logo'] = 'default/company_logo.jpg';
        }
        if(empty($row['thumb'])){
            $row['thumb'] = 'default/company_thumb.jpg';
        }
        $row['avg_score'] = 0;
        $row['avg_scores'] = array('score'=>0,'score1'=>0,'score2'=>0,'score3'=>0,'score4'=>0,'score5'=>0);
        if($row['comments']){
            $row['avg_score'] = $row['avg_scores']['score'] = round($row['score'] / $row['comments'], 1);
            $row['avg_scores']['score1'] = round($row['score1'] / $row['comments'], 1);
            $row['avg_scores']['score2'] = round($row['score2'] / $row['comments'], 1);
            $row['avg_scores']['score3'] = round($row['score3'] / $row['comments'], 1);
            $row['avg_scores']['score4'] = round($row['score4'] / $row['comments'], 1);
            $row['avg_scores']['score5'] = round($row['score5'] / $row['comments'], 1);            
        }
        if($group['priv']['show_phone'] < 0){
            $row['show_phone'] = $site_cfg['phone'];
            $row['show_qq'] = $kfqq;
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
        $row['company_url'] = $this->_company_url($row);
        return $row;                
    }

    protected function _company_url($row)
    {
        static $site = null, $domain_company=null;

        if($site === null){
            $site = K::$system->config->get('site');
            $domain_company = K::$system->_CFG['domain']['company'];
        }
        if($domain_company && $row['domain']){
            return 'http://'.$row['domain'].'.'.$domain_company;
        }
        $link = K::M('helper/link')->mklink('company',array($row['company_id']), array(), 'city:'.$row['city_id'], $site['rewrite']);
        return $link;
    }    

    public function block_items()
    {
        
    }

    public function format_items_ext($items)
    {
        if(empty($items)){
            return false;
        }
        $uids = $company_ids = array();
        foreach((array)$items as $k=>$v){
            if($v['uid']){
                $uids[$v['uid']] = $v['uid'];
            }
            $company_ids[$v['company_id']] = $v['company_id'];
        }
        if($uids){
            $member_list = K::M('member/member')->items_by_ids($uids);
        }
        foreach((array)$items as $k=>$v){
            $member = array();
            if(!$member = $member_list[$v['uid']]){
                $member = array();
            }
            $v['ext'] = array('member'=>$member);
            $items[$k] = $v;            
        }
        return $items;
    }
}