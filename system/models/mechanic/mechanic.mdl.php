<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: mechanic.mdl.php 2754 2014-01-04 11:36:20Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Mechanic_Mechanic extends Mdl_Table
{   
  
    protected $_table = 'mechanic';
    protected $_pk = 'uid';
    protected $_cols = 'uid,group_id,name,city_id,area_id,mobile,qq,yuyue_num,tenders_num,tenders_sign,about,views,orderby,flushtime,audit,closed';
    protected $_orderby = array('orderby'=>'ASC','flushtime'=>'DESC', 'views'=>'DESC');
	
	protected $order_list = array(1=>array('title'=>'默认'),2=>array('title'=>'预约数'));
	
	public function get_order(){
        
        return $this->order_list;
    }

    public function create($data, $account=null, $checked=false)
    {
		$data['flushtime'] = __TIME;
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }else if($uid = (int)$data['uid']){
            $this->db->insert($this->_table, $data);
            return $uid;
        }else if($account){
            $account['from'] = 'mechanic';
            if($uid = K::M('member/account')->create($account)){
                $this->update($uid, $data, true);
                return $uid;
            }
        }
        return false;   
    }

    public function update($uid, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $uid)){
            return false;
        }

        if($ret = $this->db->update($this->_table, $data, $this->field($this->_pk, $uid))){
			if($group_id = (int)$data['group_id']){
				K::M('member/member')->update($uid, array('group_id'=>$group_id), true);
			}		
		}
		return $ret;
    }
        
    public function items_by_attr($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
        if($attrs = $filter['attrs']){
            $attr_ids = join(',',$attrs);
            $attr_count = array_sum($attrs);
            $attr_sql = "SELECT uid FROM ".$this->table('mechanic_attr')." WHERE attr_value_id IN($attr_ids) GROUP BY uid HAVING SUM(attr_value_id)=$attr_count";
        }      
        unset($filter['attrs']);
        $where = $this->where($filter, 'mm.');
        if($attr_sql){
            $where .= " AND mm.uid IN($attr_sql)";
        }
        $orderby = $this->order($orderby, null, 'mm.');
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS m.*,mm.* FROM ".$this->table($this->_table)." mm LEFT JOIN ".$this->table('member')." m ON m.uid=mm.uid WHERE $where $orderby $limit";
        if($rs = $this->db->query($sql)){
            $count = $this->db->GetOne("SELECT FOUND_ROWS()");
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                if($this->_pk){
                    $items[$row[$this->_pk]] = $row;
                }else{
                    $items[] = $row;
                }
            }
        }
        return $items;
    }
    
    public function items($filter=array(), $orderby=null, $p=1, $l=50, &$count=0)
    {
        $member_filter = array();
        foreach((array)$filter as $k=>$v){          
            if(substr($k, 0, 7) == 'member.'){
                $member_filter[substr($k, 7)] = $v;
                unset($filter[$k]);
            }
        }

        $where = $this->where($filter,'d.');
        if($member_filter){
            $member_where = K::M('member/member')->where($member_filter, 'm.');
            if(!empty($where)){
                $where .= " AND {$member_where}";
            }else{
                $where = $member_where;
            }
            $count_sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('member')." m ON m.uid=d.uid WHERE $where";
        }else{
            $count_sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." d WHERE $where";
        }
    
        $orderby = $orderby ? $orderby : $this->_orderby;  
        $orderby = $this->order($orderby, null, 'd.');
        $limit = $this->limit($p, $l);
        $items = array();
        if($count = $this->db->GetOne($count_sql)){
            $sql = "SELECT m.*,d.uid mechanic_id,d.* FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('member')." m ON m.uid=d.uid WHERE $where $orderby $limit";
            if($rs = $this->db->Execute($sql)){
                while($row = $rs->fetch()){
                    $row = $this->_format_row($row);
                     $items[$row[$this->_pk]] = $row;
                }
            }
        }
        return $items;        
    }

    public function detail($uid, $closed=false)
    {
        if(!$uid = (int)$uid){
            return false;
        }
        $where = "d.uid='$uid'";
        if($closed){
            $where .= " AND d.closed='0'";
        }        
        $sql = "SELECT m.*,d.uid mechanic_id,d.* FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('member')." m ON m.uid=d.uid WHERE $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    public function items_by_ids($uids)
    {
       if(!$uids = K::M('verify/check')->ids($uids)){
            return false;
        }
        $filter['uid'] = explode(',', $uids);
        return $this->items($filter, null, 1, 500);
    }
       
    protected function _format_row($row)
    {
        static $group_list = null, $default_group = null, $site_cfg = null, $kfqq='';
        if($group_list === null){
            $group_list = K::M('member/group')->items_by_from('mechanic');
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
        $row = K::M('member/member')->format_row($row);
        $row['group_name'] = $group['group_name'];
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        if($area = K::M('data/area')->area($row['area_id'])){
            $row['area_name'] = $area['area_name'];
        }
        if($cate = K::M('data/cate')->cate($row['cate_id'])){
            $row['cate_title'] = $cate['title'];
        }
        if($group['priv']['show_phone'] < 0){
            $row['show_phone'] = $site_cfg['phone'];
            $row['show_qq'] = $site_cfg['kfqq'];
        }else{
            $row['show_phone'] = $row['mobile'];
            $row['show_qq'] = $row['qq'];
        }
        if($site_cfg['site_400']){
            $row['show_phone'] = $site_cfg['site_400'];
            if($row['fenji_400']){
                $row['show_phone'] .= '-'.$row['fenji_400'];
            }
        }
        $row['group'] = $group;
        return $row;        
    }

    public function format_items_ext($items)
    {
        $uids = $attrs = array();
        foreach($items  as $k=>$v){
            $uids[$v['uid']] = $v['uid'];
        }
        if($uids){
            if($attrs_list = K::M('mechanic/attr')->items(array('uid'=>$uids), null, 1, 1000)){
                foreach($attrs_list as $v){
                    $attrs[$v['uid']][$v['attr_value_id']] = $v['attr_value_id'];
                }
            }
        }
        foreach($items as $k=>$v){
            if($attr = $attrs[$v['uid']]){
                $v['attrs'] = $attr;
            }
            $items[$k] = $v;
        }
        return $items;
    }

}