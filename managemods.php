<?php
//  AcmlmBoard XD - Local moderator assignment tool
//  Access: administrators only

$noAutoHeader = TRUE;
include("lib/common.php");

$title = "Manage localmod assignments";

if($loguser['powerlevel'] < 3)
	Kill("You're not an admin.");

if(!isset($_GET['action']))
{
	include("lib/header.php");
	$qFora = "select * from forums order by catid, forder";
	$rFora = Query($qFora);
	while($forum = Fetch($rFora))
	{
		$modList = "";
		$qMods = "select * from forummods where forum=".$forum['id'];
		$rMods = Query($qMods);
		while($mods = Fetch($rMods))
		{
			$qMod = "select name, displayname, id, powerlevel, sex from users where id=".$mods['user'];
			$rMod = Query($qMod);
			$mod = Fetch($rMod);
			$modList .= format(
"
				<li>
					{0}
					<sup>
						[<a href=\"managemods.php?action=delete&amp;fid={1}&amp;mid={2}\">delete</a>]
					</sup>
				</li>
", UserLink($mod), $forum['id'], $mods['user']);
		}
		$theList .= format(
"
		<li>
			{0}
			<ul>
				{2}
				<li>
					<a href=\"managemods.php?action=add&amp;fid={1}\">Add</a>
				</li>
			</ul>
		</li>
", $forum['title'], $forum['id'], $modList);
	}
	write(
"
	<div class=\"faq outline margin\">
		<h3>Moderators as of {0}</h3>
		<ul>
			{1}
		</ul>
	</div>
", gmdate("F jS Y"), $theList);
}
elseif($_GET['action'] == "delete")
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
	Redirect("Removed!","managemods.php","the mod manager");
}
elseif($_GET['action'] == "add")
{
	include("lib/header.php");
	if(!isset($_GET['fid']))
		Kill("Forum ID unspecified.");

	$fid = (int)$_GET['fid'];

	if(!isset($_GET['mid']))
	{
		$modList = "";
		$qMod = "select * from users where powerlevel=1 order by name asc";
		$rMod = Query($qMod);
		while($mod = Fetch($rMod))
		{
			$qCheck = "select user from forummods where forum=".$fid." and user=".$mod['id'];
			$rCheck = Query($qCheck);
			if(NumRows($rCheck))
				$add = "already there";
			else
				$add = format("<a href=\"managemods.php?action=add&amp;fid={0}&amp;mid={1}\">add</a>", $fid, $mod['id']);
			$modList .= format(
"
<li>
{0}
<sup>[{1}]</sup>
</li>
", UserLink($mod), $add);
		}
		write(
"
		<div class=\"faq outline margin\">
			Pick a mod, any mod.
			<ul>
				{0}
			</ul>
		</div>
",	$modList);
	}
	else
	{
		$mid = (int)$_GET['mid'];
		$qMod = "insert into forummods (forum	, user) values (".$fid.", ".$mid.")";
		$rMod = Query($qMod);
		Redirect("Added!","managemods.php","the mod manager");
	}
}

?>
