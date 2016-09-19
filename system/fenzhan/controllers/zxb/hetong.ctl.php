<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Zxb_Hetong extends Ctl
{
    

    public function edit($hetong_id=null)
    {
        if(!($hetong_id = (int)$hetong_id) && !($hetong_id = $this->GP('hetong_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('zxb/hetong')->detail($hetong_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$items = K::M('zxb/zxb')->detail($detail['zxb_id'])){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($items['city_id'] != CITY_ID){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
			if($_FILES['data']){
				foreach($_FILES['data'] as $k=>$v){
					foreach($v as $kk=>$vv){
						$attachs[$kk][$k] = $vv;
					}
				}
				$upload = K::M('magic/upload');
				foreach($attachs as $k=>$attach){
					if($attach['error'] == UPLOAD_ERR_OK){
						if($a = $upload->file($attach, 'zxb/hetong')){
							$data[$k] = $a['attach'];
						}
					}
				}
			}
			unset($data['dateline']);
			if(K::M('zxb/hetong')->update($hetong_id, $data)){
				
				if($data['status'] == '1' && $items['status']<3){
					K::M('zxb/zxb')->update($detail['zxb_id'],array('status'=>'3'));
				}
				if($step = $this->checksubmit('step')){
					$step['status'] = $data['status'];
					if($_FILES['step']){
						foreach($_FILES['step'] as $k=>$v){
							foreach($v as $kk=>$vv){
								$attachs[$kk][$k] = $vv;
							}
						}
						$upload = K::M('magic/upload');
						foreach($attachs as $k=>$attach){
							if($attach['error'] == UPLOAD_ERR_OK){
								if($a = $upload->upload($attach, 'zxb/hetong')){
									$step[$k] = $a['photo'];
								}
							}
						}
					}
					
					$step_list = K::M('zxb/step')->items(array('zxb_id'=>$detail['zxb_id'],'step' => 3));
					foreach($step_list as $k => $v){
						$step_id = $v['step_id'];
					}
					
					K::M('zxb/step')->update($step_id,$step);
				}
                $this->err->add('修改内容成功');
            } 
        }else{
			$step =K::M('zxb/step')->items(array('zxb_id'=>$detail['zxb_id'],'step'=>3));
			foreach($step as $k => $v){
				$this->pagedata['step'] = $v;
			}
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'fenzhan:zxb/hetong/edit.html';
        }
    }

}