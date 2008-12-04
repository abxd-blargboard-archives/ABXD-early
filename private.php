<?php
//  AcmlmBoard XD - Private message inbox/outbox viewer
//  Access: users

include("lib/common.php");

if(!$loguserid)
	Kill("You must be logged in to view your private messages.");

$whereFrom = "where userfrom = ".$loguserid;
if(isset($_GET['showsent']))
	$showSent = "&amp;showsent=1";
else
	$whereFrom = "where userto = ".$loguserid;

$qTotal = "select count(*) from pmsgs ".$whereFrom;
$total = FetchResult($qTotal);

$ppp = $loguser['postsperpage'];

if(isset($_GET['from']))
	$from = $_GET['from'];
else
	$from = 0;

if($from)
	$links = "<a href=\"private.php?from=".$from.(isset($_GET['showsent']) ? "\">Show recieved" : "&amp;showsent=1\">Show sent")."</a> | ";
else
	$links = "<a href=\"private.php".(isset($_GET['showsent']) ? "\">Show recieved" : "?showsent=1\">Show sent")."</a> | ";

$links .= "<a href=\"sendprivate.php\">Send PM</a>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php"), $links);

$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id ".$whereFrom." order by date desc limit ".$from.", ".$ppp;
$rPM = Query($qPM);
$numonpage = NumRows($rPM);

for($i = $ppp; $i < $total; $i+=$ppp)
	$pagelinks .= " <a href=\"private.php?from=".$i."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	$pagelinks = "<a href=\"private.php".str_replace("&amp;","?",$showSent)."\">1</a>".$pagelinks;
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";
}

print "<div class=\"border\">";
print "<table>";
print "<tr class=\"header0\">";
print "<th>&nbsp;</th>";
print "<th style=\"width: 75%;\">Title</th>";
print "<th>From</th>";
print "<th>Date</th>";
print "</tr>";

while($pm = Fetch($rPM))
{
	$qUser = "select * from users where id = ".$pm['userfrom'];
	$rUser = Query($qUser);
	if(NumRows($rUser))
		$user = Fetch($rUser);

	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>";
	if(!$pm['msgread'])
		print "<img src=\"img/status/new.png\" alt=\"NEW\" />";
	print "</td>";
	print "<td><a href=\"showprivate.php?id=".$pm['id']."\">".$pm['title']."</a></td>";
	print "<td>".(NumRows($rUser) ? UserLink($user) : "???")."</td>";
	print "<td>".date($dateformat,$pm['date'])."</td>";
	print "</tr>";
}
print "</table>";
print "</div>";

if($pagelinks)
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php"), $links);

?>
