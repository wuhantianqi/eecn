<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: bulletin.php 5739 2014-06-30 08:26:09Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'open_shop' => 
  array (
    'label' => '商铺中心公告',
    'field' => 'open_shop',
    'type' => 'boolean',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => true,
  ),
  'shop' => 
  array (
    'label' => '商铺中心公告',
    'field' => 'shop',
    'type' => 'textarea',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => true,
  ),
  'open_member' => 
  array (
    'label' => '个人中心公告',
    'field' => 'open_member',
    'type' => 'boolean',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => true,
  ),
  'member' => 
  array (
    'label' => '公告内容',
    'field' => 'member',
    'type' => 'textarea',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => true,
  ),
  'open_company' => 
  array (
    'label' => '企业公告',
    'field' => 'open_company',
    'type' => 'boolean',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => true,
  ),
  'company' => 
  array (
    'label' => '公告内容',
    'field' => 'company',
    'type' => 'textarea',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => true,
  ),
);