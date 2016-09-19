<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 5454 2014-06-11 05:17:35Z $
 */

class Widget_Public extends Model
{

    public function help(&$params)
    {   
        $data['cate_list']      = K::M('article/cate')->fetch_all();
        $data['content_list']   = K::M('article/article')->items(array('from'=>'help','closed'=>0),array('article_id'=>'ASC'),1,50);
        $params['tpl'] = $params['tpl'] ? $params['tpl']: 'help.html';
        return $data;
    }

    public function kefu(&$params)
    {           
        $params['tpl'] =  $params['tpl'] ? $params['tpl'] : 'kefu.html';
        return true;
    }

    public function share(&$params)
    {
        $params['tpl'] =  $params['tpl'] ? $params['tpl'] : 'share.html';
        return $params;     
    }

    public function sobox(&$params)
    {
        $params['tpl'] =  $params['tpl'] ? $params['tpl'] : 'sobox.html';
        $request = K::$system->request;
        $all_sotype = array('gs'=>array('ctl'=>'gs:items', 'title'=>'公司'), 'mall/store'=>array('ctl'=>'mall/store','title'=>'商铺'), 'product'=>array('ctl'=>'mall/product','title'=>'商品'), 'designer'=>array('ctl'=>'designer:items','title'=>'设计师'), 'mechanic'=>array('ctl'=>'mechanic:items','title'=>'技工'), 'case'=>array('ctl'=>'case:album', 'title'=>'案例'), 'home'=>array('ctl'=>'home:items','title'=>'小区'), 'site'=>array('ctl'=>'home:items','title'=>'工地'), 'home:tuan'=>array('ctl'=>'home:tuan','title'=>'团装'), 'article'=>array('ctl'=>'article:items','title'=>'学装修'));
        
        if($a = $sotype[$request['ctl'].':'.$request['act']]){            
        }else if($request['ctl'] == 'mall/store'){
            $a = array('ctl'=>'mall/store','title'=>'商铺');
        }else if(strpos($request['ctl'], 'mall') !== false){
            $a = array('ctl'=>'mall/product','title'=>'商品');
        }else if(strpos($request['ctl'], 'article') !== false){
            $a = array('ctl'=>'article:items','title'=>'学装修');
        }else if(strpos($request['ctl'], 'case') !== false){
            $a = array('ctl'=>'case:aublm','title'=>'案例');
        }else if(!$a = $sotype[$request['ctl']]){
            $a = array('ctl'=>'gs:items','title'=>'公司');
        }
        $data['all_sotype'] = $all_sotype;
        $data['sotype'] = $a;
        return $data;
    }
}