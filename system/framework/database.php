<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: database.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class DB
{
    
    public static $db = null;
    public static $tablepre = '';

    public static function update($table, $data, $condition, $low_priority=false)
    {

    }

    public static function insert($table, $data, $return_insert_id=false, $replace=false)
    {
    	return self::$db->insert($this->table);
    }

    public static function table($table)
    {
    	return self::$tablepre.$table;
    }
}