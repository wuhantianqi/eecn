<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 6072 2014-08-12 12:23:29Z youyi $
 */

class Widget_Member extends Model
{

    public function index(&$params)
    {
        
    }

    public function from(&$params)
    {
        if(!$params['tpl']){
            if(!in_array($params['type'], array('label', 'checkbox', 'radio', 'option'))){
                $params['type'] = 'option';
            }
            $params['tpl'] = 'widget:default/'.$params['type'].'.html';
        }
        $data['value'] = $params['value'] ? $params['value'] : 'member';
        $data['options'] = K::M('member/member')->from_list();
        return $data;        
    }

    public function group(&$params)
    {
        if(!$params['tpl']){
            if(!in_array($params['type'], array('label', 'checkbox', 'radio', 'option'))){
                $params['type'] = 'option';
            }
            $params['tpl'] = 'widget:default/'.$params['type'].'.html';
        }
        $data['from'] = $params['from'] ? $params['from'] : 'member';
        $data['value'] = $params['value'] ? $params['value'] : '';
        $data['options'] = K::M('member/group')->options($data['from']);
        return $data;
    }

    public function group_by_privs(&$params)
    {
        if(!$params['tpl']){
            if(!in_array($params['type'], array('label', 'checkbox', 'radio', 'option'))){
                $params['type'] = 'option';
            }
            $params['tpl'] = 'widget:default/'.$params['type'].'.html';
        }
        $data['name'] = $params['name'];
        $data['value'] = $params['value'] ? explode(',', $params['value']) : '';
        $data['options'] = K::M('member/group')->items_by_privs($params['privs']);
        return $data;
    }

}