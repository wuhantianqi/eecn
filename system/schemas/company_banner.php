<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: company_banner.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'banner_id' => 
  array (
    'field' => 'banner_id',
    'label' => 'ID',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'company_id' => 
  array (
    'field' => 'company_id',
    'label' => '公司',
    'pk' => false,
    'add' => true,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => '=',
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
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => 'like',
  ),
  'photo' => 
  array (
    'field' => 'photo',
    'label' => '图片',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'photo',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'link' => 
  array (
    'field' => 'link',
    'label' => '链接',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
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
    'show' => false,
    'list' => true,
    'type' => 'number',
    'comment' => '数字越小排序越高',
    'default' => '5',
    'SO' => false,
  ),
);