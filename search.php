<?php
// AcmlmBoard XD - Search page - by Mega-Mario
// Access: all (except for IP search)

include("lib/common.php");

$pl = $loguser['powerlevel'];
if ($pl == -1) $pl = 0;

$SearchString = deSlashMagic($_GET['q']);
$Mode = (int)$_GET['m'];
$FindMode = (int)$_GET['fm'];

$Forum = (int)$_GET['f'];
$ThreadCreator = deSlashMagic($_GET['tc']);
$PostCreator = deSlashMagic($_GET['pc']);
if ($pl >= 3) $PostIP = deSlashMagic($_GET['ip']); // IP search is admin only
else $PostIP = "";

$ppp = $loguser['postsperpage'];
if (!$ppp) $ppp = 20;
$tpp = $loguser['threadsperpage'];
if (!$tpp) $tpp = 20;

if ($Mode == 0) $rpp = $ppp;
else $rpp = $tpp;

if (isset($_GET['page']))
	$from = (int)$_GET['page'] * $rpp;
else
	$from = 0;
	
$HasSearchString = preg_match("@[^%\s\"-]@si", $SearchString);

$SearchAtAll = ($HasSearchString or $ThreadCreator or $PostCreator or $PostIP);

if ($HasSearchString)
{
	// Split the keywords into an array
	$_keywords = explode(" ", $SearchString);
	if (count($_keywords) == 0) $HasSearchString = 0;
}

if ($SearchAtAll)
{
	if ($HasSearchString)
	{
		$Keywords = array();
		$BannedKeywords = array();
		
		for ($i = 0; $i < count($_keywords);)
		{
			if ($_keywords[$i][0] == "\"")
			{
				$temp = substr($_keywords[$i], 1);
				for ($j = $i+1; $j < count($_keywords); $j++)
				{
					if ($_keywords[$j][strlen($_keywords[$j])-1] == "\"")
					{
						$temp .= " ".substr($_keywords[$j], 0, strlen($_keywords[$j])-1);
						$i = $j;
						break;
					}
					else
						$temp .= " ".$_keywords[$j];
					
					$i++;
				}
				$i++;
				$Keywords[] = $temp;
			}
			elseif ($_keywords[$i][0] == "-")
			{
				$BannedKeywords[] = substr($_keywords[$i], 1);
				$i++;
			}
			else
			{
				$Keywords[] = $_keywords[$i];
				$i++;
			}
		}
	}
				
	if ($Mode == 0)
	{
		$query = "SELECT threads.id AS tid, threads.user, threads.title AS ttitle,
					posts.id, posts.date, posts.num, posts.options, posts.mood, posts.ip,
					posts_text.text, posts_text.revision, 
					users.id AS uid, users.name, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, 
					users.postheader, users.signature, users.globalblock, users.lastposttime, users.lastactivity, users.regdate
				FROM threads,posts,posts_text,users
				WHERE ";
		$wheretosearch = "posts_text.text";
	}
	else
	{
		$query = "SELECT threads.*,
					forums.id AS fid, forums.title AS ftitle,
					su.id AS sid, su.name AS sname, su.powerlevel AS spower, su.sex AS ssex,
					lu.id AS lid, lu.name AS lname, lu.powerlevel AS lpower, lu.sex AS lsex
				FROM threads,forums,users su,users lu
				WHERE ";
		$wheretosearch = "threads.title";
	}
		
	if ($HasSearchString)
	{
		if (count($Keywords) > 0)
		{
			$query .= "(";
			foreach ($Keywords as $kw)
			{
				$query .= $wheretosearch." LIKE '%".mysql_real_escape_string($kw)."%'".($FindMode==0 ? " OR " : " AND ");
			}
			$query .= ($FindMode==0 ? "0=1" : "1=1").") AND ";
		}
		if (count($BannedKeywords) > 0)
		{
			$query .= "(";
			foreach ($BannedKeywords as $bkw)
			{
				$query .= $wheretosearch." NOT LIKE '%".mysql_real_escape_string($bkw)."%' AND ";
			}
			$query .= "1=1) AND ";
		}
	}
	
	if ($Forum > 0)
	{
		$query .= "threads.forum = ".$Forum." AND ";
	}
	if ($Mode == 0)
	{
		if ($ThreadCreator)
		{
			$query .= "(SELECT name FROM users WHERE id=threads.user) LIKE '%".mysql_real_escape_string($ThreadCreator)."%' AND ";
		}
		if ($PostCreator)
		{
			$query .= "users.name LIKE '%".mysql_real_escape_string($PostCreator)."%' AND ";
		}
		if ($PostIP)
		{
			$query .= "posts.ip LIKE '%".mysql_real_escape_string($PostIP)."%' AND ";
		}
		
		$query .= "posts_text.pid = posts.id AND posts_text.revision = (SELECT MAX(revision) FROM posts_text WHERE pid=posts.id) AND posts.thread = threads.id AND posts.user = users.id AND ";
		$query .= "(SELECT minpower FROM categories WHERE id=(SELECT catid FROM forums WHERE id=threads.forum)) <= ".$pl." AND (SELECT minpower FROM forums WHERE id=threads.forum) <= ".$pl;
	}
	else
	{
		if ($ThreadCreator)
		{
			$query .= "(SELECT name FROM users WHERE id=threads.user) LIKE '%".mysql_real_escape_string($ThreadCreator)."%' AND ";
		}
		
		$query .= "threads.user = su.id AND threads.lastposter = lu.id AND threads.forum = forums.id AND ";
		$query .= "(SELECT minpower FROM categories WHERE id=forums.catid) <= ".$pl." AND forums.minpower <= ".$pl;
	}
	
	if ($Mode == 0) $query .= " ORDER BY date DESC";
	else $query .= " ORDER BY sticky DESC, lastpostdate DESC";
		
	$rSearchResult = Query($query);
}

