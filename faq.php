<?php
//  AcmlmBoard XD - Frequently Asked Questions page
//  Access: all

include("lib/common.php");

/* Normally, I write my PHP with embedded HTML. However, the FAQ is very text-heavy which is a pain in the ass to do in embedded HTML, especially when there's a lot of " going on. And that is why, like lib/header.php, the FAQ is mostly raw HTML. -- Kawa */

?>

<div class="faq outline margin">
<h3 style="border-top: 0px none;">General disclaimer</h3>
<p>
	The site does not own and cannot be held responsible for statements made by members on the forum. This site is offered as-is to the user. Any statements made on the board may be altered or removed at the discretion of the staff. Furthermore, all users are expected to have read, understood, and agreed to this FAQ before posting.
</p>
<p>
	<strong>We do <em>not</em> sell, distribute or otherwise disclose member information like IP addresses to any third party.</strong> If you have questions about any information contained in this FAQ, please send a private message with your question to a moderator or administrator <em>before</em> posting.
</p>

<h3>Why should I register an account on the board?</h3>
<p>
	By registering a user account, you will be able to post on the board as well as use several features only accessible through registering, such as the ability to mark forums as read and private messaging. Unregistered users have guest access to the board, meaning they can view threads but not reply to them.
</p>

<h3>What's with the funky colors everyone's names are written in?</h3>
<p>
	The color of a given user's name is determined by that user's sex and power level.
</p>
<?php
	//This is where we embed a bit of PHP code to generate a nice table.
	$sexes = array(0=>"Male",1=>"Female",2=>"N/A");
	$powerlevels = array("x"=>"Banned user",0=>"Normal user",1=>"Local Moderator",2=>"Full Moderator",3=>"Administrator");
	print "<table style=\"width: 50%; margin-left: 25%; margin-right: auto\">";
	print "<tr class=\"header0\">";
	foreach($sexes as $sn => $ss)
	{
		print "<th>".$ss."</th>";
	}
	print "</tr>";
	foreach($powerlevels as $pn => $ps)
	{
		$cellClass = ($cellClass+1) % 2;
		print "<tr class=\"cell".$cellClass."\">";
		foreach($sexes as $sn => $ss)
		{
			print "<td class=\"nc".$sn.$pn." center\" style=\"font-weight: bold;\">".$ps."</td>";
		}
		print "</tr>";
	}
	print "</table>";
?>

<h3>What is the standard of acceptable behaviour on the board?</h3>
<p>
	First off, do <em>not</em> assume that our rules are the same as those of other bulletin boards &mdash; just because spammy posts might be acceptable on another forum, they are not necessarily so here.
</p>
<p>
	However, most of the rules are not that hard to follow, and as long as you keep them in mind, you should not run into trouble.
