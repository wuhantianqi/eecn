<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Weixin extends Mdl_Table
{   
  
    protected $_table = 'weixin';
    protected $_pk = 'wx_id';
    protected $_cols = 'wx_id,uid,city_id,wx_sid,wx_name,weixin,admin,type,face,appid,secret,token,access_token,expires_in,addon';

    
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

    public function detail_by_sid($wx_sid)
    {
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE wx_sid='$wx_sid'";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    public function  weixin_by_uid($uid)
    {
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE uid='$uid'";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;        
    }

    public function admin($city_id=0)
    {
        $city_id = (int)$city_id;
        $cache_key = 'wechat-admin-'.$city_id;
        if(!$row = K::$system->cache->get($cache_key)){
            $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE admin=1 AND city_id=$city_id";
            if($row = $this->db->GetRow($sql)){
                $row = $this->_format_row($row);
            }
            K::$system->cache->set($cache_key, $row);
        }
        return $row;            
    }

    public function admin_wechat_client($city_id=0)
    {
        static $clients = array();
        $city_id = (int)$city_id;
        if(!$client = $clients[$city_id]){
            if($weixin_admin = $this->admin($city_id)){
                Import::L('weixin/wechat.class.php');
                $client = new WechatClient($weixin_admin['appid'], $weixin_admin['secret']);
                $client->weixin_type = $weixin_admin['type'];
                $clients[$city_id] = $client;
            }
        }
        return $client;
    }

    protected function _format_row($row)
    {
        $row['addon'] = unserialize($row['addon']);
        return $row;
    }

    protected function _check($data, $id=null)
    {
        if(isset($data['addon'])){
            $data['addon'] = serialize($data['addon']);
        }
        return parent::_check($data);
    }
}