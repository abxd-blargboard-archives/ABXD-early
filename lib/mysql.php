<?php
//  AcmlmBoard XD support - MySQL database wrapper functions

if(is_file("lib/database.php"))
	include("database.php");
else
	die("You should <a href=\"install.php\">install</a> the board database first.");

$queries = 0;

mysql_connect("localhost", $dbuser, $dbpass) or Kill("Could not connect to database.");
mysql_select_db($dbname);
unset($dbpass);

function Query($query)
{
	global $queries, $loguser, $thisURL;
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
