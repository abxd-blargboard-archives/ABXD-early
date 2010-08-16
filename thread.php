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

$qCategories = "select * from categories where id=".$forum['catid'];
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	$category = Fetch($rCategories);
	if($category['minpower'] > $pl)
		Kill("You are not allowed to browse forums in this category.");
} else
	Kill("Unknown category ID.");

$thread['title'] = htmlspecialchars($thread['title']);
$title = $thread['title'];
$thread['title'] .= " ".ParseThreadTags($thread['title']);

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
		$poll = Fetch(Query("select * from poll where id=".$thread['poll']));
		if($poll['doublevote'])
		{
			//Multivote.
			$rCheck = Query("select * from pollvotes where poll=".$thread['poll']." and choice=".(int)$_GET['vote']." and user=".$loguserid);
			if(NumRows($rCheck))
				$rCheck = Query("delete from pollvotes where  poll=".$thread['poll']." and choice=".(int)$_GET['vote']." and user=".$loguserid);		
			else
				$rPoll = Query("insert into pollvotes (poll, choice, user) values (".(int)$thread['poll'].", ".(int)$_GET['vote'].", ".(int)$loguserid.")");
		}
		else
		{
			//Single vote only?
			//Remove any old votes by this user on this poll, then add a new one.
			$rCheck = Query("select * from pollvotes where poll=".$thread['poll']." and user=".$loguserid);
			if(NumRows($rCheck))
				$rCheck = Query("delete from pollvotes where  poll=".$thread['poll']." and user=".$loguserid);
			$rPoll = Query("insert into pollvotes (poll, choice, user) values (".(int)$thread['poll'].", ".(int)$_GET['vote'].", ".(int)$loguserid.")");
		}
	}
	else
		Kill("This is not a poll.");
}

if($loguser['powerlevel'] == -1)
	$links .= "<li>You're banned.</li>";
elseif(!$thread['closed'] || $loguser['powerlevel'] > 2)
	$links .= "<li><a href=\"newreply.php?id=".$tid."\">Post Reply</a></li>";
else
	$links .= "Thread closed. | ";
if(CanMod($loguserid,$forum['id']))
{
	$links .= "<li><a href=\"editthread.php?id=".$tid."\">Edit</a></li>";
	if($thread['closed'])
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=open\">Open</a></li>";
	else
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=close\">Close</a></li>";
	if($thread['sticky'])
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=unstick\">Unstick</a></li>";
	else
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=stick\">Stick</a></li>";
	$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=delete\" onclick=\"if(!confirm('Are you sure you want to just up and delete this whole thread?') || !confirm('Seriously?')) return false;\">Delete</a></li>";
	if(strpos($forum['description'],"[trash]") === FALSE)
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=trash\">Trash</a></li>";
}
else if($thread['user'] == $loguserid)
{
	$links .= "<li><a href=\"editthread.php?id=".$tid."\">Edit</a></li>";
}

if($isBot)
	$links = "";

//$links = substr($links, 0, strlen($links) - 2);

$onlineUsers = OnlineUsers($fid);

write(
"
	<script type=\"text/javascript\" src=\"lib/Tween.js\"></script>
	<script type=\"text/javascript\" src=\"lib/OpacityTween.js\"></script>
	<script type=\"text/javascript\">
		window.onload = function() { startOnlineUsers({0}); };
	</script>
	<div class=\"header0 cell1 center outline smallFonts\" style=\"overflow: auto;\">
		<span id=\"onlineUsers\">
			{1}
		</span>
	</div>
", $fid, $onlineUsers);

DoPrivateMessageBar();

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>""), $links);

if($thread['poll'])
{
	$qPoll = "select * from poll where id=".$thread['poll'];
	$rPoll = Query($qPoll);
	if(NumRows($rPoll))
	{
		$poll = Fetch($rPoll);

		$qCheck = "select * from pollvotes where poll=".$thread['poll']." and user=".$loguserid;
		$rCheck = Query($qCheck);
		if(NumRows($rCheck))
		{
			while($check = Fetch($rCheck))
				$pc[$check['choice']] = "&#x2714; "; //use &#x2605; for a star
		}

		$qVotes = "select count(*) from pollvotes where poll=".$thread['poll'];
		$totalVotes = FetchResult($qVotes);

		$qOptions = "select * from poll_choices where poll=".$thread['poll'];
		$rOptions = Query($qOptions);
		$pops = 0;
		$options = array();
		$noColors = 0;
		$defaultColors = array(
					  "#0000B6","#00B600","#00B6B6","#B60000","#B600B6","#B66700","#B6B6B6",
			"#676767","#6767FF","#67FF67","#67FFFF","#FF6767","#FF67FF","#FFFF67","#FFFFFF",);
		while($option = Fetch($rOptions))
		{
			$options[] = $option;
		}
		foreach($options as $option)
		{
			if($option['color'] == "")
				$option['color'] = $defaultColors[($pops + 9) % 15];
				
			$option['choice'] = htmlspecialchars($option['choice']);

			$qVotes = "select * from pollvotes where poll=".$thread['poll']." and choice=".$pops;
			$rVotes = Query($qVotes);
			$votes = NumRows($rVotes);

			$cellClass = ($cellClass+1) % 2;
			if($loguserid && !$thread['closed'])
				$label = format("{0} <a href=\"thread.php?id={1}&amp;vote={2}\">{3}</a>", $pc[$pops], $thread['id'], $pops, $option['choice']);
			else
				$label = format("{0} {1}", $pc[$pops], $option['choice']);
			
			$bar = "&nbsp;0";
			if($totalVotes > 0)
			{
				$width = 100 * ($votes / $totalVotes);
				$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", $votes, $totalVotes, $width);
				$bar = format("<div class=\"pollbar\" style=\"background: {0}; width: {1}%;\" title=\"{2}\">&nbsp;{3}</div>", $option['color'], $width, $alt, $votes);
				if($width == 0)
					$bar = "&nbsp;".$votes;
			}

			$pollLines .= format(
"
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td class=\"width75\">
				<div class=\"pollbarContainer\">
					{2}
				</div>
			</td>
		</tr>
", $cellClass, $label, $bar);
			$pops++;
		}
		write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"2\">
				Poll
			</th>
		</tr>
		<tr class=\"cell0\">
			<td colspan=\"2\">
				{1}
			</td>
		</tr>
		{2}
	</table>
", $cellClass, htmlspecialchars($poll['question']), $pollLines);
	}
}

$qRead = "delete from threadsread where id=".$loguserid." and thread=".$tid;
$rRead = Query($qRead);
$qRead = "insert into threadsread (id,thread,date) values (".$loguserid.", ".$tid.", ".time().")";
$rRead = Query($qRead);

$activity = array();
$rActivity = Query("select user, count(*) num from posts where date > ".(time() - 86400)." group by user");
while($act = Fetch($rActivity))
	$activity[$act['user']] = $act['num'];

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
	"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.signsep, users.globalblock, users.lastposttime, users.lastactivity, users.regdate";
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
	write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);
}

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
		//$poster = $post;
		//$poster['id'] = $post['uid'];
		$post['activity'] = $activity[$post['uid']];
		$post['closed'] = $thread['closed'];
		MakePost($post, $tid, $fid);
	}
}

if($pagelinks)
	write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>""), $links);

?>
