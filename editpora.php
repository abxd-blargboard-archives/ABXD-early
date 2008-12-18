<?php
//  AcmlmBoard XD - Points of Required Attention editing page
//  Access: administrators

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the Points of Required Attention.");

if($_POST['action'] == "Edit")
{
	TidyPost($_POST['text']);
	$qPora = "update misc set porabox = '".$_POST['text']."'";
	$rPora = Query($qPora);
	Redirect("Edited!","./","the main page.");
}

print "<div class=\"border\" style=\"width: 48%; float: right;\">";
print "<table class=\"PoRT\" id=\"preview\">";
print "<tr class=\"header1\">";
print "<th>Demonstration of Required Attention Span</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\" id=\"previewtext\">";
print "</td>";
print "</tr>";
print "</table>";
print "</div>";

print "<form action=\"editpora.php\" method=\"post\">";
print "<div class=\"border width50\">";
print "<table id=\"t\">";
print "<tr class=\"cell0\">";
print "<td>Content (HTML)</td>";
print "<td style=\"width: 80%;\">";
print "<textarea name=\"text\" rows=\"16\" style=\"width: 97%;\" id=\"box\">".$misc['porabox']."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</div>";
print "</form>";

doFooter();

?>

<script type="text/javascript">
	var ta = document.getElementById("box");
	var pr = document.getElementById("preview");
	var prt = document.getElementById("previewtext");
	var tbl = document.getElementById("t");

	function update()
	{
		prt.innerHTML = ta.value;
		setTimeout("update();", 100);
	}

	pr.style.display = "";
	update();
</script>