</p>
<h4>Do</h4>
<ul>
	<li>Follow the rules the local moderators proclaim in their forums.</li>
	<li>Listen to the administrators when they warn you.</li>
	<li>Use proper grammar and avoid '1337speak' and AOL speak (u/ur/y/ in place of words such as you/you're/why). It is a good way to gain respect.</li>
	<li><strong>Exercise common sense when posting.</strong></li>
	<li>Exercise caution when posting threads about controversial topics such as religion.</li>
</ul>
<h4>Don't</h4>
<ul>
	<li>Spam.</li>
	<li>Be a bigot. Hateful, discriminating or homophobic remarks are not welcome on this board. (<q>DUDE THATS GAY STFU UR GAY</q>)</li>
	<li>Bump (post in) old or outdated threads.</li>
	<li>Create multiple accounts unless explicitely authorized to do so by an administrator. Creation of secondary accounts to evade bans will result in a <strong>permanent</strong> banishment from the board.</li>
	<li>Ask any questions that could be answered using this FAQ or easily and commonly available sources.</li>
	<li>Stick around if you hate this board. Nobody is forcing you to.</li>
	<li>Act like the board is a chat room (post messages like <q>HEY ACMLM</q>, <q>I AGREE</q>, <q>What's up?</q>, or any messages directed to individuals). This is what the private message function is for.</li>
	<li>Flame, or purposefully try to be an ass to people.</li>
	<li>Ask to be a moderator, suck up or act like a moderator. It's one of the best ways <strong>not</strong> to get what you want.</li>
	<li>Threaten to hack the board. Chances are you can't. Besides, we have a few real hackers on staff who test the board constantly for any security problems.</li>
	<li>Post blatantly pornographic material in inappropriate situations where it might be frowned upon. In case of doubt, usage of NSFW tags in the thread title or on links is recommended.</li>
	<li>Post ROM/warez links on the board. You are free to exchange links with others through private messages, but do not post them out in the open.</li>
	<li>Join the board with the sole intent of advertising your site/service/etc.</li>
	<li>Respond to a spammer attack/obvious flame. If it's obvious that the post/thread in question will be deleted, don't post and make fun of the person or ask for the post/thread to be taken care of.</li>
</ul>
<p>
	Naturally, exceptions to most of those rules could arise out of social context or similar; however, unless you have gained a deeper understanding of the forum community, you are on the safe side assuming that to not be the case. In case of any remaining doubts, <em>common sense prevails</em>.
</p>

<h3>What is considered "spam" at the board?</h3>
<p>
	Spam is making:
</p>
<ul>
	<li>A post that is off-topic, meaning that it has nothing to do with the original thread.</li>
	<li>A post that is only a few words long and doesn't contribute anything meaningful to the discussion.</li>
	<li>A thread that doesn't have any real meaning.</li>
	<li>A thread with a poll that is pointless.</li>
	<li>A thread that is an exact duplicate of a pre-existing, recent thread.</li>
</ul>
<p>
	If you feel that a thread is spammy, then PM or IM an admin and ask them to take a look at it. Don't ask for someone to take a look at it in the thread itself or lecture the poster on the dangers of spamming.
</p>

<h3>What can I do about my thread being closed/trashed/deleted?</h3>
<p>
	First off, don't complain on the board about a thread being closed or deleted, and don't single out a certain mod or admin as being responsible. This will result in a ban. Learn from your mistakes and move on.
</p>
<p>
	If you feel you need clarification on why the thread was closed or if you feel a mistake was made, contact a local mod in charge of the forum or an admin and politely explain the problem.
</p>

<h3>Help! I've been banned! What do I do now?</h3>
<p>
	First off, <strong>do not</strong> create a new account to complain about your ban, as that will just result in a <strong>permanent</strong> ban from the board. If you feel that the ban was unfair or if you wish to know exactly why you were banned, PM an admin and calmly ask about it. Aside from that, the only thing you can do is wait for the ban to expire and make sure you know why it happened.
</p>

<h3> Are there any rules for post layouts?</h3>
<p>
	There are a few simple rules which all users should follow in their layouts:
</p>
<ul>
	<li>No flashy backgrounds which make text difficult to read. This doesn't mean you can't have a nice background, but at least make sure that the area where the text is doesn't make a post hard to read.</li>
	<li>No huge pictures or excessive amounts of them. Huge pictures just make threads longer to load and having too many animated GIFs can slow down some computers.</li>
	<li>No broken tables. If you're working with tables and feel that you might be messing up, check your post in your user profile. If the profile seems to be messed up, then the layout is as well. Broken tables can wreak havoc with threads.
	<li>The board is designed to be fairly dark. Suddenly looking at a relatively bright post layout can make your eyes hurt, so don't do that &mdash; keep the brightness subdued.</li>
	<li>Bad color contrast in general, for basically the same reason as above.</li>
</ul>
<p>
	If your layout is spotted or reported as breaking any of these rules, it will be deleted and you will be notified of what was wrong by private message, possibly with suggestions. If the problem is not related to images but perhaps just badly chosen color values we may try to fix it for you and inform you later by PM. Anything else is out of our hands, so yeah.
</p>
<p>
	The last few rules and the deletion/editing policy are blatantly taken from Stark. <a href="http://acmlm.no-ip.org/board/thread.php?id=4207">Thanks, dude!</a>
</p>
<p>
	On a highly related note, if you link in external CSS files you can use the $layout variable in your post header to link in specific versions: <code>&lt;link rel="stylesheet" type="text/css" href="http://.../MyLayout_<b>$theme</b>.css" /&gt;</code> becomes <code>&lt;link rel="stylesheet" type="text/css" href="http://.../MyLayout_<b><?php print $theme ?></b>.css" /&gt;</code> with the current board settings. The following are possible: <code><?php foreach($themeFiles as $t) print $t." "; ?></code>
</p>

<h3>How can I become a moderator or administrator?</h3>
<p>
	If us administrators trust you enough for this. Don't ask us, we may ask you if we ever feel you worthy of being promoted. Being a good and regular member helps, while asking for this doesn't. It also depends whether we feel a need to promote more people, which isn't so often the case.
</p>

<h3>How can I change my username?</h3>
<p>
	You can't change it yourself, only administrators can, but you can ask one of them for a name change.
</p>

</div>

<?php
doFooter();

?>
