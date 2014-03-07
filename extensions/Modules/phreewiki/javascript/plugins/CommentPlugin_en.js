/***
|''Name:''|CommentPlugin.en|
|''Source:''|[[TiddlyWiki-zh|http://tiddlywiki-zh.googlecode.com/svn/trunk/contributors/BramChen/locales/plugins/]]|
|''Requires:''|[[CommentPlugin]]|
|''Note:''|CommentPlugin 0.7.0+ modified by BramChen is required|
***/
//{{{
if (typeof config.CommentPlugin != 'undefined'){
		merge(config.CommentPlugin.CPlingo, {
		dateFormat: "DD MMM YYYY 0hh:0mm:0ss",
		CommentInTitle: " Comment ",
		comments: "comments",
		add: "New Comment Here...",
		edit: "Edit",
		tooltips:" Create a new comment tiddler associated with this tiddler",
		Title: "%0 Comment %1",
		CommenteditTemplate: {yourName: "Your Name: ", nickName: "(nick name)", comments: "Comment: "}
	});
	CPlingo = config.CommentPlugin.CPlingo;
	merge(config.macros.newCommentLink, {
 		label: CPlingo.add,
 		prompt: CPlingo.tooltips
	});
	merge(config.macros.comments, {
 		dateFormat: CPlingo.dateFormat
	});
config.CommentPlugin.only_on_tags.push(CPlingo.comments);
config.shadowTiddlers.CommentEditTemplate="<div class='toolbar' macro='toolbar +saveTiddler -cancelTiddler deleteTiddler wikibar'></div><div class='title' macro='view title'></div><div class='editor' macro='edit tags' style='display:none;'></div><div class='GuestSign' >" + CPlingo.CommenteditTemplate.yourName + "<span macro='option txtUserName'></span>" + CPlingo.CommenteditTemplate.nickName + "<br />" + CPlingo.CommenteditTemplate.comments + "</div><div class='editor' macro='edit text'></div>";
}
//}}}