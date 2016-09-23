<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Case extends Ctl
{
	public function index($page = 1)
	{
		$this->album($page);
	}

	public function items($page = 1)
	{
        $pager = $filter = $attrs = $attr_ids = $attr_vids = $attr_value_ids = $attr_value_titles = array();
        $order = 0;
        $attr_values = K::M('data/attr')->attrs_by_from('zx:case', true);
        $uri = $this->request['uri'];
        if(preg_match('/items-([\d\-]+)?(-(\d+)).html/i', $uri, $m)){
            $page = (int)$m[3];
            if($m[1]){
                $attr_vids = explode('-', trim($m[1], '-'));
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
            $v['link'] = $this->mklink('case:items', array(implode('-', $vids)));
            $v['checked'] = true;
            foreach($v['values'] as $kk=>$vv){
                $vv['checked'] = false;
                if(in_array($kk, $attr_ids)){
                    $v['checked'] = false;
                    $vv['checked'] = true;
                }                
                $vids[$k] = $kk;
                $vv['link'] = $this->mklink('case:items', array(implode('-', $vids)));
                $v['values'][$kk] = $vv;

            }
            $attr_values[$k] = $v;
        }
        $order_list = array(0=>array('title'=>'今日推荐'), 1=>array('title'=>'最受欢迎 '), 2=>array('title'=>'人气排行'));
        $order_list[0]['link'] = $this->mklink('case:items', array(implode('-', $attr_value_ids), 0, 1));
        $order_list[1]['link'] = $this->mklink('case:items', array(implode('-', $attr_value_ids), 1, 1));
        $order_list[2]['link'] = $this->mklink('case:items', array(implode('-', $attr_value_ids), 2, 1));
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['order'] = $order;
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['city_id'] = array($this->request['city_id'], 0);
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
        if($order == 2){
            $orderby = array('likes'=>'DESC');
        }else if($order == 1){
            $orderby = array('views'=>'DESC');
        }else{
            $orderby = NULL;
        }
        if ($items = K::M('case/case')->items($filter, $orderby, $page, $limit, $count)) {
            $lastphotos = array();
            foreach ($items as $k => $val) {
                if ($val['lastphotos']) {
                    $lastphotos[] = $val['lastphotos'];
                    $items[$k]['lastphotos'] = explode(',', $val['lastphotos']);
                }
            }
            if (!empty($lastphotos)) {
                $lastphotos = join(',', $lastphotos);
                $this->pagedata['photos'] = K::M('case/photo')->items_by_ids($lastphotos);
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('case:items', array(implode('-', $attr_value_ids), $order, '{page}'), $params));
        }
        $this->pagedata['attr_values'] = $attr_values;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['pager'] = $pager;
        $seo = array('attr'=>'', 'page'=>'');
        if($attr_value_titles){
            $seo['attr'] = implode('_', $attr_value_titles);
        }
        if($page > 1){
            $seo['page'] = $page;
        }    
        $this->seo->init('case', $seo);
        $this->tmpl = 'case/items.html';
	}
	public function decorationCase(){
		$this->tmpl = 'case/default.html';
	}
	public function album($page = 1)
	{
        $pager = $filter = $attrs = $attr_ids = $attr_vids = $attr_value_ids = $attr_value_titles = array();
        $order = 0;
        $attr_values = K::M('data/attr')->attrs_by_from('zx:case');
        $uri = $this->request['uri'];
        if(preg_match('/album(-[\d\-]+)?(-(\d+)).html/i', $uri, $m)){
            $page = (int)$m[3];
            if($m[1]){
                $attr_vids = explode('-', trim($m[1], '-'));
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
            $v['link'] = $this->mklink('case:album', array(implode('-', $vids)));
            $v['checked'] = true;
            foreach($v['values'] as $kk=>$vv){
                $vv['checked'] = false;
                if(in_array($kk, $attr_ids)){
                    $v['checked'] = false;
                    $vv['checked'] = true;
                }                
                $vids[$k] = $kk;
                $vv['link'] = $this->mklink('case:album', array(implode('-', $vids)));
                $v['values'][$kk] = $vv;

            }
            $attr_values[$k] = $v;
        }
        $order_list = array(0=>array('title'=>'今日推荐'), 1=>array('title'=>'最受欢迎 '), 2=>array('title'=>'人气排行'));
        $order_list[0]['link'] = $this->mklink('case:album', array(implode('-', $attr_value_ids), 0, 1));
        $order_list[1]['link'] = $this->mklink('case:album', array(implode('-', $attr_value_ids), 1, 1));
        $order_list[2]['link'] = $this->mklink('case:album', array(implode('-', $attr_value_ids), 2, 1));
        $pager = $filter = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['order'] = $order;
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['city_id'] = array($this->request['city_id'], 0);
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
        if($order == 2){
            $orderby = array('likes'=>'DESC');
        }else if($order == 1){
            $orderby = array('views'=>'DESC');
        }else{
            $orderby = NULL;
        }
        if ($items = K::M('case/case')->items($filter, $orderby, $page, $limit, $count)) {
            $lastphotos = array();
            foreach ($items as $k => $val) {
                if ($val['lastphotos']) {
                    $lastphotos[] = $val['lastphotos'];
                    $items[$k]['lastphotos'] = explode(',', $val['lastphotos']);
                }
            }
            if (!empty($lastphotos)) {
                $lastphotos = join(',', $lastphotos);
                $this->pagedata['photos'] = K::M('case/photo')->items_by_ids($lastphotos);
            }
            $this->pagedata['items'] = $items;
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('case:album', array(implode('-', $attr_value_ids), $order, '{page}'), $params));
        }
        $this->pagedata['attr_values'] = $attr_values;
        $this->pagedata['order_list'] = $order_list;
        $this->pagedata['pager'] = $pager;
        $seo = array('attr'=>'', 'page'=>'');
        if($attr_value_titles){
            $seo['attr'] = implode('_', $attr_value_titles);
        }
        if($page > 1){
            $seo['page'] = $page;
        }    
        $this->seo->init('case', $seo);
        $this->tmpl = 'case/album.html';
	}

	public function detail($case_id, $page = 1)
    {
		$case = $this->check_case($case_id);
		K::M('case/case')->update_count($case_id, 'views', 1);
        if($company_id = $case['company_id']){
		  $this->pagedata['company'] = K::M('company/company')->detail($case['company_id']);
        }else if($member = K::M('member/member')->member($case['uid'])){
            if($member['from'] == 'gz'){
                $this->pagedata['gz'] = K::M('gz/gz')->detail($case['uid']);
            }else if($member['from'] == 'designer'){
                $this->pagedata['designer'] = K::M('designer/designer')->detail($case['uid']);
            }
        }
        if($attr_values = K::M('case/attr')->attrs_by_case($case_id)){
            foreach($attr_values as $k=>$v){
                $case['attrvalues'][$k] = $v['attr_value_id'];
            }
        }
		$this->pagedata['photos'] = K::M('case/photo')->items_by_case($case_id, 1, 50);
		$filter = $pager = array();
		$pager['page'] = max(intval($page), 1);
		$pager['limit'] = $limit = 5;
		$filter['case_id'] = $case_id;
		if ($items = K::M('case/comment')->items($filter,array('dateline'=>'desc'), $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('case:detail', array($case_id,'{page}')));            
			$uids = array();
			foreach ($items as $k => $v) {
				$uids[$v['uid']] = $v['uid'];
			}
            if($uids){
                $this->pagedata['user_list'] = K::M('member/member')->items_by_ids($uids);
            }
            $this->pagedata['items'] = $items;            
		}
		$access = $this->system->config->get('access');
		$this->pagedata['comment_yz'] = $access['verifycode']['comment'];
        $this->pagedata['detail'] = $case;
        $this->pagedata['pager'] = $pager;
		$this->seo->init('case_detail',array(
			'title' => $case['title'],
			'seo_title' => $case['seo_title'],
			'seo_keywords' => $case['seo_keywords'],
			'seo_description' => $case['seo_description'],
		));
		$this->tmpl = 'case/detail.html';
    }

	public function comment($case_id)
	{
		if(!$this->check_login()){
			$this->err->add('您还没有登录，不能评论', 101);
		}elseif (($audit = K::M('member/group')->check_priv($this->MEMBER['group_id'],'allow_comment')) == -1) {
            $this->err->add('很抱歉您所在的用户组没有权限操作', 201);
        }elseif (!$content = $this->GP('content')) {
              $this->err->add('评论内容不能不能为空', 211);
        }else {
			$verifycode_success = true;
			$access = $this->system->config->get('access');
			if($access['verifycode']['comment']){
				if(!$verifycode = $this->GP('verifycode')){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}else if(!K::M('magic/verify')->check($verifycode)){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}
			}
			if($verifycode_success){
				$case = $this->check_case($case_id);
				$data = array(
					'case_id' => $case_id,
					'uid' => $this->uid,
					'content' => $content,
					'audit' => $audit,
					'city_id' => $this->request['city_id']
				);
				K::M('case/comment')->create($data);
				$this->err->add('评论成功！');
			}
        }
	}

	protected function check_case($case_id)
    {
		if (!$case_id = (int) $case_id) {
            $this->error(404);
        }else if (!$case = K::M('case/case')->detail($case_id)) {
            $this->error(404);
        }elseif (!$case['audit']) {
           $this->err->add("内容审核中，暂不可访问", 211)->response();
        }
		$this->pagedata['uri'] = $this->request['uri'];
        $this->pagedata['detail'] = $case;
        return $case;
    }

    public function like($case_id)
    {
        if (!$case_id = (int) $case_id) {
            $this->err->add('案例不存在', 211);
        }else if (!$case = K::M('case/case')->detail($case_id)) {
            $this->err->add('案例不存在', 212);
        }else if (!$case['audit']) {
            $this->err->add('该案例还未通过审核', 212);
        }else if (K::M('case/like')->is_like($case_id, __IP)) {
            $this->err->add('已经喜欢过了', 212);
        } else {
            $data = array('case_id' => $case_id, 'uid' => $this->uid, 'create_ip' => __IP, 'dateline' => __TIME);
            K::M('case/like')->create($data);
            K::M('case/case')->update_count($case_id, 'likes', 1);
            $this->err->add('喜欢成功');
        }
    }
}