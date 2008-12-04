<?php
//  AcmlmBoard XD - Post revision comparison page
//  Access: moderators

include('lib/common.php');
include('lib/diff/Diff.php');
include('lib/diff/Diff/Renderer/inline.php');

$pid = (int)$_GET['id'];
$r1 = (int)$_GET['r1'];
$r2 = (int)$_GET['r2'];

$qPost = "select thread,user,num from posts where id=".$pid;
$rPost = Query($qPost);
if(NumRows($rPost))
	$post = Fetch($rPost);
else
	Kill("Unknown post ID.");

$qThread = "select * from threads where id=".$post['thread'];
$rThread = Query($qThread);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Unknown thread ID.");

$qUser = "select * from users where id=".$post['user'];
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");


if(!CanMod($loguserid, $thread['forum']))
	Kill("You're not allowed to view revisions.");

$qRev1 = "select text from posts_text where pid=".$pid." and revision=".$r1;
$qRev2 = "select text from posts_text where pid=".$pid." and revision=".$r2;
$rRev1 = Query($qRev1);
$rRev2 = Query($qRev2);
if(NumRows($rRev1) == 0)
	Kill("Could not load revision ".$r1.".");
if(NumRows($rRev2) == 0)
	Kill("Could not load revision ".$r2.".");
$rev1 = Fetch($rRev1);
$rev2 = Fetch($rRev2);
$rev1 = stripslashes($rev1['text']);
$rev2 = stripslashes($rev2['text']);

$diff = &new Text_Diff("native",array(explode("\n",$rev1),explode("\n",$rev2)));

?>
<style type=text/css>
ins::before, del::before, ins::after, del::after
{
	display: inline-block !important;
	text-decoration: none !important;
}

del::before, del::after
{
	background-color: #800;
	color: #F00;
}
ins::before, ins::after
{
	background-color: #080;
	color: #0F0;
}

del::before
{
	content: "<DEL>";
}
del::after
{
	content: "</DEL>";
}

ins::before
{
	content: "<INS>";
}
ins::after
{
	content: "</INS>";
}

del
{
	text-decoration: none;
	background-color: #400 !important;
	color: #F88;
}
ins
{
	text-decoration: none;
	background-color: #040 !important;
	color: #8F8;
}
*/
</style>
<?php

$renderer = &new Text_Diff_Renderer_inline();

print "<div>Comparing post #".$pid." revisions ".$r1." and ".$r2."...</div>";
$previewPost['text'] = str_replace("\n","<br>",$renderer->render($diff));;
$previewPost['num'] = $post['num'];
MakePost($previewPost, $user, $tid, $fid);


doFooter();

?>
