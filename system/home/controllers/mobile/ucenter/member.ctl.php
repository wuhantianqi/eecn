<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
class Ctl_Mobile_Ucenter_Member extends Ctl_Mobile_Ucenter
{
	public function zxb()
	{
        $filter['uid'] = $this->uid;
        if($items = K::M('zxb/zxb')->items($filter,array('dateline'=>'desc'))){
			foreach($items as $k => $v){
				$companys[$v['company_id']] = $v['company_id'];
			}
			if($companys){
                $this->pagedata['company_list'] = K::M('company/company')->items_by_ids($companys);
            }
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
            $this->pagedata['items'] = $items;
        }
		$pager['backurl'] = $this->mklink('mobile/ucenter/sign-sign');
        $this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/ucenter/member/zxb.html';
	}

	public function zxb_lists($zxb_id)
	{
		$uid = $this->uid;
        if(!$items = K::M('zxb/zxb')->detail($zxb_id)){
			$this->err->add('该装修保不存在或已被删除', 202);
		}elseif(!$zxb = K::M('zxb/zxb')->items(array('uid'=>$uid,'zxb_id'=>$zxb_id))){
			$this->err->add('您没有权限查看该装修保', 212);
		}else{
            foreach($zxb as $k => $v){
				$company_id = $v['company_id'];
			}
			$this->pagedata['company'] = K::M('company/company')->detail($company_id);
			$tenders = K::M('tenders/tenders')->items(array('zxb_id'=>$zxb_id));
			foreach($tenders as $k => $v){
				$tenders_id = $v['tenders_id'];
			}
			$tenders_look = K::M('tenders/look')->items(array('tenders_id'=>$tenders_id));
			foreach($tenders_look as $k => $v){
				if($v['is_signed'] == 1){
					$this->pagedata['tenders_look'] = $v;
				}
				$this->pagedata['is_look'] = $v;
			}
			
			$hetong = K::M('zxb/hetong')->items(array('zxb_id'=>$zxb_id));
			foreach($hetong as $k => $v){
				$this->pagedata['total_price'] = $v['total_price'];
			}
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
			$this->pagedata['item'] = $items;
			$pager['backurl'] = $this->mklink('mobile/ucenter/member:zxb');
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/ucenter/member/zxb_lists.html';
        }
		
	}

	public function zxb_detail($zxb_id,$status)
	{
		
		if($items = K::M('zxb/zxb')->detail($zxb_id)){
			if(!$company_list = K::M('zxb/zxb')->items(array('uid'=>$this->uid))){
				$this->err->add('您没有权限查看该预约', 201);
			}else{
				$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
				$this->pagedata['item'] = $items;
			}
		}else{
			$this->err->add('该装修保不存在', 203);
		}
		if($status == '3'){
			 $this->hetong($zxb_id,$status);
		}else if($status == '4' || $status == '5' ||$status == '6'){
			$this->step($zxb_id,$status);
		}else if($status == '7'||$status == '8'){
			$this->endstep($zxb_id,$status);
		}else{
			$this->err->add('当前状态不正确', 211);
		}
	}

