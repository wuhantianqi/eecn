<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Mobile_Ceform extends Ctl_Mobile
{
  public function index()
    {
		$this->seo->init('mobile_form');
        $this->tmpl = 'mobile/newpage/ceform.html';
    }

}