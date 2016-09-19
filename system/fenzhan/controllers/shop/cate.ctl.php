<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cate.ctl.php 5639 2014-06-25 06:37:14Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Shop_Cate extends Ctl
{
    public function children($pid=null)
    {
        if(!is_numeric($pid)){
            return false;
        }
        $cats = array();
        if($childrens = K::M('shop/cate')->childrens($pid)){
            foreach($childrens as $k=>$v){
                $cats[] = array('cat_id'=>$v['cat_id'], 'parent_id'=>$v['parent_id'], 'title'=>$v['title']);
            }
        }
        $this->err->set_data('cats', $cats);        
        $this->err->json();        
    }   
}