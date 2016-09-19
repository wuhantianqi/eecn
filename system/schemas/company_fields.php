<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: company_ex.php 5425 2014-06-05 11:33:31Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

return array(
    'company_id' =>
    array(
        'field' => 'company_id',
        'label' => 'ID',
        'pk' => true,
        'add' => true,
        'edit' => false,
        'html' => false,
        'empty' => false,
        'show' => false,
        'list' => true,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'info' =>
    array(
        'field' => 'info',
        'label' => '公司介绍',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => true,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'editor',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'skin' =>
    array(
        'field' => 'skin',
        'label' => '模板',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'text',
        'comment' => '',
        'default' => '',
        'SO' => 'like',
    ),
	 'seo_title' => 
  array (
    'field' => 'seo_title',
    'label' => 'SEO标题',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'seo_keyword' => 
  array (
    'field' => 'seo_keyword',
    'label' => 'SEO关键字',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'text',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
  'seo_description' => 
  array (
    'field' => 'seo_description',
    'label' => 'SEO描述',
    'pk' => false,
    'add' => true,
    'edit' => true,
    'html' => false,
    'empty' => true,
    'show' => false,
    'list' => false,
    'type' => 'textarea',
    'comment' => '',
    'default' => '',
    'SO' => false,
  ),
);
