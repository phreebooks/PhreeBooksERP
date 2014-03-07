/***

''This plugin was previously called StyleChooser.''

|Name|SelectThemePlugin|
|Created by|SimonBaird and SaqImtiaz|
|Location|http://tw.lewcid.org/#SelectThemePlugin|
|Version|1.2.5|
|Requires|~TW2.x|
!Description
*An alternative style switcher, can be used to switch just stylesheets and/or pagetemplates, or a combination of both (a theme)
*you can add your own stylesheets and pagetemplates, or use a ThemePack, like BigThemePack.

!Usage
* You have to have fetch or create some styleSheets and pageTemplates to use this plugin.
**You can either get a ThemePack like BigThemePack which automatically adds themes to ThemeSelect.
**or create tiddlers with styleSheets and pageTemplates and tag them styleSheets and pageTemplates respectively.
* Put {{{<<themeSelect style 'Select theme'>>}}} in your SideBarOptions.

!Creating Theme Packs
*You can create your own theme pack if you like. Instructions can be found [[here.|CreateThemePack]]

!History
*20-Dec-06, v 1.2.5, fixed horizontal rules for IE (thanks Clint), compatibility fix with HoverMenuPlugin
* 08-Sept-06, v1.2.4, fixed bug with TW2.1
* 15-May-06, v1.2.3, added paramifier so you can put theme on url, eg http://www.somewhere.com/twfile.html#theme:Berry2, thanks Clint (Simon).
* 28-Apr-o6, v1.2.2, fixed bug with opening TW after deleting themepacks. (Saq)
* 26-Apr-06, v1.2.1, more code optimization, dropdowns now updated on the fly. (Saq)
* 25-Apr-06, v1.2.0, added 3rd party ThemePack support, and made various other improvements.(Simon & Saq)
* 24-Apr-06, v1.1.0, added: no styles and default styles options,<<br>>support for ThemePack, support for tag variations(Saq)
* 21-Apr-06, v1.0.0, Reworked dropdowns to include option for pagetemplates (Saq)
* 21-Apr-06, v0.9.0, Rewrote and added Saq's lovely dropdown select (Simon)
* 20-Apr-06, v0.0.1, Basic switcher working (Simon)

!Examples
|!Source|!Output|h
|{{{<<themeSelect style>>}}} for a dropdown with StyleSheets|<<themeSelect style>>|
|{{{<<themeSelect pagetemplate>>}}} for a dropdown with PageTemplates|<<themeSelect pagetemplate>>|
|{{{<<themeSelect style customlabel>>}}} to use a customlabel|<<themeSelect style customlabel>>|
* When applying a stylesheet or template, it also looks for a template or stylesheet respectively based on naming convention, eg MyFunkyStyleSheet and MyFunkyPageTemplate.

!Notes
* See also http://www.tiddlytools.com/#SelectStyleSheetPlugin for a more feature-rich style sheet switcher

! Ideas
* do ViewTemplate also?
* Pretty up the [x] bit

!Code
***/
//{{{
// for compatibility with TW <2.0.9
if (!Array.prototype.contains)
   Array.prototype.contains = function(item)
   {
    return this.find(item) != null;
    };

// for compatibility with TW <2.0.9
if (!Array.prototype.containsAny)
   Array.prototype.containsAny = function(items)
   {
    for(var i=0; i<items.length; i++)
        if (this.contains(items[i]))
            return true;
    return false;
    };
//}}}

//{{{
version.extensions.SelectTheme = { major: 1, minor: 2, revision: 5, date: new Date(2006,12,20),
	source: "http://tw.lewcid.org/#SelectThemePlugin"
};

