<?php
include("lib/mysql.php");
$action = $_GET['a'];
$id = (int)$_GET['id'];
if($action == "q")
{
	$qPost = "select * from posts where id=".$id;
	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die("Unknown post ID.");
	$qText = "select text from posts_text where pid=".$id." order by revision desc limit 1";
	$text = FetchResult($qText, 0, 0);
	$qPoster = "select name from users where id=".$post['user'];
	$rPoster = Query($qPoster);
	$poster = Fetch($rPoster);
	$reply = "[quote=\"".$poster['name']."\" id=\"".$id."\"]".$text."[/quote]";
	die($reply);
}
die("Unknown action.");
?>
