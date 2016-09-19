<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Home extends Ctl
{
    
    public function index()
    {
        $this->items();
    }

    public function items()
    {
        $pager = $filter = $attrs = $attr_ids = $attr_vids = $attr_value_ids = $attr_value_titles = array();
        $area_id = $order = 0;
        $attr_values = K::M('data/attr')->attrs_by_from('zx:home', true);
        $uri = $this->request['uri'];
        if(preg_match('/items(-[\d\-]+)?(-(\d+)).html/i', $uri, $m)){
            $page = (int)$m[3];
            if($m[1]){
                $attr_vids = explode('-', trim($m[1], '-'));
                $area_id = $attr_vids ? array_shift($attr_vids) : 0;
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
            $v['link'] = $this->mklink('home:items', array($area_id, implode('-', $vids)));
            $v['checked'] = true;
            foreach($v['values'] as $kk=>$vv){
                $vv['checked'] = false;
                if(in_array($kk, $attr_ids)){
                    $v['checked'] = false;
                    $vv['checked'] = true;
                }                
                $vids[$k] = $kk;
                $vv['link'] = $this->mklink('home:items', array($area_id, implode('-', $vids)));
                $v['values'][$kk] = $vv;

            }
            $attr_values[$k] = $v;
        }
        $area_list = $this->request['area_list'];
        $area_all_link = $this->mklink('home:items', array(0, implode('-', $attr_value_ids), $order, 1));
        foreach ($area_list as $k=>$v) {
            $v['link'] = $this->mklink('home:items', array($k, implode('-', $attr_value_ids), $order, 1));
            $area_list[$k] = $v;
        }
        $order_list = array(0=>array('title'=>'默认'), 1=>array('title'=>'价格'), 2=>array('title'=>'方案'));
        $order_list[0]['link'] = $this->mklink('home:items', array($area_id, implode('-', $attr_value_ids), 0, 1));
        $order_list[1]['link'] = $this->mklink('home:items', array($area_id, implode('-', $attr_value_ids), 1, 1));
        $order_list[2]['link'] = $this->mklink('home:items', array($area_id, implode('-', $attr_value_ids), 2, 1));

        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['area_id'] = $area_id;
        $pager['order'] = $order;
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        $filter['city_id'] = $this->request['city_id'];
        if($area_id){
            $filter['area_id'] = $area_id;
        }
        $filter['closed'] = 0;
        $filter['audit'] = 1;
        if($attr_ids){
            $filter['attrs'] = $attr_ids;
        }
        if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $params['kw'] = $kw;
            $filter[':OR'] = array('title'=>'LIKE:%'.$kw.'%', 'name'=>'LIKE:%'.$kw.'%');
        }
        if($order == 1){
            $orderby = array('price'=>'DESC');
        }else if($order == 2){
            $orderby = array('case_num'=>'DESC');
        }else{
            $orderby = NULL;
        }
        if ($items = K::M('home/home')->items($filter, $orderby, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('home:items', array($area_id, implode('-', $attr_value_ids), $order, '{page}'), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['attr_values'] = $attr_values;
        $this->pagedata['area_list'] = $area_list;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['area_all_link'] = $area_all_link;
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
        $this->seo->init('home_items', $seo);
        $this->tmpl = 'home/items.html';
    }

    public function detail($home_id)
    {
        $home = $this->check_home($home_id);
        K::M('home/home')->update_count($home_id, 'views', 1);   
        $this->tmpl = 'home/detail.html';
    }

    public function cases($home_id, $page=1)
    {
        $home = $this->check_home($home_id);
        $filter = $pager = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 5;
        $filter['home_id'] = $home_id;
        $filter['closed'] = '0';
        $filter['audit'] = 1;
        if($items = K::M('case/case')->items($filter, NULL, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('home:cases',array($home_id, '{page}')));
			$company_ids = $uids = array();                
            foreach ($items as $val) {
                $uids[$val['uid']] = $val['uid'];
                if (!empty($val['company_id'])) {
                    $company_ids[$val['company_id']] = $val['company_id'];
                }
            }
            if (!empty($company_ids)){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
            }
			if($member_list = K::M('member/member')->items_by_ids($uids)){
                $designer_ids = $gz_ids = array();
                foreach($member_list as $v){
                    if($v['from'] == 'designer'){
                        $designer_ids[$v['uid']] = $v['uid'];
                    }
                    if($v['from'] == 'gz'){
                        $gz_ids[$v['uid']] = $v['uid'];
                    }
                }
                if($designer_ids){
                    $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
                }
                if($gz_ids){
                    $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
                }                
            }
        }
        $this->pagedata['pager'] = $pager;
        $this->pagedata['items'] = $items;
        $this->tmpl = 'home/cases.html';
    
    }

    public function info($home_id)
    {
       $home = $this->check_home($home_id);
       $this->tmpl = 'home/info.html';
    }

    public function photo($home_id,$type, $page)
    {
        $home = $this->check_home($home_id);
        $pager = $filter = array();
        $filter['home_id'] = $home_id;
        $filter['type'] = $type;
        $pager['page'] = $page  = max((int)$page, 1);
        $pager['limit'] = $limit = 9;
		$pager['count'] = $count = 0;
		if($items = K::M('home/photo')->items($filter, NULL, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('home:photo',array($home_id, $type, '{page}')));
        }
		$this->pagedata['type'] = $type;
		$this->pagedata['pager'] = $pager;
		$this->pagedata['items'] = $items;
		$this->tmpl = 'home/photo.html';
        
    }

    public function site($home_id, $page=1)
    {
		$home = $this->check_home($home_id);
        $pager = $filter = array();
		$filter['home_id'] = $home_id;
		$filter['audit'] = 1;
		$pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 5;
		$pager['count'] = $count = 0;
		if($items = K::M('home/site')->items($filter, NULL, $page, $limit, $count)){                
			$pager['count'] = $count;
			$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('home:site',array($home_id, '{page}')));
            $company_ids = $uids = array();
			foreach ($items as $val) {
                $uids[$val['uid']] = $val['uid'];
				if (!empty($val['company_id'])) {
					$company_ids[$val['company_id']] = $val['company_id'];
				}
			}
			
			if (!empty($company_ids)){
				$this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
			}
			
            if($member_list = K::M('member/member')->items_by_ids($uids)){
                $designer_ids = $gz_ids = array();
                foreach($member_list as $v){
                    if($v['from'] == 'designer'){
                        $designer_ids[$v['uid']] = $v['uid'];
                    }
                    if($v['from'] == 'gz'){
                        $gz_ids[$v['uid']] = $v['uid'];
                    }
                }
                if($designer_ids){
                    $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
                }
                if($gz_ids){
                    $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
                }                
            }
		}
		$this->pagedata['status'] =K::M('home/site')->get_status();
		$this->pagedata['pager'] = $pager;
		$this->pagedata['items'] = $items;
		$this->tmpl = 'home/site.html';
    }

    public function caseDetail($home_id, $case_id, $page=1)
    {
		$home = $this->check_home($home_id);
        if(!$case_id = (int)$case_id){
            $this->error(404);
        }else if(!$case = K::M('case/case')->detail($case_id)){
            $this->error(404);
        }else{
            $this->pagedata['case'] = $case;
            if($case['company_id']){          
                $company = K::M('company/company')->detail($case['company_id']);
				$company['group'] = K::M('member/group')->check_priv($company['group_id'],'allow_yuyue');
				$this->pagedata['company'] = $company;
            }else if($case['uid']){
                if($member = K::M('member/member')->member($case['uid'])){
                    if($member['from'] == 'gz'){
                        $gz = K::M('gz/gz')->detail($case['uid']);
						$gz['group'] = K::M('member/group')->check_priv($gz['group_id'],'allow_yuyue');
						$this->pagedata['gz'] = $gz;
                    }else if($member['from'] == 'designer'){
                        $designer = K::M('designer/designer')->detail($case['uid']);
						$designer['group'] = K::M('member/group')->check_priv($designer['group_id'],'allow_yuyue');
						$this->pagedata['designer'] = $designer;
                    }
                    $this->pagedata['member'] = $member;
                }
            }
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 12;
            if($items = K::M('case/photo')->items(array('case_id'=>$case_id,'closed = 0'), null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($home_id, $case_id, '{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->tmpl = 'home/caseDetail.html';
        }
    }

	public function map()
	{
		$area_list = K::M('data/area')->areas_by_city($this->request['city_id']);
		$attr_values = K::M('data/attr')->attrs_by_from('zx:home');
		$this->pagedata['area_list'] = $area_list;
		$this->pagedata['attr_values'] = $attr_values;
		K::M('helper/seo')->init('home_maps', array());
        $this->tmpl = 'home/maps.html';
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
                $items = K::M('home/home')->items($filter, null, 1, 100, $count);
                $data = array();
                foreach ($items as $val) {
                    $data[$val['home_id']] = array(
                        'home_id' => $val['home_id'],
                        'link' => $this->mklink('home:detail', array($val['home_id']), array(), true),
                        'name' => $val['name'],
                        'thumb' => $val['thumb'],
                        'qq_qun' => $val['qq_qun'],
                        'phone' => $val['phone'],
						'price' => $val['price'],
						'jf_date' => $val['jf_date'],
                        'lng' => $val['lng'],
                        'lat' => $val['lat'],
                        'addr' => $val['addr'],
						'kp_date' => $val['kp_date'],
						'kfs' => $val['kfs'],
                    );
                }
				
                $this->err->set_data('total', $count);
                $this->err->set_data('result', $data);
            }
        }
        $this->err->json();
        die;
    }

	public function tuan($area_id=0, $order=0 ,$page=null)
    {
        if($page === null && $area_id){
            $page = (int)$area_id;
            $area_id = 0;
            $order = 0;
        }
        $filter = $pager = $orderby = array();   
        $area_list = $this->request['area_list'];
        $area_all_link = $this->mklink('home:tuan', array(0, $order, 1));
        foreach ($area_list as $k=>$v) {
            $v['link'] = $this->mklink('home:tuan', array($k, $order, 1));
            $area_list[$k] = $v;
        }
        $order_list = array(0=>array('title'=>'默认'), 1=>array('title'=>'报名'), 2=>array('title'=>'签约'));
        $order_list[0]['link'] = $this->mklink('home:tuan', array($area_id, 0, 1));
        $order_list[1]['link'] = $this->mklink('home:tuan', array($area_id, 1, 1));
        $order_list[2]['link'] = $this->mklink('home:tuan', array($area_id, 2, 1));
        $pager['area_id'] = $area_id;
        $pager['order'] = $order;
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 12;
        $filter['audit'] = 1;
        if ($area_id = (int)$area_id) {
            $filter['area_id'] = $area_id;
        } else {
            $filter['city_id'] = $this->request['city_id'];
        }
        if($order == 1){
            $orderby = array('sign_num'=>'DESC');
        }else if($order == 2){
            $orderby = array('qy_num'=>'DESC');
        }else{
            $orderby = null; 
        }
        if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $filter['title'] = "LIKE:%{$kw}%";            
        }
        if ($items = K::M('home/tuan')->items($filter, $orderby, $page, $limit, $count)) {
            $home_ids = array();
            foreach ($items as $k => $v) {
                if ($v['home_id']) {
                    $home_ids[$v['home_id']] = $v['home_id'];
                }
            }
            if (!empty($home_ids)) {
                $this->pagedata['home_list'] = K::M('home/home')->items_by_ids($home_ids);
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('home:tuan', array($area_id, $order, '{page}'), $params));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['area_list'] = $area_list;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['area_all_link'] = $area_all_link;
        $this->pagedata['pager'] = $pager;
        $seo = array('area_name'=>'', 'page'=>'');
        if($area_id){
            $seo['area_name'] = $area_list[$area_id]['area_name'];
        }
        if($page > 1){
            $seo['page'] = $page;
        }
        $this->seo->init('home_tuan', $seo);
        $this->tmpl = 'home/tuan.html';
    }

	public function tuanDetail($tuan_id)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('没有您要查看的团装', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('没有您要查看的团装', 212);
        }else{
            $package = K::M('home/package')->items(array('tuan_id'=>$tuan_id));
            $huxingIds = array();
            foreach($package as $v){
                if($v['huxing_id']){
                    $huxingIds[$v['huxing_id']] = $v['huxing_id']; 
                }
            }
            if(!empty($huxingIds)){
                $this->pagedata['huxing'] = K::M('home/photo')->items_by_ids($huxingIds);
            }
            $this->pagedata['tuan'] = $detail;
            $this->pagedata['package'] = $package;
            $this->pagedata['home'] = $home = K::M('home/home')->detail($detail['home_id']);
            if($company_id = $detail['company_id']){
                $company = K::M('company/company')->detail($detail['company_id']);
                $this->pagedata['company'] = $company;
            }
            $seo = array('title'=>$detail['title'], 'home_name'=>$home['name'], 'company_name'=>$company['name'], 'tuan_desc'=>'');
            $seo['tuan_desc'] = K::M('content/text')->substr(K::M('content/html')->text($detail['content'], true), 0, 200);
            $this->seo->init('home_tuan_detail', $seo);
            $this->tmpl = 'home/tuanDetail.html';
        }
    }

	public function tuanSign($tuan_id,$package_id)
    {
        if(!($tuan_id = (int)$tuan_id) && !($tuan_id = (int)$this->GP('tuan_id'))){
            $this->err->add('没有您要查看的团装', 211);
        }else if(!$detail = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('没有您要查看的团装', 212);
        }else if ($this->checksubmit()) {
            if (!$data = $this->GP('data')) {
                $this->err->add('非法的数据提交', 201);
            }else {
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
					$data['tuan_id'] = $tuan_id;
					if($package_id){
						$data['package_id'] = $package_id;
					}else{
						$data['package_id'] = 0;
					}
					if ($sign_id = K::M('home/sign')->create($data)) {
						K::M('home/tuan')->update_count($tuan_id,'sign_num', 1);
						$home = K::M('home/home')->detail($detail['home_id']);
						$company = K::M('company/company')->detail($detail['company_id']);
						$smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'home_tuan'=>$home['name'],'tuan_name'=>$detail['title']);
						K::M('sms/sms')->send($data['mobile'], 'home_tuan', $smsdata);
						K::M('sms/sms')->company($company, 'home_tuan_company', $smsdata);
						K::M('helper/mail')->sendcompany($company, 'home_tuan', $maildata);
						$this->err->add('恭喜您报名成功');
					}
				}
            }
        }else{
			$access = $this->system->config->get('access');
			$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
            $this->pagedata['tuan'] = $detail;
			$this->pagedata['package_id'] = $package_id;
            $this->tmpl = 'home/tuanSign.html';
        }      
    }

    protected function check_home($home_id)
    {
        if(!$home_id = (int)$home_id){
            $this->error(404);
        }else if(!$home = K::M('home/home')->detail($home_id)){
            $this->error(404);
        }else if(empty($home['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }
        $this->pagedata['home'] = $home;
        $seo = array('home_name'=>$home['name'], 'home_title'=>$home['title'],'home_desc'=>'');
        $seo['home_desc'] = K::M('content/text')->substr(K::M('content/html')->text($home['content'], true), 0, 200);
        $this->seo->init('home_detail', array('home_name' => $home['name']));
        return $home;
    }
}