<?php
error_reporting(E_ALL ^ E_NOTICE);
define('UC_CLIENT_VERSION', '1.6.0');
define('UC_CLIENT_RELEASE', '20110501');

define('API_DELETEUSER', 1);			//用户删除 API
define('API_RENAMEUSER', 1);            //用户改名 API
define('API_GETTAG', 1);                //获取标签 API
define('API_SYNLOGIN', 1);              //同步登录 API
define('API_SYNLOGOUT', 1);             //同步登出 API
define('API_UPDATEPW', 1);              //更改用户密码
define('API_UPDATEBADWORDS', 1);        //更新关键字列表
define('API_UPDATEHOSTS', 1);           //更新域名解析缓存
define('API_UPDATEAPPS', 1);            //更新应用列表
define('API_UPDATECLIENT', 1);          //更新客户端缓存
define('API_UPDATECREDIT', 1);          //更新用户积分
define('API_GETCREDITSETTINGS', 1);     //向 UCenter 提供积分设置
define('API_GETCREDIT', 1);             //获取用户的某项积分
define('API_UPDATECREDITSETTINGS', 1);  //积分设置

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');
require(substr(__FILE__, 0, -10).'system/home/index.php');
$K = new Index('magic-shell');
$K->config->ucenter();
if(!defined('UC_OPEN') || !UC_OPEN || !defined('UC_KEY') || !UC_KEY){
    exit('UC Closed');
}
$get = $post = array();
$code = @$_GET['code'];
parse_str(UC_Authcode($code, 'DECODE', UC_KEY), $get);
$timestamp = time();
if(empty($get)){
	exit('Invalid Request');
}elseif($timestamp - $get['time'] > 3600){
	exit('Authracation has expiried,'.$timestamp.':'.$get['time']);
}

$action = $get['action'];
define('CU_CLIENT_DIR', __CFG::DIR.'libs/uc_client/');
include_once CU_CLIENT_DIR.'lib/xml.class.php';
$post = xml_unserialize(file_get_contents('php://input'));

if(strtoupper(UC_CHARSET) == 'GBK'){
	foreach($get as $k=>$v){
		$get[$k] = iconv('GBK', 'UTF-8//TRANSLIT', $v);
	}
	foreach($post as $k=>$v){
		$post[$k] = iconv('GBK', 'UTF-8//TRANSLIT', $v);
	}
}

if(in_array($action, array('test', 'deleteuser', 'renameuser', 'synlogin', 'synlogout', 'updatepw', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
	$uc_note = new uc_note();
	echo $uc_note->$get['action']($get, $post);
	exit;
} else {
	exit(API_RETURN_FAILED);
}

class uc_note
{

	public function __construct()
	{

	}

	function test($get, $post) 
	{
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post)
 	{
		if(!API_DELETEUSER) {
			return API_RETURN_FORBIDDEN;
		}
		$uids = str_replace("'", '', stripslashes($get['ids']));
		if(K::M('verify/check')->ids($uids)){
			K::M('member/handler')->delete($uids);		
		}
		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post)
	{
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}
		if($uname = $get['newusername']){
			$ucuid = (int)$get['uid'];
			if(($ucm = K::M('member/view')->member_by_uc($ucuid))) {
				if(!$member = K::M('member/view')->member($uname, 'uname')){
					K::M('member/handler')->update(array('uname'=>$uname), "uid='{$ucm['uid']}'");
				}				
			}
		}
		return API_RETURN_SUCCEED;
	}


	function synlogin($get, $post)
	{
		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$uid = intval($get['uid']);
		if(($member = K::M('member/view')->member_by_uc($uid))) {
			$token = K::$system->auth->create_token($member['uid'], $member['passwd']);
			K::$system->cookie->set('TOKEN', $token);
		}
		return API_RETURN_SUCCEED;
	}

	function synlogout($get, $post)
	{
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		K::$system->cookie->delete('TOKEN');
		return API_RETURN_SUCCEED;
	}

	function updatepw($get, $post)
	{
		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}
		$uname = $get['username'];
		$newpw = md5($get['password']);
		if($member = K::M('member/view')->member($uname, 'uname')){
			K::M('member/view')->update($member['uid'], array('passwd'=>$newpw));
		}
		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post)
	{
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}

		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$cachefile = CU_CLIENT_DIR.'data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post)
	{
		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = CU_CLIENT_DIR.'data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post)
	{
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}

		$UC_API = '';
		if($post['UC_API']) {
			$UC_API = $post['UC_API'];
			unset($post['UC_API']);
		}
		$cachefile = CU_CLIENT_DIR.'data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		if($UC_API && is_writeable(__CFG::DIR.'./config/uc_config.php')) {
			if(preg_match('/^https?:\/\//is', $UC_API)) {
				$configfile = trim(file_get_contents(__CFG::DIR.'./uc_config.php'));
				$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
				$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '".addslashes($UC_API)."');", $configfile);
				if($fp = @fopen(__CFG::DIR.'./uc_config.php', 'w')) {
					@fwrite($fp, trim($configfile));
					@fclose($fp);
				}
			}
		}
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post)
	{
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = CU_CLIENT_DIR.'data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post)
	{
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];

		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) 
	{
		return API_RETURN_FORBIDDEN;
	}

	function getcreditsettings($get, $post)
	{
		return API_RETURN_FORBIDDEN;
	}

	function updatecreditsettings($get, $post)
	{
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		return API_RETURN_SUCCEED;
	}

	function addfeed($get, $post)
	{
		if(!API_ADDFEED) {
			return API_RETURN_FORBIDDEN;
		}
		return API_RETURN_SUCCEED;
	}

	public function iconv($str, $uc=false)
	{
    	if(strtoupper(UC_CHARSET) != 'UTF-8'){
    		if($uc){
    			return iconv('GBK', 'UTF-8//TRANSLIT', $str);
    		}else{
    			return iconv('UTF-8', 'GBK//TRANSLIT', $str);
    		}
    	}
    	return $str;
	}
}

function UC_Authcode($string, $operation = 'DECODE', $key = '', $expiry = 0 ,$source=0)
{
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}