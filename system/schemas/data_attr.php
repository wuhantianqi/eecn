<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: data_attr.php 2034 2013-12-07 03:08:33Z $
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
    'show' => true,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'title' => 
  array (
    'field' => 'title',
    'label' => '属性名称',
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
  'from' => 
  array (
    'field' => 'from',
    'label' => '关联模块',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'select',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'multi' => 
  array (
    'field' => 'multi',
    'label' => '是否多选',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'radio',
    'comment' => '',
    'default' => 'Y',
    'SO' => false,
  ),
  'index' => 
  array (
    'field' => 'index',
    'label' => '首页显示',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'radio',
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
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);