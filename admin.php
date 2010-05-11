<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$title = "Administration";

print "<div class=\"outline margin faq\">";
print "<ul>";
print "<li><a href=\"recalc.php\">Recalculate statistics</a> (not admin-only)</li>";
print "<li><a href=\"lastknownbrowsers.php\">Last Known Browsers</a> (not admin-only)</li>";
print "<li><a href=\"editpora.php\">Edit Points of Required Attention Span</a></li>";
print "<li><a href=\"ipbans.php\">Manage IP bans</a></li>";
print "<li><a href=\"managemods.php\">Manage local moderator assignments</a></li>";
print "<li><a href=\"editfora.php\">Edit forum list</a></li>";
print "<li><a href=\"editcats.php\">Edit category list</a></li>";
print "<li><a href=\"editsettings.php\">Edit Settings</a> (formerly Hax)</li>";
print "</div>";

doFooter();

?>
