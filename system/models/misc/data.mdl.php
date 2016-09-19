<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Misc_Data
{    
    
    public function yuyue($status=null)
    {
        static $status_list = array('0'=>'未处理','1'=>'意向', '2'=>'已签','-1'=>'无效');
        static $status_list2 = array('0'=>'<b class="red">未处理</b>','1'=>'<b class="orange">意向</b>', '2'=>'<b class="blue">已签</b>','-1'=>'<b>无效</b>');
        if($status === null){
            return $status_list;
        }else{
            return $status_list2[$status];
        }
    }
}