	public function hetong($zxb_id,$status='3')
	{
		if (!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->err->add('未指定要查看的装修保', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的装修保不存在或已经删除', 212);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该装修保', 213);
        }else if($detail['status'] < ($status-1)){
			 $this->err->add('当前状态不正确', 214);
		}else if(!$member = K::M('member/member')->detail($this->uid)){
			 $this->err->add('当前用户不存在', 215);
		}else{
			if($hetong_list = K::M('zxb/hetong')->items(array('zxb_id'=>$detail['zxb_id']))){
				foreach($hetong_list as $v){
					$this->pagedata['hetong'] = $v;
				}
			}
			if($this->checksubmit('data')){
				if(!$data = $this->GP('data')){
					$this->err->add('非法的数据提交', 201);
				}else{
					if($_FILES['step']){
						foreach($_FILES['step'] as $k=>$v){
							foreach($v as $kk=>$vv){
								$attachs[$kk][$k] = $vv;
							}
						}
						$upload = K::M('magic/upload');
						foreach($attachs as $k=>$attach){
							if($attach['error'] == UPLOAD_ERR_OK){
								if($a = $upload->upload($attach, 'zxb')){
									$step[$k] = $a['photo'];
								}
							}
						}
					}
					$data['yezhu_time']  = __TIME;
					$data['yezhu'] = $member['uname'];
					$data['uid'] = $step['uid'] =  $this->uid;
					$data['yezhu_status'] = $step['yezhu_status'] = '1';
					$step['step'] = $status;
					$step['zxb_id'] = $zxb_id;
					$step['yezhu_content'] = $this->GP('content');
					if($this->pagedata['hetong']['uid']){
						unset($data['zxb_id'],$data['yezhu_time'],$data['yezhu'],$data['uid']);
						if(!$hetong_id = $this->GP('hetong_id')){
							$this->err->add('非法的数据提交', 201);
						}else if(!$step_list = K::M('zxb/step')->items(array('zxb_id'=>$zxb_id,'step' => $status))){
							if(K::M('zxb/hetong')->update($hetong_id,$data)){
								K::M('zxb/step')->create($step);
								$this->err->add('修改内容成功');
							}
						}else{
							foreach($step_list as $v){
								$step_list = $v;
							}
							if(K::M('zxb/hetong')->update($hetong_id,$data)){
								K::M('zxb/step')->update($step_list['step_id'],$step);
								$this->err->add('修改内容成功');
							}
						}
					}else{
						if(!$hetong_id = $this->GP('hetong_id')){
							$this->err->add('非法的数据提交', 201);
						}else{
							if(K::M('zxb/hetong')->update($hetong_id,$data)){								
								K::M('zxb/step')->create($step);
								$this->err->add('添加内容成功');
							}
						}
					}
				}
			}else{
				if($step_list = K::M('zxb/step')->items(array('zxb_id'=>$zxb_id,'step' => $status))){
					foreach($step_list as $v){
						$this->pagedata['step'] = $v;
					}
				}
				$pager['backurl'] = $this->mklink('mobile/ucenter/member:zxb_lists',array('zxb_id'=>$zxb_id));
				$this->pagedata['pager'] = $pager;
				$this->pagedata['now_status'] = $status;
				$this->tmpl = 'mobile/ucenter/member/hetong.html';
			}
        }
	}

