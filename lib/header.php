<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title><?php print $boardname . ($title ? " - ".$title : "") ?></title>
	<meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="css/common.css" />
	<link rel="stylesheet" type="text/css" href="css/<?php print $themeFile; ?>" />
	<link rel="alternate" type="application/rss+xml" title="RSS feed" href="rss2.php" />
	<script type="text/javascript" src="lib/tricks.js"></script>
</head>
<body>
	<div class="outline margin width100" id="header">
		<table>
			<tr>
				<td colspan="3" class="cell0">
					<!-- Board header goes here -->
					<table>
						<tr>
							<td style="border: 0px none;">
								<img src="<?php print $logopic; ?>" alt="<?php print $logoalt; ?>" title="<?php print $logotitle; ?>" style="padding: 8px;" />
							</td>
							<td style="border: 0px none;">
								<div class="PoRT">
									<div class="errort">
										<strong><?php print $misc['poratitle']; ?></strong>
									</div>
									<div class="errorc cell2 left">
										<?php print $misc['porabox']; ?>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="smallFonts cell1">
				<td rowspan="2" style="text-align: center; width: 10%;">
					Views: <?php print number_format($misc['views'])."\n"; ?>
				</td>
				<td style="text-align: center; width: 80%;">
					<ul class="pipemenu">
						<?php
							if($loguser['powerlevel'] == 3)
							print "<li><a href=\"admin.php\">Admin</a></li>";
						?>
						<li><a href="index.php">Main</a></li>
						<li><a href="faq.php">FAQ</a></li>
						<li><a href="memberlist.php">Member list</a></li>
						<li><a href="groups.php">Groups</a></li>
						<li><a href="ranks.php">Ranks</a></li>
						<li><a href="avatarlibrary.php">Avatars</a></li>
						<li><a href="online.php">Online users</a></li>
					</ul>
				</td>
				<td rowspan="2" style="text-align: center; width: 10%;">
					<?php print gmdate($dateformat)."\n"; ?>
				</td>
			</tr>
			<tr class="smallFonts cell2">
				<td style="text-align: center">
					<?php
						if($loguserid)
						{
							print UserLink($loguser).": \n";
					?>
					<ul class="pipemenu">
						<li><a href="login.php?action=logout">Log out</a></li>
						<li><a href="editprofile.php">Edit profile</a></li>
						<li><a href="private.php">Private messages</a></li>
						<li><a href="editavatars.php">Mood avatars</a></li>
						<?php
								if(!isset($_POST['id']) && isset($_GET['id']))
									$_POST['id'] = (int)$_GET['id'];
								if(strpos($_SERVER['SCRIPT_NAME'], "forum.php"))
									print "<li><a href=\"index.php?fid=".$_POST['id']."&amp;action=markasread\">Mark forum read</a></li>";
								elseif(strpos($_SERVER['SCRIPT_NAME'], "index.php"))
									print "<li><a href=\"index.php?action=markallread\">Mark all forums read</a></li>";
							}
							else
							{
						?>
					<ul class="pipemenu">
						<li><a href="register.php">Register</a></li>
						<li><a href="login.php">Log in</a></li>
						<?php
							}
						?>
					</ul>
				</td>
			</tr>
		</table>
	</div>
	<div id="body">
<?php
	$timeStart = usectime();

	//if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 6.") !== FALSE)
	if($isIE6)
	{
		print "<div id=\"getabetterbrowseryoulazyfuck\">";
		print "You seem to be using Microsoft Internet Explorer 6. That is a very old browser. <a href=\"http://www.browserchoice.eu\">Get updated</a>, enjoy a better Web.";
		print "</div>";
	}
?>
