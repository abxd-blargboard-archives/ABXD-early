
/* InnerText hack
   --------------
   Because Firefox doesn't support innerText,
   ONLY the DOM Level 3 textContent. Have these
   goons no sense of backwards compatibility!?
 */

//Just to be sure...
if (document.getElementsByClassName == undefined) {
	document.getElementsByClassName = function(cl) {
		var retnode = [];
		var myclass = new RegExp('\\b'+cl+'\\b');
		var elem = this.getElementsByTagName('*');
		for (var i = 0; i < elem.length; i++) {
			var classes = elem[i].className;
			if (myclass.test(classes)) retnode.push(elem[i]);
		}
		return retnode;
	};
}

var hasInnerText = 9001;
function changeText(obj, text)
{
	if(hasInnerText == 9001)
		hasInnerText = (document.getElementsByTagName("body")[0].innerText != undefined) ? true : false;
	if(!hasInnerText)
		obj.textContent = text;
	else
		obj.innerText = text;
}


/* Spoiler buttons for posts
   -------------------------
   Used to be a simple one-way trick.
 */
function toggleSpoiler(obj)
{
	var button = obj.children[0];
	var div = obj.children[1];

	if(div.className == "spoiled")
	{
		if(button.className != "named")
			changeText(button, "Show spoiler");
		div.className = "spoiled hidden";
	}
	else
	{
		if(button.className != "named")
			changeText(button, "Hide spoiler");
		div.className = "spoiled";
	}
}



/* Quote support
   -------------
   Thanks to Mega-Mario for the idea
 */
function insertQuote(pid)
{
	var editor = document.getElementById("text");
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			//editor.value += xmlHttp.responseText;

			//Replacement by Mega-Mario -- Thanks
			editor.focus();
			if (document.selection)
			{
				document.selection.createRange().text += xmlHttp.responseText;
			}
			else
			{
				editor.value = editor.value.substring(0, editor.selectionEnd) + xmlHttp.responseText + editor.value.substring(editor.selectionEnd, editor.value.length);
			}
			editor.scrollTop = editor.scrollHeight;
		}
	};
	xmlHttp.open("GET", "ajaxcallbacks.php?a=q&id=" + pid, true);
	xmlHttp.send(null);
}

function insertChanLink(pid)
{
	var editor = document.getElementById("text");
	var linkText = ">>" + pid + "\r\n";
	editor.focus();
	if (document.selection)
	{
		document.selection.createRange().text += linkText;
	}
	else
	{
		editor.value = editor.value.substring(0, editor. selectionEnd) + linkText + editor.value.substring(editor.selectionEnd, editor.value.length);
	}
	editor.scrollTop = editor.scrollHeight;
}




/* Smiley tricks
   -------------
   Inspired by Mega-Mario's quote system.
 */
function insertSmiley(smileyCode)
{
	var editor = document.getElementById("text");
	editor.focus();
	if (document.selection)
	{
		document.selection.createRange().text += " " + smileyCode;
	}
	else
	{
		editor.value = editor.value.substring(0, editor. selectionEnd) + smileyCode + editor.value.substring(editor.selectionEnd, editor.value.length);
	}
	editor.scrollTop = editor.scrollHeight;
}
function expandSmilies()
{
	var container = document.getElementById("smiliesContainer");
	var expandedSet = document.getElementById("expandedSet");
	var button = document.getElementById("smiliesExpand");

	if(expandedSet.style['display'] != "block")
	{
		expandedSet.style['display'] = "block";
		var opacityTween = new OpacityTween(expandedSet, Tween.regularEaseIn, 0, 100, 1);
		opacityTween.onMotionFinished = function()
		{
			changeText(button, String.fromCharCode(0x25B2));
		}
		opacityTween.start();
	}
	else
	{
		var opacityTween = new OpacityTween(expandedSet, Tween.regularEaseIn, 100, 0, 1);
		opacityTween.onMotionFinished = function()
		{
			changeText(button, String.fromCharCode(0x25BC));
			expandedSet.style['display'] = "none";
		}
		opacityTween.start();
	}
}

