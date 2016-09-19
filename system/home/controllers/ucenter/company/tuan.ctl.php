<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Company_Tuan extends Ctl_Ucenter
{
    public function index($page)
    {
        if($company = $this->ucenter_company()){
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 30;
			if($items = K::M('home/tuan')->items(array('company_id'=>$company['company_id']), array('ltime'=>'desc'), $page, $limit, $count)){
				$this->pagedata['items'] = $items;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('page' => '{page}')));
			}
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'ucenter/company/tuan/items.html';
        }
    }

	public function sign($tuan_id,$page)
	{
		$company = $this->ucenter_company();
        if(!$tuan_id = (int)$tuan_id){
            $this->error(404);
        }else if(!$tuan = K::M('home/tuan')->detail($tuan_id)){
            $this->err->add('该团装小区不存在或已经删除', 211);
        }else{
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 30;
			$filter['tuan_id'] = $tuan_id;
			if($items = K::M('home/sign')->items($filter, null, $page, $limit, $count)){
				 foreach($items as $k=>$v){
					$uids[] = $v['uid'];
					$items[$k]['clientip'] = $v['clientip'].'('. K::M("misc/location")->location($v['clientip']) .')';
				}
				$this->pagedata['member_list'] = K::M('member/member')->items_by_ids($uids);
				$pager['count'] = $count;
				$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('tuan_id'=>$tuan_id,'page' => '{page}')));
			}
			$this->pagedata['items'] = $items;
			$this->pagedata['pager'] = $pager;
			$this->pagedata['tuan_id'] = $tuan_id;
			$this->pagedata['tuan'] = K::M('home/tuan')->detail($tuan_id);
			$this->pagedata['package'] = K::M('home/package')->items(array('tuan_id'=>$tuan_id));
			$this->tmpl = 'ucenter/company/tuan/sign.html';
		}
	}
    
    
}