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
if(isset($_GET['letter']) && is_string($_GET['letter']))
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
		$pagelinks .= " ".mlink($sort,$sex,$pow,$ppp,$letter,$i).(($i/$ppp)+1)."</a>";
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = "1".$pagelinks;
	else
		$pagelinks = mlink($sort,$sex,$pow,$ppp,$letter,0)."1</a>".$pagelinks;
}

for($l = 0; $l < 26; $l++)
{
	$let = chr(65+$l);
	$alphabet .= "<li>".mlink($sort,$sex,$pow,$ppp,$let).$let."</a></li>\n";
}

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
				Sex
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{6}
					</li>
					<li>
						{7}
					</li>
					<li>
						{8}
					</li>
					<li>
						{9}
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
						{10}
					</li>
					<li>
						{11}
					</li>
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
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				Name
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					{16}
				</ul>
			</td>
		</tr>
",	Plural($numUsers, "user"),

	mlink(""     ,$sex,$pow,$ppp,$letter)."Posts</a>",
	mlink("id"   ,$sex,$pow,$ppp,$letter)."ID</a>",
	mlink("name" ,$sex,$pow,$ppp,$letter)."Username</a>",
	mlink("karma",$sex,$pow,$ppp,$letter)."Karma</a>",
	mlink("reg"  ,$sex,$pow,$ppp,$letter)."Registration date</a>",

	mlink($sort,"m",$pow,$ppp,$letter)."Male</a>",
	mlink($sort,"f",$pow,$ppp,$letter)."Female</a>",
	mlink($sort,"n",$pow,$ppp,$letter)."N/A</a>",
	mlink($sort,"", $pow,$ppp,$letter)."All</a>",
	
	mlink($sort,$sex,"-1",$ppp,$letter)."Banned</a>",
	mlink($sort,$sex, "0",$ppp,$letter)."Normal</a>",
	mlink($sort,$sex, "1",$ppp,$letter)."Local moderator</a>", 
	mlink($sort,$sex, "2",$ppp,$letter)."Full moderator</a>",
	mlink($sort,$sex, "3",$ppp,$letter)."Administrator</a>",
	mlink($sort,$sex, "", $ppp,$letter)."All</a>",
	
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

function mlink($sort,$sex,$pow,$ppp,$letter="",$from=1)
{
	return "<a href=\"memberlist.php?"
			.($sort   ?"sort=$sort":"")
			.($sex    ?"&amp;sex=$sex":"")
			.(isset($pow)?"&amp;pow=$pow":"")
			.($letter!=""?"&amp;letter=$letter":"")
			.($from!=1?"&amp;from=$from":"")
			."\">";
}

?>
