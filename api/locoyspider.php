<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: locoyspider.php 6233 2014-09-05 11:45:55Z youyi $
 */
require('../system/home/index.php');
$K = new Index('magic-shell');
set_time_limit(0);
ini_set('memory_limit','128M');
ini_set('allow_url_fopen', 'On');
$cfg = $K->config->get('locoyspider');
new locoyspider($cfg);

class locoyspider
{

    public $cfg = array();

    public function __construct($cfg)
    {
        $this->cfg = $cfg;
        if(!$this->cfg['isopen']){
            exit('Locoyspider Api Closed!');
        }else if(!$Authorize = trim($_REQUEST['Authorize'])){
            exit('Authorize error');
        }else if($this->cfg['Authorize'] != $Authorize){
            exit('Authorize error');
        }else if(!$api = trim($_REQUEST['api'])){
            $api = 'index';
        }else if(!in_array($api, array('index', 'article','cate','city','area', 'company', 'casepic', 'shop','shopcate', 'home', 'product', 'photo', 'member','designer','ask'))){
            exit('Api error');
        }
        $this->$api();
    }
    
    public function index()
    {
        echo "HQ Locoyspider Api success";
    }

    public function article()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            
            $data['from'] = 'article';
            $attachcfg = K::$system->config->get('attach');
            //正则匹配文章中的图片链接，选择第一张作为文章的缩略图，注意目录结构
            if(preg_match('/attachs\/photo\/(\w+)\/(\d+)\/(\w+)\.(jpg|png|gif|jpeg)/i', $data['content'], $match)){
				if($this->cfg['Autothumb']){
					echo $this->cfg['Autothumb'];
                    $thumb = substr($match[0], 8);
					
                }
                $data['content'] = preg_replace('/attachs\/photo\/(\w+)\/(\d+)\/(\w+)\.(jpg|png|gif|jpeg)/i', $attachcfg['attachurl']."/photo/$1/$2/$3.$4", $data['content']);
            }
            if(empty($data['thumb']) && $thumb){
                $data['thumb'] = $thumb;
            }
            if($article_id = K::M('article/article')->create($data)){
                echo 'success';
            }else{
                K::$system->err->json();
            }
        }
    }

    public function cate()
    {
        $tree = '';
        if($cats = K::M('article/cate')->tree('article')){
            foreach($cats as $v){
                $tree .= $v['cat_id'].':'.$v['title'].';';
                foreach((array)$v['children'] as $vv){
                    $tree .= $vv['cat_id'].':  '.$vv['title'].';';
                    foreach((array)$vv['children'] as $vvv){
                        $tree .= $vvv['cat_id'].':    '.$vvv['title'].';';
                    }
                }
            }       
        }
        echo $tree;
    }

    public function designer()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            if(!$uname = trim($data['name'])){
                echo 'uname error';
            }
            if(K::M('verify/check')->mail($data['mail'])){
                $mail = $data['mail'];
            }else{
                $mail = substr(md5($uname),8,10).'@ijh.cc';
            }
            $face = $data['face'];
            unset($data['mail'], $data['face']);
            $account = array('uname'=>$uname, 'mail'=>$mail,'from'=>'designer');
            $account['passwd'] = substr(md5(microtime().PRI_KEY.rand()),0,8);
            if($designer_id = K::M('designer/designer')->create($data, $account)){
                if($face){
                    $cfg = K::$system->config->get('attach');
                    if($face = $this->download($face)){
                        $face = $cfg['attachdir'].$face;
                        K::M('member/face')->update_face($designer_id, $face);
                        @unlink($face);
                    }
                }
                echo 'success';
            }else{
                K::$system->err->json();
            }         
        }          
    }

    public function company()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            $siteCfg = K::$system->config->get('site');
            if($data['logo'] = $this->download($data['logo'])){
                $data['logo'] = str_replace('\\', '/', $data['logo']);
            }
            if($data['thumb']){
                $data['thumb'] = $this->download($data['square_logo']);
                $data['thumb'] = str_replace('\\', '/', $data['square_logo']);
            }else if(empty($data['thumb'])){
                $data['thumb'] = $data['logo'];
            }
            if(!K::M('verify/check')->phone($data['tel']) && !K::M('verify/check')->mobile($data['tel'])){
                $data['phone'] = $siteCfg['phone'];
            }
			$data['score1'] = rand(3,5);
            if($company_id = K::M('company/company')->create($data, true)){
                if(!$ex = K::$system->gpc->p('ex')){
                    $ex = array();
                }
                $ex['company_id'] = $company_id;
                K::M('company/fields')->create($ex, true);    
                echo 'success';
            }else{
                K::$system->err->json();
            }           
        }           
    }

    public function city()
    {
        $tree = '';
        if($citys = K::M('data/city')->options()){
            foreach($citys as $k=>$v){
                $tree .= $k.':'.$v.';';
            }       
        }
        echo $tree;
    }

    public function area()
    {
        $tree = '';
        $city_id = (int)$_GET['city_id'];
        if($areas = K::M('data/area')->options($city_id)){
            foreach($areas as $k=>$v){
                $tree .= $k.':'.$v.';';
            }       
        }
        echo $tree;        
    }

    public function shop()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            $siteCfg = K::$system->config->get('site');
            if($data['logo'] = $this->download($data['logo'])){
                $data['logo'] = str_replace('\\', '/', $data['logo']);
            }else if(!preg_match('/^[\w\/\.]+\.(jpg|jpeg|png|gif)$/i', $data['logo'])){
                unset($data['logo']);
            }
            if(!K::M('verify/check')->phone($data['phone']) && !K::M('verify/check')->mobile($data['phone'])){
                if(K::M('verify/check')->phone($data['mobile']) || K::M('verify/check')->mobile($data['mobile'])){
                    $data['phone'] = $data['mobile'];
                }else{
                    $data['phone'] = $siteCfg['phone'];
                }
            }
            if(empty($data['name'])){
                $data['name'] = $data['title'];
            }
            unset($data['mobile']);
            $data['dateline'] = __CFG::TIME;
            if($shop_id = K::M('shop/shop')->create($data, true)){
                if(!$fields = K::$system->gpc->p('fields')){
                    $fields = array();
                }
                if(preg_match("/src=\"([\w\/]+)\.(jpg|jpeg|png|gif)\"/i", stripcslashes($fields['banner']), $match)){
                    $fields['banner'] = $match[1].'.'.$match[2];
                }else if(!preg_match('/^[\w\/\.]+\.(jpg|jpeg|png|gif)$/i', $fields['banner'])){
                    unset($fields['banner']);
                }                
                K::M('shop/fields')->update($shop_id, $fields);    
                echo 'success';
            }else{
                //print_r(K::$system->db->SQLLOG());
                K::$system->err->json();
            }
        }   
    }

    public function shopcate()
    {
        $tree = '';
        $pid = (int)K::$system->gpc->p('pid');
        if($cats = K::M('shop/cate')->childrens($pid)){
            foreach($cats as $v){
                $tree .= $v['cat_id'].':'.$v['title'].';';
            }       
        }
        echo $tree;        
    }

    public function casepic()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            $cfg = K::$system->config->get('attach');
            $D = $cfg['attachdir'].'temp'.DIRECTORY_SEPARATOR;
            $photos = $data['photos'];
            unset($data['photos']);
            $data['clientip'] = __IP;
            $data['dateline'] = __CFG::TIME;
            if($case_id = K::M('case/case')->create($data, true)){
                foreach(explode(';', $photos) as $photo){
                    $index ++ ;
                    $title = $data['title'].$index;
                    if(preg_match('/^(.*):(http:\/\/.+\.(jpg|png|jpeg|gif))$/i', $photo, $a)){
                        $title = $a[1];
                        $photo = $a[2];
                    }
                    $file = $D.md5($photo);
                    $index = 0;
                    if(preg_match('/^http:\/\/.+\.(jpg|png|jpeg|gif)$/i', $photo, $match)){
                        if($content = file_get_contents($photo)){                           
                            file_put_contents($file, $content);
                            $info = getimagesize($file);
                            $attach = array();
                            $attach['type'] = $info['mime'];
                            $attach['size'] = filesize($file);
                            $attach['tmp_name'] = $file; 
                            $attach['name'] = $data['title'].$index.'.'.$match[1];
                            $attach['error'] = '0';
                            K::M('case/photo')->upload($case_id, $attach);                      
                        }
                    }
                }
                echo 'success';
            }else{
                K::$system->err->json();
            }
        }         
    }

    public function photo()
    {

    }

    public function product()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            $cfg = K::$system->config->get('attach');
            $data['clientip'] = __IP;
            $data['dateline'] = __CFG::TIME;
            $data['shop_id'] = $data['shop_id'] ? (int)$data['shop_id'] : 1;
            $data['cat_id'] = $data['cat_id'] ? (int)$data['cat_id'] : 1;
            $data['vcat_id'] = $data['vcat_id'] ? $data['vcat_id'] : 1;
            if($product_id = K::M('product/product')->create($data)){
                if($fields = K::$system->gpc->p('fields')){
                    if($fields['info']){
                        $fields['info'] = preg_replace("/src=\"([\w+\/]+)\.(jpg|png|gif|jpeg)\"/i", "src=\"/attachs/$1.$2\"", $fields['info']);
                        $fields['info'] = preg_replace("/src=\'([\w+\/]+)\.(jpg|png|gif|jpeg)\'/i", "src=\"/attachs/$1.$2\"", $fields['info']);
                    }
                    K::M('product/fields')->update($product_id, $fields);
                }
                if($photos = K::$system->gpc->p('photos')){
                    foreach(explode(';', $photos) as $photo){
                        $index ++ ;
                        $title = $data['title'].$index;
                        if(preg_match('/^(.*):(http:\/\/.+\.(jpg|png|jpeg|gif))$/i', $photo, $match)){
                            $title = $match[1];
                            $photo = $match[2];
                        }
                        if($file = $this->download($photo)){                            
                            $file = $cfg['attachdir'].$file;
                            $info = getimagesize($file);
                            $attach = array();
                            $attach['type'] = $info['mime'];
                            $attach['size'] = filesize($file);
                            $attach['tmp_name'] = $file; 
                            $attach['name'] = $title.'.jpg';
                            $attach['error'] = 0;
                            K::M('product/photo')->upload($product_id, $attach);                              
                        }
                    }
                }
                echo 'success';
            }else{
                print_r(K::$system->db->SQLLOG());
                K::$system->err->json();
            }
        }
    }

    public function home()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{
            $cfg = K::$system->config->get('attach');            
            $photos = $data['photos'];
            unset($data['photos']);
            if($face_pic = $this->download($data['thumb'])){
                $data['thumb'] = str_replace('\\', '/', $face_pic);
            }else{
                unset($data['thumb']);
            }
            if(!K::M('verify/check')->phone($data['tel']) && !K::M('verify/check')->mobile($data['tel'])){
                $data['phone'] = $siteCfg['phone'];
            }
            /*if($data['city'] && empty($data['city_id'])){
                if($city_list = K::M('data/city')->options()){
                    foreach($city_list as $k=>$v){
                        if($v == $data['city']){
                            $data['city_id'] = $k; break;
                        }
                    }
                }
            }
            if($data['area'] && empty($data['area_id']) && $data['city_id']){
                if($area_list = K::M('data/area')->options($data['city_id'])){
                    foreach($area_list as $k=>$v){
                        if(stripos($v,$data['area']) !== false){
                            $data['area_id'] = $k; break;
                        }
                    }
                }
            }
            unset($data['city'], $data['area']);*/
            if($home_id = K::M('home/home')->create($data, true)){
                $D = $cfg['attachdir'].'temp'.DIRECTORY_SEPARATOR;
                if($huxin = K::$system->gpc->p('huxin')){
                    $index = 0;
                    foreach(explode(';', $huxin) as $photo){
                        $index ++ ;
                        $title = $data['name'].$index;
                        if(preg_match('/^(.*):(http:\/\/.+\.(jpg|png|jpeg|gif))$/i', $photo, $a)){
                            $title = $a[1];
                            $photo = $a[2];
                        }
                        $file = $D.md5($photo);
                        if(preg_match('/^http:\/\/.+\.(jpg|png|jpeg|gif)$/i', $photo, $match)){
                            if($content = file_get_contents($photo)){                           
                                file_put_contents($file, $content);
                                $info = getimagesize($file);
                                $attach = array();
                                $attach['type'] = $info['mime'];
                                $attach['size'] = filesize($file);
                                $attach['tmp_name'] = $file; 
                                $attach['name'] = $data['title'].$index.'.'.$match[1];
                                $attach['error'] = '0';
                                K::M('home/photo')->upload($home_id,1, $attach);
                            }
                        }
                    }
                }
                if($shijing = K::$system->gpc->p('shijing')){
                    $index = 0;
                    foreach(explode(';', $shijing) as $photo){
                        $index ++ ;
                        $title = $data['name'].$index;
                        if(preg_match('/^(.*):(http:\/\/.+\.(jpg|png|jpeg|gif))$/i', $photo, $a)){
                            $title = $a[1];
                            $photo = $a[2];
                        }
                        $file = $D.md5($photo);
                        if(preg_match('/^http:\/\/.+\.(jpg|png|jpeg|gif)$/i', $photo, $match)){
                            if($content = file_get_contents($photo)){                           
                                file_put_contents($file, $content);
                                $info = getimagesize($file);
                                $attach = array();
                                $attach['type'] = $info['mime'];
                                $attach['size'] = filesize($file);
                                $attach['tmp_name'] = $file; 
                                $attach['name'] = $data['title'].$index.'.'.$match[1];
                                $attach['error'] = '0';
                                K::M('home/photo')->upload($home_id,2, $attach);
                            }
                        }
                    }
                }
                if($yangban = K::$system->gpc->p('yangban')){
                    foreach(explode(';', $yangban) as $photo){
                        $index ++ ;
                        $title = $data['name'].$index;
                        if(preg_match('/^(.*):(http:\/\/.+\.(jpg|png|jpeg|gif))$/i', $photo, $a)){
                            $title = $a[1];
                            $photo = $a[2];
                        }
                        $file = $D.md5($photo);
                        if(preg_match('/^http:\/\/.+\.(jpg|png|jpeg|gif)$/i', $photo, $match)){
                            if($content = file_get_contents($photo)){                           
                                file_put_contents($file, $content);
                                $info = getimagesize($file);
                                $attach = array();
                                $attach['type'] = $info['mime'];
                                $attach['size'] = filesize($file);
                                $attach['tmp_name'] = $file; 
                                $attach['name'] = $data['title'].$index.'.'.$match[1];
                                $attach['error'] = '0';
                                K::M('home/photo')->upload($home_id,3, $attach);
                            }
                        }
                    }
                }
                echo 'success';
            }else{
                K::$system->err->json();
            }
        }         
    }

    public function ask()
    {
        if(!$data = K::$system->gpc->p('data')){
            exit('data error');
        }else{            
            $data['uid'] = rand(1, 90);
            if(($cate_title = $data['cate']) && empty($data['cat_id'])){
                $cate_list = K::M('ask/cate')->fetch_all();
                foreach($cate_list as $v){
                    if((stripos($v['title'], $cate_title) !==false) || (stripos($cate_title, $v['title']) !== false)){
                        $data['cat_id'] = $v['cat_id']; break;
                    }
                }
            }
            if(empty($data['cat_id'])){
                $data['cat_id'] = 1;
            }
            $answer = $data['answer'];
            unset($data['answer'], $data['cate']);
            $data['audit'] = 1;
            $data['dateline'] = __TIME;
            $data['create_ip'] = '127.0.0.1';
            if($ask_id = K::M('ask/ask')->create($data)){
                $uid = $data['uid'] + rand(1,10);
                $a = array('uid'=>$uid, 'ask_id'=>$ask_id, 'contents'=>$answer, 'audit'=>1, 'dateline'=>__TIME, 'create_ip'=>'127.0.0.1');
                if($answer_id = K::M('ask/answer')->create($a)){
                    K::M('ask/ask')->update($ask_id, array('answer_id'=>$answer_id, 'answer_num'=>1));
                }
                echo 'success';
            }else{
                K::$system->err->json();
            }             
        }        
    }

    public function download($photo, &$file='')
    {
        $cfg = K::$system->config->get('attach');
        if(preg_match("/src=\"([\w\/]+)\.(jpg|jpeg|png|gif)\"/i", stripcslashes($photo), $match)){
            $photo = $match[1].'.'.$match[2];
        }
        if(preg_match('/^http:\/\/.+\.(jpg|png|jpeg|gif)$/i', $photo, $match)){
            if($ch = curl_init($photo)){
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $content = curl_exec($ch);
                if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200){
                    $B = realpath($cfg['attachdir']).DIRECTORY_SEPARATOR;
                    if(empty($file)){
                        $file = 'photo'.DIRECTORY_SEPARATOR.date('Ym').DIRECTORY_SEPARATOR.date('Ymd_').strtoupper(md5(microtime().PRI_KEY.rand())).".{$match[1]}";
                    }
                    $F = $B.$file;
                    K::M('io/dir')->create(dirname($F), 0777, true);
                    file_put_contents($F, $content);
                }
                return $file;
            }
            return false;
        }else if(preg_match('/^([\w\/]+)\.(jpg|png|jpeg|gif)$/', $photo)){
            return $photo;
        } 
        return false;      
    }
}