<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: company.ctl.php 10346 2015-05-20 11:01:02Z xiaorui $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Company extends Ctl_Ucenter 
{

    protected $_allow_fields = 'city_id,area_id,title,name,slogan,contact,phone,addr,qq,mobile,lng,lat,video';   
    public function index()
    {
        $company = $this->ucenter_company();
        $this->pagedata['youhui_count'] = K::M('company/youhui')->count(array('company_id'=>$company['company_id']));
        $this->pagedata['youhui_sign_count'] = K::M('company/sign')->count(array('company_id'=>$company['company_id']));        
        $this->tmpl = 'ucenter/company/index.html';
    }

	public function refresh()
    {
		$company = $this->ucenter_company();
		$integral = K::$system->config->get('integral');
		$counts = K::M('member/flush')->flushs($this->uid);
		$is_gold = abs($integral['gold']);
		if($counts >= $company["group"]["priv"]["day_free_count"]){
			$this->pagedata['gold'] = $is_gold;
		}
		$this->pagedata['is_refresh'] = $counts;
		$this->pagedata['counts'] = $company["group"]["priv"]["day_free_count"];
		if($this->GP('fromid')){
			$isrefresh = true;
			if($counts >= $company["group"]["priv"]["day_free_count"]){
				if($this->MEMBER['gold']<$is_gold){
					$isrefresh = false;
					$this->err->add('您的金币余额不足，请先充值', 215);
				}
			}
			$data['gold'] = '0';
			if($isrefresh && $counts >= $company["group"]["priv"]["day_free_count"]){
				$data['gold'] = $is_gold;
				if($is_gold > 0){
                    if(!K::M('member/gold')->update($this->uid, -$is_gold, "刷新公司")){
						$isrefresh = false;
                        $this->err->add('扣费失败', 201)->response();
                    }
                }
			}
			$data['uid'] = $this->uid;$data['from'] = 'company';$data['itemId'] = $company['company_id'];
			if($isrefresh && K::M('member/flush')->create($data)){
				K::M('company/company')->update($company['company_id'], array('flushtime'=>__TIME));
				$this->err->add('刷新成功');
			}

		}else{
			$this->pagedata['fromid'] = $company['company_id'];	
			$this->tmpl = 'ucenter/company/refresh/look.html';
		}
	}

    public function info()
    {
        $company = $this->ucenter_company();
        if($data = $this->checksubmit('data')){
            if (!$data = $this->check_fields($data, $this->_allow_fields)) {
                $this->err->add('非法的数据提交', 201);
            } else {
                if ($_FILES['data']) {
                    foreach ($_FILES['data'] as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $cfg = K::$system->config->get('attach');
                    $oImg = K::M('image/gd');
                    $upload = K::M('magic/upload');
                    foreach ($attachs as $k => $attach) {
                        if ($attach['error'] == UPLOAD_ERR_OK) {
                            if ($a = $upload->upload($attach, 'company')) {
                                $data[$k] = $a['photo'];
                                if ($k === 'logo') {
                                    $size['photo'] = $cfg['company']['logo'] ? $cfg['company']['logo'] : '200X100';
                                } else {
                                    $size['photo'] = $cfg['company']['thumb'] ? $cfg['company']['thumb'] : '300X300';
                                }
                                $oImg->thumbs($a['file'], array($size['photo'] => $a['file']));
                            }
                        }
                    }
                }
                if(!$company['company_id']){
                    $data['uid'] = $this->uid;
                    if($group = K::M('member/group')->default_group('company')){
                        $data['group_id'] = $group['group_id'];
                    }
                    if ($company_id = K::M('company/company')->create($data)) {
						K::M('member/member')->update($this->uid,array('group_id'=>$data['group_id']));
                        if ($attr = $this->GP('attr')) {
                            K::M('company/attr')->update($company_id, $attr);
                        }
                        if ($fields = $this->GP('fields')) {
                            if($fields = $this->check_fields($fields, array('info'))) {
                                K::M('company/fields')->update($company_id, $fields);
                            }
                        }
                        $this->err->add('设置公司资料成功');
                    }
                }else if (K::M('company/company')->update($company['company_id'], $data)) {
                    if ($attr = $this->GP('attr')) {
                        K::M('company/attr')->update($company['company_id'], $attr);
                    }
                    if ($fields = $this->GP('fields')) {
                        if($fields = $this->check_fields($fields, array('info'))) {
                            K::M('company/fields')->update($company['company_id'], $fields);
                        }
                    }
                    $this->err->add('设置公司资料成功');
                }                
            }
        }else{
            if($attrs = K::M('company/attr')->attrs_by_company($company['company_id'])){
                $this->pagedata['attr_values'] = array_keys($attrs);
            }
            $this->tmpl = 'ucenter/company/info.html';
        }
    }
	
    public function skin()
    {
        $company = $this->ucenter_company();
        $allow_skin = K::M('member/group')->check_priv($company['group_id'], 'allow_skin', $group_name);
        $skins = include(__CFG::TMPL_DIR.'default/company/config.php');   
        if($skin = $this->checksubmit('skin')){
            if($audit_skin < 0){
                $this->err->add('您是【'.$audit_title.'】没有权限更换模板', 333);
            }else if(!$cfg = $skins[$skin]){
                $this->err->add('选择的模板不存在', 211);
            }else if(K::M('company/fields')->update($company['company_id'], array('skin'=>$skin), true)){
                $this->err->add('修改公司模板成功');
            }
        }else{
            $pager = array('allow_skin'=>$allow_skin);
            $this->pagedata['pager'] = $pager;
            $this->pagedata['skins'] = $skins;
            $this->tmpl = 'ucenter/company/skin.html';
        }    
    }

}