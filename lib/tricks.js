function $(id) { return document.getElementById(id); }

/* InnerText hack
   --------------
   Because Firefox doesn't support innerText,
   ONLY the DOM Level 3 textContent. Have these
   goons no sense of backwards compatibility!?
 */
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
	var editor = $("text");
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
	var editor = $("text");
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
	var editor = $("text");
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
	var container = $("smiliesContainer");
	var expandedSet = $("expandedSet");
	var button = $("smiliesExpand");

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
	var container = $("postHelpContainer");
	//var commonSet = $("commonHelp");
	var expandedSet = $("expandedHelp");
	var button = $("postHelpExpand");

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
	var targetObj = $(targetId);

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
	var url = $("swf" + id + "url").innerHTML;
	var mainPanel = $("swf" + id + "main");
	var playButton = $("swf" + id + "play");
	var stopButton = $("swf" + id + "stop");
	mainPanel.innerHTML = '<object data="' + url + '" style="width: 100%; height: 100%;"><embed src="' + url + '" style="width: 100%; height: 100%;"></embed></object>';
	playButton.className = "swfbuttonon";
	stopButton.className = "swfbuttonoff";
}
function stopFlash(id)
{
	var mainPanel = $("swf" + id + "main");
	var playButton = $("swf" + id + "play");
	var stopButton = $("swf" + id + "stop");
	mainPanel.innerHTML = '';
	playButton.className = "swfbuttonoff";
	stopButton.className = "swfbuttonon";
}



function startPoraUpdate()
{
	var ta = $("editbox");
	var tt = $("title");
	var prt = $("previewtext");
	var pri = $("previewtitle");

	prt.innerHTML = ta.value;
	pri.innerText = tt.value;
	setTimeout("startPoraUpdate();", 100);
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
			var oldOnlines = $("onlineUsers");
			var newOnlines = xmlHttp.responseText;
			if(newOnlines.toLowerCase() != oldOnlines.innerHTML.toLowerCase())
			{
				var opacityTween = new OpacityTween(oldOnlines, Tween.regularEaseIn, 100, 0, 1);
				opacityTween.onMotionFinished = function()
				{
					oldOnlines.innerHTML = newOnlines;
					opacityTween = new OpacityTween(oldOnlines, Tween.regularEaseIn, 0, 100, 1);
					opacityTween.onMotionFinished = function()
					{
						startOnlineUsers()
					};
					opacityTween.start();
				};
				opacityTween.start();
				//setTimeout("fadeBackOnlineUsers()", 1000);
			}
			else
			{
				startOnlineUsers()
			}
		}
	};
	xmlHttp.open("GET", "ajaxcallbacks.php?a=o&f=" + onlineFID + "&salt=" + Date(), true);
	xmlHttp.send(null);
	
}

function fadeBackOnlineUsers()
{
	var opacityTween = new OpacityTween($("onlineUsers"), Tween.regularEaseIn, 0, 100, 1);
	opacityTween.onMotionFinished = function() { startOnlineUsers() };
	opacityTween.start();
}



function showEditProfilePart(newId)
{
	$("general").style.display = "none";
	//$("login").style.display = "none";
	//$("avatar").style.display = "none";
	$("personal").style.display = "none";
	$("postlayout").style.display = "none";
	$("theme").style.display = "none";
	$("plugin").style.display = "none";
	$(newId).style.display = "table";

	$("generalButton").className = "";
	//$("loginButton").className = "";
	//$("avatarButton").className = "";
	$("personalButton").className = "";
	$("postlayoutButton").className = "";
	$("themeButton").className = "";
	$("pluginButton").className = "";
	$(newId + "Button").className = "selected";
}





var textEditor;
function hookUpControls()
{
	//Now functional!
	textEditor = $("text");
	textEditor.addEventListener("keypress", HandleKey, true);	
	ConstructToolbar();
}

function ConstructToolbar()
{
	var toolbar = document.createElement("DIV");
	toolbar.className = "postToolbar";

	var buttons =
	[
		{ label: "B", style: "font-weight: bold", insert: "b" },
		{ label: "I", style: "font-style: italic", insert: "i" },
		{ label: "U", style: "text-decoration: underline", insert: "u" },
	];

	for(var i = 0; i < buttons.length; i++)
	{
		var button = buttons[i];
		var newButton = "<button ";
		if (button.style != undefined)
			newButton += "style=\"" + button.style + "\" ";
		newButton += "onclick=\"Insert('" + button.insert + "'); return false;\"";
		newButton += ">" + button.label + "</button>";
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
function Insert(stuff)
{
	var oldSelS = textEditor.selectionStart;
	var oldSelE = textEditor.selectionEnd;
	var scroll = textEditor.scrollTop;
	var selectedText = textEditor.value.substr(oldSelS, oldSelE - oldSelS);
	textEditor.value = textEditor.value.substr(0, oldSelS) + "[" + stuff + "]" + selectedText + "[/" + stuff + "]" + textEditor.value.substr(oldSelE);
	textEditor.selectionStart = oldSelS + 3;
	textEditor.selectionEnd = oldSelS + 3 + selectedText.length;
	textEditor.scrollTop = scroll;
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
			$('theme_css').href = response[0];
			$('theme_banner').src = response[1];
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
	var table = $(tableName);
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
	meta = $("meta_" + pid);
	dyna = $("dyna_" + pid);
	meta.style['display'] = "";
	dyna.style['display'] = "none";
}

function showRevisions(pid)
{
	meta = $("meta_" + pid);
	dyna = $("dyna_" + pid);
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
	post = $("post_" + pid);
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
	var ca = $("ca");
	var checked = ca.checked;
	var checks = document.getElementsByTagName("INPUT");
	for(var i = 0; i < checks.length; i++)
		checks[i].checked = checked;
}

