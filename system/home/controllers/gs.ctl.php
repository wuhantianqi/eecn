<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Gs extends Ctl
{
    public function index()
    {
        $this->items($page);
    }

	public function items($page = 1)
	{
        $pager = $filter = $attrs = $attr_ids = $attr_vids = $attr_value_ids = $attr_value_titles = array();
        $area_id = $group_id = $order = 0;
        $attr_values = K::M('data/attr')->attrs_by_from('zx:company', true);
        $uri = $this->request['uri'];
        if(preg_match('/items(-[\d\-]+)?(-(\d+)).html/i', $uri, $m)){
            $page = (int)$m[3];
            if($m[1]){
                $attr_vids = explode('-', trim($m[1], '-'));
                $area_id = $attr_vids ? array_shift($attr_vids) : 0;
                $group_id = $attr_vids ? array_shift($attr_vids) : 0;
                $order = $attr_vids ? array_pop($attr_vids) : 0;
            }
        }
        foreach($attr_values as $k=>$v){
            if($v['filter']){
                $attr_value_ids[$k] = 0;
                foreach($attr_vids as $vv){
                    if($v['values'][$vv]){
                        $attr_value_ids[$k] = $vv;
                        $attr_ids[$k] = $vv;
                        $attrs[$k] = $v['values'][$vv];
                        $attr_value_titles[$k] = $v['values'][$vv]['title'];
                    }
                }
            }
        }        
        $attr_vids = $attr_ids;    
        foreach($attr_values as $k=>$v){
            $vids = $attr_value_ids;
            $vids[$k] = 0;
            $vids['order'] = $order;
            $vids['page'] = 1;            
            $v['link'] = $this->mklink('gs:items', array($area_id, $group_id, implode('-', $vids)));

            $v['checked'] = true;
            foreach($v['values'] as $kk=>$vv){
                $vv['checked'] = false;
                if(in_array($kk, $attr_ids)){
                    $v['checked'] = false;
                    $vv['checked'] = true;
                }                
                $vids[$k] = $kk;
                $vv['link'] = $this->mklink('gs:items', array($area_id, $group_id, implode('-', $vids)));
                $v['values'][$kk] = $vv;

            }
            $attr_values[$k] = $v;
        }
        if($group_list = K::M('member/group')->items_by_from('company')){
            $group_all_link = $this->mklink('gs:items', array($area_id, 0, implode('-', $attr_value_ids), $order, 1));
            foreach($group_list as $k=>$v){
                $v['link'] = $this->mklink('gs:items', array($area_id, $k, implode('-', $attr_value_ids), $order, 1));
                $group_list[$k] = $v;
            }
        }
        $area_list = $this->request['area_list'];
        $area_all_link = $this->mklink('gs:items', array(0, $group_id, implode('-', $attr_value_ids), $order, 1));
        foreach ($area_list as $k=>$v) {
            $v['link'] = $this->mklink('gs:items', array($k, $group_id, implode('-', $attr_value_ids), $order, 1));
            $area_list[$k] = $v;
        }
        define('__SYS_DEBUG', 1);
        $order_list = array(0=>array('title'=>'默认'), 1=>array('title'=>'签单'), 2=>array('title'=>'口碑'));
        $order_list[0]['link'] = $this->mklink('gs:items', array($area_id, $group_id, implode('-', $attr_value_ids), 0, 1));
        $order_list[1]['link'] = $this->mklink('gs:items', array($area_id, $group_id, implode('-', $attr_value_ids), 1, 1));
        $order_list[2]['link'] = $this->mklink('gs:items', array($area_id, $group_id, implode('-', $attr_value_ids), 2, 1));
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['area_id'] = $area_id;
        $pager['group_id'] = $group_id;
        $pager['order'] = $order;
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter['city_id'] = $this->request['city_id'];
        if($area_id){
            $filter['area_id'] = $area_id;
        }
        if($group_id){
            $filter['group_id'] = $group_id;
        }
        $filter['closed'] = 0;
        $filter['audit'] = 1;
        if($attr_ids){
            $filter['attrs'] = $attr_ids;
        }
        if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $params['kw'] = $kw;
            $filter['title'] = "LIKE:%{$kw}%";            
        }
        if($order == 1){
            $orderby = array('tenders_num'=>'DESC');
        }else if($order == 2){
            $orderby = array('score'=>'DESC');
        }else{
            $orderby = NULL;
        }
        if ($items = K::M('company/company')->items($filter, $orderby, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('gs:items', array($area_id, $group_id, implode('-', $attr_value_ids), $order, '{page}'), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['attr_values'] = $attr_values;
        $this->pagedata['area_list'] = $area_list;
        $this->pagedata['group_list'] = $group_list;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['area_all_link'] = $area_all_link;
        $this->pagedata['group_all_link'] = $group_all_link;
        $this->pagedata['pager'] = $pager;
        $seo = array('area_name'=>'', 'attr'=>'', 'page'=>'');
        if($area_id){
            $seo['area_name'] = $area_list[$area_id]['area_name'];
        }
        if($attr_value_titles){
            $seo['attr'] = implode('_', $attr_value_titles);
        }
        if($page > 1){
            $seo['page'] = $page;
        }    
        $this->seo->init('gs_items', $seo);
        $this->tmpl = 'gs/items.html';
		
	}

	public function map()
	{
		$area_list = K::M('data/area')->areas_by_city($this->request['city_id']);
		$attr_values = K::M('data/attr')->attrs_by_from('zx:company');
		$this->pagedata['area_list'] = $area_list;
		$this->pagedata['attr_values'] = $attr_values;
		K::M('helper/seo')->init('gs_maps', array());
        $this->tmpl = 'gs/maps.html';
	}

	public function result()
    {
        $SO = $this->GP('SO');
        if (!empty($SO['lng_start']) && !empty($SO['lng_end']) && !empty($SO['lat_start']) && !empty($SO['lat_end'])) {
            if (is_numeric($SO['lng_start']) && is_numeric($SO['lng_end']) && is_numeric($SO['lat_start']) && is_numeric($SO['lat_end'])) {
                $filter['lng'] = $SO['lng_start'] . '~' . $SO['lng_end'];
                $filter['lat'] = $SO['lat_start'] . '~' . $SO['lat_end'];
				if($SO['name']){
					$filter['name'] = "LIKE:%".$SO['name']."%";
				}
				if($SO['area_id']){
					$filter['area_id'] = $SO['area_id'];
				}
				if($SO['attr1']){
					$filter['attrs'][0] = $SO['attr1'];
				}
				if($SO['attr2']){
					$filter['attrs'][1] = $SO['attr2'];
				}
                $filter['closed'] = 0;
                $items = K::M('company/company')->items($filter, null, 1, 100, $count);
                $data = array();
                foreach ($items as $val) {
                    $data[$val['company_id']] = array(
                        'company_id' => $val['company_id'],
                        'link' => $val['company_url'],
                        'name' => $val['title'],
                        'thumb' => $val['thumb'],
                        'contact' => $val['contact'],
						'phone' => $val['show_phone'],
                        'lng' => $val['lng'],
                        'lat' => $val['lat'],
                        'addr' => $val['addr'],
                    );
                }				
                $this->err->set_data('total', $count);
                $this->err->set_data('result', $data);
            }
        }
        $this->err->json();
        die;
    }

	 public function yuyue($company_id)
     {
		if(!($company_id = (int) $company_id) && !($company_id = (int)$this->GP('company_id'))) {
            $this->error(404);;
        } else if (!$detail = K::M('company/company')->detail($company_id)) {
            $this->err->add('装修公司不存在或已经删除', 212);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else{
            if($this->checksubmit('data')){
                if(!$data = $this->GP('data')){
                    $this->err->add('非法的数据提交', 201);
                }else{
					$verifycode_success = true;
					$access = $this->system->config->get('access');
					if($access['verifycode']['yuyue']){
						if(!$verifycode = $this->GP('verifycode')){
							$verifycode_success = false;
							$this->err->add('验证码不正确', 212);
						}else if(!K::M('magic/verify')->check($verifycode)){
							$verifycode_success = false;
							$this->err->add('验证码不正确', 212);
						}
					}
					if($verifycode_success){
						$data['uid'] = (int)$this->uid;
						$data['company_id'] = $company_id;
						$data['content'] = "预约装修";
						$data['city_id'] = $this->request['city_id'];
						if($yuyue_id = K::M('company/yuyue')->create($data)){
                            K::M('company/yuyue')->yuyue_count($company_id);
							$this->err->add('预约装修公司成功！');
						}
					}
                } 
            }else{
				$access = $this->system->config->get('access');
				$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
                $this->pagedata['company_id'] = $company_id;
				$this->pagedata['detail'] = $detail;
                $this->tmpl = 'gs/yuyue.html';
            }            
        }
    }
}