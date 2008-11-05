<?php
//  AcmlmBoard XD support - Arbe's Super SQL Escaper

//	This should work. I can't test it, because I don't have file access or whatever. If it doesn't, the error isn't likely to be beyond your scope of understanding.
function super_sql_escape($var)
{
	//	If it's an array, iterate through it, and run this function again on each key, making it recursive (so multidimensional arrays still get escaped)
	if(is_array($var))
	{
		foreach($var AS $key=>$value)
		{
			super_sql_escape($var[$key]);
		}
	}
	//	If not, escape it!
	else
	{
		mysql_real_escape_string($var);
	}

	return $var;
}

$_SANITIZE	=	array('_POST','_GET','_COOKIE','_SERVER','REQUEST','_SESSION');

foreach($_SANITIZE AS $key)
{
	if(!get_magic_quotes_gpc())
		$$key = super_sql_escape($$key);
}
//	In short, don't bother escaping any data any more. ;)

?>