config.SelectTheme = {
	things: {
		style: {
			tag:        ["StyleSheets","StyleSheet","styleSheet","styleSheets","stylesheet","stylesheets"],
			theDefault: "StyleSheet",
			suffix:     "StyleSheet",
			notify:     refreshStyles,
			cookie:     "txtStyleSheet",
			otherThing: "pagetemplate",
			label:      "Choose StyleSheet: ",
			tooltip:     "Choose a StyleSheet",
			caseNone: { text:"None", title:"NoStyleSheet"},
                        caseDefault: { text:"Default", title:"StyleSheet" }

		},
		pagetemplate: {
			tag:        ["PageTemplates","PageTemplate","pageTemplates","pageTemplate","pagetemplate","pagetemplates"],
			theDefault: "PageTemplate",
			suffix:     "PageTemplate",
			notify:     refreshPageTemplate,
			cookie:     "txtPageTemplate",
			otherThing: "style",
			label: "Choose PageTemplate: ",
			tooltip:    "Choose a PageTemplate",
			caseNone: { text:"None", title:"NoPageTemplate"},
                        caseDefault: { text:"Default", title:"PageTemplate" }
		}

	},

                         specialCases: ["caseNone","caseDefault"]

};

TiddlyWiki.prototype.removeNotification = function(title,fn) {
	for (var i=0;i<this.namedNotifications.length;i++)
		if((this.namedNotifications[i].name == title) && (this.namedNotifications[i].notify == fn))
			this.namedNotifications.splice(i,1); // counting on it only being there once
};


var things = config.SelectTheme.things;
var specialCases=config.SelectTheme.specialCases;

for (var zz in things) {
	// make sure we have a value
	if (!config.options[things[zz].cookie])
		config.options[things[zz].cookie] = things[zz].theDefault;

	// remove core notify
	store.removeNotification(things[zz].theDefault,things[zz].notify);

	// and add our one
	store.addNotification(config.options[things[zz].cookie],things[zz].notify);

}

//checks to see if a tiddler exists in store or as a shadow.
TiddlyWiki.prototype.isTiddler= function (title){
	return store.tiddlerExists(title) || store.isShadowTiddler(title);
	};

//hijack core function & make sure template exists
window.applyPageTemplate_themeSelect=window.applyPageTemplate;
window.applyPageTemplate=function(title){
           if(!store.isTiddler(title))
                       {title = things.pagetemplate.theDefault;}
           applyPageTemplate_themeSelect(title);
 };

TiddlyWiki.prototype.makeActiveTheme = function(what,title,alsoCheckOtherThing) {

	var thing = things[what];
        if (!store.isTiddler(title))
		title = thing.theDefault;

	var oldTitle = config.options[thing.cookie];

	if (what == "style") {
		// remove old style element from DOM
		var oldStyleElement = document.getElementById(oldTitle);
		if (oldStyleElement) oldStyleElement.parentNode.removeChild(oldStyleElement);
	}

	store.removeNotification(oldTitle,thing.notify);
	store.addNotification(title,thing.notify);
	store.notify(title);

	config.options[thing.cookie] = title;
	saveOptionCookie(thing.cookie);
	if (alsoCheckOtherThing)
		this.makeActiveTheme(thing.otherThing,
				title.replace(new RegExp(thing.suffix+"$"),"") + things[thing.otherThing].suffix,
						false);
};

if (config.hoverMenu)
    {
    old_hovermenu_makeActiveTheme = TiddlyWiki.prototype.makeActiveTheme;
    TiddlyWiki.prototype.makeActiveTheme = function(what,title,alsoCheckOtherThing)
        {
         old_hovermenu_makeActiveTheme.apply(this,arguments);
         if (!alsoCheckOtherThing)
                    config.hoverMenu.handler();
        };
    }

config.shadowTiddlers.NoStyleSheet = config.shadowTiddlers.StyleSheet;
config.shadowTiddlers.NoPageTemplate = config.shadowTiddlers.PageTemplate;


function switchTheme(e){
         if (!e) var e = window.event;
         var theTarget = resolveTarget(e);
         var theLink = theTarget;
         var switchTo= theLink.getAttribute("switchTo");
         var mode = theLink.getAttribute("mode");
         if ((config.options[things[mode].cookie])!=switchTo)
               {store.makeActiveTheme(mode,switchTo,true);};
         return(false);
}


