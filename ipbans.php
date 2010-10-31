<?php
//  AcmlmBoard XD - IP ban management tool
//  Access: administrators only

include("lib/common.php");

$title = "IP bans";

AssertForbidden("editIPBans");

if($loguser['powerlevel'] < 3)
	Kill("Only administrators get to manage IP bans.");

MakeCrumbs(array("Main"=>"./", "IP ban manager"=>""), "");

if($_POST['action'] == "Add")
{
	$qIPBan = "insert into ipbans (ip, reason, date) values ('".justEscape($_POST['ip'])."', '".justEscape($_POST['reason'])."', ".((int)$_POST['days'] > 0 ? time() + ((int)$_POST['days'] * 86400) : 0).")";
	$rIPBan = Query($qIPBan);
	Alert("Added.","Notice");
} elseif($_GET['action'] == "delete")
{
	$qIPBan = "delete from ipbans where ip='".justEscape($_GET['ip'])."' limit 1";
	$rIPBan = Query($qIPBan);
	Alert("Removed.","Notice");
}

$qIPBan = "select * from ipbans order by date desc";
$rIPBan = Query($qIPBan);

$banList = "";
while($ipban = Fetch($rIPBan))
{
	$cellClass = ($cellClass+1) % 2;
	if($ipban['date'])
		$date = gmdate($dateformat,$ipban['date'])." (".TimeUnits($ipban['date']-time())." left)";
	else
		$date = "Permanent";
	$banList .= format(
"
	<tr class=\"cell{0}\">
		<td>
			{1}
		</td>
		<td>
			{2}
		</td>
		<td>
			{3}
		</td>
		<td>
			<a href=\"ipbans.php?ip={1}&amp;action=delete\">del</a>
		</td>
	</tr>
", $cellClass, $ipban['ip'], $ipban['reason'], $date);
}

write("
<table class=\"outline margin width50\">
	<tr class=\"header1\">
		<th>IP</th>
		<th>Reason</th>
		<th>Date</th>
		<th>&nbsp;</th>
	</tr>
	{0}
</table>

<form action=\"ipbans.php\" method=\"post\">
	<table class=\"outline margin width50\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				Add
			</th>
		</tr>
		<tr>
			<td class=\"cell2\">
				IP
			</td>
			<td class=\"cell0\">
				<input type=\"text\" name=\"ip\" style=\"width: 98%;\" maxlength=\"25\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				Reason
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"reason\" style=\"width: 98%;\" maxlength=\"25\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				For
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"days\" size=\"13\" maxlength=\"13\" /> days
			</td>
		</tr>
		<tr class=\"cell2\">
			<td></td>
			<td>
				<input type=\"submit\" name=\"action\" value=\"Add\" />
			</td>
		</tr>
	</table>
</form>
", $banList);

MakeCrumbs(array("Main"=>"./", "IP ban manager"=>""), "");

?>
