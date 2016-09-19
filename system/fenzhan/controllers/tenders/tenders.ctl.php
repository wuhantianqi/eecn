<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Tenders_Tenders extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['tenders_id']){$filter['tenders_id'] = $SO['tenders_id'];}
            if($SO['from']){$filter['from'] = $SO['from'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['title']){$filter['title'] = "LIKE:%".$SO['title']."%";}
            if($SO['contact']){$filter['contact'] = "LIKE:%".$SO['contact']."%";}
            if($SO['mobile']){$filter['mobile'] = "LIKE:%".$SO['mobile']."%";}
            if($SO['home_name']){$filter['home_name'] = "LIKE:%".$SO['home_name']."%";}
            if(is_numeric($SO['status'])){$filter['status'] = $SO['status'];}
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
            if(is_array($SO['zx_time'])){if($SO['zx_time'][0] && $SO['zx_time'][1]){$a = strtotime($SO['zx_time'][0]); $b = strtotime($SO['zx_time'][1])+86400;$filter['zx_time'] = $a."~".$b;}}
            if(is_array($SO['tx_time'])){if($SO['tx_time'][0] && $SO['tx_time'][1]){$a = strtotime($SO['tx_time'][0]); $b = strtotime($SO['tx_time'][1])+86400;$filter['tx_time'] = $a."~".$b;}}           
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
		$filter['city_id'] = CITY_ID;
        if($items = K::M('tenders/tenders')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'fenzhan:tenders/tenders/items.html';
    }

    public function so()
    {
        $this->tmpl = 'fenzhan:tenders/tenders/so.html';
    }

    public function detail($tenders_id = null)
    {
         if(!$tenders_id = (int)$tenders_id){
            $this->err->add('未指定要查看内容的ID', 211);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        }else{
            $uids = array();
            if($uid = $detail['uid']){
                $uids[$v['uid']] = $uid;
            }
            if($look_list = K::M('tenders/look')->items_by_tenders($tenders_id, 1, 200)){
                foreach($look_list as $k=>$v){
                    $uids[$v['uid']] = $v['uid'];
                }
                $this->pagedata['look_list'] = $look_list;
            }
            if($uids){
                $this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
            }
            if(!$attrs = K::M('tenders/attr')->attrs_by_tenders($tenders_id)){
                $attrs = array();
            }
            $detail['from_attr_key'] = 'tenders:'.$detail['from'];
            $detail['attrvalues'] = array_keys($attrs);       
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'fenzhan:tenders/tenders/detail.html';
        }
    }

    public function edit($tenders_id=null)
    {
        if(!($tenders_id = (int)$tenders_id) && !($tenders_id = $this->GP('tenders_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($attach = $_FILES['huxing']){
                    $upload = K::M('magic/upload');
                    if($attach['error'] == UPLOAD_ERR_OK){
                        if($a = $upload->upload($attach, 'tenders', $detail['hunxing'])){
                            $data['huxing'] = $a['photo'];
                        }
                    }
                }
				unset($data['city_id']);
                if(K::M('tenders/tenders')->update($tenders_id, $data)){
					if($detail['from'] == 'ZXB'){
						K::M('zxb/zxb')->update($detail['zxb_id'],array('audit'=>$data['audit']));
					}
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'fenzhan:tenders/tenders/edit.html';
        }
    }

    public function doaudit($tenders_id=null)
    {
        if($tenders_id = (int)$tenders_id){
            if(K::M('tenders/tenders')->batch($tenders_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('tenders_id')){
            if(K::M('tenders/tenders')->batch($ids, array('audit'=>1))){
                $this->err->add('批量审核内容成功');
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($tenders_id=null)
    {
        if($tenders_id = (int)$tenders_id){
            if(K::M('tenders/tenders')->delete($tenders_id)){
                $this->err->add('删除成功');
            }
        }else if($ids = $this->GP('tenders_id')){
            if(K::M('tenders/tenders')->delete($ids)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}