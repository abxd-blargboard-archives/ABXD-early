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
				editor.value = editor.value.substring(0, editor. selectionEnd) + xmlHttp.responseText + editor.value.substring(editor.selectionEnd, editor.value.length);
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

	prt.innerHTML = ta.value;
	pri.innerText = tt.value;
	setTimeout("startPoraUpdate();", 100);
}


var onlineFID = 0;

function startOnlineUsers(fid)
{
	onlineFID = fid;
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
	var opacityTween = new OpacityTween(document.getElementById("onlineUsers"), Tween.regularEaseIn, 0, 100, 1);
	opacityTween.onMotionFinished = function() { startOnlineUsers() };
	opacityTween.start();
}



function showEditProfilePart(newId)
{
	document.getElementById("general").style.display = "none";
	document.getElementById("login").style.display = "none";
	document.getElementById("avatar").style.display = "none";
	document.getElementById("personal").style.display = "none";
	document.getElementById("postlayout").style.display = "none";
	document.getElementById("theme").style.display = "none";
	document.getElementById(newId).style.display = "table";

	document.getElementById("generalButton").className = "";
	document.getElementById("loginButton").className = "";
	document.getElementById("avatarButton").className = "";
	document.getElementById("personalButton").className = "";
	document.getElementById("postlayoutButton").className = "";
	document.getElementById("themeButton").className = "";
	document.getElementById(newId + "Button").className = "selected";
}



function hookUpControls()
{
	document.getElementById("text").onkeyup = function()
	{
		if(event.ctrlKey)
		{
			//66 > B
			//73 > I
			//115 > U

			return false;
		}
	};
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




var goomba, goomPos, goomDir, goomStep, goomStomped, goomAudio;
function startGoomba()
{
	goomba = document.getElementById("goomba");
	if(goomba == null)
	{
		setTimeout("startGoomba();", 100);
		return;
	}
	if(Math.random() > 0.5)
	{
		goomPos = getWidth();
		goomDir = -1;
	}
	else
	{
		goomPos = -15;
		goomDir = 1;
	}
	goomStep = 0;
	goomAudio = new Audio("img/goomba.ogg");
	setTimeout("moveGoomba();", 100);
}

function moveGoomba()
{
	if(goomStomped)
		return;
	goomPos += goomDir;
	if(goomPos > getWidth() || goomPos < -16)
	{
		if(Math.random() > 0.5)
		{
			goomPos = getWidth();
			goomDir = -1;
		}
		else
		{
			goomPos = -15;
			goomDir = 1;
		}
	}
	goomba.style.left = goomPos + "px";
	goomStep = (goomStep + 1) % 2;
	goomba.style.backgroundPosition = "-" + (goomStep * 16) + "px 0px";
	setTimeout("moveGoomba();", 100);
}

function stompGoomba()
{
	goomStomped = 1;
	goomba.style.backgroundPosition = "-32px 0px";
	goomAudio.play();
	setTimeout("removeGoomba();", 500);
}

function removeGoomba()
{
	goomba.style.display = "none";
}

function getWidth()
{
	if (self.innerHeight)
		return self.innerWidth;
	else if (document.documentElement && document.documentElement.clientHeight)
		return document.documentElement.clientWidth;
	else if (document.body)
		return document.body.clientWidth;
}
