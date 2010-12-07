<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators

include("lib/common.php");

AssertForbidden("viewAdminRoom");

if($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$title = "Administration";

write(
"
	<div class=\"outline margin faq\" style=\"float: left; width: 45%;\">
		<ul>
			<li><a href=\"recalc.php\">Recalculate statistics</a> (not admin-only)</li>
			<li><a href=\"lastknownbrowsers.php\">Last Known Browsers</a> (not admin-only)</li>
			<li><a href=\"editpora.php\">Edit Points of Required Attention Span</a></li>
			<li><a href=\"ipbans.php\">Manage IP bans</a></li>
			<li><a href=\"managemods.php\">Manage local moderator assignments</a></li>
			<li><a href=\"editfora.php\">Edit forum list</a></li>
			<li><a href=\"editcats.php\">Edit category list</a></li>
			<li><a href=\"editsettings.php\">Edit Settings</a></li>
			<li><a href=\"optimize.php\">Optimize tables</a></li>
			<li><a href=\"log.php\">View log</a></li>
			{1}
		</ul>
	</div>
	<div class=\"outline margin faq width50\" style=\"float: right;\">
		<dl>
			<dt>Last viewcount milestone:</dt>
			<dd>{0}</dd>
",	$misc['milestone'], ($loguser['powerlevel'] == 4 ? "<li><a href=\"sql.php\">SQL Console</a></li>
" : ""));
runBucket("admins");
write(
"
		</dl>
	</div>
");
?>
