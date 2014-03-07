/***
!Metadata:
|''Name:''|BreadcrumbsPlugin|
|''Description:''||
|''Version:''|1.4.2|
|''Date:''|Feb 15, 2007|
|''Source:''|http://sourceforge.net/project/showfiles.php?group_id=150646|
|''Author:''|Alan Hecht (with 2.0 update from 'jack' and revisions by Bram Chen)|
|''License:''|[[Creative Commons Attribution-ShareAlike 2.5 License]]|
|''~CoreVersion:''|2.1.0|
|''Browser:''|Firefox 1.5+; InternetExplorer 6.0|

!Revision History:
|''Version''|''Date''|''Note''|
|1.4.2|Feb 15, 2007|Runs compatibly with TW 2.2.0 (rev #1501+)|
|1.4.1|Aug 05, 2006|in restartHome(), check for valid crumbArea before setting style, by Eric|
|1.4.0|Aug 02, 2006|Fixed bug, the redefined onClickTiddlerLink_orig_breadCrumbs works incorrectly on IE|
|1.3.0|Jul 20, 2006|Runs compatibly with TW 2.1.0 (rev #403+)|
|1.2.0|Feb 07, 2006|change globle array breadCrumbs to config.breadCrumbs by Eric's suggestion|
|1.1.0|Feb 04, 2006|JSLint checked|
|1.0.0|Feb 01, 2006|TW2 ready and code Cleaned-up|

!Code section:
***/
//{{{
version.extensions.breadCrumbs = {major: 1, minor: 4, revision: 2,date: new Date("Feb 15, 2007")};
config.breadCrumbs = [];

window.onClickTiddlerLink_orig_breadCrumbs = window.onClickTiddlerLink;
window.onClickTiddlerLink = function(e){
	if (!e) {var e = window.event;}	
	window.onClickTiddlerLink_orig_breadCrumbs(e);
	addCrumb(e);
	return false;
};

function addCrumb(e){
	if (!e) {var e = window.event;}
	var thisCrumb = "[[" + resolveTarget(e).getAttribute("tiddlyLink") + "]]";
	var ind = config.breadCrumbs.find(thisCrumb);
	if(ind === null){
		config.breadCrumbs.push(thisCrumb);
	}
	else{
		config.breadCrumbs.length = ind++;
		}
	refreshCrumbs();
	return false;
}

function refreshCrumbs(){
	var crumbArea = document.getElementById("breadCrumbs");
	if (!crumbArea) {
		crumbArea = document.createElement("div");
		crumbArea.id = "breadCrumbs";
		crumbArea.style.visibility = "hidden";
		var targetArea = document.getElementById("tiddlerDisplay");
			targetArea = (targetArea === null )?document.getElementById("storyDisplay"):targetArea;
			targetArea.parentNode.insertBefore(crumbArea,targetArea);
	}
	crumbArea.style.visibility = "visible";
	removeChildren(crumbArea);
	createTiddlyButton(crumbArea,"Home",null,restartHome);
	wikify(" || " + config.breadCrumbs.join(' > '),crumbArea);
}

function restartHome(){
	story.closeAllTiddlers();
	restart();
	config.breadCrumbs = [];
	var crumbArea = document.getElementById("breadCrumbs");
	if (crumbArea) // ELS: added check to make sure crumbArea exists
		crumbArea.style.visibility = "hidden";
}
//}}}