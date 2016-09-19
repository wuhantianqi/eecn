<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: log.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Member_Log extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['log_id']){$filter['log_id'] = $SO['log_id'];}
            if($SO['uid']){$filter['uid'] = $SO['uid'];}
            if($SO['from']){$filter['from'] = $SO['from'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1]);$filter['dateline'] = $a."~".$b;}}
        }
        if($items = K::M('member/log')->items($filter, null, $page, $limit, $count)){
            $uids = array();
            foreach($items as $k=>$v){
                $uids[$v['uid']] = $v['uid'];
            }
            $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));;
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:member/log/items.html';
    }

    public function so()
    {
        $this->tmpl = 'admin:member/log/so.html';
    }

    public function member($uid)
    {
    	$this->pagedata['detail'] = K::M('member/log')->detail($pk);
    	$this->tmpl = 'admin:member/log/detail.html';
    }

    public function delete($pk=null)
    {
        if(!empty($pk)){
            if(K::M('member/log')->delete($pk)){
                $this->err->add('删除成功');
            }
        }else if($pks = $this->GP('log_id')){
            if(K::M('member/log')->delete($pks)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}