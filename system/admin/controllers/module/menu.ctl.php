<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: menu.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Module_Menu extends Ctl
{
	
	public function index()
	{
		$this->pagedata['tree'] = K::M('module/view')->tree();
		$this->tmpl = 'admin:module/menu/index.html';	
	}

	public function create($pid=null)
	{
		if($PID = intval($pid)){
			$pager['PID'] = $PID;
			$this->pagedata['pager'] = $pager;
		}
		$this->pagedata['tree'] = K::M('module/view')->tree();
		$this->tmpl = 'admin:module/menu/create.html';
	}
	
	public function edit($pid)
	{

		if(!$ID = intval($pid)){
			$this->err->add('未指定要修改的菜单',201);
		}else if(!$menu= K::M('module/view')->module($ID)){
			$this->err->add('要修改的菜单不存在',202);
		}else if($menu['module'] == 'module'){
			$this->err->add('要修改的菜单不存在',203);
		}else{
			$this->pagedata['tree'] = K::M('module/view')->tree();
			$this->pagedata['menu'] = $menu;
			$this->tmpl = 'admin:module/menu/create.html';
		}
	}

	public function save()
	{
		if(!$data = $this->GP('data')){
			$this->err->add('非法的数据提交', 201);
		}else if($ID = $this->GP('ID')){
			if(!$menu = K::M('module/view')->module($ID)){
				$this->err->add('要修改的菜单不存在', 202);
			}else if($menu['module'] == 'module'){
				$this->err->add('修改的不是导航菜单', 203);
			}else if(K::M('module/menu')->update($ID, $data)){
				$this->err->add('修改导航菜单成功');
			}
		}else if($ID = K::M('module/menu')->create($data)){
			$this->err->add('添加导航菜单成功');
		}
	}

	public function update()
	{
		if('orderby' == $this->GP('batch')){
			if($orderbys = $this->GP('orderby')){
				$oMenu = K::M('module/menu');
				foreach($orderbys as $k=>$v){
					if(($k = intval($k)) && ($v = intval($v))){
						$oMenu->update($k, array('orderby'=>$v));
					}
				}
				$this->err->add('修改菜单排序成功');
			}
		}
	}

	public function remove($mid)
	{
		if(($ID = intval($mid)) || ($ID = $this->GP('IDS'))){
			if(K::M('module/menu')->remove($ID)){
				$this->err->add('删除导航菜单成功');
			}
		}else {
			$this->err->add('非法请求!',201);
		}		
	}
}