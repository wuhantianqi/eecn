<?php

class Mdl_System_Audit extends Mdl_Table
{   
    
    /**
     * @param string $auth 权限
     * @param array $member 用户信息
     * @param array $data   店铺/酒店
     * @return int  -1 代表没有权限  0 代表需要审核  1 代表不需要审核
     */
    public function audit($auth, $member, &$data=array(), &$title='')
    {
        
        if(empty($member)) return 0; // 支持一下点评的时候需要匿名来点评；         
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_'.$member['from'].'_';
        switch($member['from']){            
            case 'member':
                return $this->member($auth, $member, $title);
                break;
            case 'mechanic':
                return $this->designer($auth, $member, $title);              
                break;                    
            case 'designer':
                return $this->designer($auth, $member, $title);              
                break;
			case 'gz':
                return $this->gz($auth, $member, $title);              
                break;
            case 'company':
                if(empty($data)){
                    $data = K::M('company/company')->company_by_uid($member['uid']);
                }
                $data['member'] = $member;
                return $this->company($auth, $data, $title); 
                break;
            case 'shop':
                if(empty($data)){
                    $data = K::M('shop/shop')->shop_by_uid($member['uid']);
                }
                $data['member'] = $member;
                return $this->company($auth, $data, $title);
                break;          
        }
        return 0;
    }

    public function member($auth, $member, &$title='')
    {
        if(empty($member)) return 0; // 支持一下点评的时候需要匿名来点评
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_member_';
        if(!empty($member['verify_name'])){
            $key.='Y'; $title = '认证会员';
        }else{
            $key.='N'; $title = '普通会员';
        }
        if(isset($cfg[$key])) return (int)$cfg[$key];
        return 0;
    }

    public function mechanic($auth, $mechanic, &$title='')
    {
        if(empty($mechanic)) return 0; // 支持一下点评的时候需要匿名来点评
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_mechanic_';
        if(!empty($mechanic['verify_name'])){
            $key.='Y'; $title = '认证设计师';
        }else{
            $key.='N'; $title = '普通设计师';
        }
        if(isset($cfg[$key])) return (int)$cfg[$key];
        return 0;        
    }

    public function designer($auth, $designer, &$title='')
    {
        if(empty($designer)) return 0; // 支持一下点评的时候需要匿名来点评
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_designer_';
        if(!empty($designer['verify_name'])){
            $key.='Y'; $title = '认证设计师';
        }else{
            $key.='N'; $title = '普通设计师';
        }
        if(isset($cfg[$key])) return (int)$cfg[$key];
        return 0;        
    }

	public function gz($auth, $gz, &$title='')
    {
        if(empty($gz)) return 0; // 支持一下点评的时候需要匿名来点评
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_gz_';
        if(!empty($gz['group_id'])){
            $key.='Y'; $title = '认证工长';
        }else{
            $key.='N'; $title = '普通工长';
        }
        if(isset($cfg[$key])) return (int)$cfg[$key];
        return 0;        
    }
    
    public function company($auth, $company, &$title='')
    {
        if(empty($company)) return -1;
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_company_';
        if($company['is_vip']){
            $key.='V'; $title = 'VIP企业';
        }else if($company['verify_name']){
            $key.='Y'; $title = '认证企业';
        }else if(isset($company['member']) && $company['member']['verify_name']){
            $key.='Y'; $title = '认证企业';
        }else{
            $key.='N'; $title = '普通企业';
        }
        if(isset($cfg[$key])) return (int)$cfg[$key];
        return 0; 
    }

    public function shop($auth, $shop, &$title='')
    {
        if(empty($shop)) return -1;
        $cfg = K::M('system/config')->get('audit');
        $key = $auth.'_shop_';
        if($shop['is_vip']){
            $key.='V'; $title = 'VIP商铺';
        }else if($shop['verify_name']){
            $key.='Y'; $title = '认证商铺';
        }else if(isset($shop['member']) && $shop['member']['verify_name']){
            $key.='Y'; $title = '认证商铺';
        }else{
            $key.='N'; $title = '普通商铺';
        }
        if(isset($cfg[$key])) return (int)$cfg[$key];
        return 0; 
    }
}