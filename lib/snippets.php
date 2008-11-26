<?php
//  AcmlmBoard XD support - Handy snippets

function usectime()
{
	$t=gettimeofday();
	return $t[sec]+$t[usec] / 1000000;
}

function Plural($i, $s)
{
	if($i == 1) //For 1, just return that.
		return $i." ".$s;

	if(substr($s,-1) == "y") //Grammar Nazi strikes back!
		$s = substr($s, 0, strlen($s)-1)."ies"; //query -> queries
	else
		$s .= "s"; //record -> records

	return $i." ".$s;
}

function DoFooter()
{
	global $timeStart, $queries, $overallTidy;
	print "<div class=\"footer\">";
	print "AcmlmBoard XD by Kawa, Arbe &mdash; <em>Release Canditate</em><br />";
	print "Page rendered in ".sprintf("%1.3f",usectime()-$timeStart)." seconds with ".Plural($queries, "MySQL query").".<br />";

	print "<a href=\"http://validator.w3.org/check?uri=referer\"><img src=\"img/xhtml10.png\" alt=\"Valid XHTML 1.0 Strict\" /></a>";
	print "<a href=\"http://jigsaw.w3.org/css-validator/\"><img src=\"img/css.png\" alt=\"Valid CSS!\" /></a>";

	print "<a href=\"rss2.php\"><img src=\"img/rss.png\" alt=\"RSS 2.0 feed available\" /></a>";
	print "<a href=\"http://firefoxmyths.com\"><img src=\"img/fxmyths.png\" alt=\"Firefox Myths\" /></a>";

	print "</div>";
	print "</body>";
	print "</html>";
}

function UserLink($user)
{
	$className = "nc" . $user['sex'] . (($user['powerlevel'] == -1) ? "x" : $user['powerlevel']);
	return "<a href=\"profile.php?id=".$user['id']."\"><span class=\"".$className."\">".$user['name']."</span></a>";
}

function CanMod($userid, $fid)
{
	global $loguser;
	if($loguser['powerlevel'] > 1)
		return 1;
	if($loguser['powerlevel'] == 1)
	{
		$qMods = "select * from forummods where forum=".$fid." and user=".$userid;
		$rMods = Query($qMods);
		if(NumRows($rMods))
			return 1;
	}
	return 0;
}

function MakeCrumbs($path, $links)
{
	print "<table class=\"breadcrumbs\">";
	print "<tr><td>";
	foreach($path as $text=>$link)
	{
		$link = str_replace("&","&amp;",$link);
		if($link)
			$crumbs .= "<a href=\"".$link."\">".$text."</a> - ";
		else
			$crumbs .= $text. " - ";
	}
	print substr($crumbs, 0, strlen($crumbs)-3);
	print "</td><td class=\"right\">".$links."</td></tr>";
	print "</table>";
}

function TimeUnits($sec)
{
	if($sec <    60) return "$sec sec.";
	if($sec <  3600) return floor($sec/60)." min.";
	if($sec < 86400) return floor($sec/3600)." hour".($sec >= 7200 ? "s" : "");
	return floor($sec/86400)." day".($sec >= 172800 ? "s" : "");
}

?>
