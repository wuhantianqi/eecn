<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Zxb_Zxb extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['zxb_id']){$filter['zxb_id'] = $SO['zxb_id'];}
			if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
			if($SO['uid']){$filter['uid'] = $SO['uid'];}
			if($SO['company_id']){$filter['company_id'] = $SO['company_id'];}
        }
        if($items = K::M('zxb/zxb')->items($filter,array('zxb_id'=>'desc'), $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
		foreach ($items as $k => $v) {
			if ($v['uid']) {
				$uids[$v['uid']] = $v['uid'];
			}
			if ($v['company_id']) {
				$company_ids[$v['company_id']] = $v['company_id'];
			}
		}

		if (!empty($uids)) {
			$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
		}
		if (!empty($company_ids)) {
			$this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
		}
		$this->pagedata['status'] = K::M("zxb/zxb")->get_status();
		$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:zxb/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:zxb/so.html';
    }

    public function detail($zxb_id = null)
    {
        if(!$zxb_id = (int)$zxb_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:zxb/detail.html';
        }
    }

    public function create()
    {
        if($data = $this->checksubmit('data')){
            
            if($zxb_id = K::M('zxb/zxb')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?zxb/zxb-index.html');
            } 
        }else{
		   $this->pagedata['status'] = K::M("zxb/zxb")->get_status();
           $this->tmpl = 'admin:zxb/create.html';
        }
    }
	
	public function dialog($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['from'] = 'all';
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['uname']){$filter['uname'] = "LIKE:%".$SO['uname']."%";}
            if($SO['mail']){$filter['mail'] = "LIKE:%".$SO['mail']."%";}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if($SO['realname']){$filter['realname'] = "LIKE:%".$SO['realname']."%";}
            if($SO['regip']){$filter['regip'] = "LIKE:%".$SO['regip']."%";}
            if($SO['closed']){
                $filter['closed'] = $SO['closed'];
            }else{
                $filter['closed'] = array(0, 1, 2);
            }
            if(is_array($SO['lastlogin'])){if($SO['lastlogin'][0] && $SO['lastlogin'][1]){$a = strtotime($SO['lastlogin'][0]); $b = strtotime($SO['lastlogin'][1]);$filter['lastlogin'] = $a."~".$b;}}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1]);$filter['dateline'] = $a."~".$b;}}
        }else{
            $filter['closed'] = array(0, 1, 2);
        }
		$pager['from'] = $filter['from'] = array('audit','1');
		
        if($items = K::M('zxb/zxb')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:zxb/dialog.html';   
    }

    public function edit($zxb_id=null)
    {
        if(!($zxb_id = (int)$zxb_id) && !($zxb_id = $this->GP('zxb_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            unset($data['zxb_id'],$data['city_id']);
            if(K::M('zxb/zxb')->update($zxb_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
			$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
            $this->pagedata['detail'] = $detail;
            if ($company_id = $detail['company_id']) {
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            
            if ($uid = (int) $detail['uid']) {
                $this->pagedata['member'] = K::M('member/member')->detail($uid);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:zxb/edit.html';
        }
    }

    public function doaudit($zxb_id=null)
    {
        if($zxb_id = (int)$zxb_id){
            if(K::M('zxb/zxb')->batch($zxb_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('zxb_id')){
            if(K::M('zxb/zxb')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($zxb_id=null)
    {
		if($zxb_id = (int)$zxb_id){
            if($detail = K::M('zxb/zxb')->detail($zxb_id)){
                if(!$this->check_city($detail['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('zxb/zxb')->delete($zxb_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('zxb_id')){
            if($items = K::M('zxb/zxb')->items_by_ids($ids)){
                $aids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['zxb_id']] = $v['zxb_id'];
                }
                if($aids && K::M('zxb/zxb')->delete($aids)){
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }
	
	public function change_zxb($tenders_id)
	{
		if(!$tenders_id = (int)$tenders_id){
            $this->err->add('未指定要修改内容的ID', 211);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('您要转化的内容不存在或已经删除', 212);
        }else{
			$data = $tenders_update =  array();
			$data['city_id'] = $detail['city_id'];
			$data['tenders_id'] = $detail['tenders_id'];
			$data['uid'] = $detail['uid'];
			$data['company_id'] = $detail['sign_company_id'];
			$data['contact'] = $detail['contact'];
			$data['mobile'] = $detail['mobile'];
			$data['comment'] = $detail['comment'];
			if($detail['sign_company_id']){
				$data['status'] = 2;
			}else{
				$data['status'] = 1;
			}
			$data['audit'] = $detail['audit'];
			$data['clientip'] = $detail['clientip'];
			$data['dateline'] = $detail['dateline'];
			if($zxb_id = K::M('zxb/zxb')->create($data)){
				$tenders_update['from'] = 'ZXB';
				$tenders_update['zxb_id'] = $zxb_id;
				K::M('tenders/tenders')->update($tenders_id, $tenders_update);
				$this->err->add('转为装修保成功');
			}
		}
	}
}