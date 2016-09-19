<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: photo.mdl.php 2466 2013-12-23 11:13:30Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Product_Photo extends Mdl_Table
{   
  
    protected $_table = 'product_photo';
    protected $_pk = 'photo_id';
    protected $_cols = 'photo_id,product_id,photo,title,size,orderby,dateline';
    protected $_orderby = array('orderby'=>'ASC', 'photo_id'=>'DESC');
    
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

    public function items_by_product($product_id)
    {
        if(!$product_id = (int)$product_id){
            return false;
        }
        return $this->items(array('product_id'=>$product_id), null, 1, 50);
    }

    public function count_by_product($product_id)
    {
        if(!$product_id = (int)$product_id){
            return false;
        }
        $sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." WHERE product_id='$product_id'";
        return $this->db->GetOne($sql);
    }

    public function upload($product_id, $attach)
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
        $oImg = K::M('image/gd');
        $thumbs = $size = array();
        $size['photo'] = $cfg['product']['photo'] ? $cfg['product']['photo'] : '720';
        $size['thumb'] = $cfg['product']['thumb'] ? $cfg['product']['thumb'] : '200';
        $size['small'] = $cfg['product']['small'] ? $cfg['product']['small'] : '60X60';
        $thumbs = array($size['photo']=>"{$D}/{$fname}",$size['thumb']=>"{$D}/{$fname}_thumb.jpg",$size['small']=>"{$D}/{$fname}_small.jpg");
        $oImg->thumbs($F, $thumbs);
        if($cfg['product']['watermark']){
            $uname = $attach['uname'] ? $attach['uname'] : 'IJH';
            $oImg->watermark("{$D}/{$fname}", $attach['uname']);
        }
        $data = array();
        $data['product_id'] = (int)$product_id;
        if(!$data['title'] = $attach['title']){
            $data['title'] = preg_replace("/\.(jpg|jpeg|png|gif|bmp)$/i", '', $attach['name']);
        }
        $data['title'] = K::M('content/html')->encode($data['title']);
        $data['photo'] = $B.$fname;
        $data['photo'] = K::M('content/html')->encode($data['photo']);
        $data['size'] = (int)$attach['size'];
        $data['dateline'] = __CFG::TIME;
        if($photo_id =$this->db->insert($this->_table, $data, true)){
            $data['photo_id'] = $photo_id;
            if($product_id){
                K::M('product/product')->update_count($product_id, 'photos', 1);
            }
            return $data;
        }
        return false;          
    }

    public function update_product_id($ids, $product_id)
    {
        if(!$ids = K::M('verify/check')->ids($ids)){
            return false;
        }else{
            $sql = "SELECT COUNT(1) count, SUM(size) size FROM ". $this->table($this->_table)." WHERE photo_id IN($ids) AND product_id=0";
            if($row = $this->db->GetRow($sql)){
                K::M('product/product')->update($product_id, array('count'=>"`count`+{$row['count']}", 'size'=>"`size`+{$row['size']}"), true);
            }
            return $this->db->update($this->_table, array('product_id'=>$product_id), "photo_id IN($ids) AND product_id=0");
        }
    }
  
}