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
  'wechat_token' => 
  array (
    'label' => '微信Token',
    'field' => 'wechat_token',
    'type' => 'text',
    'default' => '',
    'comment' => '微信接口Token,全站统一Token',
    'html' => false,
    'empty' => false,
  ),
  'robot_open' => 
  array (
    'label' => '启用机器人',
    'field' => 'robot_open',
    'type' => 'boolean',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => true,
  ),
  'robot_tuling' => 
  array (
    'label' => '图灵机器人',
    'field' => 'robot_tuling',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => true,
  ),
  'robot_rand' => 
  array (
    'label' => '随机回复内容',
    'field' => 'robot_rand',
    'type' => 'textarea',
    'default' => '',
    'comment' => '当上面接口返回内容异常时会随机取当前的内容进行回复，按行一条回复输入',
    'html' => false,
    'empty' => true,
  ),
);