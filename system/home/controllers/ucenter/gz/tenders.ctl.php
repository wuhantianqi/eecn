<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Gz_Tenders extends Ctl_Ucenter
{
    
    public function index($page=1)
    {
        $gz = $this->ucenter_gz();
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['city_id'] = $gz['city_id'];
        $filter['status'] = 0;
        $filter['audit'] = 1;
        $tenders_ids =  array();
        if($items = K::M('tenders/tenders')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
        }
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/gz/tenders/items.html';
    }

    public function detail($tenders_id=null)
    {
        $gz = $this->ucenter_gz();
        $audit_tenders = K::M('system/audit')->gz('tenders', $gz, $audit_title);
        $pager = array('audit_tenders'=>$audit_tenders, 'audit_title'=>$audit_title);
        if(!$tenders_id = (int)$tenders_id){
            $this->error(404);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('招标不存在或已经删除', 211);
        }else if(empty($detail['audit'])){
            $this->err->add('招标还在审核中，不可查看', 211);
        }else{
            K::M('tenders/tenders')->update_count($tenders_id,'pv_num');
            if($look_list = K::M('tenders/look')->items_by_tenders($tenders_id)){
                $gz_ids = array();
                foreach($look_list as $k=>$v){
                    $gz_ids[$v['uid']] = $v['uid'];
                    if($v['uid'] == $gz['uid']){
                        $detail['looked'] = $k;
                    }
                }
                $this->pagedata['look_list'] = $look_list;
                if($gz_ids){
                    $this->pagedata['gz_list'] = K::M('gz/gz')->items_by_ids($gz_ids);
                }
            }
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/member')->member($uid);
            }
            $this->pagedata['pager'] = $pager;
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/gz/tenders/detail.html';
        }
    }

    public function looked($page=1)
    {
        $gz = $this->ucenter_gz();
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $tenders_ids = array();
        $filter['uid'] = $gz['uid'];
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
        $this->tmpl = 'ucenter/gz/tenders/looks.html';
    }

    public function tracking($look_id=null, $page=1)
    {
        $gz = $this->ucenter_gz();
        if(!$look_id = (int)$look_id){
             $this->error(404);
        }else if(!$look = K::M('tenders/look')->detail($look_id)){
            $this->err->add('您的投标不存在或已经删除', 211);
        }elseif($look['uid'] != $gz['uid']){
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
            if($track_list = K::M('tenders/track')->items(array('look_id'=>$look_id), array('tracking_id'=>'DESC'), $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $count, $this->mklink(null, array($look_id, '{page}')));
                $this->pagedata['track_list'] = $track_list; 
            }    
            if($uid = $detail['uid']){
                $this->pagedata['member'] = K::M('member/view')->detail($uid);
            }
            $this->pagedata['look_id'] = $look_id;
            $this->pagedata['look'] = $look;
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/gz/tenders/tracking.html';
        }  
    }

    public function track()
    {
        $gz = $this->ucenter_gz();
        if(!$look_id = (int)$this->GP('look_id')){
            $this->err->add('非法的数据提交', 211);
        }elseif(!$look = K::M('tenders/look')->detail($look_id)){
            $this->err->add('没有您的标', 211);
        }elseif($look['uid'] != $gz['uid']){
            $this->err->add('非法的数据提交', 211);
        }else if(!$content = $this->GP('tack_content')){
            $this->err->add('非法的数据提交', 211);
        }else{
            $data = array('content'=>$content, 'look_id'=>$look_id, 'create_ip'=>__IP, 'dateline'=>__TIME);

            if($tracking_id = K::M('tenders/track')->create($data)){
                $this->err->add('添加内容成功');
            }
        }        
    }

    public function look($tenders_id=null)
    {
        $gz = $this->ucenter_gz();      
        if(!($tenders_id = (int)$tenders_id) && !($tenders_id = (int)$this->GP('tenders_id'))){
            $this->error(404);
        }else if(($audit_tenders = K::M('system/audit')->gz('tenders', $gz, $audit_title)) < 0){
            $this->err->add('您是【'.$audit_title.'】不能进行投标', 333);
        }else if(!$detail = K::M('tenders/tenders')->detail($tenders_id)){
            $this->err->add('招标不存在或已经删除', 212);
        }else if(!$detail['audit']){
            $this->err->add('该招标还没有公布不好意思!', 212); 
        }elseif((int)$detail['status'] === 1){
            $this->err->add('该招标已经结束了!', 212); 
        }else if($detail['looks'] >= $detail['max_look']){
            $this->err->add('该招标已经结束了!', 212); 
        }else if(K::M('tenders/look')->is_looked($tenders_id, $gz['uid'])){
            $this->err->add('您已经投过标了，不需要重复投标', 213);
        }else if(($detail['gold']) && ($this->MEMBER['gold']<$detail['gold'])){
            $this->err->add('您的金币不足，请先充值', 215);
        }else if(K::M('member/group')->check_priv($this->MEMBER['group_id'], 'tenders_look') < 0){
			 $this->err->add('您是【'.$this->MEMBER['group_name'].'】没有权限投标', 211);
		}else if($data = $this->checksubmit('data')){
			if($detail['gold'] > 0){
				if(!K::M('member/gold')->update($this->uid, -$detail['gold'], "看标：".$detail['title']."(ID:{$tenders_id})")){
					$this->err->add('扣费失败', 201)->response();
				}
			}
			$data['tenders_id'] = $tenders_id;
			$data['uid'] = $gz['uid'];
			if($look_id = K::M('tenders/look')->create($data)){
				K::M('tenders/tenders')->update_count($tenders_id, 'looks');
				$this->err->add('参加竞标成功！');
			}
        }else{
            $this->pagedata['tenders'] = $detail;
            $this->tmpl = 'ucenter/gz/tenders/look.html';
        }
    }

}