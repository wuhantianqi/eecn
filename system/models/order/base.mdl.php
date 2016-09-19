<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: base.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_order_Base extends Mdl_Table
{   
    
    protected $_table = 'order';
    protected $_pk = 'order_id';
    protected $_cols = 'order_id,city_id,shop_id,product_id,uid,contact,from,mobile,date,qq,address,notice,payment,audit,closed,clientip,dateline';
}