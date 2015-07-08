/***
!Metadata:
|''Name:''|XMLReader|
|''Description:''||
|''Version:''|2.2.0|
|''Date:''|May 19, 2007|
|''Source:''|http://sourceforge.net/project/showfiles.php?group_id=150646|
|''Author:''|BramChen (bram.chen (at) gmail (dot) com)|
|''License:''|[[Creative Commons Attribution-ShareAlike 2.5 License]]|
|''~CoreVersion:''|2.2.0|
|''Browser:''|Firefox 1.5+; InternetExplorer 6.0|
|''Required:''|As the param "asHtml" is used, [[NestedSlidersPlugin|http://www.tiddlytools.com/#NestedSlidersPlugin]] should be installed|
!Syntax:
{{{<<rssfeed withDesc|noDesc|asHtml rssfeed.xml|http://www.example.com/rssfeed.rdf>>}}}
!Revision History:
|''Version''|''Date''|''Note''|
|2.2.0|May 19, 2007|Atom feeds suppported|
|2.1.1|May 15, 2007|Fixed cache bug|
|2.1.0|May 10, 2007|Fixed bugs:<br>1.missing parameter 'responseText' of processResponse<br>2.Caches failed|
|2.0.0|Mar 08, 2007|Required TW 2.2.0+|
|1.5.0|Mar 04, 2007|Codes reworked, more easier reused|
|1.2.0|Jul 20, 2006|Runs compatibly with TW 2.1.0 (rev #403+)|
|1.1.0|Jul 10, 2006)|change xmlhttp.send(null)/send() to xmlhttp.send("") for more compatibility for some browsers|
|1.0.0|Mar 11, 2006|Initial release|
|~|~|This macro is reworked from RssNewsMacro, but it can be easy to extended to support different structure of xml document from rss feeds|
|~|~|You could uninstall the RssNewsMacro, but still use the original syntax,<<br>>{{{<<rssfeed  withDesc|noDesc|asHtml "rssfeed.xml"|"http://www.example.com/rssfeed.rdf">>}}}|

!Code section:
***/
//{{{
version.extensions.xmlreader = {major: 2, minor: 2, revision: 0,
	date: new Date("May 19, 2007"),
	name: "XMLReader",
	type: "Macro",
	author: "BramChen",
	source: "http://sourceforge.net/project/showfiles.php?group_id=150646"
};

config.messages.XmlReader = {
	fromCache: "^^(//from cache//)^^",
	errorInDataRetriveing: "Problem retrieving XML data: %0",
	invalidXML: "Invalid XML retrieved from: %0",
	urlNotAccessible: "Access to %0 is not allowed,\nPlease check the setting of your browser:\n1.For Gecko based, you should set the 'signed.applets.codebase_principal_support' to be true, in about:config.\n2.For IE, you should add this web site to your trust list."
};

function XmlReader(place,withDesc,xmlURL) {
	this.xmlhttp = null;
	this.place = place;
	this.xmlURL = xmlURL;
	this.withDesc = withDesc;
	this.itemStructure = {title:'Title',link:'Link',pubDate:'PubDate',description:'Desc'};
	this.atomStructure = {title:'Title',id:'Link',updated:'Updated',summary:'Desc'};
//	this.rsTemplate = function(){var t='';for (var i in itemStructure){t+='_'+itemStructure[i]}};
	this.rsTemplate = '_pubDate\n**[[_title|_link]]_description';
	this.items = {Elm: "%0Elm", Text: "_%0"};
	this.keyItem = "item";
	this.dateFormat = "DDD, DD MMM YYYY";
	this.groupBy = null;
	return this;
};

