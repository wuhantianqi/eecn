<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: modifier.iplocal.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

function smarty_modifier_iplocal($dotip, $format=null)
{
    return K::M('misc/location')->location($dotip);
}