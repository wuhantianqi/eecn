<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: base.mdl.php 2034 2013-12-07 03:08:33Z $
 */

class Mdl_Developer_Base extends Model
{   

	public function loadtmpl($tmpl)
    {
        static $tmpls = array();
        if(!isset($tmpls[$tmpl])){
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$tmpl.'.tmpl';
            if(file_exists($file)){
                $tmpls[$tmpl] = file_get_contents($file);
            }else{
                return false;
            }
        }
        return $tmpls[$tmpl];
    }

    protected function __tmpl($tmpl, $data, $load=true)
    {
        if($load){
            return str_replace(array_keys($data), array_values($data), $this->loadtmpl($tmpl));
        }else{
            return str_replace(array_keys($data), array_values($data), $tmpl); 
        }
    }

    protected function __save($file, $content)
    {
        $dir = dirname($file).DIRECTORY_SEPARATOR;
        if(file_exists($file)){
            K::M('io/file')->move($file, $dir.__CFG::TIME.'.'.basename($file));
        }else if(!file_exists($dir)){
            K::M('io/dir')->create($dir);
        }
        file_put_contents($file, $content);
    }

    public function check_tmpl($file, $force=false)
    {
        if(file_exists($file)){
            if($force){
                //强制生成，当存在备份文件
                K::M('io/file')->move($file, dirname($file).DIRECTORY_SEPARATOR.__CFG::TIME.basename($file));
                return true;
            }
            return false;
        }
        return true;
    }    
}