function expandPostHelp()
{
	var container = document.getElementById("postHelpContainer");
	//var commonSet = document.getElementById("commonHelp");
	var expandedSet = document.getElementById("expandedHelp");
	var button = document.getElementById("postHelpExpand");

	if(expandedSet.style['display'] != "block")
	{
		expandedSet.style['display'] = "block";
		var opacityTween = new OpacityTween(expandedSet, Tween.regularEaseIn, 0, 100, 0.25);
		opacityTween.onMotionFinished = function()
		{
			changeText(button, String.fromCharCode(0x25B2));
		}
		opacityTween.start();
	}
	else
	{
		var opacityTween = new OpacityTween(expandedSet, Tween.regularEaseIn, 100, 0, 0.25);
		opacityTween.onMotionFinished = function()
		{
			changeText(button, String.fromCharCode(0x25BC));
			expandedSet.style['display'] = "none";
		}
		opacityTween.start();
	}
}



/* AJAX support functions
   ----------------------
   Press button, recieve content.
 */
var xmlHttp = null; //Cache our request object

function GetXmlHttpObject()
{
	//If we already have one, just return that.
	if (xmlHttp != null) return xmlHttp;

	//Modern browsers...
	try
	{
		xmlHttp = new XMLHttpRequest();
	}
	catch (e)
	{
		//...and old Internet Explorers. I suppose these can be removed over time :)
		try
		{
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

function LoadSomething(targetId, url, useTimeSalt)
{
	xmlHttp = GetXmlHttpObject();
	var targetObj = document.getElementById(targetId);

	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			targetObj.innerHTML = xmlHttp.responseText;
		}
	};

	if(useTimeSalt)
 		xmlHttp.open("GET", url + "&salt=" + Date(), true);
 	else
 		xmlHttp.open("GET", url, true);

	xmlHttp.send(null);
}

/* Flashloops */
function startFlash(id)
{
	var url = document.getElementById("swf" + id + "url").innerHTML;
	var mainPanel = document.getElementById("swf" + id + "main");
	var playButton = document.getElementById("swf" + id + "play");
	var stopButton = document.getElementById("swf" + id + "stop");
	mainPanel.innerHTML = '<object data="' + url + '" style="width: 100%; height: 100%;"><embed src="' + url + '" style="width: 100%; height: 100%;"></embed></object>';
	playButton.className = "swfbuttonon";
	stopButton.className = "swfbuttonoff";
}
function stopFlash(id)
{
	var mainPanel = document.getElementById("swf" + id + "main");
	var playButton = document.getElementById("swf" + id + "play");
	var stopButton = document.getElementById("swf" + id + "stop");
	mainPanel.innerHTML = '';
	playButton.className = "swfbuttonoff";
	stopButton.className = "swfbuttonon";
}



function startPoraUpdate()
{
	var ta = document.getElementById("editbox");
	var tt = document.getElementById("title");
	var prt = document.getElementById("previewtext");
	var pri = document.getElementById("previewtitle");

	prt.innerHTML = ta.value;//.replace("\n", "<br />");
	pri.innerText = tt.value;
	//setTimeout("startPoraUpdate();", 100);
}


var onlineFID = 0;

function startOnlineUsers()
{
	//onlineFID = fid;
	setTimeout("getOnlineUsers()", 10000);
}

function getOnlineUsers()
{
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			var oldOnlines = document.getElementById("onlineUsers");
			var newOnlines = xmlHttp.responseText;
			//if(newOnlines.length < 5)
			//	alert("Warning: bad newOnlines.\n\nValue is \"" + newOnlines + "\".");
			//alert("Old: \"" + oldOnlines.innerHTML + "\"\n\nNew: \"" + newOnlines + "\"");
			if(newOnlines.length > 4 && newOnlines.toLowerCase() != oldOnlines.innerHTML.toLowerCase())
			{
				oldOnlines.innerHTML = newOnlines;
				opacityTween = new OpacityTween(oldOnlines, Tween.regularEaseIn, 0, 100, 0.5);
				opacityTween.start();
				//setTimeout("fadeBackOnlineUsers()", 1000);
			}

			xmlHttp = null;
			xmlHttp = GetXmlHttpObject();
			xmlHttp.onreadystatechange = function()
			{
				if(xmlHttp.readyState == 4)
				{
					var oldViews = document.getElementById("viewCount");
					var newViews = xmlHttp.responseText;
					if(newViews != oldViews.innerHTML)
					{
						oldViews.innerHTML = newViews;
						opacityTween = new OpacityTween(oldViews, Tween.regularEaseIn, 0, 100, 0.5);
						opacityTween.start();
					}
				}
			};
			xmlHttp.open("GET", "ajaxcallbacks.php?a=vc&salt=" + Date(), true);
			xmlHttp.send(null);
		}
	};
	xmlHttp.open("GET", "ajaxcallbacks.php?a=ou&f=" + onlineFID + "&salt=" + Date(), true);
	xmlHttp.send(null);

	startOnlineUsers()
}

