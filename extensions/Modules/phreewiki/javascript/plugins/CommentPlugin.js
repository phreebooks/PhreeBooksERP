/***
|''Name:''|CommentPlugin|
|''Source:''|http://sourceforge.net/project/showfiles.php?group_id=150646|
|''Author:''|Tim Morgan (modified by Bram Chen|
|''Version:''|1.0.0|
|''Date:''|Aug 25, 2007|
|''License:''|[[Creative Commons Attribution-ShareAlike 3.0 License|http://creativecommons.org/licenses/by-sa/3.0/]]|
|''~CoreVersion:''|2.0.11|
|''Description:''|Adds "comments" to any TiddlyWiki or adaptation.|
|~|Used in conjunction with the RecentPlugin, one can have a decent forum environment.|

''Translation sample 1:''
{{{
config.CommentPlugin.CPlingo = {
	dateFormat: "YYYY年0MM月0DD日 0hh:0mm:0ss",
	CommentInTitle: " 迴響 ",
	comments:"迴響",
	add:"回應 »",
	edit:"編輯",
	tooltips:"發表關於此文的相關意見",
	Title: "%0 迴響 %1",
	CommenteditTemplate: {yourName: "請簽名：", nickName: "(中英文暱稱)", comments: "留言內容："}
};
}}}
''Translation sample 2:''
{{{
config.CommentPlugin = {
 CPlingo:{
	dateFormat: "DD MMM YYYY 0hh:0mm:0ss",
	CommentInTitle: " Comment ",
	comments: "comments",
	add: "New Comment Here...",
	edit: "Edit",
	tooltips:" Create a new comment tiddler associated with this tiddler",
	Title: "%0 Comment %1",
	CommenteditTemplate: {yourName: "Your Name: ", nickName: "(nick name)", comments: "Comment: "}
 };
}}}

''Revision history:''
* v1.0.0
** Fixed bug, those tiddlers tagging with some other tiddlers and not tagged with only_on_tags would also be created a comment links with count 0.
* v0.8.0 (Jan 17, 2007)
** Some minor changes and bugs fixed (Bram)
* v0.7.0 (Nov 09, 2006)
** Minor changes, more easier to be translated (Bram)
* v0.6.0 (Nov 09, 2006)
** Runs compatibly with TW 2.1.0+ (Bram)
* v0.5.0 (Jun 15, 2006)
** Fixed bug for feature of CommentEditTemplate (bug reported by MilchFlasche, fixed by Bram)
** Fixed bug in redefined TiddlyWiki.prototype.saveTiddler (Bram)
* v0.4.0 (Jun 03, 2006) Added CommentEditTemplate (Bram)
* v0.3.0 (Jun 01, 2006) Some minor changes for readOnly mode (Bram)
* v0.2.0 (Apr 04, 2006) Fixed bug for only_on_tags (Bram)
* v0.1.0 (Mar 13, 2006) Modified by Bram Chen.
***/
// //''Code section:''
//{{{
config.CommentPlugin = {
 CPlingo:{
	dateFormat: "DD MMM YYYY 0hh:0mm:0ss",
	CommentInTitle: " Comment ",
	comments: "comments",
	add: "New Comment Here...",
	edit: "Edit",
	tooltips: "Create a new comment tiddler associated with this tiddler",
	Title: "%0 Comment %1",
	CommenteditTemplate: {yourName: "Your Name: ", nickName: "(nick name)", comments: "Comment: "}
 },
 only_on_tags: ['Public'],
 not_on_tags: ['about'],
 // "true" or "false"...
 fold_comments: true,
 default_fold: true,
 max_comment_count: 500
};

var CPlingo = config.CommentPlugin.CPlingo;
config.CommentPlugin.only_on_tags.push(CPlingo.comments);

function get_parent(tiddler){
	while(tiddler.isTagged(CPlingo.comments)){
		tiddler=store.fetchTiddler(tiddler.tags[0]);
	}
	return tiddler
};

function count_comments(title){
	var tagged=store.getTaggedTiddlers(title);
	var count=0;
	for(var i=0;i<tagged.length;i++){
		if(tagged[i].tags.contains(CPlingo.comments)){
			count+=count_comments(tagged[i].title)+1;
		}
	}
	return count
};
config.shadowTiddlers.ViewTemplate += "\n<div class='comments' macro='comments'></div>";

config.shadowTiddlers.CommentEditTemplate="<div class='toolbar' macro='toolbar +saveTiddler -cancelTiddler deleteTiddler wikibar'></div><div class='title' macro='view title'></div><div class='editor' macro='edit tags' style='display:none;'></div><div class='GuestSign' >" + CPlingo.CommenteditTemplate.yourName + "<span macro='option txtUserName'></span>" + CPlingo.CommenteditTemplate.nickName + "<br />" + CPlingo.CommenteditTemplate.comments + "</div><div class='editor' macro='edit text'></div>";
config.tiddlerTemplates[3]="CommentEditTemplate";	
var COMMENT_EDIT_TEMPLATE = 3;

