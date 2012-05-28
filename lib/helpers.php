<?php
	function endswith($str, $end){
		if(strlen($end)>strlen($str)) return false;
		return substr($str, strlen($str)-strlen($end))==$end;
	}

	function str_rtrim($str, $end){
		if(!endswith($str, $end)) return $str;
		return substr($str, 0, strlen($str)-strlen($end));
	}