function fadeBackOnlineUsers()
{
	var opacityTween = new OpacityTween(document.getElementById("onlineUsers"), Tween.regularEaseIn, 0, 100, 1);
	opacityTween.onMotionFinished = function() { startOnlineUsers() };
	opacityTween.start();
}



function showEditProfilePart(newId)
{
	tables = document.getElementsByClassName('eptable');
	for (i=0;i<tables.length;i++) {
		tables[i].style.display = "none";
	}
	document.getElementById(newId).style.display = "table";
	tabs = document.getElementsByClassName('tab');
	for (i=0;i<tabs.length;i++) {
		tabs[i].className = "tab";
	}
	document.getElementById(newId+"Button").className = "tab selected";
}

var textEditor;
function hookUpControls()
{
	//Now functional!
	textEditor = document.getElementById("text");
	textEditor.addEventListener("keypress", HandleKey, true);
	ConstructToolbar();
}

function ConstructToolbar()
{
	var toolbar = document.createElement("DIV");
	toolbar.className = "postToolbar";

	var buttons =
	[
		{ label: "B", title: "Bold", style: "font-weight: bold", insert: "b" },
		{ label: "I", title: "Italic", style: "font-style: italic", insert: "i" },
		{ label: "U", title: "Underlined", style: "text-decoration: underline", insert: "u" },
		{ label: "S", title: "Strikethrough", style: "text-decoration: line-through", insert: "s" },
		{ label: "-" },
		{ label: "x&#x00B2;", title: "Superscript", insert: "sup", html: true },
		{ label: "x&#x2082;", title: "Subscript", insert: "sub", html: true },
		//{ label: "A", title: "Big", insert: "big", html: true },
		//{ label: "a", title: "Small", insert: "small", html: true },
		{ label: "-" },
		{ label: "url", title: "Link", style: "color: blue; text-decoration: underline", insert: "url" },
		{ label: "<img src=\"img/stdimg.png\" style=\"height: 0.9em;\" />", title: "Image", insert: "img" },
		{ label: "-" },
		{ label: "&ldquo; &rdquo;", title: "Quote", insert: "quote" },
		{ label: "&hellip;", title: "Spoiler", style: "opacity: 0.25", insert: "spoiler" },
		//{ label: "abc", title: "Insert code block", style: "font-family: monospace", insert: "code" },

	];

	for(var i = 0; i < buttons.length; i++)
	{
		var button = buttons[i];
		if(button.label == "-")
		{
			toolbar.innerHTML += " ";
			continue;
		}
		var newButton = "<button ";
		if (button.title != undefined)
			newButton += "title=\"" + button.title + "\" ";
		newButton += "onclick=\"Insert('" + button.insert + "', " + button.html + "); return false;\">";
		if (button.style != undefined)
			newButton += "<span style=\"" + button.style + "\">";
		newButton += button.label;
		if (button.style != undefined)
			newButton += "</span>";
		newButton += "</button>";
		toolbar.innerHTML += newButton;
	}

	textEditor.parentNode.insertBefore(toolbar, textEditor);
}
function HandleKey()
{
	if(event.ctrlKey && !event.altKey)
	{
		var charCode = event.charCode ? event.charCode : event.keyCode;
		var c = String.fromCharCode(charCode).toLowerCase();
		if (c == "b" || c == "i" || c == "u")
		{
			textEditor.focus();
			Insert(c);
			event.preventDefault();
			return false;
		}
	}
}
function Insert(stuff, html)
{
	var oldSelS = textEditor.selectionStart;
	var oldSelE = textEditor.selectionEnd;
	var scroll = textEditor.scrollTop;
	var selectedText = textEditor.value.substr(oldSelS, oldSelE - oldSelS);

	if(html)
		textEditor.value = textEditor.value.substr(0, oldSelS) + "<" + stuff + ">" + selectedText + "</" + stuff + ">" + textEditor.value.substr(oldSelE);
	else
		textEditor.value = textEditor.value.substr(0, oldSelS) + "[" + stuff + "]" + selectedText + "[/" + stuff + "]" + textEditor.value.substr(oldSelE);

	textEditor.selectionStart = oldSelS + stuff.length + 2;
	textEditor.selectionEnd = oldSelS + stuff.length + 2 + selectedText.length;
	textEditor.scrollTop = scroll;
	textEditor.focus();
}




