<?php
//  AcmlmBoard XD - The Records
//  Access: all

include("lib/common.php");

$df = "l, F jS Y, G:i:s";

write(
"
<table class=\"outline margin width75\">
	<tr class=\"header0\">
		<th colspan=\"2\">
			Highest Numbers
		</th>
	</tr>
	<tr class=\"cell0\">
		<td>
			Highest number of posts in 24 hours
		</td>
		<td>
			<strong>{0}</strong>, on {1} GMT
		</td>
	</tr>
	<tr class=\"cell1\">
		<td>
			Highest number of posts in one hour
		</td>
		<td>
			<strong>{2}</strong>, on {3} GMT
		</td>
	</tr>
	<tr class=\"cell0\">
		<td>
			Highest number of users in five minutes
		</td>
		<td>
			<strong>{4}</strong>, on {5} GMT
		</td>
	</tr>
	<tr class=\"cell1\">
		<td></td>
		<td>
			{6}
		</td>
	</tr>
</table>
",	$misc['maxpostsday'], gmdate($df, $misc['maxpostsdaydate']),
	$misc['maxpostshour'], gmdate($df, $misc['maxpostshourdate']),
	$misc['maxusers'], gmdate($df, $misc['maxusersdate']),
	$misc['maxuserstext']);

$rStats = Query("show table status");
while($stat = Fetch($rStats))
	$tables[$stat['Name']] = $stat;

$tablelist = "";
foreach($tables as $table)
{
	$cellClass = ($cellClass+1) % 2;
	$tablelist .= format(
"
	<tr class=\"cell{0}\">
		<td class=\"cell2\">{1}</td>
		<td>
			{2}
		</td>
		<td>
			{3}
		</td>
		<td>
			{4}
		</td>
		<td>
			{5}
		</td>
		<td>
			{6}
		</td>
		<td>
			{7}
		</td>
	</tr>
",	$cellClass, $table['Name'], $table['Rows'], sp($table['Avg_row_length']),
	sp($table['Data_length']), sp($table['Index_length']), sp($table['Data_free']),
	sp($table['Data_length'] + $table['Index_length']));
}

write(
"
<table class=\"outline margin\">
	<tr class=\"header0\">
		<th colspan=\"7\">
			Table Status
		</th>
	</tr>
	<tr class=\"header1\">
		<th>
			Name
		</th>
		<th>
			Rows
		</th>
		<th>
			Avg. data/row
		</th>
		<th>
			Data size
		</th>
		<th>
			Index size
		</th>
		<th>
			Unused data
		</th>
		<th>
			Total size
		</th>
	</tr>
	{0}
</table>
", $tablelist);

function sp($sz)
{
	return number_format($sz,0,'.',',');
}
?>
