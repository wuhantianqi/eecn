<?php
/**
 * Copy	Right Anhuike.com
 * $Id format.mdl.php shzhrui<anhuike@gmail.com>
 */

class Mdl_Helper_Format
{
	
	/**
	 * 格式化输出字节数 {GB,MB,KB,Bytes}
	 * @param	$size	字节数单位bytes
	 * @return string	
	 */
	static public function size($size)
	{
		if($size >= 1073741824) {
			$size = round($size / 1073741824 * 100) / 100 . ' GB';
		} elseif($size >= 1048576) {
			$size = round($size / 1048576 * 100) / 100 . ' MB';
		} elseif($size >= 1024) {
			$size = round($size / 1024 * 100) / 100 . ' KB';
		} else {
			$size = $size . ' Bytes';
		}
		return $size;		
	}

	static public function price($price)
	{
		
	}

	static public function time($time, $format='')
	{
		
		if($format){
			return date($format,$time);
		}
		$s = date('Y-m-d H:i:s',$time);
		$sdaytime = K::$system->sdaytime;
		$stime = __CFG::TIME - $time;
		if($time >= $sdaytime){ //当天
			if($stime > 3600) {
				return '<span title="'.$s.'">'.intval($stime / 3600).'小时前</span>';
			} elseif($stime > 1800) {
				return '<span title="'.$s.'">半小时前</span>';
			} elseif($stime > 60) {
				return '<span title="'.$s.'">'.intval($stime / 60).'分钟前</span>';
			} elseif($stime > 0) {
				return '<span title="'.$s.'">'.$stime.'秒前</span>';
			} elseif($stime == 0) {
				return '<span title="'.$s.'">刚刚</span>';
			} else {
				return '<span title="'.$s.'">'.$s.'</span>';
			}
		}else if(($days = intval($stime / 86400)) >= 0 && $days < 7){
			if($days == 0) {
				return '<span title="'.$s.'">昨天&nbsp;'.date("H:i", $time).'</span>';
			} elseif($days == 1) {
				return '<span title="'.$s.'">前天&nbsp;'.date("H:i", $time).'</span>';
			} else {
				return '<span title="'.$s.'">'.($days + 1).'天前</span>';
			}
		}else if(empty($time)){
			return '<span title="'.$s.'">0</span>';
		}else{
			return '<span title="'.$s.'">'.$s.'</span>';
		}
	}

    static public function microtime($time=0)
    {
        $time = $time ? $time : explode(' ',microtime());
        return date('Y-m-d H:i:s',$time[0]).",{$time[1]}毫秒";
    }
}