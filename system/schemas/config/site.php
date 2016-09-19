<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: site.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'title' => 
  array (
    'label' => '网站名称',
    'field' => 'title',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'siteurl' => 
  array (
    'label' => '网站网址',
    'field' => 'siteurl',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'secret' => 
  array (
    'label' => '网站密钥',
    'field' => 'secret',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'mail' => 
  array (
    'label' => '联系邮箱',
    'field' => 'mail',
    'type' => 'mail',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'kfqq' => 
  array (
    'label' => '客服QQ',
    'field' => 'kfqq',
    'type' => 'text',
    'default' => '',
    'comment' => '多个QQ用\\",\\"分隔',
    'html' => false,
    'empty' => false,
  ),
  'phone' => 
  array (
    'label' => '联系电话',
    'field' => 'phone',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'logo' => 
  array (
    'label' => '网站LOGO',
    'field' => 'logo',
    'type' => 'photo',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => true,
  ),
  'weixin' => 
  array (
    'label' => '微信二维码',
    'field' => 'weixin',
    'type' => 'photo',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => true,
  ),  
  'city_id' => 
  array (
    'label' => '默认城市',
    'field' => 'city_id',
    'type' => 'city',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'multi_city' => 
  array (
    'label' => '开启多城市',
    'field' => 'multi_city',
    'type' => 'boolean',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'domain' => 
  array (
    'label' => '根域名',
    'field' => 'domain',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'intro' => 
  array (
    'label' => '网站简介',
    'field' => 'intro',
    'type' => 'textarea',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
  'tongji' => 
  array (
    'label' => '统计代码',
    'field' => 'tongji',
    'type' => 'textarea',
    'default' => '',
    'comment' => '',
    'html' => true,
    'empty' => false,
  ),
  'icp' => 
  array (
    'label' => '备案信息',
    'field' => 'icp',
    'type' => 'text',
    'default' => '',
    'comment' => '',
    'html' => false,
    'empty' => false,
  ),
);