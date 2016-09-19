<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Designer extends Ctl_Ucenter 
{
    protected $_allow_fields = 'company_id,city_id,area_id,school,qq,about,name,slogan,mobile';

    public function index()
    {
        $this->info();
    }

    public function info()
    {
        $designer = $this->ucenter_designer();
        if($data = $this->checksubmit('data')){
            if(!$data = $this->check_fields($data, $this->_allow_fields)){
                $this->err->add('非法的数据提交', 211);
            }else{
                if(!$designer['designer_id']){
					$data['uid'] = $this->uid;
					if($group = K::M('member/group')->default_group('designer')){
						$data['group_id'] = $group['group_id'];
					}
                    if ($designer_id = K::M('designer/designer')->create($data)) {
						K::M('member/member')->update($designer['uid'],array('group_id'=>$data['group_id']));
                        if ($attr = $this->GP('attr')) {
                            K::M('designer/attr')->update($designer_id, $attr);
                        }
                    }
                }else if (K::M('designer/designer')->update($designer['uid'], $data)) {
                    if ($attr = $this->GP('attr')) {
                        K::M('designer/attr')->update($designer['uid'], $attr);
                    }
                }
                $this->err->add('设计师资料设置成功');
            }
        }else{
            if($company_id = $designer['company_id']){
                $this->pagedata['company'] = K::M('company/company')->detail($company_id);
            }
            $this->tmpl = 'ucenter/designer/info.html';
        }        
    }

    public function attr()
    {
        $designer = $this->ucenter_designer();
        if($attr = $this->checksubmit('attr')){
            K::M('designer/attr')->update($this->uid, $attr);
            $this->err->add('设计师属性设置成功');
        }else{
            $this->pagedata['attr'] = K::M('designer/attr')->attrs_ids_by_designer($this->uid);
            $this->tmpl = 'ucenter/designer/attr.html';
        }         
    }

	public function refresh()
    {
		$designer = $this->ucenter_designer();
		$integral = K::$system->config->get('integral');
		$counts = K::M('member/flush')->flushs($this->uid);
		$is_gold = abs($integral['gold']);
		if($counts >= $designer["group"]["priv"]["day_free_count"]){
			$this->pagedata['gold'] = $is_gold;
		}
		$this->pagedata['is_refresh'] = $counts;
		$this->pagedata['counts'] = $designer["group"]["priv"]["day_free_count"];
		if($this->GP('fromid')){
			$isrefresh = true;
			if($counts >= $designer["group"]["priv"]["day_free_count"]){
				if($this->MEMBER['gold']<$is_gold){
					$isrefresh = false;
					$this->err->add('您的金币余额不足，请先充值', 215);
				}
			}
			$data['gold'] = '0';
			if($isrefresh && $counts >= $designer["group"]["priv"]["day_free_count"]){
				$data['gold'] = $is_gold;
				if($is_gold > 0){
                    if(!K::M('member/gold')->update($this->uid, -$is_gold, "刷新设计师")){
						$isrefresh = false;
                        $this->err->add('扣费失败', 201)->response();
                    }
                }
			}
			$data['uid'] = $this->uid;$data['from'] = 'designer';$data['itemId'] = $designer['uid'];
			if($isrefresh && K::M('member/flush')->create($data)){
				K::M('designer/designer')->update($designer['uid'], array('flushtime'=>__TIME));
				$this->err->add('刷新成功');
			}

		}else{
			$this->pagedata['fromid'] = $designer['uid'];		
			$this->tmpl = 'ucenter/designer/refresh/look.html';
		}
	}

}