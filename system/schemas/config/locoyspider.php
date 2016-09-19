<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: locoyspider.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'Authorize' => 
  array (
    'label' => '通信密钥',
    'field' => 'Authorize',
    'type' => 'text',
    'default' => '',
    'comment' => '在WEB发布模块中填写相同的值',
    'html' => false,
    'empty' => false,
  ),
  'Autothumb' => 
  array (
    'label' => '自动缩略图',
    'field' => 'Autothumb',
    'type' => 'boolean',
    'default' => '',
    'comment' => '会自动提取文章中的第一张图为缩略图',
    'html' => false,
    'empty' => false,
  ),
  'Loadimage' => 
  array (
    'label' => '保存远程图片',
    'field' => 'Loadimage',
    'type' => 'boolean',
    'default' => '',
    'comment' => '自动保存远程图片到本地',
    'html' => false,
    'empty' => false,
  ),
);