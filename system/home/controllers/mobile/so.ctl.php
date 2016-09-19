<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: city.ctl.php 3053 2014-01-15 02:00:13Z youyi $
 */

class Ctl_Mobile_So extends Ctl_Mobile
{
    
    public function index($city_id=null)
    {
		$pager['backurl'] = $this->mklink('mobile');
		$this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/so/so.html';
    }
}
?>