/***
|''Name:''|LoadExtPlugin|
|''Description:''|LoadExtPlugin allows you to load external extensions from the file lists (named .js) within those tiddlers taged with "ExtList".|
|''Version:''|1.8.0|
|''Date:''|Apr 30, 2007|
|''Source:''|http://www.sourceforge.net/projects/ptw/|
|''Author:''|BramChen (bram.chen (at) gmail (dot) com)|
|''License:''|[[Creative Commons Attribution-ShareAlike 2.5 License]]|
|''CoreVersion:''|2.1.0|
|''Browser:''|Firefox 1.5+; InternetExplorer 6.0|

+++!^[Revision History:]
v1.8.0 (Apr 30 2007)
*config.macros.loadExt support to load singgle external script by using {{{<<loadExt '/pathto/scriptfile.js'>>}}}
*Ensure LoadExtPlugin loading itself and/or loadling scripts after core has been loaded with external core js and itself|
v1.7.2 (Sep 28 2006)
*Fixed bugs on IE
v1.7.1 (30 Aug 2006)
* Changed rule check of ExtList
v1.7.0 (20 Jul 2006)
* Runs compatibly with TW 2.1.0 (rev #403+)
v1.6.0 (13 Jul 2006)
* Fixed bugs in refreshCode and config.macros.loadExt.loadScripts on IE
* Runs compatibly with TW 2.1.0 (rev #359+)
v1.5.2 (21 Jun 2006)
* minor changes for XHTML compliant
v1.5.1 (26 Feb 2006)
* JSLint checked
v1.5.0 (02 Feb 2006)
* add new function config.macros.loadExt.LoadScripts(), keep all variables to be local, thanks Udo.
* Fixed several missing variable declarations
v1.4.0 (20 Jan 2006)
* refreshCode() improved.
v1.3.0 (14 Jan 2006) 
* strip startup error massage for IE
v1.2.0 (13 Jan 2006) 
* TiddlyWiki version 2.0.0 or above required.
* refreshCode() improved.
v1.1.0 (10 Jan 2006)
* To make the extensions list handling more robust, thanks Udo.
* Fix bugs for multi-tiddlers tagged with ExtList
v1.0.0 (07 Jan 2006) 
* Combine the RefreshExt code and LoadExtPlugin, and also make TW 1.2 to be backward compatible, thanks Udo.
* Globle function refreshCode() added, and reserve the refreshExt macro.
* Fix a minor bug for variable "scriptfile".
v0.3.0 (29 Dec 2005)
* macro refreshExt modified to refresh formatter
v0.2.0 (24 Nov 2005)
* macro refreshExt modified for TW 1.2.39 beta 2 and above
v0.1.0 (25 Sep 2005) 
* initial release
===

!''Code section:''
***/
//{{{
version.extensions.loadExt = {major: 1, minor: 8, revision: 0,
 date: new Date("Apr 30, 2007"),
 name: "LoadExtPlugin",
 type: "Plugin",
 author: "BramChen",
 source: "http://sourceforge.net/project/showfiles.php?group_id=150646"
};

config.macros.loadExt = {};
config.macros.loadExt.handler = function(place,macroName,params){
	if (params[0])
		this.loadScriptFile(params[0]);
	else
		this.loadScripts();
};

config.macros.loadExt.loadScriptFile = function(scriptfile){
	var scriptfile = scriptfile.trim();
	if (scriptfile.length < 2 || scriptfile.substr(0,2) == "//" || scriptfile.indexOf(".js") == -1){
		return;
	}
	// displayMessage("loaded: "+ scriptfile);
	var n = document.createElement("script");
	n.type = "text/javascript";
	n.src = scriptfile;
	document.getElementsByTagName("head")[0].appendChild(n);
};

config.macros.loadExt.loadScripts = function() {
	var extTag = "ExtList";
	var str = ""; var scripts = [];
	var tiddlers = store.getTaggedTiddlers(extTag);
	for(var s=0 ; s<tiddlers.length; s++){
		str += store.getRecursiveTiddlerText(tiddlers[s].title)+"\n";
	}
	scripts = str.replace(/[;\r]/mg,"\n").split("\n");
	for (var i=0; i<scripts.length-1; i++) {
		this.loadScriptFile(scripts[i]);
	}

	if (config.browser.isIE){
//		setTimeout(function(){window.refreshCode();return false;},500);
		var lerInterval = setInterval(function(){if(formatter) {clearInterval(lerInterval); window.refreshCode();};},100);
	}
	else {
		var theCodes = "//<![CDATA[\nwindow.refreshCode();//]]>";
		n = document.createElement("script");
		n.type = "text/javascript";
		n.appendChild(document.createTextNode(theCodes));
		document.getElementsByTagName("head")[0].appendChild(n);
		this.refreshCodeInserted = true;
	}
};

window.refreshCode = function (){
	formatter = new Formatter(config.formatters);
	story.forEachTiddler(function(title,e){story.refreshTiddler(title,DEFAULT_VIEW_TEMPLATE,true);});
	refreshDisplay();
	return false;
}

// setTimeout(function(){config.macros.loadExt.loadScripts();return false;},500);
var loadextpluginInterval = setInterval(function(){if(formatter) {clearInterval(loadextpluginInterval);	if(!config.macros.loadExt.refreshCodeInserted) config.macros.loadExt.loadScripts();}},100);
//}}}