?>
<div class="outline PoRT margin width25">
	<div class="errort">
		<strong>Search help</strong>
	</div>
	<div class="errorc left">
		'%' acts as a generic wildcard.<br>
		To search for a group of words, surround them with quotes.<br>
		To exclude words from the results, preceed them with a dash.<br>
	</div>
</div>

<form action="search.php" method="get">
<table class="outline margin width50">
	<tr class="header0">
		<th colspan="2">Search</th>
	</tr>
	<tr class="cell0">
		<td>Search for:</td>
		<td><input type="text" name="q" style="width: 98%;" value="<?php print htmlval($SearchString); ?>" /></td>
	</tr>
	<tr class="cell1">
		<td>In:</td>
		<td>
			<input type="radio" id="m1" name="m" value="0"<?php if ($Mode==0) print " checked=\"checked\"";?> onclick="var e = document.getElementsByClassName('mode0only'); for (i=0; i<e.length; i++) e[i].style.display = '';" /> <label for="m1">Post content</label>
			<input type="radio" id="m2" name="m" value="1"<?php if ($Mode==1) print " checked=\"checked\"";?> onclick="var e = document.getElementsByClassName('mode0only'); for (i=0; i<e.length; i++) e[i].style.display = 'none';" /> <label for="m2">Thread title</label>
		</td>
	</tr>
	<tr class="cell0">
		<td>Find:</td>
		<td>
			<input type="radio" id="fm1" name="fm" value="0"<?php if ($FindMode==0) print " checked=\"checked\"";?> /> <label for="fm1">Atleast one</label>
			<input type="radio" id="fm2" name="fm" value="1"<?php if ($FindMode==1) print " checked=\"checked\"";?> /> <label for="fm2">All of the keywords</label>
		</td>
	</tr>
	<tr class="header0">
		<th colspan="2">Filters</th>
	</tr>
	<tr class="cell1">
		<td>Forum:</td>
		<td>
			<select name="f">
				<option value="0"<?php if ($Forum == 0) print " selected=\"selected\""; ?>>Any</option>
				<?php
					$rCategories = Query("SELECT id,name FROM categories WHERE minpower <= ".$pl);
					while ($_cat = Fetch($rCategories))
					{
						print "<optgroup label=\"".$_cat['name']."\">";
						$rForums = Query("SELECT id,title FROM forums WHERE catid=".$_cat['id']." AND minpower <= ".$pl." ORDER BY forder");
						while ($_forum = Fetch($rForums))
						{
							if ($Forum == $_forum['id']) $sel = " selected=\"selected\"";
							else $sel = "";
							
							print "<option value=\"".$_forum['id']."\"".$sel.">".$_forum['title']."</option>";
						}
						print "</optgroup>";
					}
				?>
			</select>
		</td>
	</tr>
	<tr class="cell0">
		<td>Thread creator:</td>
		<td><input type="text" name="tc" value="<?php print htmlval($ThreadCreator); ?>" /></td>
	</tr>
	<tr class="cell1 mode0only">
		<td>Post creator:</td>
		<td><input type="text" name="pc" value="<?php print htmlval($PostCreator); ?>" /></td>
	</tr>
	<?php if ($pl >= 3) { ?>
	<tr class="cell0 mode0only">
		<td>IP address:</td>
		<td><input type="text" name="ip" value="<?php print htmlval($PostIP); ?>" /></td>
	</tr>
	<?php } ?>
	<tr class="header0">
		<th colspan="2">&nbsp;</th>
	</tr>
	<tr class="cell0">
		<td>&nbsp;</td>
		<td><input type="submit" value="Search" /></td>
	</tr>
