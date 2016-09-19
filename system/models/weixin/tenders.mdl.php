<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Tenders extends Mdl_Table 
{    
    protected $_table = 'weixin_tenders';
    protected $_pk = 'tenders_id';
    protected $_cols = 'tenders_id,openid';
    protected $_orderby = array('tenders_id'=>'DESC');

    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function update_openid($tenders_id, $openid)
    {
        if(!$tenders_id = (int)$tenders_id){
            return false;
        }else if(!$openid = addslashes($openid)){
            return false;
        }else if($count = $this->count("tenders_id=$tenders_id")){
            return $this->update($tenders_id, array('openid'=>$openid));
        }else{
            return $this->create(array('tenders_id'=>$tenders_id, 'openid'=>$openid), true);
        }
    }

    public function detail($tenders_id, $closed=false)
    {
        if(!$tenders_id = (int)$tenders_id){
            return false;
        }
        $sql = "SELECT w.openid,t.* FROM ".$this->table($this->_table)." w LEFT JOIN ".$this->table('tenders')." t ON w.tenders_id=t.tenders_id WHERE w.tenders_id='$tenders_id'";
        if($row = $this->db->GetRow($sql)){
            $row = K::M('tenders/tenders')->format_row($row);
        }
        return $row;
    }

    public function items_by_openid($openid)
    {
        $sql = "SELECT w.openid,t.* FROM ".$this->table($this->_table)." w LEFT JOIN ".$this->table('tenders')." t ON w.tenders_id=t.tenders_id WHERE w.openid='$openid' ORDER BY t.tenders_id DESC";
        $items = array();
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $items[$row['tenders_id']] = K::M('tenders/tenders')->format_row($row);
            }
        }
        return $items;       
    }
}