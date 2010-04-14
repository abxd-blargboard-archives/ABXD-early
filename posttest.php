<?php
include("lib/common.php");

//$user = Fetch(Query("select * from users where id=1"));
//$user['postheader'] = "";
//$user['signature'] = "";
$user = $loguser;

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

GeSHi colorcoding:
[source]void Function()
{
  Console.Write(\"No language given, C# assumed.\");
  someVar += 2;
  // Note that this introduces several new CSS classes:
  // .geshi      main block -- all others are below this: \".geshi .kw0\"
  // .kw0 to 4   several kinds of keywords
  // .st0        string literals
  // .nu0        number literals
  // .br0        brackets
  // .sy0        operators and such
  // .co1        comments
}[/source]
[source=C]void Function()
{
  printf(\"This is plain C.\");
  someVar += 2;
}[/source]
[source=html4strict]<p>
  This is a paragraph.
  This is <b>bold</b>
</p>[/source]

Linebreak type tests:
\\r\\n \r\n Should have a break
\\n        \n Should have a break
\\r      \r   Should not break

Single spoiler: [spoiler]This is a spoiler.[/spoiler]
Double spoiler: [spoiler]This is a spoiler [spoiler]containing another spoiler[/spoiler] which may not work properly[/spoiler]
Spoiler in a quote: [quote=Ryuzaki][spoiler]I'm L.[/spoiler][/quote]

Youtube embed:
[youtube]8l0IbaOr6go[/youtube]

Video tag:
[video]../over9000.ogv[/video]

Flash tag:
[swf 300 200]../houseloop.swf[/swf]
";

$previewPost['text'] = $preview;

$previewPost['num'] = "???";
$previewPost['id'] = "???";
$previewPost['uid'] = $user['id'];
$copies = explode(",","title,name,picture,sex,powerlevel,avatar,postheader,signature,posts,regdate,lastactivity,lastposttime");
foreach($copies as $toCopy)
	$previewPost[$toCopy] = $user[$toCopy];
MakePost($previewPost, 0, 0);

doFooter();

?>
