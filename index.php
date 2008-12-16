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
    <div class="border width100">
      <table>
        <tr>
          <td colspan="3" class="cell0">
            <!-- Board header goes here -->
            <table>
              <tr>
                <td style="border: 0px none;">
                  <img src="img/themes/default/logo.png" alt="board?" title="Board2? This is KAWAAA!" />
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

if(!isset($_POST['action']))
{
	print "<form action=\"install.php\" method=\"post\">";
	print "<div class=\"border width25\">";
	print "<table>";
	print "<tr class=\"header0\">";
	print "<th colspan=\"2\">Installation options</th>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dbn\">Database name</label></td>";
	print "<td class=\"cell0\"><input type=\"text\" id=\"dn\" name=\"dbname\" size=\"25\" maxlength=\"25\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dun\">Database user name</label></td>";
	print "<td class=\"cell1\"><input type=\"text\" id=\"dun\" name=\"dbuser\" size=\"13\" maxlength=\"13\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"dpw\">Database user password</label></td>";
	print "<td class=\"cell1\"><input type=\"password\" id=\"dpw\" name=\"dbpass\" size=\"13\" maxlength=\"13\"></input></td>";
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
	print "</div>";
	print "</form>";
} else if($_POST['action'] == "Install")
{
	print "<div class=\"border faq\">";
	print "Trying to connect to database...<br />";
	$dbuser = $_POST['dbuser'];
	$dbpass = $_POST['dbpass'];
	$dbname = $_POST['dbname'];
	mysql_connect("localhost", $dbuser, $dbpass) or Kill("Could not connect to database.");
	mysql_select_db($dbname) or Kill("Could not select database.");
	print "Writing database configuration file...<br />";
	$dbcfg = fopen("lib/database.php", "w+") or Kill("Could not open \"lib/database.php\" for writing.");
	fwrite($dbcfg, "<?php\n");
	fwrite($dbcfg, "//  AcmlmBoard XD support - Database settings\n\n");
	fwrite($dbcfg, "\$dbuser = \"".$dbuser."\";\n");
	fwrite($dbcfg, "\$dbpass = \"".$dbpass."\";\n");
	fwrite($dbcfg, "\$dbname = \"".$dbname."\";\n");
	fwrite($dbcfg, "\n?>");
	fclose($dbcfg);
	include("lib/mysql.php");
	print "Creating tables...<br />";

	CreateTable("blockedlayouts",
				"user smallint(5) unsigned not null default '0',".
				"blockee smallint(5) unsigned not null default '0',".
				"key user (user)");
	CreateTable("categories",
				"id smallint(5) unsigned not null auto_increment,".
				"name varchar(255) not null default '',".
				"minpower tinyint(4) default '0',".
				"primary key (id)");
	CreateTable("forummods",
				"forum smallint(5) not null default '0',".
				"user mediumint(8) not null default '0'");
	CreateTable("forums",
				"id smallint(5) unsigned not null auto_increment,".
				"title varchar(250) default NULL,".
				"description text,".
				"catid smallint(5) unsigned not null default '0',".
				"minpower tinyint(2) not null default '0',".
				"minpowerthread tinyint(2) not null default '0',".
				"minpowerreply tinyint(2) not null default '0',".
				"numthreads mediumint(8) unsigned not null default '0',".
				"numposts mediumint(8) unsigned not null default '0',".
				"lastpostdate int(11) not null default '0',".
				"lastpostuser int(11) unsigned not null default '0',".
				"forder smallint(5) not null default '0',".
				"primary key (id),".
				"key catid (catid)");
	CreateTable("guests",
				"id mediumint(8) unsigned not null auto_increment,".
				"ip varchar(32) not null default '',".
				"date int(11) not null default '0',".
				"lasturl varchar(100) not null default '',".
				"lastforum tinyint(3) unsigned not null default '0',".
				"useragent varchar(100) not null default '',".
				"bot tinyint(4) not null default '0',".
				"primary key (id)");
	CreateTable("ipbans",
				"ip varchar(15) not null default '',".
				"reason varchar(100) not null default '',".
				"date int(10) unsigned not null default '0',".
				"unique key ip (ip)");
	CreateTable("misc",
				"views int(11) unsigned not null default '0',".
				"hotcount smallint(5) unsigned default '30',".
				"porabox text not null");
	CreateTable("pmsgs",
				"id mediumint(8) unsigned not null auto_increment,".
				"userto smallint(5) unsigned not null default '0',".
				"userfrom smallint(5) unsigned not null default '0',".
				"date int(10) unsigned not null default '0',".
				"ip char(15) not null default '',".
				"msgread tinyint(3) unsigned not null default '0',".
				"primary key (id),".
				"key userto (userto),".
				"key userfrom (userfrom),".
				"key msgread (msgread)");
	CreateTable("pmsgs_text",
				"pid mediumint(8) unsigned not null default '0',".
				"title varchar(255) not null default '',".
				"text mediumtext not null,".
				"primary key (pid)");
	CreateTable("poll",
				"id int(11) not null auto_increment,".
				"question varchar(255) not null default '',".
				"briefing text not null,".
				"closed tinyint(1) not null default '0',".
				"doublevote tinyint(1) not null default '0',".
				"primary key (id)");
	CreateTable("poll_choices",
				"id int(11) not null auto_increment,".
				"poll int(11) not null default '0',".
				"choice varchar(255) not null default '',".
				"color varchar(25) not null default '',".
				"primary key (id)");
	CreateTable("pollvotes",
				"poll int(11) not null default '0',".
				"choice int(11) not null default '0',".
				"user int(11) not null default '0',".
				"unique key choice (choice,user)");
	CreateTable("posts",
				"id mediumint(8) not null auto_increment,".
				"thread int(10) unsigned not null default '0',".
				"user smallint(5) unsigned not null default '0',".
				"date int(10) unsigned not null default '0',".
				"ip char(15) not null default '0.0.0.0',".
				"num mediumint(8) not null default '0',".
				"deleted tinyint(1) not null default '0',".
				"options tinyint(4) not null default '0',".
				"primary key (id),".
				"key thread (thread),".
				"key date (date),".
				"key user (user),".
				"key ip (ip)");
	CreateTable("posts_text",
				"pid mediumint(8) unsigned not null default '0',".
				"headtext text,".
				"text mediumtext,".
				"signtext text,".
				"revision int(11) not null default '0'");
	CreateTable("ranks",
				"rset tinyint(3) unsigned not null default '1',".
				"num mediumint(8) not null default '0',".
				"text varchar(255) not null default '',".
				"key count (num)");
	CreateTable("ranksets",
				"id tinyint(3) unsigned not null auto_increment,".
				"name varchar(50) not null default '',".
				"primary key (id)");
	CreateTable("smilies",
				"code char(8) not null default '',".
				"image char(16) not null default ''");
	CreateTable("threads",
				"id int(10) unsigned not null auto_increment,".
				"forum tinyint(3) unsigned not null default '0',".
				"user smallint(5) unsigned not null default '0',".
				"views smallint(5) unsigned not null default '0',".
				"closed tinyint(1) unsigned not null default '0',".
				"title varchar(100) not null default '',".
				"icon varchar(200) not null default '',".
				"replies smallint(5) unsigned not null default '0',".
				"lastpostdate int(10) not null default '0',".
				"lastposter smallint(5) unsigned not null default '0',".
				"sticky tinyint(1) unsigned not null default '0',".
				"poll smallint(5) unsigned not null default '0',".
				"locked tinyint(1) unsigned not null default '0',".
				"primary key (id),".
				"key forum (forum),".
				"key user (user),".
				"key sticky (sticky),".
				"key pollid (poll),".
				"key lastpostdate (lastpostdate)");
	CreateTable("threadsread",
				"id smallint(5) not null default '0',".
				"thread tinyint(10) not null default '0',".
				"date int(11) not null default '0'");
	CreateTable("users",
				"id smallint(5) unsigned not null auto_increment,".
				"posts mediumint(9) not null default '0',".
				"regdate int(11) not null default '0',".
				"name varchar(25) not null default '',".
				"password varchar(32) not null default '',".
				"minipic varchar(100) not null default '',".
				"picture varchar(100) not null default '',".
				"postbg varchar(250) not null default '',".
				"postheader text,".
				"signature text,".
				"bio text,".
				"powerlevel tinyint(2) not null default '0',".
				"sex tinyint(1) unsigned not null default '2',".
				"title varchar(255) not null default '',".
				"rankset tinyint(5) unsigned not null default '0',".
				"realname varchar(60) not null default '',".
				"location varchar(200) not null default '',".
				"birthday int(11) not null default '0',".
				"email varchar(60) not null default '',".
				"homepageurl varchar(80) not null default '',".
				"homepagename varchar(100) not null default '',".
				"lastposttime int(10) unsigned not null default '0',".
				"lastactivity int(10) unsigned not null default '0',".
				"lastip varchar(15) not null default '',".
				"lasturl varchar(100) not null default '',".
				"lastforum tinyint(3) unsigned not null default '0',".
				"postsperpage smallint(4) unsigned not null default '20',".
				"threadsperpage smallint(4) unsigned not null default '50',".
				"timezone float not null default '0',".
				"scheme tinyint(2) unsigned not null default '0',".
				"signsep tinyint(3) unsigned not null default '0',".
				"dateformat varchar(20) not null default 'm-d-y',".
				"timeformat varchar(20) not null default 'h:i a',".
				"blocklayouts tinyint(1) not null default '0',".
				"globalblock tinyint(1) not null default '0',".
				"primary key (id),".
				"key posts (posts),".
				"key name (name),".
				"key lastforum (lastforum),".
				"key lastposttime (lastposttime),".
				"key lastactivity (lastactivity)");
	CreateTable("uservotes",
				"uid int(11) not null,".
				"voter int(11) not null,".
				"up tinyint(1) not null");
	print "</ul>";

	print "Resetting misc counters...<br />";
	Query("insert into misc values (0, 30, '...')");

	print "Inserting smilies...<br />";
	$smilies = array(
		'-_-' => 'annoyed.gif',
		'~:o' => 'baby.gif',
		'o_O' => 'bigeyes.gif',
		':D' => 'biggrin.gif',
		'o_o' => 'blank.gif',
		';_;' => 'cry.gif',
		'^^;;;' => 'cute2.gif',
		'^_^;' => 'cute.gif',
		'^_^' => 'cute.gif',
		'@_@' => 'dizzy.gif',
		'O_O' => 'eek.gif',
		'>:)' => 'evil.gif',
		'Â¬_Â¬' => 'eyeshift.gif',
		':(' => 'frown.gif',
		'8-)' => 'glasses.gif',
		':O' => 'jawdrop.gif',
		':LOL:' => 'lol.gif',
		'>:' => 'mad.gif',
		':NO:' => 'no.png',
		'<_<' => 'shiftleft.gif',
		'>_>' => 'shiftright.gif',
		'x_x' => 'sick.gif',
		':)' => 'smile.gif',
		':P' => 'tongue.gif',
		':[' => 'vamp.gif',
		';)' => 'wink.gif',
		':S' => 'wobbly.gif',
		':YES:' => 'yes.png',
		'>_<' => 'yuck.gif',
		':awsum:' => 'awsum.gif',
	);
	foreach($smilies as $k => $s)
		Query("insert into smilies values ('".$k."', '".$s."')");

	if($_POST['addbase'])
	{
		print "Adding starting fora...<br />";
		Query("insert into categories values (1, 'Staff', 1)");
		Query("insert into categories values (2, 'General', 0)");
		Query("insert into categories values (3, 'Janitorial Services', 0)");
		Query("insert into forums (id, title, description, catid, minpower) values (1, 'Admin room', 'Staff discussion forum', 1, 1)");
		Query("insert into forums (id, title, description, catid) values (2, 'General chat', 'Talk about serious stuff', 2)");
		Query("insert into forums (id, title, description, catid) values (3, 'Off-Topic', 'Talk about other stuff', 2)");
		Query("insert into forums (id, title, description, catid, minpowerthread, minpowerreply) values (4, 'Trash', '[trash]Where deleted threads go', 3, 3, 3)");
	}

	unlink("index.php") or Kill("Could not delete installation script.");
	rename("_index.php", "index.php") or Kill("Could not install new index page.");

	print "<h3>Your board has been set up.</h3>";
	print "Things for you to do now:";
	print "<ul>";
	print "<li><a href=\"register.php\">Register your account</a> &mdash; the first to register gets to be administrator.</li>";
	print "<li>Check out the <a href=\"admin.php\">administrator's toolkit</a>.</li>";
	print "</ul>";
	print "The installation script, being a security hazard if left alone, has been removed and replaced by the actual board index.";

	print "</div>";
}

DoFooter();

function CreateTable($tn, $ts)
{
	print "<li>".$tn;
	Query("drop table if exists ".$tn);
	Query("create table if not exists ".$tn." ( ".$ts. ")");
	print " &mdash; OK</li>";
}

?>
