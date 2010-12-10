<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>[[BOARD TITLE HERE]]</title>
	<meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta name="description" content="<?php print $metaDescription; ?>" />
	<meta name="keywords" content="<?php print $metaKeywords; ?>" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="css/common.css" />
	<link rel="stylesheet" type="text/css" href="css/<?php print $themeFile; ?>" id="theme_css" />
	<link rel="alternate" type="application/rss+xml" title="RSS feed" href="rss2.php" />
	<script type="text/javascript" src="lib/tricks.js"></script>
</head>
<body style="font-size: <?php print $loguser['fontsize']; ?>%;">
	<div class="outline margin width100" id="header">
		<table>
			<tr>
				<td colspan="3" class="cell0">
					<!-- Board header goes here -->
					<table>
						<tr>
							<?php if($misc['porabox']) { ?>
							<td style="border: 0px none;">
								<img src="<?php print $logopic; ?>" alt="<?php print $logoalt; ?>" title="<?php print $logotitle; ?>" id="theme_banner" style="padding: 8px;" />
							</td>
							<td style="border: 0px none;">
								<div class="PoRT nom">
									<div class="errort">
										<strong><?php print $misc['poratitle']; ?></strong>
									</div>
									<div class="errorc cell2 left">
										<?php print $misc['porabox']; ?>
									</div>
								</div>
							</td>
							<?php } else { ?>
							<td style="border: 0px none; text-align: center;">
								<img src="<?php print $logopic; ?>" alt="<?php print $logoalt; ?>" title="<?php print $logotitle; ?>" id="theme_banner" style="padding: 8px;" />
							</td>
							<?php } ?>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="cell1">
				<td rowspan="2" class="smallFonts" style="text-align: center; width: 10%;">
					Views: <?php print number_format($misc['views'])."\n"; ?>
				</td>
				<td class="smallFonts" style="text-align: center; width: 80%;">
					<ul class="pipemenu">
						<?php
							if($loguser['powerlevel'] > 2 && IsAllowed("viewAdminRoom"))
								print "<li><a href=\"admin.php\">Admin</a></li>";
							print "<li><a href=\"index.php\">Main</a></li>";
							print "<li><a href=\"faq.php\">FAQ</a></li>";
							if(IsAllowed("viewUploader"))
								print "<li><a href=\"uploader.php\">Uploader</a></li>";
							if(IsAllowed("viewMembers"))
								print "<li><a href=\"memberlist.php\">Member list</a></li>";
							print "<li><a href=\"groups.php\">Groups</a></li>";
							if(IsAllowed("viewRanks"))
								print "<li><a href=\"ranks.php\">Ranks</a></li>";
							if(IsAllowed("viewCalendar"))
								print "<li><a href=\"calendar.php\">Calendar</a></li>";
							if(IsAllowed("viewAvatars"))
								print "<li><a href=\"avatarlibrary.php\">Avatars</a></li>";
							if(IsAllowed("viewOnline"))
								print "<li><a href=\"online.php\">Online users</a></li>";
							if(IsAllowed("search"))
								print "<li><a href=\"search.php\">Search</a></li>";

							runBucket("headers", "top");
						?>
					</ul>
				</td>
				<td rowspan="2" class="smallFonts" style="text-align: center; width: 10%;">
					<?php print cdate($dateformat)."\n"; ?>
				</td>
			</tr>
			<tr class="cell2">
				<td class="smallFonts" style="text-align: center">
					<?php
						if($loguserid)
						{
							print UserLink($loguser).": ";
							print "<ul class=\"pipemenu\">";
							print "<li><a href=\"#\" onclick=\"if(confirm('Are you sure you want to log out?')) document.forms[0].submit();\">Log out</a></li>";

							if(IsAllowed("editProfile"))
								print "<li><a href=\"editprofile.php\">Edit profile</a></li>";
							if(IsAllowed("viewPM"))
								print "<li><a href=\"private.php\">Private messages</a></li>";
							if(IsAllowed("editMoods"))
								print "<li><a href=\"editavatars.php\">Mood avatars</a></li>";

							runBucket("headers", "bottom");

							if(!isset($_POST['id']) && isset($_GET['id']))
								$_POST['id'] = (int)$_GET['id'];

							if(strpos($_SERVER['SCRIPT_NAME'], "forum.php"))
								print "<li><a href=\"index.php?fid=".$_POST['id']."&amp;action=markasread\">Mark forum read</a></li>";
							elseif(strpos($_SERVER['SCRIPT_NAME'], "index.php"))
								print "<li><a href=\"index.php?action=markallread\">Mark all forums read</a></li>";
							
						}
						else
						{
							print "<ul class=\"pipemenu\">";
							print "<li><a href=\"register.php\">Register</a></li>";
							print "<li><a href=\"login.php\">Log in</a></li>";
							}
						?>
					</ul>
				</td>
			</tr>
		</table>
	</div>
	<form action="login.php" method="post" id="logout">
		<div style="display: none;">
			<input type="hidden" name="action" value="logout" />
		</div>
	</form>
	<div id="body">
<?php
	$timeStart = usectime();

	if($isIE6)
	{
		print "<div id=\"getabetterbrowseryoulazyfuck\">";
		print "You seem to be using Microsoft Internet Explorer 6. That is a very old browser. <a href=\"http://www.browserchoice.eu\">Get updated</a>, enjoy a better Web.";
		print "</div>";
	}
?>
