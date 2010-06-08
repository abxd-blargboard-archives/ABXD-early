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
	global $timeStart, $queries, $overallTidy, $boardname, $title;
	print "<div class=\"footer\">";
	print "AcmlmBoard XD by Kawa, Arbe, Mega-Mario et al &mdash; version 1.3.1<br />";
	print "AcmlmBoard &copy; Jean-Fran&ccedil;ois Lapointe<br/>";
	print "Page rendered in ".sprintf("%1.3f",usectime()-$timeStart)." seconds with ".Plural($queries, "MySQL query").".<br />";

	print "<a href=\"http://validator.w3.org/check?uri=referer\"><img src=\"img/xhtml10.png\" alt=\"Valid XHTML 1.0 Strict\" /></a>";
	print "<a href=\"http://jigsaw.w3.org/css-validator/\"><img src=\"img/css.png\" alt=\"Valid CSS!\" /></a>";

	print "<a href=\"rss2.php\"><img src=\"img/rss.png\" alt=\"RSS 2.0 feed available\" /></a>";
	print "<a href=\"http://home.comcast.net/~SupportCD/FirefoxMyths.html\"><img src=\"img/fxmyths.png\" alt=\"Firefox Myths\" /></a>";
	print "<a href=\"http://opera.com\"><img src=\"img/opera.png\" alt=\"Opera!\" /></a>";

	print "</div>";
	print "</div>";
	print "</body>";
	print "</html>";
	
	$boardTitle = $boardname;
	if($title != "")
		$boardTitle .= " &raquo; ".$title;
	
	$raw = ob_get_contents();
	$raw = str_replace("<title>[[BOARD TITLE HERE]]</title>", "<title>".$boardTitle."</title>", $raw);
	ob_end_clean();

	if(!$overallTidy)
	{
	print $raw;
		return;
	}
		
	$tidyConfig = array
	(
		"show-body-only"=>0,
		"output-xhtml"=>1,
		"doctype"=>"strict",
		"logical-emphasis"=>1,
		"alt-text"=>"",
		"drop-proprietary-attributes"=>1,
		"wrap"=>0,
		"preserve-entities"=>1,
		"indent"=>1,
		"input-encoding"=>"utf8",
		"char-encoding"=>"utf8",
		"output-encoding"=>"utf8",
		"new-blocklevel-tags"=>"video",
	);

	$raw = OptimizeLayouts($raw);
	$clean = tidy_repair_string($raw, $tidyConfig);
		
	$textareaFixed = str_replace("\r", "", $clean);
	$textareaFixed = str_replace(" </text", "</text", $textareaFixed);
	$textareaFixed = str_replace("\n</text", "</text", $textareaFixed);
	//$textareaFixed = str_replace("\n</text", "</text", $textareaFixed);
	print $textareaFixed;
	exit();	
}

function GetRainbowColor()
{
	$stime = gettimeofday();
	$h = (($stime[usec] / 5) % 600);
	if($h < 100)
	{
		$r = 255;
		$g = 155 + $h;
		$b = 155;
	}
	else if($h < 200)
	{
		$r = 255 - $h + 100;
		$g = 255;
		$b = 155;
	}
	else if($h < 300)
	{
		$r = 155;
		$g = 255;
		$b = 155 + $h - 200;
	}
	else if($h < 400)
	{
		$r = 155;
		$g = 255 - $h + 300;
		$b = 255;
	}
	else if($h < 500)
	{
		$r = 155 + $h - 400;
		$g = 155;
		$b = 255;
	}
	else
	{
		$r = 255;
		$g = 155;
		$b = 255 - $h + 500;
	}
	return substr(dechex($r * 65536 + $g * 256 + $b), -6);
}

