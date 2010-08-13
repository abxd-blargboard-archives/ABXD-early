<?php
// AcmlmBoard XD - Search page - by Mega-Mario
// Access: all (except for IP search)

include('lib/common.php');

$pl = $loguser['powerlevel'];
if ($pl == -1) $pl = 0;

$SearchString = deSlashMagic($_GET['q']);
$Mode = (int)$_GET['m'];
$FindMode = (int)$_GET['fm'];

$Forum = (int)$_GET['f'];
$ThreadCreator = deSlashMagic($_GET['tc']);
if ($Mode == 0) $PostCreator = deSlashMagic($_GET['pc']);
else $PostCreator = '';
if ($pl >= 1) $PostIP = deSlashMagic($_GET['ip']); // IP search is staff only
else $PostIP = '';

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
	
$HasSearchString = IsNonEmpty($SearchString);
if ($HasSearchString)
{
	// Restriction: search string may not exceed 256 characters.
	$SearchString = substr($SearchString, 0, 256);
	
	// Split the keywords into an array
	$_keywords = explode(' ', $SearchString);
	if (count($_keywords) == 0) $HasSearchString = 0;
}

$SearchAtAll = ($HasSearchString or ($Forum!=0 and (IsNonEmpty($ThreadCreator) or IsNonEmpty($PostCreator)) or $PostIP));

if ($SearchAtAll)
{
	$error = 0;
	$filterdesc = array(1=>'thread creator', 'post creator', 'IP address');
	if (ContainsTooManyWildcards($ThreadCreator)) $error = 1;
	if (ContainsTooManyWildcards($PostCreator)) $error = 2;
	if (ContainsTooManyWildcards($PostIP)) $error = 3;
	if ($error != 0)
	{
		Alert('The "'.$filterdesc[$error].'" filter value you entered contains too many wildcards.', 'Search flood protection');
		$SearchAtAll = 0;
	}
}

