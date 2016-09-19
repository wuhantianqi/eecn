<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: shop_attr.php 2335 2013-12-18 17:15:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'attr_id' => 
  array (
    'field' => 'attr_id',
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
  'title' => 
  array (
    'field' => 'title',
    'label' => '属性',
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
  'cat_id' => 
  array (
    'field' => 'cat_id',
    'label' => '分类',
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
    'SO' => '=',
  ),
  'multi' => 
  array (
    'field' => 'multi',
    'label' => '多选',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => 'Y',
    'SO' => false,
  ),
  'filter' => 
  array (
    'field' => 'filter',
    'label' => '检索',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => 'Y',
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
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);