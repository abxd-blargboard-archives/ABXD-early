<?php
//  AcmlmBoard XD - Realtime visitor statistics page
//  Access: all

include("lib/common.php");

$time = (int) $_GET['time'];
if(!$time) $time = 300;

$qUsers  = "select * from users where lastactivity > ".(time()-$time)." order by lastactivity desc";
$qGuests = "select * from guests where date > ".(time()-$time)." and bot = 0 order by date desc";
$qBots   = "select * from guests where date > ".(time()-$time)." and bot = 1 order by date desc";
$rUsers = Query($qUsers);
$rGuests = Query($qGuests);
$rBots = Query($qBots);

print "<div class=\"border\">";
print "<table>";
print "<tr class=\"header0\">";
print "<th colspan=\"5\">Online users</th>";
print "</tr>";
print "<tr class=\"header1\">";
print "<th style=\"width: 30px;\">#</th>";
print "<th>Name</th>";
print "<th style=\"width: 140px;\">Last view</th>";
print "<th style=\"width: 140px;\">Last post</th>";
print "<th>URL</th>";
print "</tr>";

$i = 1;
while($user = Fetch($rUsers))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$i."</td>";
	print "<td>".UserLink($user)."</td>";
	print "<td>".date("d-m-y G:i:s",$user['lastactivity'])."</td>";
	print "<td>".($user['lastposttime'] ? date("d-m-y G:i:s",$user['lastposttime']) : "Never")."</td>";
	print "<td>".($user['lasturl'] ? "<a href=\"".$user['lasturl']."\">".str_replace(array("%20","_")," ", $user['lasturl'])."</a>" : "None")."</td>";
	print "</tr>";
}

print "</table>";

print "<table>";
print "<tr class=\"header0\">";
print "<th colspan=\"5\">Guests</th>";
print "</tr>";
print "<tr class=\"header1\">";
print "<th style=\"width: 30px;\">#</th>";
print "<th>User agent</th>";
print "<th style=\"width: 140px;\">Last view</th>";
print "<th>URL</th>";
print "<th style=\"width: 120px;\">IP</th>";
print "</tr>";

if(NumRows($rGuests))
{
	$i = 1;
	while($guest = Fetch($rGuests))
	{
		$cellClass = ($cellClass+1) % 2;
		print "<tr class=\"cell".$cellClass."\">";
		print "<td>".$i."</td>";
		print "<td title=\"".htmlspecialchars($guest['useragent'])."\">".htmlspecialchars(substr($guest['useragent'], 0, 65))."</td>";
		print "<td>".date("d-m-y G:i:s",$guest['date'])."</td>";
		print "<td>".($guest['date'] ? "<a href=\"".$guest['lasturl']."\">".str_replace(array("%20","_")," ", $guest['lasturl'])."</a>" : "None")."</td>";
		print "<td>".$guest['ip']."</td>";
		print "</tr>";
	}
} else
	print "<tr class=\"cell0\"><td colspan=\"5\">No guests</td></tr>";

print "<tr class=\"header0\"><th colspan=\"5\">Bots</th></tr>";

if(NumRows($rBots))
{
	$i = 1;
	while($bot = Fetch($rBots))
	{
		$cellClass = ($cellClass+1) % 2;
		print "<tr class=\"cell".$cellClass."\">";
		print "<td>".$i."</td>";
		print "<td title=\"".htmlspecialchars($bot['useragent'])."\">".htmlspecialchars(substr($bot['useragent'], 0, 65))."</td>";
		print "<td>".date("d-m-y G:i:s",$bot['date'])."</td>";
		print "<td>".($bot['date'] ? "<a href=\"".$bot['lasturl']."\">".str_replace(array("%20","_")," ", $bot['lasturl'])."</a>" : "None")."</td>";
		print "<td>".$bot['ip']."</td>";
		print "</tr>";
	}
} else
	print "<tr class=\"cell0\"><td colspan=\"5\">No bots</td></tr>";

print "</table>";
print "</div>";

doFooter();

?>
