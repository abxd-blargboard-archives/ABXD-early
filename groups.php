<?php
include("lib/common.php");

$title = "User groups";

$groups = array();
$rGroups = Query("select * from groups order by id asc");
while($group = Fetch($rGroups))
	$groups[$group['id']] = array('id' => $group['id'], 'name' => $group['name'], 'leader' => $group['leader'], 'members' => array() );

$rAffils = Query("select name, gid, uid, powerlevel, sex, status from groupaffiliations left join users on users.id = uid order by gid asc");
while($affil = Fetch($rAffils))
{
	$gid = $affil['gid'];
	$groups[$gid]['members'][$affil['uid']] = array('id' => $affil['uid'], 'name' => $affil['name'], 'powerlevel' => $affil['powerlevel'], 'sex' => $affil['sex'], 'status' => $affil['status']);
}

//print_r($groups);

if($_GET['action'] == "pass")
{
	$gid = (int)$_GET['gid'];
	if($gid == 0 || !array_key_exists($gid, $groups))
		Alert("Invalid group.");
	else
	{
		$uid = (int)$_GET['uid'];
		if($uid == 0)
			Alert("Invalid user.");
		else
		{
			$group = $groups[$gid];
			if($group['leader'] == $loguserid || $loguser['powerlevel'] >=3)
			{
				Query("update groups set leader=".$uid." where id=".$gid);
				$groups[$gid]['leader'] = $uid;
			}
		}
	}
}
else if($_GET['action'] == "kick" || $_GET['action'] == "deny")
{
	$gid = (int)$_GET['gid'];
	if($gid == 0 || !array_key_exists($gid, $groups))
		Alert("Invalid group.");
	else
	{
		$uid = (int)$_GET['uid'];
		//if($uid == 0)
		//	Alert("Invalid user.");
		//else
		{
			$group = $groups[$gid];
			if($group['leader'] == $loguserid || $loguser['powerlevel'] >=3)
			{
				Query("delete from groupaffiliations where uid=".$uid." and gid=".$gid);
				$groups[$gid]['members'][$uid]['status'] = 3;
			}
		}
	}
}
else if($_GET['action'] == "ban")
{
	$gid = (int)$_GET['gid'];
	if($gid == 0 || !array_key_exists($gid, $groups))
		Alert("Invalid group.");
	else
	{
		$uid = (int)$_GET['uid'];
		if($uid == 0)
			Alert("Invalid user.");
		else
		{
			$group = $groups[$gid];
			if($group['leader'] == $loguserid || $loguser['powerlevel'] >=3)
			{
				Query("update groupaffiliations set status=3 where uid=".$uid." and gid=".$gid);
				$groups[$gid]['members'][$uid]['status'] = 3;
			}
		}
	}
}
else if($_GET['action'] == "join")
{
	$gid = (int)$_GET['gid'];
	if($gid == 0 || !array_key_exists($gid, $groups))
		Alert("Invalid group.");
	else
	{
		$uid = (int)$_GET['uid'];
		if($uid == 0)
			Alert("Invalid user.");
		else
		{
			$group = $groups[$gid];
			if($group['leader'] == $loguserid || $loguser['powerlevel'] >=3)
			{
				Query("update groupaffiliations set status=0 where uid=".$uid." and gid=".$gid);
				$groups[$gid]['members'][$uid]['status'] = 0;
			}
		}
	}
}
else if($_GET['action'] == "request" && $loguserid)
{
	$gid = (int)$_GET['gid'];
	if($gid == 0 || !array_key_exists($gid, $groups))
		Alert("Invalid group.");
	else
	{
		$group = $groups[$gid];
		if(!array_key_exists($loguserid, $group['members']))
		{
			Query("insert into groupaffiliations (gid,uid,status) values (".$gid.",".$loguserid.",1)");
			$groups[$gid]['members'][$loguserid] = $loguser;
			$groups[$gid]['members'][$loguserid]['status'] = 1;
		}
	}
}
else if($_POST['action'] == "Create" && $loguser['powerlevel'] > 2)
{
	Query("insert into groups (name) values ('".justEscape($_POST['name'])."')");
	$gid = mysql_insert_id();
	$groups[$gid] = array('id' => $gid, 'name' => deSlashMagic($_POST['name']), 'leader' => 0, 'members' => array() );
}
else if($_GET['action'] == "delete" && $loguser['powerlevel'] > 2)
{
	$gid = (int)$_GET['gid'];
	if($gid == 0 || !array_key_exists($gid, $groups))
		Notice("Invalid group.");
	else
	{
		Query("delete from groups where id=".$gid);
		Query("delete from groupaffiliations where gid=".$gid);
		$groups[$gid] = 0;
	}
}

