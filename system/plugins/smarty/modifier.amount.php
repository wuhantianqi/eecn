<?php
/**
 * Copy Right Anhuike.com
 * Each engineer has a duty to keep the code elegant
 * Author shzhrui<anhuike@gmail.com>
 * $Id: modifier.amount.php 2335 2013-12-18 17:15:56Z youyi $
 */

function smarty_modifier_amount($amount, $precision=2, $prefix='￥')
{
	$precision = (int)$precision;
	return $prefix.round($amount, $precision);
}