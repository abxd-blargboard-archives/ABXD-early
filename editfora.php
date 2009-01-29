<?php
//  AcmlmBoard XD - Forum list editing tool
//  Access: administrators

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the forum list.");

if($_POST['action'] == "Move")
{
	$qForum = "update forums set forder=".(int)$_POST['order'].", catid=".$_POST['category']." where id=".$_POST['fid']." limit 1";
	$rForum = Query($qForum);
	Alert("Forum moved.","Notice");
} elseif($_POST['action'] == "Add")
{
	$qForum = "insert into forums (title, description, catid, forder) values ('".$_POST['title']."', '".$_POST['description']."', ".$_POST['category'].", ".$_POST['order'].")";
	$rForum = Query($qForum);
	Alert("Forum added.","Notice");
} elseif($_POST['action'] == "Remove")
{
	$qForum = "select * from forums where id=".$_POST['fid'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	print "<div class=\"errort\"><strong>Confirm deletion of \"".$forum['title']."\"</strong></div>";
	print "<div class=\"errorc cell2\">";
	print "<form action=\"editfora.php\" method=\"post\">";
	print "<input type=\"submit\" name=\"action\" value=\"Yes, do as I say.\"></input>";
	print "<input type=\"hidden\" name=\"fid\" value=\"".$forum['id']."\"></input>";
	print "</form>";
	print "</div>";
} elseif($_POST['action'] == "Yes, do as I say.")
{
	$qForum = "delete from forums where id=".$_POST['fid'];
	$rForum = Query($qForum);
	Alert("Forum removed.","Notice");
} elseif($_POST['action'] == "Edit")
{
	$qForum = "update forums set title='".$_POST['title']."', description='".$_POST['description']."' where id=".$_POST['fid']." limit 1";
	$rForum = Query($qForum);
	Alert("Forum edited.","Notice");
}

$qCategories = "select * from categories";
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	print "<div class=\"border\">";
	print "<table>";
	print "<tr class=\"header1\">";
	print "<th>Forum title</th>";
	print "<th>Stats</th>";
	print "<th>Order</th>";
	print "</tr>";
	while($category = Fetch($rCategories))
	{
		print "<tr class=\"header0\">";
		print "<th colspan=\"4\">".$category['name']."</th>";
		print "</tr>";
		$qFora = "select * from forums where catid=".$category['id']." order by forder";
		$rFora = Query($qFora);
		if(NumRows($rFora))
		{
			while($forum = Fetch($rFora))
			{
				$localMods = "<small>No mods";
				$qMods = "select * from forummods where forum=".$forum['id'];
				$rMods = Query($qMods);
				if(NumRows($rMods))
				{
					while($mod = Fetch($rMods))
					{
						$qMod = "select name, id, powerlevel, sex from users where id=".$mod['user'];
						$rMod = Query($qMod);
						$mod2 = Fetch($rMod);
						$localMods .= UserLink($mod2).", ";
					}
					$localMods = "<small>Moderated by: ".substr($localMods,0,strlen($localMods)-2);
				}

				print "<tr class=\"cell1\">";
				print "<td>";
				print "<form action=\"editfora.php\" method=\"post\">";
				print "<input type=\"text\" name=\"title\" size=\"40\" value=\"".htmlentities2($forum['title'])."\"></input><br />";
				print "<input type=\"text\" name=\"description\" size=\"80\" value=\"".htmlentities2($forum['description'])."\"></input>";
				print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
				print "<input type=\"hidden\" name=\"fid\" value=\"".$forum['id']."\"></input>";
				print "</form>";
				print $localMods. "<small> (<a href=\"managemods.php\">Edit</a>)</small>";
				print "</td>";
				print "<td>";
				print "".$forum['numthreads']." thread(s), ".$forum['numposts']." post(s).";
				print "</td>";
				print "<td>";
				print "";
				print "<form action=\"editfora.php\" method=\"post\">";
				print MakeOptions($forum['catid']);
				print " <input type=\"text\" name=\"order\" value=\"".$forum['forder']."\" size=\"2\" maxlength=\"2\"></input> ";
				print "<input type=\"submit\" name=\"action\" value=\"Move\"></input> ";
				print "<input type=\"submit\" name=\"action\" value=\"Remove\"></input>";
				print "<input type=\"hidden\" name=\"fid\" value=\"".$forum['id']."\"></input>";
				print "</form>";
				print "</td>";
				print "</tr>";
			}
		}
	}
	print "<form action=\"editfora.php\" method=\"post\">";
	print "<tr class=\"header0\">";
	print "<th colspan=\"4\">Add a Forum</th>";
	print "</tr>";
	print "<tr class=\"cell1\">";
	print "<td colspan=\"2\"><input type=\"text\" name=\"title\" size=\"40\"></input><br/>";
	print "<input type=\"text\" name=\"description\" size=\"80\"></input></td>";
	print "<td>";
	print MakeOptions(-1);
	print " <input type=\"text\" name=\"order\" value=\"0\" size=\"2\" maxlength=\"2\"></input> ";
	print $levels;
	print "<input type=\"submit\" name=\"action\" value=\"Add\"></input> ";
	print "</td>";
	print "</tr>";
	print "</form>";

	print "</table>";
	print "</div>";
}

print "<div>For more complex things, try PMA. This is just a toy-like quick access.</div>";

doFooter();

function MakeOptions($catid)
{
	$sel[$catid] = " selected=\"true\"";
	$qFora = "select id,name from categories";
	$rFora = Query($qFora);
	$result = "<select name=\"category\" size=\"1\">";
	while($forum = Fetch($rFora))
		$result .= "<option value=\"".$forum['id']."\"".$sel[$forum['id']]."\>".$forum['name']."</option>";
	$result .= "</select>";
	return $result;
}

?>
