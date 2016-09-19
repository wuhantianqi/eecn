<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: tools.ctl.php 3304 2014-02-14 11:01:43Z youyi $
 */
class Ctl_Tools extends Ctl 
{
    public $_call = 'index';
    public function index($tool)
	{
        $this->items($tool);
    }
    
    public function items($tool = null)
    {   
        $tool = htmlspecialchars($tool);
        $tools_array =array(
            'items' => '涂料预算器',
            'qiangzhuan' => '墙砖预算器',
            'dizhuan' => '地砖计算器',
            'chuanlian' => '窗帘计算器',
            'bizhi' => '壁纸计算器',
            'diban' => '地板计算器',
            'anjie' => '按揭贷款计算器',
            'denge' => '等额本金还贷计算器'
        );    
        $this->pagedata['tools_array'] = $tools_array;
        if(empty($tool) || !isset($tools_array[$tool])){
            $tool = 'items';
        }
        $this->pagedata['tool'] = $tool; 
        $this->tmpl = 'tools/'.$tool.'.html';
        $this->seo->init('tools', array('title'=>$tools_array[$tool]));
    }

}
