/***
|Name|NestedSlidersPlugin|
|Source|http://www.TiddlyTools.com/#NestedSlidersPlugin|
|Version|2.3.1|
|Author|Eric Shulman - ELS Design Studios|
|License|http://www.TiddlyTools.com/#LegalStatements <<br>>and [[Creative Commons Attribution-ShareAlike 2.5 License|http://creativecommons.org/licenses/by-sa/2.5/]]|
|~CoreVersion|2.1|
|Type|plugin|
|Requires||
|Overrides|Slider.prototype.stop|
|Description|show content in nest-able 'slider' or 'floating' panels, without needing to create separate tiddlers for each panel|

!!!!!Configuration
<<<
Enable animation for slider panels
<<option chkFloatingSlidersAnimate>> allow sliders to animate when opening/closing
>(note: This setting is in //addition// to the general option for enabling/disabling animation effects:
><<option chkAnimate>> enable animations (entire document)
>For slider animation to occur, you must also allow animation in general.

Debugging messages for 'lazy sliders' deferred rendering:
<<option chkDebugLazySliderDefer>> show debugging alert when deferring slider rendering
<<option chkDebugLazySliderRender>> show debugging alert when deferred slider is actually rendered
<<<
!!!!!Usage
<<<
When installed, this plugin adds new wiki syntax for embedding 'slider' panels directly into tiddler content.  Use {{{+++}}} and {{{===}}} to delimit the slider content.  You can also 'nest' these sliders as deep as you like (see complex nesting example below), so that expandable 'tree-like' hierarchical displays can be created.  This is most useful when converting existing in-line text content to create in-line annotations, footnotes, context-sensitive help, or other subordinate information displays.

Additional optional syntax elements let you specify
*default to open
*cookiename
*heading level
*floater (with optional CSS width value)
*transient display (clicking elsewhere closes panel)
*custom class/label/tooltip/accesskey
*alternate label/tooltip (displayed when panel is open)
*panelID (for later use with {{{<<DOM>>}}} macro.  See [[DOMTweaksPlugin]])
*automatic blockquote style on panel
*deferred rendering of panel content
The complete syntax, using all options, is:
//{{{
++++(cookiename)!!!!!^width^*{{class{[label=key|tooltip][altlabel|alttooltip]}}}#panelID:>...
content goes here
===
//}}}
where:
* {{{+++}}} (or {{{++++}}}) and {{{===}}}<br>marks the start and end of the slider definition, respectively.  When the extra {{{+}}} is used, the slider will be open when initially displayed.
* {{{(cookiename)}}}<br>saves the slider opened/closed state, and restores this state whenever the slider is re-rendered.
* {{{!}}} through {{{!!!!!}}}<br>displays the slider label using a formatted headline (Hn) style instead of a button/link style
* {{{^width^}}} (or just {{{^}}})<br>makes the slider 'float' on top of other content rather than shifting that content downward.  'width' must be a valid CSS value (e.g., "30em", "180px", "50%", etc.).  If omitted, the default width is "auto" (i.e., fit to content)
* {{{"*"}}} //(without the quotes)//<br>denotes "transient display": when a click occurs elsewhere in the document, the slider/floating panel will be automatically closed.  This is useful for creating 'pulldown menus' that automatically go away after they are used.
* """{{class{[label=key|tooltip][altlabel|alttooltip]}}}"""<br>uses label/tooltip/accesskey.  """{{class{...}}}""", """=key""", """|tooltip""" and """[altlabel|alttooltip]""" are optional.  'class' is any valid CSS class name, used to style the slider label text.  'key' must be a ''single letter only''.  altlabel/alttooltip specifiy alternative label/tooltip for use when slider/floating panel is displayed.
* {{{#panelID:}}}<br>defines a unique DOM element ID that is assigned to the panel element used to display the slider content.  This ID can then be used later to reposition the panel using the {{{<<DOM move id>>}}} macro (see [[DOMTweaksPlugin]]), or to access/modify the panel element through use of {{{document.getElementById(...)}}}) javascript code in a plugin or inline script.
* {{{">"}}} //(without the quotes)//<br>automatically adds blockquote formatting to slider content
* {{{"..."}}} //(without the quotes)//<br>defers rendering of closed sliders until the first time they are opened.  //Note: deferred rendering may produce unexpected results in some cases.  Use with care.//

//Note: to make slider definitions easier to read and recognize when editing a tiddler, newlines immediately following the {{{+++}}} 'start slider' or preceding the {{{===}}} 'end slider' sequence are automatically supressed so that excess whitespace is eliminated from the output.//
<<<
!!!!!Examples
<<<
simple in-line slider: 
{{{
+++
   content
===
}}}
+++
   content
===
----
use a custom label and tooltip: 
{{{
+++[label|tooltip]
   content
===
}}}
+++[label|tooltip]
   content
===
----
content automatically blockquoted: 
{{{
+++>
   content
===
}}}
+++>
   content
===
----
all options combined //(default open, cookie, heading, sized floater, transient, class, label/tooltip/key, blockquoted, deferred)//
{{{
++++(testcookie)!!!^30em^*{{big{[label=Z|click or press Alt-Z to open]}}}>...
   content
===
}}}
++++(testcookie)!!!^30em^*{{big{[label=Z|click or press Alt-Z to open]}}}>...
   content
===
----
complex nesting example:
{{{
+++[get info...=I|click for information or press Alt-I]
	put some general information here,
	plus a floating panel with more specific info:
	+++^10em^[view details...|click for details]
		put some detail here, which could in turn contain a transient panel,
		perhaps with a +++^25em^*[glossary definition]explaining technical terms===
	===
===
}}}
+++[get info...=I|click for information or press Alt-I]
	put some general information here,
	plus a floating panel with more specific info:
	+++^10em^[view details...|click for details]
		put some detail here, which could in turn contain a transient panel,
		perhaps with a +++^25em^*[glossary definition]explaining technical terms===
	===
===
<<<
!!!!!Installation
<<<
import (or copy/paste) the following tiddlers into your document:
''NestedSlidersPlugin'' (tagged with <<tag systemConfig>>)
<<<
!!!!!Revision History
<<<
''2007.07.26 - 2.3.1'' in document.onclick(), propagate return value from hijacked core click handler to consume OR bubble up click as needed.  Fixes "IE click disease", whereby nearly every mouse click causes a page transition.
''2007.07.20 - 2.3.0'' added syntax for setting panel ID (#panelID:).  This allows individual slider panels to be repositioned within tiddler content simply by giving them a unique ID and then moving them to the desired location using the {{{<<DOM move id>>}}} macro.
''2007.07.19 - 2.2.0'' added syntax for alttext and alttip (button label and tooltip to be displayed when panel is open)
''2007.07.14 - 2.1.2'' corrected use of 'transient' attribute in IE to prevent (non-recursive) infinite loop
''2007.07.12 - 2.1.0'' replaced use of "*" for 'open/close on rollover' (which didn't work too well).  "*" now indicates 'transient' panels that are automatically closed if a click occurs somewhere else in the document.  This permits use of nested sliders to create nested "pulldown menus" that automatically disappear after interaction with them has been completed.  Also, in onClickNestedSlider(), use "theTarget.sliderCookie", instead of "this.sliderCookie" to correct cookie state tracking when automatically dismissing transient panels.
''2007.06.10 - 2.0.5'' add check to ensure that window.adjustSliderPanel() is defined before calling it (prevents error on shutdown when mouse event handlers are still defined)
''2007.05.31 - 2.0.4'' add handling to invoke adjustSliderPanel() for onmouseover events on slider button and panel.  This allows the panel position to be re-synced when the button position shifts due to changes in unrelated content above it on the page.  (thanks to Harsha for bug report)
''2007.03.30 - 2.0.3'' added chkFloatingSlidersAnimate (default to FALSE), so that slider animation can be disabled independent of the overall document animation setting (avoids strange rendering and focus problems in floating panels)
''2007.03.01 - 2.0.2'' for TW2.2+, hijack Morpher.prototype.stop so that "overflow:hidden" can be reset to "overflow:visible" after animation ends
''2007.03.01 - 2.0.1'' in hijack for Slider.prototype.stop, use apply() to pass params to core function
|please see [[NestedSlidersPluginHistory]] for additional revision details|
''2005.11.03 - 1.0.0'' initial public release
<<<
!!!!!Credits
<<<
This feature was implemented by EricShulman from [[ELS Design Studios|http:/www.elsdesign.com]] with initial research and suggestions from RodneyGomes, GeoffSlocock, and PaulPetterson.
<<<
!!!!!Code
***/
//{{{
version.extensions.nestedSliders = {major: 2, minor: 3, revision: 1, date: new Date(2007,7,26)};
//}}}

