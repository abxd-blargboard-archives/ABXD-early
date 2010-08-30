<?php

header("Content-type: application/rss+xml");

//Edit the following lines to your preference.
$feedname = "AcmlmBoard XD";
$boardurl = "http://helmet.kafuka.org/nikoboard";
$description = "The latest replies on the board.";
//</edit>

$maxPosts = 20;
$postCount = 0;

//header('Content-Type: text/xml; charset=utf8', true);
include("lib/mysql.php");
include("lib/snippets.php");
include("lib/post.php");

print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">

<channel>
	<title><?php print $feedname; ?></title>
	<link><?php print $boardurl; ?></link>
	<description><?php print $description; ?></description>
	<atom:link href="<?php print $boardurl; ?>/rss2.php" rel="self" type="application/rss+xml" />

<?php
	//$qPosts = "select * from posts order by date desc limit 0, ".($maxPosts * 2);
	$qPosts = "select ";
	$qPosts .=
	"posts.id, posts.thread, posts.date, posts.num, posts.deleted, posts.options, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname";
	$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
	$qPosts .= " where deleted=0 order by date desc limit 0, ".($maxPosts * 2);
	$rPosts = mysql_query($qPosts) or die(mysql_error());
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

		//$qPoster = "select * from users where id=".$post['user'];
		//$rPoster = Query($qPoster);
		//$poster = Fetch($rPoster);

		//$qText = "select text,revision from posts_text where pid=".$post['id']." order by revision desc limit 1";
		//$rText = Query($qText);
		//$text = Fetch($rText);
		//$post = array_merge($post, $text);
		
		$forum['title'] = htmlentities($forum['title']);
		$thread['title'] = htmlentities($thread['title']);
		$poster['name'] = htmlentities($poster['name']);

		$poster = $post;
		$poster['id'] = $post['uid'];

		print "\n<item>\n";
		
		//if($thread['replies'] < 1)
		//	print "<title>New thread by ".$poster['name']." (".$forum['title'].": ".$thread['title'].")</title>\n";
		//else
		//	print "<title>New reply by ".$poster['name']." (".$forum['title'].": ".$thread['title'].")</title>\n";
		$reply = ($post['revision'] > 0) ? "Post edited" : "New post";

		print "<title>".$reply." by ".$poster['name']." (".$forum['title'].": ".$thread['title'].")</title>\n";
			
		print "<link>".$boardurl."/thread.php?pid=".$post['id']."#".$post['id']."</link>\n";
		print "<pubDate>".gmdate(DATE_RFC1123, $post['date'])."</pubDate>\n";
		print "<description><![CDATA[".CleanUpPost($post['text'], $poster['name'], true)."]]></description>\n";
		print "<guid isPermaLink=\"false\">t".$thread['id']."p".$post['id']."</guid>\n";
		print "</item>\n";

		$numPosts++;
 		if($numPosts == $maxPosts)
 			break;
	}
?>

</channel>

</rss>
