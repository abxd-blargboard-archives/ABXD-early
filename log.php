<?php
include("lib/common.php");

if($loguser['powerlevel'] < 1)
	Kill("Access denied.");

$logR = Query("select * from reports order by time desc");
while($item = Fetch($logR))
{
	$blar = $item['text'];
	$blar = str_replace("[g]", "", $blar);
	$blar = str_replace("[b]", "", $blar);
	$blar = str_replace("[/]", "", $blar);
	$blar = str_replace("->", "&rarr;", $blar);
	//TODO: Linkify

	$cellClass = ($cellClass + 1) % 2;
	$log .= format(
"
		<tr>
			<td class=\"cell2\">
				{1}
			</td>
			<td class=\"cell{0}\">
				{2}
			</td>
		</tr>
", $cellClass, TimeUnits(time() - $item['time']), $blar);
}

write(
"
	<table>
		<tr class=\"header1\">
			<th>
				Time
			</th>
			<th>
				Event
			</th>
		</tr>
		{0}
	</table>
", $log);

?>