<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: msgbox.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

Import::M('helper/error');
class Mdl_Magic_Msgbox extends Mdl_Helper_Error
{
	
	public function response()
	{
		$request = K::$system->request;
		$objctl = &K::$system->objctl;
		if(!$tmpl = $objctl->tmpl){
			$tmpl = $objctl->pagedata['_OO_'];
		}
		if($request['MINI'] == 'load'){
			if($tmpl){
				$objctl->output();
			}else{
				$this->show($request['referer'], 'HTML');
			}
		}else if($request['XREQ']){
			if($tmpl){
				$this->_data['html'] = $objctl->output(false);
			}
			$this->show('', 'JSON');
		}else if($tmpl){
			$objctl->output();
		}else{
			$this->show($request['referer'], 'HTML');
		}
	}

}