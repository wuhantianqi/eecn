<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: view.mdl.php 5531 2014-06-19 10:26:25Z youyi $
 */


if(!defined('__CORE_DIR')){
    exit("Access Denied");
}
//edit by shzhrui 
Import::M('case/case');
class Mdl_Case_View extends Mdl_Case_Case
{
    //别名类，兼容用
}
/*
class Mdl_Case_View extends Mdl_Table
{   
    protected $_table = 'case';
    protected $_pk = 'case_id';
    
    public function items($filter=array(), $orderby=null, $p=1, $l=20, &$count=0)
    {
        if($attrs = $filter['attrs']){
            $attr_ids = join(',',$attrs);
            $attr_count = array_sum($attrs);
            $attr_sql = "SELECT case_id FROM ".$this->table('case_attr')." WHERE attr_value_id IN($attr_ids) GROUP BY case_id HAVING SUM(attr_value_id)=$attr_count";
			$ids = array();
			if($rs = $this->db->query($attr_sql)){
				 while($row = $rs->fetch()){
					$ids[$row['case_id']] = $row['case_id'];
				}
			}
		
			if(!empty($ids)){
				$str = join(',',$ids);
				$datasql=" AND case_id IN($str) ";
			}else{
				$datasql =  false;
			}
			
		}
      
        unset($filter['attrs']);
        $where = $this->where($filter);
        if($datasql !== false){
            $where .= $datasql;
        }else{
			return array();
		}
	
        $orderby = $this->order($orderby, null);
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$this->table($this->_table)."  WHERE $where $orderby $limit";
        if($rs = $this->db->query($sql)){
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                if($this->_pk){
                    $items[$row[$this->_pk]] = $row;
                }else{
                    $items[] = $row;
                }
            }
            $count = $this->db->GetOne("SELECT FOUND_ROWS()");
        }
        return $items;
    }
    
    
}
*/