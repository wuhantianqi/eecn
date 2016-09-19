<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Prize extends Ctl
{
    
    
	public function items($scratch_id,$page=1)
    {

		if(!($scratch_id = (int)$scratch_id) && !($scratch_id = $this->GP('scratch_id'))){
            $this->err->add('未指定要刮刮卡的ID', 211);
        }else if(!$detail = K::M('weixin/scratch')->detail($scratch_id)){
            $this->err->add('刮刮卡内容不存在或已经删除', 212);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
					if($SO = $this->GP('SO')){
				$pager['SO'] = $SO;
				
			}
			$filter['scratch_id'] = $scratch_id;
			if($items = K::M('weixin/prize')->items($filter, null, $page, $limit, $count)){
				$uids = '';
				foreach($items as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
				$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
			}
			$this->pagedata['items'] = $items;
			$this->pagedata['pager'] = $pager;
			$this->pagedata['detail'] = $detail;
			$this->tmpl = 'admin:weixin/prize/items.html';
		}
    }

	public function create($scratch_id)
    {
		if(!($scratch_id = (int)$scratch_id) && !($scratch_id = $this->GP('scratch_id'))){
            $this->err->add('未指定要刮刮卡的ID', 211);
        }else if(!$detail = K::M('weixin/scratch')->detail($scratch_id)){
            $this->err->add('刮刮卡内容不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
			$data['scratch_id'] = $scratch_id;
			if($_FILES['data']){
				foreach($_FILES['data'] as $k=>$v){
					foreach($v as $kk=>$vv){
						$attachs[$kk][$k] = $vv;
					}
				}
				$upload = K::M('magic/upload');
				foreach($attachs as $k=>$attach){
					if($attach['error'] == UPLOAD_ERR_OK){
						if($a = $upload->upload($attach, 'weixin')){
							$data[$k] = $a['photo'];
						}
					}
				}
			}
            if($id = K::M('weixin/prize')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?weixin/prize-items-'.$scratch_id);
            } 
		}else{
		   $this->pagedata['scratch_id'] = $scratch_id;
		   $this->tmpl = 'admin:weixin/prize/create.html';
		}
    }
    

    public function edit($id=null)
    {
        if(!($id = (int)$id) && !($id = $this->GP('id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('weixin/prize')->detail($id)){
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
						if($a = $upload->upload($attach, 'weixin')){
							$data[$k] = $a['photo'];
						}
					}
				}
			}

            if(K::M('weixin/prize')->update($id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:weixin/prize/edit.html';
        }
    }



    public function delete($id=null)
    {
        if($id = (int)$id){
            if(!$detail = K::M('weixin/prize')->detail($id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/prize')->delete($id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('id')){
            if(K::M('weixin/prize')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}