//{{{
// options for deferred rendering of sliders that are not initially displayed
if (config.options.chkDebugLazySliderDefer==undefined) config.options.chkDebugLazySliderDefer=false;
if (config.options.chkDebugLazySliderRender==undefined) config.options.chkDebugLazySliderRender=false;
if (config.options.chkFloatingSlidersAnimate==undefined) config.options.chkFloatingSlidersAnimate=false;

// default styles for 'floating' class
setStylesheet(".floatingPanel { position:absolute; z-index:10; padding:0.5em; margin:0em; \
	background-color:#eee; color:#000; border:1px solid #000; text-align:left; }","floatingPanelStylesheet");
//}}}

//{{{
config.formatters.push( {
	name: "nestedSliders",
	match: "\\n?\\+{3}",
	terminator: "\\s*\\={3}\\n?",
	lookahead: "\\n?\\+{3}(\\+)?(\\([^\\)]*\\))?(\\!*)?(\\^(?:[^\\^\\*\\[\\>]*\\^)?)?(\\*)?(?:\\{\\{([\\w]+[\\s\\w]*)\\{)?(\\[[^\\]]*\\])?(\\[[^\\]]*\\])?(?:\\}{3})?(\\#[^:]*\\:)?(\\>)?(\\.\\.\\.)?\\s*",
	handler: function(w)
		{
			lookaheadRegExp = new RegExp(this.lookahead,"mg");
			lookaheadRegExp.lastIndex = w.matchStart;
			var lookaheadMatch = lookaheadRegExp.exec(w.source)
			if(lookaheadMatch && lookaheadMatch.index == w.matchStart)
			{
				// var defopen=lookaheadMatch[1]
				// var cookiename=lookaheadMatch[2]
				// var header=lookaheadMatch[3]
				// var panelwidth=lookaheadMatch[4]
				// var transient=lookaheadMatch[5]
				// var class=lookaheadMatch[6]
				// var label=lookaheadMatch[7]
				// var openlabel=lookaheadMatch[8]
				// var panelID=lookaheadMatch[9]
				// var blockquote=lookaheadMatch[10]
				// var deferred=lookaheadMatch[11]

				// location for rendering button and panel
				var place=w.output;

				// default to closed, no cookie, no accesskey, no alternate text/tip
				var show="none"; var cookie=""; var key="";
				var closedtext=">"; var closedtip="";
				var openedtext="<"; var openedtip="";

				// extra "+", default to open
				if (lookaheadMatch[1]) show="block";

				// cookie, use saved open/closed state
				if (lookaheadMatch[2]) {
					cookie=lookaheadMatch[2].trim().slice(1,-1);
					cookie="chkSlider"+cookie;
					if (config.options[cookie]==undefined)
						{ config.options[cookie] = (show=="block") }
					show=config.options[cookie]?"block":"none";
				}

				// parse label/tooltip/accesskey: [label=X|tooltip]
				if (lookaheadMatch[7]) {
					var parts=lookaheadMatch[7].trim().slice(1,-1).split("|");
					closedtext=parts.shift();
					if (closedtext.substr(closedtext.length-2,1)=="=")	
						{ key=closedtext.substr(closedtext.length-1,1); closedtext=closedtext.slice(0,-2); }
					openedtext=closedtext;
					if (parts.length) closedtip=openedtip=parts.join("|");
					else { closedtip="show "+closedtext; openedtip="hide "+closedtext; }
				}

				// parse alternate label/tooltip: [label|tooltip]
				if (lookaheadMatch[8]) {
					var parts=lookaheadMatch[8].trim().slice(1,-1).split("|");
					openedtext=parts.shift();
					if (parts.length) openedtip=parts.join("|");
					else openedtip="hide "+openedtext;
				}

				var title=show=='block'?openedtext:closedtext;
				var tooltip=show=='block'?openedtip:closedtip;

				// create the button
				if (lookaheadMatch[3]) { // use "Hn" header format instead of button/link
					var lvl=(lookaheadMatch[3].length>6)?6:lookaheadMatch[3].length;
					var btn = createTiddlyElement(createTiddlyElement(place,"h"+lvl,null,null,null),"a",null,lookaheadMatch[6],title);
					btn.onclick=onClickNestedSlider;
					btn.setAttribute("href","javascript:;");
					btn.setAttribute("title",tooltip);
				}
				else
					var btn = createTiddlyButton(place,title,tooltip,onClickNestedSlider,lookaheadMatch[6]);
				btn.innerHTML=title; // enables use of HTML entities in label

				// set extra button attributes
				btn.setAttribute("closedtext",closedtext);
				btn.setAttribute("closedtip",closedtip);
				btn.setAttribute("openedtext",openedtext);
				btn.setAttribute("openedtip",openedtip);
				btn.sliderCookie = cookie; // save the cookiename (if any) in the button object
				btn.defOpen=lookaheadMatch[1]!=null; // save default open/closed state (boolean)
				btn.keyparam=key; // save the access key letter ("" if none)
				if (key.length) {
					btn.setAttribute("accessKey",key); // init access key
					btn.onfocus=function(){this.setAttribute("accessKey",this.keyparam);}; // **reclaim** access key on focus
				}
				btn.onmouseover=function(event) // mouseover on button aligns floater position with button
					{ if (window.adjustSliderPos) window.adjustSliderPos(this.parentNode,this,this.sliderPanel,this.sliderPanel.className); }

				// create slider panel
				var panelClass=lookaheadMatch[4]?"floatingPanel":"sliderPanel";
				var panelID=lookaheadMatch[9]; if (panelID) panelID=panelID.slice(1,-1); // trim off delimiters
				var panel=createTiddlyElement(place,"div",panelID,panelClass,null);
				panel.button = btn; // so the slider panel know which button it belongs to
				btn.sliderPanel=panel; // so the button knows which slider panel it belongs to
				panel.defaultPanelWidth=(lookaheadMatch[4] && lookaheadMatch[4].length>2)?lookaheadMatch[4].slice(1,-1):"";
				panel.setAttribute("transient",lookaheadMatch[5]=="*"?"true":"false");
				panel.style.display = show;
				panel.style.width=panel.defaultPanelWidth;
				panel.onmouseover=function(event) // mouseover on panel aligns floater position with button
					{ if (window.adjustSliderPos) window.adjustSliderPos(this.parentNode,this.button,this,this.className); }

				// render slider (or defer until shown) 
				w.nextMatch = lookaheadMatch.index + lookaheadMatch[0].length;
				if ((show=="block")||!lookaheadMatch[11]) {
					// render now if panel is supposed to be shown or NOT deferred rendering
					w.subWikify(lookaheadMatch[10]?createTiddlyElement(panel,"blockquote"):panel,this.terminator);
					// align floater position with button
					if (window.adjustSliderPos) window.adjustSliderPos(place,btn,panel,panelClass);
				}
				else {
					var src = w.source.substr(w.nextMatch);
					var endpos=findMatchingDelimiter(src,"+++","===");
					panel.setAttribute("raw",src.substr(0,endpos));
					panel.setAttribute("blockquote",lookaheadMatch[10]?"true":"false");
					panel.setAttribute("rendered","false");
					w.nextMatch += endpos+3;
					if (w.source.substr(w.nextMatch,1)=="\n") w.nextMatch++;
					if (config.options.chkDebugLazySliderDefer) alert("deferred '"+title+"':\n\n"+panel.getAttribute("raw"));
				}
			}
		}
	}
)

