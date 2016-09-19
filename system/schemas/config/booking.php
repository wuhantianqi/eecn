<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: booking.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'max_look' => 
  array (
    'label' => ' 最大投标数',
    'field' => 'max_look',
    'type' => 'number',
    'default' => '',
    'comment' => '一个预约最多可多少商家查看',
    'html' => false,
    'empty' => false,
  ),
  'look_gold' => 
  array (
    'label' => '默认金币',
    'field' => 'look_gold',
    'type' => 'number',
    'default' => '',
    'comment' => '查看预约所花的金币',
    'html' => false,
    'empty' => false,
  ),
);