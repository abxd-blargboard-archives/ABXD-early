<?php
include("lib/common.php");
$users = array();
$rUsers = Query("select id, name, powerlevel, sex, posts from users order by id asc");
while($user = Fetch($rUsers))
	$users[$user['id']] = $user;
$rankset = isset($_POST['rankset']) ? (int)$_POST['rankset'] : 1;
$ranks = array();
$rRanks = Query("select num, text from ranks where rset=".$rankset." order by num asc");
while($rank = Fetch($rRanks))
	$ranks[] = $rank;
print "<form action=\"ranks.php\" method=\"post\" id=\"myForm\">";
print "<table class=\"outline margin width25\">";
print "<tr class=\"header1\">";
print "<th colspan=\"2\">User ranks</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Set</td>";
print "<td><select name=\"rankset\" size=\"1\" onchange=\"myForm.submit();\">";
$rSets = Query("select * from ranksets order by id asc");
$selected[$rankset] = " selected = \"selected\"";
while($set = Fetch($rSets))
	print "<option value=\"".$set['id']."\"".$selected[$set['id']].">".$set['name']."</option>";
print "</option><input type=\"submit\" value=\"Change\"></input></td>";
print "</tr>";
print "</table>";
print "</form>";


print "<table class=\"width75 margin outline\">";
print "<tr class=\"header0\"><th>Rank</th><th>To get</th><th>&nbsp;</th></tr>";
for($i = 0; $i < count($ranks); $i++)
{
	$rank = $ranks[$i];
	$nextRank = $ranks[$i+1];
	$members = "";
	$comma = "";
	foreach($users as $user)
	{
		if($user['posts'] >= $rank['num'] && $user['posts'] < $nextRank['num'])
		{
			$members .= $comma.UserLink($user);
			$comma = ", ";
		}
	}
	$rankText = ($loguser['posts'] >= $rank['num'] || $members) ? str_replace("<br />", " ", $rank['text']) : "???";
	if($members == "")
		$members = "&nbsp;";
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td class=\"cell2\">".$rankText."</td>";
	print "<td>".$rank['num']."</td>";
	print "<td>".$members."</td>";
	print "</tr>";
}
print "</table>";
doFooter();
?>