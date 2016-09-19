<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product_attr.php 2335 2013-12-18 17:15:56Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'product_id' => 
  array (
    'field' => 'product_id',
    'label' => '商品ID',
    'pk' => true,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'attr_id' => 
  array (
    'field' => 'attr_id',
    'label' => '属性ID',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'attr_value_id' => 
  array (
    'field' => 'attr_value_id',
    'label' => '属性值ID',
    'pk' => true,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);