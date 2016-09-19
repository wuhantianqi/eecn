<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Lotterysn extends Ctl
{
    
    public function items($lottery_id,$page=1)
    {
		if(!($lottery_id = (int)$lottery_id) && !($lottery_id = $this->GP('lottery_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/lottery')->detail($lottery_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
			$filter['lottery_id'] = $lottery_id;
			if($items = K::M('weixin/lotterysn')->items($filter, null, $page, $limit, $count)){
				foreach($items as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
				
				$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
			}
			$this->pagedata['detail'] = $detail;
			$this->pagedata['items'] = $items;
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'admin:weixin/lotterysn/items.html';
		}
    }

    public function create($lottery_id)
    {
		if(!($lottery_id = (int)$lottery_id) && !($lottery_id = $this->GP('lottery_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/lottery')->detail($lottery_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else{
			if($data = $this->checksubmit('data')){
				$data['lottery_id'] = $lottery_id;
				if($sn_id = K::M('weixin/lotterysn')->create($data)){
					$this->err->add('添加内容成功');
					$this->err->set_data('forward', '?weixin/lotterysn-items-'.$lottery_id);
				} 
			}else{
			   $this->pagedata['lottery_id'] = $lottery_id;
			   $this->tmpl = 'admin:weixin/lotterysn/create.html';
			}
		}
    }

    public function edit($sn_id=null)
    {
		if(!($sn_id = (int)$sn_id) && !($sn_id = $this->GP('sn_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/lotterysn')->detail($sn_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            
            if(K::M('weixin/lotterysn')->update($sn_id, $data)){

                $this->err->add('修改内容成功');
            }  
        }else{
			$member = K::M('member/member')->detail($detail['uid']);
			$detail['member'] = $member['uname'];
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:weixin/lotterysn/edit.html';
        }
    }



    public function delete($sn_id=null)
    {
        if($sn_id = (int)$sn_id){
            if(!$detail = K::M('weixin/lotterysn')->detail($sn_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/lotterysn')->delete($sn_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('sn_id')){
            if(K::M('weixin/lotterysn')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}