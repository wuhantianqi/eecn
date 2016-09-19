<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Member_Group extends Mdl_Table
{   
  
    protected $_table = 'member_group';
    protected $_pk = 'group_id';
    protected $_cols = 'group_id,group_name,from,icon,priv,orderby';
    protected $_orderby = array('from'=>'ASC','orderby'=>'DESC');
    protected $_pre_cache_key = 'member-group-list';

    
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

    public function update_priv($group_id, $priv)
    {
        $data = addslashes(serialize($priv));
        if($rs = $this->update($group_id, array('priv'=>$data), true)){
            $this->clear_cache($group_id);
        }
        return $rs;
    }


    public function items_by_privs($privs)
    {
		
		if(strpos($privs,',') && !is_array($privs)){
			$priv_arr = explode(',',$privs);
		}else if(!is_array($privs)){
			$priv_arr[] = $privs;
		}else{
			$priv_arr = $privs;
		}
		$count = count($priv_arr);
        $items = $this->fetch_all();
		$tmp = array();
		foreach($items as $k => $v){
			foreach($priv_arr as $kk => $vv){
				if($v["priv"][$vv] == '1'){
					$tmp[$k][] = $v['group_name'];
				}
			}
		}
		foreach($tmp as $k => $v){
			if(count($v) != $count){
				unset($tem[$k]);
			}else{
				$item[$k] = $v[0];
			}
		}
		return $item;
    }

    public function options($from='member')
    {
        $options = array();
        if($items = $this->fetch_all()){
            foreach($items as $v){
                if($v['from'] == $from){
                    $options[$v['group_id']] = $v['group_name'];
                }
            }
        }
        return $options;
    }

    public function items_by_from($from='member')
    {
        $group_list = array();
        if($items = $this->fetch_all()){
            foreach($items as $v){
                if($v['from'] == $from){
                    $group_list[$v['group_id']] = $v;
                }
            }
        }
        return $group_list;
    }

    public function group($group_id)
    {
        if($items = $this->fetch_all()){
            return $items[$group_id];
        }
        return false;
    }

    public function check_priv($gid, $priv, &$group_name='')
    {
        if(!$group = $this->group($gid)){
            return -1;
        }
        $group_name = $group_name['group_name'];
        if(isset($group['priv'][$priv])) return (int)$group['priv'][$priv];
        return 0;
    }

    public function default_group($from='member')
    {
        if($items = $this->fetch_all()){
            foreach($items as $v){
                if($v['from'] == $from){
                    return $v;
                }
            }
        }
        return false;
    }

    protected function _format_row($row)
    {
        static $from_list = null;
        if($from_list === null){
            $from_list = K::M('member/member')->from_list();
        }
        $row['from_title'] = $from_list[$row['from']];
        if($row['priv']){
            $row['priv'] = unserialize(stripslashes($row['priv']));
        }
        return $row;
    }
}