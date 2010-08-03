<?php
//  AcmlmBoard XD - Private message inbox/outbox viewer
//  Access: users

include("lib/common.php");

if(!$loguserid)
	Kill("You must be logged in to view your private messages.");

$user = $loguserid;
if(isset($_GET['user']) && $loguser['powerlevel'] > 2)
{
	$user = (int)$_GET['user'];
	$snoop = "&amp;snooping=1";
	$userGet = "&amp;user=".$user;
}

$title = "Private messages";

if(isset($_GET['del']))
{
	$pid = (int)$_GET['del'];
	$rPM = Query("select * from pmsgs where id = ".$pid." and (userto = ".$loguserid." or userfrom = ".$loguserid.")");
	if(NumRows($rPM))
	{
		$pm = Fetch($rPM);
		$val = $pm['userto'] == $loguserid ? 2 : 1;
		$newVal = ($pm['deleted'] | $val);
		if($newVal == 3)
		{
			Query("delete from pmsgs where id = ".$pid);
			Query("delete from pmsgs_text where pid = ".$pid);
		}
		else
			Query("update pmsgs set deleted = ".$newVal." where id = ".$pid);
		Alert("Private message deleted.");
	}
}

$whereFrom = "userfrom = ".$user;
if(isset($_GET['showsent']))
{
	$showSent = "&amp;showsent=1";
	$deleted = 1;
}
else
{
	$whereFrom = "userto = ".$user;
	$deleted = 2;
}

$qTotal = "select count(*) from pmsgs where ".$whereFrom." and deleted != ".$deleted;
$total = FetchResult($qTotal);

$ppp = $loguser['postsperpage'];

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;


$links = "<a href=\"private.php".(isset($_GET['showsent']) ? str_replace("&amp;", "?", $userGet) . "\">Show received" : "?showsent=1".$userGet."\">Show sent")."</a> | ";

$links .= "<a href=\"sendprivate.php\">Send PM</a>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php"), $links);

$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where ".$whereFrom." and deleted != ".$deleted." order by date desc limit ".$from.", ".$ppp;
$rPM = Query($qPM);
$numonpage = NumRows($rPM);

for($i = $ppp; $i < $total; $i+=$ppp)
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"private.php?from=".$i.$showSent.$userGet."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"private.php".str_replace("&amp;","?", $showSent).$userGet."\">1</a>".$pagelinks;
	write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);
}

if(NumRows($rPM))
{
	while($pm = Fetch($rPM))
	{
		$qUser = "select * from users where id = ".(isset($_GET['showsent']) ? $pm['userto'] : $pm['userfrom']);
		$rUser = Query($qUser);
		if(NumRows($rUser))
			$user = Fetch($rUser);

		$cellClass = ($cellClass+1) % 2;
		if(!$pm['msgread'])
			$img = "<img src=\"img/status/new.png\" alt=\"New!\" />";
		else
			$img = "";

		$sender = (NumRows($rUser) ? UserLink($user) : "???");

		$pms .= format(
"
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td>
				<a href=\"showprivate.php?id={2}{3}\">{4}</a>
			</td>
			<td>
				{5}
			</td>
			<td>
				{6}
			</td>
			{7}
		</tr>
",	$cellClass, $img, $pm['id'], $snoop, htmlspecialchars($pm['title']), $sender, cdate($dateformat,$pm['date']), $snoop == "" ? "<td><a href=\"private.php?del=".$pm['id']."\">del</a>" : "");
	}
}
else
	$pms = format(
"
		<tr class=\"cell1\">
			<td colspan=\"4\">
				You have no messages.
			</td>
		</tr>
");

write(
"
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th>&nbsp;</th>
			<th style=\"width: 75%;\">Title</th>
			<th>{0}</th>
			<th>Date</th>
			{2}
		</tr>
		{1}
	</table>
", (isset($_GET['showsent']) ? "To" : "From"), $pms, $snoop == "" ? "<th></th>" : "");

if($pagelinks)
	write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php"), $links);

?>
