<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: upload.mdl.php 6072 2014-08-12 12:23:29Z youyi $
 */

/**
 * 上传类只支持图片格式
 *
 * 601:上传失败
 * 602:不支持的文件扩展名
 * 603:不支持的文件类型
 * 604:上传的文件太大
 * 605:
 */

class Mdl_Helper_Upload
{
    public $message = '';
    public $code = '200';
    public $succeed = true;

    private $_allow_exts = array('gif','jpg', 'png','jpeg','bmp');
    private $_allow_zip_exts = array('zip', 'gz', 'tar', 'rar');
    private $_allow_file_exts = array('doc','docx','txt','pdf', 'rtf', 'xls', 'xlsx', 'ppt', 'pptx','zip', 'gz', 'tar', 'rar','gif','jpg', 'png','jpeg','bmp');
    private $_allow_type = array('image/gif', 'image/jpeg','image/pjpeg', 'image/png', 'image/x-png', 'image/bmp','application/octet-stream');
    private $_check_allow_type = true;
    private $_allow_max_size = 2097152;

    public function __construct($system)
    {
        $cfg = $system->config->get('attach');
        if(is_numeric($cfg['allow_size'])){
            $this->_allow_max_size = $cfg['allow_size'] * 1024;
        }
        if($cfg['allow_exts']){
            if($_allow_exts = explode(',', $cfg['allow_exts'])){
                $this->_allow_exts = $_allow_exts;
            }
        }
        if($cfg['allow_exts_zip']){
            if($_allow_zip_exts = explode(',', $cfg['allow_exts'])){
                $this->_allow_zip_exts = $_allow_zip_exts;
            }
        }
        if($cfg['allow_exts_file']){
            if($_allow_file_exts = explode(',', $cfg['allow_exts'])){
                $this->_allow_file_exts = $_allow_file_exts;
            }
        }        
    }

    function upload(&$attach, $dir, &$fname="")
    {
        if(!$this->_check($attach)){
            return false;
        }
        K::M('io/dir')->create($dir, 0777, true);
        if(empty($fname)){
            $fname = date('Ymd_').strtoupper(md5(microtime().$attach['tmp_name'].PRI_KEY.rand())).".{$attach['extension']}";
        }
        $file = $dir.$fname;
        if(move_uploaded_file($attach['tmp_name'],$file)){
            return $this->check_safe($file);
        }else if(K::M('io/file')->move($attach['tmp_name'],$file)){
            return $this->check_safe($file);
        }else{
            K::M('helper/error')->add("上传失败",605);
            return false;
        }
    }

    public function zip($attach, $dir, &$fname='')
    {
        $_allow_exts = $this->_allow_exts;
        $_check_allow_type = $this->_check_allow_type;
        $this->set_allow_exts($this->_allow_zip_exts);
        $this->_check_allow_type = false;
        if(!$this->_check($attach)){
            return false;
        }
        K::M('io/dir')->create($dir, 0777, true);
        if(empty($fname)){
            $fname = date('Ymd_').strtoupper(md5(microtime().$attach['tmp_name'].PRI_KEY.rand())).".{$attach['extension']}";
        }
        $file = $dir.$fname;
        if(move_uploaded_file($attach['tmp_name'],$file)){
            $ret = $file;
        }else if(K::M('io/file')->move($attach['tmp_name'],$file)){
            $ret = $file;
        }else{
            K::M('helper/error')->add("上传文件失败",605);
            $ret = false;
        }
        $this->_allow_exts = $_allow_exts;
        $this->_check_allow_type = $_check_allow_type;
        return $ret;
    }

    public function file($attach, $dir, &$fname='')
    {
        $_allow_exts = $this->_allow_exts;
        $_check_allow_type = $this->_check_allow_type;
        $this->set_allow_exts($this->_allow_file_exts);
        $this->_check_allow_type = false;
        if(!$this->_check($attach)){
            return false;
        }
        K::M('io/dir')->create($dir, 0777, true);
        if(empty($fname)){
            $fname = date('Ymd_').strtoupper(md5(microtime().$attach['tmp_name'].PRI_KEY.rand())).".{$attach['extension']}";
        }
        $file = $dir.$fname;
        if(move_uploaded_file($attach['tmp_name'],$file)){
            $ret = $file;
        }else if(K::M('io/file')->move($attach['tmp_name'],$file)){
            $ret = $file;
        }else{
            K::M('helper/error')->add("上传文件失败",605);
            $ret = false;
        }
        $this->_allow_exts = $_allow_exts;
        $this->_check_allow_type = $_check_allow_type;
        return $ret;
    }

    public function set_max_size($size)
    {
        if(!is_numeric($size) || $size>2097152 || $size< 1){
            return false;
        }
        $this->_allow_max_size = $size;
    }
    
    public function set_allow_exts($ext)
    {
        $this->_allow_exts = $ext;
    }

    public function check_safe($file)
    {
        if($data = @file_get_contents($file)){
            if(preg_match("/\<(\?php|\<\? )/is", $data)){
                K::M('helper/error')->add('不是安全的图片', 999);
                K::M('io/file')->remove($file);
                return false;
            }
            //$data = preg_replace("/(\<\?|\<\%)/i", '00', $data);
            //@file_put_contents($file, $data);
        }
        return $file;
    }

    private function _check(&$attach)
    {
        if($attach['error'] != UPLOAD_ERR_OK/* || $attach['size']<1 || !file_exists($attach['tmp_name'])*/){
            K::M('helper/error')->add("上传失败".$attach['error'],601);
            return false;
        }
        $attach['extension'] = strtolower(K::M('io/file')->extension($attach['name']));
        $attach['type'] = strtolower($attach['type']);
        if(!in_array($attach['extension'], $this->_allow_exts)){
            K::M('helper/error')->add("不支持的文件扩展名",602);
        }else if($this->_check_allow_type && !in_array($attach['type'],$this->_allow_type)){
            K::M('helper/error')->add("不支持的文件类型",603);
        }else if($attach['size']>$this->_allow_max_size){
            K::M('helper/error')->add("上传的文件太大",604);
        }else{
            return true;
        }
        return false;
    }
}