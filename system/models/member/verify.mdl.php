<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: verify.mdl.php 5610 2014-06-23 16:47:52Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Member_Verify extends Mdl_Table
{   
  
    protected $_table = 'member_verify';
    protected $_pk = 'uid';
    protected $_cols = 'uid,name,id_number,id_photo,mobile,verify,refuse,verify_time,request_ip,request_time';
    protected static $_verify_from = array('0'=>'null', '1'=>'pass', '2'=>'refuse');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        $data['request_ip'] = $data['request_ip'] ? $data['request_ip'] : __IP;
        $data['request_time'] = $data['request_time'] ? $data['request_time'] : __CFG::TIME;
        return $this->db->insert($this->_table, $data);
    }

    public function update($uid, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check($data,  $uid)){
            return false;
        }
        $data['verify'] = 0;
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $uid));
    }

    public function refuse($uids, $refuse='')
    {
        if(!empty($uids)) {
            if(is_array($uids)){
                $uids = implode(',', $uids);
            }
            if(!K::M('verify/check')->ids($uids)){
                return false;
            }
            $uids = explode(',', $uids);
            $refuse = K::M('content/html')->encode($refuse);
            return $this->db->update($this->_talbe, array('refuse'=>$refuse), self::field($this->_pk, $uids));
        }
        return false;
    }

	public function items_by_city($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
        $where = $this->where($filter, 'd.');
        $orderby = $this->order($orderby, null, 'd.');
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS m.*,d.* FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('member')." m ON m.uid=d.uid WHERE $where and m.city_id=".CITY_ID." $orderby $limit";
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

    public function update_verify($uids, $verify='pass', $refuse='')
    {
        if(!$uids = K::M('verify/check')->ids($uids)){
            return false;
        }else if($verify == 'refuse'){
            $refuse = K::M('content/html')->encode($refuse);
			$uid_arr = explode(',',$uids);
			foreach($uid_arr as $k => $v){
				if($this->db->update($this->_table, array('refuse'=>$refuse, 'verify'=>2), self::field($this->_pk, $v))){
					K::M('member/magic')->verify_name($v, false);
				}
			}
           
        }else if($verify == 'pass'){            
            if($items = $this->items_by_ids($uids)){
                $ids = array();
                foreach($items as $k=>$v){
                    if($v['verify'] != 1){
                        $ids[$v['uid']] = $v['uid'];
                    }
                }
                if($this->db->update($this->_table, array('verify'=>1, 'verify_time'=>__CFG::TIME), self::field($this->_pk, $ids))){
                    K::M('member/magic')->verify_name($uids, true);
                    if($gold = K::M('system/integral')->integral('certification')){
                        K::M('member/gold')->update($ids, $gold, '实名认证通过');
                    }
                }                 
            }

        }else{
            if($this->db->update($this->_table, array('verify'=>0), self::field($this->_pk, $uids))){
                K::M('member/magic')->verify_name($uids, false);
            }             
        }
        return true;
    }

    protected function _format_row($row)
    {
        $row['verify_from'] = self::$_verify_from[$row['verify']];
        return $row;
    }

    protected function _check($data, $uid=null)
    {
        unset( $data['refuse'], $data['verify_time']);
        if(empty($uid) || isset($data['name'])){
            if(empty($data['name'])){
                $this->err->add('名称不能为空', 451);
                return false;
            }
            $data['name'] = K::M('content/html')->encode($data['name']);
        }
        if(empty($uid) || isset($data['mobile'])){
            if(!K::M('verify/check')->mobile($data['mobile'])){
                $this->err->add('手机格式不合法', 452);
                return false;                
            }
        }
        if(isset($data['id_number'])){
            if(!preg_match('/^[a-z0-9]+$/i', $data['id_number'])){
                $this->err->add('证件号码不合法，只能为字母和数字', 453);
                return false;  
            }
        }
        if(isset($data['id_photo'])){
            $data['id_photo'] = K::M('content/html')->encode($data['id_photo']);
        }
        return parent::_check($data, $uid);
    }
}