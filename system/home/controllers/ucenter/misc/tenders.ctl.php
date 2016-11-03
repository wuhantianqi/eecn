<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Misc_Tenders extends Ctl_Ucenter
{
    

	public function __construct(&$system)
	{
		parent::__construct($system);
		switch($this->MEMBER['from']){
			case 'gz'		:	$gz = $this->ucenter_gz(); $city_id = $gz['city_id']; break;
			case 'designer'	:	$designer = $this->ucenter_designer(); $city_id = $designer['city_id']; break;
			case 'mechanic'	:	$mechanic = $this->ucenter_mechanic(); $city_id = $mechanic['city_id']; break;
			case 'company'	:	$company = $this->ucenter_company(); $city_id = $company['city_id']; break;
			case 'shop'		:	$shop = $this->ucenter_shop(); $city_id = $shop['city_id']; break;
		}        
		$this->city_id = $city_id;       
	}

	public function index($page=1)
	{
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['city_id'] = $this->city_id;
        $filter['status'] = array(0,1);
        $filter['audit'] = 1;
		$filter['sign_uid'] = "<:1";
        $tenders_ids =  array();
        if($items = K::M('tenders/tenders')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/misc/tenders/items.html';
	}


	public function company($page=1)
	{
		$this->tenders($page);
	}

	public function shop($page=1)
	{
		$this->tenders($page);
	}

	public function designer($page=1)
	{
		$this->tenders($page);
	}

	public function gz($page=1)
	{
		$this->tenders($page);
	}

	public function mechanic($page=1)
	{
		$this->tenders($page);
	}

	protected function tenders($page=1)
	{
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['city_id'] = $this->city_id;
        $filter['status'] = array(0,1,2);
        $filter['audit'] = 1;
        $tenders_ids =  array();
        if($items = K::M('tenders/tenders')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/misc/tenders/items.html';		
	}

    public function detail($tenders_id=null)
    {
        if(!$tenders_id = (int)$tenders_id){
            $this->error(404);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('招标不存在或已经删除', 211);
        }else if(empty($detail['audit'])){
            $this->err->add('招标还在审核中，不可查看', 211);
        }else{
            K::M('tenders/tenders')->update_count($tenders_id,'views');
            if($look_list = K::M('tenders/look')->items_by_tenders($tenders_id)){
                $uids = array();
                foreach($look_list as $k=>$v){
                    $uids[$v['uid']] = $v['uid'];
                    if($v['uid'] == $this->uid){
                        $detail['looked'] = $k;
                    }
                }
                $this->pagedata['look_list'] = $look_list;
                if($uids){
					if($member_list = K::M('member/member')->items_by_ids($uids)){
						$gz_uids = $designer_uids = $mechanic_uids = $company_uids = $shop_uids = array();
						foreach($member_list as $v){
							switch($v['from']){
								case 'company'	: $company_uids[$v['uid']]	= $v['uid']; break;
								case 'designer'	: $designer_uids[$v['uid']] = $v['uid']; break;
								case 'gz'		: $gz_uids[$v['uid']]		= $v['uid']; break;
								case 'mechanic'	: $mechanic_uids[$v['uid']] = $v['uid']; break;
								case 'shop'		: $shop_uids[$v['uid']]		= $v['uid']; break;
							}
						}
						if($gz_uids){
							$this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_uids);
						}
						if($designer_uids){
							$this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_uids);
						}
						if($mechanic_uids){
							$this->pagedata['mechanic_list'] = K::M('mechanic/mechanic')->items_by_ids($mechanic_uids);
						}
						if($company_uids){
							$this->pagedata['company_list'] = K::M('company/company')->items_by_uids($company_uids);
						}
						if($shop_uids){
							$this->pagedata['shop_list'] = K::M('shop/shop')->items_by_uids($shop_uids);
						}
						$this->pagedata['member_list'] = $member_list;
					}
                }
                $this->pagedata['look_list'] = $look_list;
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            $this->pagedata['pager'] = $pager;
            $this->pagedata['detail'] = $detail;
            K::M('tenders/tenders')->update_count($tenders_id, 'views');
            $this->tmpl = 'ucenter/misc/tenders/detail.html';
        }
    }

    public function looked($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $tenders_ids = array();
        $filter['uid'] = $this->uid;
        if($items = K::M('tenders/look')->items($filter, null, $page, $limit, $count)){
            foreach($items as $k=>$v){
                $tenders_ids[$v['tenders_id']] = $v['tenders_id'];
            }
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            if($tenders_ids){
                $this->pagedata['tenders_list'] = K::M('tenders/tenders')->items_by_ids($tenders_ids);
            }
        }
        
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/misc/tenders/looks.html';
    }

    public function track($look_id=null, $page=1)
    {
        if(!$look_id = (int)$look_id){
             $this->error(404);
        }else if(!$look = K::M('tenders/look')->detail($look_id)){
            $this->err->add('您的投标不存在或已经删除', 211);
        }elseif($look['uid'] != $this->uid){
            $this->err->add('非法操作，你没有权限查看该标', 212);
        }else if(!$detail = K::M('tenders/tenders')->detail($look['tenders_id'])){
            $this->err->add('该招标数据不存在！可能由管理员删除', 213);
        }else if(empty($detail['audit'])){
             $this->err->add('该招标已经进入待审中，暂时不能查看', 214);
        }else{
            if($home_id = (int)$detail['home_id']){
                $this->pagedata['home'] = K::M('home/home')->detail($home_id);
            }
            $this->pagedata['detail'] = $detail;
            $pager = array();
            $pager['page'] = $page = max((int)$page, 1);
            $pager['limit'] = $limit = 10;
            $pager['count'] = $count = 0;
            if($track_list = K::M('tenders/track')->items(array('look_id'=>$look_id), array('track_id'=>'DESC'), $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $count, $this->mklink(null, array($look_id, '{page}')));
                $this->pagedata['track_list'] = $track_list; 
            }
            $this->pagedata['look_id'] = $look_id;
            $this->pagedata['look'] = $look;
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/misc/tenders/track.html';
        }  
    }

    public function comment()
    {
        if(!$look_id = (int)$this->GP('look_id')){
            $this->err->add('非法的数据提交', 211);
        }elseif(!$look = K::M('tenders/look')->detail($look_id)){
            $this->err->add('没有您的标', 211);
        }elseif($look['uid'] != $this->uid){
            $this->err->add('非法的数据提交', 211);
        }else if(!$content = $this->GP('tack_content')){
            $this->err->add('非法的数据提交', 211);
        }else{
            // 设置反馈数为1
            $dats['tracks']=1;
            $data = array('content'=>$content, 'look_id'=>$look_id);
            if($tracking_id = K::M('tenders/track')->create($data)){
                // 更新招标信息里面的反馈数
                if($tracks=K::M('tenders/tenders')->update($look['tenders_id'], $dats)){
                    $this->err->add('添加内容成功');
                }
                
            }
        }        
    }
    public function look($tenders_id=null)
    {
        if(!($tenders_id = (int)$tenders_id) && !($tenders_id = (int)$this->GP('tenders_id'))){
            $this->error(404);
        }else if(!$tenders = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('招标不存在或已经删除', 212);
        }else if(($tenders_look = K::M('member/group')->check_priv($this->MEMBER['group_id'], 'tenders_look')) < 0){
            $this->err->add('您是【'.$this->MEMBER['group_name'].'】不能进行投标', 333);
        }else if(!$tenders['audit']){
            $this->err->add('该招标还没有公布不好意思!', 212); 
        }else if($tenders['status'] < 0){
            $this->err->add('该招标已经作废，不可投标!', 212); 
        }else if($tenders['sign_uid']){
            $this->err->add('该招标已经结束了!', 212); 
        }else if($tenders['looks'] >= $tenders['max_look']){
            $this->err->add('该招标已经结束了!', 212); 
        }else if(K::M('tenders/look')->is_looked($tenders_id, $this->uid)){
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
				$datas = K::M('tenders/look')->getdata($this->uid);
				$datas['tenders_id'] = $tenders_id;
				$datas['uid'] = $this->uid;
				$datas['content'] = $content;
                if($look_id = K::M('tenders/look')->create($datas)){
                    K::M('tenders/tenders')->update_count($tenders_id, 'looks');
                    switch ($this->MEMBER['from']) {
                        case 'gz':
                            K::M('gz/gz')->update_count($this->uid, 'tenders_num'); break;
                        case 'designer':
                            K::M('designer/designer')->update_count($this->uid, 'tenders_num'); break;
                        case 'mechanic':
                            K::M('mechanic/mechanic')->update_count($this->uid, 'tenders_num'); break;
                        case 'company':
                            K::M('company/company')->update_count($this->company['company_id'], 'tenders_num'); break;
                        case 'shop':
                            K::M('shop/shop')->update_count($this->shop['shop_id'], 'tenders_num'); break;
                    }
                    $this->err->add('参加竞标成功！');
                }
            }
        }else{
            $this->pagedata['tenders'] = $tenders;
            $this->tmpl = 'ucenter/misc/tenders/look.html';
        }
    }

}