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

$qPosts = "select * from posts where user=".$id." order by date asc limit ".$from.", ".$ppp;
$rPosts = Query($qPosts);
$numonpage = NumRows($rPosts);

for($i = $ppp; $i < $total; $i+=$ppp)
	$pagelinks .= " <a href=\"listposts.php?id=".$id."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	$pagelinks = "<a href=\"listposts.php?id=".$id."\">1</a>".$pagelinks;
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";
}

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
//Revisions
		$qText = "select text,revision from posts_text where pid=".$post['id']." order by revision desc limit 1";
		$rText = Query($qText);
		$text = Fetch($rText);
		$post = array_merge($post, $text);
//</revisions>

		$qThread = "select * from threads where id=".$post['thread'];
		$rThread = Query($qThread);
		$thread = Fetch($rThread);

		print "<div><a name=\"".$post['id']."\" />\n";
		MakePost($post, $user, $thread, -1);
		print "</div>";
	}
}

if($pagelinks)
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

MakeCrumbs(array("Main"=>"./", $user['name']=>"profile.php?id=".$id, "Lists of posts"=>""), $links);

doFooter();

?>
