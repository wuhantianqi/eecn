<?php
/**
 * Copy	Right Anhuike.com
 * $Id auth.mdl.php shzhrui<anhuike@gmail.com>
 */

class Mdl_Fenzhan_Auth extends Model
{
	public $fz_uid = 0;
	public $fz_name = '';
	public $admin = array();
	public $role = null;

	public $_menu_tree = null;
	
	public function token()
	{
		if($token = $this->cookie->get('FZTOKEN')){
			if($this->_check_token($token)){
                $a = array('FZTOKEN'=>$token,'AGENT'=>$_SERVER['HTTP_USER_AGENT']);
                K::$system->OFZTOKEN = K::M('secure/crypt')->arrhex($a);
				return true;
			}
			$this->cookie->delete('FZTOKEN');
		}
		return false;
	}

	/**
	 * 用户登录
	 * @param   $u  用户名/邮箱
	 * @param   $p  密码{明文密码}
	 */
	public function login($u, $p, $city_id)
	{
		$passwd = md5($p);
		if(!$m = K::M('fenzhan/admin')->admin(0,$u)){
			$this->err->add('登录名或密码不正确!!',111);
		}else if($m['fz_passwd'] != $passwd){
			$this->err->add('登录名或密码不正确!!',112);
		}else if($m['city_id'] != $city_id){
			$this->err->add('分站城市错误!!',112);
		}else if($m['closed'] == 3){
			$this->err->add('很抱歉,访用户已经删除!!',112);
		}else if($m['closed'] == 2){
			$this->err->add('很抱歉,该用户已锁定不能登录',113);
		}else{
			$this->fz_uid = $m['fz_uid'];
			$this->fz_name = $m['fz_name'];
			$this->admin = $m;
			$this->role = K::M('admin/role')->fenzhan();
			$token = $this->create_token($this->fz_uid, $passwd);
			K::M('fenzhan/admin')->update_login($this->fz_uid);
			$this->cookie->set('FZTOKEN', $token, 0);
			$this->cookie->set('FZADMIN', $this->fz_name, NULL);
			return true;
		}
		return false;
	}
	

	public function loginout()
	{
		$this->cookie->delete('FZTOKEN');
		return true;		
	}

	public function modifypasswd($odlpasswd, $newpasswd)
	{
		if(md5($odlpasswd) != $this->admin['fz_passwd']){
			$this->err->add('原密码输入不正确', 421);
			return false;
		}else if(!preg_match('/^[\x21-\x7E]{6,15}$/', $newpasswd)){
            $this->err->add('用户密码只包含(数字,大小写字母,特殊符号,不含空格)长度6~15字符', 422);
            return false;
        }
        $passwd = md5($newpasswd);
        return K::M('fenzhan/admin')->update_passwd($this->fz_uid, $passwd);
	}
  
	//生成TOKEN
	public function create_token($uid,$pwd)
	{
		//$s = md5($_SERVER['HTTP_USER_AGENT'].$uid.md5(__CFG::SECRET_KEY.$pwd.__IP,true),true);
		//$s = K::M('secure/crypt')->strh);
		//$s = strtoupper(md5($_SERVER['HTTP_USER_AGENT'].$uid.md5(__CFG::SECRET_KEY.$pwd.__IP,true)));
		$s = strtoupper(md5($_SERVER['HTTP_USER_AGENT'].$uid.md5(__CFG::SECRET_KEY.$pwd,true)));
		$token = "{$uid}-KT{$s}";
		return $token;
	}

	private function _check_token($token)
	{
		$a = explode('-',$token);
		if(!$uid = intval($a[0])){
			return false;
		}
		if(!$m = K::M('fenzhan/admin')->admin($uid,'fz_uid')){
			return false;
		}else if($this->create_token($m['fz_uid'],$m['fz_passwd']) != $token){
			return false;
		}else if(!in_array($m['closed'],array(0,1))){
			return false;
		}
		$this->fz_uid = $m['fz_uid'];
		$this->fz_name = $m['fz_name'];
		$this->admin = $m;
		$this->role = K::M('admin/role')->fenzhan();
		return true;
	}


	public function tree()
	{
		if($this->_menu_tree !== null){
			return $this->_menu_tree;
		}		
		if(!$this->admin['priv'] && $this->admin['role'] != 'admin'){
			return false; 
		}
		if(!$tree = K::M('module/view')->tree()){
			return false;
		}
		$menu = array();
		foreach($tree as $k=>$v){
			$a = array();
			foreach((array)$v['menu'] as $kk=>$vv){
				$b = array();
				foreach((array)$vv['menu'] as $kkk=>$vvv){
					if($vvv['visible'] && $this->check_priv($kkk)){
						$b[$kkk] = $vvv;
					}
				}
				if(!empty($b) || $this->role['role'] == 'admin'){ 
					$vv['menu'] = $b;
					$a[$kk] = $vv;
				}
			}
			if(!empty($a) || $this->role['role'] == 'admin'){ 
				$v['menu'] = $a;
				$menu[$k] = $v;
			}
		}
		$this->_menu_tree = $menu;
		return $menu;
	}

	public function fenzhan_tree()
	{
		
		if(!$tree = K::M('module/view')->tree()){
			return false;
		}
		$menu = array();
		foreach($tree as $k=>$v){
			$a = array();
			foreach((array)$v['menu'] as $kk=>$vv){
				$b = array();
				foreach((array)$vv['menu'] as $kkk=>$vvv){
					if($this->check_priv($kkk)){
						$b[$kkk] = $vvv;
					}
				}
				if(!empty($b) || $this->role['role'] == 'admin'){ 
					$vv['menu'] = $b;
					$a[$kk] = $vv;
				}
			}
			if(!empty($a) || $this->role['role'] == 'admin'){ 
				$v['menu'] = $a;
				$menu[$k] = $v;
			}
		}
		$this->_menu_tree = $menu;
		return $menu;
	}

	public function check_priv($mod_id)
	{	
		if(in_array($mod_id, $this->role['priv'])){
			if($this->admin['role'] == 'admin'){
				return true;
			}else if(in_array($mod_id, $this->admin['priv'])){
				return true;
			}
		}
		return false;
	}
}