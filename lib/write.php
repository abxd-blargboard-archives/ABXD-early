<?php

if(!function_exists('format'))
{
	function format()
	{
		$argc = func_num_args();
		if($argc == 1)
			return func_get_arg(0);
		$args = func_get_args();
		$output = $args[0];
		for($i = 1; $i < $argc; $i++)
			$output = str_replace("{".($i-1)."}", $args[$i], $output);
		return $output;
	}

	function write()
	{
		$argc = func_num_args();
		if($argc == 0)
			return func_get_arg(0);
		$args = func_get_args();
		$output = $args[0];
		for($i = 1; $i < $argc; $i++)
			$output = str_replace("{".($i-1)."}", $args[$i], $output);
		print $output;	
	}
}

?>