<?php
$noViewCount = TRUE;
$noOnlineUsers = TRUE;
$noFooter = TRUE;
function cdate($format, $date = 0)
{
	global $loguser;
	if($date == 0)
		$date = gmmktime();
	$hours = (int)($loguser['timezone']/3600);
	$minutes = floor(abs($loguser['timezone']/60)%60);
	$plusOrMinus = $hours < 0 ? "" : "+";
	$timeOffset = $plusOrMinus.$hours." hours, ".$minutes." minutes";
	return gmdate($format, strtotime($timeOffset, $date));
}
$loguser['fontsize'] = 80;
$themeFile = "default.css";
include("lib/snippets.php");
include("lib/settings.php");
$logopic = "img/themes/default/logo.png";
$overallTidy = 0;
$misc['poratitle'] = "Installing&hellip;";
$misc['porabox'] = "Hold onto your hat.";
$title = "Installation";
ob_start("DoFooter");
$timeStart = usectime();
include("lib/feedback.php");
include("lib/header.php");
include("lib/write.php");

if(isset($_GET['delete']))
{
	include("lib/common.php");
	unlink("install.php") or Kill("Could not delete installation script.");
	Redirect("Installation file removed.","./","the main page");
}

if(!isset($_POST['action']))
{
	write(
"
	<form action=\"install.php\" method=\"post\">
		<table class=\"outline margin width50\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Installation options
				</th>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dbs\">Database server</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"dbs\" name=\"dbserv\" style=\"width: 98%;\" value=\"localhost\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dbn\">Database name</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"dbn\" name=\"dbname\" style=\"width: 98%;\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dun\">Database user name</label>
				</td>
				<td class=\"cell1\">
					<input type=\"text\" id=\"dun\" name=\"dbuser\" style=\"width: 98%;\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\"><label for=\"dpw\">Database user password</label></td>
				<td class=\"cell1\"><input type=\"password\" id=\"dpw\" name=\"dbpass\" style=\"width: 98%;\"></input></td>
			</tr>
			<tr>
				<td class=\"cell2\">
					Options
				</td>
				<td class=\"cell1\">
					<label>
						<input type=\"checkbox\" id=\"b\" name=\"addbase\" />
						Add starting forums and the usual Super Mario rankset
					</label>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"Install\" />
				</td>
			</tr>
		</table>
	</form>
");

}
else if($_POST['action'] == "Install")
{

	print "<div class=\"outline faq\">";
	print "Trying to connect to database&hellip;<br />";
	$dbserv = $_POST['dbserv'];
	$dbuser = $_POST['dbuser'];
	$dbpass = $_POST['dbpass'];
	$dbname = $_POST['dbname'];
	mysql_connect($dbserv, $dbuser, $dbpass) or Kill("Could not connect to database.");
	mysql_select_db($dbname) or Kill("Could not select database.");

	print "Writing database configuration file&hellip;<br />";
	$dbcfg = fopen("lib/database.php", "w+") or Kill("Could not open \"lib/database.php\" for writing.");
	fwrite($dbcfg, "<?php\n");
	fwrite($dbcfg, "//  AcmlmBoard XD support - Database settings\n\n");
	fwrite($dbcfg, "\$dbserv = \"".$dbserv."\";\n");
	fwrite($dbcfg, "\$dbuser = \"".$dbuser."\";\n");
	fwrite($dbcfg, "\$dbpass = \"".$dbpass."\";\n");
	fwrite($dbcfg, "\$dbname = \"".$dbname."\";\n");
	fwrite($dbcfg, "\n?>");
	fclose($dbcfg);

	print "Detecting Tidy support&hellip; ";
	$tidy = (int)function_exists('tidy_repair_string');
	if($tidy)
	{
		print "available.<br />";
		print "Writing board configuration file&hellip;<br />";
		include("lib/settings.php");
		$hax = fopen("lib/settings.php", "w");
		fputs($hax, "<?php\n");
		fputs($hax, "//Generated and parsed by the Board Settings admin panel.\n");
		fputs($hax, "\n");
		fputs($hax, "//Settings\n");
		fputs($hax, "\$boardname = \"".prepare($boardname)."\";\n");
		fputs($hax, "\$logoalt = \"".prepare($logoalt)."\";\n");
		fputs($hax, "\$logotitle = \"".prepare($logotitle)."\";\n");
		fputs($hax, "\$dateformat = \"".prepare($dateformat)."\";\n");
		fputs($hax, "\$autoLockMonths = ".(int)$autoLockMonths.";\n");
		fputs($hax, "\$warnMonths = ".(int)$warnMonths.";\n");
		fputs($hax, "\$customTitleThreshold = ".(int)$customTitleThreshold.";\n");
		fputs($hax, "\$viewcountInterval = ".(int)$viewcountInterval.";\n");
		fputs($hax, "\$overallTidy = ".(int)$tidy.";\n");
		fputs($hax, "\$theWord = \"".prepare($theWord)."\";\n");
		fputs($hax, "\$systemUser = ".(int)$systemUser.";\n");
		fputs($hax, "\n");
		fputs($hax, "//Hacks\n");
		fputs($hax, "\$hacks['forcetheme'] = ".(int)$hacks['forcetheme'].";\n");
		fputs($hax, "\$hacks['themenames'] = ".(int)$hacks['themenames'].";\n");
		fputs($hax, "\$hacks['postfilter'] = ".(int)$hacks['postfilter'].";\n");
		fputs($hax, "\n");
		fputs($hax, "//Profile Preview Post\n");
		fputs($hax, "\$profilePreviewText = \"".prepare($profilePreviewText, "\\\"")."\";\n");
		fputs($hax, "\n");
		fputs($hax, "//Meta\n");
		fputs($hax, "\$metaDescription = \"The owner of this ABXD board was too lazy to edit the description field. Kill him.\";\n");
		fputs($hax, "\$metaKeywords = \"abxd,acmlmboard\";\n");
		fputs($hax, "?>");
		fclose($hax);
	}
	else
	{
		print "not available.<br />";
	}

	include("lib/mysql.php");
	print "Creating tables&hellip;<br />";

	Import("installTables.sql");
	if($_POST['addbase'])
	{
		print "Creating starting fora&hellip;<br />";
		Import("installDefaults.sql");
	}

	print "<h3>Your board has been set up.</h3>";
	print "Things for you to do now:";
	print "<ul>";
	print "<li><a href=\"register.php\">Register your account</a> &mdash; the first to register gets to be administrator.</li>";
	print "<li>Check out the <a href=\"admin.php\">administrator's toolkit</a>.</li>";
	print "<li><a href=\"install.php?delete=1\">Delete</a> the installation script.</li>";
	print "</ul>";
	//print "The installation script, being a security hazard if left alone, has been removed and replaced by the actual board index.";

	print "</div>";
}

//SQL importer based on KusabaX installer
function Import($sqlFile)
{
	$handle = fopen($sqlFile, "r");
	$data = fread($handle, filesize($sqlFile));
	fclose($handle);

	$sqlData = explode("\n", $data);
	//Filter out the comments and empty lines...
	foreach ($sqlData as $key => $sql)
		if (strstr($sql, "--") || strlen($sql) == 0)
			unset($sqlData[$key]);
	$data = implode("",$sqlData);
	$sqlData = explode(";",$data);
	foreach($sqlData as $sql)
	{
		if(strlen($sql) === 0)
			continue;
		if(strstr($sql, "CREATE TABLE `"))
		{
			$pos1 = strpos($sql, '`');
			$pos2 = strpos($sql, '`', $pos1 + 1);
			$tableName = substr($sql, $pos1+1, ($pos2-$pos1)-1);
			print "<li>".$tableName."</li>";
		}
		$query = str_replace("SEMICOLON", ";", $sql);
		Query($query);
	}
}


function prepare($text, $quot = "&quot;")
{
	$s = str_replace("\"", $quot, deSlashMagic($text));
	return $s;
}
function deSlashMagic($text)
{
	if(get_magic_quotes_gpc())
		return stripslashes($text);
	else
		return $text;
}
?>
