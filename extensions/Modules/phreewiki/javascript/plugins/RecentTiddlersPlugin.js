/***
!Metadata:
|''Name:''|RecentTiddlersPlugin|
|''Source:''|http://sourceforge.net/project/showfiles.php?group_id=150646|
|''Author:''|BramChen (bram.chen (at) gmail (dot) com)|
|''Version:''|1.1.1|
|''Date:''|Aug 28, 2007|
|''License:''|[[Creative Commons Attribution-ShareAlike 3.0 License|http://creativecommons.org/licenses/by-sa/3.0/]]|
|''~CoreVersion:''|2.1.0|
|''Description:''|Display DefaultTiddlers and recently modified tiddlers at startup.|
|''Browser:''|Firefox 1.5+; InternetExplorer 6.0; Opera|
!Usage:
>Just to set the options by changing the slice values in RecentTiddlersOptions, if needed.
!Revision History:
|''Version''|''Date''|''Note''|
|1.1.1|Aug 28, 2007|No more hijeck core restart()|
|1.1.0|Aug 25, 2007|Easy to change the options by using tiddler slices, defined in RecentTiddlersOptions|
|1.0.0|Apr 18, 2007|Initial release, codes reworked from Tim Morgan's RecentPlugin |
!Code section:
***/
//{{{
version.extensions.recentTiddlers = {major: 1, minor: 1, revision: 1, date: new Date("Aug 28, 2007")};

config.recentTiddlers = {
	maxNums: 5,
	includeTags: ['*'],
	excludeTags: ['systemConfig','systemTiddlers', 'excludeLists']
};

config.shadowTiddlers.RecentTiddlersOptions = 'maxNums: 5\nincludeTags: *\nexcludeTags: systemConfig,systemTiddlers,excludeLists';

config.recentTiddlers.getRecents = function (){
	var c = store.getTiddlerSlices('RecentTiddlersOptions',['maxNums','includeTags','excludeTags']);
	var maxNums = (c.maxNums) ?	parseInt(c.maxNums) : config.recentTiddlers.maxNums;
	var includeTags = (c.includeTags) ? c.includeTags.split(',') : config.recentTiddlers.includeTags;
	var excludeTags = (c.excludeTags) ? c.excludeTags.split(',') : config.recentTiddlers.excludeTags;
	var CPlingo =  (config.CommentPlugin !== undefined)?config.CommentPlugin.CPlingo:null;
	var rs = store.getTiddlerText("DefaultTiddlers").readBracketedList();
	var tiddlers = store.getTiddlers("modified");
	var n = tiddlers.length -1 - maxNums;

	for (var t=tiddlers.length-1; t>n && t>0; t--){
		if(CPlingo !== null && tiddlers[t].isTagged(CPlingo.comments)) {
			var tt = tiddlers[t].title.split(CPlingo.CommentInTitle)[0];
			if(store.tiddlerExists(tt))
				rs.pushUnique(tt);
		}
		else {
			if (tiddlers[t].tags.containsAny(excludeTags))
				n--;
			else {
				if (includeTags.length == 0 || includeTags[0] == '*' || tiddlers[t].tags.containsAny(includeTags))
				rs.pushUnique(tiddlers[t].title);
			}
		}
	}
	return rs;
};

if(!window.location.hash)
	var recentInterval = setInterval(function(){if(story) {clearInterval(recentInterval); story.closeAllTiddlers();story.displayTiddlers(null,config.recentTiddlers.getRecents());};},100);

//}}}