<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: magic.mdl.php 3053 2014-01-15 02:00:13Z youyi $
 */

class Mdl_Magic_Magic extends Model 
{
    

    public function sitecount()
    {
    	if(!$sitecount = $this->cache->get('site-info-count')){
    		$sitecount = array('member'=>0, 'designer'=>0, 'gz'=>0,'mechanic'=>0, 'company'=>0, 'shop'=>0, 'tenders'=>0, 'product'=>0, 'case'=>0, 'home'=>0,'site'=>'0');
    		$sitecount['member'] = K::M('member/member')->count();
            $sitecount['designer'] = K::M('designer/designer')->count();
            $sitecount['gz'] = K::M('gz/gz')->count();
    		$sitecount['mechanic'] = K::M('mechanic/mechanic')->count();
    		$sitecount['company'] = K::M('company/company')->count();
			$sitecount['home'] = K::M('home/home')->count();
    		$sitecount['shop'] = K::M('shop/shop')->count();
            $sitecount['tenders'] = K::M('tenders/tenders')->count();
			$sitecount['designer_yuyue'] = K::M('designer/yuyue')->count();
    		$sitecount['product'] = K::M('product/product')->count();
    		$sitecount['case'] = K::M('case/case')->count();
			$sitecount['photo'] = K::M('case/photo')->count();
			$sitecount['article'] = K::M('article/cate')->count();
			$city_id = K::$system->request['city_id'];
			$sql = "select `status`,count(*) as count from jh_home_site where city_id='".$city_id."' group by `status`";
			$rs = $this->db->query($sql);
			while($row = $rs->fetch()){
                $items[$row['status']] = $row;
            }
			$sitecount['site'] = $items;
    		$this->cache->set('site-info-count', $sitecount, 86400);
    	}
    	return $sitecount;
    }
}