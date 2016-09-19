<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Msite_Banner extends Mdl_Table
{   
  
    protected $_table = 'weixin_msite_banner';
    protected $_pk = 'photo_id';
    protected $_cols = 'photo_id,wx_id,title,photo,link,orderby';
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

    public function upload($wx_id, $attach)
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
        $data['wx_id'] = (int)$wx_id;
        if(!$data['title'] = $attach['title']){
            $data['title'] = preg_replace("/\.(jpg|jpeg|png|gif|bmp)$/i", '', $attach['name']);
        }
        $data['title'] = K::M('content/html')->encode($data['title']);
        $data['photo'] = $B.$fname;        
        $data['photo'] = K::M('content/html')->encode($data['photo']);
        $data['link'] = '###';
        $data['orderby'] = 0;
        if($photo_id =$this->db->insert($this->_table, $data, true)){
            $data['photo_id'] = $photo_id;
            return $data;
        }
        return false;          
    }  

	public function items_by_weixin($wx_id)
	{
		if(!$wx_id = (int)$wx_id){
			return false;
		}
		return $this->items(array('wx_id'=>$wx_id), null, 1, 10);
	}
}