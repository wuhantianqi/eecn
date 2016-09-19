<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tenders.php 5531 2014-06-19 10:26:25Z youyi $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

return array(
    'id' =>
    array(
        'field' => 'id',
        'label' => 'ID',
        'pk' => true,
        'add' => false,
        'edit' => false,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'title' =>
    array(
        'field' => 'title',
        'label' => '标题',
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
    'city_id' =>
    array(
        'field' => 'city_id',
        'label' => '城市',
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
    'area_id' =>
    array(
        'field' => 'area_id',
        'label' => '地区',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'area',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'uid' =>
    array(
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
    'name' =>
    array(
        'field' => 'name',
        'label' => '称呼',
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
    'mobile' =>
    array(
        'field' => 'mobile',
        'label' => '手机',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => false,
        'show' => false,
        'list' => true,
        'type' => 'mobile',
        'comment' => '',
        'default' => '',
        'SO' => 'like',
    ),
    'home_id' =>
    array(
        'field' => 'home_id',
        'label' => '小区ID',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'home_name' =>
    array(
        'field' => 'home_name',
        'label' => '小区名称',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'text',
        'comment' => '可为空，在没有小区ID的选择的时候',
        'default' => '',
        'SO' => false,
    ),
    'type_id' =>
    array(
        'field' => 'type_id',
        'label' => '招标类型',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'style_id' =>
    array(
        'field' => 'style_id',
        'label' => '设计风格',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'budget_id' =>
    array(
        'field' => 'budget_id',
        'label' => '预算范围',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'service_id' =>
    array(
        'field' => 'service_id',
        'label' => '服务需求',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'house_type_id' =>
    array(
        'field' => 'house_type_id',
        'label' => '空间户型',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'addr' =>
    array(
        'field' => 'addr',
        'label' => '地址',
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
    'demand' =>
    array(
        'field' => 'demand',
        'label' => '其他需求',
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
    'feedback' =>
    array(
        'field' => 'feedback',
        'label' => '客服反馈',
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
    'start_time' =>
    array(
        'field' => 'start_time',
        'label' => '开始时间',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'date',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'area' =>
    array(
        'field' => 'area',
        'label' => '面积',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'gold' =>
    array(
        'field' => 'gold',
        'label' => '看标需要消耗金币',
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
        'SO' => false,
    ),
    'num' =>
    array(
        'field' => 'num',
        'label' => '允许看标数',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'number',
        'comment' => '',
        'default' => '3',
        'SO' => false,
    ),
    'num2' =>
    array(
        'field' => 'num2',
        'label' => '参加看标的数',
        'pk' => false,
        'add' => false,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'pv_num' =>
    array(
        'field' => 'pv_num',
        'label' => '浏览量',
        'pk' => false,
        'add' => false,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => false,
        'type' => 'number',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'sign_mater_id' =>
    array(
        'field' => 'sign_mater_id',
        'label' => '签约商家',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'int',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'sign_time' =>
    array(
        'field' => 'sign_time',
        'label' => '签约时间',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'unixtime',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'status' =>
    array(
        'field' => 'status',
        'label' => '招标状态',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'int',
        'comment' => '',
        'default' => '',
        'SO' => '=',
    ),
    'money' =>
    array(
        'field' => 'money',
        'label' => '预算',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'int',
        'comment' => '',
        'default' => '0',
        'SO' => '=',
    ),
    'dingjin' =>
    array(
        'field' => 'dingjin',
        'label' => '定金数',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'int',
        'comment' => '',
        'default' => '0',
        'SO' => '=',
    ),
    'is_pay' =>
    array(
        'field' => 'is_pay',
        'label' => '资金是否已托管',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'int',
        'comment' => '',
        'default' => '0',
        'SO' => '=',
    ),
    'audit' =>
    array(
        'field' => 'audit',
        'label' => '是否审核',
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
    'create_ip' =>
    array(
        'field' => 'create_ip',
        'label' => 'IP',
        'pk' => false,
        'add' => true,
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
    array(
        'field' => 'dateline',
        'label' => '创建时间',
        'pk' => false,
        'add' => true,
        'edit' => false,
        'html' => false,
        'empty' => true,
        'show' => false,
        'list' => true,
        'type' => 'dateline',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
    'photo' =>
    array(
        'field' => 'photo',
        'label' => '图片',
        'pk' => false,
        'add' => true,
        'edit' => true,
        'html' => false,
        'empty' => false,
        'show' => false,
        'list' => true,
        'type' => 'photo',
        'comment' => '',
        'default' => '',
        'SO' => false,
    ),
);