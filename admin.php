<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$title = "Administration";

write(
"
	<div class=\"outline margin faq width50\" style=\"float: left\">
		<ul>
			<li><a href=\"recalc.php\">Recalculate statistics</a> (not admin-only)</li>
			<li><a href=\"lastknownbrowsers.php\">Last Known Browsers</a> (not admin-only)</li>
			<li><a href=\"editpora.php\">Edit Points of Required Attention Span</a></li>
			<li><a href=\"ipbans.php\">Manage IP bans</a></li>
			<li><a href=\"managemods.php\">Manage local moderator assignments</a></li>
			<li><a href=\"editfora.php\">Edit forum list</a></li>
			<li><a href=\"editcats.php\">Edit category list</a></li>
			<li><a href=\"editsettings.php\">Edit Settings</a></li>
		</ul>
	</div>
	<div class=\"outline margin faq width25\" style=\"float: right\">
		<dl>
			<dt>Last viewcount milestone</dt>
			<dd>{0}</dd>
		</dl>
	</div>
",	$misc['milestone']);

?>
