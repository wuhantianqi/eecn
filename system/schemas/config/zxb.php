<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * #fileid#
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'zxb_yezhu' => 
  array (
    'label' => '已服务业主起始数',
    'field' => 'zxb_yezhu',
    'type' => 'number',
    'default' => '5000',
    'comment' => '装修保累计已服务业主数以此基础增加',
    'html' => false,
    'empty' => false,
  ),
  'zxb_price' => 
  array (
    'label' => '累计交易额起始数',
    'field' => 'zxb_price',
    'type' => 'number',
    'default' => '',
    'comment' => '装修保累计交易额数以此基础增加',
    'html' => false,
    'empty' => false,
  ),
  'zxb_company' => 
  array (
    'label' => '累计认证装修公司起始数',
    'field' => 'zxb_company',
    'type' => 'number',
    'default' => '',
    'comment' => '装修保累计认证装修公司以此基础增加',
    'html' => false,
    'empty' => false,
  ),
  'hetong' => 
  array (
    'label' => '合同下载路径',
    'field' => 'hetong',
    'type' => 'text',
    'default' => '',
    'comment' => '填写装修保合同下载地址',
    'html' => false,
    'empty' => false,
  ),
);