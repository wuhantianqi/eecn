<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: censor.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Data_Censor extends Mdl_Table
{   
    CONST SPLIT_SIZE = 1000;

    protected $_table = 'data_censor';
    protected $_pk = 'ID';
    protected $_cols = 'ID,type,find,replace,admin,dateline';
    protected $_orderby = array('ID'=>'DESC');
    protected $_pre_cache_key = 'data-censor-list';
    protected $_type_list = array('shield'=>'禁用词', 'censor'=>'审核词', 'filter'=>'替换词');
    


    public function fetch_all()
    {
        static $censor = null;
        if($censor === null && !($censor = $this->cache->get($this->_pre_cache_key))){
            $sql = "SELECT * FROM ".$this->table($this->_table);
            $censor = array();
            if($rs = $this->db->Execute("SELECT * FROM ".$this->table($this->_table))){
                while($row = $rs->fetch()){
                    $row['find'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($row['find'], '/'));
                    switch($row['type']){
                        case 'shield':
                            $censor['shilde'][] = $row['find']; break;
                        case 'censor': case 'mod';
                            $censor['censor'][] = $row['find']; break;
                        default :
                            $censor['filter']['find'][] = "/{$row[find]}/ui";
                            $censor['filter']['replace'][] = $row['replace'];
                    }
                }
                if($censor['shilde']){
                    foreach(array_chunk($censor['shilde'], self::SPLIT_SIZE) as $v){
                        $this->_censor['shilde'][] = '/('.implode('|',$v).')/ui';
                    }
                }
                if($censor['censor']){
                    foreach(array_chunk($censor['censor'], self::SPLIT_SIZE) as $v){
                        $this->_censor['censor'][] = '/('.implode('|',$v).')/ui';
                    }
                }
                if($censor['filter']['find'] && $censor['filter']['find']){
                    foreach(array_chunk($censor['filter']['find'], self::SPLIT_SIZE) as $k=>$v){
                        $this->_censor['filter'][$k]['find'] = $v;
                    }
                    foreach(array_chunk($censor['filter']['replace'], self::SPLIT_SIZE) as $k=>$v){
                        $this->_censor['filter'][$k]['replace'] = $v;
                    }
                }
            }
            $this->cache->set($this->_pre_cache_key, $censor);
        }
        return $censor;       
    }

    public function create($data)
    {
        if(!$data = $this->_check($data)){
            return false;
        }
        $data['dateline'] = __CFG::TIME;
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($id, $data, $checked=false)
    {
        if(!$id = (int)$id){
            return false;
        }else if(!$checked && !$data = $this->_check($data,  $id)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $id));
    }

    public function type_list()
    {
        return $this->_type_list;
    }

    public function item_by_word($word)
    {
        $sql = "SELECT * FROM ".$this->table($this->_table)." WHERE find='$word'";
        return $this->db->GetRow($sql);
    }

    protected function _check($data, $id=null)
    {
        if(empty($data['find'])){
            $this->err->add('关键词不能为空', 411);
            return false;
        }else if(!$this->_type_list[$data['type']]){
            $this->err->add('未知关键词类型', 412);
            return false;
        }else if($data['type'] == 'filter' && empty($data['replace'])){
            $this->err->add('替换词不能为空', 413);
            return false;
        }
        if($item = $this->item_by_word($data['find'])){
            if(!$id){
                $this->err->add('关键词已经存在', 415);
                return false;
            }else if($id != $item['ID']){
                $this->err->add('关键词已经存在', 415);
                return false;
            }
        }
        return parent::_check($data);
    }
}