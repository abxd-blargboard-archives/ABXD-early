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
	fputs($hax, "\$theWord = \"".prepare($_POST['theWord'])."\";\n");
	fputs($hax, "\$systemUser = ".(int)$_POST['systemUser'].";\n");
	fputs($hax, "\n");
	fputs($hax, "//Hacks\n");
	fputs($hax, "\$hacks['forcetheme'] = ".(int)$_POST['theme'].";\n");
	fputs($hax, "\$hacks['themenames'] = ".(int)$_POST['names'].";\n");
	fputs($hax, "\$hacks['postfilter'] = ".(int)$_POST['filters'].";\n");
	fputs($hax, "\n");
	fputs($hax, "//Profile Preview Post\n");
	fputs($hax, "\$profilePreviewText = \"".prepare($_POST['previewtext'], "\\\"")."\";\n");
	fputs($hax, "\n");
	fputs($hax, "//Meta\n");
	fputs($hax, "\$metaDescription = \"".prepare($_POST['metadesc'], "\\\"")."\";\n");
	fputs($hax, "\$metaKeywords = \"".prepare($_POST['metakeys'], "\\\"")."\";\n");
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

if(!function_exists('tidy_repair_string'))
	$tidyAvailable = "disabled=\"disabled\"";

write(
"
	<form action=\"editsettings.php\" method=\"post\">
		<table class=\"outline margin width50\">

			<tr class=\"header1\">
				<th colspan=\"2\">
					Settings
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"boardname\">Board name</label>
				</td>
				<td>
					<input type=\"text\" id=\"boardname\" name=\"boardname\" value=\"{0}\" class=\"width75\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"logoalt\">Logo alt text</label>
				</td>
				<td>
					<input type=\"text\" id=\"logoalt\" name=\"logoalt\" value=\"{1}\" class=\"width75\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"logotitle\">Logo title</label>
				</td>
				<td>
					<input type=\"text\" id=\"logotitle\" name=\"logotitle\" value=\"{2}\" class=\"width75\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"dateformat\">Datetime format</label>
				</td>
				<td>
					<input type=\"text\" id=\"dateformat\" name=\"dateformat\" value=\"{3}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"autoLockMonths\">Autolock months</label>
				</td>
				<td>
					<input type=\"text\" id=\"autoLockMonths\" name=\"autoLockMonths\" value=\"{4}\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"warnMonths\">Bump warning months</label>
				</td>
				<td>
					<input type=\"text\" id=\"warnMonths\" name=\"warnMonths\" value=\"{5}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"viewcountInterval\">Viewcount report interval</label>
				</td>
				<td>
					<input type=\"text\" id=\"viewcountInterval\" name=\"viewcountInterval\" value=\"{6}\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"customTitleThreshold\">Custom title threshold</label>
				</td>
				<td>
					<input type=\"text\" id=\"customTitleThreshold\" name=\"customTitleThreshold\" value=\"{7}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					Markup Cleanup
				</td>
				<td>
					<label>
						<input type=\"checkbox\" id=\"ot\" name=\"overallTidy\" {8} {14} />
						Use HtmlTidy
					</label>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"theWord\">Registration word</label>
				</td>
				<td>
					<input type=\"text\" id=\"theWord\" name=\"theWord\" value=\"{9}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"systemUser\">System user ID</label>
				</td>
				<td>
					<input type=\"text\" id=\"systemUser\" name=\"systemUser\" value=\"{10}\" />
				</td>
			</tr>

			<tr class=\"header1\">
				<th colspan=\"2\">
					Hacks
				</th>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"theme\">Theme</label>
				</td>
				<td>{11}
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"names\">Names</label>
				</td>
				<td>{12}
				</td>
			</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"filters\">Filters</label>
				</td>
				<td>{13}
				</td>
			</tr>
			<tr class=\"header0\">
				<th colspan=\"2\">
					Profile Preview Post
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"ppp\">Text</label>
				</td>
				<td>
					<textarea id=\"ppp\" name=\"previewtext\" rows=\"8\" style=\"width: 98%;\">{15}</textarea>
				</td>
			</tr>
			<tr class=\"header0\">
				<th colspan=\"2\">
					Meta
				</th>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"metadesc\">Description</label>
				</td>
				<td>
					<input type=\"text\" id=\"metadesc\" name=\"metadesc\" value=\"{16}\" class=\"width75\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"metakeys\">Keywords</label>
				</td>
				<td>
					<input type=\"text\" id=\"metakeys\" name=\"metakeys\" value=\"{17}\" class=\"width75\" />
				</td>
			</tr>
			<tr class=\"cell2\">
				<td>
				</td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"Edit\" />
				</td>
			</tr>
		</table>
	</form>
",	htmlval($boardname), htmlval($logoalt), htmlval($logotitle), htmlval($dateformat),
	$autoLockMonths, $warnMonths, $viewcountInterval, $customTitleThreshold,
	($overallTidy ? "checked=\"checked\"" : ""), htmlval($theWord), $systemUser,
	MakeSelect("theme",$forcetheme,$themelist), MakeSelect("names",$themenames,$names),
	MakeSelect("filters",$postfilter,$filters), $tidyAvailable, $profilePreviewText,
	htmlval($metaDescription), htmlval($metaKeywords)
);

function MakeSelect($fieldName, $checkedIndex, $choicesList, $extras = "")
{
	$checks[$checkedIndex] = " selected=\"selected\"";
	foreach($choicesList as $key=>$val)
		$options .= format("
						<option value=\"{0}\"{1}>{2}</option>", $key, $checks[$key], $val);
	$result = format(
"
					<select id=\"{0}\" name=\"{0}\" size=\"1\" {1} >{2}
					</select>", $fieldName, $extras, $options);
	return $result;
}

function prepare($text, $quot = "&quot;")
{
	$s = str_replace("\"", $quot, deSlashMagic($text));
	return $s;
}

?>