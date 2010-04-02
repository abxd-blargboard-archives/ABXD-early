<?php
include("lib/common.php");

$user = Fetch(Query("select * from users where id=1"));
//$user['postheader'] = "";
//$user['signature'] = "";

$preview =
"Regular text.
One break

Two breaks
Sentence with a<br />HTML BR tag inbetween

[b]BB Bold[/b], [i]BB Italic[/i], <strong>HTML Strong</strong>, <em>HTML Emphasis</em>, <b>HTML Bold</b>, <u>HTML Underline</u> (should become a styled span), [s]BB Strikethrough[/s] and <del>HTML Deleted</del>.

&lt;tag> with the opening bracket escaped

/me does an IRC action

[url]http://helmet.kafuka.org[/url]
[url=http://helmet.kafuka.org]Titled URL[/url]
[img]img/avatars/1[/img]

<span style=\"display: none\">If you can see this line, \"display:\" is filtered into nonfunctionality.</span>

<script>alert(\"Scripts should be filtered.\");</script>

[quote]Quote block[quote][quote][quote]Sub-quote[/quote]Sub-quote[/quote]Sub-quote[/quote][/quote]
[quote=Ryuzaki]Quote with attribution[/quote]
[quote=\"Ryuzaki\" id=\"52\"]Quote with attribution and link[/quote]
[reply=\"Ryuzaki\"]Reply, attribution and link mandatory.[/quote]

[code]BB Code
Second line
<tag> in the middle

Fifth line, after empty fourth[/code]

<pre>HTML Pre tag
  Second line, has two spaces.
<tag> in the middle, tag should not be visible, leaving one space.

Fifth line, after empty fourth
	Sixth line, after tab (probably 8 characters, starting at the 'n' in \"line\")</pre>

HTML Pre tag around a BB Code:
<pre>[code]".'//Smilies
if(!$noSmilies)
{
	for($i = 0; $i < count($smilies); $i++)
		$s = str_replace($smilies[$i][\'code\'], "«".$smilies[$i][\'image\']."»", $s);
	for($i = 0; $i < count($smilies); $i++)
		$s = str_replace("«".$smilies[$i][\'image\']."»", "<img src=\\\"img/smilies/".$smilies[$i][\'image\'].
			"\\\" alt=\\\"".$smilies[$i][\'code\']."\\\" />", $s);
}'."[/code]</pre>

Linebreak type tests:
\\\\r\\\\n \r\n Should have a break
\\\\n        \n Should have a break
\\\\r      \r   Should not break

Single spoiler: [spoiler]This is a spoiler.[/spoiler]
Double spoiler: [spoiler]This is a spoiler [spoiler]containing another spoiler[/spoiler] which may not work properly[/spoiler]
Spoiler in a quote: [quote=Ryuzaki][spoiler]I'm L.[/spoiler][/quote]

Youtube embed:
[youtube]8l0IbaOr6go[/youtube]

Video tag:
[video]../over9000.ogv[/video]
";

$previewPost['text'] = $preview;

$previewPost['num'] = "???";
$previewPost['id'] = "???";
MakePost($previewPost, $user, 0, 0);

doFooter();

?>
