<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Mobile_Designer extends Ctl_Mobile
{
	public $_call = 'index';
	private $_action = array('items','detail','cases','article','article_info','yuyue');

	public function index()
    {
        $uri = str_replace('.html', '', str_replace('mobile/designer-', '', $this->request['uri']));
		
        if (empty($uri)) {
            $this->err->add('该设计师不存在', 211);
        } else {
			$url = explode('-',$uri);
			if(is_numeric($url[0])){
				$this->detail($url[0]);
			}else{
				if(in_array($url[0],$this->_action)){
					$this->$url[0]($url[1]);
				}else{
					$this->items($page=1);
				}
				
			}
        }
    }

    public function items($page=1)
    {
        $url = array();
		if($length = strpos($this->request['uri'],'&')){
            $this->request['uri'] = substr($this->request['uri'],0,$length);
        }
        $attr_values = K::M('data/attr')->attrs_by_from('zx:designer');
        $http_key = $attr_keys = array();
        $http_key['area_id'] = 'area_id';
        foreach ($attr_values as $key => $value) {
            $http_key['attr' . $key] = 'attr' . $key;
        }
        $http_key['page'] = 'page';
        $num = count($http_key);
        if(preg_match('/([\/a-zA-Z\-0-9])+/', $this->request['uri'], $match)){
			 $uri = explode('-',$match[0]);
		}
        foreach ($uri as $k => $v) {
            if (!is_numeric($v)) {
                unset($uri[$k]);
            }
        }
        if (count($uri) > $num) {
            $uri = array_slice($uri, 0, $num);
        }else{
            $uri = array_pad($uri, $num, 0);
        }
        $url = array_combine($http_key, $uri);
        $page = empty($url['page']) ? 1 : (int) $url['page'];
        $attr = array();
        foreach ($attr_values as $key => $value) {
            $attr_values[$key]['link'] = $this->mklink('mobile/designer:items', array_merge($url, array('attr' . $key => 0)));
            if (empty($url['attr' . $key]))
                $attr_values[$key]['checked'] = true;
            foreach ($value['values'] as $k => $v) {
                $attr_values[$key]['values'][$k]['link'] = $this->mklink('mobile/designer:items', array_merge($url, array('attr' . $key => $k)));
                if (!empty($url['attr' . $key]) && $url['attr' . $key] == $k) {
                    $attr[] = $k;
                    $attr_values[$key]['values'][$k]['checked'] = true;
                }
            }
        }
        $filter = $pager = array();
        $pager['page'] = $page = max(intval($page), 1);
        $pager['limit'] = $limit = 5;
        $filter['audit'] = 1;
        $filter['closed'] = 0;
		
        if($area_id = (int)$url['area_id']){
            $filter['area_id'] = $area_id;
        }else{
            $filter['city_id'] = $this->request['city_id'];
        }
		if ($kw = $this->GP('kw')) {
            $pager['sokw'] = $kw = htmlspecialchars($kw);
            $filter['name'] = "LIKE:%{$kw}%";            
        }
		
        if($attr){
            $filter['attrs'] = $attr;
            if ($items = K::M('designer/designer')->items_by_attr($filter,null , $page, $limit, $count)) {
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/designer:items', array_merge($url, array('page' => '{page}')), array(), true),array('kw' => $pager['sokw']));
            }            
        }else{ 
            if($items = K::M('designer/designer')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/designer:items', array_merge($url, array('page' => '{page}')), array(), true),array('kw' => $pager['sokw']));
            } 
        }
		
        $area_list = K::M('data/area')->areas_by_city($this->request['city_id']);
        foreach ($area_list as $k => $v) {
            if ($k == $url['area_id']){
                $area_list[$k]['checked'] = true;
			}
            $area_list[$k]['link'] = $this->mklink('mobile/designer:items', array_merge($url, array('area_id' => $k)));
        }
		$attr = array_slice($attr_values, 0);
        $this->pagedata['area_url'] = $this->mklink('mobile/designer:items', array_merge($url, array('area_id' => 0)));
        $this->pagedata['area_list'] = $area_list;
        $this->pagedata['url_data'] = $url;
        $this->pagedata['attr_values'] = $attr;
        $this->pagedata['designers'] = $items;
		if($area_list[$area_id]['area_name']){
			$this->pagedata['area_name'] = $area_list[$area_id]['area_name'];
		}else{
			$this->pagedata['area_name'] = $area_list[$this->request['city_id']]['city_name'];
		}
		$pager['backurl'] = $this->mklink('mobile');
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'mobile/designer/items.html'; 
    }

	public function detail($designer_id)
	{
		$detail = $this->check_designer($designer_id);
		$this->pagedata['detail'] = $detail;
		$this->pagedata['company'] = K::M('company/company')->detail($detail['company_id']);
		$this->pagedata['city_list'] = K::M("data/city")->fetch_all();
		$this->pagedata['area_list'] = K::M("data/area")->fetch_all();
		$this->pagedata['type'] = 'about';
		$pager['backurl'] = $this->mklink('mobile/designer');
		$this->pagedata['pager'] = $pager;
		$this->tmpl = 'mobile/designer/detail.html';
	}

	public function cases($designer_id,$page='1')
	{
		$detail = $this->check_designer($designer_id);
		$page = empty($page) ? 1 : (int) $page;
		$limit = 3;
		$case_info = K::M("case/case")->items(array('closed'=>'0','audit'=>'1','uid'=>$uid),array('case_id'=>'desc'),$page,$limit,$count);
		$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/designer:cases', array($designer_id,'{page}')));
		$pager['backurl'] = $this->mklink('mobile/designer',array('designer_id'=>$designer_id));
		$this->pagedata['pager'] = $pager;
		$this->pagedata['type'] = 'cases';
		$this->pagedata['case_info'] = $case_info;
		$this->tmpl = 'mobile/designer/cases.html';
	}

	public function article($designer_id,$page='1')
	{
		$detail = $this->check_designer($designer_id);
		$page = empty($page) ? 1 : (int) $page;
		$limit = 6;
		$blog_info = K::M("designer/article")->items(array('audit'=>'1','uid'=>$designer_id),array('is_top'=>'desc','views'=>'desc'),$page,$limit,$count);
		$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('mobile/designer:article',array($uid,'{page}')));
		$pager['backurl'] = $this->mklink('mobile/designer',array('designer_id'=>$designer_id));
		$this->pagedata['pager'] = $pager;
		$this->pagedata['type'] = 'article';
		$this->pagedata['city_list'] = K::M("data/city")->fetch_all();
		$this->pagedata['blog_info'] = $blog_info;
		$this->tmpl = 'mobile/designer/article.html';
	}

	public function article_info($blog_id)
	{
		if(!($blog_id = (int)$blog_id) && !($blog_id = $this->GP('blog_id'))){
            $this->error(404);
        }else if(!$blog_info = K::M('designer/article')->detail($blog_id)){
            $this->error(404);
        }elseif(!$detail = K::M('designer/designer')->detail($blog_info['uid'])){
			 $this->error(404);
		}else{
			$this->pagedata['blogs'] = K::M("designer/article")->items(array('audit'=>'1','uid'=>$blog_info['uid']),array('is_top'=>'desc','views'=>'desc'),1,10);
			K::M('designer/article')->update($blog_id,array('views'=>$blog_info['views']+1));
			$detail['group'] = K::M('member/group')->check_priv($detail['group_id'],'allow_yuyue');
			$pager['backurl'] = $this->mklink('mobile/designer',array('designer_id'=>$designer_id));
			$this->pagedata['detail'] = $detail;
			$this->pagedata['blog_info'] = $blog_info;
			$this->pagedata['type'] = 'article';
			$this->pagedata['pager'] = $pager;
			$this->tmpl = 'mobile/designer/article_info.html';
		}
	}

	public function yuyue($uid)
	{
		if (!$this->check_login()) {
			$this->err->add('您还没有登录，不能预约', 101);
		}elseif(!($uid = (int)$uid) && !($uid = (int)$this->GP('uid'))){
            $this->err->add('没有您要的数据', 211);
        }else if(!$detail = K::M('designer/designer')->detail($uid)){
            $this->err->add('没有您要的数据', 212);
        }else if(empty($detail['audit'])){
            $this->err->add("内容审核中，暂不可访问", 211)->response();
        }else{
            if($this->checksubmit('data')){
               if(!$data = $this->GP('data')){
                    $this->err->add('非法的数据提交', 201);
                }else{
                    $data['designer_id'] = $uid;
                    $data['company_id'] = $detail['company_id'];
                    $data['uid'] = (int)$this->uid;
                    $data['content'] = "预约设计师:".$detail['uname'];
                    $data['city_id'] =  $this->request['city_id'];
                    if($yuyue_id = K::M('designer/yuyue')->create($data)){
						K::M('designer/designer')->update($uid,array('yuyue_num'=>$detail['yuyue_num']+1));
						$smsdata = $maildata = array('contact'=>$data['contact'] ? $data['contact'] : '业主','mobile'=>$data['mobile'],'designer'=>$detail['realname']);
                        K::M('sms/sms')->send($data['mobile'], 'designer_yuyue', $smsdata);
                        if($company_id = $detail['company_id']){
                            if($company = K::M('company/company')->detail($company_id)){
                                $company['member'] = $detail;
                                K::M('sms/sms')->company('designer_tongzhi', $smsdata);
                                K::M('helper/mail')->sendcompany($company, 'designer_yuyue', $maildata);
                            }
                        }else{
                            if($detail['verify_mobile'] && K::M('verify/check')->mobile($detail['mobile'])){
                                K::M('sms/sms')->send($detail['mobile'], 'designer_tongzhi', $smsdata);
                            }
                            K::M('helper/mail')->sendmail($detail['mail'], 'designer_yuyue', $maildata);
                        }
                        $this->err->add('预约设计师成功');
						$this->err->set_data('forward', $this->mklink('mobile/designer:detail', array('uid'=>$uid)));
                    }
                } 
            }else{
				$pager['title'] = '装修设计师'.$detail['uname'].'预约';
				$pager['backurl'] = $this->mklink('mobile/designer',array('designer_id'=>$designer_id));
				$this->pagedata['pager'] = $pager;
                $this->pagedata['designer_id'] = $uid;
                $this->tmpl = 'mobile/designer/yuyue.html';              
            }
		}
	}

	protected function check_designer($uid)
    {
        if(!($uid = (int)$uid) && !($uid = $this->GP('uid'))){
            $this->error(404);
        }else if(!$detail = K::M('designer/designer')->detail($uid)){
            $this->error(404);
        }
		$detail['group'] = K::M('member/group')->check_priv($detail['group_id'],'allow_yuyue');
        $this->pagedata['detail'] = $detail;
        return $detail;
    }
}