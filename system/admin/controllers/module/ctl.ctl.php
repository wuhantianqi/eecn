<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: ctl.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Module_Ctl extends Ctl
{
	
	public function index()
	{
		$this->pagedata['menu_tree'] = K::M('module/view')->tree();
		$this->tmpl = 'admin:module/ctl/index.html';
	}

	public function batch()
	{
		$this->pagedata['menu_tree'] = K::M('module/view')->tree();
		$this->tmpl  = 'admin:module/ctl/detail.html';		
	}

	public function detail($pid)
	{
		if(!$PID = intval($pid)){
			$this->err->add('参数未传递',201);
		}else if(!$menu = K::M('module/view')->module($PID)){
			$this->err->add('您要管理的菜单模型不存在',202);
		}else if($menu['module'] != 'menu'){
			$this->err->add('非Menu类型菜单不含有控制模块',203);
		}else{
			$pager['PID'] = $PID;
			$this->pagedata['modules'] = K::M('module/view')->modules($PID);
			$this->pagedata['menu_tree'] = K::M('module/view')->tree();
			$this->pagedata['pager'] = $pager;
			$this->tmpl  = 'admin:module/ctl/detail.html';
		}
	}

	public function save()
	{
		if($this->GP('batch') == 'module'){ //批量修改/添加
			$mdl = K::M('module/handler');
			$err = false;
			$isempty = true;
			if($mods = $this->GP('module')){
				foreach($mods as $k=>$v){
					$v['visible'] = $v['visible'] ? 1 : 0;
					if(!$mdl->update($k, $v)){
						$err = true;
					}
					$isempty = false;
				}
			}
			if($data = $this->GP('data')){
				foreach($data as $k=>$v){
					if($v['title'] && $v['ctl'] && $v['act'] && $v['parent_id']){
						if(!$mdl->create($v)){
							$err = true;
						}
						$isempty = false;
					}
				}
			}
			if($isempty){
				$this->err->add('请先添加数据后再保存',201);
			}else if(!$err){
				$this->err->add('更新控制模块成功');
				$this->logs('更新控制模块成功',$data);
			}
		}else if($ID = $this->GP('ID')){ //修改
			if(!$mod = K::M('module/view')->module($ID)){
				$this->err->add('要修改的控制模块不存在', 202);
			}else if(K::M('module/handler')->update($ID,$data)){
				$this->err->add('修改控制模块成功');
				$this->logs('修改控制模块成功',$data);
			}
		}else if($ID = K::M('module/handler')->create($data)){
			$this->err->add('添加控制模块成功');
			$this->logs('添加控制模块成功',$data);
		}
	}

	public function remove($ids)
	{
		if(K::M('verify/check')->ids($ids)){
			if(K::M('module/handler')->remove($ids)){
				$this->err->add('删除控制模型成功');
				$this->logs('添加控制模块成功');
			}
		}else {
			$this->err->add('非法请求!',201);
		}	
	}
}