function UserLink($user, $field = "id")
{
	global $hacks;

	$fpow = $user['powerlevel'];
	$fsex = $user['sex'];
	$fname = $user['name'];

	if($hacks['alwayssamepower'])
		$fpow = $hacks['alwayssamepower'] - 1;
	if($hacks['alwayssamesex'])
		$fsex = $hacks['alwayssamesex'];

	$classing = " class=\"nc" . $fsex . (($fpow == -1) ? "x" : $fpow)."\"";

	if($hacks['themenames'] == 1)
	{
		global $lastJokeNameColor;
		$classing = " style=\"color: ";
		if($lastJokeNameColor % 2 == 1)
			$classing .= "#E16D6D; \"";
		else
			$classing .= "#44D04B; \"";
		if($fpow == -1)
			$classing = " class=\"nc0x\"";
		$lastJokeNameColor++;
	} else if($hacks['themenames'] == 2 && $fpow > -1)
	{
		$classing = " style =\"color: #".GetRainbowColor()."\"";
	} else if($hacks['themenames'] == 3)
	{
		if($fpow == 3)
		{
			$fname = "Administration";
			$classing = " class=\"nc23\"";
		} else if($fpow == -1)
		{
			$fname = "Idiot";
			$classing = " class=\"nc2x\"";
		} else
		{
			$fname = "Anonymous";
			$classing = " class=\"nc22\"";
		}
	}

	return "<a href=\"profile.php?id=".$user[$field]."\"><span".$classing.">".$fname."</span></a>";
	return "<a href=\"profile.php?id=".$user[$field]."\"".$classing.">".$fname."</a>";
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
	//print "\n<table class=\"breadcrumbs\">\n";
	//print "\t<tr>\n\t\t<td>\n";
	foreach($path as $text=>$link)
	{
		$link = str_replace("&","&amp;",$link);
		if($link)
			$crumbs .= "<a href=\"".$link."\">".$text."</a> &raquo; ";
		else
			$crumbs .= $text. " &raquo; ";
	}
	$crumbs = substr($crumbs, 0, strlen($crumbs) - 8);
	//print "\t\t</td>\n\t\t<td class=\"right\">\n\t\t\t".$links."\n\t\t</td>\n\t</tr>\n";
	//print "</table>\n";
	print "<div class=\"margin\">\n";
	print "<div style=\"float: right;\"><ul class=\"pipemenu smallFonts\">".$links."</ul></div>\n";
	print $crumbs;
	print "</div>\n\n";
}


function TimeUnits($sec)
{
	if($sec <    60) return "$sec sec.";
	if($sec <  3600) return floor($sec/60)." min.";
	if($sec < 86400) return floor($sec/3600)." hour".($sec >= 7200 ? "s" : "");
	return floor($sec/86400)." day".($sec >= 172800 ? "s" : "");
}

function DoPrivateMessageBar()
{
	global $loguserid, $loguser, $dateformat;

	if($loguserid)
	{
		$qUnread = "select count(*) from pmsgs where userto = ".$loguserid." and msgread=0";
		$unread= FetchResult($qUnread);

		if($unread)
		{
			$pmNotice = $loguser['usebanners'] ? "id=\"pmNotice\" " : "";
			$qLast = "select * from pmsgs where userto = ".$loguserid." and msgread=0 order by date desc limit 0,1";
			$rLast = Query($qLast);
			$last = Fetch($rLast);
			$qUser = "select * from users where id = ".$last['userfrom'];
			$rUser = Query($qUser);
			$user = Fetch($rUser);
			print "<div ".$pmNotice."class=\"outline margin cell1 smallFonts\">";
			//print "<img src=\"img/status/new.png\" alt=\"NEW\" /> ";
			print "You have ".Plural($unread, "new <a href=\"private.php\">private message")."</a>. ";
			print "<a href=\"showprivate.php?id=".$last['id']."\">Last message</a> from ".UserLink($user)." on ".gmdate($dateformat, $last['date']).".";
			print "</div>";
		}
	}
}

function DoSmileyBar($taname = "text")
{
	global $smilies;
	$expandAt = 26;
	LoadSmilies();
	print "<div class=\"outline PoRT margin\" style=\"width: 180px;\">";
	print "<div class=\"errort\"><strong>Smilies</strong></div>";
	print "<div class=\"errorc cell0\" id=\"smiliesContainer\">";
	if(count($smilies) > $expandAt)
		print "<button class=\"expander\" id=\"smiliesExpand\" onclick=\"expandSmilies();\">&#x25BC;</button>"; 
	print "<div class=\"smilies\" id=\"commonSet\">";
	for($i = 0; $i < count($smilies) - 1; $i++)
	{
		if($i == $expandAt)
			print "</div><div class=\"smilies\" id=\"expandedSet\">";
		$s = $smilies[$i];
		print "<img src=\"img/smilies/".$s['image']."\" alt=\"".$s['code']."\" title=\"".$s['code']."\" onclick=\"insertSmiley(' ".$s['code']." ');\" />";
	}
	print "</div>";
	print "</div>";
	print "</div>";
}

