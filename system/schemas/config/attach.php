<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: attach.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'dir' => 
  array (
    'label' => '附件保存位置',
    'field' => 'dir',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'url' => 
  array (
    'label' => '附件URL地址',
    'field' => 'url',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'watermarktype' => 
  array (
    'label' => '水印类型',
    'field' => 'watermarktype',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
);