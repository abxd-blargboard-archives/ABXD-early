<?php
//  AcmlmBoard XD - Smiley editing tool
//  Access: administrators only

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the smiley table.");

if($_POST['action'] == "Apply")
{
	$qSmilies = "select * from smilies";
	$rSmilies = Query($qSmilies);
	$numSmilies = NumRows($rSmilies);

	for($i = 0; $i <= $numSmilies; $i++)
	{
		if($_POST['code_'.$i] != $_POST['oldcode_'.$i] || $_POST['image_'.$i] != $_POST['oldimage_'.$i])
		{
			if($_POST['code_'.$i] == "")
			{
				$act = "deleted";
				$qSmiley = "delete from smilies where code='".$_POST['oldcode_'.$i]."'";
			} else
			{
				$act = "edited to \"".$_POST['image_'.$i]."\"";
				$qSmiley = "update smilies set code='".$_POST['code_'.$i]."', image='".$_POST['image_'.$i]."' where code='".$_POST['oldcode_'.$i]."'";
			}
			$rSmiley = Query($qSmiley);
			$log .= "Smiley \"".$_POST['oldcode_'.$i]."\" ".$act.".<br />";
		}
	}

	if($_POST['code_add'] && $_POST['image_add'])
	{
		$qSmiley = "insert into smilies (code,image) value ('".$_POST['code_add']."', '".$_POST['image_add']."')";
		$rSmiley = Query($qSmiley);
		$log .= "Smiley \"".$_POST['code_add']."\" added.<br />";
	}
	if($log)
		Alert($log,"Log");
}

print "<div class=\"border faq\">";
print "To add, fill in both bottom fields and apply.<br />";
print "To edit, change either code or image fields to <em>not</em> match their hidden counterparts.";
print "</div>";

print "<form method=\"post\" action=\"editsmilies.php\">";

print "<div class=\"border\" style=\"width: 30%;\">";
print "<table>";
print "<tr class=\"header1\">";
print "<th>Code</th><th>Image</th>";
print "</tr>";

$qSmilies = "select * from smilies";
$rSmilies = Query($qSmilies);
while($smiley = Fetch($rSmilies))
{
	$cellClass = ($cellClass+1) % 2;
	$i++;

	print "<tr class=\"cell".$cellClass."\">";
	print "<td><input type=\"text\" name=\"code_".$i."\" value=\"".htmlentities2($smiley['code'])."\"></input><input type=\"hidden\" name=\"oldcode_".$i."\" value=\"".htmlentities2($smiley['code'])."\"></input></td>";
	print "<td><input type=\"text\" name=\"image_".$i."\" value=\"".htmlentities2($smiley['image'])."\"></input><input type=\"hidden\" name=\"oldimage_".$i."\" value=\"".htmlentities2($smiley['image'])."\"></input> <img src=\"img/smilies/".$smiley['image']."\" alt=\"".$smiley['code']."\"></td>";
	print "</tr>";
}

$cellClass = ($cellClass+1) % 2;
print "<tr class=\"header0\"><th colspan=\"2\">Add</th></tr>";
print "<tr class=\"cell".$cellClass."\">";
print "<td><input type=\"text\" name=\"code_add\"></input></td>";
print "<td><input type=\"text\" name=\"image_add\"></input></td>";
print "</tr>";

print "<tr class=\"cell2\">";
print "<td>&nbsp;</td>";
print "<td><input type=\"submit\" name=\"action\" value=\"Apply\"></td>";
print "</tr>";

print "</table>";
print "</div>";
print "</form>";

doFooter();

?>
