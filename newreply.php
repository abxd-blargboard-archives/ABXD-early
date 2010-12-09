<?php
//  AcmlmBoard XD - Reply submission/preview page
//  Access: users

include("lib/common.php");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("Thread ID unspecified.");

$tid = (int)$_GET['id'];
AssertForbidden("viewThread", $tid);
AssertForbidden("makeReply", $tid);

if($loguser['powerlevel'] == -1)
	Kill("You're banned.");

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
{
	$thread = Fetch($rThread);
	$fid = $thread['forum'];
}
else
	Kill("Unknown thread ID.");
$thread['title'] = htmlspecialchars($thread['title']);

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");
$fid = $forum['id'];
AssertForbidden("viewForum", $fid);

if($forum['minpowerreply'] > $loguser['powerlevel'])
	Kill("Your power is not enough.");	

if($thread['closed'] && $loguser['powerlevel'] < 3)
	Kill("This thread is locked.");

$onlineUsers = OnlineUsers($fid);

write(
"
	<script type=\"text/javascript\" src=\"lib/Tween.js\"></script>
	<script type=\"text/javascript\" src=\"lib/OpacityTween.js\"></script>
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");
if(!$noAjax)
	write(
"
	<script type=\"text/javascript\">
		onlineFID = {0};
		window.addEventListener(\"load\",  startOnlineUsers, false);
	</script>
	<div class=\"header0 cell1 center outline smallFonts\" style=\"overflow: auto;\">
		&nbsp;
		<span id=\"onlineUsers\">
			{1}
		</span>
		&nbsp;
	</div>
", $fid, $onlineUsers);

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

if($warnMonths > 0 && $thread['lastpostdate'] < time() - (2592000 * $warnMonths))
	Alert("You are about to bump an old thread. This is usually a very bad idea. Please think about what you are about to do before you press the Post button.");

if($_POST['text'] && CheckTableBreaks($_POST['text']))
{
	$_POST['action'] = "";
	Alert("This post would break the board layout.", "I'm sorry, Dave.");
}

if($_POST['text'] && $_POST['action'] != "Preview")
{
	$words = explode(" ", $_POST['text']);
	$wordCount = count($words);
	if($wordCount < $minWords)
	{
		$_POST['action'] = "";
		Alert("Your post is too short to have any real meaning. Try a little harder.", "I'm sorry, Dave.");
	}
}

$ninja = FetchResult("select id from posts where thread=".$tid." order by date desc limit 0, 1",0,0);
if($_POST['action'] && isset($_POST['ninja']) && $_POST['ninja'] != $ninja)
{
	$_POST['action'] == "Preview";
	Alert("You got ninja'd. You might want to review the post made while you were typing before you submit yours.");
}

if($_POST['text'] && $_POST['action'] == "Post")
{
	$lastPost = time() - $loguser['lastposttime'];
	if($lastPost < $minSeconds)
	{
		$_POST['action'] = "";
		Alert("You're going too damn fast! Slow down a little.", "Hold your horses.");
	}
}

$postingAs = $loguserid;
$postingAsUser = $loguser;
if($_POST['username'] != "" && $_POST['password'] != "")
{
	//Entered another user's name and password. Look it up now.
	$original = $_POST['password'];
	$sha = hash("sha256", $original.$salt, FALSE);
	$qUser = "select * from users where name='".justEscape($_POST['username'])."' and password='".$sha."'";
	$rUser = Query($qUser);
	if(NumRows($rUser))
	{
		$postingAsUser = Fetch($rUser);
		$postingAs = $postingAsUser['id'];
		$postingAsUser['uid'] = $postingAs;
		if($postingAsUser['powerlevel'] == -1)
		{
			Alert("Nope, still banned.");
			$_POST['action'] = "";
			$_POST['password'] = "";
		}
	}
	else
	{
		Alert("Invalid user name or password.");
		$_POST['action'] = "";
		$_POST['password'] = "";
	}
}

if($_POST['action']=="Post")
{
	if($postingAs == 0)
		Kill("You must be logged in to post.");

	if($_POST['text'])
	{
		$post = htmlentities2(deSlashMagic($_POST['text']));
		$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
		TidyPost($post);
		$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
		$post = mysql_real_escape_string($post);

		$options = 0;
		if($_POST['nopl']) $options |= 1;
		if($_POST['nosm']) $options |= 2;
		if($_POST['nobr']) $options |= 4;
		
		if(CanMod($loguserid, $forum['id']))
		{
			if($_POST['lock'])
				$mod.= ", closed = 1";
			else if($_POST['unlock'])
				$mod.= ", closed = 0";
			if($_POST['stick'])
				$mod.= ", sticky = 1";
			else if($_POST['unstick'])
				$mod.= ", sticky = 0";
		}

		if($thread['lastposter']==$postingAs && $thread['lastpostdate']>=time()-86400 && $postingAsUser['powerlevel']<3)
			Kill("You can't double post until it's been at least one day.");

		$qUsers = "update users set posts=".($postingAsUser['posts']+1).", lastposttime=".time()." where id=".$postingAs." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num, options, mood) values (".$tid.",".$postingAs.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($postingAsUser['posts']+1).", ".$options.", ".(int)$_POST['mood'].")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$postingAs.", lastpostid=".$pid." where id=".$fid." limit 1";
		$rFora = Query($qFora);

		$qThreads = "update threads set lastposter=".$postingAs.", lastpostdate=".time().", replies=".($thread['replies']+1).", lastpostid=".$pid.$mod." where id=".$tid." limit 1";
		$rThreads = Query($qThreads);

		if($forum['minpower'] < 1)
			Report("New reply by [b]".$postingAsUser['name']."[/] in [b]".$thread['title']."[/] (".$forum['title'].") -> [g]#HERE#?pid=".$pid);
		Redirect("Posted!","thread.php?pid=".$pid."#".$pid,"the thread");
		exit();
	}
	else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_POST['text'])
	$prefill = htmlentities2(deSlashMagic($_POST['text']));

if($_POST['action']=="Preview")
{
	if($_POST['text'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = $postingAsUser['posts']+1;
		$previewPost['posts'] = $postingAsUser['posts']+1;
		$previewPost['id'] = "???";
		$previewPost['uid'] = $postingAs;
		$copies = explode(",","title,name,displayname,picture,sex,powerlevel,avatar,postheader,signature,signsep,regdate,lastactivity,lastposttime");
		foreach($copies as $toCopy)
			$previewPost[$toCopy] = $postingAsUser[$toCopy];
		$previewPost['mood'] = (int)$_POST['mood'];
		$previewPost['options'] = 0;
		if($_POST['nopl']) $previewPost['options'] |= 1;
		if($_POST['nosm']) $previewPost['options'] |= 2;
		if($_POST['nobr']) $previewPost['options'] |= 4;
		MakePost($previewPost, 0, $fid);
	}
	else
		Alert("Enter a message and try again.","Your post is empty.");
}

if(!$_POST['text'])
	$_POST['text'] = $post['text'];
if($_POST['text'])
	$prefill = htmlval(deSlashMagic($_POST['text']));

if($_GET['link'])
{
	$prefill = ">>".(int)$_GET['link']."\r\n\r\n";
}
else if($_GET['quote'])
{
	$qQuote = "select * from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision where id=".(int)$_GET['quote'];
	$rQuote = Query($qQuote);
	
	if(NumRows($rQuote))
	{
		$quote = Fetch($rQuote);

		//SPY CHECK!
		$qThread = "select forum from threads where id=".$quote['thread'];
		$rThread = Query($qThread);
		$thread = Fetch($rThread);
		$qForum = "select minpower from forums where id=".$thread['forum'];
		$rForum = Query($qForum);
		$forum = Fetch($rForum);
		if($forum['minpower'] > $postingAsUser['powerlevel'])
			$quote['text'] = str_rot13("Pools closed due to not enough power. Prosecutors will be violated.");

		$qPoster = "select * from users where id=".$quote['user'];
		$rPoster = Query($qPoster);
		$poster = Fetch($rPoster);
		$prefill = "[quote=\"".$poster['name']."\" id=\"".$quote['id']."\"]".htmlval($quote['text'])."[/quote]";
		$prefill = str_replace("/me", "[b]* ".$poster['name']."[/b]", $prefill);
	}
}

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";
if($_POST['nosm'])
	$nosm = "checked=\"checked\"";
if($_POST['nobr'])
	$nobr = "checked=\"checked\"";

if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">[Default avatar]</option>\n";
$rMoods = Query("select mid, name from moodavatars where uid=".$postingAs." order by mid asc");
while($mood = Fetch($rMoods))
	$moodOptions .= format(
"
	<option {0} value=\"{1}\">{2}</option>
",	$moodSelects[$mood['mid']], $mood['mid'], $mood['name']);

$ninja = FetchResult("select id from posts where thread=".$tid." order by date desc limit 0, 1",0,0);

if(CanMod($loguserid, $fid))
{
	$mod = "\n\n<!-- Mod options -->\n";
	//print $thread['closed'];
	if(!$thread['closed'])
		$mod .= "<label><input type=\"checkbox\" name=\"lock\">&nbsp;Close&nbsp;thread</label>\n";
	else
		$mod .= "<label><input type=\"checkbox\" name=\"unlock\">&nbsp;Open&nbsp;thread</label>\n";
	if(!$thread['sticky'])
		$mod .= "<label><input type=\"checkbox\" name=\"stick\">&nbsp;Sticky</label>\n";
	else
		$mod .= "<label><input type=\"checkbox\" name=\"unstick\">&nbsp;Unstick</label>\n";
	$mod .= "<!-- More could follow -->\n\n";
}

write(
"
	<table style=\"width: 100%;\">
		<tr>
			<td style=\"vertical-align: top; border: none;\">
				<form action=\"newreply.php\" method=\"post\">
					<input type=\"hidden\" name=\"ninja\" value=\"{0}\" />
					<table class=\"outline margin width100\">
						<tr class=\"header1\">
							<th colspan=\"2\">
								New Reply
							</th>
						</tr>
						<tr class=\"cell0\">
							<td>
								<label for=\"uname\">
									User&nbsp;name
								</label>
							</td>
							<td>
								<input type=\"text\" id=\"uname\" name=\"username\" value=\"{1}\" style=\"width: 98%;\" maxlength=\"25\" />
							</td>
						</tr>
						<tr class=\"cell1\">
							<td>
								<label for=\"upass\">
									Password
								</label>
							</td>
							<td>
								<input type=\"password\" id=\"upass\" name=\"password\" value=\"{2}\" size=\"13\" maxlength=\"32\" />
							</td>
						</tr>
						<tr class=\"cell0\">
							<td>
								<label for=\"text\">
									Post
								</label>
							</td>
							<td>
								<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">{3}</textarea>
							</td>
						</tr>
						<tr class=\"cell2\">
							<td></td>
							<td>
								<input type=\"submit\" name=\"action\" value=\"Post\" /> 
								<input type=\"submit\" name=\"action\" value=\"Preview\" />
								<select size=\"1\" name=\"mood\">
									{4}
								</select>
								<label>
									<input type=\"checkbox\" name=\"nopl\" {5} />&nbsp;Disable&nbsp;post&nbsp;layout
								</label>
								<label>
									<input type=\"checkbox\" name=\"nosm\" {6} />&nbsp;Disable&nbsp;smilies
								</label>
								<label>
									<input type=\"checkbox\" name=\"nobr\" {9} />&nbsp;Disable&nbsp;auto-&lt;br>
								</label>
								<input type=\"hidden\" name=\"id\" value=\"{7}\" />
								{8}
							</td>
						</tr>
					</table>
				</form>
			</td>
			<td style=\"width: 200px; vertical-align: top; border: none;\">
",	$ninja, htmlval($postingAsUser['name']), $_POST['password'], $prefill, $moodOptions, $nopl, $nosm, $tid, $mod, $nobr);

DoSmileyBar();
DoPostHelp();

write("
			</td>
		</tr>
	</table>
");

$qPosts = "select ";
$qPosts .=
	"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname, users.rankset, users.powerlevel, users.sex, users.posts";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where thread=".$tid." and deleted=0 order by date desc limit 0, 20";

$rPosts = Query($qPosts);
if(NumRows($rPosts))
{
	$posts = "";
	while($post = Fetch($rPosts))
	{
		$cellClass = ($cellClass+1) % 2;

		$poster = $post;
		$poster['id'] = $post['uid'];

		$nosm = $post['options'] & 2;
		$nobr = $post['options'] & 4;

		$posts .= Format(
"
		<tr>
			<td class=\"cell2\">
				{1}
			</td>
			<td class=\"cell{0}\">
				<button style=\"float: right;\" onclick=\"insertQuote({2});\">Quote</button>
				<button style=\"float: right;\" onclick=\"insertChanLink({2});\">Link</button>
				{3}
			</td>
		</tr>
",	$cellClass, UserLink($poster), $post['id'], CleanUpPost($post['text'], $poster['name'], $nosm, $nobr));
	}
	Write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"2\">Thread review</th>
		</tr>
		{0}
	</table>
",	$posts);
}

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

?>
