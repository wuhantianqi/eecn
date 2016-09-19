<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: product.mdl.php 5402 2014-06-03 10:17:57Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Product_Product extends Mdl_Table
{   
  
    protected $_table = 'product';
    protected $_pk = 'product_id';
    protected $_cols = 'product_id,title,name,shop_id,cat_id,vcat_id,brand_id,city_id,area_id,market_price,price,danwei,freight,photo,photos,views,score,comments,buys,onsale,onpayment,sale_type,sale_time,sale_sku,is_tuan,sale_count,sale_remai,sale_youhui,sale_tuijian,orderby,audit,closed,lastupdate,dateline';
    protected $_orderby = array('orderby'=>'ASC', 'product_id'=>'DESC');

    protected $_hot_orderby = array('score'=>'DESC','views'=>'ASC');
    protected $_hot_filter = array('audit'=>'1', 'closed'=>0);
    protected $_new_orderby = array('shop_id'=>'DESC');
    protected $_new_filter = array('audit'=>'1', 'closed'=>0);       

    public function items($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
		
        if($attrs = $filter['attrs']){
            if($attr_ids = K::M('verify/check')->ids($attrs)){
                $attr_count = array_sum(explode(',', $attr_ids));
                $attr_sql = "SELECT product_id FROM ".$this->table('product_attr')." WHERE attr_value_id IN($attr_ids) GROUP BY product_id HAVING SUM(attr_value_id)=$attr_count";
            }
        }
        unset($filter['attrs']);
		
        if(is_numeric($filter['cat_id'])){
            if($cat_ids = K::M('shop/cate')->children_ids($filter['cat_id'])){
                $cat_ids = explode(',', $cat_ids);
                $filter['cat_id'] = $cat_ids;
            }           
        }else if(strpos($filter['cat_id'],',')){
			$cat_ids = explode(',',$filter['cat_id']);
            $filter['cat_id'] = $cat_ids;
		}
        $where = $this->where($filter);
        if($attr_sql){
            $where .= " AND product_id IN($attr_sql)";
        }
        $orderby = $this->order($orderby);
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$this->table($this->_table)." WHERE $where $orderby $limit";
        //if($rs = $this->db->Execute($sql)){Mdl_Mysql_Safecheck::checkquery 
        if($rs = $this->db->query($sql)){
            $count = $this->db->GetOne("SELECT FOUND_ROWS()");
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                $items[$row[$this->_pk]] = $row;
            }
        }       
        return $items;        
    }
	public function items_by_spec($shop_id)
	{
		$filter['shop_id'] = $shop_id; $filter['closed'] = 0; $filter['audit'] = 1;
		$where = $this->where($filter, 'd.');
        $sql = "SELECT m.*,d.* FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('product_spec')." m ON m.product_id=d.product_id WHERE $where";
		if($rs = $this->db->query($sql)){
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                $items[] = $row;
            }
        }
        return $items;
	}

	public function items_by_items($connect_ids)
	{
		if(is_array($connect_ids)){
            $ids = implode(',', $connect_ids);
        }
        if(!K::M('verify/check')->ids($ids)){
            return false;
        }
        $order = $this->order();
        $where = "d.{$this->_pk} IN ($ids)";
		$sql = "SELECT SQL_CALC_FOUND_ROWS m.*,d.* FROM ".$this->table($this->_table)." d LEFT JOIN ".$this->table('product_spec')." m ON m.product_id=d.product_id WHERE $where";
		if($rs = $this->db->query($sql)){
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
				if($row['spec_id']){
					$items[$row['spec_id']] = $row;
				}else{
					$items[$row['product_id']] = $row;
				}
            }
        }
        return $items;
	}

    public function detail($product_id, $closed=false)
    {
        if(!$product_id = (int)$product_id){
            return false;
        }
        $where = "p.product_id='$product_id'";
        $where .= $closed ? ' AND p.closed=0' : '';
        $sql = "SELECT f.*,p.* FROM ".$this->table($this->_table)." p LEFT JOIN ".$this->table('product_fields')." f ON p.product_id=f.product_id WHERE $where";
        if($row = $this->db->GetRow($sql)){
            $row = $this->_format_row($row);
        }
        return $row;
    }

    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        $data['lastupdate'] = __CFG::TIME;
        if($product_id = $this->db->insert($this->_table, $data, true)){
            K::M('product/fields')->create(array('product_id'=>$product_id, 'clientip'=>__IP), true);
            K::M('shop/shop')->update_count($data['shop_id'], 'products', 1);
        }
        return $product_id;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        $data['lastupdate'] = __CFG::TIME;
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function update_score($product_id, $score=3, $count=1)
    {
        $product_id = (int)$product_id;
        $score = (int)$score;
        $count = (int)$count;
        return $this->update($product_id, array('score'=>"`score`+$score", 'comments'=>'`comments`+1'), true);
    }

    public function items_by_shop($shop_id, $p=1, $l=50, &$count=0)
    {
        if(!$shop_id = (int)$shop_id){
            return false;
        }
        $filter = array('shop_id'=>$shop_id, 'audit'=>1, 'closed'=>0);
        return $this->items($filter, null, $p, $l, $count);
    }

    public function format_items_ext($items)
    {
        if(empty($items)){
            return false;
        }
        $shop_ids = array();
        foreach((array)$items as $k=>$v){
            $shop_ids[$v['shop_id']] = $v['shop_id'];
        }
        if($shop_ids){
            $shop_list = K::M('shop/shop')->items_by_ids($shop_ids);
        }
        foreach((array)$items as $k=>$v){
            $shop = array();
            if(!$shop = $shop_list[$v['shop_id']]){
                $member = array();
            }
            $v['ext'] = array('shop'=>$shop);
            $items[$k] = $v;            
        }
        return $items;
    }    

    protected function _format_row($row)
    {
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }
        if($area_id = $row['area_id']){
            if($city = K::M('data/area')->area($area_id)){
                $row['area_name'] = $city['area_name'];
            }
        }
        if($cat_id = $row['cat_id']){
            if($cate = K::M('shop/cate')->cate($cat_id)){
                $row['cate_title'] = $row['cate_name'] = $cate['title'];
            }
        }
        if($brand_id = $row['brand_id']){
            if($brand = K::M('shop/brand')->brand($brand_id)){
                $row['brand_title'] = $brand['title'];
            }
        }
        if(empty($row['photo'])){
            $row['photo'] = 'default/product_photo.jpg';
        }
        if($row['sale_type'] == 2){
            $row['sale_type_title'] = '限时抢购';
        }else if($row['sale_type'] == 1){
            $row['sale_type_title'] = '限量抢购';
        }
        return $row;    
    }

    protected function _check($data, $product_id=null)
    {
        $data['lastupdate'] = __CFG::TIME;
        return parent::_check($data, $product_id);
    }
}