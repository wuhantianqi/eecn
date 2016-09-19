<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Zxb_Step extends Ctl
{
    
    public function index($zxb_id=null)
    {
		if(!($zxb_id = (int)$zxb_id) && !($zxb_id = $this->GP('zxb_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('zxb/zxb')->detail($zxb_id)){
            $this->err->add('您要要查看的内容不存在或已经删除', 212);
		}else{
			$filter['zxb_id'] = $zxb_id;
			$hetong = K::M('zxb/hetong')->items($filter);
			foreach($hetong as $k => $v){
				$item = $v;
			}
			$this->pagedata['hetong'] = $item;
			$tenders = K::M('tenders/tenders')->items($filter);
			foreach($tenders as $k => $v){
				$tenders_id = $v['tenders_id'];
			}
			$tenders_look = K::M('tenders/look')->items(array('tenders_id'=>$tenders_id,'is_signed'=>'1'));
			
			foreach($tenders_look as $k => $v){
				$this->pagedata['tenders_look'] = $v;
			}
			$step = K::M('zxb/step')->items($filter);
			foreach($step as $k => $v){
				
				$steps[$v['step']] = $v;
				if($v['step']>3 && $v['step']<7){
					if($photo_lists =K::M('zxb/photo')->items(array('zxb_id'=>$detail['zxb_id'],'company_id'=>$detail['company_id'],'step'=>$v['step']))){
						$steps[$v['step']]['photo'] = $photo_lists;
					}
				}
			}
			$this->pagedata['step'] = $steps;
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
			$this->pagedata['company'] = K::M('company/company')->detail($detail['company_id']);
			$this->pagedata['member'] = K::M('member/member')->detail($detail['uid']);
			$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
			$this->pagedata['detail'] = $detail;
			$this->pagedata['items'] = $items;
			$this->tmpl = 'admin:zxb/step/items.html';
		}
		
    }

    public function edit($step_id=null)
    {
        if(!($step_id = (int)$step_id) && !($step_id = $this->GP('step_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('zxb/step')->detail($step_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$items = K::M('zxb/zxb')->detail($detail['zxb_id'])){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
			if($detail['step']>=3 && $detail['step']<=7){
				if($detail['yezhu_status'] != 1){
						$this->err->add('装修公司和业主未审核通过 请等待审核', 216);
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
								if($a = $upload->upload($attach, 'step')){
									$data[$k] = $a['photo'];
								}
							}
						}
					}
					$data['time'] = __TIME;
					if($detail['step'] == '7'){
						$data['company_status'] = 1;
					}
					
					if(K::M('zxb/step')->update($step_id, $data)){
						
						if($items['status']<$detail['step']){
							if($detail['step'] == '7'){
								K::M('zxb/zxb')->update($detail['zxb_id'], array('status'=>'8'));
							}else{
								K::M('zxb/zxb')->update($detail['zxb_id'], array('status'=>$detail['step']));
							}
						}
						if($detail['step'] == '3'){
							$hetong = K::M('zxb/hetong')->items(array('zxb_id'=>$detail['zxb_id']));
							foreach($hetong as $k => $v){
								$hetong_id= $v['hetong_id'];
							}
							K::M('zxb/hetong')->update($hetong_id, array('status'=>'1'));
						}
						$this->err->add('修改内容成功');
					} 
				}
			}else{
				$this->err->add('您要修改的内容状态不正确', 215);
			}

        }else{
			$this->pagedata['status'] = K::M('zxb/zxb')->get_status();
			$this->pagedata['detail'] = $detail;
			$this->pagedata['items'] = $items;
			$this->pagedata['company'] = K::M('company/company')->detail($detail['company_id']);
			$this->pagedata['member'] = K::M('member/member')->detail($detail['uid']);	
        	$this->tmpl = 'admin:zxb/step/edit.html';
        }
    }

}