function startNewMarkers()
{
	setTimeout("getNewMarkers()", 30000);
}

function getNewMarkers()
{
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			var news = xmlHttp.responseText.split(',');
			var pic = "<img src=\"img/status/new.png\" alt=\"New!\"/>";

			var allTDs = document.getElementsByTagName("TD");
			var j = 0;
			for (var i = 0; i < allTDs.length; i++)
			{
				var classes = allTDs[i].getAttribute("class");
				if (classes != null && classes.indexOf("newMarker") > -1)
				{
					if(news[j] > 0)
						allTDs[i].innerHTML = pic + news[j];
					else
						allTDs[i].innerHTML = "";
					j++;
				}
			}

			startNewMarkers();
		}
	};
	xmlHttp.open("GET", "ajaxcallbacks.php?a=ni&salt=" + Date(), true);
	xmlHttp.send(null);

}



// Live theme changer by Mega-Mario
function ChangeTheme(newtheme)
{
	var req = GetXmlHttpObject();

	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			var response = req.responseText.split('|');
			document.getElementById('theme_css').href = response[0];
			document.getElementById('theme_banner').src = response[1];
		}
	};
	req.open("GET", "ajaxcallbacks.php?a=tf&t="+newtheme, true);
	req.send(null);
}



//Search page pager
function ChangePage(newpage)
{
        var pagenums = document.getElementsByClassName('pagenum');
        for (i = 0; i < pagenums.length; i++)
                pagenums[i].href = '#';

        pagenums = document.getElementsByClassName('pagenum'+newpage);
        for (i = 0; i < pagenums.length; i++)
                pagenums[i].removeAttribute('href');

        var pages = document.getElementsByClassName('respage');
        for (i = 0; i < pages.length; i++)
                pages[i].style.display = 'none';

        pages = document.getElementsByClassName('respage'+newpage);
        for (i = 0; i < pages.length; i++)
                pages[i].style.display = '';
}




function expandTable(tableName, button)
{
	var table = document.getElementById(tableName);
	var rows = table.getElementsByTagName("tr");

	for(var i = 0; i < rows.length; i++)
	{
		//alert(rows[i].className + ", " + rows[i].style['display']);
		if(rows[i].className == "header1")
			continue;

		if(rows[i].style['display'] == "none")
			rows[i].style['display'] = "";
		else
			rows[i].style['display'] = "none";
	}
}

function hideTricks(pid)
{
	meta = document.getElementById("meta_" + pid);
	dyna = document.getElementById("dyna_" + pid);
	meta.style['display'] = "";
	dyna.style['display'] = "none";
}

function showRevisions(pid)
{
	meta = document.getElementById("meta_" + pid);
	dyna = document.getElementById("dyna_" + pid);
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			meta.style['display'] = "none";
			dyna.style['display'] = "";
			dyna.innerHTML = xmlHttp.responseText;
		}
	}
	xmlHttp.open("GET", "ajaxcallbacks.php?a=srl&id=" + pid, true);
	xmlHttp.send(null);
}

function showRevision(pid, rev)
{
	post = document.getElementById("post_" + pid);
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			post.innerHTML = xmlHttp.responseText;
		}
	}
	xmlHttp.open("GET", "ajaxcallbacks.php?a=sr&id=" + pid + "&rev=" + rev, true);
	xmlHttp.send(null);
}

function checkAll()
{
	var ca = document.getElementById("ca");
	var checked = ca.checked;
	var checks = document.getElementsByTagName("INPUT");
	for(var i = 0; i < checks.length; i++)
		checks[i].checked = checked;
}


