<?php
/**
 * Copy	Right Anhuike.com
 * $Id json.mdl.php shzhrui<anhuike@gmail.com>
 */

class Mdl_Helper_Json
{
	public function encode($data)
	{
		return json_encode($data);
	}

	public function decode($data)
	{
		return json_decode($data);
	}
}