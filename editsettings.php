<?php
//  AcmlmBoard XD - Board Settings editing page
//  Access: administrators

include("lib/common.php");

$title = "Edit settings";

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the Points of Required Attention.");

if($_POST['action'] == "Edit")
{
	$hax = fopen("lib/settings.php", "w");
	fputs($hax, "<?php\n");
	fputs($hax, "//Generated and parsed by the Board Settings admin panel.\n");
	fputs($hax, "\n");
	fputs($hax, "//Settings\n");
	fputs($hax, "\$boardname = \"".prepare($_POST['boardname'])."\";\n");
	fputs($hax, "\$logoalt = \"".prepare($_POST['logoalt'])."\";\n");
	fputs($hax, "\$logotitle = \"".prepare($_POST['logotitle'])."\";\n");
	fputs($hax, "\$dateformat = \"".prepare($_POST['dateformat'])."\";\n");
	fputs($hax, "\$autoLockMonths = ".(int)$_POST['autoLockMonths'].";\n");
	fputs($hax, "\$warnMonths = ".(int)$_POST['warnMonths'].";\n");
	fputs($hax, "\$customTitleThreshold = ".(int)$_POST['customTitleThreshold'].";\n");
	fputs($hax, "\$viewcountInterval = ".(int)$_POST['viewcountInterval'].";\n");
	fputs($hax, "\$overallTidy = ".($_POST['overallTidy'] != "" ? 1 : 0).";\n");
	fputs($hax, "\$theWord = \"".$_POST['theWord']."\";\n");
	fputs($hax, "\n");
	fputs($hax, "//Hacks\n");
	fputs($hax, "\$hacks['forcetheme'] = ".(int)$_POST['theme'].";\n");
	fputs($hax, "\$hacks['themenames'] = ".(int)$_POST['names'].";\n");
	fputs($hax, "\$hacks['postfilter'] = ".(int)$_POST['filters'].";\n");
	fputs($hax, "?>");
	fclose($hax);
	Redirect("Edited!","./","the main page");
}

$forcetheme = $hacks['forcetheme'];
$themenames = $hacks['themenames'];
$postfilter = $hacks['postfilter'];

$themelist[-1] = "[Disabled]";
foreach($themes as $theme)
	$themelist[] = $theme;
arsort($themes);
$names = array("[Disabled]", "Christmas", "Rainbow", "Anonymous");
$filters = array("[Disabled]", "Pirate", "Swedish Chef", "Kraut", "Jive", "Fudd");

print "<form action=\"editsettings.php\" method=\"post\">";
print "<table id=\"t\" class=\"outline margin width50\">";

print "<tr class=\"header1\">";
print "<th colspan=\"2\">Settings</th>";
print "<tr class=\"cell0\">";
print "<td><label for=\"boardname\">Board name</label></td><td><input type=\"text\" id=\"boardname\" name=\"boardname\" value=\"".htmlval($boardname)."\" class=\"width75\"></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"logoalt\">Logo alt text</label></td><td><input type=\"text\" id=\"logoalt\" name=\"logoalt\" value=\"".htmlval($logoalt)."\" class=\"width75\"></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"logotitle\">Logo title</label></td><td><input type=\"text\" id=\"logotitle\" name=\"logotitle\" value=\"".htmlval($logotitle)."\" class=\"width75\"></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"dateformat\">Datetime format</label></td><td><input type=\"text\" id=\"dateformat\" name=\"dateformat\" value=\"".htmlval($dateformat)."\"></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"autoLockMonths\">Autolock months</label></td><td><input type=\"text\" id=\"autoLockMonths\" name=\"autoLockMonths\" value=\"".$autoLockMonths."\"></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"warnMonths\">Bump warning months</label></td><td><input type=\"text\" id=\"warnMonths\" name=\"warnMonths\" value=\"".$warnMonths."\"></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"viewcountInterval\">Viewcount report interval</label></td><td><input type=\"text\" id=\"viewcountInterval\" name=\"viewcountInterval\" value=\"".$viewcountInterval."\"></td>";
print "<tr class=\"cell1\">";
print "<td><label for=\"customTitleThreshold\">Custom title threshold</label></td><td><input type=\"text\" id=\"customTitleThreshold\" name=\"customTitleThreshold\" value=\"".$customTitleThreshold."\"></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Markup Cleanup</td><td><input type=\"checkbox\" id=\"ot\" name=\"overallTidy\" ".($overallTidy ? "checked=\"checked\"" : "")."></input> <label for=\"ot\">Use HtmlTidy</label></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"theWord\">Registration word</label></td><td><input type=\"text\" id=\"theWord\" name=\"theWord\" value=\"".htmlval($theWord)."\"></td>";
print "</tr>";

print "<tr class=\"header1\">";
print "<th colspan=\"2\">Hacks</th>";
print "<tr class=\"cell1\">";
print "<td><label for=\"theme\">Theme</label></td><td>".MakeSelect("theme",$forcetheme,$themelist)."</td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"names\">Names</label></td><td>".MakeSelect("names",$themenames,$names)."</td>";
print "</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"filters\">Filters</label></td><td>".MakeSelect("filters",$postfilter,$filters)."</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</form>";

doFooter();

function MakeSelect($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " selected=\"selected\"";
	$result = "<select id=\"".$fieldname."\" name=\"".$fieldName."\" size=\"1\">";
	foreach($choicesList as $key=>$val)
		$result .= "<option value=\"".$key."\"".$checks[$key].">".$val."</option>";
	$result .= "</select>";
	return $result;
}

function prepare($text)
{
	$s = str_replace("\"", "\\\"", deSlashMagic($text));
	return $s;
}

?>