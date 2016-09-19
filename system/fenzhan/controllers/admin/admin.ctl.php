<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: admin.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Admin_Admin extends Ctl
{
	
	public function index($page=1)
	{
		$pager['page'] = $page = max(intval($page), 1);
		$pager['limit'] = $limit = 50;
		$pager['count'] = $count = 0;
		$filter['closed'] = 0;
		$filter['city_id'] = CITY_ID;
		if($items = K::M('fenzhan/admin')->items($filter, null, $page, $limit, $count)){
			$pager['count'] = $count;
			$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
			$this->pagedata['items'] = $items;
		}
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'fenzhan:admin/admin/index.html';
	}

	public function create()
	{
		if($this->fenzhan->role['role'] == 'editor'){
			$this->err->add('您没有权限创建或修改管理员',201);
		}else{
			$this->pagedata['menu_tree'] = K::M('fenzhan/auth')->fenzhan_tree();
			$this->tmpl = 'fenzhan:admin/admin/detail.html';
		}
	}

	public function edit($ID)
	{
		if($this->fenzhan->role['role'] == 'editor'){
			$this->err->add('您没有权限创建或修改管理员',201);
		}else if(!$ID = intval($ID)){
			$this->err->add('没有指定要修改的管理员',202);
		}else if(!$detail = K::M('fenzhan/admin')->admin($ID)){
			$this->err->add('你要修改的管理员不存在或已经删除',201);
		}else{
			
			if($detail['priv']){
				foreach($detail['priv'] as $k => $v){
					$tmeps[$v] = $v;
				}
				$detail['priv'] = $tmeps;
			}
			$this->pagedata['detail'] = $detail;
			
			$this->pagedata['menu_tree'] = K::M('fenzhan/auth')->fenzhan_tree();
			$this->tmpl = 'fenzhan:admin/admin/detail.html';
		}
	}

	public function save()
	{	
		if($this->fenzhan->admin['role'] == 'editor'){
			$this->err->add('您没有权限创建或修改管理员',201);
		}else if(!$data = $this->GP('data')){
			$this->err->add('非法的数据提交',202);
		}else if($ID = $this->GP('fz_uid')){
			if(empty($data['fz_passwd'])){
				unset($data['fz_passwd']);
			}
			if(K::M('fenzhan/admin')->update($ID, $data)){
				$this->err->add('修改管理员成功');
			}
		}else if(K::M('fenzhan/admin')->create($data)){
			$this->err->add('添加管理员成功');
		}
	}

    public function delete($fz_uid)
    {
        if(!empty($fz_uid)){
            if(K::M('fenzhan/admin')->delete($fz_uid, true)){
                $this->err->add('删除管理员成功');
            }
        }else if($pks = $this->GP('fz_uid')){
            if(K::M('fenzhan/admin')->delete($pks, true)){
                $this->err->add('批量删除管理员成功');
            }
        }else{
            $this->err->add('未指定要删除的管理员ID', 401);
        }
    }
}