if ($SearchAtAll)
{
	if ($HasSearchString)
	{
		$Keywords = array();
		$BannedKeywords = array();
		
		for ($i = 0; $i < count($_keywords);)
		{
			if ($_keywords[$i][0] == '"')
			{
				$temp = substr($_keywords[$i], 1);
				for ($j = $i+1; $j < count($_keywords); $j++)
				{
					if ($_keywords[$j][strlen($_keywords[$j])-1] == '"')
					{
						$temp .= ' '.substr($_keywords[$j], 0, strlen($_keywords[$j])-1);
						$i = $j;
						break;
					}
					else
						$temp .= ' '.$_keywords[$j];
					
					$i++;
				}
				$i++;
				$Keywords[] = $temp;
			}
			elseif ($_keywords[$i][0] == '-')
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
		
		// Restrictions on keyword length and number of wildcards
		$checkarray = array_merge($Keywords, $BannedKeywords);
		$error = 0;
		$errdesc = array(1=>'is too long', 'contains too many wildcards');
		foreach ($checkarray as $check)
		{
			if (strlen($check) > 32) $error = 1;
			else if (ContainsTooManyWildcards($check)) $error = 2;
			
			if ($error) break;
		}
		
		if ($error)
		{
			Alert('One of the keywords you entered '.$errdesc[$error].'.', 'Search flood protection');
			$SearchAtAll = 0;
		}
	}
}

if ($SearchAtAll)
{
				
	if ($Mode == 0)
	{
		$query = 'SELECT threads.id AS thread, threads.user, threads.title AS threadname,
					posts.id, posts.date, posts.num, posts.options, posts.mood, posts.ip,
					posts_text.text, posts_text.revision, 
					users.id AS uid, users.name, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, 
					users.postheader, users.signature, users.globalblock, users.lastposttime, users.lastactivity, users.regdate
				FROM threads,posts,posts_text,users
				WHERE ';
		$wheretosearch = 'posts_text.text';
	}
	else
	{
		$query = 'SELECT threads.*,
					forums.id AS fid, forums.title AS ftitle,
					su.id AS sid, su.name AS sname, su.powerlevel AS spower, su.sex AS ssex,
					lu.id AS lid, lu.name AS lname, lu.powerlevel AS lpower, lu.sex AS lsex
				FROM threads,forums,users su,users lu
				WHERE ';
		$wheretosearch = 'threads.title';
	}
		
	if ($HasSearchString)
	{
		if (count($Keywords) > 0)
		{
			$query .= '(';
			foreach ($Keywords as $kw)
			{
				$query .= $wheretosearch.' LIKE \'%'.mysql_real_escape_string($kw).'%\''.($FindMode==0 ? ' OR ' : ' AND ');
			}
			$query .= ($FindMode==0 ? '0=1' : '1=1').') AND ';
		}
		if (count($BannedKeywords) > 0)
		{
			$query .= '(';
			foreach ($BannedKeywords as $bkw)
			{
				$query .= $wheretosearch.' NOT LIKE \'%'.mysql_real_escape_string($bkw).'%\' AND ';
			}
			$query .= '1=1) AND ';
		}
	}
	
	if ($Forum > 0)
	{
		$query .= 'threads.forum = '.$Forum.' AND ';
	}
	if ($Mode == 0)
	{
		if ($ThreadCreator)
		{
			$query .= '(SELECT name FROM users WHERE id=threads.user) LIKE \'%'.mysql_real_escape_string($ThreadCreator).'%\' AND ';
		}
		if ($PostCreator)
		{
			$query .= 'users.name LIKE \'%'.mysql_real_escape_string($PostCreator).'%\' AND ';
		}
		if ($PostIP)
		{
			$query .= 'posts.ip LIKE \'%'.mysql_real_escape_string($PostIP).'%\' AND ';
		}
		
		$query .= 'posts_text.pid = posts.id AND posts_text.revision = (SELECT MAX(revision) FROM posts_text WHERE pid=posts.id) AND posts.thread = threads.id AND posts.user = users.id AND ';
		$query .= '(SELECT minpower FROM categories WHERE id=(SELECT catid FROM forums WHERE id=threads.forum)) <= '.$pl.' AND (SELECT minpower FROM forums WHERE id=threads.forum) <= '.$pl;
		$query .= ' ORDER BY date DESC';
	}
	else
	{
		if ($ThreadCreator)
		{
			$query .= 'su.name LIKE \'%'.mysql_real_escape_string($ThreadCreator).'%\' AND ';
		}
		
		$query .= 'threads.user = su.id AND threads.lastposter = lu.id AND threads.forum = forums.id AND ';
		$query .= '(SELECT minpower FROM categories WHERE id=forums.catid) <= '.$pl.' AND forums.minpower <= '.$pl;
		$query .= ' ORDER BY sticky DESC, lastpostdate DESC';
	}
		
	$rSearchResult = Query($query);
	
	Query('INSERT INTO lastsearches (ip,userid,time) VALUES (\''.$_SERVER['REMOTE_ADDR'].'\', '.$loguserid.', '.time().')');
}

?>
<div class="outline PoRT margin width25">
	<div class="errort">
		<strong>Search help</strong>
	</div>
	<div class="errorc left">
		"%" acts as a generic wildcard.<br />
		"_" acts as a one-character wildcard.<br />
		To search for a group of words, surround them with quotes.<br />
		To exclude words from the results, preceed them with a dash.<br />
	</div>
</div>

<div class="outline PoRT margin width25">
	<div class="errort">
		<strong>Search rules</strong>
	</div>
	<div class="errorc left">
		Each keyword may not exceed 32 characters.<br />
		Each keyword and filter value may not contain more than 30% of wildcards.<br />
		You may not perform more than 5 searches per minute.<br />
	</div>
</div>

<form action="search.php" method="get">
<table class="outline margin width50">
	<tr class="header0">
		<th colspan="2">Search</th>
	</tr>
	<tr class="cell0">
		<td>Search for:</td>
		<td><input type="text" name="q" style="width: 98%;" value="<?php print htmlval($SearchString); ?>" maxlength="256" /></td>
	</tr>
	<tr class="cell1">
		<td>In:</td>
		<td>
			<input type="radio" id="m1" name="m" value="0"<?php if ($Mode==0) print ' checked="checked"';?> onclick="var e = document.getElementsByClassName('mode0only'); for (i=0; i<e.length; i++) e[i].style.display = '';" /> <label for="m1">Post content</label>
			<input type="radio" id="m2" name="m" value="1"<?php if ($Mode==1) print ' checked="checked"';?> onclick="var e = document.getElementsByClassName('mode0only'); for (i=0; i<e.length; i++) e[i].style.display = 'none';" /> <label for="m2">Thread title</label>
		</td>
	</tr>
	<tr class="cell0">
		<td>Find:</td>
		<td>
			<input type="radio" id="fm1" name="fm" value="0"<?php if ($FindMode==0) print ' checked="checked"';?> /> <label for="fm1">Atleast one</label>
			<input type="radio" id="fm2" name="fm" value="1"<?php if ($FindMode==1) print ' checked="checked"';?> /> <label for="fm2">All of the keywords</label>
		</td>
	</tr>
	<tr class="header0">
		<th colspan="2">Filters</th>
	</tr>
	<tr class="cell1">
		<td>Forum:</td>
		<td>
			<select name="f">
				<option value="0"<?php if ($Forum == 0) print ' selected="selected"'; ?>>Any</option>
				<?php
					$rCategories = Query('SELECT id,name FROM categories WHERE minpower<='.$pl);
					while ($_cat = Fetch($rCategories))
					{
						write('<optgroup label="{0}">', $_cat['name']);
						$rForums = Query('SELECT id,title FROM forums WHERE catid='.$_cat['id'].' AND minpower<='.$pl.' ORDER BY forder');
						while ($_forum = Fetch($rForums))
						{
							if ($Forum == $_forum['id']) $sel = ' selected="selected"';
							else $sel = '';
							
							write('<option value="{0}"{1}>{2}</option>', $_forum['id'], $sel, $_forum['title']);
						}
						write('</optgroup>');
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
	<?php if ($pl >= 1) { ?>
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
	write('<recaptcha type="text/javascript">var e = document.getElementsByClassName("mode0only"); for (i=0; i<e.length; i++) e[i].style.display = "none";</script>');

if ($SearchAtAll)
{
	$numres = NumRows($rSearchResult);
		
	write('<table class="outline margin">');
	write('<tr class="header0">');
	write('<th>Search results &mdash; {0} found</th>', Plural($numres, ($Mode==0 ? 'post' : 'thread')));
	write('</tr>');
	write('</table>');
	
	if ($numres > 0)
	{
		$pagelinks = 'Pages: <a class="pagenum pagenum0" onclick="ChangePage(0); return false;">1</a>';
		if ($numres > $rpp)
		{
			for ($i = $rpp; $i < $numres; $i += $rpp)
			{
				$pagelinks .= ' <a class="pagenum pagenum'.$i.'" href="#" onclick="ChangePage('.$i.'); return false;">'.(intval($i / $rpp) + 1).'</a>';
			}
		}
		
		write($pagelinks);
		
		if ($Mode == 0)
		{
			write('<div class="respage respage0">');
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
						if (!IsNonEmpty($kw)) continue;
						$kw = preg_quote($kw,'@');
						$kw = str_replace('%', '\w+', $kw);
						$kw = str_replace('_', '\w', $kw);
						$post['text'] = preg_replace('@('.$kw.')@si', '<span style="border: 1px solid #002 !important; background: #cff !important; color: #002 !important;">$1</span>', $post['text']);
					}
				}
				
				MakePost($post, array('id'=>$post['thread'], 'title'=>$post['threadname']), -2);
				
				$i++;
				if (($i % $ppp) == 0)
				{
					write('</div>');
					write('<div class="respage respage{0}">', $i);
				}
			}
			
			write('</div>');
		}
		else
		{
			write('<table class="outline margin">');
			write('<tr class="header1">');
			write('<th style="width: 20px;">&nbsp;</th>');
			write('<th>Posted into</th>');
			write('<th style="width: 16px;">&nbsp;</th>');
			write('<th style="width: 60%;">Title</th>');
			write('<th>Started by</th>');
			write('<th>Replies</th>');
			write('<th>Views</th>');
			write('<th>Last post</th>');
			write('</tr>');
			
			$postread = readpostread($loguserid);
			$j = 0; $k = 0;
			
			while($thread = Fetch($rSearchResult))
			{
				$thread['ftitle'] = htmlspecialchars($thread['ftitle']);

				$last = array('id'=>$thread['lid'], 'name'=>$thread['lname'], 'powerlevel'=>$thread['lpower'], 'sex'=>$thread['lsex']);
				$starter = array('id'=>$thread['sid'], 'name'=>$thread['sname'], 'powerlevel'=>$thread['spower'], 'sex'=>$thread['ssex']);

				$NewIcon = '';
				if($thread['closed'])
					$NewIcon = 'off';
				if($thread['replies'] >= $misc['hotcount'])
					$NewIcon .= 'hot';
				if((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
					($loguserid && $thread['lastpostdate'] > $postread[$thread['id']]))
					$NewIcon .= 'new';
				if($NewIcon)
					$NewIcon = '<img src="img/status/'.$NewIcon.'.png" alt=""/>';

				if($thread['icon'])
					$ThreadIcon = '<img src="'.htmlspecialchars($thread['icon']).'" alt=""/>';
				else
					$ThreadIcon = '';

				$cellClass = ($cellClass+1) % 2;

				if($thread['sticky'])
					$cellClass = 2;

				if($thread['sticky'] == 0 && $haveStickies == 1)
				{
					$haveStickies = 2;
					write('<tr class="header0"><th colspan="8" style="height: 8px;"></th></tr>');
				}
				if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

				$poll = ($thread['poll'] ? '<img src="img/poll.png" alt="Poll" /> ' : '');

				$pl = '';
				$total = $thread['replies'];
				for($i = $ppp; $i <= $total; $i+=$ppp)
					$pl .= ' <a href="thread.php?id='.$thread['id'].'&amp;from='.$i.'">'.(($i/$ppp)+1).'</a>';
				if($pl)
					$pl = ' <span class="smallFonts">[<a href="thread.php?id='.$thread['id'].'">1</a>'.$pl.']</span>';

				$lastLink = '';
				if($thread['lastpostid'])
					$lastLink = ' <a href="thread.php?pid='.$thread['lastpostid'].'#'.$thread['lastpostid'].'">&raquo;</a>';
					
				// Highlighting of search terms
				// Note, we could have used str_ireplace(), but the advantage of this method
				// is that it leaves the keywords' case unchanged
				if ($HasSearchString)
				{
					foreach ($Keywords as $kw)
					{
						if (!IsNonEmpty($kw)) continue;
						$kw = preg_quote($kw,'@');
						$kw = str_replace('%', '\w+', $kw);
						$kw = str_replace('_', '\w', $kw);
						$thread['title'] = preg_replace('@('.$kw.')@si', '<span style="border: 1px solid #002 !important; background: #cff !important; color: #002 !important;">$1</span>', $thread['title']);
					}
				}

				write('<tr class="cell{0} respage respage{1}">', $cellClass, $k);
				write('<td class="cell2" style="width: 17px;">{0}</td>', $NewIcon);
				write('<td><a href="forum.php?id={0}">{1}</a></td>', $thread['fid'], $thread['ftitle']);
				write('<td style="width: 17px;">{0}</td>', $ThreadIcon);
				write('<td>{0}<a href="thread.php?id={1}">{2}</a>{3}</td>', $poll, $thread['id'], htmlspecialchars($thread['title']), $pl);
				write('<td>{0}</td>', UserLink($starter));
				write('<td class="center">{0}</td>', $thread['replies']);
				write('<td class="center">{0}</td>', $thread['views']);
				write('<td class="smallFonts">{0}<br />by {1}{2}</td>', gmdate($dateformat,$thread['lastpostdate']), UserLink($last), $lastLink);
				write('</tr>');
				
				$j++;
				if (($j % $tpp) == 0) $k += $tpp;
			}
			
			write('</table>');
		}
		
		write($pagelinks);
		write('<recaptcha type="text/javascript">ChangePage({0});</script>', $from); // haxx
	}
}

function readpostread($userid)
{
  $postreads = Query('select * from threadsread where id='.$userid);
  while($read1 = Fetch($postreads))
  	$postread[$read1['thread']]=$read1['date'];
  return $postread;
}

function IsNonEmpty($str)
{
	return preg_match('@[^%_\(\)\s"-]@si', $str);
}

function ContainsTooManyWildcards($str)
{
	if (strlen($str) == 0) return false; // avoid division by zero
	$numwc = preg_match_all('@[%_\(\)]@si', $str, $dummy);
	$wcpercent = ($numwc * 100) / strlen($str);
	return ($wcpercent >= 30);
}

?>