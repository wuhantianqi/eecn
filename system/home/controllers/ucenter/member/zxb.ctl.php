<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Member_Zxb extends Ctl_Ucenter 
{
    public function index($page=1)
    {
        $pager = $filter = $companys = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
        $filter['uid'] = $this->uid;
        if($items = K::M('zxb/zxb')->items($filter,array('zxb_id'=>'desc'),$page, $limit, $count)){
			foreach($items as $k => $v){
				$companys[$v['company_id']] = $v['company_id'];
			}
			if($companys){
                $this->pagedata['companys'] = K::M('company/company')->items_by_ids($companys);
            }
			
            $this->pagedata['member'] = K::M('member/member')->detail($this->uid);
            
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/member/zxb/items.html';
    }

	public function lists($zxb_id)
	{
		$uid = $this->uid;
        if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
			$this->err->add('该装修保不存在或已被删除', 202);
		}elseif($detail['uid'] != $uid){
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
			$this->pagedata['member'] = K::M('member/member')->detail($this->uid);
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
			$this->pagedata['item'] = $detail;
			$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
			$this->tmpl = 'ucenter/member/zxb/lists.html';
        }
		
	}

	public function detail($zxb_id,$status)
	{
		
		if($items = K::M('zxb/zxb')->detail($zxb_id)){
			if(!$company_list = K::M('zxb/zxb')->items(array('company_id'=>$items['company_id']))){
				$this->err->add('您没有权限查看该预约', 201);
			}else{
				foreach($company_list as $k => $v){
					$company_id = $v['company_id'];
				}
				$this->pagedata['company'] = K::M('company/company')->detail($company_id);
				$tenders = K::M('tenders/tenders')->items(array('zxb_id'=>$zxb_id));
				foreach($tenders as $k => $v){
					$tenders_id = $v['tenders_id'];
				}
				$tenders_look = K::M('tenders/look')->items(array('tenders_id'=>$tenders_id));
				foreach($tenders_look as $k => $v){
					$this->pagedata['tenders_look'] = $v;
				}
				$hetong = K::M('zxb/hetong')->items(array('zxb_id'=>$zxb_id));
				foreach($hetong as $k => $v){
					$this->pagedata['total_price'] = $v['total_price'];
				}
				$this->pagedata['member'] = K::M('member/member')->detail($this->uid);
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
				$this->pagedata['now_status'] = $status;
				$this->tmpl = 'ucenter/member/zxb/hetong.html';
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
				$this->tmpl = 'ucenter/member/zxb/detail.html';
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
				$this->tmpl = 'ucenter/member/zxb/endstep.html';
			}
        }
	} 

	public function plaint($zxb_id)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}elseif(!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->error(404);;
        }else if (!$detail = K::M('zxb/zxb')->detail($zxb_id)) {
            $this->err->add('装修宝不存在或已经删除', 212);
        }else if (!$detail['company_id']) {
            $this->err->add('装修宝还未确认公司', 213);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可投诉", 211)->response();
        }else{
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
					$data['uid'] = (int)$this->uid;
					$data['company_id'] = $detail['company_id'];
					$data['zxb_id'] = $detail['zxb_id'];
					$data['type'] = "1";
					$data['yezhu_time'] = __TIME;
                    if($yuyue_id = K::M('zxb/plaint')->create($data)){
                        $this->err->add('投诉发布成功');  
                    }
                } 
            }else{             
                $this->pagedata['zxb_id'] = $zxb_id;
                $this->tmpl = 'ucenter/member/zxb/look.html';
            }            
        }
    }    

	public function plaintlists($zxb_id)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}elseif(!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->error(404);;
        }else if (!$detail = K::M('zxb/zxb')->detail($zxb_id)) {
            $this->err->add('装修宝不存在或已经删除', 212);
        }else if (!$detail['company_id']) {
            $this->err->add('装修宝还未确认公司', 213);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可投诉", 211)->response();
        }else if(!$plaint = K::M('zxb/plaint')->items(array('zxb_id'=>$zxb_id))){
            $this->err->add("您还没有投诉内容", 214);
        }else{
                
			$this->pagedata['zxb_id'] = $zxb_id;
			$this->pagedata['plaint'] = $plaint;
			$this->tmpl = 'ucenter/member/zxb/plaint.html';           
        }
	}

	public function plaintedit($plaint_id)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}elseif(!($plaint_id = (int) $plaint_id) && !($plaint_id = (int)$this->GP('plaint_id'))) {
            $this->error(404);;
        }else if(!$plaint = K::M('zxb/plaint')->detail($plaint_id)){
            $this->err->add("您还没有投诉内容", 214);
        }else if (!$detail = K::M('zxb/zxb')->detail($plaint['zxb_id'])) {
            $this->err->add('装修宝不存在或已经删除', 212);
        }else if (!$detail['company_id']) {
            $this->err->add('装修宝还未确认公司', 213);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中", 211)->response();
        }else{
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
					$data['yezhu_time'] = __TIME;
                    if($yuyue_id = K::M('zxb/plaint')->update($plaint_id,$data)){
                        $this->err->add('投诉修改成功');
                    }
                } 
            }else{             
                $this->pagedata['zxb_id'] = $zxb_id;
				$this->pagedata['plaint'] = $plaint;
                $this->tmpl = 'ucenter/member/zxb/plaintedit.html';
            }            
		}
	}
}