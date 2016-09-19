<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Api extends Ctl
{  
    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->wechatCfg = $system->config->get('wechat');
    } 

    //平台微信号接口
    public function wx()
    {
        $this->check_signature();
    }

    public function index()
    {
        $this->check_signature();  
        //接收微信推送消息API
        Import::L('weixin/wechat.class.php');
        $wechat = new WeixinWechat();
        $data = $wechat->get_data();
        if (! empty ( $data ['ToUserName'] )) {
            $weixin = K::M('weixin/weixin')->detail_by_sid($data['ToUserName']);
        }
        if (! empty ( $data ['FromUserName'] )) {
            $openid = $data ['FromUserName'];
        }
        K::M('weixin/log')->log($weixin['wx_sid'], $data, $GLOBALS ['HTTP_RAW_POST_DATA']);
        $this->reply($data, $weixin, $wechat);
        exit();
    }


    /**
     * 通过微信事件来定位处理的插件
     * event可能的值：
     * subscribe : 关注公众号
     * unsubscribe : 取消关注公众号
     * scan : 扫描带参数二维码事件
     * click : 自定义菜单事件
     */
    protected function reply($data, $weixin, $wechat)
    {
        $key = $data ['Content'];
        $keywordArr = array ();
        if ($data ['MsgType'] == 'event') {
            $event = strtolower($data ['Event']);
            if('subscribe' == $event){ //关注
                if($reply_id = (int)$weixin['addon']['welcome']['reply_id']){                    
                    $wechat->replyId($reply_id);
                }else if($content = $weixin['addon']['welcome']['content']){
                    $wechat->replyText($content);
                }
            }else if('unsubscribe' == $event){ //取消关注

            }else if('scan' == $event){ //二难码                
                if($openid = $data['FromUserName']){                    
                    if($scene_id = (int)$data['EventKey']){                        
                        if($row = K::M('weixin/authcode')->detail($scene_id)){               
                            if($row['type'] == 'tenders'){ //关注投标
                                //$wechat->replyText('发布招标成功:'.$row['addon']['tenders_id']);
                                if($tenders_id = (int)$row['addon']['tenders_id']){
                                    if($tenders = K::M('tenders/tenders')->detail($tenders_id)){
                                        //更新招标信息，有更进微信通知业主
                                        K::M('weixin/tenders')->update_openid($tenders_id, $openid);
                                        $CFG = K::$system->_CFG;
                                        $wx_tenders_url = 'weixin/tenders-detail-'.$tenders_id.'.html';
                                        if($CFG['site']['rewrite']){
                                            $wx_tenders_url = $CFG['site']['siteurl'].'/'.$wx_tenders_url;
                                        }else{
                                            $wx_tenders_url = $CFG['sute']['siteurl'].'/index.php?'.$wx_tenders_url;
                                        }
                                        $wechat->replyText('您申请的装修服务我们客服将在24小时内联系您，您也可以在微信内查询您的<a href="'.$wx_tenders_url.'">装修进度</a>');
                                    }
                                }
                            }else if($row['type'] == 'login'){ //微信登录
                                if($a = K::M('member/weixin')->detail_by_openid($openid)){
                                    K::M('weixin/authcode')->update($row['id'], array('uid'=>$a['uid'], 'status'=>1, 'addon'=>array('openid'=>$openid)));
                                }else{
                                    K::M('weixin/authcode')->update($row['id'], array('status'=>1, 'addon'=>array('openid'=>$openid)));
                                }
                                $CFG = K::$system->_CFG;
                                $wx_login_url = 'weixin/account-login-'.$row['id'].'.html';
                                if($CFG['site']['rewrite']){
                                    $wx_login_url = $CFG['site']['siteurl'].'/'.$wx_login_url;
                                }else{
                                    $wx_login_url = $CFG['sute']['siteurl'].'/index.php?'.$wx_login_url;
                                }
                                $wechat->replyText('<a href="'.$wx_login_url.'">点击立即登录网页</a>');
                            }
                            if($row['uid']){ //绑定用户
                                if($member = K::M('member/member')->member($row['uid'])){
                                    if($mwechat = K::M('member/weixin')->detail($row['uid'])){
                                        if($mwechat['openid'] != $openid){
                                            K::M('member/weixin')->update($row['uid'], array('openid'=>$openid, 'dateline'=>__TIME, 'info'=>''));
                                        }
                                    }else{
                                        K::M('member/weixin')->create(array('uid'=>$row['uid'],'openid'=>$openid));
                                    }
                                    if($row['type'] == 'bind'){ //当是绑定
                                        $wechat->replyText('绑定微信帐号成功');
                                    }
                                }
                            }
                        }
                    }
                }

            }else if('click' == $event){ //菜单
                if(preg_match('/^MENU\:(\d+)$/i', $data['EventKey'], $m)){
                    if($menu = K::M('weixin/menu')->detail($m[1])){
                        if($reply_id = (int)$menu['reply_id']){
                            $wechat->replyId($reply_id);
                        }else if($content = $menu['content']){
                            $wechat->replyText($content);
                        }
                    }
                }else if(!empty($data['EventKey'])){
                    $key = $data['Content'] = $data['EventKey'];
                }
            }
        }
        if($key = trim($key)){
            if($keywordArr = K::M('weixin/keyword')->detail_by_keyword($key, $weixin['wx_id'])){
                if($reply_id = (int)$keywordArr['reply_id']){
                    $wechat->replyId($reply_id);
                }else if($keywordArr['plugin']){ //判断是插件，那个插件，
					$plugin_arr = explode(':',$keywordArr['plugin']);
					$plugin = $plugin_arr[0];
					$plugin_id = $plugin_arr[1];
					if($news = K::M('weixin/'.$plugin)->format_reply($plugin_id)){
						$wechat->replyNews($news);
					}					
				}else{
                    $wechat->replyText($keywordArr['content']);
                }
            }else if(!$this->sys_reply($key, $weixin, $wechat)){
                if($this->wechatCfg['robot_open']){ 
                    if(!$this->robot_tuling($key, $wechat)){
                        $wechat->replyText($content);
                    }
                }
            }
        }
    }

    protected function sys_reply($key, $weixin, $wechat)
    {
        //$wechat->replyText('执行这里了:'.$key);exit;
        if($key == '微官网'){//微信官网
            //$wechat->replyText('执行这里了143');exit;
            if($msite = K::M('weixin/msite')->detail($weixin['wx_id'])){
                $a = array('Title'=>$msite['title'], 'Description'=>$msite['intro']);
                $a['PicUrl'] =  Mdl_System_Config::$_CFG['attach']['attachurl'].'/'.$msite['photo'];
                $a['Url'] = Mdl_System_Config::$_CFG['site']['siteurl'].'/index.php?/weixin/msite-'.$msite['wx_id'].'.html';
                $wechat->replyNews(array($a));
                return true;
            }
        }
        return false;
    }

    //随即回复
    protected function rand_reply($key, $wechat)
    {
        $this->wechatCfg['rand_reply'] = array_map( 'trim', explode ("\n", $this->wechatCfg ['rand_reply']));
        $key = array_rand ($this->wechatCfg ['rand_reply']);        
        if($content = $this->wechatCfg ['rand_reply'][$key]){
            $wechat->replyText($content);
        }
    }


    // 图灵机器人
    private function robot_tuling($key, $wechat)
    {  
        if(!$tuling_key = $this->wechatCfg['tuling_key']){
            return false;
        }
        $apiurl = "http://www.tuling123.com/openapi/api?key=".$tuling_key."&info=".$key;
        $result = file_get_contents($apiurl);
        $result = json_decode ( $result, true );
        if ($result ['code'] < 100000) {
            return false;
        }
        switch ($result ['code']) {
            case '200000' :
                $text = $result ['text'] . ',<a href="' . $result ['url'] . '">点击进入</a>';
                $wechat->replyText ( $text );
                break;
            case '301000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => $info ['author'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '302000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['article'],
                            'Description' => $info ['source'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '304000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => $info ['count'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '305000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['start'] . '--' . $info ['terminal'],
                            'Description' => $info ['starttime'] . '--' . $info ['endtime'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '306000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['flight'] . '--' . $info ['route'],
                            'Description' => $info ['starttime'] . '--' . $info ['endtime'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '307000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => $info ['info'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '308000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => $info ['info'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '309000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => '价格 : ' . $info ['price'] . ' 满意度 : ' . $info ['satisfaction'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '310000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['number'],
                            'Description' => $info ['info'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '311000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => '价格 : ' . $info ['price'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            case '312000' :
                foreach ( $result ['list'] as $info ) {
                    $articles [] = array (
                            'Title' => $info ['name'],
                            'Description' => '价格 : ' . $info ['price'],
                            'PicUrl' => $info ['icon'],
                            'Url' => $info ['detailurl'] 
                    );
                }
                $wechat->replyNews ( $articles );
                break;
            default :
                $wechat->replyText ( $result ['text'] );
        }        
        return true;
    }

    protected function check_signature()
    {       
        if (! empty ( $_GET ['echostr'] ) && ! empty ( $_GET ["signature"] ) && ! empty ( $_GET ["nonce"] )) {
            $signature = $_GET ["signature"];
            $timestamp = $_GET ["timestamp"];
            $nonce = $_GET ["nonce"];
            $token = $this->wechatCfg['wechat_token'] ? $this->wechatCfg['wechat_token'] : md5(__CFG::SECRET_KEY.__CFG::Authorize);
            $tmpArr = array ($token, $timestamp, $nonce);
            sort ( $tmpArr, SORT_STRING );
            $tmpStr = sha1 ( implode ( $tmpArr ) );
            if ($tmpStr == $signature) {
                echo $_GET ["echostr"];
            }
            exit ();
        }         
    }

}