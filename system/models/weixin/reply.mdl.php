<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Weixin_Reply extends Mdl_Table
{   
  
    protected $_table = 'weixin_reply';
    protected $_pk = 'reply_id';
    protected $_cols = 'reply_id,wx_id,title,intro,photo,jumpurl,content,views,dateline';

    
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


    /*
     * 回复图文消息 articles array 格式如下： array( array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''), array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>'') );
     */
    public function format_wechat($row)
    {
        $article = array();
        $article['title'] = $row['title'];
        $article['Description'] = $row['intro'];
        $article['PicUrl'] = Mdl_System_Config::$_CFG['attach']['attachurl'].'/'.$row['photo'];
        if($row['jumpurl'] && K::M('verify/check')->url($row['jumpurl'])){
            $article['Url'] = $row['jumpurl'];
        }else{
            $article['Url'] =  Mdl_System_Config::$_CFG['site']['siteurl'].'/index.php?/weixin/index-news-'.$row['reply_id'].'.html';
        }
        return $article;
    }
}