// TBD: ignore 'quoted' delimiters (e.g., "{{{+++foo===}}}" isn't really a slider)
function findMatchingDelimiter(src,starttext,endtext) {
	var startpos = 0;
	var endpos = src.indexOf(endtext);
	// check for nested delimiters
	while (src.substring(startpos,endpos-1).indexOf(starttext)!=-1) {
		// count number of nested 'starts'
		var startcount=0;
		var temp = src.substring(startpos,endpos-1);
		var pos=temp.indexOf(starttext);
		while (pos!=-1)  { startcount++; pos=temp.indexOf(starttext,pos+starttext.length); }
		// set up to check for additional 'starts' after adjusting endpos
		startpos=endpos+endtext.length;
		// find endpos for corresponding number of matching 'ends'
		while (startcount && endpos!=-1) {
			endpos = src.indexOf(endtext,endpos+endtext.length);
			startcount--;
		}
	}
	return (endpos==-1)?src.length:endpos;
}
//}}}

//{{{
window.onClickNestedSlider=function(e)
{
	if (!e) var e = window.event;
	var theTarget = resolveTarget(e);
	var theLabel = theTarget.firstChild.data;
	var theSlider = theTarget.sliderPanel
	var isOpen = theSlider.style.display!="none";

	// toggle label
	theTarget.innerHTML=isOpen?theTarget.getAttribute("closedText"):theTarget.getAttribute("openedText");
	// toggle tooltip
	theTarget.setAttribute("title",isOpen?theTarget.getAttribute("closedTip"):theTarget.getAttribute("openedTip"));

	// deferred rendering (if needed)
	if (theSlider.getAttribute("rendered")=="false") {
		if (config.options.chkDebugLazySliderRender)
			alert("rendering '"+theLabel+"':\n\n"+theSlider.getAttribute("raw"));
		var place=theSlider;
		if (theSlider.getAttribute("blockquote")=="true")
			place=createTiddlyElement(place,"blockquote");
		wikify(theSlider.getAttribute("raw"),place);
		theSlider.setAttribute("rendered","true");
	}
	// show/hide the slider
	if(config.options.chkAnimate && (theSlider.className!='floatingPanel' || config.options.chkFloatingSlidersAnimate))
		anim.startAnimating(new Slider(theSlider,!isOpen,e.shiftKey || e.altKey,"none"));
	else
		theSlider.style.display = isOpen ? "none" : "block";
	// reset to default width (might have been changed via plugin code)
	theSlider.style.width=theSlider.defaultPanelWidth;
	// align floater panel position with target button
	if (!isOpen && window.adjustSliderPos) window.adjustSliderPos(theSlider.parentNode,theTarget,theSlider,theSlider.className);
	// if showing panel, set focus to first 'focus-able' element in panel
	if (theSlider.style.display!="none") {
		var ctrls=theSlider.getElementsByTagName("*");
		for (var c=0; c<ctrls.length; c++) {
			var t=ctrls[c].tagName.toLowerCase();
			if ((t=="input" && ctrls[c].type!="hidden") || t=="textarea" || t=="select")
				{ ctrls[c].focus(); break; }
		}
	}
	var cookie=theTarget.sliderCookie;
	if (cookie && cookie.length) {
		config.options[cookie]=!isOpen;
		if (config.options[cookie]!=theTarget.defOpen)
			saveOptionCookie(cookie);
		else { // remove cookie if slider is in default display state
			var ex=new Date(); ex.setTime(ex.getTime()-1000);
			document.cookie = cookie+"=novalue; path=/; expires="+ex.toGMTString();
		}
	}
	return false;
}
//}}}

