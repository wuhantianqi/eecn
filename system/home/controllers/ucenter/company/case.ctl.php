<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class Ctl_Ucenter_Company_Case extends Ctl_Ucenter
{
    
    protected $_allower_fields = 'home_id,home_name,huxing_id,uid,title,intro,seo_title,seo_keywords,seo_description';

    public function index($page = 1)
    {
        $company = $this->ucenter_company();
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 20;
        $filter['company_id'] = $company['company_id'];
        $filter['closed'] = 0;
        if ($items = K::M('case/case')->items($filter, null, $page, $limit, $count)) {
            $pager['count'] = $count;
            $home_ids = $designer_ids = array();
            foreach ($items as $k => $v) {
                if ($v['home_id']) {
                    $home_ids[$v['home_id']] = $v['home_id'];
                }
                if ($v['designer_id']) {
                    $designer_ids[$v['designer_id']] = $v['designer_id'];
                }
            }
            if (!empty($home_ids)) {
                $this->pagedata['home_list'] = K::M('home/home')->items_by_ids($home_ids);
            }
            if (!empty($designer_ids)) {
                $this->pagedata['designer_list'] = K::M('designer/designer')->items_by_ids($designer_ids);
            }
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}'), array(), true), array());
            $this->pagedata['items'] = $items;
        }        
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'ucenter/company/case/items.html';
    }

    public function create()
    {
        $company = $this->ucenter_company();
        $allow_case = K::M('member/group')->check_priv($company['group_id'], 'allow_case');
        if(K::M('member/integral')->check('case',  $this->MEMBER) === false){
            $this->err->add('很抱歉您的账户余额不足！', 201);
        }else if($data = $this->checksubmit('data')) {
            if (!$data = $this->check_fields($data, $this->_allower_fields)) {
                $this->err->add('非法的数据提交', 201);
            } else {
                $data['company_id'] = $company['company_id'];
                $data['city_id'] = $company['city_id'];
                $data['audit'] = $allow_case;
				if($_FILES['data']['name']['huxing']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'home')){
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }else{
					if($photo = K::M('home/photo')->detail($data['huxing_id'])){
						$data['huxing'] = $photo['photo'];
					}
				}
                if ($case_id = K::M('case/case')->create($data)) {
                    if (!$attr = $this->GP('attr')) {
                        $attr = array();
                    }
                    K::M('case/attr')->update($case_id, $attr);
                    K::M('company/company')->update_count($company_id, 'case_num', 1);
                    if ($home_id = (int) $data['home_id']) {
                        K::M('home/home')->update_count($home_id, 'case_num', 1);
                    }
                    if($home_id && $company_id){
                         K::M('home/home')->update($home_id, array('last_case_company_id'=>$company_id));
                    }                    
                    $this->err->add('添加案例成功');
                    $this->err->set_data('forward', $this->mklink('ucenter/company/case:detail', array($case_id), array(), true));
                }
            }
        } else {
            $this->pagedata['designers'] = K::M('designer/designer')->items(array('company_id'=>$company['company_id']), null, 1, 200);
            $pager = array('audit_case'=>$audit_case, 'audit_title'=>$audit_title);
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/company/case/create.html';
        }
    }

    public function edit($case_id = null)
    {
        $company = $this->ucenter_company();
        if (!($case_id = (int) $case_id) && !($case_id = (int)$this->GP('case_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('case/case')->detail($case_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } elseif ($detail['company_id'] != $company['company_id']) {
            $this->err->add('不许越权管理别人的内容', 212);
        } else if ($data = $this->checksubmit('data')) {
            if (!$data = $this->check_fields($data,  $this->_allower_fields)) {
                $this->err->add('非法的数据提交', 201);
            } else {
				unset($data['city_id']);
				if($_FILES['data']['name']['huxing']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'home')){
								if($a['photo']){
									$data[$k] = $a['photo'];
									$data['huxing_id'] = '0';
								}
                            }
                        }
                    }
                }else{
					if($photo = K::M('home/photo')->detail($data['huxing_id'])){
						$data['huxing'] = $photo['photo'];
					}
				}
                if (K::M('case/case')->update($case_id, $data)) {
                    if($detail['home_id'] != $data['home_id']){
                        if($home_id = (int) $data['home_id']){
                            K::M('home/home')->update_count($home_id, 'case_num', 1);
                        }
                        if($home_id = (int)$detail['home_id']){
                             K::M('home/home')->update_count($home_id, 'case_num', -1);
                        }
                    }
                    if (!$attr = $this->GP('attr')) {
                        $attr = array();
                    }
                    K::M('case/attr')->update($case_id, $attr);
                    $this->err->add('修改内容成功');
                }
            }
        } else {
            if ($attrs = K::M('case/attr')->attrs_by_case($case_id)) {
                $this->pagedata['attrs'] = $attrs;
                $detail['attrvalues'] = array_keys($attrs);
            }
            if ($home_id = (int) $detail['home_id']) {
                $this->pagedata['home'] = K::M('home/home')->detail($home_id);
            }
            if ($huxing_id = (int) $detail['huxing_id']) {
                $this->pagedata['huxing'] = K::M('home/photo')->detail($huxing_id);
            }
            $this->pagedata['designers'] = K::M('designer/designer')->items(array('company_id' => $company['company_id']), array(), 1, 1000, $c);
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'ucenter/company/case/edit.html';
        }
    }

    public function detail($case_id=null, $page=1)
    {
        $company = $this->ucenter_company();
        $pager = array();
        if (!($case_id = (int) $case_id) && !($case_id = (int)$this->GP('case_id'))) {
            $this->error(404);
        } else if (!$detail = K::M('case/case')->detail($case_id)) {
            $this->err->add('您要查看的内容不存在或已经删除', 212);
        } else if ($detail['company_id'] != $company['company_id']) {
            $this->err->add('您没有权限查看该内容', 212);
        } else {
            $pager['case_id'] = $case_id;
            $pager['page'] = (int)$page;
            $pager['limit'] = $limit = 20;
            $pager['count'] = $count = 0;
            $this->pagedata['detail'] = $detail;
            if($items = K::M('case/photo')->items_by_case($case_id, $page, $limit, $count)){
                $this->pagedata['items'] = $items;
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink("ucenter/company/case:detail", array($case_id,'{page}')));
            }            
            $this->pagedata['pager'] = $pager;
            $this->tmpl = 'ucenter/company/case/detail.html';
        }        
    }

    public function upload($case_id = null)
    {
        $company = $this->ucenter_company();
        $allow_case = K::M('member/group')->check_priv($company['group_id'], 'allow_case'); 
        if($allow_case < 0){
             $this->err->add('您是【'.$company['group_name'].'】没有权限上传案例', 333);
        }else if(K::M('member/integral')->check('case',  $this->MEMBER) === false){
            $this->err->add('很抱歉您的账户余额不足！', 201);
        }else if(!$case_id = (int)$this->GP('case_id')){
            $this->err->add('非法的参数请求', 201);
        }else if(!$case = K::M('case/case')->detail($case_id)){
            $this->err->add('案例不存在或已经删除', 202);
        }elseif ($case['company_id'] != $company['company_id']) {
            $this->err->add('不许越权管理别人的内容', 212);
        } else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else{
            $attach['city_id'] = $company['city_id'];
            if($data = K::M('case/photo')->upload($case_id, $attach)){
                //发布案例第一张图的时候才判断审核！
                if($allow_case != $case['audit'] && empty($case['photos'])){
                    K::M('case/case')->update($case_id, array('audit'=>$allow_case));
                    K::M('member/integral')->commit('case', $this->MEMBER, '发布案例');
                }
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('photo', $cfg['attachurl'].'/'.$data['photo']);
                $this->err->add('上传图片成功');
            }
        }
        $this->err->json();
    }    
    
    public function update($case_id = null)
    {
        $company = $this->ucenter_company();
        if (!($case_id = (int) $case_id) && !($case_id = (int)$this->GP('case_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('case/case')->detail($case_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } elseif ($detail['company_id'] != $company['company_id']) {
            $this->err->add('不许越权管理别人的内容', 212);
        } else if ($data = $this->checksubmit('data')) {
            $photo_ids = array();
            foreach($data as $k=>$v){
                $photo_ids[$k] = $k;
            }
            if(empty($photo_ids)){
                $this->err->add('没有您要更新的内容', 212);
            }else if(!$photoinfos = K::M('case/photo')->items_by_ids($photo_ids)){
                $this->err->add('没有您要更新的内容', 212); 
            }else{
                $obj = K::M('case/photo');
                foreach($data as $k=>$v){
                    if($photoinfos[$k]['case_id'] == $case_id){
                        $obj->update($k, array('title'=>$v['title'], 'orderby'=>(int)$v['orderby']));
                    }
                }
                $this->err->add('更新成功');
            }            
        }        
    }    
    
    public function delete($case_id= null)
    {
        $company = $this->ucenter_company();
        if (!($case_id = (int) $case_id) && !($case_id = (int)$this->GP('case_id'))) {
            $this->error(404);
        }else if(!$case = K::M('case/case')->detail($case_id)){
            $this->err->add('案例不存在或已经删除', 212);
        }else if ($case['company_id'] != $company['company_id']) {
            $this->err->add('不许越权管理别人的内容', 212);
        }else if(K::M('case/case')->delete($case_id)){
            $this->err->add('删除案例成功');
        }   
    }

	public function defaultphoto($photo_id= null)
	{
		$company = $this->ucenter_company();
        if (!($photo_id = (int) $photo_id) && !($photo_id = (int)$this->GP('photo_id'))) {
            $this->error(404);
        }else if(!$detail = K::M('case/photo')->detail($photo_id)) {
            $this->err->add('效果图不存在或已经删除', 211);
        }else if(!$case = K::M('case/case')->detail($detail['case_id'])){
            $this->err->add('案例不存在或已经删除', 212);
        }else if ($case['company_id'] != $company['company_id']) {
            $this->err->add('不许越权管理别人的内容', 213);
        } else{
            if(K::M('case/case')->update($detail['case_id'],array('photo'=>$detail['photo']))){
                $this->err->add('修改封面成功');
            }
        }   
	}

    public function deletephoto($photo_id= null)
    {
        $company = $this->ucenter_company();
        if (!($photo_id = (int) $photo_id) && !($photo_id = (int)$this->GP('photo_id'))) {
            $this->error(404);
        }else if(!$detail = K::M('case/photo')->detail($photo_id)) {
            $this->err->add('效果图不存在或已经删除', 211);
        }else if(!$case = K::M('case/case')->detail($detail['case_id'])){
            $this->err->add('案例不存在或已经删除', 212);
        }else if ($case['company_id'] != $company['company_id']) {
            $this->err->add('不许越权管理别人的内容', 213);
        } else{
            if(K::M('case/photo')->delete($photo_id)){
                $this->err->add('删除效果图成功');
            }
        }   
    }    


}