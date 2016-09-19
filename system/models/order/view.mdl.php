<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: view.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

Import::M('order/base');
class Mdl_order_View extends Mdl_order_Base
{   
  
    
    public function items_by_shop($shop_id, $p=1, $l=50, &$count=0)
    {
    	if(!$shop_id = (int)$shop_id){
    		return false;
    	}
    	return $this->items(array('shop_id'=>$shop_id, 'closed'=>0), null, $p, $l, $count);
    }

    public function items_by_uid($uid, $p=1, $l=50, &$count=0)
    {
    	if(!$uid = (int)$uid){
    		return false;
    	}
    	return $this->items(array('uid'=>$uid, 'closed'=>0), null, $p, $l, $count);
    }    
}