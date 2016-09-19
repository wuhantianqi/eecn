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
  'url' => 
  array (
    'label' => '3G版域名',
    'field' => 'url',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'forward' => 
  array (
    'label' => '自动跳转',
    'field' => 'forward',
    'type' => 'boolean',
    'default' => '',
    'comment' => '开启后用户使用手机浏览器访问社区论坛功能页以外页面时自动跳转到论坛首页进行访问',
    'html' => false,
    'empty' => false,
  ),
);