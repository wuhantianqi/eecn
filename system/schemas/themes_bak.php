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
  'bak_id' => 
  array (
    'field' => 'bak_id',
    'label' => 'ID',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'admin' => 
  array (
    'field' => 'admin',
    'label' => '操作者',
    'pk' => false,
    'add' => true,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'tmpl' => 
  array (
    'field' => 'tmpl',
    'label' => '文件',
    'pk' => false,
    'add' => true,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'content' => 
  array (
    'field' => 'content',
    'label' => '内容',
    'pk' => false,
    'add' => true,
    'edit' => false,
    'html' => true,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'textarea',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'dateline' => 
  array (
    'field' => 'dateline',
    'label' => '备份时间',
    'pk' => false,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);