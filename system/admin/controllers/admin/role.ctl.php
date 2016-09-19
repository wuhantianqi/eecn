<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: role.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Admin_Role extends Ctl
{
	
	public function index()
	{
		
		$this->pagedata['role_list'] = K::M('admin/role')->fetch_all();
		$this->tmpl = 'admin:admin/role/index.html';
	}

	public function create()
	{
		$this->pagedata['menu_tree'] = K::M('module/view')->tree();
		$this->tmpl = 'admin:admin/role/detail.html';
	}

	public function detail($ID)
	{
		if(!$ID = intval($ID)){
			$this->err->add('非法的参数传递',201);
		}else if(!$detail = K::M('admin/role')->role($ID)){
			$this->err->add('要管理的角色不存在',202);
		}else{
			$this->pagedata['detail'] = $detail;
			$this->pagedata['menu_tree'] = K::M('module/view')->tree();
			$this->tmpl = 'admin:admin/role/detail.html';
		}
	}

	public function save()
	{
		if(!$data = $this->GP('data')){
			$this->err->add('非法的数据提交',201);
		}else if($ID = intval($this->GP('role_id'))){
			if($priv = $this->GP('priv')){
				$data['priv'] = is_array($priv) ? implode(',', $priv) : '';
			}else{
				$data['priv'] = '';
			}
			if(!$role = K::M('admin/role')->role($ID)){
				$this->err->add('是修改的角色不存在',202);
			}else if(K::M('admin/role')->update($ID, $data)){
				$this->err->add('修改用角色成功');
			}
		}else if($ID = K::M('admin/role')->create($data)){
			$this->err->add('添加用角色成功');
			$this->err->set_data('redirect',"?admin/role-detail-{$ID}.html");
		}
	}

	public function delete($ids)
	{
		if(K::M('verify/check')->ids($ids)){
			if(K::M('admin/role')->delete($ids)){
				$this->err->add('删除管理员角色成功');
			}
		}else {
			$this->err->add('非法请求!',201);
		}	
	}
}