<?php
//  AcmlmBoard XD - Member list page
//  Access: all

include("lib/common.php");

$title = "Member list";

AssertForbidden("viewMembers");

$ppp = $loguser['postsperpage'];
if($ppp<1) $ppp=50;

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(isset($dir)) unset($dir);
if(isset($_GET['dir']))
{
	$dir = $_GET['dir'];
	if($dir != "asc" && $dir != "desc")
		unset($dir);
}
$sort = $_GET['sort'];
$sex = $_GET['sex'];
if(isset($_GET['pow']) && $_GET['pow'] != "")
	$pow = (int)$_GET['pow'];
if(isset($_GET['letter']) && is_string($_GET['letter']))
	$letter = $_GET['letter'][0];
else
	$letter = "";
$order = "";
$where = "";

switch($sort)
{
	case "id": $order = "id ".(isset($dir) ? $dir : "asc"); break;
	case "name": $order = "name ".(isset($dir) ? $dir : "asc"); break;
	case "reg": $order = "regdate ".(isset($dir) ? $dir : "desc"); break;
	case "karma": $order = "karma ".(isset($dir) ? $dir : "desc"); break;
	default: $order="posts ".(isset($dir) ? $dir : "desc");
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
{
	if($letter == "@")	//I can't figure it out. Anybody else?
		$where.= " and substring(name, 1,1) regexp '[:punct:]' or substring(displayname, 1,1) regexp '[:punct:]'";
	if($letter == "#")
		$where.= " and substring(name, 1,1) regexp '[0-9]' or substring(displayname, 1,1) regexp '[0-9]'";
	else
		$where.= " and name like '".$letter."%' or displayname like '".$letter."%'";
}

if(!(isset($pow) && $pow == 5))
	$where.= " and powerlevel < 5";

$numUsers = FetchResult("select count(*) from users where ".$where, 0, 0);

$qUsers = "select * from users where ".$where." order by ".$order.", name asc limit ".$from.", ".$ppp;
$rUsers = Query($qUsers);

$numonpage = NumRows($rUsers);
for($i = $ppp; $i < $numUsers; $i+=$ppp)
{
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " ".mlink($sort,$sex,$pow,$ppp,$letter,$dir,$i).(($i/$ppp)+1)."</a>";
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = "1".$pagelinks;
	else
		$pagelinks = mlink($sort,$sex,$pow,$ppp,$letter,$dir,0)."1</a>".$pagelinks;
}

//$alphabet .= "<li>".mlink($sort,$sex,$pow,$ppp,"@",$dir)."@</a></li>\n";
$alphabet .= "<li>".mlink($sort,$sex,$pow,$ppp,"%23",$dir)."#</a></li>\n";
for($l = 0; $l < 26; $l++)
{
	$let = chr(65+$l);
	$alphabet .= "<li>".mlink($sort,$sex,$pow,$ppp,$let,$dir).$let."</a></li>\n";
}
$alphabet .= "<li>".mlink($sort,$sex,$pow,$ppp,"",$dir)."All</a></li>\n";

write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"8\">
				Options
			</th>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"8\">
				{0} found.
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Sort by
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{1}
					</li>
					<li>
						{2}
					</li>
					<li>
						{3}
					</li>
					<li>
						{4}
					</li>
					<li>
						{5}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Order
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{6}
					</li>
					<li>
						{7}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Sex
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{8}
					</li>
					<li>
						{9}
					</li>
					<li>
						{10}
					</li>
					<li>
						{11}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Power
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{12}
					</li>
					<li>
						{13}
					</li>
					<li>
						{14}
					</li>
					<li>
						{15}
					</li>
					<li>
						{16}
					</li>
					<li>
						{17}
					</li>
					<li>
						{18}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Name
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					{19}
				</ul>
			</td>
		</tr>
",	Plural($numUsers, "user"),

	mlink(""     ,$sex,$pow,$ppp,$letter,$dir)."Posts</a>",
	mlink("id"   ,$sex,$pow,$ppp,$letter)."ID</a>",
	mlink("name" ,$sex,$pow,$ppp,$letter,$dir)."Username</a>",
	mlink("karma",$sex,$pow,$ppp,$letter,$dir)."Karma</a>",
	mlink("reg"  ,$sex,$pow,$ppp,$letter,$dir)."Registration date</a>",

	mlink($sort,$sex,$pow,$ppp,$letter,"asc")."Ascending</a>",
	mlink($sort,$sex,$pow,$ppp,$letter,"desc")."Descending</a>",

	mlink($sort,"m",$pow,$ppp,$letter,$dir)."Male</a>",
	mlink($sort,"f",$pow,$ppp,$letter,$dir)."Female</a>",
	mlink($sort,"n",$pow,$ppp,$letter,$dir)."N/A</a>",
	mlink($sort,"", $pow,$ppp,$letter,$dir)."All</a>",
	
	mlink($sort,$sex,"-1",$ppp,$letter,$dir)."Banned</a>",
	mlink($sort,$sex, "0",$ppp,$letter,$dir)."Normal</a>",
	mlink($sort,$sex, "1",$ppp,$letter,$dir)."Local moderator</a>", 
	mlink($sort,$sex, "2",$ppp,$letter,$dir)."Full moderator</a>",
	mlink($sort,$sex, "3",$ppp,$letter,$dir)."Administrator</a>",
	mlink($sort,$sex, "4",$ppp,$letter,$dir)."Root</a>",
	mlink($sort,$sex, "", $ppp,$letter,$dir)."All</a>",
	
	$alphabet
);

