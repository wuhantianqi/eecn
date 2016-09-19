<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: member.php 2893 2014-01-08 04:58:58Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'fans_id' => 
  array (
    'field' => 'fans_id',
    'label' => 'fans_id',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => '=',
  ),
    'myid' => 
  array (
    'field' => 'myid',
    'label' => 'myid',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => '=',
  ),
    'uid' => 
  array (
    'field' => 'uid',
    'label' => 'uid',
    'pk' => true,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => true,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => '=',
  ),
    'dateline' => 
  array (
    'field' => 'dateline',
    'label' => 'dateline',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => true,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => '=',
  ),
);
    