function hookUploadCheck(id, type, size)
{
	var obj = document.getElementById(id);
	if(type == 0)
	{
		obj.onchange = function()
		{
			var submit = document.getElementById("submit");
			var sizeWarning = document.getElementById("sizeWarning");
			var typeWarning = document.getElementById("typeWarning");

			submit.disabled = (obj.value == "");

			if(obj.files != undefined)
			{
				var file = obj.files[0];
				var fileSize = 0;
				if(file != undefined)
					fileSize = file.size;
				sizeWarning.style['display'] = (fileSize > size) ? "inline" : "none";
				submit.disabled = (fileSize > size);
				if(file != undefined)
				{
					switch(file.type)
					{
						case "image/jpeg":
						case "image/png":
						case "image/gif":
							typeWarning.style['display'] = "none";
							break;
						default:
							typeWarning.style['display'] = "inline";
							submit.disabled = true;
					}
				}
			}
		};
	}
	else if(type == 1)
	{
		obj.onchange = function()
		{
			var submit = document.getElementById("submit");
			var sizeWarning = document.getElementById("sizeWarning");
			var typeWarning = document.getElementById("typeWarning");

			submit.disabled = (obj.value == "");
			if(obj.files != undefined)
			{
				var file = obj.files[0];
				var fileSize = 0;
				if(file != undefined)
					fileSize = file.size;
				sizeWarning.style['display'] = (fileSize > size) ? "inline" : "none";
				submit.disabled = (fileSize > size);
				if(file != undefined)
				{
					switch(file.type)
					{
						case "application/x-msdownload":
						case "text/html":
							typeWarning.style['display'] = "inline";
							submit.disabled = true;
							break;
						default:
							typeWarning.style['display'] = "none";
					}
				}
			}
		};
	}
}






fid = 0;
hint = true;
function pickForum(id) {
	if (hint == true) {
		$("#hint").remove();
		hint = false;
	}
	$(".f, .c").css("outline", "0px none");
	$("#forum"+id).css("outline", "1px solid #888")
	if ($("#editcontent").is(":hidden")) $("#editcontent").show();
	$("#editcontent").load('./editfora.php?editforum='+id);
	fid = id;
}

function changeForumInfo(forum)
{
	//<Kawa> Okay, let's see what we got here...
	xmlHttp = GetXmlHttpObject();
	var title = document.getElementById("title").value;
	var description = document.getElementById("description").value;
	var category = document.getElementById("category").value;
	var forder = document.getElementById("forder").value;
	var minpower = document.getElementById("minpower").value;
	var minpowerthread = document.getElementById("minpowerthread").value;
	var minpowerreply = document.getElementById("minpowerreply").value;

	var data = "updateforum=1&id="+forum+"&title="+encodeURIComponent(title)+"&description="+encodeURIComponent(description)+"&forder="+forder+"&category="+category+"&minpower="+minpower+"&minpowerthread="+minpowerthread+"&minpowerreply="+minpowerreply;
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			if(xmlHttp.responseText.indexOf("Change OK") != -1)
				$("#flist").load("editfora.php?action=forumTable&s"+forum);
			else
				alert("Something went wrong.");
		}
	}
	xmlHttp.open("GET", "editfora.php?" + data, true);
	xmlHttp.send(null);
}

function deleteForum(fid)
{
	xmlHttp = GetXmlHttpObject();
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			if(xmlHttp.responseText.indexOf("Deleted OK") != -1)
			{
				$("#flist").load("editfora.php?action=forumTable");
				document.getElementById("editcontent").innerText="";
			}
			else
				alert("Something went wrong." + xmlHttp.responseText);
		}
	}
	xmlHttp.open("GET", "editfora.php?deleteforum="+fid, true);
	xmlHttp.send(null);
}

function addForum()
{
	xmlHttp = GetXmlHttpObject();
	var title = document.getElementById("title").value;
	var description = document.getElementById("description").value;
	var category = document.getElementById("category").value;
	var forder = document.getElementById("forder").value;
	var minpower = document.getElementById("minpower").value;
	var minpowerthread = document.getElementById("minpowerthread").value;
	var minpowerreply = document.getElementById("minpowerreply").value;

	var data = "addforum=2&title="+encodeURIComponent(title)+"&description="+encodeURIComponent(description)+"&forder="+forder+"&category="+category+"&minpower="+minpower+"&minpowerthread="+minpowerthread+"&minpowerreply="+minpowerreply;
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4)
		{
			if(xmlHttp.responseText.indexOf("Added OK") != -1)
			{
				$("#flist").load("editfora.php?action=forumTable");
				document.getElementById("editcontent").innerText="";
			}
			else
				alert("Something went wrong.");
		}
	}
	xmlHttp.open("GET", "editfora.php?" + data, true);
	xmlHttp.send(null);
}