//{{{
// click in document background closes transient panels 
document.nestedSliders_savedOnClick=document.onclick;
document.onclick=function(ev) { if (!ev) var ev=window.event; var target=resolveTarget(ev);
	// call original click handler
	if (document.nestedSliders_savedOnClick)
		var retval=document.nestedSliders_savedOnClick.apply(this,arguments);
	// if click was inside transient panel (or something contained by a transient panel)... leave it alone
	var p=target;
	while (p)
		if ((p.className=="floatingPanel"||p.className=="sliderPanel")&&p.getAttribute("transient")=="true") break;
		else p=p.parentNode;
	if (p) return retval;
	// otherwise, find and close all transient panels...
	var all=document.all?document.all:document.getElementsByTagName("DIV");
	for (var i=0; i<all.length; i++) {
		 // if it is not a transient panel, or the click was on the button that opened this panel, don't close it.
		if (all[i].getAttribute("transient")!="true" || all[i].button==target) continue;
		// otherwise, if the panel is currently visible, close it by clicking it's button
		if (all[i].style.display!="none") window.onClickNestedSlider({target:all[i].button}) 
	}
	return retval;
};
//}}}

//{{{
// adjust floating panel position based on button position
if (window.adjustSliderPos==undefined) window.adjustSliderPos=function(place,btn,panel,panelClass) {
	if (panelClass=="floatingPanel") {
		var left=0;
		var top=btn.offsetHeight; 
		if (place.style.position!="relative") {
			var left=findPosX(btn);
			var top=findPosY(btn)+btn.offsetHeight;
			var p=place; while (p && p.className!='floatingPanel') p=p.parentNode;
			if (p) { left-=findPosX(p); top-=findPosY(p); }
		}
		if (findPosX(btn)+panel.offsetWidth > getWindowWidth())  // adjust position to stay inside right window edge
			left-=findPosX(btn)+panel.offsetWidth-getWindowWidth()+15; // add extra 15px 'fudge factor'
		panel.style.left=left+"px"; panel.style.top=top+"px";
	}
}

function getWindowWidth() {
	if(document.width!=undefined)
		return document.width; // moz (FF)
	if(document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
		return document.documentElement.clientWidth; // IE6
	if(document.body && ( document.body.clientWidth || document.body.clientHeight ) )
		return document.body.clientWidth; // IE4
	if(window.innerWidth!=undefined)
		return window.innerWidth; // IE - general
	return 0; // unknown
}
//}}}

//{{{
// TW2.1 and earlier:
// hijack Slider animation handler 'stop' handler so overflow is visible after animation has completed
Slider.prototype.coreStop = Slider.prototype.stop;
Slider.prototype.stop = function()
	{ this.coreStop.apply(this,arguments); this.element.style.overflow = "visible"; }

// TW2.2+
// hijack Morpher animation handler 'stop' handler so overflow is visible after animation has completed
if (version.major+.1*version.minor+.01*version.revision>=2.2) {
	Morpher.prototype.coreStop = Morpher.prototype.stop;
	Morpher.prototype.stop = function()
		{ this.coreStop.apply(this,arguments); this.element.style.overflow = "visible"; }
}
//}}}