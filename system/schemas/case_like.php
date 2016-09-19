<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: case_like.php 2216 2013-12-16 06:39:13Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

return array (
  'like_id' => 
  array (
    'field' => 'like_id',
    'label' => 'ID',
    'pk' => true,
    'add' => false,
    'edit' => false,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => false,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
   'case_id' => 
  array (
    'field' => 'case_id',
    'label' => '案例',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => false,
    'show' => false,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => '=',
  ),
    'uid' => 
  array (
    'field' => 'uid',
    'label' => '用户',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => true,
    'type' => 'number',
    'comment' => '',
    'default' => '',
    'SO' => '=',
  ),
   'clientip' => 
  array (
    'field' => 'clientip',
    'label' => 'clientip',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'clientip',
    'comment' => '',
    'default' => '0.0.0.0',
    'SO' => false,
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
    'show' => false,
    'list' => true,
    'type' => 'dateline',
    'comment' => '',
    'default' => '',
    'SO' => 'betweendate',
  ),   
    
    
);