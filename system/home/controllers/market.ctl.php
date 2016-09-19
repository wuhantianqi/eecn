<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Market extends Ctl 
{
    
    public function index()
    {
        
    }

    public function adclick($item_id)
    {
        if($item = K::M('adv/item')->detail($item_id)){
            K::M('adv/item')->update_count($item_id, 'clicks');
            if(preg_match('/(http|https)\:\/\//i', $item['link'])){
                header('Location:'.$item['link']);
            }
            exit();
        }else{
            $this->error(404);
        }
    }

}