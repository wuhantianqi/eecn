<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: case.mdl.php 6093 2014-08-15 11:58:57Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Case_Case extends Mdl_Table
{   
  
    protected $_table = 'case';
    protected $_pk = 'case_id';
    protected $_cols = 'case_id,city_id,home_id,uid,huxing,huxing_id,company_id,intro,home_name,title,photo,size,views,likes,seo_title,seo_keywords,seo_description,orderby,lastphotos,lasttime,audit,closed,clientip,dateline';
    protected $_orderby = array('orderby'=>'ASC', 'lasttime'=>'DESC');

    protected $_hot_orderby = array('likes'=>'DESC','orderby'=>'ASC');
    protected $_hot_filter = array('audit'=>'1', 'closed'=>'0');
    protected $_new_orderby = array('lasttime'=>'DESC');
    protected $_new_filter = array('audit'=>'1', 'closed'=>'0');

    public function items($filter=array(), $orderby=null, $p=1, $l=50, &$count=0)
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
	
        $orderby = $this->order($orderby);
        $limit = $this->limit($p, $l);
        $items = array();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$this->table($this->_table)." WHERE $where $orderby $limit";
        if($rs = $this->db->query($sql)){
            $count = $this->db->GetOne("SELECT FOUND_ROWS()");
            while($row = $rs->fetch()){
                $row = $this->_format_row($row);
                $items[$row[$this->_pk]] = $row;
            }
        }
        return $items;
    }
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($case_id = $this->db->insert($this->_table, $data, true)){
            $this->update_ext_count($data);
        }
        return $case_id;
    }

	public function case_count($val)
	{
	    if($case = K::M('case/case')->detail($val)){
            if($company_id = (int)$case['company_id']){
				$this->company_count($company_id);
			}
			if($uid = (int)$case['uid']){
				$member = K::M('member/member')->detail($uid);
				if($member['from'] == 'gz' ||  $member['from'] == 'designer'){
					$this->uid_count($uid,$member['from']);
				}
			}
			if ($home_id = (int) $case['home_id']) {
				$this->home_count($home_id);
			}
        }
	}

	public function down_count($case)
	{
		if($company_id = (int)$case['company_id']){
			$this->company_count($company_id);
		}
		if($uid = (int)$case['uid']){
			$member = K::M('member/member')->detail($uid);
			if($member['from'] == 'gz' ||  $member['from'] == 'designer'){
				$this->uid_count($uid,$member['from']);
			}
		}
		if ($home_id = (int) $case['home_id']) {
			$this->home_count($home_id);
		}
	}

	public function downs_count($items)
	{
		$company_ids = $homes_id = $uids = array();
		foreach($items as $v){
			$company_ids[$v['company_id']] = $v['company_id'];
			$uids[$v['uid']] = $v['uid'];
			$homes_id[$v['home_id']] = $v['home_id'];
		}

		if($company_ids){
			$this->company_count($company_ids);
		}
		if($homes_id){
			$this->home_count($homes_id);
		}
		if($uids){
			$member = K::M('member/member')->items_by_ids($uids);
			$gzs = $designers = array();
			foreach($member as $k => $v){
				if($v['from'] == 'gz'){
					$gzs[$v['uid']] = $v['uid'];
				}elseif($v['from'] == 'designer'){
					$designers[$v['uid']] = $v['uid'];
				}
			}
			if(!empty($gzs)){
				$this->uid_count($gzs,'gz');
			}elseif(!empty($designers)){
				$this->uid_count($designers,'designer');
			}			
		}
	}

	public function company_count($val)
	{
		$count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT company_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('company_id', $val)." and closed = 0 GROUP BY company_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['company_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('company/company')->update($k, array('case_num'=>$v), true);
                $count ++;
            }            
        }
		K::M('company/company')->update($val, array('last_case' => __TIME));
        return $count;
	}

	public function uid_count($val,$table)
	{
		$count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT uid, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('uid', $val)." and closed = 0 GROUP BY uid";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['uid']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M($table.'/'.$table)->update($k, array('case_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
	}

	public function home_count($val)
	{
		$count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT home_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('home_id', $val)." and closed = 0 GROUP BY home_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['home_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('home/home')->update($k, array('case_num'=>$v), true);
                $count ++;
            }            
        }
        return $count;
	}


    public function update($case_id, $data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data,  $case_id)){
            return false;
        }else if(!$case = $this->detail($case_id)){
            return false;
        }
        if($ret = $this->db->update($this->_table, $data, $this->field($this->_pk, $case_id))){
            $this->update_ext_count($data, $case);
        }
        return $ret;
    }
    
    
    public function up_detail($pk, $audit = 1,$closed=false)
	{
		if(!$pk = (int)$pk){
			return false;
		}
		$this->_checkpk();	
        $audit = (int) $audit;
		$where = $this->_pk." < {$pk} AND audit={$audit}";
		if($closed && $this->field_exists('closed')){
			$where .= " AND closed='0'";
		}
		$sql = "SELECT * FROM ".$this->table($this->_table)." WHERE $where  ORDER BY ".$this->_pk." DESC LIMIT 1";
		if($detail = $this->db->GetRow($sql)){
			$detail = $this->_format_row($detail);
		}
		return $detail;
	}
    
    public function next_detail($pk,$audit = 1, $closed=false){
        if(!$pk = (int)$pk){
			return false;
		}
		$this->_checkpk();		
        $audit = (int) $audit;
		$where = $this->_pk." > {$pk} AND audit={$audit}";
		if($closed && $this->field_exists('closed')){
			$where .= " AND closed='0'";
		}
		$sql = "SELECT * FROM ".$this->table($this->_table)." WHERE $where  ORDER BY ".$this->_pk." ASC LIMIT 1";
		if($detail = $this->db->GetRow($sql)){
			$detail = $this->_format_row($detail);
		}
		return $detail;
    }
    

    public function delete($val, $force=false)
    {
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $ret = false;
        if($items = $this->items_by_ids($ids)){
            $case_ids = $designer_ids = $home_ids = $company_ids = array();
            foreach($items as $k=>$v){
                if(empty($v['closed'])){
                    if($v['designer_id']){
                        $designer_id[$v['designer_id']] ++; 
                    }
                    if($v['home_id']){
                        $designer_id[$v['home_id']] ++; 
                    }
                    if($v['company_id']){
                        $designer_id[$v['company_id']] ++; 
                    }
                    $case_ids[$v['case_id']] = $v['case_id'];
                }
            }
            if($case_ids){
                if($ret = parent::delete($case_ids, $force)){
                    if($designer_ids){
                        foreach($designer_ids as $k=>$v) {
                            K::M('designer/designer')->update_count($k, 'case_num', -(int)$v);
                        }
                    }
                    if($company_ids){
                        foreach($company_ids as $k=>$v) {
                            K::M('company/company')->update_count($k, 'case_num', -(int)$v);
                        }
                    }
                    if($home_ids){
                        foreach($home_ids as $k=>$v) {
                            K::M('home/home')->update_count($k, 'case_num', -(int)$v);
                        }
                    }                                        
                }
            }
        }
        return $ret;       
    }

    public function update_ext_count($data, $case=array())
    {
        if(isset($data['home_id']) || isset($data['company_id']) || isset($data['designer_id'])){
            if(isset($data['home_id'])){
                if($case['home_id'] != $data['home_id']){
                    if($case['home_id']){
                        K::M('home/home')->update_count($case['home_id'], 'case_num', -1);
                    }
                    if($data['home_id']){
                        K::M('home/home')->update_count($data['home_id'], 'case_num', 1);
                    }
                }
            }
            if(isset($data['company_id'])){
                if($case['company_id'] != $data['company_id']){
                    if($case['company_id']){
                        K::M('company/company')->update_count($case['company_id'], 'case_num', -1);
                    }
                    if($data['company_id']){
                        K::M('company/company')->update_count($data['company_id'], 'case_num', 1);
                    }
                }
            }
            if(isset($data['designer_id'])){
                if($case['designer_id'] != $data['designer_id']){
                    if($case['designer_id']){
                        K::M('designer/designer')->update_count($case['designer_id'], 'case_num', -1);
                    }
                    if($data['designer_id']){
                        K::M('designer/designer')->update_count($data['designer_id'], 'case_num', 1);
                    }                        
                }
            }
        }       
    }

    public function update_last($case_id, $size=0, $num=1)
    {
        $lastpids = array(); 
        if(!$size = (int)$size){
            return false;
        }else if(!$num = (int)$num){
            return false;
        }else if(!$case_id = (int)$case_id){
            return false;
        }
        $filter = array('closed'=>0, 'case_id'=>$case_id);
        $photo = '';
        if($items = K::M('case/photo')->items($filter, array('photo_id'=>'DESC'), 1, 10)){
            foreach($items as $v){
                $lastpids[$v['photo_id']] = $v['photo_id'];
            }
            $last = array_shift($items);
            $photo = $last['photo'];
        }
        $pids = implode(',', $lastpids);
        $time = __CFG::TIME;
        $sql = "UPDATE ".$this->table($this->_table)." SET photo='{$photo}', lastphotos='{$pids}', lasttime='{$time}',`photos`=`photos`+$num,`size`=`size`+$size WHERE case_id='$case_id'";
        return $this->db->Execute($sql);
    }

    /**
     * 重置案例统计数
     */
    public function reset_count($case_id)
    {
        if(!$case_id = (int)$case_id){
            return false;
        }else if(!$data = K::M('case/photo')->count_by_case($case_id)){
            $data = array('case_id'=>$case_id, 'photos'=>0, 'size'=>0);
        }
        return $this->db->update($this->_table, array('photos'=>$data['photos'], 'size'=>$data['size']), "case_id='{$case_id}'");
    }

    public function format_items_ext($items)
    {
        if(empty($items)){
            return false;
        }
        $case_ids = $photo_ids = $home_ids = $designer_ids = array();
        foreach((array)$items as $k=>$v){
            $case_ids[$v['case_id']] = $v['case_id'];
            if($v['lastphotos']){
                foreach(explode(',', $v['lastphotos']) as $id){
                    $photo_ids[$id] = $id;
                }
            }
            if($v['home_id']){
                $home_ids[$v['home_id']] = $v['home_id'];
            }
            if($v['designer_id']){
                $designer_ids[$v['designer_id']] = $v['designer_id'];
            }
        }
        if($photo_ids){
            $photo_list = K::M('case/photo')->items_by_ids($photo_ids);
        }
        if($home_ids){
            $home_list = K::M('home/home')->items_by_ids($home_ids);
        }
        if($designer_ids){
            $designer_list = K::M('designer/designer')->items_by_ids($designer_ids);
        }
        if($case_ids){
            $attr_list = K::M('case/attr')->items(array('case_id'=>$case_ids), null, 1, 500);
        }
        foreach((array)$items as $k=>$v){
            $photos = $designer = $home = array();
            if($v['lastphotos']){                    
                foreach(explode(',', $v['lastphotos']) as $id){
                    if($photo = $photo_list[$id]){
                        $photos[$id] = $photo;
                    }
                }
            }
            if(!$home = $home_list[$v['home_id']]){
                $home = array();
            }
            if(!$designer = $designer_list[$v['designer_id']]){
                $designer = array();
            }
            $v['ext'] = array('photos'=>$photos, 'home'=>$home, 'designer'=>$designer, 'attrs'=>array());
            $items[$k] = $v;            
        }
        $obj = K::M('data/attrvalue');
        foreach($attr_list as $k=>$v){
            if($items[$v['case_id']]){
                if($val = $obj->attrvalue($v['attr_value_id'])){
                    $items[$v['case_id']]['ext']['attrs'][$v['attr_value_id']] = $val['title'];
                }
            }
        }
        return $items;
    }
}