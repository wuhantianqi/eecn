<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Product_Spec extends Mdl_Table
{   
  
    protected $_table = 'product_spec';
    protected $_pk = 'spec_id';
    protected $_cols = 'spec_id,product_id,price,spec_name,spec_photo,sale_sku,sale_count';

    
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

    public function upload($spec_id, $attach)
    {
        if(!$spec_id = (int)$spec_id){
            return false;
        }
        if(!UPLOAD_ERR_OK == $attach['error']){
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
        $photo = K::M('content/html')->encode($B.$fname);
        return $this->update($spec_id, array('spec_photo'=>$photo));
    }

}