if($pagelinks)
{
	write(
"
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Page
			</td>
			<td colspan=\"6\">
				{0}
			</td>
		</tr>
",	$pagelinks);
}

$memberList = "";
if($numUsers)
{
	while($user = Fetch($rUsers))
	{
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		$daysKnown = (time()-$user['regdate'])/86400;
		$user['average'] = sprintf("%1.02f", $user['posts'] / $daysKnown);

		$userPic = "";
		if($user['picture'] && $hacks['themenames'] != 3)
			$userPic = "<img src=\"".str_replace("img/avatars/", "img/avatars/", $user['picture'])."\" alt=\"\" style=\"width: 60px;\" />";

		$cellClass = ($cellClass+1) % 2;
		$memberList .= format(
"
		<tr class=\"cell{0}\">
			<td>{1}</td>
			<td>{2}</td>
			<td>{3}</td>
			<td>{4}</td>
			<td>{5}</td>
			<td>{6}</td>
			<td>{7}</td>
			<td>{8}</td>
		</tr>
",	$cellClass, $user['id'], $userPic, UserLink($user), $user['posts'],
	$user['average'], $user['karma'],
	($user['birthday'] ? cdate("M jS", $user['birthday']) : "&nbsp;"),
	cdate("M jS Y", $user['regdate'])
	);
	}
} else
{
	$memberList = format(
"
		<tr class=\"cell0\">
			<td colspan=\"8\">
				Nothing here.
			</td>
		</tr>
");
}

write(
"
		<tr class=\"header1\">
			<th style=\"width: 30px; \">#</th>
			<th style=\"width: 62px; \">Picture</th>
			<th>Name</th>
			<th style=\"width: 50px; \">Posts</th>
			<th style=\"width: 50px; \">Average</th>
			<th style=\"width: 50px; \">Karma</th>
			<th style=\"width: 80px; \">Birthday</th>
			<th style=\"width: 130px; \">Registered on</th>
		</tr>
		{0}
",	$memberList);

if($pagelinks)
{
	write(
"
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Page
			</td>
			<td colspan=\"6\">
				{0}
			</td>
		</tr>
",	$pagelinks);
}

write("
	</table>
");

function mlink($sort,$sex,$pow,$ppp,$letter="",$dir="",$from=1)
{
	return "<a href=\"memberlist.php?"
			.($sort   ?"sort=$sort":"")
			.($sex    ?"&amp;sex=$sex":"")
			.(isset($pow)?"&amp;pow=$pow":"")
			.($letter!=""?"&amp;letter=$letter":"")
			.($dir   ?"&dir=$dir":"")
			.($from!=1?"&amp;from=$from":"")
			."\">";
}

?>
