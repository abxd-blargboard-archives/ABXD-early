/* Spoiler buttons for posts
   -------------------------
   Used to be a simple one-way trick.
 */
function toggleSpoiler(obj)
{
	var button = obj.children(0);
	var div = obj.children(1);
	
	if(div.className == "spoiled")
	{
		button.innerText = "Show spoiler"	
		div.className = "spoiled hidden";
	}
	else
	{
		button.innerText = "Hide spoiler"
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
	var url = document.getElementById("swf" + id + "url").innerText;
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