<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <title>
      AcmlmBoard XD - Installation
    </title>
    <meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
    <link rel="stylesheet" type="text/css" href="css/default.css" />
  </head>
  <body>
    <div class="outline margin width100">
      <table>
        <tr>
          <td colspan="3" class="cell0">
            <!-- Board header goes here -->
            <table>
              <tr>
                <td style="border: 0px none;">
                  <img src="img/themes/default/logo.png" alt="" />
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
<?php
include("lib/snippets.php");
$timeStart = usectime();
include("lib/feedback.php");

if(isset($_GET['delete']))
{
	unlink("install.php") or Kill("Could not delete installation script.");
	Redirect("Installation file removed.","./","the main page");
}

if(!isset($_POST['action']))
{
	print "<form action=\"install.php\" method=\"post\">";
	print "<table class=\"outline margin width50\">";
	print "<tr class=\"header0\">";
	print "<th colspan=\"2\">Installation options</th>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dbs\">Database server</label></td>";
	print "<td class=\"cell0\"><input type=\"text\" id=\"dbs\" name=\"dbserv\" style=\"width: 98%;\" maxlength=\"25\" value=\"localhost\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dbn\">Database name</label></td>";
	print "<td class=\"cell0\"><input type=\"text\" id=\"dbn\" name=\"dbname\" style=\"width: 98%;\" maxlength=\"25\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dun\">Database user name</label></td>";
	print "<td class=\"cell1\"><input type=\"text\" id=\"dun\" name=\"dbuser\" style=\"width: 98%;\" maxlength=\"13\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dpw\">Database user password</label></td>";
	print "<td class=\"cell1\"><input type=\"password\" id=\"dpw\" name=\"dbpass\" style=\"width: 98%;\" maxlength=\"13\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\">Options</td>";
	print "<td class=\"cell1\"><input type=\"checkbox\" id=\"b\" name=\"addbase\"></input><label for=\"b\">Add starting fora</label></td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td><input type=\"submit\" name=\"action\" value=\"Install\"></input></td>";
	print "</tr>";
	print "</table>";
	print "</form>";
} else if($_POST['action'] == "Install")
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

DoFooter();

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

?>
