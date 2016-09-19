<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_System_Themebak extends Mdl_Table
{   
  
    protected $_table = 'themes_bak';
    protected $_pk = 'bak_id';
    protected $_cols = 'bak_id,admin,tmpl,content,dateline';

    
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

    protected function _format_row($row)
    {
        //list($row['admin_id'], $row['admin_name']) = each(explode(':', $row['admin']));
        //list($row['theme'], $row['tpl']) = each(explode(':', $row['tmpl']));
        return $row;
    }

	function dir_decode($path)
	{
		$path = base64_decode($path);
		$path = trim(stripslashes($path),'/');
		return $path;
		
	}

	function dir_encode($path)
	{
		if(strpos($path,':')){
			preg_match("/(\w+):([\w+\/\.]+)/i", $path, $m);
			if($pre = trim(dirname($m[2]),'/')){
				if($pre !='.' && $pre !='..'){
					$url = '-'.base64_encode($pre);
					return $url;
				}
			}
		}else{
			$url = base64_encode($path);
			return $url;
		}
	}

    public function items_by_tmpl($tmpl)
    {
        $items = array();
        $sql = "SELECT * FROM ".$this->table($this->_table)." tmpl='{$tmpl}'";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                $items[$row['bak_id']] = $row;
            }
        }
        return $items;
    }
}