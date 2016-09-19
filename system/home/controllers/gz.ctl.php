<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Gz extends Ctl
{
    public function index($page)
    {
        $this->items($page);
    }

	public function items($page=1)
	{
        $pager = $filter = $attrs = $attr_ids = $attr_vids = $attr_value_ids = $attr_value_titles = array();
        $area_id = $group_id = $order = 0;
        $attr_values = K::M('data/attr')->attrs_by_from('zx:gz', true);
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
            $v['link'] = $this->mklink('gz:items', array($area_id, $group_id, implode('-', $vids)));
            $v['checked'] = true;
            foreach($v['values'] as $kk=>$vv){
                $vv['checked'] = false;
                if(in_array($kk, $attr_ids)){
                    $v['checked'] = false;
                    $vv['checked'] = true;
                }                
                $vids[$k] = $kk;
                $vv['link'] = $this->mklink('gz:items', array($area_id, $group_id, implode('-', $vids)));
                $v['values'][$kk] = $vv;

            }
            $attr_values[$k] = $v;
        }
        if($group_list = K::M('member/group')->items_by_from('gz')){
            $group_all_link = $this->mklink('gz:items', array($area_id, 0, implode('-', $attr_value_ids), $order, 1));
            foreach($group_list as $k=>$v){
                $v['link'] = $this->mklink('gz:items', array($area_id, $k, implode('-', $attr_value_ids), $order, 1));
                $group_list[$k] = $v;
            }
        }
        $area_list = $this->request['area_list'];
        $area_all_link = $this->mklink('gz:items', array(0, $group_id, implode('-', $attr_value_ids), $order, 1));
        foreach ($area_list as $k=>$v) {
            $v['link'] = $this->mklink('gz:items', array($k, $group_id, implode('-', $attr_value_ids), $order, 1));
            $area_list[$k] = $v;
        }
        $order_list = array(0=>array('title'=>'默认'), 1=>array('title'=>'签单'), 2=>array('title'=>'口碑'));
        $order_list[0]['link'] = $this->mklink('gz:items', array($area_id, $group_id, implode('-', $attr_value_ids), 0, 1));
        $order_list[1]['link'] = $this->mklink('gz:items', array($area_id, $group_id, implode('-', $attr_value_ids), 1, 1));
        $order_list[2]['link'] = $this->mklink('gz:items', array($area_id, $group_id, implode('-', $attr_value_ids), 2, 1));

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
            $filter['name'] = "LIKE:%{$kw}%";            
        }
        if($order == 1){
            $orderby = array('tenders_num'=>'DESC');
        }else if($order == 2){
            $orderby = array('score'=>'DESC');
        }else{
            $orderby = NULL;
        }
        if ($items = K::M('gz/gz')->items($filter, $orderby, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('gz:items', array($area_id, $group_id, implode('-', $attr_value_ids), $order, '{page}'), $params));
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
        $this->seo->init('gz_items', $seo);
        $this->tmpl = 'gz/items.html';
	}

	public function detail($uid)
	{
        $detail = $this->check_gz($uid);
		$case_list = K::M("case/case")->items(array('closed'=>'0','audit'=>'1','uid'=>$uid), null, 1, 3);
		$site_list = K::M("home/site")->items(array('audit'=>'1','uid'=>$uid), null, 1, 5);	
		$this->pagedata['case_list'] = $case_list;
		$this->pagedata['site_list'] = $site_list;
		$this->tmpl = 'gz/detail.html';
	}

	public function attention($uid)
	{	
		$detail = $this->check_gz($uid);
		if (!$detail['audit']) {
            $this->err->add('您关注的内容还在审核中，暂不可评论', 213);
        }else {
            if(K::M('gz/gz')->update($uid,array('attention_num'=>$detail['attention_num']+1))){
				$this->err->add('关注成功！');
			}
        }
	}	

	public function yuyue($uid)
	{
		if(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('没有您要的数据', 211);
        }else if(!$detail = K::M('gz/gz')->detail($uid)){
            $this->err->add('没有您要的数据', 212);
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
						$data['gz_id'] = $detail['uid'];
						$data['content'] = "预约工长:".$detail['uname'];
						$data['city_id'] =  $this->request['city_id'];
						if($yuyue_id = K::M('gz/yuyue')->create($data)){
                            K::M('gz/yuyue')->yuyue_count($uid);
                            $smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'designer'=>$detail['realname']);
							K::M('sms/sms')->send($data['mobile'], 'gz_yuyue', $smsdata);
                            $this->err->add('预约工长成功');
						}
					}
                } 
            }else{
				$access = $this->system->config->get('access');
				$this->pagedata['yuyue_yz'] = $access['verifycode']['yuyue'];
                $this->pagedata['gz_id'] = $uid;
				$this->pagedata['detail'] = $detail;
                $this->tmpl = 'gz/yuyue.html';              
            }
		}
	}

	public function about($uid)
	{
		$detail = $this->check_gz($uid);
		$this->tmpl = 'gz/about.html';        
	}

	public function cases($uid, $page=1)
	{
        $detail = $this->check_gz($uid);
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;

        if($items = K::M("case/case")->items(array('closed'=>'0','audit'=>1,'uid'=>$uid), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('gz:cases', array($uid ,'{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;      
        $this->tmpl = 'gz/cases.html';
	}


	public function site($uid, $page=1)
	{
		$detail = $this->check_gz($uid);
		$pager['page'] = $page = max((int)$page, 1);
		$pager['limit'] = $limit = 10;
        if($items = K::M("home/site")->items(array('audit'=>'1','uid'=>$uid), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('gz:site', array($uid ,'{page}')));
            $this->pagedata['items'] = $items;
        }
		$this->pagedata['pager'] = $pager;		
		$this->tmpl = 'gz/site.html';
	}

	public function comment($uid, $page=1)
	{
        $detail = $this->check_gz($uid);
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 10;
        $pager['count'] = $count = 0;
        if($items = K::M('gz/comment')->items(array('closed'=>'0','gz_id'=>$uid), null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('gz:comment',array($uid, '{page}')));
            $this->pagedata['items'] = $items;
            $uids = array();
            foreach($items as $v){
                $uids[$v['uid']] = $v['uid'];
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
        }
		$order = array('comment_id'=>'desc');
		$comment_info = K::M("gz/comment")->items(array('closed'=>'0','gz_id'=>$uid),$order,$page,$limit,$count);
		$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('gz:comment',array($uid,'{page}')));
		$this->pagedata['pager'] = $pager;
		foreach($comment_info as $k =>$v){
			$uids[$v['uid']] = $v['uid'];
		}
		if(!empty($uids)){
			$this->pagedata['user_list'] = K::M('member/member')->items_by_ids($uids);
		}
		$access = $this->system->config->get('access');
		$this->pagedata['comment_yz'] = $access['verifycode']['comment'];
		$this->pagedata['comment_info'] = $comment_info;
		$this->tmpl = 'gz/comment.html';
	}

	public function commentsSign($uid)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能评论', 101);
		}elseif (($audit = K::M('member/group')->check_priv($this->MEMBER['group_id'],'allow_score')) == -1) {
			$this->err->add('很抱歉您所在的用户组没有权限操作', 201);
		}elseif(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('没有您要的数据', 211);
        }else if(!$detail = K::M('gz/gz')->detail($uid)){
            $this->err->add('没有您要的数据', 212);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else{
			if(!$data = $this->GP('data')){
				$this->err->add('非法的数据提交', 201);
			}else{
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
					$data['uid'] = $this->uid;
					$data['gz_id'] = $uid;
					$data['city_id'] = $this->request['city_id'];
					$data['audit'] = $audit;
					if($comment = K::M('gz/comment')->create($data)){
						K::M('gz/comment')->comment($data);
						$this->err->add('评论工长成功！');
					}
				}
			}
		}
	}

    protected function check_gz($uid)
    {
        if(!($uid = (int)$uid) && !($uid = $this->GP('uid'))){
            $this->error(404);
        }else if(!$detail = K::M('gz/gz')->detail($uid)){
            $this->error(404);
        }else if($detail['closed'] || !$detail['audit']){
            $this->error(404);
        }
        $detail['group'] = K::M('member/group')->check_priv($detail['group_id'],'allow_yuyue');
        $this->pagedata['detail'] = $detail;
        $seo = array('name'=>$detail['name'], 'about'=>'');
        $seo['about'] = K::M('content/text')->substr(K::M('content/html')->text($detail['about'], true), 0, 200);
        $this->seo->init('gz_detail', $seo); 
        K::M('gz/gz')->update_count($uid, 'views');
        return $detail;
    }
}