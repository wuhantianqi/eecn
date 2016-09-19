<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<anhuike@gmail.com>
 * $Id: mdl.view.php 2034 2013-12-07 03:08:33Z $
 */

Import::M('#base#/base');
class Mdl_#base#_View extends Mdl_#base#_Base
{

	public function items($filter, $orderby=null, $p=1, $l=50, &$count=0)
	{
		$items = array();
		$where = $this->where($filter);
		$limit = $this->limit($p, $l);
		$orderby = $this->order($orderby);
		if($count = $this->count($where)){
			if($rs = $this->db->Execute("SELECT * FROM ".$this->table($this->_table)." WHERE $where $orderby $limit")){
				while($row = $rs->fetch()){
					if($this->_pk){
						$items[$row[$this->_pk]] = $row;
					}else{
						$items[] = $row;	
					}
				}
			}
		}
		return $items;
	}

	public function detail($#pk#, $closed=false)
	{
		$#pk# = self::quote($#pk#);
		$sql = "SELECT * FROM ".$this->table($this->_table)." WHERE `#pk#`='{$#pk#}'";
		return $this->db->GetRow($sql);
	}

}