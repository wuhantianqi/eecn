<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Menu extends Mdl_Table
{   
  
    protected $_table = 'weixin_menu';
    protected $_pk = 'menu_id';
    protected $_cols = 'menu_id,title,parent_id,wx_id,wx_sid,type,reply_id,content,link,orderby';
    protected $_orderby = array('parent_id'=>'ASC', 'orderby'=>'ASC');

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function items_by_weixin($wx_id)
    {
        $menu_list = array();
        if($items = $this->items(array('wx_id'=>$wx_id), array('parent_id'=>'ASC', 'orderby'=>'ASC'), 1, 30)){
            foreach($items as $k=>$v){
                if($v['parent_id'] == 0){
                    $menu_list[$k] = $v;
                    foreach($items as $kk=>$vv){
                        if($vv['parent_id'] == $k){
                            $menu_list[$kk] = $vv;
                        }
                    }
                }
            }
        }
        return $menu_list;
    }

    public function wechat_buttons($wx_id)
    {
        $buttons = array();
        if($items = $this->items_by_weixin($wx_id)){
            foreach($items as $k=>$v){
                if(empty($v['parent_id'])){
                    $sub_buttons = array();
                    foreach($items as $kk=>$vv){
                        if($vv['parent_id'] == $k){
                            $sub_buttons[] = $this->_format_menu_row($vv);
                        }
                    }
                    if($sub_buttons){
                        $buttons[] = array('name'=>urlencode($v['title']), 'sub_button'=>$sub_buttons);
                    }else{
                        $buttons[] = $this->_format_menu_row($v);
                    }
                }
            }
        }
        return $buttons;
    }

    protected function _format_menu_row($row)
    {        
        if($row['type'] == 'link'){
            $a = array('type'=>'view', 'name'=>urlencode($row['title']), 'url'=>$row['link']);
        }else{
            $a = array('type'=>'click', 'name'=>urlencode($row['title']), 'key'=>'MENU:'.$row['menu_id']);
        }
        return $a;
    }
}