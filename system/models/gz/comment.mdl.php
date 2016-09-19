<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Gz_Comment extends Mdl_Table
{   
  
    protected $_table = 'gz_comment';
    protected $_pk = 'comment_id';
    protected $_cols = 'comment_id,city_id,gz_id,uid,score1,score2,score3,content,reply,replyip,replytime,audit,closed,clientip,dateline';

    
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

    public function comment_count($val)
    {
        $count = 0;
        $sql = "SELECT gz_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('gz_id', $val)." GROUP BY gz_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                K::M('gz/gz')->update($row['gz_id'], array('comments'=>$row['c']), true);
                $count ++;
            }
        }
        return $count;
    }

	public function comment($data)
	{
		if($detail = K::M('gz/gz')->detail($data['gz_id'])){
			$update['comments'] = $detail['comments']+1;
			$update['score1'] = $detail['score1']+$data['score1'];
			$update['score2'] = $detail['score2']+$data['score2'];
			$update['score3'] = $detail['score3']+$data['score3'];
			$update['score'] = round(($update['score1']+$update['score2']+$update['score3'])/3);
			K::M('gz/gz')->update($data['gz_id'], $update, true);
		}
	}

    protected function _format_row($row)
    {
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }
        return $row;
    }     

    protected function _check($data, $comment_id=null)
    {
        unset($data['comment_id'], $data['clientip'], $data['dateline']);
        if(!$comment_id || isset($data['content'])){
            if(empty($data['content'])){
                $this->err->add('留言内容不能为空', 411);
                return false;
            }else if(!K::M('verify/check')->len(K::M("content/text")->strlen($data['content']), 5, 512)){
                $this->err->add('留言内容长度为5~512个字', 411);
                return false;               
            }
            $data['content'] = K::M('content/html')->encode($data['content']);
        }
        for($i=1; $i<4; $i++){
            $k = 'score'.$i;
            if(!$comment_id || isset($data[$k])){
                $data[$k] = (int)$data[$k];
                if($data[$k]<1 || $data[$k]>5){
                    $data[$k] = 3;
                }
            }            
        }
        return parent::_check($data);
    } 

}