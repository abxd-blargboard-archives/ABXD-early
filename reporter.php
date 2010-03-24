<?php

include("lib/database.php");
mysql_connect("localhost", $dbuser, $dbpass) or die("Could not connect to database.");
mysql_select_db($dbname);

$callerIP = $_SERVER['REMOTE_ADDR'];
mysql_query("delete from reporters where ip='".$callerIP."'");
mysql_query("insert into reporters (ip, date) values ('".$callerIP."', ".time().")");

print "OK: ".$callerIP;
?>