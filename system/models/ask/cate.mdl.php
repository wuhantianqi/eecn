<?php

/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: cate.mdl.php 2387 2013-12-20 07:57:21Z $
 */
if (!defined('__CORE_DIR')) {
    exit("Access Denied");
}

class Mdl_Ask_Cate extends Mdl_Table {

    protected $_table = 'ask_cate';
    protected $_pk = 'cat_id';
    protected $_cols = 'cat_id,title,parent_id,seo_keywords,seo_description,orderby,audit,closed,dateline';
    protected $_orderby = array('parent_id' => 'ASC', 'orderby' => 'ASC');
    protected $_pre_cache_key = 'ask_cat_list';

    public function detail($cat_id, $closed = false)
    {
        if ($detail = parent::detail($cat_id, $closed)) {
            $detail['pids'] = $this->parent_ids($cat_id);
            $detail = $this->_format_row($detail);
        }
        return $detail;
    }

    public function create($data, $checked = false)
    {
        if (!$checked && !$data = $this->_check_schema($data)) {
            return false;
        }
        if ($cat_id = $this->db->insert($this->_table, $data, true)) {
            $this->flush();
        }
        return $cat_id;
    }

    public function update($cat_id, $data, $checked = false)
    {
        if (!$cat_id = (int) $cat_id) {
            return false;
        }
        else if (!$checked && !$data = $this->_check_schema($data, $cat_id)) {
            return false;
        }
        if ($ret = $this->db->update($this->_table, $data, $this->field($this->_pk, $cat_id))) {
            $this->flush();
        }
        return $ret;
    }

    public function cate($cat_id)
    {
        if (!$cat_id = (int) $cat_id) {
            return false;
        }
        else if ($cate_list = $this->fetch_all()) {
            return $cate_list[$cat_id];
        }
        return false;
    }

    public function parent_ids($cat_id, $glue = ',', $append = false)
    {
        if (!$cat_id = (int) $cat_id) {
            return false;
        }
        $pids = $append ? array($cat_id) : array();
        if ($cats = $this->fetch_all()) {
            while ($a = $cats[$cat_id]) {
                if ($cat_id = $a['parent_id']) {
                    array_unshift($pids, $cat_id);
                }
            }
        }
        return implode($glue, $pids);
    }

    public function options($pid = 0)
    {
        $options = array();
        if ($items = $this->fetch_all()) {
            foreach ($items as $k => $v) {
                if ($v['parent_id'] == $pid) {
                    $options[$k] = $v['title'];
                }
            }
        }
        return $options;
    }

    public function childrens($pid = 0)
    {
        $childrens = array();
        if ($items = $this->fetch_all()) {
            foreach ((array) $items as $k => $v) {
                if ($v['parent_id'] == $pid) {
                    $childrens[$k] = $v;
                }
            }
        }
        return $childrens;
    }

    public function children_ids($pid = 0)
    {
        $ids = array($pid);
        $return = array($pid);
        if ($items =  $this->fetch_all()) {
            if (!empty($pid))
                unset($items[$pid]);
            while (true) {
                $local = array();
                foreach ($items as $k => $v) {
                    if (in_array($v['parent_id'], $ids)) {
                        $return[$k] = $local[$k] = $k;
                        unset($items[$k]);
                    }
                }
                if (empty($local))
                    break;
                $ids = $local;
            }
        }
        return $return;
    }

    protected function _format_row($row)
    {

        return $row;
    }

}
