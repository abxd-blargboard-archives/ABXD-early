<?php
include("lib/common.php");
$title = "Ranks";
$users = array();
$rUsers = Query("select id, name, displayname, powerlevel, sex, posts from users order by id asc");
while($user = Fetch($rUsers))
	$users[$user['id']] = $user;
$rankset = isset($_POST['rankset']) ? (int)$_POST['rankset'] : 1;
$ranks = array();
$rRanks = Query("select num, text from ranks where rset=".$rankset." order by num asc");
while($rank = Fetch($rRanks))
	$ranks[] = $rank;

$rSets = Query("select * from ranksets order by id asc");
$selected[$rankset] = " selected = \"selected\"";
$ranksets = "";
while($set = Fetch($rSets))
	$ranksets .= format(
"
					<option value=\"{0}\"{1}>{2}</option>
",	$set['id'], $selected[$set['id']], $set['name']);

write(
"
<form action=\"ranks.php\" method=\"post\" id=\"myForm\">
	<table class=\"outline margin width25\">
		<tr class=\"header0\">
			<th colspan=\"2\">
				User ranks
			</th>
		</tr>
		<tr class=\"cell0\">
			<td>
				Set
			</td>
			<td>
				<select name=\"rankset\" size=\"1\" onchange=\"myForm.submit();\">
					{0}
				</select>
				<input type=\"submit\" value=\"Change\" />
			</td>
		</tr>
	</table>
</form>
", $ranksets);

$ranklist = "";
for($i = 0; $i < count($ranks); $i++)
{
	$rank = $ranks[$i];
	$nextRank = $ranks[$i+1];
	$members = array();
	foreach($users as $user)
	{
		if($user['posts'] >= $rank['num'] && $user['posts'] < $nextRank['num'])
			$members[] = $comma.UserLink($user);
	}
	$rankText = ($loguser['powerlevel'] > 0 || $loguser['posts'] >= $rank['num'] || count($members) > 0) ? str_replace("<br />", " ", $rank['text']) : "???";
	if(count($members) == 0)
		$members = "&nbsp;";
	else
		$members = join(", ", $members);

	$cellClass = ($cellClass+1) % 2;
	
	$ranklist .= format(
"
	<tr class=\"cell{0}\">
		<td class=\"cell2\">{1}</td>
		<td>{2}</td>
		<td>{3}</td>
	</tr>
", $cellClass, $rankText, $rank['num'], $members);
}
write(
"
<table class=\"width75 margin outline\">
	<tr class=\"header1\">
		<th>
			Rank
		</th>
		<th>
			To&nbsp;get
		</th>
		<th>
			&nbsp;
		</th>
	</tr>
	{0}
</table>
",	$ranklist);

?>