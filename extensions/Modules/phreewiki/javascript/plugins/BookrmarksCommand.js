/***
!Metadata:
|''Name:''|BookmarksCommand|
|''Description:''|Toolbar button for bookmarks services|
|''Version:''|1.1.1|
|''Date:''|May 01, 2007|
|''Source:''|http://sourceforge.net/project/showfiles.php?group_id=150646|
|''Author:''|BramChen (bram.chen (at) gmail (dot) com)|
|''License:''|[[Creative Commons Attribution-ShareAlike 2.5 License]]|
|''~CoreVersion:''|2.2.0|
|''Browser:''|Firefox 1.5+; InternetExplorer 6.0|
!Usage:
*Manually add 'bookmarks' to parms of toolbar macro in ViewTemplate, if necessary..
*Add and/or modify the declarations of bookmark services predefined in 'BookmarkService.*'.
> add the name of services to 'Services' slice, each services seprated by a comma (,).
> add a slice named with sach service name, and the slice value is formed with the URLs of the services or.
!Revision History:
|''Version''|''Date''|''Note''|
|1.1.1|May 01, 2007|Improved RegExp of isPretyLink<br>Fixed query strings of Technorati<br>Added more default bookmarks services|
|1.1.0|Apr 22, 2007|Supported multi-lingo by using tiddler slice|
|1.0.1|Apr 20, 2007|Supported TiddlyWiki prety link markups and simple url form|
|1.0.0|Apr 19, 2007|Initial release|
!Code section:
***/
//{{{
//#config.options.chkUsedSel = false;
config.commands.bookmarks = {
	BookmarkServices: 'BookmarkServices',
	bsDefs: "!Decriptions of Params\n|!Params |%0|%1|%2|%3|%4|%5|\n|!Descriptions|title|url|selections|descriptions|rererence|tags|\n!Lingos of command button\n{{{\n''text:'' Bookmarks\n''tooltip:'' Bookmark this tiddlers to ...\n''popupNone:'' There are no bookmark services\n}}}\n!List of Services\n{{{\n''Services:'' Del.icio.us,Digg,Google,Yahoo,Furl,HemiDemi,MyShare,Baidu,Youpus,Technorati\n}}}\n!Definition of Services\n{{{\n''HemiDemi:''<br/>[[HemiDemi|http://www.hemidemi.com/user_bookmark/new?title=%0&url=%1&quotes=%2&description=%3&via=%4&tag_string=%5]]\n''MyShare:''<br/>[[MyShare|http://myshare.url.com.tw/index.php?func=newurl&from=mysharepop&url=%1&desc=%0&contents=%3]]\n''Baidu:''<br/>[[Baidu|http://cang.baidu.com/do/add?iu=%1&it=%0&dc=%3]]\n''Google:''<br/>[[Google|http://www.google.com/bookmarks/mark?op=add&title=%0&bkmk=%1&annotation=%3&labels=%5]]\n''Yahoo:''<br/>[[Yahoo|http://tw.myweb2.search.yahoo.com/myresults/bookmarklet?t=%1&u=%0&d=%3&ei=UTF-8]]\n''Del.icio.us:''<br/>[[Del.icio.us|http://del.icio.us/post?title=%0&url=%1&notes=%3&tags=%5]]\n''Digg:''<br/>[[Digg|http://digg.com/submit?phase=2&url=%0&title=%1&bodytext=%3]]\n''Technorati:''<br/>[[Technorati|http://technorati.com/faves?add=%1&title=%0]]\n''Furl:''<br/>[[Furl|http://www.furl.net/storeIt.jsp?t=%0&u=%1&r=%4&c=%2&p=1]]\n''Youpush:''<br/>[[Youpush|http://www.youpush.net/submit.php?url=%1]]\n}}}",
	text: "Bookmarks",
	tooltip:"Bookmark this tiddler to ...",
	popupNone: "There are no bookmark services",
	type: 'popup',
	chkToolbar:	function(text) {
		if(text)
			text = text.replace(/macro\=\'toolbar closeTiddler/g, 'macro=\'toolbar bookmarks closeTiddler');
		return text;
	},
	urlFormat: "<html><a href=\"%0\">%1</a><br/></html>",
	imgFormat: "<img title=\"%1\" src=\"%0\"></img>",
//	isPretyLink: /^\[.*\]\]/,
	isPretyLink: /\[[<>]?[Ii][Mm][Gg]\[|\[\[([^\]]+)\]\]/,
	imgSuffix: "_IMG",
	lingoNames: ['text', 'tooltip', 'popupNone']
};

config.commands.bookmarks.init = function(r) {
	if (!config.options.txtLocale) {
		config.options.txtLocale = config.locale ? config.locale : 'en';
	}
	var bs = this.BookmarkServices.replace(/\..*$/,'');
	bs = bs + '.' + config.options.txtLocale;

	if (!store.tiddlerExists(bs)){
		var tiddler = store.createTiddler(bs);
		store.setValue(tiddler,'text',this.bsDefs);
		store.setValue(tiddler,'modifier','BookmarksCommand');
	}
	this.BookmarkServices = bs;

	var lingo = store.getTiddlerSlices(this.BookmarkServices,this.lingoNames);
	if (!lingo) return false;
	for (i in lingo) {
		this[i] = lingo[i];
	}
	this.addToolbar('ViewTemplate');
	if(r) this.refreshUI(); // If BC works with other plugins, like as PopupTipsPlugin, to avoid refreshTiddler to be called multiple times.
};

config.commands.bookmarks.refreshUI = function() {
	story.forEachTiddler(function(title){story.refreshTiddler(title,DEFAULT_VIEW_TEMPLATE,true);});
};

config.commands.bookmarks.addToolbar = function(v) {
	if (store.tiddlerExists(v)){
		var tiddler = store.getTiddler(v);
		var text = tiddler.text;
		store.setValue(tiddler,'text',this.chkToolbar(text));
		store.setValue(tiddler,'modifier','BookmarksCommand');
	}
	else {
		if (store.isShadowTiddler(v))
			config.shadowTiddlers[v] = this.chkToolbar(config.shadowTiddlers[v]);
	}
};

config.commands.bookmarks.handlePopup = function(popup,title)
{
	var outputs={},imgSrc=null;
	var s = store.getTiddlerSlice(this.BookmarkServices,'Services');
	s=s?s.split(','):[];
	var services = store.getTiddlerSlices(this.BookmarkServices,s);
	for(var b in services) {
		var sLists = config.commands.bookmarks.getLists(title);
		outputs[b] = [null,null];
		outputs[b][0] = services[b].format(sLists);
		outputs[b][1] = store.getTiddlerSlice(this.BookmarkServices,b+this.imgSuffix);
	}
	var output='';

	for(var b in outputs) {
		output = outputs[b][0];
		var match = this.isPretyLink.exec(output);
		if (!match){
			imgSrc = (typeof outputs[b][1] == 'undefined')?b:this.imgFormat.format([outputs[b][1],b]);
			output = this.urlFormat.format([outputs[b][0],imgSrc]);
		}
		wikify(output,popup);
	}
};

config.commands.bookmarks.getLists = function(title)
{
	var t = encodeURIComponent(String.encodeTiddlyLink(title));
	if(document.location.hash != t)
		document.location.hash = t;
	t = encodeURIComponent(document.title + ' -- ') + t;
	var href = encodeURIComponent(document.location.href);
	var isShadowed = store.isShadowTiddler(title) && !store.tiddlerExists(title)
	var tiddler = store.getTiddler(title);
	var sel = config.commands.bookmarks.getSel();
	var desc = '';
	if (tiddler && tiddler.isTagged('systemConfig')){
		var isPlugin = true;
		var p = getPluginInfo(tiddler);
		desc = 'Author:' + p.Author + '-' + p.Description;
	}
	else
		desc = isShadowed?config.shadowTiddlers[title]:tiddler.text
	desc = config.options.chkUsedSel?sel:encodeURIComponent(desc);
	var via = config.commands.bookmarks.getRef();
	var tags = isShadowed?'':tiddler.tags.join(' ');
	tags = encodeURIComponent('TiddlyWiki'+ (isPlugin?'Plugin ':' ') + tags);
	return [t,href,sel,desc,via,tags];
};

config.commands.bookmarks.getSel = function(){
	var sel = '';
	if(window.getSelection) sel=window.getSelection();
	if(document.getSelection) sel=document.getSelection();
	if(document.selection) sel=document.selection.createRange().text;
	return sel;
};

config.commands.bookmarks.getRef = function(){
	var ref = '';
	if(document.referrer) ref=document.referrer;
	if(typeof(_ref)!='undefined') ref=_ref;
	return ref;
};
//# If this plugin installed as js file, to ensure it would be started after TW core has been loaded.
var bsInterval = setInterval(function(){if(formatter) {clearInterval(bsInterval); config.commands.bookmarks.init(true);};},100);
//}}}