config.macros.themeSelect={};
config.macros.themeSelect.dropdownchar = (document.all?"▼":"▾");
config.macros.themeSelect.handler = function(place,macroName,params,wikifier,paramString,tiddler){
         var arrow = config.macros.themeSelect.dropdownchar;
         var mode = params[0];
         var label = (params[1]?params[1]:things[mode].label) + arrow;
         var cookie = (config.options[things[mode].cookie]);

         var onclick = function(e)
             { if (!e) var e = window.event;
             var popup = Popup.create(this);

             var tagged=[];

	     store.forEachTiddler(function(title,tiddler) {
                  if ((tiddler.tags).containsAny(things[mode].tag)){
					tagged.push(tiddler.title);}
	     });

             //integrate ThemePacks
	     if (config.themes) {
		     // see what themes have been loaded...
		     for (var i=0;i<config.themes.length;i++) {
			    // see if there is one
			    var lookForThis = config.themes[i] + things[mode].suffix;
			    if (store.isShadowTiddler(lookForThis)) {
				   tagged.pushUnique(lookForThis);
			    }
		    }
		     tagged = tagged.sort();
             }

             //this function used later to create buttons
             var createThemeButton = function(switchTo){
                        var theButton = createTiddlyButton(createTiddlyElement(popup,"li"),text,null,switchTheme,useClass);
                        theButton.setAttribute("switchTo",switchTo);
                        theButton.setAttribute("mode",mode);};

            //create Buttons for None(shadow styles) & Default (StyleSheet)
                     // Default button is not created if StyleSheet doesnt exist.
             for(var t=0; t<specialCases.length; t++){
             var special = specialCases[t];
             var text = things[mode][special].text;
             var useClass = "tiddlyLinkExisting";   //redundant, optimize!
             if ((things[mode][special].title==cookie)||(special=="caseNone"&&!store.isTiddler(cookie)))
                      {text+= " [x]";
                      useClass = "currentlySelected";}
             if (!((special=="caseDefault")&&(!store.getTiddler(things[mode][special].title))))
             createThemeButton(things[mode][special].title);     }

             //insert horizontal rule
             //createTiddlyElement(createTiddlyElement(popup,"li"),"hr");
             createTiddlyElement(createTiddlyElement(popup,"li",null,"listBreak"),"div");

             //create buttons for all other stylesheet tiddlers
             for(var t=0; t<tagged.length; t++)
                     { var useClass = "tiddlyLinkExisting";
                       var text = (tagged[t]).replace((things[mode].suffix),"");
                     if (tagged[t]==(cookie) )
                           {text+=" [x]"; useClass="currentlySelected";}
                     if ((tagged[t]!= (things[mode].theDefault))&&tagged[t]!= (things[mode].none))
                        {createThemeButton(tagged[t]);}}
             Popup.show(popup,false);
             e.cancelBubble = true;
             if (e.stopPropagation)
                e.stopPropagation();
             return(false);
             };

        var createdropperButton = function(place){
           var sp = createTiddlyElement(place,"span",null,"ThemeChooserButton");
           var theDropDownBtn = createTiddlyButton(sp,label,things[mode].tooltip,onclick);
        };

        createdropperButton(place);
};


setStylesheet(".popup li a.currentlySelected {background:#ccc;color:black;font-weight:bold;}","currentlySelectedStyle"); // could do better probably...

config.macros.layoutChooser=config.macros.themeSelect;

//shadow tiddler to hold instructions for creating ThemePacks
config.shadowTiddlers.ThemePack='See http://simonbaird.com/mptw/#CreateThemePack'; 

config.macros.applyTheme = {handler: function (place,macroName,params,wikifier,paramString,tiddler) {
	var theme = params[0];
	var label = params[1]?params[1]:'Apply theme "' + theme + '"';
        var tooltip = 'Apply the "'+theme+'" theme to this TiddlyWiki';
	createTiddlyButton(place,label,tooltip,function() {
		store.makeActiveTheme("style",theme+things.style.suffix,true);
	});
}};


// this means you can put #theme:ThemeName in url. suggested by Clint
config.paramifiers.theme = {
	onstart: function(themeName) {
		store.makeActiveTheme("style",themeName+config.SelectTheme.things.style.suffix,true);
	}
};

//}}}