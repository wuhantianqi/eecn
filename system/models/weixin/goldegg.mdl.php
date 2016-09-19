<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Goldegg extends Mdl_Table
{   
  
    protected $_table = 'weixin_goldegg';
    protected $_pk = 'id';
    protected $_cols = 'id,predict_num,wx_id,keyword,photo,title,use_tips,stime,ltime,info,aginfo,end_tips,end_photo,fist,fistnums,fistlucknums,second,secondnums,secondlucknums,third,thirdnums,thirdlucknums,joinnum,max_num,parssword,four,fournums,fourlucknums,five,fivenums,fivelucknums,six,sixnums,sixlucknums,daynums,member_condtion,dateline,follower_condtion';

    
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

	public function format_reply($goldegg_id)
	{
		if($row = $this->detail($goldegg_id)){
			$cfg = K::$system->config->get('attach');
			$a = array('Title'=>$row['title'], 'Description'=>$row['info'], 'PicUrl'=>$cfg['attachurl'].'/'.$row['photo'],
				'Url'=>K::M('helper/link')->mklink('weixin/goldegg:show', array($goldegg_id), array(), 'www')
				);
			return array($a);
		}
		return false;
	}
}