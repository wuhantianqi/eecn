<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: view.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

Import::M('admin/base');
class Mdl_Admin_View extends Mdl_Admin_Base
{
    
    public function admin($id=0,$name='')
    {
        if($id = intval($id)){
            $where = "admin_id='$id'";
        }else if($name = trim($name)){
            $where = "admin_name='$name'";
        }else{
            return false;
        }
        return $this->db->GetRow("SELECT * FROM ".$this->table($this->_table)." WHERE $where");
    }

    protected function _format_row($row)
    {
        if($row['last_login']){
            $row['format_last_login'] = date("Y-m-d H:i:s", $row['last_login']);
        }else{
            $row['format_last_login'] = '未曾登录';
        }
        return $row;       
    }

}