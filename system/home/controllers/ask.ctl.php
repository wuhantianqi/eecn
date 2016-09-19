<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: ask.ctl.php 5605 2014-06-23 11:04:22Z youyi $
 */
class Ctl_Ask extends Ctl
{

    public function index()
    {
		$access = $this->system->config->get('access');
		$this->pagedata['ask_yz'] = $access['verifycode']['ask'];
        $this->pagedata['cates'] = K::M('ask/cate')->fetch_all();
		$this->seo->init('ask');
        $this->tmpl = 'ask/ask.html';
    }

    public function items($cat_id = 0, $type = 0, $page = 1)
    {
		$filter = $pager = $cate = array();
        $pager['cat_id'] = $cat_id = (int)$cat_id;
        $pager['type'] = $type = (int)$type;
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 30;
		$cate_list = K::M('ask/cate')->fetch_all();
		foreach($cate_list as $k => $v){
			if($v['cat_id'] == $cat_id){
				$cate_list[$k]['current'] = 'current';
				if($v['parent_id'] != '0'){
					$cate_list[$v['parent_id']]['current'] = 'current';
					$pager['parent_id'] = $v['parent_id'];
					$filter['cat_id'][] = $cat_id;
				}else{
					$pager['parent_id'] = $v['cat_id'];
					foreach($cate_list as $kk => $vv){
						if($vv['parent_id'] == $cat_id){
							$filter['cat_id'][] = $vv['cat_id'];
						}
					}
				}
			}
		}
        if ($type = (int) $type) {
            if ($type == 1) {
                $filter['answer_id'] = '>:0';
            } else  {
                $filter['answer_id'] = '0';
            }
        }
        $filter['audit'] = 1;		
        if ($items = K::M('ask/ask')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('ask:items', array($cat_id, $type, '{page}')), array());
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
		$this->pagedata['cate_list'] =$cate_list;
        $cate = $cate_list[$cat_id];
        $this->seo->init('ask_items', array('cate_name'=>$cate['title']));
        $this->tmpl = 'ask/items.html';
    }

    public function make()
    {
        $title = htmlspecialchars($this->GP('title'), ENT_QUOTES, 'utf-8');
		$access = $this->system->config->get('access');
		$this->pagedata['ask_yz'] = $access['verifycode']['ask'];
		$this->pagedata['cates'] = K::M('ask/cate')->fetch_all();
        $this->pagedata['title'] = $title;
        $this->seo->init('ask');
        $this->tmpl = 'ask/make.html';
    }

    public function save()
    {
        if (!$this->check_login()) {
            $this->err->add('登录后才能发布问题', 101);
        }else if (($audit = K::M('member/group')->check_priv($this->MEMBER['group_id'], 'allow_ask')) == -1) {
            $this->err->add('很抱歉您所在的用户组没有权限操作', 201);
        }else if ($data = $this->checksubmit('data')) {
			$verifycode_success = true;
			$access = $this->system->config->get('access');
			if($access['verifycode']['ask']){
				if(!$verifycode = $this->GP('verifycode')){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}else if(!K::M('magic/verify')->check($verifycode)){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}
			}
			if($verifycode_success){
				if($data['content']){
					$data['title'] =  K::M('content/string')->sub($data['content'],0,20,$suffix="");
					$data['intro'] =  K::M('content/string')->sub($data['content'],20,K::M('content/string')->Len($data['content']),$suffix="");
				}
				$data = array(
					'title' => $data['title'],
					'intro' => $data['intro'],
					'uid' => $this->uid,
					'audit' => $audit,
				);
				if($this->GP('cat_id')){
					$data['cat_id'] = $this->GP('cat_id');
				}
				if($_FILES['data']){
					foreach($_FILES['data'] as $k=>$v){
						foreach($v as $kk=>$vv){
							$attachs[$kk][$k] = $vv;
						}
					}
					$upload = K::M('magic/upload');
					foreach($attachs as $k=>$attach){
						if($attach['error'] == UPLOAD_ERR_OK){
							if($a = $upload->upload($attach, 'home')){
								$data[$k] = $a['photo'];
							}
						}
					}
				}
				if(!$cat_id = $this->GP('cat_id')){
					$cat_id = '1';
				}
				if ($ask_id = K::M('ask/ask')->create($data)) {
					K::M('system/integral')->commit('ask', $this->MEMBER, '发布问题');
					$this->err->add('发表问题成功');
					$this->err->set_data('forward', $this->mklink('ask:items', array($cat_id)));
				}
			}
        }
        else {
            $this->err->add('发表问题失败', 201);
        }
    }

