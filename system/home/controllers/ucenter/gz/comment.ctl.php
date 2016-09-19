<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Gz_Comment extends Ctl_Ucenter
{
    
    protected $_allower_fields = 'reply,replyip,replytime,audit';

    public function index($page = 1)
    {
        $gz = $this->ucenter_gz();
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['gz_id'] = $gz['uid'];
        $filter['closed'] = 0;
        if ($items = K::M('gz/comment')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
		$uids  = array();
		foreach($items as $k => $v){
			$uids[$v['uid']] = $v['uid'];
		}
		if($member = K::M('member/member')->items_by_ids($uids)){
			$this->pagedata['member'] = $member;
		}
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/gz/comment/items.html';
    }

	public function reply($comment_id=null)
    {
        $gz = $this->ucenter_gz();
        if (!($comment_id = (int) $comment_id) && !($comment_id = (int)$this->GP('comment_id'))) {
            $this->error(404);
        } else if (!$detail = K::M('gz/comment')->detail($comment_id)) {
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        } else if ($detail['gz_id'] != $gz['uid']) {
            $this->err->add('不许越权管理别人的内容', 212);
        } else if ($data = $this->checksubmit('data')) {
            if (!$data = $this->check_fields($data,  $this->_allower_fields)) {
                $this->err->add('非法的数据提交', 201);
            } else {
				$data['replyip'] = __IP;
				$data['replytime'] = __TIME;
				$data['audit'] = '1';
				if(K::M('gz/comment')->update($comment_id, $data)){
					$this->err->add('修改回复成功');
					$this->err->set_data('forward', $this->mklink('ucenter/gz/comment:index'));
				}
            }
        } else {
			$member = K::M('member/member')->detail($detail['uid']);
			$detail['uname'] = $member['uname'];
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/gz/comment/reply.html';
        } 
    }
}