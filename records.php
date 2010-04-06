<?php
//  AcmlmBoard XD - The Records
//  Access: all

include("lib/common.php");

print "<table class=\"outline margin width50\">";
print "<tr class=\"header0\"><th colspan=\"2\">Highest Numbers</th></tr>";

$df = "l, F jS Y, h:i:s";
print "<tr class=\"cell0\">";
print "<td>Highest number of posts in 24 hours</td>";
print "<td><strong>".$misc['maxpostsday']."</strong>, on ".gmdate($df, $misc['maxpostsdaydate'])."</td>";
print "</tr>";

print "<tr class=\"cell1\">";
print "<td>Highest number of posts in one hour</td>";
print "<td><strong>".$misc['maxpostshour']."</strong>, on ".gmdate($df, $misc['maxpostshourdate'])."</td>";
print "</tr>";

print "<tr class=\"cell0\">";
print "<td>Highest number of users in five minutes</td>";
print "<td><strong>".$misc['maxusers']."</strong>, on ".gmdate($df, $misc['maxusersdate'])."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td></td>";
print "<td>".$misc['maxuserstext']."</td>";
print "</tr>";

print "</table>";

$rStats = Query("show table status");
while($stat = Fetch($rStats))
	$tables[$stat['Name']] = $stat;

print "<table class=\"outline margin\">";
print "<tr class=\"header0\"><th colspan=\"7\">Table Status</th></tr>";
print "<tr class=\"header1\">";
print "<th>Name</th>";
print "<th>Rows</th>";
print "<th>Avg. data/row</th>";
print "<th>Data size</th>";
print "<th>Index size</th>";
print "<th>Unused data</th>";
print "<th>Total size</th>";
print "</tr>";
foreach($tables as $table)
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td class=\"cell2\">".$table['Name']."</td>";
	print "<td>".$table['Rows']."</td>";
	print "<td>".sp($table['Avg_row_length'])."</td>";
	print "<td>".sp($table['Data_length'])."</td>";
	print "<td>".sp($table['Index_length'])."</td>";
	print "<td>".sp($table['Data_free'])."</td>";
	print "<td>".sp($table['Data_length']+$table['Index_length'])."</td>";
	print "</tr>";
}

print "</table>";

doFooter();

function sp($sz)
{
	return number_format($sz,0,'.',' ');
}
?>
