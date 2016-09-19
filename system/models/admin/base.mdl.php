<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: base.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Admin_Base extends Mdl_Table
{
    protected $_table = 'admin';
    protected $_pk = 'admin_id';
    protected $_cls = 'admin_id,admin_name,passwd,role_id,last_login,closed,dateline';
	protected $_orderby = array('admin_id'=>'ASC');
	protected $_pre_cache_key = 'admin-admin-list';

}