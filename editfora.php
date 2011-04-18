<?php
//Category/forum editor -- By Nikolaj
$noAutoHeader = true;
include('lib/common.php');
$title = "Edit forums/categories";

if ($loguser['powerlevel'] < 3) Kill("You're not allowed to access the category editor.");

$noFooter = true;
if ($_GET['updateforum']) {
	$id = (int)$_GET['id'];
	$title = $_GET['title'];
	$description = $_GET['description'];
	$category = (int)$_GET['category'];
	$forder = (int)$_GET['forder'];
	$minpower = (int)$_GET['minpower'];
	$minpowerthread = (int)$_GET['minpowerthread'];
	$minpowerreply = (int)$_GET['minpowerreply'];
	$qForum = "UPDATE forums SET title = '".justEscape($title)."', description = '".justEscape($description)."', catid = ".$category.", forder = ".$forder.", minpower = ".$minpower.", minpowerthread = ".$minpowerthread.", minpowerreply = ".$minpowerreply." WHERE id = ".$id;
	Query($qForum);
	die("Change OK.");
}
else if ($_GET['editforum']) {
	$id = (int)$_GET['editforum'];
	$qForum = "SELECT * FROM forums WHERE id=".$id;
	$rForum = Query($qForum);
	if (!NumRows($rForum)) {
		Kill("Forum not found.");
	}
	$forum = Fetch($rForum);
	$qCats = "SELECT * FROM categories";
	$rCats = Query($qCats);
	if (!NumRows($rCats))
		$cSelect = "No categories...";
	else {
		$cats = array();
		while ($cat = Fetch($rCats))
			$cats[$cat['id']] = $cat;
	}
	
	Write('
	<table class="outline margin">
		<tr class="header1">
			<th colspan="2">
				Edit forum
			</th>
		</tr>
		<tr class="cell1">
			<td style="width: 25%;">
				Title
			</td>
			<td>
				<input type="text" style="width: 98%;" id="title" value="{0}" />
			</td>
		</tr>
		<tr class="cell1">
			<td>
				Description
			</td>
			<td>
				<input type="text" style="width: 98%;" id="description" value="{1}" />
			</td>
		</tr>
		<tr class="cell0">
			<td>
				Category
			</td>
			<td>
				{2}
			</td>
		</tr>
		<tr class="cell1">
			<td>
				Listing order
			</td>
			<td>
				<input type="text" size="2" id="forder" value="{7}" />
				<img src="img/icons/icon5.png" title="Forums are sorted by listing order first, then by ID. If all forums in a category have their listing order set to 0, they will therefore be sorted by ID only." alt="[?]" />
			</td>
		</tr>
		<tr class="cell0">
			<td>
				Powerlevel required
			</td>
			<td>
				{3}
				to view
				<br />
				{4}
				to post threads
				<br />
				{5}
				to reply
			</td>
		</tr>
		<tr class="cell2">
			<td>
				&nbsp;
			</td>
			<td>
				<button onclick="changeForumInfo({6})">
					Update
				</button>
				<button onclick="deleteForum({6})" {8}>
					Delete
				</button>
			</td>
		</tr>
	</table>', $forum['title'], $forum['description'], MakeCatSelect('cat', $cats, $forum['catid']), PowerSelect('minpower', $forum['minpower']), PowerSelect("minpowerthread", $forum['minpowerthread']), PowerSelect('minpowerreply', $forum['minpowerreply']), $forum['id'], $forum['forder'], $forum['numthreads'] > 0 ? 'disabled="disabled"' : '');
	
	die();
}
else if ($_GET['addforum'] == 2) {
	$title = $_GET['title'];
	$description = $_GET['description'];
	$category = (int)$_GET['category'];
	$forder = (int)$_GET['forder'];
	$minpower = (int)$_GET['minpower'];
	$minpowerthread = (int)$_GET['minpowerthread'];
	$minpowerreply = (int)$_GET['minpowerreply'];

	$newID = FetchResult("SELECT id+1 FROM forums WHERE (SELECT COUNT(*) FROM forums f2 WHERE f2.id=forums.id+1)=0 ORDER BY id ASC LIMIT 1");
	if($newID < 1) $newID = 1;

	$qForum = "INSERT INTO forums (`id`, `title`, `description`, `catid`, `forder`, `minpower`, `minpowerthread`, `minpowerreply`) VALUES (".$newID.", '".justEscape($title)."', '".justEscape($description)."', ".$category.", ".$forder.", ".$minpower.", ".$minpowerthread.", ".$minpowerreply.")";
	Query($qForum);
	die("Added OK.");
}
else if ($_GET['addforum'] == 1) {
	$qCats = "SELECT * FROM categories";
	$rCats = Query($qCats);
	if (!NumRows($rCats))
		$cSelect = "No categories...";
	else {
		$cats = array();
		while ($cat = Fetch($rCats))
			$cats[$cat['id']] = $cat;
	}
	
	Write('
	<table class="outline margin">
		<tr class="header1">
			<th colspan="2">
				Add forum
			</th>
		</tr>
		<tr class="cell1">
			<td style="width: 25%;">
				Title
			</td>
			<td>
				<input type="text" style="width: 98%;" id="title" value="New forum" />
			</td>
		</tr>
		<tr class="cell1">
			<td>
				Description
			</td>
			<td>
				<input type="text" style="width: 98%;" id="description" value="" />
			</td>
		</tr>
		<tr class="cell0">
			<td>
				Category
			</td>
			<td>
				{0}
			</td>
		</tr>
		<tr class="cell1">
			<td>
				Listing order
			</td>
			<td>
				<input type="text" size="2" id="forder" value="0" />
				<img src="img/icons/icon5.png" title="Forums are sorted by listing order first, then by ID. If all forums in a category have their listing order set to 0, they will therefore be sorted by ID only." alt="[?]" />
			</td>
		</tr>
		<tr class="cell0">
			<td>
				Powerlevel required
			</td>
			<td>
				{1}
				to view
				<br />
				{2}
				to post threads
				<br />
				{3}
				to reply
			</td>
		</tr>
		<tr class="cell2">
			<td>
				&nbsp;
			</td>
			<td>
				<button onclick="addForum()">
					Add forum
				</button>
				<button onclick="document.getElementById(\'editcontent\').innerText=\'\'">
					Cancel
				</button>
			</td>
		</tr>
	</table>', MakeCatSelect('cat', $cats, 1), PowerSelect('minpower', 0), PowerSelect("minpowerthread", 0), PowerSelect('minpowerreply', 0));
	
	die();
}
else if ($_GET['deleteforum'])
{
	$id = (int)$_GET['deleteforum'];
	$qForum = "SELECT * FROM forums WHERE id=".$id;
	$rForum = Query($qForum);
	if (!NumRows($rForum))
		die("No such forum.");
	$forum = Fetch($rForum);
	if($forum['numthreads'] > 0)
		die("Forum has threads. Move those first.");
	Query("DELETE FROM `forums` WHERE `id` = ".$id);
	die("Deleted OK.");
}
else if ($_GET['action'] == "forumTable")
{
	$returnForumTable = true;	
}

$cats = array();
$qCats = "SELECT * FROM categories ORDER BY corder, id";
$rCats = Query($qCats);
if (NumRows($rCats)) {
	while ($cat = Fetch($rCats)) {
		$cats[$cat['id']] = $cat;
	}
	$qForums = "SELECT * FROM forums ORDER BY forder, id";
	$rForums = Query($qForums);
	$forums = array();
	if (NumRows($rForums)) {
		while ($forum = Fetch($rForums)) {
			$forums[$forum['id']] = $forum;
		}
	}
}
if ($cats) {
	$tbl = Format('
<div id="editcontent" style="float: right; width: 45%;">
	&nbsp;
</div>
<div id="flist">
<table class="outline margin" style="width: 45%;">');

$ftbl = Format('
	<tr class="header1">
		<th>
			Edit forums
		</th>
	</tr>');
	foreach ($forums as $forum) {
		$cats[$forum['catid']]['forums'][$forum['id']] = $forum;
	}
	foreach ($cats as $cat) {
		$ftbl .= Format('
	<tbody id="cat{1}" class="c">
		<tr class="cell{2}">
			<td class="c">
				<strong>{0}</strong>
			</td>
		</tr>', $cat['name'], $cat['id'], cell());
		if(isset($cat['forums'])) //<Kawa> empty categories look BAD.
		{
			foreach ($cat['forums'] as $cf) {
				$ftbl .= Format('
		<tr class="cell{3}" style="cursor: hand;">
			<td style="padding-left: 24px;{4}" class="f" onclick="pickForum({1});" id="forum{1}">
				{0}<br />
				<small style="opacity: 0.75;">{2}</small>
			</td>
		</tr>', $cf['title'], $cf['id'], $cf['description'], cell(), ($_GET['s'] == $cf['id'] ? ' outline: 1px solid #888;"' : ''));
			}
		}
		$ftbl .= Format("
	</tbody>");
	}
	
	$ftbl .= Format('
	<tr class="cell2">
		<td>
			<span style="float: right;">
				<button onclick="$(\'#editcontent\').load(\'./editfora.php?addforum=1\');">Add Forum</button>
			</span>{0}
		</td>
	</tr>', (!$_GET['n'] ? '
			<span id="hint">
				Hint: Click a forum or a category to select it.
			</span>' : ''));
$tbl2 .= Format('
</table>
</div>');
}
if (!$returnForumTable) {
	$noFooter = false;
	include('lib/header.php');
}
Write("{0}{1}{2}", $tbl, $ftbl, $tbl2);


function cell() {
	global $cell;
	$cell = ($cell == 1 ? 0 : 1);
	return $cell;
}

function MakeCatSelect($i, $o, $v) {
	$r = '
			<select id="category">';
	foreach ($o as $opt) {
		$r .= '
				<option value="'.$opt['id'].'"'.($v == $opt['id'] ? ' selected="selected"' : '').'>
					'.$opt['name'].'
				</option>';
	}
	$r .= '
			</select>';
	return $r;
}
function PowerSelect($id, $s) {
	$r = Format('
				<select id="{0}">
	', $id);
	$powers = array(-1=>"Banned", 0=>"Regular", 1=>"Local mod", 2=>"Full mod", 3=>"Admin");
	foreach ($powers as $k => $v) {
		$r .= Format('
					<option value="{0}"{2}>{1}</option>
		', $k, $v, ($k == $s ? ' selected="selected"' : ''));
	}
	$r .= '
				</select>';
	return $r;
}

//Sort array by values in sub-arrays
//This will not work if the values in the sub-arrays are the same, but since this is made for ordering forums anyway, who cares?
function sort_by_order($array, $key, $order_column = "forder") {
	$r = array();
	foreach ($array as $k => $v) {
		$r[$v[$order_column]] = $v;
	}
	return $r;
}