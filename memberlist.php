<?php
//  AcmlmBoard XD - Member list page
//  Access: all

//TODO: pagination

include("lib/common.php");

if($ppp<1) $ppp=50;
if($page<1) $page=1;

$sort = $_GET['sort'];
$sex = $_GET['sex'];
if(isset($_GET['pow']) && $_GET['pow'] != "")
	$pow = (int)$_GET['pow'];
$order = "";
$where = "";

switch($sort)
{
	case "id": $order = "id asc"; break;
	case "name": $order = "name"; break;
	case "reg": $order = "regdate desc"; break;
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

$qUsers = "select * from users where ".$where." order by ".$order;
$rUsers = Query($qUsers);
$numUsers = NumRows($rUsers);
print "<table class=\"outline margin\">";
print "<tr class=\"header0\">";
print "<th colspan=\"5\">Options</th>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"6\">".$numUsers." user".(($numUsers>1 || $numUsers==0) ? "s" : "")." found.</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Sort by</td>";
print "<td colspan=\"4\">";
print mlink(""    ,$sex,$pow,$ppp)."Posts</a> | ";
print mlink("id"  ,$sex,$pow,$ppp)."ID</a> | ";
print mlink("name",$sex,$pow,$ppp)."Username</a> | ";
print mlink("reg" ,$sex,$pow,$ppp)."Registration date</a>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Sex</td>";
print "<td colspan=\"4\">";
print mlink($sort,"m",$pow,$ppp)."Male</a> | ";
print mlink($sort,"f",$pow,$ppp)."Female</a> | ";
print mlink($sort,"n",$pow,$ppp)."N/A</a> | ";
print mlink($sort,"", $pow,$ppp)."All</a>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2 smallFonts\">";
print "<td colspan=\"2\">Power</td>";
print "<td colspan=\"4\">";
print mlink($sort,$sex,"-1",$ppp)."Banned</a> | ";
print mlink($sort,$sex, "0",$ppp)."Normal</a> | ";
print mlink($sort,$sex, "1",$ppp)."Local moderator</a> | ";
print mlink($sort,$sex, "2",$ppp)."Full moderator</a> | ";
print mlink($sort,$sex, "3",$ppp)."Administrator</a> | ";
print mlink($sort,$sex, "",$ppp)."All</a>";
print "</td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th style=\"width: 30px; \">#</th>";
print "<th style=\"width: 62px; \">Picture</th>";
print "<th>Name</th>";
//print "<th style=\"width: 130px; \">Rank</th>";
print "<th style=\"width: 50px; \">Posts</th>";
print "<th style=\"width: 130px; \">Registered on</th>";
print "</tr>";
if($numUsers)
{
	while($user = Fetch($rUsers))
	{
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
		print "<td>".gmdate("M jS Y",$user['regdate'])."</td>";
		print "</tr>";
	}
} else
{
		print "<tr class=\"cell0\">";
		print "<td colspan=\"5\">Nothing here.</td>";
		print "</tr>";
}
print "</table>";

doFooter();

function mlink($sort,$sex,$pow,$ppp,$page=1)
{
	return "<a href=\"memberlist.php?"
			.($sort   ?"sort=$sort":"")
			.($sex    ?"&amp;sex=$sex":"")
			.(isset($pow)?"&amp;pow=$pow":"")
			.($ppp!=50?"&amp;ppp=$ppp":"")
			.($page!=1?"&amp;page=$page":"")
			."\">";
}

?>
