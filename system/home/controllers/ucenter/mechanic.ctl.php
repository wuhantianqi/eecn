<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Mechanic extends Ctl_Ucenter 
{

    protected $_allow_fields = 'city_id,area_id,mobile,qq,about,name';

    public function index()
    {
        $this->info();
    }
    
    public function info()
    {
        $mechanic = $this->ucenter_mechanic();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{ 
				if(!$mechanic['mechanic_id']){
                    $data['uid'] = $this->uid;
                    if($group = K::M('member/group')->default_group('mechanic')){
                        $data['group_id'] = $group['group_id'];
                    }
					
					if ($mechanic_id = K::M('mechanic/mechanic')->update($mechanic['uid'], $data)) {
						K::M('member/member')->update($mechanic['uid'],array('group_id'=>$data['group_id']));
                    }
                }else{
                    K::M('mechanic/mechanic')->update($this->uid, $data);
                }
                if ($attr = $this->GP('attr')) {
                    K::M('mechanic/attr')->update($this->uid, $attr);
                }
                $this->err->add('资料设置成功');
            }
        }else{
            $this->tmpl = 'ucenter/mechanic/info.html';
        }
    }

	public function refresh()
    {
		$mechanic = $this->ucenter_mechanic();
		$integral = K::$system->config->get('integral');
		$counts = K::M('member/flush')->flushs($this->uid);
		$is_gold = abs($integral['gold']);
		if($counts >= $mechanic["group"]["priv"]["day_free_count"]){
			$this->pagedata['gold'] = $is_gold;
		}
		$this->pagedata['is_refresh'] = $counts;
		$this->pagedata['counts'] = $mechanic["group"]["priv"]["day_free_count"];
		if($this->GP('fromid')){
			$isrefresh = true;
			if($counts >= $mechanic["group"]["priv"]["day_free_count"]){
				if($this->MEMBER['gold']<$is_gold){
					$isrefresh = false;
					$this->err->add('您的金币余额不足，请先充值', 215);
				}
			}
			$data['gold'] = '0';
			if($isrefresh && $counts >= $mechanic["group"]["priv"]["day_free_count"]){
				$data['gold'] = $is_gold;
				if($is_gold > 0){
                    if(!K::M('member/gold')->update($this->uid, -$is_gold, "刷新技工")){
						$isrefresh = false;
                        $this->err->add('扣费失败', 201)->response();
                    }
                }
			}
			$data['uid'] = $this->uid;$data['from'] = 'mechanic';$data['itemId'] = $mechanic['uid'];
			if($isrefresh && K::M('member/flush')->create($data)){
				K::M('mechanic/mechanic')->update($mechanic['uid'], array('flushtime'=>__TIME));
				$this->err->add('刷新成功');
			}

		}else{
			$this->pagedata['fromid'] = $mechanic['uid'];		
			$this->tmpl = 'ucenter/mechanic/refresh/look.html';
		}
	}

    public function attr()
    {
        $mechanic = $this->ucenter_mechanic();
        if($attr = $this->checksubmit('attr')){
            K::M('mechanic/attr')->update($this->uid, $attr);
            $this->err->add('属性设置成功');
        }else{
            $this->pagedata['attr'] = K::M('mechanic/attr')->attrs_ids_by_mechanic($this->uid);
            $this->tmpl = 'ucenter/mechanic/attr.html';
        } 
    }

}