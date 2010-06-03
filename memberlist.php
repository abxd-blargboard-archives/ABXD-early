<?php
//  AcmlmBoard XD - Member list page
//  Access: all

include("lib/common.php");

$title = "Member list";

$ppp = $loguser['postsperpage'];
if($ppp<1) $ppp=50;

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

$sort = $_GET['sort'];
$sex = $_GET['sex'];
if(isset($_GET['pow']) && $_GET['pow'] != "")
	$pow = (int)$_GET['pow'];
if(isset($_GET['letter']))
	$letter = $_GET['letter'][0];
else
	$letter = "";
$order = "";
$where = "";

switch($sort)
{
	case "id": $order = "id asc"; break;
	case "name": $order = "name"; break;
	case "reg": $order = "regdate desc"; break;
	case "karma": $order = "karma desc"; break;
	default: $order="posts desc";
}

switch($sex)
{
	case "m": $where = "sex=0"; break;
	case "f": $where = "sex=1"; break;
	case "n": $where = "sex=2"; break;
	default: $where = "1";
}

if(isset($pow))
	$where.= " and powerlevel=".$pow;
if($letter != "")
	$where.= " and name like '".$letter."%'";

$numUsers = FetchResult("select count(*) from users where ".$where, 0, 0);

$qUsers = "select * from users where ".$where." order by ".$order.", name asc limit ".$from.", ".$ppp;
$rUsers = Query($qUsers);

$numonpage = NumRows($rUsers);
for($i = $ppp; $i < $numUsers; $i+=$ppp)
{
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " ".mlink($sort,$sex,$pow,$ppp,$letter,$i).(($i/$ppp)+1)."</a>";
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = "1".$pagelinks;
	else
		$pagelinks = mlink($sort,$sex,$pow,$ppp,$letter,0)."1</a>".$pagelinks;
}

//$numUsers = NumRows($rUsers);
print "<table class=\"outline margin\">";
print "<tr class=\"header0\">";
print "<th colspan=\"8\">Options</th>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"8\">".$numUsers." user".(($numUsers>1 || $numUsers==0) ? "s" : "")." found.</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Sort by</td>";
print "<td colspan=\"6\">";
print mlink(""     ,$sex,$pow,$ppp,$letter)."Posts</a> | ";
print mlink("id"   ,$sex,$pow,$ppp,$letter)."ID</a> | ";
print mlink("name" ,$sex,$pow,$ppp,$letter)."Username</a> | ";
print mlink("karma",$sex,$pow,$ppp,$letter)."Karma</a> | ";
print mlink("reg"  ,$sex,$pow,$ppp,$letter)."Registration date</a>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Sex</td>";
print "<td colspan=\"6\">";
print mlink($sort,"m",$pow,$ppp,$letter)."Male</a> | ";
print mlink($sort,"f",$pow,$ppp,$letter)."Female</a> | ";
print mlink($sort,"n",$pow,$ppp,$letter)."N/A</a> | ";
print mlink($sort,"", $pow,$ppp,$letter)."All</a>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Power</td>";
print "<td colspan=\"6\">";
print mlink($sort,$sex,"-1",$ppp,$letter)."Banned</a> | ";
print mlink($sort,$sex, "0",$ppp,$letter)."Normal</a> | ";
print mlink($sort,$sex, "1",$ppp,$letter)."Local moderator</a> | ";
print mlink($sort,$sex, "2",$ppp,$letter)."Full moderator</a> | ";
print mlink($sort,$sex, "3",$ppp,$letter)."Administrator</a> | ";
print mlink($sort,$sex, "", $ppp,$letter)."All</a>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Name</td>";
print "<td colspan=\"6\">";
for($l = 0; $l < 26; $l++)
{
	$let = chr(65+$l);
	print mlink($sort,$sex,$pow,$ppp,$let).$let."</a>".($l < 25 ? " | " :"");
}
print "</td>";
if($pagelinks)
{
	print "</tr>";
	print "<tr class=\"cell2 smallFonts\">";
	print "<td colspan=\"2\">Page</td>";
	print "<td colspan=\"6\">".$pagelinks."</td>";
	print "</tr>";
}

print "<tr class=\"header0\">";
print "<th style=\"width: 30px; \">#</th>";
print "<th style=\"width: 62px; \">Picture</th>";
print "<th>Name</th>";
//print "<th style=\"width: 130px; \">Rank</th>";
print "<th style=\"width: 50px; \">Posts</th>";
print "<th style=\"width: 50px; \">Average</th>";
print "<th style=\"width: 50px; \">Karma</th>";
print "<th style=\"width: 80px; \">Birthday</th>";
print "<th style=\"width: 130px; \">Registered on</th>";
print "</tr>";
if($numUsers)
{
	while($user = Fetch($rUsers))
	{
		$daysKnown = (time()-$user['regdate'])/86400;
		$user['average'] = sprintf("%1.02f", $user['posts'] / $daysKnown);

		$userPic = "";
		if($user['picture'] && $hacks['themenames'] != 3)
			$userPic = "<img src=\"".str_replace("img/avatars/", "img/avatars/", $user['picture'])."\" alt=\"\" style=\"width: 60px;\" />";

		$cellClass = ($cellClass+1) % 2;
		print "<tr class=\"cell".$cellClass."\">";
		print "<td>".$user['id']."</td>";
		print "<td>".$userPic."</td>";
		print "<td>".UserLink($user)."</td>";
//		print "<td>".GetRank($user)."</td>";
		print "<td>".$user['posts']."</td>";
		print "<td>".$user['average']."</td>";
		print "<td>".$user['karma']."</td>";
		print "<td>".($user['birthday'] ? gmdate("M jS",$user['birthday']) : "&nbsp;")."</td>";
		print "<td>".gmdate("M jS Y",$user['regdate'])."</td>";
		print "</tr>";
	}
} else
{
		print "<tr class=\"cell0\">";
		print "<td colspan=\"8\">Nothing here.</td>";
		print "</tr>";
}

if($pagelinks)
{
	print "</tr>";
	print "<tr class=\"cell2 smallFonts\">";
	print "<td colspan=\"2\">Page</td>";
	print "<td colspan=\"4\">".$pagelinks."</td>";
	print "</tr>";
}
print "</table>";

doFooter();

function mlink($sort,$sex,$pow,$ppp,$letter="",$from=1)
{
	return "<a href=\"memberlist.php?"
			.($sort   ?"sort=$sort":"")
			.($sex    ?"&amp;sex=$sex":"")
			.(isset($pow)?"&amp;pow=$pow":"")
			.($letter!=""?"&amp;letter=$letter":"")
			//.($ppp!=50?"&amp;ppp=$ppp":"")
			.($from!=1?"&amp;from=$from":"")
			."\">";
}

?>
