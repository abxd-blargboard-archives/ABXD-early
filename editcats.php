<?php
//  AcmlmBoard XD - Forum category editing tool
//  Access: administrators

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the category list.");

elseif($_POST['action'] == "Add")
{
	$qCategory = "insert into categories (name, minpower) values ('".justEscape($_POST['name'])."', ".(int)$_POST['minpower'].")";
	$rCategory = Query($qCategory);
	Alert("Category added.","Notice");
} elseif($_POST['action'] == "Remove")
{
	$qCategory = "select * from categories where id=".(int)$_POST['cid'];
	$rCategory = Query($qCategory);
	$category = Fetch($rCategory);
	print "<div class=\"errort\"><strong>Confirm deletion of \"".$category['name']."\"</strong></div>";
	print "<div class=\"errorc cell2\">";
	print "<form action=\"editcats.php\" method=\"post\">";
	print "<input type=\"submit\" name=\"action\" value=\"Yes, do as I say.\"></input>";
	print "<input type=\"hidden\" name=\"cid\" value=\"".(int)$category['id']."\"></input>";
	print "</form>";
	print "</div>";
} elseif($_POST['action'] == "Yes, do as I say.")
{
	$qCategory = "delete from categories where id=".(int)$_POST['cid'];
	$rCategory = Query($qCategory);
	Alert("Category removed.","Notice");
} elseif($_POST['action'] == "Edit")
{
	$qCategory = "update categories set name='".justEscape($_POST['name'])."', minpower=".(int)$_POST['minpower']." where id=".(int)$_POST['cid']." limit 1";
	$rCategory = Query($qCategory);
	Alert("Category edited.","Notice");
}

$levels = array(-1 => "-1 - Banned", 0 => "0 - Normal user", 1 => "1 - Local Mod", 2 => "2 - Full Mod", 3 => "3 - Admin");

print "<div class=\"outline margin\">";
print "<div class=\"errort\"><strong>Category list</strong></div>";

$qCategories = "select * from categories";
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	while($category = Fetch($rCategories))
	{
		print "<div class=\"errorc left\" style=\"clear: both; overflow: auto;\">";
		print "<form action=\"editcats.php\" method=\"post\">";
		print "<input type=\"text\" name=\"name\" size=\"70\" value=\"".htmlval($category['name'])."\"></input> ";
		print MakeSelect("minpower",$category['minpower'],$levels)." ";
		print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
		print "<input type=\"submit\" name=\"action\" value=\"Remove\"></input>";
		print "<input type=\"hidden\" name=\"cid\" value=\"".$category['id']."\"></input>";
		print "</form>";
		print "</div>";
	}
}
print "</div>";

print "<form action=\"editcats.php\" method=\"post\">";
print "<div class=\"outline margin\">";
print "<div class=\"errort\"><strong>Add a Category</strong></div>";
print "<div class=\"errorc left\" style=\"clear: both; overflow: auto;\">";
print "<input type=\"text\" name=\"name\" size=\"70\"></input></td>";
print MakeSelect("minpower",0,$levels)." ";
print "<input type=\"submit\" name=\"action\" value=\"Add\"></input>";
print "</div>";
print "</div>";
print "</form>";
print "<div>For more complex things, try PMA. This is just a toy-like quick access.</div>";

doFooter();

function MakeSelect($fieldName, $checkedIndex, $choicesList)
{
	global $id;
	$checks[$checkedIndex] = " selected=\"true\"";
	$result = "<select name=\"".$fieldName."\" size=\"1\"".$kawa.">";
	foreach($choicesList as $key=>$val)
		$result .= "<option value=\"".$key."\"".$checks[$key].">".$val."</option>";
	$result .= "</select>";
	return $result;
}

?>
