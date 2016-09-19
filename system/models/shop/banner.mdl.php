<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: banner.mdl.php 2430 2013-12-21 12:11:29Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Banner extends Mdl_Table
{   
  
    protected $_table = 'shop_banner';
    protected $_pk = 'banner_id';
    protected $_cols = 'banner_id,shop_id,photo,title,link,orderby,dateline';
    protected $_orderby = array('orderby'=>'DESC', 'banner_id'=>'DESC');
    
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

    public function items_by_shop($shop_id)
    {
        return $this->items(array('shop_id'=>$shop_id), null, 1, 50);
    }

    public function count_by_shop($shop_id)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." WHERE shop_id='$shop_id'";
        return $this->db->GetOne($sql);
    }

    public function upload($shop_id, $attach)
    {
        if(!UPLOAD_ERR_OK == $attach['error']){
            $this->err->add('上传文件失败',201);
            return false;
        }
        $cfg = K::$system->config->get('attach');
        $B = 'product/'.date('Ym/',__CFG::TIME);
        $D = $cfg['attachdir'].$B;
        if(!$F = K::M('helper/upload')->upload($attach, $D, $fname)){
            return false;
        }
        $data = array();
        $data['shop_id'] = (int)$shop_id;
        if(!$data['title'] = $attach['title']){
            $data['title'] = preg_replace("/\.(jpg|jpeg|png|gif|bmp)$/i", '', $attach['name']);
        }
        $data['title'] = K::M('content/html')->encode($data['title']);
        $data['photo'] = $B.$fname;
        $data['photo'] = K::M('content/html')->encode($data['photo']);
        $data['dateline'] = __CFG::TIME;
        if($banner_id =$this->db->insert($this->_table, $data, true)){
            $data['banner_id'] = $banner_id;
            return $data;
        }
        return false;          
    }    
}