    public function detail($ask_id = null,$page=1)
    {
        if (!($ask_id = (int) $ask_id) && !($ask_id = (int) $this->GP('ask_id'))) {
            $this->err->add('内容不存在', 211);
        }else if (!$detail = K::M('ask/ask')->detail($ask_id)) {
            $this->err->add('内容不存在', 212);
        }else if (!$detail['audit']) {
            $this->err->add('尊敬的用户该问题正在审核中！', 212);
            $this->err->set_data('forward', $this->mklink('ask:items'));
        }else{
            $uids = array();
            if($uid = (int)$detail['uid']){
                $uids[$uid] = $uid;
            }            
            if($detail['answer_id']){
                $answer = K::M('ask/answer')->detail($detail['answer_id']);
                $this->pagedata['answer'] = $answer;
                $uids[$answer['uid']] = $answer['uid'];
            }
            
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 10;
            $filter['ask_id'] = $ask_id;
			$filter['answer_id'] = "<>:".$detail['answer_id'];
            if ($items = K::M('ask/answer')->items($filter, null, $page, $limit, $count)) {
                foreach($items as $v){
                    $askids[$v['ask_id']] = $v['ask_id'];
                    if ($v['uid']) {
                        $uids[$v['uid']] = $v['uid'];
                    }
                }
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('ask:items', array($ask_id, '{page}')), array());
            }
			
            $this->pagedata['answers'] = $items;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['member_list'] = K::M('member/view')->items_by_ids($uids);
            $this->pagedata['cate_list'] = $cate_list = K::M('ask/cate')->fetch_all();
            $this->pagedata['detail'] = $detail;
			$this->pagedata['uid'] = $this->uid;
            $seo = array('title'=>$detail['title'], 'cate_name'=>'', 'intro'=>$detail['intro']);
            if($cate = $cate_list[$detail['cat_id']]){
                $seo['cate_name'] = $cate['title'];
            }
            $this->seo->init('ask_detail', $seo);
            if($seo_title = $detail['seo_title']){
                $this->seo->set_title($seo_title);
            }
            if($seo_description = $detail['seo_description']){
                $this->seo->set_description($seo_description);
            }
            if($seo_keywords = $detail['seo_keyword']){
                $this->seo->set_keywords($seo_keywords);
            }
			$access = $this->system->config->get('access');
			$this->pagedata['ask_yz'] = $access['verifycode']['ask'];
            K::M('ask/ask')->update_count($ask_id,'views');
            $this->tmpl = 'ask/detail.html';
        }
    }

    public function yes($answer_id = null)
    {
        if (!$this->check_login()) {
            $this->err->add('请登录', 101);
        }elseif(!($answer_id = (int) $answer_id) && !($answer_id = (int)$this->GP('answer_id'))){
             $this->err->add('内容不存在', 211);
        }elseif(!$detail = K::M('ask/answer')->detail($answer_id)){
            $this->err->add('内容不存在', 211);
        }elseif(!$ask = K::M('ask/ask')->detail($detail['ask_id'])){
           $this->err->add('内容不存在', 211); 
        }elseif($ask['uid'] != $this->uid){
             $this->err->add('您没有权限管理', 211); 
        }else{
            K::M('ask/ask')->update($detail['ask_id'],array('answer_id'=>$answer_id));
            $this->err->add('操作成功！');
        }
        
    }

	public function supply($ask_id = null)
    {
        if (!$this->check_login()) {
            $this->err->add('请登录', 101);
        }elseif (!($ask_id = (int) $ask_id) && !($ask_id = (int) $this->GP('ask_id'))) {
            $this->err->add('内容不存在', 211);
        }else if (!$detail = K::M('ask/ask')->detail($ask_id)) {
            $this->err->add('内容不存在', 212);
        }elseif($detail['uid'] != $this->uid){
            $this->err->add('这不是您发的问题', 212); 
        }elseif(!$content = $this->GP('content')){
             $this->err->add('请输入您要补充的内容', 212);            
        }else{
			$verifycode_success = true;
			$access = $this->system->config->get('access');
			if($access['verifycode']['ask']){
				if(!$verifycode = $this->GP('verifycode')){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}else if(!K::M('magic/verify')->check($verifycode)){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}
			}
			if($verifycode_success){
				$data = array(
					'intro'  => $detail['intro'].'\n'.$content,
				);
				if($supply_id = K::M('ask/ask')->update($detail['ask_id'],$data)){
					$this->err->add('补充内容成功');
				}else{
					$this->err->add('补充内容失败', 212);
				} 
			}            
        }        
    }
    
    public function answer($ask_id = null)
    {
        if (!$this->check_login()) {
            $this->err->add('请登录', 101);
        }elseif (($audit = K::M('member/group')->check_priv($this->MEMBER['group_id'],'allow_answer')) == -1) {
            $this->err->add('很抱歉您所在的用户组没有权限操作', 201);
        }elseif (!($ask_id = (int) $ask_id) && !($ask_id = (int) $this->GP('ask_id'))) {
            $this->err->add('内容不存在', 211);
        }else if (!$detail = K::M('ask/ask')->detail($ask_id)) {
            $this->err->add('内容不存在', 212);
        }elseif($detail['uid'] == $this->uid){
             $this->err->add('您不能回复自己的问题', 214);            
        }elseif(!$contents = $this->GP('contents')){
             $this->err->add('请输入您要回答的内容', 212);            
        }else{
			$verifycode_success = true;
			$access = $this->system->config->get('access');
			if($access['verifycode']['ask']){
				if(!$verifycode = $this->GP('verifycode')){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}else if(!K::M('magic/verify')->check($verifycode)){
					$verifycode_success = false;
					$this->err->add('验证码不正确', 212);
				}
			}
			if($verifycode_success){
				$data = array(
					'ask_id'    => $ask_id,
					'uid'       => $this->uid,
					'contents'  => $contents,
					'audit'     => $audit
				);
				
				if($supply_id = K::M('ask/answer')->create($data)){
					if($this->MEMBER['from'] == 'company'){
						$company = K::M('company/company')->company_by_uid($this->uid);
						if($company){
							K::M('company/company')->update_count($company['company_id'],'answer_num',1);
						}
					}
					K::M('ask/ask')->update_count($ask_id,'answer_num',1);
					K::M('system/integral')->commit('answer', $this->MEMBER, '回答问题');
					$this->err->add('回答问题成功');
				}else{
					$this->err->add('回答问题失败', 212);
				}
			}
        }    
    }
}
