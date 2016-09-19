<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tools.ctl.php 3304 2014-02-14 11:01:43Z youyi $
 */
class Ctl_Newactivity extends Ctl 
{
	// 三分钟装修
    public function index()
    {
		$this->tmpl = 'newactivity/index.html';
    }
    // 管家页面
    public function housekeeper()
    {
		$this->tmpl = 'newactivity/housekeeper.html';
    }
    // 管家页面
    public function loan()
    {
        $this->tmpl = 'newactivity/loan.html';
    }
}
