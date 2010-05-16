<?php
//  AcmlmBoard XD - Posts by user viewer
//  Access: all

include("lib/common.php");

if(!isset($_GET['id']))
	Kill("User ID unspecified.");

$id = (int)$_GET['id'];

$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

$total = $user['posts'];
$ppp = $loguser['postsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$ppp) $ppp = 25;

$qPosts = "select ";
$qPosts .=
	"posts.thread, posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.globalblock, users.lastposttime, users.lastactivity, users.regdate";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where users.id=".$id." order by date asc limit ".$from.", ".$ppp;

$rPosts = Query($qPosts);
$numonpage = NumRows($rPosts);

for($i = $ppp; $i < $total; $i+=$ppp)
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"listposts.php?id=".$id."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"listposts.php?id=".$id."\">1</a>".$pagelinks;
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";
}

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
		$qThread = "select * from threads where id=".$post['thread'];
		$rThread = Query($qThread);
		$thread = Fetch($rThread);

		$qForum = "select * from forums where id=".$thread['forum'];
		$rForum = Query($qForum);
		$forum = Fetch($rForum);
		
		if($forum['minpower'] > 0)
			continue;
			
		$post['threadname'] = $thread['title'];

		//print "<div><a name=\"".$post['id']."\" />\n";
		MakePost($post, $thread, -2);
		//print "</div>";
	}
}

if($pagelinks)
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

MakeCrumbs(array("Main"=>"./", $user['name']=>"profile.php?id=".$id, "Lists of posts"=>""), $links);

doFooter();

?>
