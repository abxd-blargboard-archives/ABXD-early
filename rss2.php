<?php

//Edit the following lines to your preference.
$feedname = "AcmlmBoard XD";
$boardurl = "http://helmetedrodent.kickassgamers.com/ab3test";
$description = "The latest replies on the board.";
//</edit>

$numposts = 20;

header('Content-Type: text/xml; charset=utf8', true);
include("lib/mysql.php");
include("lib/post.php");

print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<rss version="2.0">

<channel>
	<title><?php print $feedname; ?></title>
	<link><?php print $boardurl; ?></link>
	<description><?php print $description; ?></description>

<?php
	$qPosts = "select * from posts order by date desc limit 0, ".$numposts;
	$rPosts = Query($qPosts);
	while($post = Fetch($rPosts))
	{
		$qThread = "select * from threads where id=".$post['thread'];
		$rThread = Query($qThread);
		$thread = Fetch($rThread);

		$qForum = "select * from forums where id=".$thread['forum'];
		$rForum = Query($qForum);
		$forum = Fetch($rForum);

		$qPoster = "select * from users where id=".$post['user'];
		$rPoster = Query($qPoster);
		$poster = Fetch($rPoster);

		$qText = "select text,revision from posts_text where pid=".$post['id']." order by revision desc limit 1";
		$rText = Query($qText);
		$text = Fetch($rText);
		$post = array_merge($post, $text);

		print "\n<item>\n";
		print "<title>New reply by ".$poster['name']." (".$forum['title'].": ".$thread['title'].")</title>\n";
		print "<link>".$boardurl."/thread.php?pid=".$post['id']."#".$post['id']."</link>\n";
		print "<pubDate>".date(DATE_RFC1123, $post['date'])."</pubDate>\n";
		print "<description><![CDATA[".CleanUpPost($post['text'],$poster)."]]></description>\n";
		print "</item>\n";
	}
?>

</channel>

</rss>
