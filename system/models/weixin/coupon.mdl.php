<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Coupon extends Mdl_Table
{   
  
    protected $_table = 'weixin_coupon';
    protected $_pk = 'coupon_id';
    protected $_cols = 'coupon_id,wx_id,member_condtion,keyword,title,intro,photo,stime,ltime,use_tips,end_tips,end_photo,num,max_count,down_count,use_count,views,follower_condtion,clientip,dateline';

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

	public function format_reply($coupon_id)
	{
		if($row = $this->detail($coupon_id)){
			$cfg = K::$system->config->get('attach');
			$a = array('Title'=>$row['title'], 'Description'=>$row['intro'], 'PicUrl'=>$cfg['attachurl'].'/'.$row['photo'],
				'Url'=>K::M('helper/link')->mklink('weixin/coupon:preview', array($coupon_id), array(), 'www')
				);
			return array($a);
		}
		return false;
	}
}