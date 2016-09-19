<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Zxb_Plaint extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['plaint_id']){$filter['plaint_id'] = $SO['plaint_id'];}
			if($SO['zxb_id']){$filter['zxb_id'] = $SO['zxb_id'];}
			if($SO['uid']){$filter['uid'] = $SO['uid'];}
        }
        if($items = K::M('zxb/plaint')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
		foreach($items as $k=>$v){
			if($v['company_id']){
				$company_ids[$v['company_id']] = $v['company_id'];
			}

			if($v['uid']){
				$uids[$v['uid']] = $v['uid'];        
			}
		   
		}             
		if(!empty($company_ids)){
			$this->pagedata['company_list'] = K::M('company/company')->items_by_ids($company_ids);
		}
		if(!empty($uids)){
			$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
		}
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'fenzhan:zxb/plaint/items.html';
    }


    public function create()
    {
        if($data = $this->checksubmit('data')){
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

            if($plaint_id = K::M('zxb/plaint')->create($data)){
                $this->err->add('添加内容成功');
                $this->err->set_data('forward', '?zxb/plaint-index.html');
            } 
        }else{
           $this->tmpl = 'fenzhan:zxb/plaint/create.html';
        }
    }

    public function edit($plaint_id=null)
    {
        if(!($plaint_id = (int)$plaint_id) && !($plaint_id = $this->GP('plaint_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('zxb/plaint')->detail($plaint_id)){
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
                    if($a = $upload->upload($attach, 'zxb')){
                        $data[$k] = $a['photo'];
                    }
                }
            }
        }
			$data['time'] = __TIME;
            if(K::M('zxb/plaint')->update($plaint_id, $data)){
                $this->err->add('修改内容成功');
            }  
        }else{
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'fenzhan:zxb/plaint/edit.html';
        }
    }


    public function delete($plaint_id=null)
    {
        if($plaint_id = (int)$plaint_id){
            if(!$detail = K::M('zxb/plaint')->detail($plaint_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('zxb/plaint')->delete($plaint_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('plaint_id')){
            if(K::M('zxb/plaint')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

}