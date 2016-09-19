<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: diary_detail.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'detail_id' => 
  array (
    'field' => 'detail_id',
    'label' => 'ID',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'diary_id' => 
  array (
    'field' => 'diary_id',
    'label' => '日记ID',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'status' => 
  array (
    'field' => 'status',
    'label' => '状态',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'contents' => 
  array (
    'field' => 'contents',
    'label' => '内容',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => true,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'editor',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'create_ip' => 
  array (
    'field' => 'create_ip',
    'label' => '创建IP',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'clientip',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'dateline' => 
  array (
    'field' => 'dateline',
    'label' => '创建时间',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);