XmlReader.prototype.asyncGet = function(xmlURL,callback){
	if(window.Components && window.netscape && window.netscape.security && this.isCrossSite(xmlURL)){
		try {netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");}
		catch (e) {displayMessage(e.description?e.description:e.toString());}
	}
	return doHttp("GET",xmlURL,null,'text/xml',null,null,callback,null,null);
};

XmlReader.prototype.genLists = function(xml){
	var itemStructure;
	if (xml.lastChild.nodeName == 'feed'){
		this.keyItem = 'entry';
		itemStructure = this.atomStructure;
	}
	else {
		itemStructure = this.itemStructure;
	}
	var itemList = xml.getElementsByTagName(this.keyItem);
	var items = this.items;
	var rsLists='', rssItem; this.groupBy='';
	for (var i=0; i<itemList.length; i++){
		var itemElms=[],itemTexts=[];
		var rsTemplate=this.rsTemplate;
		for (var j in itemStructure){
			var itemElm = items.Elm.format([j]);
			var itemText = items.Text.format([j]);
			itemElms[itemElm] = itemList[i].getElementsByTagName(j).item(0);
			if(itemElms[itemElm]){
				var theTitle = itemStructure[j];
				var theText = (itemElms[itemElm].firstChild)?itemElms[itemElm].firstChild.nodeValue:'';
				rsTemplate=this.convertTemplate(rsTemplate,j,theText);
			}
			else {
				rsTemplate = rsTemplate.replace('_'+j, '');
			}
		}
		rsLists += rsTemplate;
	}
	return rsLists;
};
	
XmlReader.prototype.convertTemplate = function(rsTemplate,j,theText){
	switch (j){
		case 'title':
			rsTemplate = rsTemplate.replace(/_title/,theText.replace(/\[|\]/g,''));
			break;
		case 'id':
			j = 'link';
		case 'link' || 'id':
			rsTemplate = rsTemplate.replace('_'+j, theText);
			break;
		case 'updated':
			j = 'pubDate'
		case 'pubDate':
			theText = this.dateFormatString(this.dateFormat, theText);
			if (this.groupBy == theText){
				rsTemplate = rsTemplate.replace('_'+j, '');
			}
			else{
				rsTemplate = rsTemplate.replace('_'+j, '\n* '+theText);
				this.groupBy = theText;
			}
			break;
		case 'summary':
			j = 'description';
		case 'description':
			var regexpDesc = new RegExp("withDesc|asHtml","g");
			if (regexpDesc.exec(this.withDesc)  && theText){
				var _description = theText.replace(/\n/g,' ');
					_description =_description.replace(/<br \/>/ig,'\n');				
				if (version.extensions.nestedSliders){
					_description = ((this.withDesc == "asHtml")?"<html>"+_description+"</html>":_description);
					rsTemplate = rsTemplate.replace('_'+j,'+++[...]'+_description+'\n===\n');
				}
				else {
					rsTemplate = rsTemplate.replace('_'+j,_description+'\n');
				}
			}
			else {
				rsTemplate = rsTemplate.replace('_'+j,'');
			}
			break;
	}
	return (rsTemplate);
};

XmlReader.prototype.dateFormatString = function(template, theDate){
	theDate = theDate.replace(/-/g,'/').replace(/T.*UT|T.*Z/ ,'');
	var dateString = new Date(theDate);
	template = template.replace(/hh|mm|ss/g,'');
	return dateString.formatString(template);
};

XmlReader.prototype.isCrossSite = function (url){
	var result = false;
	var curLoc = document.location;
	if (url.indexOf(":") != -1 && curLoc.protocol.indexOf("http") != -1) {
		var re = '/(\w+):\/\/([^/:]+)(:\d*)?([^# ]*)/';
		var rsURL=url.match(re);
		for (var i=0; i<rsURL.length; i++){
			rsURL[i]=(typeof rsURL[i] == 'undefined')?'':rsURL[i];
		}
		result = (curLoc.protocol == rsURL[1] && curLoc.host == rsURL[2] && curLoc.port == rsURL[3]);
	}
	return (!result);
};
//}}}
/***
!Macro rssfeed
***/
//{{{
config.macros.rssfeed = {
	cache: {},
	dateFormat: "YYYY/0MM/0DD"
};

config.macros.rssfeed.handler = function(place,macroName,params){
	var withDesc = params[0];
	var xmlURL = params[1];
	var rss = new XmlReader(place,withDesc,xmlURL);
	rss.dateFormat = this.dateFormat;
	var processResponse = function(status,params,responseText,xmlURL,x){
		if (window.netscape){
			if (rss.isCrossSite(xmlURL)){
				try {netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");}
				catch (e) { displayMessage(e.description?e.description:e.toString()); }
			}
		}
		if (x.responseXML){
			xmlURL = xmlURL.replace(/[\?|\&]nocache.*/,'');
			config.macros.rssfeed.cache[xmlURL] = x;
			wikify(rss.genLists(x.responseXML),place);
		}
		else {
			wikify("<html>"+ x.responseText+"</html>", place);
			displayMessage(config.messages.XmlReader.invalidXML.format([xmlURL]));
		}
	};
	if (this.cache[xmlURL]) {
		wikify(config.messages.XmlReader.fromCache,place);
		var status = false;
		var x=this.cache[xmlURL];
		processResponse(status,null,x.responseText,xmlURL,x);
	}
	else {
		rss.xmlhttp = rss.asyncGet(xmlURL, processResponse);
	}
};
//}}}