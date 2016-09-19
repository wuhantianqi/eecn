<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Company_Zxb extends Ctl_Ucenter 
{
    
    public function index($page=1)
    {
        $pager = $filter = $uids = array();
        $pager['page'] = $page = max((int)$page, 1);
        $pager['limit'] = $limit = 20;
        $pager['count'] = $count = 0;
		$company = $this->ucenter_company();
		if($company){
			$filter['company_id'] = $company['company_id'];
			if($items = K::M('zxb/zxb')->items($filter,array('zxb_id'=>'desc'), $page, $limit, $count)){
				foreach($items as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
				if($uids){
					$this->pagedata['uids'] = K::M('member/member')->items_by_ids($uids);
				}

				$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
				$this->pagedata['items'] = $items;
			}
			$this->pagedata['company'] = $company;
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'ucenter/company/zxb/items.html';
		}else{
			$this->err->add('您不是公司用户', 404);
		}
    }

	public function lists($zxb_id)
	{
		$company = $this->ucenter_company();
		if($company){
			if($items = K::M('zxb/zxb')->detail($zxb_id)){
				if(!$company_list = K::M('zxb/zxb')->items(array('company_id'=>$company['company_id'],'zxb_id'=>$zxb_id))){
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
					$tenders_look = K::M('tenders/look')->items(array('tenders_id'=>$tenders_id,'is_signed'=>'1'));
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
					$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
					$this->tmpl = 'ucenter/company/zxb/lists.html';
				}
			}else{
				$this->err->add('该装修保不存在或已被删除', 203);
			}
		}else{
			$this->err->add('您不是公司用户', 404);
		}

	}

	public function detail($zxb_id,$status)
	{
		$company = $this->ucenter_company();
		if(!$company){
			$this->err->add('您不是公司用户', 202);
		}else{
			if($items = K::M('zxb/zxb')->detail($zxb_id)){
				if(!$company_list = K::M('zxb/zxb')->items(array('company_id'=>$company['company_id']))){
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
					$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
				}
			}else{
				$this->err->add('该装修保不存在', 203);
			}
			$status_list = K::M('zxb/zxb')->get_status();
			if($status == '3'){
				$this->hetong($zxb_id,$status);
			}else if($status == '4' || $status == '5' ||$status == '6'){
				$this->step($zxb_id,$status);
			}else if($status == '7' ||$status == '8'){
				$this->endstep($zxb_id,$status);
			}else{
				$this->err->add('当前状态不正确', 211);
			}
		}
	}

	public function hetong($zxb_id,$status='3')
	{
		$company = $this->ucenter_company();
		if (!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->err->add('未指定要查看的装修保', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的装修保不存在或已经删除', 212);
        }else if($detail['company_id'] < $company['company_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else if($detail['status'] < ($status-1)){
			 $this->err->add('当前状态不正确', 214);
		}else{
			$this->system->config->get('zxb');
			if($hetong_list = K::M('zxb/hetong')->items(array('zxb_id'=>$detail['zxb_id']))){
				foreach($hetong_list as $v){
					$this->pagedata['hetong'] = $v;
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
								if($a = $upload->file($attach, 'zxb')){
									$data[$k] = $a['attach'];
								}
							}
						}
					}
					$data['company_time'] = __TIME;
					$data['company'] = $company['title'];
					$data['company_id'] = $company['company_id'];
					$data['company_status'] = '1';
					$data['zxb_id'] = $zxb_id;
					if($hetong_list){
						unset($data['zxb_id'],$data['company_id'],$data['company'],$data['company_time'],$data['company_status']);
						if(!$hetong_id = $this->GP('hetong_id')){
							$this->err->add('非法的数据提交', 201);
						}else{
							if(K::M('zxb/hetong')->update($hetong_id,$data)){
								$this->err->add('修改内容成功');
							}
						}
					}else{
						if($hetong = K::M('zxb/hetong')->create($data)){
							$this->err->add('添加内容成功');
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
				$this->tmpl = 'ucenter/company/zxb/hetong.html';
			}
        }
	}

	public function step($zxb_id,$status)
	{
		$company = $this->ucenter_company();
		if (!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->err->add('未指定要查看的装修保', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的装修保不存在或已经删除', 212);
        }else if($detail['company_id'] < $company['company_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else if($detail['status'] < ($status-1)){
			 $this->err->add('当前状态不正确', 214);
		}else{
			if($step_list = K::M('zxb/step')->items(array('zxb_id'=>$detail['zxb_id'],'company_id'=>$company['company_id'],'step'=>$status))){
				foreach($step_list as $v){
					$this->pagedata['step'] = $v;
				}
			}
			if($photo_lists = K::M('zxb/photo')->items(array('zxb_id'=>$detail['zxb_id'],'company_id'=>$detail['company_id'],'step'=>$status))){
				$this->pagedata['photo'] = $photo_lists;
			}
			if($this->checksubmit('data')){
				if(!$data = $this->GP('data')){
					$this->err->add('非法的数据提交', 201);
				}else{
					if($_FILES['files']){
						foreach($_FILES['files'] as $k=>$v){
							foreach($v as $kk=>$vv){
								$attachs[$kk][$k] = $vv;
							}
						}
						$upload = K::M('magic/upload');
						foreach($attachs as $k=>$attach){
							if($attach['error'] == UPLOAD_ERR_OK){
								if($a = $upload->file($attach, 'zxb')){
									$photo[$k] = $a['attach'];
								}
							}
						}
					}
					
					$data['company_id'] = $company['company_id'];
					$data['step'] = $status;
					$data['company_status'] = '1';
					$data['zxb_id'] = $zxb_id;
					$data['company_time'] = __TIME;
					if($step_list){
						unset($data['company_id'],$data['step'],$data['company_status'],$data['zxb_id'],$data['company_time']);
						if(!$step_id = $this->GP('step_id')){
							$this->err->add('非法的数据提交', 201);
						}else{
							if(K::M('zxb/step')->update($step_id,$data)){
								if($photo){
									$photos['company_id'] = $company['company_id'];
									$photos['step'] = $status;
									$photos['zxb_id'] = $zxb_id;
									foreach($photo as $k => $v){
										$photos['photo'] = $v;
										K::M('zxb/photo')->create($photos);
									}
									
								}
								$this->err->add('修改内容成功');
							}
						}
					}else{
						if(K::M('zxb/step')->create($data)){
							if($photo){
								$photos['company_id'] = $company['company_id'];
								$photos['step'] = $status;
								$photos['zxb_id'] = $zxb_id;
								foreach($photo as $k => $v){
									$photos['photo'] = $v;
									K::M('zxb/photo')->create($photos);
								}
							}
							$this->err->add('添加内容成功');
						}
					}
				}
			}else{
				$photo = K::M('zxb/photo')->items(array('zxb_id'=>$zxb_id,'company_id'=>$detail['company_id'],'step'=>$status));
				$this->pagedata['photo'] = $photo;
				$this->pagedata['now_status'] = $status;
				$this->tmpl = 'ucenter/company/zxb/detail.html';
			}
        }
		
	}

	public function endstep($zxb_id,$status)
	{
		$company = $this->ucenter_company();
		if (!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
            $this->err->add('未指定要查看的装修保', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要查看的装修保不存在或已经删除', 212);
        }else if($detail['company_id'] < $company['company_id']){
            $this->err->add('您没有权限查看该预约', 213);
        }else if($detail['status'] < ($status-1)){
			 $this->err->add('当前状态不正确', 214);
		}else{
			if($step_lists = K::M('zxb/step')->items(array('zxb_id'=>$detail['zxb_id'],'step'=>$status))){
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
								if($a = $upload->file($attach, 'zxb')){
									$data[$k] = $a['attach'];
								}
							}
						}
					}
					$data['company_id'] = $company['company_id'];
					$data['company_status'] = '1';
					$data['company_time'] = __TIME;
					if($step_list['uid']){
						unset($data['company_id'],$data['step'],$data['company_status'],$data['zxb_id'],$data['company_time']);
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
				$this->tmpl = 'ucenter/company/zxb/endstep.html';
			}
        }
	} 

	public function plaint($zxb_id)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}else if(!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
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
                    $data['uid'] = $detail['uid'];
                    $data['company_id'] = $detail['company_id'];
					$data['zxb_id'] = $detail['zxb_id'];
                    $data['type'] = "2";
					$data['company_time'] = __TIME;
					
                    if($yuyue_id = K::M('zxb/plaint')->create($data)){
                        $this->err->add('投诉发布成功');  
                    }
                } 
            }else{             
                $this->pagedata['zxb_id'] = $zxb_id;
                $this->tmpl = 'ucenter/company/zxb/look.html';
            }            
        }
    }    

	public function plaintlists($zxb_id)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}else if(!($zxb_id = (int) $zxb_id) && !($zxb_id = (int)$this->GP('zxb_id'))) {
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
			$this->tmpl = 'ucenter/company/zxb/plaint.html';         
        }
	}

	public function plaintedit($plaint_id)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}else if(!($plaint_id = (int) $plaint_id) && !($plaint_id = (int)$this->GP('plaint_id'))) {
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
					$data['company_time'] = __TIME;
                    if($yuyue_id = K::M('zxb/plaint')->update($plaint_id,$data)){
                        $this->err->add('投诉修改成功');
                    }
                } 
            }else{
                $this->pagedata['zxb_id'] = $zxb_id;
				$this->pagedata['plaint'] = $plaint;
                $this->tmpl = 'ucenter/company/zxb/plaintedit.html';
            }            
		}
	}
}

