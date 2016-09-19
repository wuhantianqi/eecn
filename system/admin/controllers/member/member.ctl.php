<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: member.ctl.php 5610 2014-06-23 16:47:52Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Member_Member extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['from']){$filter['from'] = $SO['from'];}
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
		
        if($items = K::M('member/member')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));;
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:member/member/items.html';
    }

    public function so($target=null, $multi=null, $from='member')
    {
        if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
            $pager['from'] = $from;
        }
        $this->pagedata['from_list'] = K::M('member/member')->from_list();
        $this->pagedata['pager'] = $pager;   
        $this->tmpl = 'admin:member/member/so.html';
    }

    public function dialog($from='member', $page=1)
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
        $from_list = K::M('member/member')->from_list();
		if($from_list[$from]){
            $pager['from'] = $filter['from'] = $from;
        }else if($from == 'cygz'){
			$pager['from'] = $filter['from'] = array('company','gz');
		}else if($from == 'cydr'){
			$pager['from'] = $filter['from'] = array('company','designer');
		}
        if($items = K::M('member/member')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($from,'{page}')), array('SO'=>$SO, 'multi'=>$multi));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:member/member/dialog.html';   
    }

	

    public function ucard($uid=null)
    {
        if(!$uid = (int)$uid){
            $this->err->add('未指定要查看的ID', 211);
        }else if(!$member = K::M('member/member')->detail($uid)){
            $this->err->add('要查看的用户不存在', 212);
        }else{
            if($member['from'] == 'shop'){
                $this->pagedata['shop'] = K::M('shop/shop')->shop_by_uid($member['uid']);
            }else if($member['company']){
                $this->pagedata['company'] = K::M('company/company')->company_by_uid($member['uid']);
            }
            $this->pagedata['detail'] = $member;
            $this->tmpl = 'admin:member/member/ucard.html';
        }
    }
    
    public function manager($uid)
    {
        if(K::M('member/auth')->manager($uid)) {
            $cfg = $this->system->config->get('site');
            $link = $cfg['siteurl'].'/index.php?ucenter/member-index.html';
            header("Location: {$link}");
            exit();
        }        
    }  

    public function ulock($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        $filter['closed'] = '2';
        if($items = K::M('member/member')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:member/member/items.html';
    }

    public function recycle($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 50;
        $filter['closed'] = '3';
        if($items = K::M('member/member')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));;
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:member/member/recycle.html';
    }

    public function regain($uid=null)
    {
        if($uid = intval($uid)){
            if(K::M('member/member')->regain($uid)){
                $this->err->add('恢复会员帐号成功');
            }
        }else if($uids = $this->GP('uid')){
            if(K::M('member/member')->regain($uids)){
                $this->err->add('批量恢复会员帐号成功');
            }
        }else{
            $this->err->add('未指定要恢复会员', 401);
        }
    }

    public function gold($uid=null)
    {
        if(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('未指定要修改的用户ID', 211);
        }else if(!$detail = K::M('member/member')->detail($uid)){
            $this->err->add('指定的用户不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 213);
            }else if(!$gold = (int)$data['gold']){
                $this->err->add('非法的充值金额', 214);
            }else if(!$log = trim($data['log'])){
                $this->err->add('没有填写充值备注', 215);
            }else if($gold<0 && (abs($gold)>$detail['gold'])){
                $this->err->add('减去的金币不能大于现有金币', 216);
            }else if(K::M('member/gold')->update($uid, $gold, $log)){
                $this->err->add('充值金币成功');
            } 
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:member/member/gold.html';
        }
    }

    public function detail($pk)
    {
    	$this->pagedata['detail'] = K::M('member/member')->detail($pk);
    	$this->tmpl = 'admin:member/member/detail.html';
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($uid = K::M('member/member')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?member/member-index.html');
                }
            } 
        }else{
            $this->pagedata['from_list'] = K::M('member/member')->from_list();
            $this->tmpl = 'admin:member/member/create.html';
        }
    }

    public function edit($uid=null)
    {
        if(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('未指定要修改的用户ID', 211);
        }else if(!$detail = K::M('member/member')->detail($uid)){
            $this->err->add('指定的用户不存在或已经删除', 212);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($data['passwd'] == '******'){
                    unset($data['passwd']);
                }else{
                    $passwd = trim($data['passwd']);
                    if(K::M('member/account')->update_passwd($uid, $passwd)){
                        $data['passwd'] = md5($passwd);
                    }
                }                
                if(K::M('member/member')->update($uid, $data)){
                    $this->err->add('修改内容成功');
                }
            } 
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:member/member/edit.html';
        }
    }

    public function face()
    {
        if(!$uid = $this->GP('itemId')){
            $this->err->add('未指定要修改的用户！',201);
        }else if(!$member = K::M('member/member')->detail($uid)){
            $this->err->add('用户不存在，请刷新重式！',202);
        }else if(!$data = file_get_contents("php://input")){
            $this->err->add('图片数据上传失败',203);
        }else if(K::M('member/face')->update_face($uid, null, $data)){
            $this->err->add('更新会员头像成功');
        }
        $this->err->json();
    }

    public function delete($uid=null, $force=false)
    {
        if($uid = intval($uid)){
            if(K::M('member/member')->delete($uid, $force)){
                $this->err->add('删除成功');
            }
        }else if($uids = $this->GP('uid')){
            $force = $this->GP('force') ? $this->GP('force') : $force;
            if(K::M('member/member')->delete($uids, $force)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}