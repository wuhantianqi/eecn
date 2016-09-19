<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: upload.mdl.php 2034 2013-12-07 03:08:33Z $
 */
if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
class Mdl_Magic_Upload extends Mdl_Table
{   
    
	protected $_table = 'upload_photo';
	protected $_pk = 'photo_id';
	protected $_cols = 'photo_id,from,hash,photo,size,name,dateline';
    protected $_orderby = array('photo_id'=>'ASC');

	public function upload($attach, $from='editor', $source=null)
	{
        $ym = date('Ym', __CFG::TIME);
        $cfg = K::$system->config->get('attach');
        $dir = $cfg['attachdir'].'photo'.DIRECTORY_SEPARATOR.$ym.DIRECTORY_SEPARATOR;
        if($source && is_string($source) && strlen($source) > 32){
            if(!preg_match('/^(http|temp|cache)/', $source)){
                $hash = md5($source);
                if($item = $this->item_by_hash($hash)){
                    $fname = basename($item['photo']);
                }
            }
        }
        if($file = K::M('helper/upload')->upload($attach, $dir, $fname)){
        	$photo = "photo/{$ym}/{$fname}";
            $hash = md5($photo);
            $a = array('size'=>$attach['size'], 'photo'=>$photo,'name'=>$attach['name'], 'hash'=>$hash);
            if($item){
                $a = array_merge($item, $a);
                $this->db->update($this->_table, $a, "photo_id='{$item[photo_id]}'");
            }else{
                $a['from'] = $from;
                $a['dateline'] = __CFG::TIME;
                $a['photo_id'] = $this->db->insert($this->_table, $a, true);
            }
        	$a['file'] = $file;
        	return $a;
        }
        return false;
	}

    public function zip($attach, $source=null)
    {
        $ym = date('Ym', __CFG::TIME);
        $cfg = K::$system->config->get('attach');
        $dir = $cfg['attachdir'].'file'.DIRECTORY_SEPARATOR.$ym.DIRECTORY_SEPARATOR;
        if($source && is_string($source) && strlen($source) > 32){
            if(!preg_match('/^(http|temp|cache)/', $source)){
                $hash = md5($source);
                if($item = $this->item_by_hash($hash)){
                    $fname = basename($item['photo']);
                }
            }
        }
        if($file = K::M('helper/upload')->zip($attach, $dir, $fname)){
            $photo = "file/{$ym}/{$fname}";
            $hash = md5($photo);
            $a = array('size'=>$attach['size'], 'photo'=>$photo,'name'=>$attach['name'], 'hash'=>$hash);
            if($item){
                $a = array_merge($item, $a);
                $this->db->update($this->_table, $a, "photo_id='{$item[photo_id]}'");
            }else{
                $a['from'] = 'zip';
                $a['attach'] = $photo;
                $a['dateline'] = __CFG::TIME;
                $a['photo_id'] = $this->db->insert($this->_table, $a, true);
            }
            $a['file'] = $file;
            return $a;
        }
        return false;
    }

    public function file($attach, $source=null)
    {
        $ym = date('Ym', __CFG::TIME);
        $cfg = K::$system->config->get('attach');
        $dir = $cfg['attachdir'].'file'.DIRECTORY_SEPARATOR.$ym.DIRECTORY_SEPARATOR;
        if($source && is_string($source) && strlen($source) > 32){
            if(!preg_match('/^(http|temp|cache)/', $source)){
                $hash = md5($source);
                if($item = $this->item_by_hash($hash)){
                    $fname = basename($item['photo']);
                }
            }
        }
        if($file = K::M('helper/upload')->file($attach, $dir, $fname)){
            $photo = "file/{$ym}/{$fname}";
            $hash = md5($photo);
            $a = array('size'=>$attach['size'], 'photo'=>$photo,'name'=>$attach['name'], 'hash'=>$hash);
            if($item){
                $a = array_merge($item, $a);
                $this->db->update($this->_table, $a, "photo_id='{$item[photo_id]}'");
            }else{
                $a['from'] = 'file';
                $a['attach'] = $photo;
                $a['dateline'] = __CFG::TIME;
                $a['photo_id'] = $this->db->insert($this->_table, $a, true);
            }
            $a['file'] = $file;
            return $a;
        }
        return false;               
    }


    public function xheditor($attach)
    {
        $ym = date('Ym', __CFG::TIME);
        $cfg = K::$system->config->get('attach');
        $dir = $cfg['attachdir'].'photo'.DIRECTORY_SEPARATOR.$ym.DIRECTORY_SEPARATOR;
        if($attach['html5']){
            if(strlen($attach['data'])>2097152){
                $this->err->add('上传的文件不能超过2M', 721);
                return false;
            }
            $ext = $attach['extension'] = strtolower(K::M('io/file')->extension($attach['name']));
            $fname = date('Ymd_').strtoupper(md5(microtime().$attach['tmp_name'].PRI_KEY.rand())).".{$attach['extension']}";
            $file = $dir.$fname;
            file_put_contents($file, $attach['data']);
        }else if(!$file = K::M('helper/upload')->upload($attach, $dir, $fname)){
            return false;
        }
        if($file){
            $photo = "photo/{$ym}/{$fname}";
            $hash = md5($photo);
            $a = array('size'=>$attach['size'], 'photo'=>$photo,'name'=>$attach['name'], 'hash'=>$hash);
            $a['from'] = $from;
            $a['dateline'] = __CFG::TIME;
            $a['photo_id'] = $this->db->insert($this->_table, $a, true);
            $a['file'] = $file;
            $size['photo'] = $cfg['editor']['photo'] ? $cfg['editor']['photo'] : '720';
            $size['thumb'] = $cfg['editor']['thumb'] ? $cfg['editor']['thumb'] : '200';
            $thumbs = array($size['photo']=>$file, $size['thumb']=>$file.'_thumb.jpg');
            K::M('image/gd')->thumbs($file, $thumbs, false);

            if($cfg['editor']['watermark']){
                $site = K::$system->config->get('site');
                $uname = $attach['uname'] ? $attach['uname'] : $site['title'];
                K::M('image/gd')->watermark($file, $uname);
            }
            return $a;
        }
        return false;
    }

	public function item_by_hash($hash)
	{
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE ".$this->field('hash', $hash);
        return $this->db->GetRow($sql);
	}
}