print "<table class=\"outline margin width100\">";
print "<tr class=\"header1\"><th>Name</th><th>Members</th></tr>";
foreach($groups as $group)
{
	if($group == 0)
		continue;
	$members = "";
	$waiters = "";
	$leader = ($group['leader'] == $loguserid || $loguser['powerlevel'] >=3);
	foreach($group['members'] as $member)
	{
		$links = " ";
		if($member['status'] == 0)
		{
			if($leader && $member['id'] != $group['leader'])
			{
				$links = "<sup>";
				$links .= "[<a href=\"groups.php?action=pass&amp;gid=".$group['id']."&amp;uid=".$member['id']."\" title=\"Pass leadership\">p</a>]";
				$links .= "[<a href=\"groups.php?action=kick&amp;gid=".$group['id']."&amp;uid=".$member['id']."\" title=\"Kick out\">k</a>]";
				$links .= "[<a href=\"groups.php?action=ban&amp;gid=".$group['id']."&amp;uid=".$member['id']."\" title=\"Ban\" onclick=\"if(!confirm('Are you sure you want to just up and ban ".$member['name']."? This is quite permanent.') || !confirm('Seriously?')) return false;\">b</a>]";
				$links .= "</sup> ";
			}
			$members .= UserLink($member).$links;
		}
		else if($member['status'] == 1)
		{
			if($leader)
			{
				$links = "<sup>";
				$links .= "[<a href=\"groups.php?action=join&amp;gid=".$group['id']."&amp;uid=".$member['id']."\" title=\"Join group\">j</a>]";
				$links .= "[<a href=\"groups.php?action=deny&amp;gid=".$group['id']."&amp;uid=".$member['id']."\" title=\"Deny membership\">d</a>]";
				$links .= "</sup> ";
			}
			$waiters .= UserLink($member).$links;
			continue;
		}
		else if($member['status'] == 3)
			continue;
	}
	
	if($waiters)
		$waiters = "<br />Waiting: ".$waiters;
	
	$join = "";
	if(!array_key_exists($loguserid, $group['members']) && $loguserid)
		$join = "<br /><small><a href=\"groups.php?action=request&amp;gid=".$group['id']."\">Request join</a></small>";
	
	if($loguser['powerlevel'] > 2)
		$delete = "<sup>[<a href=\"groups.php?action=delete&amp;gid=".$group['id']."\" title=\"Delete group\" onclick=\"if(!confirm('Are you sure you want to remove the user group ".$group['name']."?') || !confirm('Seriously?')) return false;\">d</a>]</sup>";
	
	print "<tr>";
	print "<td class=\"cell2\">".$group['name'].$delete."</td>";
	print "<td class=\"cell1\">".$members.$waiters.$join."</td>";
	print "</tr>";
}
print "</table>";

if($loguser['powerlevel'] > 2)
{
	print "<form action=\"groups.php\" method=\"post\" id=\"myForm\">";
	print "<table class=\"outline margin width25\">";
	print "<tr class=\"header1\">";
	print "<th colspan=\"2\">Create group</th>";
	print "</tr>";
	print "<tr class=\"cell0\">";
	print "<td>Name</td>";
	print "<td class=\"cell0\"><input type=\"text\" name=\"name\" style=\"width: 98%;\" maxlength=\"64\"></input></td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td><input type=\"submit\" name=\"action\" value=\"Create\"></input></td>";
	print "</tr>";
	print "</table>";
	print "</form>";
}

doFooter();
?>