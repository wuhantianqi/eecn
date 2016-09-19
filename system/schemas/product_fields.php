<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product_fields.php 2755 2014-01-04 12:40:28Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'product_id' => 
  array (
    'field' => 'product_id',
    'label' => 'ID',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'int',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'seo_title' => 
  array (
    'field' => 'seo_title',
    'label' => 'SEO标题',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'seo_keywords' => 
  array (
    'field' => 'seo_keywords',
    'label' => 'SEO关键字',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'seo_description' => 
  array (
    'field' => 'seo_description',
    'label' => 'SEO描述',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'textarea',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'info' => 
  array (
    'field' => 'info',
    'label' => '商品描述',
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
  'clientip' => 
  array (
    'field' => 'clientip',
    'label' => 'clientip',
    'pk' => false,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'clientip',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);