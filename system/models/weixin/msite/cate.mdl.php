<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Msite_Cate extends Mdl_Table
{   
  
    protected $_table = 'weixin_msite_cate';
    protected $_pk = 'cat_id';
    protected $_cols = 'cat_id,wx_id,title,link,icon,orderby';
    protected $_orderby = array('orderby'=>'ASC');
    
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
        if(!$wx_id = (int)$wx_id){
            return false;
        }
        return $this->items(array('wx_id'=>$wx_id), null, 1, 100);
    }

    public function options_by_weixin($wx_id)
    {
        $options = array();
        if($items = $this->items_by_weixin($wx_id)){
            foreach($items as $k=>$v){
                $options[$v['cat_id']] = $v['title'];
            }
        }
        return $options;
    }

    protected function _format_row($row)
    {
        if(empty($row['link'])){
            $row['url'] = '/index.php?weixin/msite-cate-'.$row['cat_id'].'.html';
        }else{
            $row['url'] = $row['link'];
        }
        return $row;
    }
}