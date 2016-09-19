<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tracking.mdl.php 4417 2014-04-08 09:28:59Z langzhong $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Tenders_Tracking extends Mdl_Table
{   
  
    protected $_table = 'tenders_tracking';
    protected $_pk = 'tracking_id';
    protected $_cols = 'tracking_id,look_id,content,create_ip,dateline,reply,reply_time';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }
    
    public function getNoReplyCountByTendersIds($ids){
        if(empty($ids)) return array();
        foreach($ids as $k=>$v){
            $ids[$k] = (int) $v;
        }
        $idstr = join(',',$ids);
        $sql = " SELECT a.tenders_id,count(b.tracking_id) as num from ".$this->table($this->_table)." b JOIN  ".$this->table('tenders_look')." a  ON(b.look_id =a.look_id) where a.tenders_id in({$idstr}) AND b.reply is null  group by  a.tenders_id  ";
        $items = array();
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                    $items[$row['tenders_id']] = $row['num'];
            }
        }
        return $items;
    }
    
    
}