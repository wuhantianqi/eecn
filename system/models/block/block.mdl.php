<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: block.mdl.php 6092 2014-08-15 11:29:42Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Block_Block extends Mdl_Table
{   
  
    protected $_table = 'block';
    protected $_pk = 'block_id';
    protected $_cols = 'block_id,title,page_id,from,type,tmpl,limit,config,ttl,orderby,dateline';
    protected $_orderby = array('orderby'=>'ASC', 'block_id'=>'ASC');
    protected $_pre_cache_key = 'block-block-list';

    protected static $_allow_from = array('mechanic'=>'技工','gz'=>'工长', 'designer'=>'设计师','company'=>'公司', 'shop'=>'商家','youhui'=>'优惠信息','news'=>'公司新闻','product'=>'商品','coupon'=>'优惠券','home'=>'小区','tuan'=>'小区团装','site'=>'工地','case'=>'案例','diary'=>'日记','ask'=>'问答','article'=>'文章','activity'=>'活动');


    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        $data['dateline'] = __CFG::TIME;
        if($block_id = $this->db->insert($this->_table, $data, true)){
            $this->flush();
        }
        return $block_id;
    }

    public function update($block_id, $data, $checked=false)
    {
        if(!$block_id = (int)$block_id){
            return false;
        }else if(!$checked && !$data = $this->_check($data,  $block_id)){
            return false;
        } 
        if($ret = $this->db->update($this->_table, $data, $this->field($this->_pk, $block_id))){
            $this->flush();
        }
        return $ret;
    }

    public function _format_row($row)
    {
        static $page_list = null;
        static $type_list = array('default'=>'默认优先', 'new'=>'最新优先', 'hot'=>'最热有限', 'zhanwei'=>'占位补充', 'only'=>'只接受推荐');
        if($page_list === null){
            $page_list = K::M('block/page')->fetch_all();
        }
        if($page = $page_list[$row['page_id']]){
            $row['page_title'] = $page['title'];
        }
        if($ttl = K::M('data/data')->ttl($row['ttl'])){
            $row['ttl_title'] = $ttl;
        }
        $row['from_title'] = self::$_allow_from[$row['from']];
        $row['type_title'] = $type_list[$row['type']];
        $row['config'] = unserialize($row['config']);
        return $row;
    }

    public function items_by_page($page_id)
    {
        $blocks = array();
        if($items = $this->fetch_all()){
            if($from){
                foreach($items as $k=>$v){
                    if($v['page_id'] == $page_id){
                        $blocks[$k] = $v;
                    }
                }
            }else{
                $blocks = $items;
            }
        }
        return $blocks;        
    }

    public function items_by_from($from=null)
    {
        $blocks = array();
        if($items = $this->fetch_all()){
            if($from){
                foreach($items as $k=>$v){
                    if($v['from'] == $from){
                        $blocks[$k] = $v;
                    }
                }
            }else{
                $blocks = $items;
            }
        }
        return $blocks;
    }

    public function block_by_id($id)
    {
        if($items = $this->fetch_all()){
            return $items[$id];
        }
        return false;        
    }

    public function block_by_name($name)
    {
        if($items = $this->fetch_all()){
            foreach($items as $v){
                if($v['title'] == $name){
                    return $v;
                }
            }
        }
        return false;
    }

    public function from_list()
    {
        return self::$_allow_from;
    }

    public function load_mdl($from)
    {
        static $_allow_mdl = array(
            'designer'=>'designer/designer','gz'=>'gz/gz',
            'mechanic'=>'mechanic/mechanic','company'=>'company/company',
            'shop'=>'shop/shop', 'product'=>'product/product', 'coupon'=>'shop/coupon',
            'home'=>'home/home', 'site'=>'home/site', 'case'=>'case/case', 'tuan'=>'home/tuan',
            'youhui'=>'company/youhui', 'news'=>'company/news', 'activity'=>'activity/activity',
            'article'=>'article/article','diary'=>'diary/diary','ask'=>'ask/ask');
        if($mdl = $_allow_mdl[$from]){
            return K::M($mdl);
        }
        return false;
    }

    public function format_item($row, $from='product')
    {
        static $oLink = null;
        static $site = null;
        static $attach = null;
        static $attachurl = null;
        if($oLink === null){
            $oLink = K::M('helper/link');
            $site = K::$system->config->get('site');
            $attach = K::$system->config->get('attach');
            $attachurl = $attach['attachurl'];
        }
        if(in_array($from, array('mechanic', 'gz', 'designer'))){
            $row['itemId'] = $row['uid'];
            $row['title'] = $row['name'];
            $row['thumb'] = $row['face'];
        }else if('article' == $from){
            $row['itemId'] = $row['article_id'];
        }else if('home' == $from){
            $row['itemId'] = $row['home_id'];
            $row['title'] = $row['name'];
        }else if('huxing' == $from){
            $row['itemId'] = $row['photo_id'];
            $row['thumb'] = $row['photo'];
        }else if('case' == $from){
            $row['itemId'] = $row['case_id'];
            $row['thumb'] = $row['photo'];
        }else if('company' == $from){
            $row['itemId'] = $row['company_id'];
            $row['title'] = $row['name'];
            $row['thumb'] = $row['thumb'];            
        }else if('shop' == $from){
            $row['itemId'] = $row['shop_id'];
            $row['thumb'] = $row['thumb'];
        }else if('product' == $from){
            $row['itemId'] = $row['product_id'];
            $row['thumb'] = $row['photo'].'_thumb.jpg';
        }else if('coupon' == $from){
            $row['itemId'] = $row['coupon_id'];
            $row['thumb'] = $row['photo'];
        }else if('tuan' == $from){
            $row['itemId'] = $row['tuan_id'];
            //$row['thumb'] = $row['thumb'];
        }
        //$link todo
        //$row['link'] = '';

        return $row;
    }

    protected function _check($data, $block_id=null)
    {
        unset($data['block_id'], $data['dateline']);
        if(!$block_id || isset($data['title'])){
            if(empty($data['title'])){
                $this->err->add('推荐位名称不能为空', 401);
                return false;
            }else if($block = $this->block_by_name($data['title'])){
                if(!$block_id || ($block['block_id'] != $block_id)){
                    $this->err->add('该推荐位称已经存在', 402);
                    return false;
                }
            }
        }
        if(!$block_id || isset($data['from'])){
            if(!self::$_allow_from[$data['from']]){
                $data['from'] = 'data';
            }          
        }
        if(isset($data['ttl'])){
            $data['ttl'] = (int)$data['ttl'];
        }        
        if(isset($data['orderby'])){
            $data['orderby'] = (int)$data['orderby'];
        }
        if(isset($data['config']) && is_array($data['config'])){
            if(isset($data['config']['group_id']) && is_array($data['config']['group_id'])){
                $gids = array();
                foreach($data['config']['group_id'] as $v){
                    if($v = (int)$v){
                        $gids[] = $v;
                    }
                }
                $data['config']['group_id'] = $gids;
            }
            if(isset($data['config']['cat_id']) && is_array($data['config']['cat_id'])){
                $cat_ids = array();
                foreach($data['config']['cat_id'] as $v){
                    if($v = (int)$v){
                        $cat_ids[] = $v;
                    }
                }
                $data['config']['cat_id'] = $cat_ids;
            }            
            $data['config'] = serialize($data['config']);
        }
        return parent::_check($data);
    }

    public function block_city_items($block, $city_id=0, $limit=null)
    {
        $block_id = $block['block_id'];
        $limit = (int)$limit ? (int)$limit : $block['limit'];
        $cache_key =$this->_pre_cache_key."-items-{$block_id}-{$city_id}-{$limit}";
        if(!$block_items = $this->cache->get($cache_key, $block['ttl'])){
            $block_items = array();
            if(!$mdl = $this->load_mdl($block['from'])){
                return false;
            }
            if($items = K::M('block/item')->items(array('block_id'=>$block_id, 'city_id'=>$city_id), null, 1, $limit)){
                $iids = array();
                $time = __CFG::TIME - 86400;
                $has_expire = false;
                $count = 0;                
                foreach($items as $k=>$v){
                    if(empty($v['expire_time']) || $v['expire_time'] > $time){
                        if(empty($v['link'])){
                            $v['link'] = $this->_format_link($v, $block);
                        }
                        if($city_id){
                            if($city_id == $v['city_id']){
                                $iids[$v['itemId']] = $v['itemId'];
                                $block_items[$v['itemId']] = $v;
                                if( ++$count >= $limit){
                                    break;
                                }
                            }
                        }else{
                            $iids[$v['itemId']] = $v['itemId'];
                            $block_items[$v['itemId']] = $v;
                            if( ++$count >= $limit){
                                break;
                            }                           
                        }              
                    }else{
                        $has_expire = true;
                    }
                }
                $_tmp_items = array();
                if($iids){                    
                    if($item_list = $mdl->items_by_ids($iids)){
                        $del_ids = array();
                        foreach($block_items as $k=>$v){
                            if($a = $item_list[$v['itemId']]){
                                $v = array_merge($a, $v);
                                if(empty($v['link'])){
                                    $v['link'] = $this->_format_link($v, $block);
                                } 
                                $_tmp_items[$v['itemId']] = $v;
                            }else{
                                $del_ids[$v['itemId']] = $v['itemId'];
                            }                            
                        }
                        if($del_ids){
                            K::M('block/item')->delete_block_item($block_id, $del_ids);
                        }
                    }
                }
                $block_items = $_tmp_items;
                if($has_expire){
                    K::M('block/item')->update_expire();
                }
            }
            $count = count($block_items);
            if($block['type'] == 'zhanwei' && $limit > $count){
                $zhanwei = $block['config']['zhanwei'];
                if(empty($zhanwei['link'])){
                    $zhanwei['link'] = 'javascript:;';
                }
                $zhanwei['zhanwei'] = 'zhanwei';
                $zhanwei['logo'] = $zhanwei['thumb'];
                $zhanwei['name'] = $zhanwei['title'];
                for(; $count<$limit; $count++){
                    $block_items['zhanwei_'.$count] = $zhanwei;
                }
            }else if($block['type'] != 'only' && $limit > $count){
                $filter = $city_id ? array('city_id'=>$city_id) : array();
                switch (strtolower($block['order'])) {
                    case 'hot':
                        $mothed = 'items_by_hot'; break;
                    case 'new':
                        $mothed = 'items_by_new'; break;
                    default:
                        $mothed = 'items'; 
                        $filter['closed'] = 0;
                        $filter['audit'] = 1;
                        break;
                }                
                if($block['from'] == 'article'){
                    $filter['from'] = empty($filter['from']) ? 'article' : $filter['from'];
                    if($cat_ids = K::M('verify/check')->ids($block['config']['cat_id'])){
                        if(is_numeric($cat_ids)){
                            $filter['cat_id'] = $cat_ids;
                        }else{
                            $filter['cat_id'] = explode(',', $cat_ids);
                        }                        
                    }
                }else if(in_array($block['from'], array('company', 'gz', 'designer', 'mechanic', 'shop'))){
                    if($group_ids = K::M('verify/check')->ids($block['config']['group_id'])){
                        if(is_numeric($group_ids)){
                            $filter['group_id'] = $group_ids;
                        }else{
                            $filter['group_id'] = explode(',', $group_ids);
                        }
                    }
                    if($score = (int)$block['config']['score']){
                        $filter['score'] = '>:'.$filter['score'];
                    }
                    if($block['from'] == 'company' || $block['from'] == 'shop'){
                        if($block['config']['xiaobao']){
                            $filter['xiaobao'] = '>:0';
                        }
                        if($block['config']['verify_name']){
                            $filter['verify_name'] = 1;
                        }
                        if($block['config']['is_vip']){
                            $filter['is_vip'] = 1;
                        }                                                
                    }
                }else if($block['from'] == 'product'){
                    if($cat_ids = K::M('verify/check')->ids($block['config']['cat_id'])){
                        if(is_numeric($cat_ids)){
                            $filter['cat_id'] = $cat_ids;
                        }else{
                            $filter['cat_id'] = explode(',', $cat_ids);
                        }
                    }
                    if($block['config']['onpayment']){
                        $filter['onpayment'] = 1;
                    }                    
                    if($block['config']['sale_remai']){
                        $filter['sale_remai'] = 1;
                    }
                    if($block['config']['sale_tuijian']){
                        $filter['sale_tuijian'] = 1;
                    }
                    if($block['config']['sale_youhui']){
                        $filter['sale_youhui'] = 1;
                    }
                }
                if($city_id  && in_array($block['from'], array('article', 'case'))){
                    $filter['city_id'] = array('0', $city_id);
                }
                if($bfilter = $this->_block_filter($block)){
                    unset($bfilter['group_id'], $bfilter['cat_id'], $bfilter['sale_tuijian'], $bfilter['sale_remai'], $bfilter['sale_youhui'], $bfilter['onpayment'], $bfilter['score']);
                    unset($bfilter['xiaobao'], $bfilter['verify_name'], $bfilter['is_vip']);
                    $filter = array_merge($filter, $bfilter);
                }
                if($mothed == 'items'){
                    $orderby = $this->_parse_orderby($block['type']);
                    $item_list = $mdl->items($filter, $orderby, 1, $limit);
                }else{
                    $item_list = $mdl->$mothed($filter, $limit);
                }
                if($item_list){
                    foreach($item_list as $k=>$v){
                        if($block_items[$k]){
                            continue;
                        }else if($count >= $limit){
                            break;
                        }                      
                        $v = $this->format_item($v, $block['from']);
                        $v['link'] = $this->_format_link($v, $block);
                        $block_items[$k] = $v;
                        $count ++ ;
                    }
                }
            }
            $block_items = $mdl->format_items_ext($block_items);
            $this->cache->set($cache_key, $block_items, $block['ttl']);
        }
        return $block_items;
    }

    public function block($params, $tmpl, $smarty)
    {	
        if($block_id = $params['id']){
            if(!$block = $this->block_by_id($block_id)){
                return false;
            }
        }else if($block_name = $params['name']){
            if(!$block = $this->block_by_name($block_name)){
                return false;
            }
        }else{
            return false;
        }
		$nums = intval($params['limit']);
        $order = strtolower($params['order']);
        $order = in_array($order,array('asc','desc','rand')) ? $order : "asc";
        $city_id = (int)$params['city_id'];
        $limit = $params['limit'] ? (int)$params['limit'] : $block['limit'];
		if($items = $this->block_city_items($block, $city_id, $limit)){
            $content = '';
            $index = 0;
            $iteration = 1;
            $count = count($items);
            $data = $smarty->tpl_vars;
            if(empty($tmpl)){
				$tmpl = $block['tmpl'];
				$tmpl = str_replace(array('[loop]', '[/loop]'), array('<{foreach $items as $item}><{assign var="first" value=$item@first}><{assign var="iteration" value=$item@iteration}>', '<{/foreach}>'), $tmpl);
				$smarty->assign('block', $block);
                $smarty->assign('items', $items);
				$smarty->assign('count', $count);
                $smarty->assign('limit', $limit);
				$content = $smarty->fetch("string:{$tmpl}");				
			}else{
                $smarty->assign('count', $count);
                $smarty->assign('limit', $limit);
                $smarty->assign('first', true);
                foreach($items as $item){
                    $smarty->assign('index', $index++);
                    $smarty->assign('iteration', $iteration++);
                    if($count>$index){
                        $smarty->assign('last', false);
                    }else{
                       $smarty->assign('last', true); 
                    }
                    $smarty->assign('item', $item);
                    $content .= $smarty->fetch("string:{$tmpl}");
                    $smarty->assign('first', false);
                }                
            }
            $smarty->tpl_vars = $data;
            return $content;
        }
        return false;
    }

    public function calldata($params, $content, $smarty)
    {
        if($model = $params['mdl']){
            if(!$mdl = K::M($model)){
                return false;
            }
        }else if($from = $params['from']){
            if(!$mdl = $this->load_mdl($from)){
                return false;
            }
        }else{
            return false;
        }
        $hash = $params['hash'] ? $params['hash'] : md5(var_export($params, true));
        $ttl = $params['ttl'] ? $params['ttl'] : 3600;
        $nocache = isset($params['nocache']) ? $params['nocache'] : ($ttl < 0 ? true : false);
        $noext = isset($params['noext']) ? $params['noext'] : false;
        if(!$nocache && !$items = $this->cache->get($hash)){
            $limit = $params['limit'] ? $params['limit'] : 10;
            $filter = $params;
            unset($filter['mdl'], $filter['order'], $filter['limit'], $filter['hash'], $filter['ttl'], $filter['nocache']);
            if($params['mdl'] == 'article/article'){
                $filter['from'] = empty($filter['from']) ? 'article' : $filter['from'];
                if($cat_id = (int)$filter['cat_id']){
                    if($cat_ids = K::M('article/cate')->children_ids($cat_id)){
                        $filter['cat_id'] = explode(',', $cat_ids);
                    }
                }                
            }else{
                unset($filter['from']);
            }
            $items = array();
            if('hot' == $params['order']){
                $items = $mdl->items_by_hot($filter, $limit);
            }else if('new' == $params['order']){
                $items = $mdl->items_by_new($filter, $limit);
            }else{
                $filter['closed'] = 0;
                $filter['audit'] = 1;
                $order = $this->_parse_orderby($params['order']);
                $items = $mdl->items($filter, $order, 1, $limit);
            }
            if(empty($noext)){
                $items = $mdl->format_items_ext($items);
            }
            if(empty($nocache)){
                $this->cache->set($hash, $items, $ttl);
            }
        }
        if($items){
            $data = '';
            $index = 0;
            $iteration = 1;
            $count = count($items);
            $smarty->assign('calldata_count', $count);
            $smarty->assign('count', $count);
            $smarty->assign('limit', $limit);
            $smarty->assign('first', true);
            foreach($items as $item){
                $smarty->assign('index', $index++);
                $smarty->assign('iteration', $iteration++);
                if($count>$index){
                    $smarty->assign('last', false);
                }else{
                   $smarty->assign('last', true); 
                }
                $smarty->assign('item', $item);
                $data .= $smarty->fetch("string:{$content}");
                $smarty->assign('first', false);
            }
            return $data;
        }
        return false;
    }

    protected function _block_filter($block)
    {
        $filter = $block['config'];
        if(in_array($block['from'], array('company','designer','shop','gz'))){
            if($score = (int)$filter['score']){
                $filter['score'] = '>=:'.$score;
            }else{
                unset($filter['score']);
            }
        }
        return $filter;
    }

    protected function _format_link($row, $block)
    {
        static $oLink = null;
        static $site = null;
        static $attach = null;
        static $attachurl = null;
        if($oLink === null){
            $oLink = K::M('helper/link');
            $site = K::$system->config->get('site');
        }
        $http = true;
        if($city_id = (int)$row['city_id']){
            $http = "city_id:{$row[city_id]}";
        }
		
        switch ($block['from']) {
            case 'designer':
                $link = $oLink->mklink('blog', array($row['itemId']), array(), $http);
                break;
            case 'gz':
                $link = $oLink->mklink('gz:detail', array($row['itemId']), array(), $http);
                break;
            case 'mechanic':
                $link = $oLink->mklink('mechanic:detail', array($row['itemId']), array(), $http);
                break;    
            case 'home':
                $link = $oLink->mklink('home:detail', array($row['itemId']), array(), $http);
                break;
            case 'tuan':
                $link = $oLink->mklink('home:tuanDetail', array($row['itemId']), array(), $http);
                break;
            case 'site':
                $link = $oLink->mklink('site:detail', array($row['itemId']), array(), $http);
                break;    
            case 'case':
                $link = $oLink->mklink('case:detail', array($row['itemId']), array(), $http);
                break;
            case 'company': 
                $link = $row['company_url'];
                break;
            case 'news':
                $link = $oLink->mklink('news:detail', array($row['itemId']), array(), $http);
                break;
            case 'youhui':
                $link = $oLink->mklink('youhui:detail', array($row['itemId']), array(), $http);
                break;
            case 'shop':
                $link = $row['shop_url'];
                break;
            case 'coupon':
                $link = $oLink->mklink('mall/coupon:detail', array($row['itemId']), array(), $http);
                break;
            case 'product':
                $link = $oLink->mklink('mall/product:detail', array($row['itemId']), array(), $http);
                break;          
            case 'activity':
                $link = $oLink->mklink('activity:detail', array($row['itemId']), array(), $http);
                break;
            case 'article':
                if($row['linkurl']){
                    $link = $row['linkurl'];
                }else{
                    $link = $oLink->mklink('article:detail', array($row['itemId']), array(), $http);
                }
                break;
            case 'diary':
                $link = $oLink->mklink('diary:detail', array($row['itemId']), array(), $http);
                break;
            case 'ask':
                $link = $oLink->mklink('ask:detail', array($row['itemId']), array(), $http);
                break;
        }
        return $link;
    }

    protected function _parse_orderby($order=null)
    {
        $orderby = array();
        if(is_array($order)){
            return $order;
        }else if($order && is_string($order)){
            foreach(explode(',', $order) as $v){
                if(strpos($v, ':')){
                    if(list($key, $val) = explode(':', $v)){
                        $val = strtoupper($val);
                        if(in_array($val, array('ASC', 'DESC'))){
                            $orderby[$key] = $val;
                        }
                    }
                }
            }
        }
        return $orderby;      
    }

}