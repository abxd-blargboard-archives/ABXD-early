<?php

include("lib/mysql.php");

Query("drop table if exists ip2c;");
Query("create table if not exists ip2c (ip_from bigint(12) not null, ip_to bigint(12) not null, cc varchar(2) not null) engine=MyISAM default charset=latin1;");

$f = fopen("ip2c.dat", "r");
while (!feof($f))
{
	$b = fgets($f, 4096);
	$b = trim($b);
	//echo $b;
	$i = explode("\t", $b);
	print "(".$i[0].", ".$i[1].", '".$i[2]."')<br />\n";
	Query("insert into ip2c (ip_from, ip_to, cc) values (".$i[0].", ".$i[1].", '".$i[2]."');");
}

print "Done."

?>
