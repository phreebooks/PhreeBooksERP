/***
! server-side TW plugin
this is acting as a common script for server-side TW
***/

/***
''no cache''
***/
serverside.fn.no_cache = function() {
	return "time"+new String((new Date()).getTime());
	};
/***
!!!message function
*using displayMessage in TW, but add autoclose timer (defined by serverside.messageDuration)
*it will display the SECOND LAST line of returned message (except in debug mode)
**this is to skip the DONE/ERROR line
*alert popup when debug enabled, displaying the whole responseText
*@param h http_request handler
***/

serverside.fn.displayMessage = function(sXml) {
	var xml = parseXml(sXml);
	if (xml){
		displayMessage($(xml).find("Message").text());
	} else {
		displayMessage(serverside.lingo.timeOut);
	}
	if( serverside.messageDuration != 0 )
		setTimeout("clearMessage()",serverside.messageDuration);
};
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/***
!!!login
code obtained from ZiddlyWiki
***/
config.macros.login = {
	label: serverside.lingo.login.login,
	prompt: serverside.lingo.login.loginPrompt,
	handler: function(place) {return true;}
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/***
!!!revision functions
code obtained from ZiddlyWiki
***/

/***
''revision command (marco)''
*obtain revision info from server and display as a list
*Each tiddler detail is separated by a linebreak "\n"
*format of returned data (space separated):
**date version_number modifier
**DONE/ERROR should be at the last line

***/
config.commands.revisions = {
  text: serverside.lingo.revision.text,
  tooltip: serverside.lingo.revision.tooltip,
  popupNone: serverside.lingo.revision.popupNone,
  hideShadow: true,
  handler: function(event,src,title) {
    var popup = Popup.create(src);
    Popup.show(popup,false);
	var callback = function(sXml) {
		var xml = parseXml(sXml);
		if (!xml) 	return;
		if (!popup) return;
		$(xml).find("revisionList").each(function() {
			var modified = Date.convertFromYYYYMMDDHHMM($(this).find("modified").text());
			var key = $(this).find("version").text();
			var modifier = $(this).find("modifier").text();
			var button = createTiddlyButton(createTiddlyElement(popup,"li"), 
				"(" + key + ")" + " - " + modified.toLocaleString() +" "+ modifier, 
				serverside.lingo.revision.tooltip, 
				function(){
					displayTiddlerRevision(this.getAttribute('tiddlerTitle'), 
					this.getAttribute('revisionkey'), this, true ); 
					return false;
				}, 'tiddlyLinkExisting tiddlyLink');
			button.setAttribute('tiddlerTitle', title);
			button.setAttribute('revisionkey', key);
			var t = store.fetchTiddler(title);
			if(!t) alert(title+serverside.lingo.revision.notExist);
			if( t && t.modified == modified )
				button.className = 'revisionCurrent';
		});
	};
	$.ajax({
		type: "GET",
		contentType: "application/xml; charset=utf-8",
		url: 'index.php?module=phreewiki&page=ajax&op=msghandle&action=revisionList&title='+encodeURIComponent(title.htmlDecode()),
		dataType: ($.browser.msie) ? "text" : "xml",
		//error: serverside.fn.displayMessage,
				error: function(XMLHttpRequest, textStatus, errorThrown) {
			  alert ("Ajax Error: " + XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown);
			},
		success: callback
	  });
	
	event.cancelBubble = true;
	if (event.stopPropagation) event.stopPropagation();
	return true;
	}
};

/***
''display tiddler revision''
*when user click on revision list, the tiddler would be changed to display the body, tag and title of the tiddler
##title (current)
##oldtitle
##body
##modifier
##modified
##created
##tags
##version
##fields (currently not used)
##updateTimeline
***/

function displayTiddlerRevision(title, revision, src, updateTimeline) {
	//display tiddler function
	var displayTiddler = function(sXml) {
		var xml = parseXml(sXml);
		if (!xml) 	return;
		$(xml).find("version").each(function() {
				var tiddler 		= new Tiddler();
				var oldtiddler 		= store.fetchTiddler(title);
				var oldtitle 		= $(this).find("title").text();
				var text 			= Tiddler.unescapeLineBreaks( $(this).find("body").text());
				var modifier		= $(this).find("modifier").text();
				var modified 		= Date.convertFromYYYYMMDDHHMM($(this).find("modified").text());
				var version 		= $(this).find("version").text();
				var tags 			= $(this).find("tags").text();
				var created 		= Date.convertFromYYYYMMDDHHMM($(this).find("created").text());
				if( oldtiddler.modified != modified ) {
					var tmpstr = " (Historical revision " + version;
					if(title != oldtitle) {
						tmpstr += " renamed from " + oldtitle;
					}
					tmpstr += ")";
				}
				tiddler.set(title, text, modifier, modified, tags, created);
				store.addTiddler(tiddler);
				store.setValue(tiddler, 'revisionkey', version);
				store.setValue(tiddler, "revisioninfo", tmpstr);
				story.refreshTiddler(title, DEFAULT_VIEW_TEMPLATE, true);
				serverside.fn.displayMessage();
		});
	};
	
	$.ajax({
		type: "GET",
		contentType: "application/xml; charset=utf-8",
		url: 'index.php?module=phreewiki&page=ajax&op=msghandle&action=revisionDisplay&title='+encodeURIComponent(title.htmlDecode())+ '&revision='+ encodeURIComponent(revision),
		dataType: ($.browser.msie) ? "text" : "xml",
		error: serverside.fn.displayMessage ,
		success: displayTiddler
	  });
	
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/***
!!!saveTiddler
Hijack saveTiddler for saving tiddler to server
*method: POST
*param:
##tiddler - a tiddler div of the new tidler data
##omodified - previous (current?) modified date
##otitle - previous (current) title
***/
//{{{

TiddlyWiki.prototype.ss_saveTiddler = TiddlyWiki.prototype.saveTiddler;		//hijack
TiddlyWiki.prototype.saveTiddler = function(title,newTitle,newBody,modifier,modified,tags,fields,clearChangeCount,created)
{
	//get previous title and
	var tiddler = this.fetchTiddler(title);

	//if minorupdate is used, it is possible that a tiddler is accidentally overwrite by another due to collision detection is done via modified date
	if( modified == undefined ) {
		if(tiddler)	{
			modified = tiddler.modified;
		}else{
			modified =  new Date();
		}
	}
	var omodified = modified;
	if(tiddler){
		omodified = tiddler.modified; // get original modified date
	}
	var tiddler = "";
	tiddler =  store.ss_saveTiddler(title,newTitle,newBody,modifier,modified,tags,fields,clearChangeCount,created);//save to local copy

	var postStr = "tiddler"+'='+encodeURIComponent(tiddler.saveToDiv());
	postStr += '&' + "omodified"+'='+encodeURIComponent(omodified.convertToYYYYMMDDHHMM());
	postStr += '&' + "otitle"+'='+encodeURIComponent(title.htmlDecode());
	$.ajax({
		type: "POST",
		url: 'index.php?module=phreewiki&page=ajax&op=msghandle&action=saveTiddler',
		dataType: ($.browser.msie) ? "text" : "xml",
		data: postStr,
		//error:   serverside.fn.displayMessage ,
		error: function(XMLHttpRequest, textStatus, errorThrown) {
		      alert ("Ajax ErrorThrown: " + errorThrown + "\nTextStatus: " + textStatus + "\nError: " + XMLHttpRequest.responseText);
			},
		success: serverside.fn.displayMessage
	  });
	return tiddler;
};

/***
!!!removeTiddler
Hijack removeTiddler for deleting tiddler on server
*method: POST
*param:
##title - title of tiddler to delete
***/
//{{{
TiddlyWiki.prototype.ss_removeTiddler = TiddlyWiki.prototype.removeTiddler;
TiddlyWiki.prototype.removeTiddler = function(title) {
	store.ss_removeTiddler(title);
	var postStr = "title"+'='+encodeURIComponent(title);
	$.ajax({
		type: "POST",
		url: 'index.php?module=phreewiki&page=ajax&op=msghandle&action=removeTiddler',
		dataType: ($.browser.msie) ? "text" : "xml",
		data: postStr ,
		error: serverside.fn.displayMessage,
		success: serverside.fn.displayMessage
	  });
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/***
!!!uploadStoreArea
used to upload all tiddlers to the server
*method: POST
*param:
##upload - contain all tiddlers in div format, identical to storeArea's div

***/
//{{{
serverside.fn.uploadStoreArea = function () {
	var postStr = "upload"+'='+encodeURIComponent(allTiddlersAsHtml());
	$.ajax({
		type: "POST",
		url: 'index.php?module=phreewiki&page=ajax&op=msghandle&action=upload',
		dataType: ($.browser.msie) ? "text" : "xml",
		data: postStr,
		error: serverside.fn.displayMessage,
		success: serverside.fn.displayMessage
	  });
};
//upload storeArea marco, will create a text button
config.macros.ssUploadStoreArea = {
	label: serverside.lingo.uploadStoreArea,
	prompt: serverside.lingo.uploadStoreArea,
	handler: function(place,macroName) {createTiddlyButton(place,this.label,this.prompt,this.onClick,null,null,null);},
	onClick: function(e) { serverside.fn.uploadStoreArea();}
};

/***
!!!RSS
used to upload rss to the server (generate by TW)
*method: POST
*param:
##rss - contain formatted rss

***/

serverside.fn.uploadRSS = function ()
{
	var postStr = "rss"+'='+encodeURIComponent(generateRss());
	$.ajax({
		type: "POST",
		url: 'index.php?module=phreewiki&page=ajax&op=msghandle&action=rss',
		dataType: ($.browser.msie) ? "text" : "xml",
		data: postStr ,
		error: serverside.fn.displayMessage,
		success: serverside.fn.displayMessage
	  });
	
};
version.extensions.ssUploadRSS = {major: 1, minor: 0, revision: 0, date: new Date(2006,9,21)};
config.macros.ssUploadRSS = {
	label: serverside.lingo.rss,
	prompt: serverside.lingo.rss,
	handler: function(place,macroName) {createTiddlyButton(place,this.label,this.prompt,this.onClick,null,null,null);},
	onClick: function(e) { serverside.fn.uploadRSS();}
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//password variable
config.options.pasSecretCode = "";

//////////////////////////////////////////////////option handler

merge(config.optionHandlers, {
	'pas': {
 		get: function(name) {return encodeCookie(Crypto.hexSha1Str(config.options[name].toString()).toLowerCase());},
		set: function(name,value) {config.options[name] = decodeCookie(value);}
	}
});

merge(config.macros.option.types, {
	'pas': {
		elementType: "input",
		valueField: "value",
		eventName: "onkeyup",
		className: "pasOptionInput",
		typeValue: 'password',
		create: config.macros.option.genericCreate,
		onChange: config.macros.option.genericOnChange
	}
});

//////////////////////////////////////////////////saveOptionCookie
window.ss_saveOptionCookie = window.saveOptionCookie;
window.saveOptionCookie = function(name) {
	if(safeMode){
		return;
	}
	var c = name + "=";
	var optType = name.substr(0,3);
	if(config.optionHandlers[optType] && config.optionHandlers[optType].get)
		c += config.optionHandlers[optType].get(name);
	if (optType == 'pas' &&  serverside.passwordTime!=0 ) {
		var date = new Date();
		date.setTime(date.getTime()+serverside.passwordTime);
		c += "; expires="+date.toGMTString()+"; path=/";
	}
	else {
		c += "; expires=Fri, 1 Jan 2038 12:00:00 UTC; path=/";
	}
	document.cookie = c;
};

function loadRemoteFile(url,callback,params) {
	if(window.Components && window.netscape && window.netscape.security && isCrossSite(url)){
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
			}
		catch (e) {
			displayMessage( e.description ? e.description : e.toString() );
			}
	}
	return doHttp("GET",url,null,null,null,null,callback,params,null);
}

function isCrossSite(url){
	var result = false;
	var curLoc = document.location;
	if (url.indexOf(":") != -1 && curLoc.protocol.indexOf("http") != -1) {
		var re = '/(\w+):\/\/([^/:]+)(:\d*)?([^# ]*)/';
		var rsURL = url.match(re);
		for (var i=0; i<rsURL.length; i++){
			rsURL[i]=(typeof rsURL[i] == "undefined")?"":rsURL[i];
		}
		result = (curLoc.protocol == (rsURL[1]+':') && curLoc.host == rsURL[2] && curLoc.port == rsURL[3]);
	}
	return (!result);

}; 

/////////////////////////////////////////////////////////remove isDirty popup dialog/////////////////////////////////////////////////

window.checkUnsavedChanges = function()	{};//ccT save on the fly

window.confirmExit = function() {
	hadConfirmExit = true;		//assume confirm exit since ccT save "on the fly"
};
/////////////////////////////////////////////////////////change title/////////////////////////////////////////////////
window.cct_main = window.main
window.main = function() {
	window.cct_main();
	window.cct_tweak();
	refreshPageTemplate('PageTemplate');
	story.forEachTiddler(function(title){story.refreshTiddler(title,DEFAULT_VIEW_TEMPLATE,true);});
	document.title=(wikifyPlain("SiteTitle") + " - " + wikifyPlain("SiteSubtitle"));
};

/////////////////////////////////////////////////////////saveChanegs/////////////////////////////////////////////////
window.saveChanges = function () {
	clearMessage();
	serverside.fn.uploadRSS();

};

