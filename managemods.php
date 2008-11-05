<?php
//  AcmlmBoard XD - Local moderator assignment tool
//  Access: administrators only

$noAutoHeader = TRUE;
include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You're not an admin.");

if(!isset($_GET['action']))
{
	include("lib/header.php");
	print "<div class=\"faq border\">";
	print "<h3>Moderators as of ".date("F jS Y")."</h3>";
	print "<ul>";
	$qFora = "select * from forums order by catid, forder";
	$rFora = Query($qFora);
	while($forum = Fetch($rFora))
	{
		print "<li>";
		print $forum['title'];
		print "<ul>";
		$qMods = "select * from forummods where forum=".$forum['id'];
		$rMods = Query($qMods);
		while($mods = Fetch($rMods))
		{
			print "<li>";
			$qMod = "select name, id, powerlevel, sex from users where id=".$mods['user'];
			$rMod = Query($qMod);
			$mod = Fetch($rMod);
			print UserLink($mod);
			print " (<a href=\"managemods.php?action=delete&amp;fid=".$forum['id']."&amp;mid=".$mods['user']."\">Delete</a>)";
			print "</li>";
		}
		print "<li>";
		print "<a href=\"managemods.php?action=add&amp;fid=".$forum['id']."\">Add</a>";
		print "</li>";
		print "</ul>";
		print "</li>";
	}
	print "</ul>";
	print "</div>";
} elseif($_GET['action'] == "delete")
{
	include("lib/header.php");
	if(!isset($_GET['fid']))
		Kill("Forum ID unspecified.");
	if(!isset($_GET['mid']))
		Kill("Mod ID unspecified.");

	$fid = (int)$_GET['fid'];
	$mid = (int)$_GET['mid'];

	$qMod = "delete from forummods where forum=".$fid." and user=".$mid;
	$rMod = Query($qMod);
	Redirect("Removed!","managemods.php","the mod manager.");
} elseif($_GET['action'] == "add")
{
	include("lib/header.php");
	if(!isset($_GET['fid']))
		Kill("Forum ID unspecified.");

	$fid = (int)$_GET['fid'];

	if(!isset($_GET['mid']))
	{
		print "<div class=\"faq border\">";
		print "Pick a mod, any mod.";
		print "<ul>";
		$qMod = "select * from users where powerlevel=1 order by name asc";
		$rMod = Query($qMod);
		while($mod = Fetch($rMod))
		{
			$qCheck = "select * from forummods where forum=".$fid." and user=".$mod['id'];
			$rCheck = Query($qCheck);
			print "<li>";
			print UserLink($mod);
			if(NumRows($rCheck))
				print " (already there)";
			else
				print " (<a href=\"managemods.php?action=add&amp;fid=".$fid."&amp;mid=".$mod['id']."\">Add</a>)";
			print "</li>";
		}
		print "</ul>";
		print "</div>";
	} else
	{
		$mid = (int)$_GET['mid'];
		$qMod = "insert into forummods (forum	, user) values (".$fid.", ".$mid.")";
		$rMod = Query($qMod);
		Redirect("Added!","managemods.php","the mod manager.");
	}
} elseif($_GET['action']=="print")
{
	print "<h3>Moderators as of ".date("F jS Y")."</h3>";
	print "<ul>";
	$qFora = "select * from forums order by catid, forder";
	$rFora = Query($qFora);
	while($forum = Fetch($rFora))
	{
		print "<li>";
		print $forum['title']." (#".$forum['id'].")";
		print "<ul>";
		$qMods = "select * from forummods where forum=".$forum['id'];
		$rMods = Query($qMods);
		while($mods = Fetch($rMods))
		{
			print "<li>";
			$qMod = "select name, id from users where id=".$mods['user'];
			$rMod = Query($qMod);
			$mod = Fetch($rMod);
			print $mod['name']." (#".$mod['id'].")";
			print "</li>";
		}
		print "</ul>";
		print "</li>";
	}
	print "</ul>";
	print "</div>";
	exit();
}

doFooter();

?>
