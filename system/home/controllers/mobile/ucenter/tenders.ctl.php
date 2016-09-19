<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Mobile_Ucenter_Tenders extends Ctl_Mobile_Ucenter
{
	
	public function tender()
	{
		$filter['uid'] = $this->uid;
		if($items = K::M('tenders/tenders')->items($filter)){
			$this->pagedata['items'] = $items;
		}
		$pager['backurl'] = $this->mklink('mobile/ucenter');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/tenders/tender.html';
	}

	public function tendersEdit($tenders_id)
	{
		$pager['backurl'] = $this->mklink('mobile/ucenter/member:tenderDetail',array('tenders_id'=>$tenders_id));
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/tenders/tendersEdit.html';
	}

	public function tenders()
	{
        $filter['uid'] = $this->uid;
		if($this->MEMBER['from'] == 'member'){
			if($items = K::M('tenders/tenders')->items($filter)){
				$this->pagedata['items'] = $items;
			}
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/ucenter/tenders/tender.html';
		}else{
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/ucenter/tenders/tender_looks.html';
		}
	}

	public function tendered()
	{
		$form = $this->MEMBER['from'];
		if($form  == 'member'){
			$this->err->add('权限错误', 211);
		}else{
			$filter = $pager = array();
			$filter['city_id'] = $form['city_id'];
			$filter['audit'] = 1;$filter['status'] = 0;
			$tenders_ids =  array();
			if($items = K::M('tenders/tenders')->items($filter)){
				$this->pagedata['items'] = $items;
			}
			$pager['backurl'] = $this->mklink('mobile/ucenter');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/ucenter/tenders/tendered.html';
		}
	}

	public function tenderDetail($tenders_id)
	{
		$form = $this->MEMBER['from'];
		if($form  == 'member'){
			$this->err->add('权限错误', 211);
		}else{
			$temp = 'ucenter_'.$form;
			$form_list = $this->$temp();
			$audit_tenders = K::M('system/audit')->$form('tenders', $form_list, $audit_title);
			$pager = array('audit_tenders'=>$audit_tenders, 'audit_title'=>$audit_title);
			if(!$tenders_id = (int)$tenders_id){
				$this->error(404);
			}else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
				$this->err->add('招标不存在或已经删除', 211);
			}else if(empty($detail['audit'])){
				$this->err->add('招标还在审核中，不可查看', 211);
			}else{
				if($look_list = K::M('tenders/look')->items_by_tenders($tenders_id)){
					$uids = array();
					foreach($look_list as $k=>$v){
						$uids[$v['uid']] = $v['uid'];
					}
					$this->pagedata['look_list'] = $look_list;
					if($uids){
						if($member_list = K::M('member/member')->items_by_ids($uids)){
							$company_uids = array();
							foreach($member_list as $v){
								$company_uids[$v['uid']]  = $v['uid'];
							}
							if($company_uids){
								$this->pagedata['company_list'] = K::M('company/company')->items_by_uids($company_uids);
							}
						}
					}
				}
				K::M('tenders/tenders')->update_count($tenders_id,'pv_num');
				if($uid = $detail['uid']){
					$this->pagedata['member'] = K::M('member/member')->member($uid);
				}
				$looked= K::M('tenders/look')->items(array('tenders_id'=>$tenders_id,'uid'=>$this->uid));
				foreach($looked as $k => $v){
					$detail['looked'] = 1;
				}
				$pager['backurl'] = $this->mklink('mobile/ucenter/tenders-tendered');
				$this->pagedata['pager'] = $pager;
				$this->pagedata['detail'] = $detail;
				$this->tmpl = 'mobile/ucenter/tenders/tenderDetail.html';
			}
		}
	}

	public function tenders_look($tenders_id)
	{	
		if(!($tenders_id = (int)$tenders_id) && !($tenders_id = (int)$this->GP('tenders_id'))){
            $this->error(404);
        }else if(($tenders_look = K::M('member/group')->check_priv($this->MEMBER['group_id'], 'tenders_look')) < 0){
            $this->err->add('您是【'.$this->MEMBER['group_name'].'】不能进行投标', 333);
        }else if(!$tenders = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('招标不存在或已经删除', 212);
        }else if(!$tenders['audit']){
            $this->err->add('该招标还没有公布不好意思!', 212); 
        }elseif((int)$tenders['status'] === 1){
            $this->err->add('该招标已经结束了!', 212); 
        }else if($tenders['looks'] >= $tenders['max_look']){
            $this->err->add('该招标已经结束了!', 212); 
        }else if($looked = K::M('tenders/look')->items(array('tenders_id'=>$tenders_id,'uid'=>$this->uid))){
            $this->err->add('您已经投过标了，不需要重复投标', 213);
        }else if(($tenders['gold']) && ($this->MEMBER['gold']<$tenders['gold'])){
            $this->err->add('您的金币全额不足，请先充值', 215);
        }else if($data = $this->checksubmit('data')){
            if(!$content = $data['content']){
                $this->err->add('给业主留言不能为空', 216);
            }else{
                if($tenders['gold'] > 0){
                    if(!K::M('member/gold')->update($this->uid, -$tenders['gold'], "看标：".$tenders['title']."(ID:{$tenders_id})")){
                        $this->err->add('扣费失败', 201)->response();
                    }
                }
                $data = array('tenders_id'=>$tenders_id, 'uid'=>$this->uid, 'content'=>$content);
                if($look_id = K::M('tenders/look')->create($data)){
                    K::M('tenders/tenders')->update_count($tenders_id, 'looks');
                    switch ($this->MEMBER['from']) {
                        case 'gz':
                            K::M('gz/gz')->update_count($this->uid, 'tenders'); break;
                        case 'designer':
                            K::M('designer/designer')->update_count($this->uid, 'tenders'); break;
                        case 'mechanic':
                            K::M('mechanic/mechanic')->update_count($this->uid, 'tenders'); break;
                        case 'company':
                            K::M('company/company')->update_count($this->company['company_id'], 'tenders'); break;
                        case 'shop':
                            K::M('shop/shop')->update_count($this->shop['shop_id'], 'tenders'); break;
                    }
                    $this->err->add('参加竞标成功！');
					$this->err->set_data('forward', $this->mklink('mobile/ucenter/tenders:tenderDetail', array('tenders_id'=>$tenders_id)));
                }
            }
        }else{
			$pager['backurl'] = $this->mklink('mobile/ucenter/tenders-tendered', array('tenders_id'=>$tenders_id));
			$this->pagedata['pager'] = $pager;
            $this->pagedata['tenders'] = $tenders;
            $this->tmpl = 'mobile/ucenter/tenders/tenderSign.html';
        }
	}

	public function tenders_looked()
	{
        $tenders_ids = array();
        $filter['uid'] = $this->uid;
        if($items = K::M('tenders/look')->items($filter)){
            foreach($items as $k=>$v){
                $tenders_ids[$v['tenders_id']] = $v['tenders_id'];
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            if($tenders_ids){
                $this->pagedata['tenders_list'] = K::M('tenders/tenders')->items_by_ids($tenders_ids);
            }
        }
        $pager['backurl'] = $this->mklink('mobile/ucenter/tenders-tenders');
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/ucenter/tenders/tenders_looked.html';
	}
}