function DoPostHelp()
{
?>
	<div class="outline cell0 PoRT margin" style="width: 180px;">
		<div class="errort"><strong>Post help</strong></div>
		<div class="errorc left">
			<button class="expander" id="postHelpExpand" onclick="expandPostHelp();">&#x25BC;</button>
			<div id="commonHelp">
				<h4>Presentation</h4>
				[b]&hellip;[/b] &mdash; <strong>bold type</strong> <br />
				[i]&hellip;[/i] &mdash; <em>italic</em> <br />
				[u]&hellip;[/u] &mdash; <span class="underline">underlined</span> <br />
				[s]&hellip;[/s] &mdash; <del>Strikethrough</del><br />
			</div>
			<div id="expandedHelp">
				[code]&hellip;[/code] &mdash; <code>Code block</code> <br />
				[spoiler]&hellip;[/spoiler] &mdash; Spoiler block <br />
				[source]&hellip;[/source] &mdash; Colorcoded block, assuming C# <br />
				[source=&hellip;]&hellip;[/source] &mdash; Colorcoded block, specific language<sup title="bnf, c, cpp, csharp, html4strict, javascript, lolcode, lua, mysql, php, qbasic, vbnet, xml">[which?]</sup> <br />
				<br />
				<h4>Links</h4>
				[img]http://&hellip;[/img] &mdash; insert image <br />
				[url]http://&hellip;[/url] <br />
				[url=http://&hellip;]&hellip;[/url] <br />
				>>&hellip; &mdash; link to post by ID <br />
				[trope]&hellip;[/trope] &mdash; link to trope <br />
				[trope=&hellip;]&hellip;[/trope] <br/>
				[wiki]&hellip;[/wiki] &mdash; link to Wikipedia <br/>
				[wiki=&hellip;]&hellip;[/wiki] <br/>
				[user=##] &mdash; link to user's profile by ID <br />
				<br />
				<h4>Quotations</h4>
				[quote]&hellip;[/quote] &mdash; untitled quote<br />
				[quote=&hellip;]&hellip;[/quote] &mdash; "Posted by &hellip;"<br />
				[quote="&hellip;" id="&hellip;"]&hellip;[/quote] &mdash; "Post by &hellip;" with link by post ID<br />
				<br />
				<h4>Embeds</h4>
				[youtube]&hellip;[/youtube] &mdash; video ID only please <br />
				[video]&hellip;[/video] &mdash; requires URL to mp4 or ogv file <br />
				[swf ## ##]&hellip;[/swf] &mdash; insert SWF clip<br />
				[svg ## ##]&hellip;[/svg] &mdash; insert SVG image<br />
				<br />
			</div>
			Most plain HTML also allowed
		</div>
	</div>
<?php
}

function OnlineUsers($forum = 0)
{
	$forumClause = "";
	$browseLocation = " online";
       
	if ($forum)
	{
		$forumClause = " and lastforum=".$forum;
		$forumName = FetchResult("SELECT title FROM forums WHERE id=".$forum);
		$browseLocation = " browsing ".$forumName;
	}
       
	$rOnlineUsers = Query("select id,name,sex,powerlevel,lastactivity,lastposttime,minipic from users where (lastactivity > ".(time()-300)." or lastposttime > ".(time()-300).")".$forumClause." order by name");
	$onlineUsers = "";
	$onlineUserCt = 0;
	while($onlineUser = Fetch($rOnlineUsers))
	{
		$loggedIn = ($onlineUser['lastpost'] <= $onlineUser['lastview']);
		$userLink = UserLink($onlineUser);
		if($onlineUser['minipic'])
			$userLink = "<img src=\"".$onlineUser['minipic']."\" alt=\"\" class=\"minipic\" />".$userLink;
		if(!$loggedIn)
			$userLink = "(".$userLink.")";
		$onlineUsers.=($onlineUserCt ? ", " : "").$userLink;
		$onlineUserCt++;
	}
	$onlineUsers = $onlineUserCt." user".(($onlineUserCt > 1 || $onlineUserCt == 0) ? "s" : "").$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;

	$guests = FetchResult("select count(*) from guests where bot=0 and date > ".(time() - 300).$forumClause);
	$bots = FetchResult("select count(*) from guests where bot=1 and date > ".(time() - 300).$forumClause);

	if($guests)
		$onlineUsers .= " | ".Plural($guests,"guest");
	if($bots)
		$onlineUsers .= " | ".Plural($bots,"bot");
	       
	return $onlineUsers;
}

?>
