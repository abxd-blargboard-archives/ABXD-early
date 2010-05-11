<?php
//  AcmlmBoard XD - IP ban management tool
//  Access: administrators only

include("lib/common.php");

$title = "IP bans";

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

print "<table class=\"outline margin width50\">";
print "<tr class=\"header1\">";
print "<th>IP</th>";
print "<th>Reason</th>";
print "<th>Date</th>";
print "<th>&nbsp;</th>";
print "</tr>";

while($ipban = Fetch($rIPBan))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$ipban['ip']."</td>";
	print "<td>".$ipban['reason']."</td>";
	if($ipban['date'])
		print "<td>".gmdate($dateformat,$ipban['date'])." (".TimeUnits($ipban['date']-time())." left)</td>";
	else
		print "<td>Permanent</td>";
	print "<td><a href=\"ipbans.php?ip=".$ipban['ip']."&amp;action=delete\">del</a></td>";
	print "</tr>";
}
print "</table>";

print "<form action=\"ipbans.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";
print "<tr class=\"header1\">";
print "<th colspan=\"2\">Add</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell2\">IP:</td>";
print "<td class=\"cell0\"><input type=\"text\" name=\"ip\" style=\"width: 98%;\" maxlength=\"25\"></input></td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell2\">Reason:</td>";
print "<td class=\"cell1\"><input type=\"text\" name=\"reason\" style=\"width: 98%;\" maxlength=\"25\"></input></td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell2\">For:</td>";
print "<td class=\"cell1\"><input type=\"text\" name=\"days\" size=\"13\" maxlength=\"13\"></input> days</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td><input type=\"submit\" name=\"action\" value=\"Add\"></input></td>";
print "</tr>";
print "</table>";
print "</form>";

MakeCrumbs(array("Main"=>"./", "IP ban manager"=>""), "");

doFooter();

?>
