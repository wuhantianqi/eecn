<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * #fileid#
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'qq_is_open' => 
  array (
    'label' => 'QQ联合登录开关',
    'field' => 'qq_is_open',
    'type' => 'radio',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'qq_app_id' => 
  array (
    'label' => 'QQ APP ID',
    'field' => 'qq_app_id',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'qq_app_key' => 
  array (
    'label' => 'QQ APP KEY',
    'field' => 'qq_app_key',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
);