<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: modifier.filter.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

/**
 * 敏感词替换
 */
function smarty_modifier_taglink($content, $limit=5)
{
    static $link = null;
    if($link === null){
        $link = K::M('article/link');
    }
    return $link->filter($content, $limit);
}