	public function step($zxb_id,$status)
	{
		if (!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->err->add('未指定要查看的装修保', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的装修保不存在或已经删除', 212);
        }else if($this->uid != $detail['uid']){
            $this->err->add('您没有权限查看该预约', 213);
        }else if($detail['status'] < ($status-1)){
			 $this->err->add('当前状态不正确', 214);
		}else{
			if($step_lists = K::M('zxb/step')->items(array('zxb_id'=>$detail['zxb_id'],'company_id'=>$detail['company_id'],'step'=>$status))){
				foreach($step_lists as $v){
					$this->pagedata['step'] = $step_list = $v;
				}
			}
			if($photo_lists = K::M('zxb/photo')->items(array('zxb_id'=>$detail['zxb_id'],'company_id'=>$detail['company_id'],'step'=>$status))){
				$this->pagedata['photo'] = $photo_lists;
			}
			if($this->checksubmit('data')){
				if(!$data = $this->GP('data')){
					$this->err->add('非法的数据提交', 201);
				}else{
					if($_FILES['data']){
						foreach($_FILES['data'] as $k=>$v){
							foreach($v as $kk=>$vv){
								$attachs[$kk][$k] = $vv;
							}
						}
						$upload = K::M('magic/upload');
						foreach($attachs as $k=>$attach){
							if($attach['error'] == UPLOAD_ERR_OK){
								if($a = $upload->upload($attach, 'zxb')){
									$data[$k] = $a['photo'];
								}
							}
						}
					}
					$data['uid'] = $this->uid;
					$data['yezhu_status'] = '1';
					$data['yezhu_time'] = __TIME;
					if($step_list['uid']){
						unset($data['uid'],$data['yezhu_status'],$data['yezhu_time']);
						if(!$step_id = $this->GP('step_id')){
							$this->err->add('非法的数据提交', 201);
						}else{
							if(K::M('zxb/step')->update($step_id,$data)){
								$this->err->add('修改内容成功');
							}
						}
					}else{
						if(!$step_id = $this->GP('step_id')){
							$this->err->add('非法的数据提交', 201);
						}else if(K::M('zxb/step')->update($step_id,$data)){
							$this->err->add('添加内容成功');
						}
					}
				}
			}else{
				$this->pagedata['now_status'] = $status;
				$pager['backurl'] = $this->mklink('mobile/ucenter/member:zxb_lists',array('zxb_id'=>$zxb_id));
				$this->pagedata['pager'] = $pager;
				$this->tmpl = 'mobile/ucenter/member/detail.html';
			}
        }
	}

	public function endstep($zxb_id,$status)
	{
		if (!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->err->add('未指定要查看的装修保', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的装修保不存在或已经删除', 212);
        }else if($this->uid != $detail['uid']){
            $this->err->add('您没有权限查看该预约', 213);
        }else if($detail['status'] < ($status-1)){
			 $this->err->add('当前状态不正确', 214);
		}else{
			if($step_lists = K::M('zxb/step')->items(array('zxb_id'=>$detail['zxb_id'],'uid'=>$this->uid,'step'=>$status))){
				foreach($step_lists as $v){
					$this->pagedata['step'] = $step_list = $v;
				}
			}
			if($this->checksubmit('data')){
				if(!$data = $this->GP('data')){
					$this->err->add('非法的数据提交', 201);
				}else{
					if($_FILES['data']){
						foreach($_FILES['data'] as $k=>$v){
							foreach($v as $kk=>$vv){
								$attachs[$kk][$k] = $vv;
							}
						}
						$upload = K::M('magic/upload');
						foreach($attachs as $k=>$attach){
							if($attach['error'] == UPLOAD_ERR_OK){
								if($a = $upload->upload($attach, 'zxb')){
									$data[$k] = $a['photo'];
								}
							}
						}
					}
					$data['uid'] = $this->uid;
					$data['yezhu_status'] = '1';
					$data['yezhu_time'] = __TIME;
					$data['zxb_id'] = $zxb_id;
					$data['step'] = $status;
					if($step_list['uid']){
						unset($data['uid'],$data['yezhu_status'],$data['yezhu_time'],$data['step'],$data['zxb_id']);
						if(!$step_id = $this->GP('step_id')){
							$this->err->add('非法的数据提交', 201);
						}else{
							if(K::M('zxb/step')->update($step_id,$data)){
								$this->err->add('修改内容成功');
							}
						}
					}else{
						if($hetong = K::M('zxb/step')->create($data)){
							$this->err->add('添加内容成功');
						}
					}
				}
			}else{
				$this->pagedata['now_status'] = $status;
				$pager['backurl'] = $this->mklink('mobile/ucenter/member:zxb_lists',array('zxb_id'=>$zxb_id));
				$this->pagedata['pager'] = $pager;
				$this->tmpl = 'mobile/ucenter/member/endstep.html';
			}
        }
	}

	public function tenderDetail($tenders_id)
	{
		if(!$tenders_id = (int)$tenders_id){
            $this->error(404);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('查看的招标不存在或已经删除', 211);
        }else if($detail['uid'] != $this->uid){
            $this->err->add('您没有权限查看该招标', 212);
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
			$pager['backurl'] = $this->mklink('mobile/ucenter');
            $this->pagedata['pager'] = $pager;
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'mobile/ucenter/member/tenderDetail.html';
        }
	}

	public function signLook($look_id)
    {
        if(!$look_id = (int)$look_id){
            $this->error(404);
        }else if(!$look = K::M('tenders/look')->detail($look_id)){
            $this->err->add('竞标不存在或已经删除', 211);
        }else if(!$tenders = K::M('tenders/tenders')->detail($look['tenders_id'])){
            $this->err->add('招标不存或已经删除', 212);
        }else if($tenders['uid'] != $this->uid){
            $this->err->add('你没有权限操作该招标信息', 213);
        }else if(empty($tenders['audit'])){
            $this->err->add('该招标还在审核中，不可操作', 214);
        }else if($tenders['sign_uid']){
            $this->err->add('已经有中标者，不可重复设置', 215);
        }else if(K::M('tenders/look')->sign($look_id)){
            if($zxb_id = (int)$tenders['zxb_id']){
                if(!$company = K::M('company/company')->company_by_uid($look['uid'])){
                    $this->err->add('只装修公司才能参加装修保', 216);
                }else{
                    K::M('zxb/zxb')->sign_company($zxb_id, $company['company_id']);
                }
            }
            $this->err->add('设置中标成功');
        }
    }
}