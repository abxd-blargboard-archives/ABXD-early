<?php
//  AcmlmBoard XD support - MySQL database wrapper functions

include("database.php");

$queries = 0;

mysql_connect($dbserv, $dbuser, $dbpass) or Kill("Could not connect to database.");
mysql_select_db($dbname);
unset($dbpass);

function Query($query)
{
	global $queries, $loguser, $thisURL;
	//write("#{0} - {1}<br/>", $queries, $query);
	$res = mysql_query($query) or Kill(mysql_error()."<br />Query was: <code>".$query."</code>");
	$queries++;
	return $res;
}

function Fetch($result)
{
	$res = mysql_fetch_array($result);
	return $res;
}

function FetchResult($query, $row = 0, $field = 0)
{
	$res = Query($query);
	if(mysql_numrows($res) == 0) return -1;
	return mysql_result($res, $row, $field);
}

function NumRows($result)
{
	return mysql_numrows($result);
}

?>