config.shadowTiddlers.CommentPluginStyle = '\n/*{{{*/\n.commentTags ul {list-style:none; padding-left:0px; margin: 0 0 3px 0;}\n.commentTags li {display:inline; color:#999;}\n.commentTags li a.button {color:#999;}\n.comment {border-left:1px solid #ccc; margin-top:10px; margin-left:10px; padding:5px;}\n.newCommentLink {padding-top:10px}\n.tagging, .selected .tagging, .tiddler .tagging {display:none;}\n.comment a.button {padding:0px; font-size:smaller; background-color:lightgray;}\n.comments a.button {background-color:lightgray;}\n/*}}}*/';
config.shadowTiddlers.StyleSheet += '\n[[CommentPluginStyle]]';
config.macros.newCommentLink = {
	label: CPlingo.add,
	prompt: CPlingo.tooltips,
	handler: function(place,macroName,params,wikifier,paramString,tiddler) {
		if(tiddler && store.tiddlerExists(tiddler.title) && !readOnly && (!window.zw || zw.loggedIn || zw.anonEdit)) {
			if(tiddler.tags.containsAny(config.CommentPlugin.not_on_tags) || !tiddler.tags.containsAny(config.CommentPlugin.only_on_tags))
				return;
			var onclick = function(e) {
				var e = (e)?e:window.event;
				var theTarget = resolveTarget(e);
				var tagxs = tiddler.title.split(CPlingo.CommentInTitle);
				var title = (tiddler.title.indexOf(CPlingo.CommentInTitle)!=-1)? tagxs[0] : tiddler.title;
				title = CPlingo.Title.format([title,(new Date()).formatString(CPlingo.dateFormat)]);
				var comment = store.createTiddler(title);
				comment.text = '';
				comment.tags = [tiddler.title, CPlingo.comments,'excludeLists'];
				readOnly = false;
				story.displayTiddler(theTarget, title, COMMENT_EDIT_TEMPLATE);
				readOnly = (window.location.protocol == "file:") ? false : config.options.chkHttpReadOnly;
				story.focusTiddler(title,"text");
				return false;
			}
			createTiddlyButton(place, this.label, this.prompt, onclick);
 		}
	}
};
config.macros.comments = {
	dateFormat: CPlingo.dateFormat,
	handler: function(place,macroName,params,wikifier,paramString,tiddler) {
	if(tiddler.title==CPlingo.comments) return;
	var comments = store.getTaggedTiddlers(tiddler.title, 'created');
	var count = count_comments(tiddler.title);
	if(count>0 && !tiddler.tags.contains(CPlingo.comments) && config.CommentPlugin.fold_comments) {
		var show = createTiddlyElement(place, 'p');
		show.innerHTML = '<a href="#" onclick="var e=document.getElementById(\'comments'+tiddler.title+'\');e.style.display=e.style.display==\'block\'?\'none\':\'block\';return false;">' + CPlingo.comments +'('+count+') »</a>';
	}
	var place = createTiddlyElement(place, 'div', 'comments'+tiddler.title, 'comments');
	if(count>0 && !tiddler.tags.contains(CPlingo.comments) && config.CommentPlugin.fold_comments && config.CommentPlugin.default_fold)
		place.style.display = 'none';
	else
		place.style.display = 'block';
	for(var i=0; i<comments.length; i++) {
		if(!comments[i].tags.contains(CPlingo.comments))continue;
		var container = createTiddlyElement(place, 'div', null, 'comment');
		var title = createTiddlyElement(container, 'strong');
		var link = createTiddlyLink(title, comments[i].modifier, true);
		createTiddlyElement(title, 'span', null, null, ', '+comments[i].created.formatString(this.dateFormat));
/* ## remove editable option for security concern
		if(comments[i].modifier == config.options.txtUserName) {
			createTiddlyElement(title, 'span', null, null, ' (');
			var edit = createTiddlyLink(title, comments[i].title);
			edit.innerHTML = CPlingo.edit;
			createTiddlyElement(title, 'span', null, null, ')');
		}
*/
		wikify('\n'+comments[i].text+'\n',container);
		config.macros.comments.handler(container,null,null,null,null,comments[i]);
	}
	readOnly = false;
	config.macros.newCommentLink.handler(place,null,null,null,null,tiddler);
//	wikify('<'+'<newCommentLink>>',place);
	readOnly = (window.location.protocol == "file:") ? false : config.options.chkHttpReadOnly;
	}
};
var CPCloseTiddlers = [];
TiddlyWiki.prototype.CommentPlugin_saveTiddler = TiddlyWiki.prototype.saveTiddler;
TiddlyWiki.prototype.saveTiddler = function(title,newTitle,newBody,modifier,modified,tags) {
	tags=(!window.zw && typeof tags == "string") ? tags.readBracketedList() : tags;
	if(tags.contains(CPlingo.comments)){
		newBody=newBody.htmlDecode(); // comment this line, for working with HTMLAreaPackage
		newBody=newBody.substr(0,config.CommentPlugin.max_comment_count);
		newBody=newBody.htmlEncode(); // comment this line, for working with HTMLAreaPackage
	}
	var t = this.CommentPlugin_saveTiddler(title,newTitle,newBody,modifier,modified,tags);
	if(tags.contains(CPlingo.comments)) {
		var original = config.CommentPlugin.default_fold;
		config.CommentPlugin.default_fold = false;
//		story.refreshTiddler(get_parent(t).title, DEFAULT_VIEW_TEMPLATE, true);
		story.refreshTiddler(t.tags[0].split(CPlingo.CommentInTitle)[0], DEFAULT_VIEW_TEMPLATE, true);
		config.CommentPlugin.default_fold = original;
		CPCloseTiddlers.push(newTitle);
		setTimeout("story.closeTiddler(CPCloseTiddlers.pop(), true)", 500);
	}
	return t;
};
Story.prototype.chooseTemplateForTiddler = function(title,template)
{
	if(!template)
		template = DEFAULT_VIEW_TEMPLATE;
	if(template == DEFAULT_VIEW_TEMPLATE
	|| template == DEFAULT_EDIT_TEMPLATE
	|| template == COMMENT_EDIT_TEMPLATE)
		template = config.tiddlerTemplates[template];
	return template;
};
//}}}