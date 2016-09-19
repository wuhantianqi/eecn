<?php

@ini_set('display_errors', 'On');
@error_reporting(30719 ^ 8 ^ 2048 ^ 2);
@set_time_limit(0);
@ini_set('memory_limit', '128M');
@ini_set('allow_url_fopen', 'On');
@date_default_timezone_set('Asia/Shanghai');
define('__APP__', 'install');
define('__APP_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('__BASE_DIR', dirname(__APP_DIR) . DIRECTORY_SEPARATOR);
define('__CORE_DIR', dirname(__APP_DIR) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
header('Content-type: text/html; charset=UTF-8');

if (!file_exists(__CORE_DIR . 'config.php')) {
	exit('请把system/config.default.php 重命名为 system/config.php 再进行安装');
}
else if (!is_writable(__CORE_DIR . 'config.php')) {
	exit('system/config.php需要写入权限，请先修改权限后再进行安装');
}
else if (!is_writable(__CORE_DIR . 'data/tplcache')) {
	exit('system/data/tplcache 目录需要可写入权限,请设置该目录权限');
}
require (__CORE_DIR . 'framework/kernel.php');
class Index extends Kernel
{
	protected function _init()
	{
		K::$system = &$this;
		$this->_client_ip();
		if (('success' != $_GET['step']) && file_exists(__CORE_DIR . 'data/install.lock')) {
			exit('您已经安装过本系统，如果想重新安装，请删除system/data目录下install.lock文件');
		}
	}

	protected function _run()
	{
		$step = trim($_GET['step']);

		if (!in_array($step, array('protocol', 'check', 'config', 'install', 'success'))) {
			$step = 'protocol';
		}

		$request['step'] = $step;
		$this->request = $request;
		$this->pagedata = array();
		$this->{$step}();
		$this->output();
	}

	protected function protocol()
	{
		$this->tmpl = 'protocol.html';
	}

	public function check()
	{
		$sysinfo = array('version' => KT_VERSION . ' RELEASE ' . KT_RELEASE . ' [<a href="http://www.ijh.cc/" class="blue" target="_blank">查看最新版本</a>]', 'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]', 'server_os' => PHP_OS, 'web_server' => $_SERVER['SERVER_SOFTWARE'], 'php_version' => PHP_VERSION, 'upload_max_filesize' => ini_get('upload_max_filesize'), 'max_execution_time' => ini_get('max_execution_time') . '秒', 'memory_limit' => ini_get('memory_limit'), 'safe_mode' => (bool) ini_get('safe_mode') ? '<span style="color:green;">√支持</span>' : '<span sylt="color:red;">×不可读</span>', 'zlib' => function_exists('gzclose') ? '<span style="color:green;">√支持</span>' : '<span sylt="color:red;">×不可读</span>', 'curl' => function_exists('curl_getinfo') ? '<span style="color:green;">√支持</span>' : '<span sylt="color:red;">×不支持</span>', 'timezone' => function_exists('date_default_timezone_get') ? date_default_timezone_get() : 'NO');

		if (function_exists('gd_info')) {
			$gd_info = @gd_info();
			$sysinfo['gd_version'] = $gd_info['GD Version'];
		}
		else {
			$sysinfo['gd_version'] = '<span sylt="color:red;">×不支持</span>';
		}

		$this->pagedata['sysinfo'] = $sysinfo;
		$need_check_dirs = array('system/data', 'system/data/cache', 'system/data/tplcache', 'system/data/tpladmin', 'system/data/logs', 'attachs', 'attachs/cache', 'attachs/face', 'attachs/photo', 'attachs/product', 'attachs/homepics', 'attachs/temp');
		$this->pagedata['check_dirs'] = $this->check_dirs($need_check_dirs);
		$this->tmpl = 'check.html';
	}

	public function config()
	{
		$this->tmpl = 'config.html';
	}

	public function install()
	{
		if (!$db = $_POST['db']) {
			$this->msgbox('数据配置不正确', 'index.php?step=config');
		}
		else if (!$auth = $_POST['auth']) {
			$this->msgbox('授权信息不正确', 'index.php?step=config');
		}
		else if (!$admin = $_POST['admin']) {
			$this->msgbox('管理员信息不正确', 'index.php?step=config');
		}

		$host = $_SERVER['HTTP_HOST'];
		$domain = trim($auth['domain']);
		$key = trim($auth['key']);
		$db_host = trim($db['host']);
		$db_port = trim($db['port']);
		$db_uname = trim($db['uname']);
		$db_passwd = trim($db['passwd']);
		$db_name = trim($db['name']);
		$tablepre = trim($db['tablepre']);
		$admin_uname = trim($admin['uname']);
		$admin_passwd = trim($admin['passwd']);
		$admin_mail = trim($admin['mail']);
//		$this->listion($key, $domain);

		if (!file_exists(__APP_DIR . 'data/jh_table.sql')) {
			exit('安装脚本缺失');
		}
		else if (!file_exists(__APP_DIR . 'data/jh_data.sql')) {
			exit('安装脚本缺失');
		}

		if (!$link = mysql_connect($db_host . ':' . $db_port, $db_uname, $db_passwd)) {
			$this->msgbox('连接数据库失败', 'index.php?step=config');
		}
		else if (!mysql_select_db($db_name)) {
			if (!mysql_query('CREATE DATABASE `' . $db_name . '` CHARACTER SET utf8')) {
				$this->msgbox('连接数据库【' . $db_name . '】不存在并且没有权限创建库', 'index.php?step=config');
			}
			else if (!mysql_select_db($db_name)) {
				$this->msgbox('连接数据库【' . $db_name . '】不存在并且没有权限创建库', 'index.php?step=config');
			}
		}

		mysql_query('SET NAMES UTF8');
		$sql = file_get_contents(__APP_DIR . 'data/jh_table.sql') . "\n";
		$sql .= file_get_contents(__APP_DIR . 'data/jh_data.sql') . "\n";

		if ($_POST['initdemo']) {
			if (file_exists(__APP_DIR . 'data/jh_demo.sql')) {
				$sql .= file_get_contents(__APP_DIR . 'data/jh_demo.sql') . "\n";
			}
		}

		$sql = preg_replace('/\\/\\*.*?\\*\\//i', '', $sql);
		$sql = str_replace("\r", "\n", str_replace(' jh_', ' ' . $tablepre, $sql));
		$sql = str_replace("\r", "\n", str_replace(' `jh_', ' `' . $tablepre, $sql));
		$ret = array();
		$num = 0;

		foreach (explode(';' . "\n" . '', trim($sql)) as $query ) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));

			foreach ($queries as $query ) {
				$ret[$num] .= ((isset($query[0]) && ($query[0] == '#')) || (isset($query[1]) && isset($query[1]) && (($query[0] . $query[1]) == '--')) ? '' : $query);
			}

			$num++;
		}

		unset($sql);

		foreach ($ret as $query ) {
			$query = trim($query);

			if ($query) {
				mysql_query($query);
			}
		}

		$md5pwd = md5($admin_passwd);
		$time = time();
		$sql = 'INSERT INTO `' . $tablepre . 'admin`(`admin_name`,`passwd`,`role_id`,`dateline`) VALUES(\'' . $admin_uname . '\', \'' . $md5pwd . '\', \'1\', \'' . $time . '\')';
		mysql_query($sql);
		$secretkey = md5(md5(microtime() . $key . $domain . rand(), true) . $host . uniqid());
		$siteArr = array('title' => '江湖家居门户系统', 'mail' => 'ijianghu@qq.com', 'kfqq' => '800070067', 'phone' => '0551-64278115', 'siteurl' => 'http://' . $host, 'secret' => trim(base64_encode($secretkey), '='), 'mobile' => '0', 'ucenter' => '0', 'city_id' => '7', 'multi_city' => '0', 'city_domain' => '', 'rewrite' => '0', 'intro' => '江湖家居经历了一年半的发展从原先不了解这个行业到做出最适合这个行业营销的平台，江 湖是一直实事求是的和站长站在统一战线！2014年是垂直行业互联网创业的最佳年份 ，那么江湖家居无疑是家居垂直领域最适合站长的O2O系统！', 'tongji' => '', 'icp' => '皖ICP备13010842号');
		$site = serialize($siteArr);
		$sql = 'UPDATE `' . $tablepre . 'system_config` SET `v`=\'' . $site . '\' WHERE `k`=\'site\'';
		mysql_query($sql);
		$cfg = file_get_contents(__CORE_DIR . 'config.php');
		$cfg = preg_replace('/CONST\\s+RES_URL\\s+\\=\\s+\\\'(.*)\\\'/i', 'CONST RES_URL = \'http://' . $host . '/static\'', $cfg);
		$cfg = preg_replace('/CONST\\s+MYSQL\\s+\\=\\s+\\\'(.*)\\\'/i', 'CONST MYSQL = \'mysql://' . $db_uname . ':' . $db_passwd . '@' . $db_host . ':' . $db_port . '/' . $db_name . '/' . $tablepre . '/UTF8\'', $cfg);
		$cfg = preg_replace('/CONST\\s+SECRET_KEY\\s+\\=\\s+\\\'(.*)\\\'/i', 'CONST SECRET_KEY = \'' . $secretkey . '\'', $cfg);
		$cfg = preg_replace('/CONST\\s+Authorize\\s+\\=\\s+\\\'(.*)\\\'/i', 'CONST Authorize = \'' . $key . '\'', $cfg);
		file_put_contents(__CORE_DIR . 'config.php', $cfg);
		file_put_contents(__CORE_DIR . 'data/install.lock', '1');
		file_put_contents(__CORE_DIR . 'data/update.lock', '1');
		$this->load_model('cache/cache')->clean();
		header('Location:index.php?step=success');
		exit();
	}

	public function success()
	{
		if (!file_exists(__CORE_DIR . 'data/install.lock')) {
			header('Location:index.php');
			exit();
		}

		$this->tmpl = 'success.html';
	}

	public function msgbox($msg, $url = '')
	{
		$this->pagedata['msg'] = $msg;
		$this->pagedata['gourl'] = $url;
		$this->tmpl = 'msgbox.html';
		$this->output();
	}

	protected function output()
	{
		$smarty = $this->load_model('system/frontend');
		$smarty->setTemplateDir(__APP_DIR . 'view');

		foreach ($this->pagedata as $k => $v ) {
			$smarty->assign($k, $v);
		}

		$smarty->assign('request', $this->request);
		$smarty->display($this->tmpl);
		exit();
	}

	public function check_dirs($dirs)
	{
		$checked_dirs = array();

		foreach ($dirs as $k => $dir ) {
			$checked_dirs[$k]['dir'] = $dir;

			if (!file_exists(__BASE_DIR . $dir)) {
				$checked_dirs[$k]['read'] = '<span style="color:red;">目录不存在</span>';
				$checked_dirs[$k]['write'] = '<span style="color:red;">目录不存在</span>';
			}
			else {
				if (is_readable(__BASE_DIR . $dir)) {
					$checked_dirs[$k]['read'] = '<span style="color:green;">√可读</span>';
				}
				else {
					$checked_dirs[$k]['read'] = '<span sylt="color:red;">×不可读</span>';
				}

				if (is_writable(__BASE_DIR . $dir)) {
					$checked_dirs[$k]['write'] = '<span style="color:green;">√可写</span>';
				}
				else {
					$checked_dirs[$k]['write'] = '<span style="color:red;">×不可写</span>';
				}
			}
		}

		return $checked_dirs;
	}

	public function listion($key, $domain)
	{
		$key = trim($key);

		if (!preg_match('/^[0-9a-f]{32}$/i', $key)) {
			exit(base64_decode('ICAgICAgICAgICAgICAg'));
		}

		$host = $_SERVER['HTTP_HOST'];
		if (($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') || ($host !== 'localhost')) {
			if (!preg_match('/^([\\w\\.\\_\\-]+)\\.[a-z]{2,}$/i', $domain)) {
				exit(base64_decode('ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg'));
			}
			else if (in_array($domain, array('com.cn', 'net.cn', 'org.cn'))) {
				exit(base64_decode('ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg'));
			}
			else if (substr($host, -strlen($domain)) != $domain) {
				exit(base64_decode('ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg'));
			}
		}

		$locked = 'xMpCOKC5I4INzFCab3WEm8TKQjiguSOCDcxQmm91hJvEykI4oLkjgg3MUJpvdYSbxMpCOKC5I4INzFCab3WEm8TKQjiguSOCDcxQmm91hJvEykI4oLkjgg3MUJpvdYSbxMpCOKC5I4INzFCab3WEm8TKQjiguSOCDcxQmm91hJvEykI4oLkjgg3MUJpvdYSbxMpCOKC5I4INzFCab3WEmw==';
		$ret = file_get_contents(sprintf('http://www.ijh.cc/index.php?ctl=jiaju&act=install&key=%s&domain=%s&version=%s', $key, $domain, JH_VERSION . '.' . JH_RELEASE));
		$hash = md5($key . base64_encode(str_repeat(md5($ret, true), 10)));

		if (md5($key . $locked) != $hash) {
			exit(base64_decode('ICAg'));
		}

		return true;
	}
}
new Index();



?>
