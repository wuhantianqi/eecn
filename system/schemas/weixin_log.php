<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'log_id' => 
  array (
    'field' => 'log_id',
    'label' => 'ID',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'weixin' => 
  array (
    'field' => 'weixin',
    'label' => '微信ID',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => 'like',
  ),
  'data' => 
  array (
    'field' => 'data',
    'label' => '数据',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => true,
    'empty' => true,
    'show' => true,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'post' => 
  array (
    'field' => 'post',
    'label' => 'POST',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => true,
    'empty' => true,
    'show' => true,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'dateline' => 
  array (
    'field' => 'dateline',
    'label' => '时间',
    'pk' => false,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);