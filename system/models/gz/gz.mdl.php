<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Gz_Gz extends Mdl_Table
{   
  
    protected $_table = 'gz';
    protected $_pk = 'uid';
    protected $_cols = 'uid,group_id,city_id,area_id,name,qq,mobile,case_num,site_num,yuyue_num,views,comments,tenders_num,tenders_sign,attention_num,score,score1,score2,score3,slogan,about,orderby,flushtime,audit,closed,dateline';
	
    protected $_orderby = array('orderby'=>'ASC','flushtime'=>'DESC','uid'=>'DESC');
	protected $order_list = array(1=>array('title'=>'默认'),2=>array('title'=>'案例数'),3=>array('title'=>'工地数'),4=>array('title'=>'预约数'));
	
	public function get_order(){
        
        return $this->order_list;
    }

    public function create($data, $account=null, $checked=false)
    {
		$data['dateline'] = $data['flushtime'] = __TIME;
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }else if($uid = $data['uid']){
            $this->db->insert($this->_table, $data);
            return $uid;
        }else if($account){
            $account['from'] = 'gz';
            if($data['mobile']){
                $account['mobile'] = $data['mobile'];
            }
            if($uid = K::M('member/account')->create($account)){
                $this->update($uid, $data, true);
                return $uid;
            }
        }
        return false;
    }
    
    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        if($ret = $this->db->update($this->_table, $data, $this->field($this->_pk, $pk))){
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
            $attr_sql = "SELECT uid FROM ".$this->table('gz_attr')." WHERE attr_value_id IN($attr_ids) GROUP BY uid HAVING SUM(attr_value_id)=$attr_count";
        }
      
        unset($filter['attrs']);
        $where = $this->where($filter, 'd.');
        if($attr_sql){
            $where .= " AND d.uid IN($attr_sql)";
        }
        $orderby = $this->order($orderby, null, 'd.');
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS m.*,d.* FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('member')." m ON m.uid=d.uid WHERE $where $orderby $limit";
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
        foreach($filter as $k=>$v){          
            if(substr($k, 0, 7) == 'member.'){
                $member_filter[substr($k, 7)] = $v;
                unset($filter[$k]);
            }
        }

        $where = $this->where($filter, 'g.');
        if($member_filter){
            $member_where = K::M('member/member')->where($member_filter, 'm.');
            if(!empty($where)){
                $where .= " AND {$member_where}";
            }else{
                $where = $member_where;
            }
            $count_sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." g LEFT JOIN ".$this->table('member')." m ON m.uid=g.uid WHERE $where";
        }else{
            $count_sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." g WHERE $where";
        }
    
        $orderby = $orderby ? $orderby : $this->_orderby;  
        $orderby = $this->order($orderby, null, 'g.');
        $limit = $this->limit($p, $l);
        $items = array();
        if($count = $this->db->GetOne($count_sql)){
            $sql = "SELECT m.*,g.* FROM ".$this->table($this->_table)." g LEFT JOIN ".$this->table('member')." m ON m.uid=g.uid WHERE $where $orderby $limit";
            if($rs = $this->db->Execute($sql)){
                while($row = $rs->fetch()){
                    $row = $this->_format_row($row);
                     $items[$row['uid']] = $row;
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
        $where = "g.uid='$uid'";
        if($closed){
            $where .= " AND d.closed='0'";
        }
        $sql = "SELECT m.*,g.uid gz_id,g.* FROM ".$this->table($this->_table)." g LEFT JOIN ".$this->table('member')." m ON m.uid=g.uid WHERE $where";
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
            $group_list = K::M('member/group')->items_by_from('gz');
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
        $row['gz_id'] = $row['uid'];
        if($city = K::M('data/city')->city($row['city_id'])){
            $row['city_name'] = $city['city_name'];
        }
        if($area = K::M('data/area')->area($row['area_id'])){
            $row['area_name'] = $area['area_name'];
        }
        if($cate = K::M('data/cate')->cate($row['cate_id'])){
            $row['cate_title'] = $cate['title'];
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

}