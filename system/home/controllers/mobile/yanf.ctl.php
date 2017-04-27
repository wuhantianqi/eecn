<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Mobile_Yanf extends Ctl_Mobile
{

    public function index()
    {
		$this->seo->init('mobile');
        $this->tmpl = 'mobile/newpage/yanf.html';    
    }

}