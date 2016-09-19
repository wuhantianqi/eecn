<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: pics.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Home_Photo extends Mdl_Table
{   
  
    protected $_table = 'home_photo';
    protected $_pk = 'photo_id';
    protected $_cols = 'photo_id,home_id,type,title,photo,size,orderby,dateline';

    protected $_type = array(1=>'户型图',2=>'实景图' ,3=>'样板间');
    
    public function get_type(){
        
        return $this->_type;
    }
    
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
    
    public function upload($home_id,$type, $attach)
    {
        if(!UPLOAD_ERR_OK == $attach['error']){
            $this->err->add('上传文件失败',201);
            return false;
        }
        $cfg = K::$system->config->get('attach');
        $B = 'homephoto/'.date('Ym/',__CFG::TIME);
        $D = $cfg['attachdir'].$B;
		
        if(!$F = K::M('helper/upload')->upload($attach, $D, $fname)){
            return false;
        }

        $oImg = K::M('image/gd');
        $thumbs = $size = array();
        $size['photo'] = $cfg['homepics']['photo'] ? $cfg['homepics']['photo'] : '720';
        $size['thumb'] = $cfg['homepics']['thumb'] ? $cfg['homepics']['thumb'] : '200';
        $thumbs = array($size['photo']=>"{$D}/{$fname}",$size['thumb']=>"{$D}/{$fname}_thumb.jpg");
        $oImg->thumbs($F,$thumbs);
        if($cfg['homepics']['watermark']){
            $uname = $attach['uname'] ? $attach['uname'] : 'IJH';
            $oImg->watermark("{$D}/{$fname}", $attach['uname']);
        }
        $data = array();
        $data['home_id'] = (int)$home_id;
        if(!$data['title'] = $attach['title']){
            $data['title'] = preg_replace("/\.(jpg|jpeg|png|gif|bmp)$/i", '', $attach['name']);
        }
		
        $data['title'] = K::M('content/html')->encode($data['title']);
        $data['type']   = $type;
        $data['photo'] = $B.$fname;
        $data['photo'] = K::M('content/html')->encode($data['photo']);
		$data['size'] = $attach['size'];
		$data['dateline'] = __TIME;
        if($id =$this->db->insert($this->_table, $data, true)){
            return $data;
        }
        return false;       
    }   
    
}