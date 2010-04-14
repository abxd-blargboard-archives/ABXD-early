<?php
//  AcmlmBoard XD - Thread display page
//  Access: all

include("lib/common.php");

if(isset($_GET['id']))
	$tid = (int)$_GET['id'];
elseif(isset($_GET['pid']))
{
	$pid = (int)$_GET['pid'];
	$qPost = "select * from posts where id=".$pid;
	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		Kill("Unknown post ID.");
	$tid = $post['thread'];
} else
	Kill("Thread ID unspecified.");

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Unknown thread ID.");

$fid = $thread['forum'];

$pl = $loguser['powerlevel'];
if($pl == -1) $pl = 0;

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
{
	$forum = Fetch($rFora);
	if($forum['minpower'] > $pl)
		Kill("You are not allowed to browse this forum.");
} else
	Kill("Unknown forum ID.");

$qViewCounter = "update threads set views=".($thread['views']+1)." where id=".$tid." limit 1";
$rViewCounter = Query($qViewCounter);

if(isset($_GET['vote']))
{
	if(!$loguserid)
		Kill("You can't vote without logging in.");
	if($thread['closed'])
		Kill("Poll's closed!");
	if($thread['poll'])
	{
		$qCheck = "select * from pollvotes where poll=".$thread['poll']." and user=".$loguserid;
		$rCheck = Query($qCheck);
		if(NumRows($rCheck))
		{
			$qCheck = "delete from pollvotes where  poll=".$thread['poll']." and user=".$loguserid;
			$rCheck = Query($qCheck);
		}
		$qPoll = "insert into pollvotes (poll, choice, user) values (".$thread['poll'].", ".$_GET['vote'].", ".$loguserid.")";
		$rPoll = Query($qPoll);
	} else
		Kill("This is not a poll.");
}

if($loguser['powerlevel'] == -1)
	$links .= "You're banned. | ";
elseif(!$thread['closed'] || $loguser['powerlevel'] > 2)
	$links .= "<a href=\"newreply.php?id=".$tid."\">Post Reply</a> | ";
else
	$links .= "Thread closed. | ";
if(CanMod($loguserid,$forum['id']))
{
	$links .= "<a href=\"editthread.php?id=".$tid."\">Edit</a> | ";
	if($thread['closed'])
		$links .= "<a href=\"editthread.php?id=".$tid."&amp;action=open\">Open</a> | ";
	else
		$links .= "<a href=\"editthread.php?id=".$tid."&amp;action=close\">Close</a> | ";
	if($thread['sticky'])
		$links .= "<a href=\"editthread.php?id=".$tid."&amp;action=unstick\">Unstick</a> | ";
	else
		$links .= "<a href=\"editthread.php?id=".$tid."&amp;action=stick\">Stick</a> | ";
	$links .= "<a href=\"editthread.php?id=".$tid."&amp;action=delete\" onclick=\"if(!confirm('Are you sure you want to just up and delete this whole thread?') || !confirm('Seriously?')) return false;\">Delete</a> | ";
	if(strpos($forum['description'],"[trash]") === FALSE)
		$links .= "<a href=\"editthread.php?id=".$tid."&amp;action=trash\">Trash</a> | ";
}

$links = substr($links, 0, strlen($links) - 2);

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>""), $links);

DoPrivateMessageBar();

if($thread['poll'])
{
	$qPoll = "select * from poll where id=".$thread['poll'];
	$rPoll = Query($qPoll);
	if(NumRows($rPoll))
	{
		$poll = Fetch($rPoll);
		print "<table class=\"outline margin\">";
		print "<tr class=\"header0\">";
		print "<th colspan=\"2\">Poll</th>";
		print "</tr>";
		print "<tr class=\"cell0\">";
		print "<td colspan=\"2\">".$poll['question']."</td>";
		print "</tr>";

		$qCheck = "select * from pollvotes where poll=".$thread['poll']." and user=".$loguserid;
		$rCheck = Query($qCheck);
		if(NumRows($rCheck))
		{
			$check = Fetch($rCheck);
			$pc[$check['choice']] = "<img src=\"img/pollchoice.png\" alt=\"*\" /> ";
		}

		$qVotes = "select count(*) from pollvotes where poll=".$thread['poll'];
		$totalVotes = FetchResult($qVotes);

		$qOptions = "select * from poll_choices where poll=".$thread['poll'];
		$rOptions = Query($qOptions);
		$pops = 0;
		while($option = Fetch($rOptions))
		{
			$qVotes = "select * from pollvotes where poll=".$thread['poll']." and choice=".$pops;
			$rVotes = Query($qVotes);
			$votes = NumRows($rVotes);

			$cellClass = ($cellClass+1) % 2;
			print "<tr class=\"cell".$cellClass."\">";
			if($loguserid && !$thread['closed'])
				print "<td>".$pc[$pops]."<a href=\"thread.php?id=".$thread['id']."&amp;vote=".$pops."\">".$option['choice']."</a></td>";
			else
				print "<td>".$pc[$pops].$option['choice']."</td>";
			print "<td><img src=\"img/bargraph.php?z=".$votes."&amp;n=".$totalVotes."&amp;c=".str_replace("#","",$option['color'])."\" title=\"".$votes." of ".$totalVotes."\" alt=\"\" /></td>";
			print "</tr>";
			$pops++;
		}

		print "</table>";
		print "</div>";
	} else
	{
		//Silently discard the stuff...
	}
}

$qRead = "delete from threadsread where id=".$loguserid." and thread=".$tid;
$rRead = Query($qRead);
$qRead = "insert into threadsread (id,thread,date) values (".$loguserid.", ".$tid.", ".time().")";
$rRead = Query($qRead);

$total = $thread['replies'] + 1; //+1 for the OP
$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;
if(isset($_GET['from']))
	$from = $_GET['from'];
else
	if(isset($pid))
		$from = (floor(FetchResult("select count(*) from posts where thread=".$tid." and id < ".$pid) / $ppp)) * $ppp;
	else
		$from = 0;

$qPosts = "select ";
$qPosts .=
	"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.globalblock, users.lastposttime, users.lastactivity, users.regdate";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where thread=".$tid." order by date asc limit ".$from.", ".$ppp;

$rPosts = Query($qPosts);
$numonpage = NumRows($rPosts);

for($i = $ppp; $i < $total; $i+=$ppp)
{
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"thread.php?id=".$tid."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"thread.php?id=".$tid."\">1</a>".$pagelinks;
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";
}

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
		$poster = unserialize(serialize($post));
		$poster['id'] = $post['uid'];
		MakePost($post, $tid, $fid);
	}
}

if($pagelinks)
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>""), $links);

doFooter();

?>
