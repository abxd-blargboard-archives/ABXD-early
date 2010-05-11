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
}

$title = "Private messages";

$whereFrom = "where userfrom = ".$user;
if(isset($_GET['showsent']))
	$showSent = "&amp;showsent=1";
else
	$whereFrom = "where userto = ".$user;

$qTotal = "select count(*) from pmsgs ".$whereFrom;
$total = FetchResult($qTotal);

$ppp = $loguser['postsperpage'];

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if($from)
	$links = "<a href=\"private.php?from=".$from.(isset($_GET['showsent']) ? "\">Show received" : "&amp;showsent=1".(isset($_GET['user']) ? "&amp;user=".$user : "")."\">Show sent")."</a> | ";
else
	$links = "<a href=\"private.php".(isset($_GET['showsent']) ? "\">Show received" : "?showsent=1".(isset($_GET['user']) ? "&amp;user=".$user : "")."\">Show sent")."</a> | ";

$links .= "<a href=\"sendprivate.php\">Send PM</a>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php"), $links);

$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id ".$whereFrom." order by date desc limit ".$from.", ".$ppp;
$rPM = Query($qPM);
$numonpage = NumRows($rPM);

for($i = $ppp; $i < $total; $i+=$ppp)
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"private.php?from=".$i."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"private.php".str_replace("&amp;","?",$showSent)."\">1</a>".$pagelinks;
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";
}

print "<table class=\"outline margin\">";
print "<tr class=\"header0\">";
print "<th>&nbsp;</th>";
print "<th style=\"width: 75%;\">Title</th>";
print "<th>".(isset($_GET['showsent']) ? "To" : "From")."</th>";
print "<th>Date</th>";
print "</tr>";

while($pm = Fetch($rPM))
{
	$qUser = "select * from users where id = ".(isset($_GET['showsent']) ? $pm['userto'] : $pm['userfrom']);
	$rUser = Query($qUser);
	if(NumRows($rUser))
		$user = Fetch($rUser);

	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>";
	if(!$pm['msgread'])
		print "<img src=\"img/status/new.png\" alt=\"NEW\" />";
	print "</td>";
	print "<td><a href=\"showprivate.php?id=".$pm['id'].$snoop."\">".$pm['title']."</a></td>";
	print "<td>".(NumRows($rUser) ? UserLink($user) : "???")."</td>";
	print "<td>".gmdate($dateformat,$pm['date'])."</td>";
	print "</tr>";
}
print "</table>";

if($pagelinks)
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php"), $links);

doFooter();

?>