</table>
</form>
<?php

if ($Mode == 1)
	print "<recaptcha type=\"text/javascript\">var e = document.getElementsByClassName('mode0only'); for (i=0; i<e.length; i++) e[i].style.display = 'none';</script>";

if ($SearchAtAll)
{
	$numres = NumRows($rSearchResult);
		
	print "<table class=\"outline margin\">";
	print "<tr class=\"header0\">";
	print "<th>Search results &mdash; ".Plural($numres, ($Mode==0 ? "post" : "thread"))." found</th>";
	print "</tr>";
	print "</table>";
	
	if ($numres > 0)
	{
		$pagelinks = "Pages: <a class=\"pagenum pagenum0\" onclick=\"ChangePage(0); return false;\">1</a>";
		if ($numres > $rpp)
		{
			for ($i = $rpp; $i < $numres; $i += $rpp)
			{
				$pagelinks .= " <a class=\"pagenum pagenum".$i."\" href=\"#\" onclick=\"ChangePage(".$i."); return false;\">".(intval($i / $rpp) + 1)."</a>";
			}
		}
		
		print $pagelinks;
		
		if ($Mode == 0)
		{
			print "<div class=\"respage respage0\">";
			$i = 0;
			
			while ($post = Fetch($rSearchResult))
			{
				// Highlighting of search terms
				// Note, we could have used str_ireplace(), but the advantage of this method
				// is that it leaves the keywords' case unchanged
				if ($HasSearchString)
				{
					foreach ($Keywords as $kw)
					{
						if (!preg_match("@[^%]@si", $kw)) continue;
						$kw = str_replace("%", ".*?", $kw);
						$post['text'] = preg_replace("@(".$kw.")@si", "<span style=\"border: 1px solid #002 !important; background: #cff !important; color: #002 !important;\">$1</span>", $post['text']);
					}
				}
				
				MakePost($post, array('id'=>$post['tid'], 'title'=>$post['ttitle']), -1);
				
				$i++;
				if (($i % $ppp) == 0)
				{
					print "</div>";
					print "<div class=\"respage respage".$i."\">";
				}
			}
			
			print "</div>";
		}
		else
		{
			print "<table class=\"outline margin\">";
			print "<tr class=\"header1\">";
			print "<th style=\"width: 20px;\">&nbsp;</th>";
			print "<th>Posted into</th>";
			print "<th style=\"width: 16px;\">&nbsp;</th>";
			print "<th style=\"width: 60%;\">Title</th>";
			print "<th>Started by</th>";
			print "<th>Replies</th>";
			print "<th>Views</th>";
			print "<th>Last post</th>";
			print "</tr>";
			
			$postread = readpostread($loguserid);
			$j = 0; $k = 0;
			
			while($thread = Fetch($rSearchResult))
			{
				$last = array("id"=>$thread['lid'], "name"=>$thread['lname'], "powerlevel"=>$thread['lpower'], "sex"=>$thread['lsex']);
				$starter = array("id"=>$thread['sid'], "name"=>$thread['sname'], "powerlevel"=>$thread['spower'], "sex"=>$thread['ssex']);

				$NewIcon = "";
				if($thread['closed'])
					$NewIcon = "off";
				if($thread['replies'] >= $misc['hotcount'])
					$NewIcon .= "hot";
				if((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
					($loguserid && $thread['lastpostdate'] > $postread[$thread['id']]))
					$NewIcon .= "new";
				if($NewIcon)
					$NewIcon = "<img src=\"img/status/".$NewIcon.".png\" alt=\"\"/>";

				if($thread['icon'])
					$ThreadIcon = "<img src=\"".$thread['icon']."\" alt=\"\"/>";
				else
					$ThreadIcon = "";

				$cellClass = ($cellClass+1) % 2;

				if($thread['sticky'])
					$cellClass = 2;

				if($thread['sticky'] == 0 && $haveStickies == 1)
				{
					$haveStickies = 2;
					print "<tr class=\"header0\"><th colspan=\"8\" style=\"height: 8px;\"></th></tr>";
				}
				if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

				$poll = ($thread['poll'] ? "<img src=\"img/poll.png\" alt=\"Poll\" /> " : "");

				$pl = "";
				$total = $thread['replies'];
				for($i = $ppp; $i <= $total; $i+=$ppp)
					$pl .= " <a href=\"thread.php?id=".$thread['id']."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
				if($pl)
					$pl = " <span class=\"smallFonts\">[<a href=\"thread.php?id=".$thread['id']."\">1</a>".$pl."]</span>";

				$lastLink = "";
				if($thread['lastpostid'])
					$lastLink = " <a href=\"thread.php?pid=".$thread['lastpostid']."#".$thread['lastpostid']."\">&raquo;</a>";
					
				// Highlighting of search terms
				// Note, we could have used str_ireplace(), but the advantage of this method
				// is that it leaves the keywords' case unchanged
				if ($HasSearchString)
				{
					foreach ($Keywords as $kw)
					{
						if (!preg_match("@[^%]@si", $kw)) continue;
						$kw = str_replace("%", ".*?", $kw);
						$thread['title'] = preg_replace("@(".$kw.")@si", "<span style=\"border: 1px solid #002 !important; background: #cff !important; color: #002 !important;\">$1</span>", $thread['title']);
					}
				}

				print "<tr class=\"cell".$cellClass." respage respage".$k."\">";
				print "<td class=\"cell2\" style=\"width: 17px;\">".$NewIcon."</td>";
				print "<td><a href=\"forum.php?id=".$thread['fid']."\">".$thread['ftitle']."</a></td>";
				print "<td style=\"width: 17px;\">".$ThreadIcon."</td>";
				print "<td>".$poll."<a href=\"thread.php?id=".$thread['id']."\">".$thread['title']."</a>".$pl."</td>";
				print "<td>".UserLink($starter)."</td>";
				print "<td class=\"center\">".$thread['replies']."</td>";
				print "<td class=\"center\">".$thread['views']."</td>";
				print "<td class=\"smallFonts\">".gmdate($dateformat,$thread['lastpostdate'])."<br />by ".UserLink($last).$lastLink."</td>";
				print "</tr>";
				
				$j++;
				if (($j % $tpp) == 0) $k += $tpp;
			}
			
			print "</table>";
		}
		
		print $pagelinks;
		print "<recaptcha type=\"text/javascript\">ChangePage(".$from.");</script>";
	}
}

doFooter();

function readpostread($userid)
{
  $postreads = Query("select * from threadsread where id=".$userid);
  while($read1 = Fetch($postreads))
  	$postread[$read1['thread']]=$read1['date'];
  return $postread;
}

?>