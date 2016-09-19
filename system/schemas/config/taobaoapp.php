<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: taobaoapp.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'taoke_pid' => 
  array (
    'label' => '淘宝客PID',
    'field' => 'taoke_pid',
    'type' => 'text',
    'default' => '',
    'comment' => '淘宝客PID返利到的帐户',
    'html' => false,
    'empty' => false,
  ),
);