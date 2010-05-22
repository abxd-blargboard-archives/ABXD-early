<?php
//  AcmlmBoard XD - Points of Required Attention editing page
//  Access: administrators

include("lib/common.php");

$title = "Points of Required Attention";

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the Points of Required Attention.");

if($_POST['action'] == "Edit")
{
	//TidyPost($_POST['text']);
	$qPora = "update misc set porabox = '".justEscape($_POST['text'])."', poratitle = '".justEscape($_POST['title'])."'";
	$rPora = Query($qPora);
	Redirect("Edited!","./","the main page");
}

print "<div class=\"PoRT\"><div class=\"errort\"><strong id=\"previewtitle\">";
print htmlval($misc['poratitle']);
print "</strong></div><div class=\"errorc cell2 left\" id=\"previewtext\">";
print $misc['porabox'];
print "</div></div>";

/*
print "<table class=\"PoRT outline margin\" style=\"width: 48%; float: right;\" id=\"preview\">";
print "<tr class=\"header1\">";
print "<th id=\"previewtitle\">".htmlval($misc['poratitle'])."</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\" id=\"previewtext\">";
print "</td>";
print "</tr>";
print "</table>";
*/

print "<form action=\"editpora.php\" method=\"post\">";
print "<table id=\"t\" class=\"outline margin width50\">";
print "<tr class=\"cell0\">";
print "<td>Title (plain)</td>";
print "<td>";
print "<input name=\"title\" id=\"title\" maxlength=\"256\" style=\"width: 80%;\" value=\"".htmlval($misc['poratitle'])."\"></input>";
print "</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td>Content (HTML)</td>";
print "<td style=\"width: 80%;\">";
print "<textarea name=\"text\" rows=\"16\" style=\"width: 97%;\" id=\"editbox\">".htmlval($misc['porabox'])."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</form>";

?>

<script type="text/javascript">
	window.onload = function()
	{
		startPoraUpdate();
	}
</script>

<?php
doFooter();

?>

