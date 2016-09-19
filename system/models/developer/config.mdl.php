<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: config.mdl.php 2034 2013-12-07 03:08:33Z $
 */

Import::M('developer/base');
class Mdl_Developer_Config extends Mdl_Developer_Base
{   
    
    public function create_cfg($cfg)
    {
    	if(empty($cfg['k'])){
    		$this->err->add('没有填写配置标识', 201);
    	}else if(K::$system->config->add($cfg['k'], $cfg['title'])){
    		if($cfg['module_id']){
    			//ctl,act,title,visible,parent_id,orderby
    			$ctl = array('ctl'=>'system/config', 'act'=>$cfg['k'], 'visible'=>1);
    			$ctl['parent_id'] = $cfg['module_id'];
    			$ctl['title'] = $cfg['title'];
    			K::M('module/handler')->create($ctl);
    		}
    		return true;
    	}
    	return false;
    }

    public function create_tmpl($cfg, $schema)
    {
    	$data = array('#K#' => $cfg['k'], '#javascript#'=>'');
		foreach((array)$schema as $k=>$v){
			$data['#label#'] = $v['label'];
			$data['data[#field#]'] = 'config['.$v['field'].']';
			$data['$detail.#field#'] = '$config.'.$v['field'];
            $date['#field#'] = $v['field'];

			$data['#default#'] = $v['default'];
			$data['#comment#'] = $v['comment'] ? '<span class="tip-comment">'.$v['comment'].'</span>' : '';
			$type = strtolower($v['type']);
		    switch($type){
		        case 'text': case 'checkbox': case 'radio': case 'select': case 'textarea':
		            $form .= $this->__tmpl('form/'.$type, $data); break;
		        case 'boolean': case 'date':  case 'number': case 'city': case 'area':
		            $form .= $this->__tmpl('form/'.$type, $data); break;
		         case 'photo': case 'attach': case 'file':
		            $upload = true;
		            $form .= $this->__tmpl('form/file', $data); break;
		        case 'editor':
		            $kindeditor = true;
		            $form .= $this->__tmpl('form/editor', $data); break;
		        default :
		            $form .= $this->__tmpl('form/text', $data); break;
			}
		}
		if($upload){
			$formentype = 'ENCTYPE="multipart/form-data"';
		}
		$data['#formentype#'] = $formentype;
		$form = $this->__tmpl('cfg/header', $data) . $form .$this->__tmpl('cfg/footer', $data);
		if($kindeditor){
			$form .= $this->loadtmpl('script/kindeditor');
		}
		$data['#topbutton#'] = '';
		$header = $this->__tmpl('view/header', $data);
        $footer = $this->__tmpl('view/footer', $data);
		$content = $header. $form . $footer;
        $file = __APP_DIR.'view'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR."{$cfg['k']}.html";
		$this->__save($file, $content);  	
    }

    public function create_schema($cfg, $schema, $create=true)
    {
        $data = array();
        foreach((array)$schema as $k=>$v){
            if(empty($v['label']) || empty($v['field']) || empty($v['type'])){
                continue;
            }
            $a = array('label'=>$v['label'], 'field'=>$v['field'], 'type'=>$v['type']);
            $a['default'] = $v['default'] ? $v['default'] : '';
            $a['comment'] = $v['comment'] ? $v['comment'] : '';
            $a['html'] = $v['html'] ? true : false;
            $a['empty'] = $v['tmepy'] ? true : false;
            $data[$v['field']] = $a;
        }
        if($data && $create){
            $file = __CFG::DIR.'schemas'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$cfg['k'].'.php';
            $content = $this->__tmpl('mdl/schema', array('#schema#'=>var_export($data, true)));
            $this->__save($file, $content);
            return $data;           
        }
        return $data;
    }   
}