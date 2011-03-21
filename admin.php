<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators

include("lib/common.php");

AssertForbidden("viewAdminRoom");

if($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$title = "Administration";

$cell = 1;
function cell($content) {
	global $cell;
	$cell = ($cell == 1 ? 0 : 1);
	Write("
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
		</tr>
	", $cell, $content);
}

Write("
	<table class=\"outline margin width50\" style=\"float: right;\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				Information
			</th>
		</tr>
");
cell(Format("
			
				Last viewcount milestone
			</td>
			<td style=\"width: 60%;\">
				{0}
			",	$misc['milestone'], ($loguser['powerlevel'] == 4 ? "<li><a href=\"sql.php\">SQL Console</a></li>
" : "")));

$bucket = "adminright"; include("./lib/pluginloader.php");

write(
"
	</table>
");

$cell = 1;
Write("
	<table class=\"outline margin width25\">
		<tr class=\"header1\">
			<th>
				Admin tools
			</th>
		</tr>
");
cell("<a href=\"recalc.php\">Recalculate statistics</a> (not admin-only)");
cell("<a href=\"lastknownbrowsers.php\">Last Known Browsers</a> (not admin-only)");
cell("<a href=\"editpora.php\">Edit Points of Required Attention Span</a>");
cell("<a href=\"ipbans.php\">Manage IP bans</a>");
cell("<a href=\"managemods.php\">Manage local moderator assignments</a>");
cell("<a href=\"editfora.php\">Edit forum list</a>");
cell("<a href=\"editcats.php\">Edit category list</a>");
cell("<a href=\"editsettings.php\">Edit Settings</a>");
cell("<a href=\"optimize.php\">Optimize tables</a>");
cell("<a href=\"log.php\">View log</a>");

$bucket = "adminleft"; include("./lib/pluginloader.php");

write(
"
	</table>
");
?>
