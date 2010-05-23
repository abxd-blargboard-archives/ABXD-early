<?php
include("lib/common.php");
$title = "Last known browsers";
$isMod = $loguser['powerlevel'] > 0;
$peeps = Query("SELECT `id`, `name`, `lastip`, `lastknownbrowser`, `sex`, `powerlevel` FROM `users` ORDER BY `id` ASC LIMIT 0, 30");
print "<table class=\"outline margin\">\n";
print "<tr class=\"header1\"><th>ID</th><th>Name</th><th>&nbsp;</th>";
if($isMod)
	print "<th>IP</th>";
print "<th>Last known browser</th></tr>\n";
while($peep = Fetch($peeps))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\"><td>".$peep['id']."</td><td>".UserLink($peep)."</td><td>".IP2C($peep['lastip'])."</td>";
	if($isMod)
		print "<td>".$peep['lastip']."</td>";
	print "<td>".str_replace("-->", "", str_replace("<!--", " --", $peep['lastknownbrowser']))."</td></tr>\n";
}
print "</table>";
doFooter();


function IP2C($ip)
{
	$q = @mysql_query("select cc from ip2c where ip_from <= inet_aton('".$ip."') and ip_to >= inet_aton('".$ip."')") or $r['cc'] = "";
	if($q) $r = @mysql_fetch_array($q);
	if($r['cc'])
		return " <img src=\"img/flags/".strtolower($r['cc']).".png\" alt=\"".$r['cc']."\" title=\"".$r['cc']."\" />";
}

?>