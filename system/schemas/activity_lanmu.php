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
  'lanmu_id' => 
  array (
    'field' => 'lanmu_id',
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
  'activity_id' => 
  array (
    'field' => 'activity_id',
    'label' => '活动',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'title' => 
  array (
    'field' => 'title',
    'label' => '标题',
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
    'SO' => false,
  ),
  'content' => 
  array (
    'field' => 'content',
    'label' => '内容',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => true,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'editor',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'orderby' => 
  array (
    'field' => 'orderby',
    'label' => '排序',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '50',
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
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);