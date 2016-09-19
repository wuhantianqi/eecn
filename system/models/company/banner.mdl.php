<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: banner.mdl.php 5818 2014-07-07 02:54:25Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_Banner extends Mdl_Table
{   
  
    protected $_table = 'company_banner';
    protected $_pk = 'banner_id';
    protected $_cols = 'banner_id,company_id,title,photo,link,orderby';
    protected $_orderby = array('orderby'=>'ASC', 'banner_id'=>'DESC');
    
    public function get_count_by_company_id($company_id)
    {
        $company_id = (int)$company_id;
        $where = " company_id = {$company_id} ";
        return $this->count($where);
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

    public function count_by_company($company_id)
    {
        if(!$company_id = (int)$company_id){
            return false;
        }
        $sql = "SELECT COUNT(1) FROM ".$this->table($this->_table)." WHERE company_id='$company_id'";
        return $this->db->GetOne($sql);
    }

    public function items_by_company($company_id)
    {
        if(!$company_id = (int)$company_id){
            return false;
        }
        return $this->items(array('company_id'=>$company_id), null, 1, 50);
    }

    public function upload($company_id, $attach)
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
        $data['company_id'] = (int)$company_id;
        if(!$data['title'] = $attach['title']){
            $data['title'] = preg_replace("/\.(jpg|jpeg|png|gif|bmp)$/i", '', $attach['name']);
        }
        $data['title'] = K::M('content/html')->encode($data['title']);
        $data['photo'] = $B.$fname;
        $data['photo'] = K::M('content/html')->encode($data['photo']);
        $data['orderby'] = 50;
        if($banner_id =$this->db->insert($this->_table, $data, true)){
            $data['banner_id'] = $banner_id;
            return $data;
        }
        return false;          
    } 

}