<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: payment_log.php 3053 2014-01-15 02:00:13Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'log_id' => 
  array (
    'field' => 'log_id',
    'label' => 'log_id',
    'pk' => true,
    'add' => true,
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
  'from' => 
  array (
    'field' => 'from',
    'label' => 'from',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '类型',
    'default' => '',
    'SO' => 'like',
  ),
  'payment' => 
  array (
    'field' => 'payment',
    'label' => 'payment',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '支付接口',
    'default' => '',
    'SO' => 'like',
  ),
  'trade_no' => 
  array (
    'field' => 'trade_no',
    'label' => 'trade_no',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '支付流水号',
    'default' => '',
    'SO' => 'like',
  ),
  'payed' => 
  array (
    'field' => 'payed',
    'label' => 'payed',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'boolean',
    'comment' => '0:未支付，1:已支付',
    'default' => '',
    'SO' => '=',
  ),
  'payedip' => 
  array (
    'field' => 'payedip',
    'label' => 'payedip',
    'pk' => false,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'text',
    'comment' => '支付成功时IP',
    'default' => '',
    'SO' => false,
  ),
  'payedtime' => 
  array (
    'field' => 'payedtime',
    'label' => 'payedtime',
    'pk' => false,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'unixtime',
    'comment' => '支付成功通知时间',
    'default' => '',
    'SO' => 'betweendate',
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
    'list' => true,
    'type' => 'clientip',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'dateline' => 
  array (
    'field' => 'dateline',
    'label' => 'dateline',
    'pk' => false,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => 'betweendate',
  ),
);