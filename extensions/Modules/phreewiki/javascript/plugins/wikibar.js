/***
|''Name:''|WikiBar|
|''Version:''|2.0.0 beta3|
|''Source:''|[[AiddlyWiki|http://aiddlywiki.sourceforge.net]]|
|''Author:''|[[Arphen Lin|mailto:arphenlin@gmail.com]]|
|''Type:''|toolbar macro command extension|
|''Required:''|TiddlyWiki 2.0.0 beta6|
!Description
WikiBar is a toolbar that gives access to most of TiddlyWiki's formatting features with a few clicks. It's a handy tool for people who are not familiar with TiddlyWiki syntax.
Besides, with WikiBar-addons, users can extend the power of WikiBar.
!Support browser
*Firefox 1.5
!Revision history
*v2.0.0 beta3 (2005/12/30)
** remove macros (replaced by TWMacro addon)
** add wikibar command in toolbar automatically
** rename DOIT to HANDLER
** rename TIP to TOOLTIP
*v2.0.0 beta2 (2005/12/21)
** re-design Wikibar addon framework
*v2.0.0 beta1 (2005/12/14)
** Note:
*** WikiBarPlugin is renamed to WikiBar
** New Features:
*** support TiddlyWiki 2.0.0 template mechanism
*** new wikibar data structure
*** new wikibar-addon framework for developers
**** support dynamic popup menu generator
*** support most new macros added in TiddlyWiki 2.0.0
*** multi-level popup menu
*** fix wikibar tab stop
*** remove paletteSelector
** Known Bugs:
*** popup-menu and color-picker can't be closed correctly
*** some macros can't be displayed correctly in previewer
*** text in previewer will be displayed italic
*v1.2.0 (2005/11/21)
**New Features:
***User defined color palettes supported
####Get color palettes from [[ColorZilla Palettes|http://www.iosart.com/firefox/colorzilla/palettes.html]].
####Save the palette file(*.gpl) as a new tiddler and tag it with 'ColorPalettes', then you can use it in WikiBar.
***WikiBar style sheet supported
***Click on document to close current colorPicker, paletteSelector or aboutWikibar
*v1.1.1 (2005/11/03)
**Bugs fixed:
***'Not enough parameters!' message is displayed when the parameter includes '%+number', ex: 'hello%20world!'
*v1.1.0 (2005/11/01)
**Bugs fixed:
***WikiBar overruns (reported by by GeoffS <gslocock@yahoo.co.uk>)
**New features:
***Insert a color code at the cursor. (Thanks to RunningUtes <RunningUtes@gmail.com>)
***Enable gradient macro. (Thanks to RunningUtes <RunningUtes@gmail.com>)
***Insert tiddler comment tags {{{/% ... %/}}}. (new feature supported by TiddlyWiki 1.2.37)
***Insert DateFormatString for {{{<<today>>}}} macro. (new feature supported by TiddlyWiki 1.2.37)
**Enhanced:
***Allow optional parameters in syntax.
**Bugs:
***'Not enough parameters!' message is displayed when the parameter includes '%+number', ex: 'hello%20world!'
*v1.0.0 (2005/10/30)
**Initial release
!Code
***/
//{{{
config.macros.wikibar = {major: 2, minor: 0, revision: 0, beta: 3, date: new Date(2005,12,30)};
config.macros.wikibar.handler = function(place,macroName,params,wikifier,paramString,tiddler){
  if(!(tiddler instanceof Tiddler))  {return;}
	story.setDirty(tiddler.title,true);
  place.id = 'wikibar'+tiddler.title;
  place.className = 'toolbar wikibar';
};
function wikibar_install(){
  config.commands.wikibar = {
  	text: 'wikibar',
  	tooltip: 'wikibar on/off',
  	handler: function(e,src,title) {
      if(!e){ e = window.event; }
      var theButton = resolveTarget(e);
      theButton.id = 'wikibarButton'+title;
      wikibarPopup.remove();
      wikibar_installAddons(theButton, title);
      wikibar_createWikibar(title);
      return(false);
    }
  };
  config.shadowTiddlers['EditTemplate'] = wikibar_addWikibarCommand(config.shadowTiddlers['EditTemplate']);
  var tiddler = store.getTiddler('EditTemplate');
  if(tiddler){
    tiddler.text = wikibar_addWikibarCommand(tiddler.text);
  }
}
function wikibar_installAddons(theButton, title){
 	var tiddlers = store.getTaggedTiddlers('wikibarAddons');
	if(!tiddlers)	  { return; }
	theButton.addons=[];
  for(var i=0; i<tiddlers.length; i++){
    try{
      eval(tiddlers[i].text);
      try{
        wikibar_addonInstall(title);
        wikibar_addonInstall = null;
        theButton.addons.push({ok:true, name:tiddlers[i].title});
      }catch(ex){
        theButton.addons.push({ok:false, name:tiddlers[i].title, error:ex});
      }
    }catch(ex){
      theButton.addons.push({ok:false, name:tiddlers[i].title, error:ex});
    }
  }
}
function wikibar_addWikibarCommand(tiddlerText){
  var div = document.createElement('div');
  div.style.display = 'none';
  div.innerHTML = tiddlerText;
  for(var i=0; i<div.childNodes.length; i++){
    var o=div.childNodes[i];
    if(o.tagName==='DIV'){
      if(o.className=='toolbar'){
        var macroText = o.getAttribute('macro').trim();
        if(macroText.search('wikibar')<=0){
          macroText += ' wikibar';
          o.setAttribute('macro', macroText);
        }
        break;
      }
    }
  }
  return div.innerHTML.replace(/\"/g, "\'");
}
function wikibar_processSyntaxParams(theSyntax, params){
  try{
    var pcr = 'AplWikibarPcr';
    var rx=null;
    var allParams=null;
    if(params){
      if(typeof(params)=='object'){
        for(var i=0; i<params.length; i++){
          if(params[i]){
            params[i] = params[i].replace(new RegExp('%','g'), pcr).trim();
            rx = '(\\[%'+(i+1)+'\\])' + '|' + '(%'+(i+1)+')';
            theSyntax = theSyntax.replace(new RegExp(rx,'g'), params[i] );
          }
        }
        allParams = params.join(' ').trim();
      }else{
        allParams = params.replace(new RegExp('%','g'), pcr).trim();
        rx = /(\[%1{1}\])|(%1{1})/g;
        theSyntax = theSyntax.replace(rx, allParams);
      }
    }
    if(allParams){
      theSyntax = theSyntax.replace(new RegExp('%N{1}','g'), allParams);
    }
    rx=/\[%(([1-9]{1,}[0-9]{0,})|(N{1}))\]/g;
    theSyntax = theSyntax.replace(rx, '');
    rx=/%(([1-9]{1,}[0-9]{0,})|(N{1}))/g;
    if( theSyntax.match(rx) ){
      throw 'Not enough parameters! ' + theSyntax;
    }
    theSyntax=theSyntax.replace(new RegExp(pcr,'g'), '%');
    return theSyntax;
  } catch(ex){
    return null;
  }
}
function wikibar_resolveEditItem(tiddlerWrapper, itemName){
  if(tiddlerWrapper.hasChildNodes()){
    var c=tiddlerWrapper.childNodes;
    for(var i=0; i<c.length; i++){
      var txt=wikibar_resolveEditItem(c[i], itemName);
      if(!txt){
        continue;
      }else{
        return txt;
      }
    }
  }
  return ((tiddlerWrapper.getAttribute && tiddlerWrapper.getAttribute('edit')==itemName)? tiddlerWrapper : null);
}
function wikibar_resolveEditItemValue(tiddlerWrapper, itemName){
  var o = wikibar_resolveEditItem(tiddlerWrapper, itemName);
  return (o? o.value.replace(/\r/mg,'') : null);
}
function wikibar_resolveTiddlerEditorWrapper(obj){
  if(obj.id=='tiddlerDisplay'){return null;}
  if((obj.getAttribute && obj.getAttribute('macro')=='edit text')){return obj;}
  return wikibar_resolveTiddlerEditorWrapper(obj.parentNode);
}
function wikibar_resolveTiddlerEditor(obj){
  if(obj.hasChildNodes()){
    var c = obj.childNodes;
    for(var i=0; i<c.length; i++){
      var o=wikibar_resolveTiddlerEditor(c[i]);
      if(o){ return o;}
    }
  }
  return ((obj.getAttribute && obj.getAttribute('edit')=='text')? obj : null);
}
function wikibar_resolveTargetButton(obj){
  if(obj.id && obj.id.substring(0,7)=='wikibar'){ return null; }
  if(obj.tiddlerTitle){
    return obj;
  }else{
    return wikibar_resolveTargetButton(obj.parentNode);
  }
}
function wikibar_isValidMenuItem(tool){
  if(!tool){  return false; }
  if(tool.TYPE=='MENU' || tool.TYPE=='MAIN_MENU'){
    for(var key in tool){
      if(key.substring(0,8)=='DYNAITEM'){ return true; }
      if(wikibar_isValidMenuItem(tool[key])){ return true; }
    }
    return false;
  }else{
    return (tool.HANDLER? true : false);
  }
}
function wikibar_editFormat(param){
  var editor = param.button.editor;
  var params = param.params;
  clearMessage();
  if(!editor){ return; }
  var repText = wikibar_processSyntaxParams(this.syntax, params);
  if(repText===null){ return; }
	var st = editor.scrollTop;
	var ss = editor.selectionStart;
	var se = editor.selectionEnd;
	var frontText= '';
	var endText  = '';
	var fullText = editor.value;
	if(se>ss && ss>=0){
	  frontText  = fullText.substring(0, ss);
	  endText    = fullText.substring(se, fullText.length);
	}
	else if(ss===0 && (se===0 || se == fullText.length) ){
    endText    = fullText;
	}
	else if(se==ss && ss>0){
    frontText  = fullText.substring(0, ss);
    endText    = fullText.substring(se, fullText.length);
	}
	if(repText.indexOf('user_text')>=0 && this.hint){
		repText = repText.replace('user_text', this.hint);
	}
	editor.value = frontText + repText + endText;
	editor.selectionStart = ss;
	editor.selectionEnd   = ss + repText.length;
	editor.scrollTop      = st;
	editor.focus();
}
function wikibar_editFormatByWord(param){
  var editor = param.button.editor;
  var params = param.params;
  clearMessage();
  if(!editor){return;}
  var repText = wikibar_processSyntaxParams(this.syntax, params);
  if(repText===null){ return; }
	var st = editor.scrollTop;
	var ss = editor.selectionStart;
	var se = editor.selectionEnd;
	var frontText= '';
	var selText  = '';
	var endText  = '';
	var fullText = editor.value;
	if(se>ss && ss>=0){
	  frontText  = fullText.substring(0, ss);
	  selText	   = fullText.substring(ss,se);
	  endText    = fullText.substring(se, fullText.length);
	}
	else if(ss===0 && (se===0 || se == fullText.length) ){
    endText    = fullText;
	}
	else if(se==ss && ss>0){
    frontText  = fullText.substring(0, ss);
    endText    = fullText.substring(se, fullText.length);
	  if(!( fullText.charAt(ss-1).match(/\W/gi) || fullText.charAt(ss).match(/\W/gi) )){
      var m = frontText.match(/\W/gi);
      if(m){
        ss = frontText.lastIndexOf(m[m.length-1])+1;
      }
      else{
        ss = 0;
      }
      m = endText.match(/\W/gi);
      if(m){
        se += endText.indexOf(m[0]);
      }
      else{
        se = fullText.length;
      }
      frontText = fullText.substring(0, ss);
  	  endText   = fullText.substring(se, fullText.length);
  	  selText   = fullText.substring(ss,se);
	  }
	}
	if(selText.length>0){
		repText = repText.replace('user_text', selText);
	}
	if(repText.indexOf('user_text')>=0 && this.hint){
		repText = repText.replace('user_text', this.hint);
	}
	editor.value = frontText + repText + endText;
	editor.selectionStart = ss;
	editor.selectionEnd   = ss + repText.length;
	editor.scrollTop      = st;
	editor.focus();
}
function wikibar_editFormatByCursor(param){
  var editor = param.button.editor;
  var params = param.params;
  clearMessage();
  if(!editor){ return; }
  var repText = wikibar_processSyntaxParams(this.syntax, params);
  if(repText===null){ return; }
	var st = editor.scrollTop;
	var ss = editor.selectionStart;
	var se = editor.selectionEnd;
	var frontText= '';
	var endText  = '';
	var fullText = editor.value;
	if(se>ss && ss>=0){
	  frontText  = fullText.substring(0, ss);
	  endText    = fullText.substring(se, fullText.length);
	}
	else if(ss===0 && (se===0 || se == fullText.length) ){
    endText    = fullText;
	}
	else if(se==ss && ss>0){
    frontText  = fullText.substring(0, ss);
    endText    = fullText.substring(se, fullText.length);
	}
	if(repText.indexOf('user_text')>=0 && this.hint){
		repText = repText.replace('user_text', this.hint);
	}
	editor.value = frontText + repText + endText;
	editor.selectionStart = ss;
	editor.selectionEnd   = ss + repText.length;
	editor.scrollTop      = st;
	editor.focus();
}
function wikibar_editFormatByLine(param){
  var editor = param.button.editor;
  var params = param.params;
  clearMessage();
  if(!editor){ return; }
  var repText = wikibar_processSyntaxParams(this.syntax, params);
  if(repText===null){ return; }
	var st = editor.scrollTop;
	var ss = editor.selectionStart;
	var se = editor.selectionEnd;
	var frontText= '';
	var selText  = '';
	var endText  = '';
	var fullText = editor.value;
	if(se>ss && ss>=0){
		if(this.byBlock){
	    frontText  = fullText.substring(0, ss);
	    selText		 = fullText.substring(ss,se);
	    endText    = fullText.substring(se, fullText.length);
		}
		else{
	  	se = ss;
		}
	}
  if(ss===0 && (se===0 || se == fullText.length) ){
    var m=fullText.match(/(\n|\r)/g);
    if(m){
      se = fullText.indexOf(m[0]);
    }else{
      se = fullText.length;
    }
    selText    = fullText.substring(0, se);
    endText    = fullText.substring(se, fullText.length);
	}
	else if(se==ss && ss>0){
    frontText  = fullText.substring(0, ss);
    endText    = fullText.substring(se, fullText.length);
    m = frontText.match(/(\n|\r)/g);
    if(m){
      ss = frontText.lastIndexOf(m[m.length-1])+1;
    }
    else{
      ss = 0;
    }
    m = endText.match(/(\n|\r)/g);
    if(m){
      se += endText.indexOf(m[0]);
    }
    else{
      se = fullText.length;
    }
    frontText = fullText.substring(0, ss);
	  selText   = fullText.substring(ss,se);
	  endText   = fullText.substring(se, fullText.length);
	}
	if(selText.length>0){
		repText = repText.replace('user_text', selText);
	}
	if(repText.indexOf('user_text')>=0 && this.hint){
		repText = repText.replace('user_text', this.hint);
	}
	if(this.byBlock){
    if( (frontText.charAt(frontText.length-1)!='\n') && ss>0 ){
    	repText = '\n' + repText;
    }
    if( (endText.charAt(0)!='\n') || se==fullText.length){
    	repText += '\n';
    }
	}
	editor.value = frontText + repText + endText;
	editor.selectionStart = ss;
	editor.selectionEnd   = ss + repText.length;
	editor.scrollTop      = st;
	editor.focus();
}
function wikibar_editFormatByTableCell(param){
  var editor = param.button.editor;
  var params = param.params;
  clearMessage();
  if(!editor){ return; }
  var repText = wikibar_processSyntaxParams(this.syntax, params);
  if(repText===null){ return; }
	var st = editor.scrollTop;
	var ss = editor.selectionStart;
	var se = editor.selectionEnd;
	var frontText= '';
	var selText  = '';
	var endText  = '';
	var fullText = editor.value;
	if(ss===0 || ss==fullText.length){
		throw 'not valid cell!';
	}
	se=ss;
  frontText  = fullText.substring(0, ss);
  endText    = fullText.substring(se, fullText.length);
  i=frontText.lastIndexOf('\n');
  j=frontText.lastIndexOf('|');
  if(i>j || j<0){
  	throw 'not valid cell!';
  }
	ss = j+1;
  i=endText.indexOf('\n');
  j=endText.indexOf('|');
  if(i<j || j<0){
  	throw 'not valid cell!';
  }
  se += j;
  frontText = fullText.substring(0, ss-1);
  selText   = fullText.substring(ss,se);
  endText   = fullText.substring(se+1, fullText.length);
	if(this.key.substring(0,5)=='align'){
		selText = selText.trim();
		if(	selText=='>' || selText=='~' ||	selText.substring(0,8)=='bgcolor(')	{return; }
	}
	if(selText.length>0){
		repText = repText.replace('user_text', selText);
	}
	if(repText.indexOf('user_text')>=0 && this.hint){
		repText = repText.replace('user_text', this.hint);
	}
	editor.value = frontText + repText + endText;
	editor.selectionStart = ss;
	editor.selectionEnd   = ss + repText.length - 2;
	editor.scrollTop      = st;
	editor.focus();
}
function wikibar_editSelectAll(param){
  var editor = param.button.editor;
	editor.selectionStart = 0;
	editor.selectionEnd   = editor.value.length;
	editor.scrollTop      = 0;
	editor.focus();
}
function wikibar_doPreview(param){
  var theButton = param.button;
  var editor = param.button.editor;
  var wikibar = theButton.parentNode;
  if(!wikibar)  { return; }
  title = theButton.tiddlerTitle;
  var editorWrapper = wikibar_resolveTiddlerEditorWrapper(editor);
  var tiddlerWrapper = editorWrapper.parentNode;
  var previewer = document.getElementById('previewer'+title);
  if(previewer){
    previewer.parentNode.removeChild(previewer);
    editorWrapper.style.display = 'block';
    visible=true;
  }else{
    previewer = document.createElement('div');
    previewer.id = 'previewer'+title;
    previewer.className = 'viewer previewer';
    previewer.style.height = (editor.offsetHeight) + 'px';
    wikify(editor.value, previewer);
    tiddlerWrapper.insertBefore(previewer, editorWrapper);
    editorWrapper.style.display = 'none';
    visible=false;
  }
  var pv=null;
  for(var i=0; i<wikibar.childNodes.length; i++){
    try{
      var btn = wikibar.childNodes[i];
      if(btn.toolItem.key == 'preview'){ pv=btn; }
      if(btn.toolItem.key != 'preview'){
        btn.style.display = visible ? '': 'none';
      }
    }catch(ex){}
  }
  if(!pv) { return; }
  if(visible){
    pv.innerHTML = '<font face=\"verdana\">&infin;</font>';
    pv.title = 'preview current tiddler';
  }
  else{
    pv.innerHTML = '<font face=\"verdana\">&larr;</font>';
    pv.title = 'back to editor';
  }
}
function wikibar_doListAddons(param){
  clearMessage();
  var title = param.button.tiddlerTitle;
  var wikibarButton = document.getElementById('wikibarButton'+title);
  var ok=0, fail=0;
  for(var i=0; i<wikibarButton.addons.length; i++){
    var addon=wikibarButton.addons[i];
    if(addon.ok){
      displayMessage('[ o ] '+addon.name);
      ok++;
    }
    else{
      displayMessage('[ x ] '+addon.name + ': ' + addon.error);
      fail++;
    }
  }
  displayMessage('---------------------------------');
  displayMessage(ok + ' ok ; ' + fail + ' failed');
}
function wikibar_getColorCode(param){
  var cbOnPickColor = function(colorCode, param){
    param.params = colorCode;
    param.button.toolItem.doMore(param);
  };
  wikibarColorTool.openColorPicker(param.button, cbOnPickColor, param);
}
function wikibar_getLinkUrl(param){
  var url= prompt('Please enter the link target', (this.param? this.param : ''));
  if (url && url.trim().length>0){
    param.params = url;
    this.doMore(param);
  }
}
function wikibar_getTableRowCol(param){
  var rc= prompt('Please enter (rows x cols) of the table', '2 x 3');
  if (!rc || (rc.trim()).length<=0){ return; }
  var arr = rc.toUpperCase().split('X');
  if(arr.length != 2)   { return; }
  for(var i=0; i<arr.length; i++){
    if(isNaN(arr[i].trim()))  { return; }
  }
  var rows = parseInt(arr[0].trim(), 10);
  var cols = parseInt(arr[1].trim(), 10);
  var txtTable='';
  for(var r=0; r<rows; r++){
    for(var c=0; c<=cols; c++){
      if(c===0){
        txtTable += '|';
      }else{
        txtTable += ' |';
      }
    }
    txtTable += '\n';
  }
  if(txtTable.trim().length>0){
    param.params = txtTable.trim();
    this.doMore(param);
  }
}
function wikibar_getMacroParam(param){
  var p = prompt('Please enter the parameters of macro \"' + this.key + '\":' +
                 '\nSyntax: ' + this.syntax +
                 '\n\nNote: '+
                 '\n%1,%2,... - parameter needed'+
                 '\n[%1] - optional parameter'+
                 '\n%N   - more than one parameter(1~n)'+
                 '\n[%N] - any number of parameters(0~n)'+
                 '\n\nPS:'+
                 '\n1. Parameters should be seperated with space character'+
                 '\n2. Use \" to wrap the parameter that includes space character, ex: \"hello world\"'+
                 '\n3. Input the word(null) for the optional parameter ignored',
                 (this.param? this.param : '') );
  if(!p)  { return; }
  p=p.readMacroParams();
  for(var i=0; i<p.length; i++){
    var s=p[i].trim();
    if(s.indexOf(' ')>0){ p[i]="'"+s+"'"; }
    if(s.toLowerCase()=='null'){ p[i]=null; }
  }
  param.params = p;
  this.doMore(param);
}
function wikibar_getMorePalette(unused){
  clearMessage();
  displayMessage('Get more color palettes(*.gpl) from ColorZilla Palettes site', 'http:\/\/www.iosart.com/firefox/colorzilla/palettes.html');
  displayMessage('Save it as a new tiddler with \"ColorPalettes\" tag');
}
function wikibar_createWikibar(title){
  var theWikibar = document.getElementById('wikibar' + title);
  if(theWikibar){
    if(theWikibar.hasChildNodes()){
      theWikibar.style.display = (theWikibar.style.display=='block'? 'none':'block');
      return;
    }
  }
  var tiddlerWrapper = document.getElementById('tiddler'+title);
  var theTextarea = wikibar_resolveTiddlerEditor(tiddlerWrapper);
  if(!theTextarea){
    clearMessage();
    displayMessage('WikiBar only works in tiddler edit mode now');
    return;
  }else{
    if(!theTextarea.id){ theTextarea.id = 'editor'+title; }
    if(!theTextarea.parentNode.id){ theTextarea.parentNode.id='editorWrapper'+title;  }
  }
  if(theWikibar){
    theWikibar = document.getElementById('wikibar'+title);
  }else{
    var editorWrapper = wikibar_resolveTiddlerEditorWrapper(theTextarea);
    theWikibar = createTiddlyElement(tiddlerWrapper, 'div', 'wikibar'+title, 'toolbar');
    addClass(theWikibar, 'wikibar');
    var previewer = document.getElementById('previewer'+title);
    if(previewer){
      tiddlerWrapper.insertBefore(theWikibar, previewer);
    }else{
      tiddlerWrapper.insertBefore(theWikibar, editorWrapper);
    }
  }
  wikibar_createMenu(theWikibar,wikibarStore,title,theTextarea);
  if(config.options['chkWikibarSetEditorHeight'] && config.options['txtWikibarEditorRows']){
    theTextarea.rows = config.options['txtWikibarEditorRows'];
  }
  setStylesheet(
    '.wikibar{text-align:left;visibility:visible;margin:2px;padding:1px;}.previewer{overflow:auto;display:block;border:1px solid;}#colorPicker{position:absolute;display:none;z-index:10;margin:0px;padding:0px;}#colorPicker table{margin:0px;padding:0px;border:2px solid #000;border-spacing:0px;border-collapse:collapse;}#colorPicker td{margin:0px;padding:0px;border:1px solid;font-size:11px;text-align:center;cursor:auto;}#colorPicker .header{background-color:#fff;}#colorPicker .button{background-color:#fff;cursor:pointer;cursor:hand;}#colorPicker .button:hover{padding-top:3px;padding-bottom:3px;color:#fff;background-color:#136;}#colorPicker .cell{padding:4px;font-size:7px;cursor:crosshair;}#colorPicker .cell:hover{padding:10px;}.wikibarPopup{position:absolute;z-index:10;border:1px solid #014;color:#014;background-color:#cef;}.wikibarPopup table{margin:0;padding:0;border:0;border-spacing:0;border-collapse:collapse;}.wikibarPopup .button:hover{color:#eee;background-color:#014;}.wikibarPopup .disabled{color:#888;}.wikibarPopup .disabled:hover{color:#888;background-color:#cef;}.wikibarPopup tr .seperator hr{margin:0;padding:0;background-color:#cef;width:100%;border:0;border-top:1px dashed #014;}.wikibarPopup tr .icon{font-family:verdana;font-weight:bolder;}.wikibarPopup tr .marker{font-family:verdana;font-weight:bolder;}.wikibarPopup td{font-size:0.9em;padding:2px;}.wikibarPopup input{border:0;border-bottom:1px solid #014;margin:0;padding:0;font-family:arial;font-size:100%;background-color:#fff;}',
  	'WikiBarStyleSheet');
}
function wikibar_createMenu(place,toolset,title,editor){
  if(!wikibar_isValidMenuItem(toolset)){return;}
  if(!(toolset.TYPE=='MAIN_MENU' || toolset.TYPE=='MENU')){ return; }
    for(var key in toolset){
      if(key.substring(0,9)=='SEPERATOR'){
        wikibar_createMenuSeperator(place);
        continue;
      }
      if(key.substring(0,8)=='DYNAITEM'){
        var dynaTools = toolset[key](title,editor);
        if(dynaTools.TYPE && dynaTools.TYPE=='MENU'){
          wikibar_createMenuItem(place,dynaTools,null,editor,title);
        }else{
          dynaTools.TYPE = 'MENU';
          wikibar_createMenu(place, dynaTools, title, editor);
        }
        continue;
      }
      if((toolset[key].TYPE!='MENU' && toolset[key].TYPE!='MAIN_MENU') && !toolset[key].HANDLER){continue;}
      wikibar_createMenuItem(place,toolset,key,editor,title);
    }
}
function wikibar_createMenuItem(place,toolset,key,editor,title){
  if(!key){
    var tool = toolset;
  }else{
    tool = toolset[key];
    tool.key = key;
  }
  if(!wikibar_isValidMenuItem(tool)){return;}
  var toolIsOnMainMenu = (toolset.TYPE=='MAIN_MENU');
  var toolIsMenu = (tool.TYPE=='MENU');
  var theButton;
  if(toolIsOnMainMenu){
    theButton = createTiddlyButton(
                  place,
                  '',
                  (tool.TOOLTIP? tool.TOOLTIP : ''),
                  (toolIsMenu? wikibar_onClickMenuItem : wikibar_onClickItem),
                  'button');
    theButton.innerHTML = (tool.CAPTION? tool.CAPTION : key);
    theButton.isOnMainMenu = true;
    addClass(theButton, (toolIsMenu? 'menu' : 'item'));
  	place.appendChild( document.createTextNode('\n') );
    if(!toolIsMenu){
      if(config.options['chkWikibarPopmenuOnMouseOver']){
        theButton.onmouseover = function(e){ wikibarPopup.remove(); };
      }
    }
  }else{
    theButton=createTiddlyElement(place, 'tr',key,'button');
    theButton.title = (tool.TOOLTIP? tool.TOOLTIP : '');
    theButton.onclick = (toolIsMenu? wikibar_onClickMenuItem : wikibar_onClickItem);
    var tdL = createTiddlyElement(theButton, 'td','','marker');
    var td = createTiddlyElement(theButton, 'td');
    var tdR = createTiddlyElement(theButton, 'td','','marker');
    td.innerHTML = (tool.CAPTION? tool.CAPTION : key);
    if(toolIsMenu){
      tdR.innerHTML='&nbsp;&nbsp;&rsaquo;';
    }
    if(tool.SELECTED){
      tdL.innerHTML = '&radic; ';
      addClass(theButton, 'selected');
    }
    if(tool.DISABLED){
      addClass(theButton, 'disabled');
    }
  }
  theButton.tiddlerTitle = title;
  theButton.toolItem = tool;
  theButton.editor = editor;
  theButton.tabIndex = 999;
  if(toolIsMenu){
    if(config.options['chkWikibarPopmenuOnMouseOver']){
      theButton.onmouseover = wikibar_onClickMenuItem;
    }
  }
}
function wikibar_createMenuSeperator(place){
  if(place.id.substring(0,7)=='wikibar')  { return; }
  var onclickSeperator=function(e){
  	if(!e){ e = window.event; }
  	e.cancelBubble = true;
    if (e.stopPropagation){ e.stopPropagation();  }
  	return(false);
  };
  var theButton=createTiddlyElement(place,'tr','','seperator');
  var td = createTiddlyElement(theButton, 'td','','seperator');
  td.colSpan=3;
  theButton.onclick=onclickSeperator;
	td.innerHTML = '<hr>';
}
function wikibar_genWikibarAbout(){
  var toolset={};
  toolset.version = {
    CAPTION: '<center>WikiBar ' +
              config.macros.wikibar.major + '.' +
              config.macros.wikibar.minor + '.' +
              config.macros.wikibar.revision +
              (config.macros.wikibar.beta? ' beta '+config.macros.wikibar.beta : '') +
              '</center>',
    HANDLER: function(){}
  };
  toolset.SEPERATOR = {};
  toolset.author = {
    CAPTION: '<center>Arphen Lin<br>arphenlin@gmail.com</center>',
    TOOLTIP: 'send mail to the author',
    HANDLER: function(){ window.open('mailto:arphenlin@gmail.com'); }
  };
  toolset.website = {
    CAPTION: '<center>aiddlywiki.sourceforge.net</center>',
    TOOLTIP: 'go to the web site of WikiBar',
    HANDLER: function(){ window.open('http:\/\/aiddlywiki.sourceforge.net/'); }
  };
  return toolset;
}
function wikibar_genWikibarOptions(title, editor){
  var toolset={};
  toolset.popOnMouseOver = {
    CAPTION:'popup menu on mouse over',
    SELECTED: config.options['chkWikibarPopmenuOnMouseOver'],
    HANDLER: function(param){
      config.options['chkWikibarPopmenuOnMouseOver'] = !config.options['chkWikibarPopmenuOnMouseOver'];
      saveOptionCookie('chkWikibarPopmenuOnMouseOver');
      var title = param.button.tiddlerTitle;
      var wikibar = document.getElementById('wikibar'+title);
      if(wikibar){ wikibar.parentNode.removeChild(wikibar); }
      wikibar_createWikibar(title);
    }
  };
  toolset.setEditorSize = {
    CAPTION:'set editor height: <input id=\"txtWikibarEditorRows\" type=text size=1 MAXLENGTH=3 value=\"' +
            (config.options['txtWikibarEditorRows']? config.options['txtWikibarEditorRows']:editor.rows) + '\"> ok',
    HANDLER: function(param){
      var input = document.getElementById('txtWikibarEditorRows');
      if(input){
        var rows = parseInt(input.value, 10);
        if(!isNaN(rows)){
          var editor = param.button.editor;
          editor.rows = rows;
        }else{
          rows=config.maxEditRows;
        }
        config.options['txtWikibarEditorRows'] = rows;
        saveOptionCookie('txtWikibarEditorRows');
        config.maxEditRows = rows;
      }
    }
  };
  toolset.setEditorSizeOnLoadingWikibar = {
    CAPTION:'set editor height on loading wikibar',
    SELECTED: config.options['chkWikibarSetEditorHeight'],
    HANDLER: function(param){
      config.options['chkWikibarSetEditorHeight'] = !config.options['chkWikibarSetEditorHeight'];
      saveOptionCookie('chkWikibarSetEditorHeight');
      if(config.options['chkWikibarSetEditorHeight']){
        var rows = config.options['txtWikibarEditorRows'];
        if(!isNaN(rows)){ rows = 15; }
        var editor = param.button.editor;
        editor.rows = rows;
        config.options['txtWikibarEditorRows'] = rows;
        saveOptionCookie('txtWikibarEditorRows');
      }
    }
  };
  toolset.SEPERATOR = {};
  toolset.update = {
    CAPTION: 'check for updates',
    DISABLED: true,
    HANDLER: function(){}
  };
  return toolset;
}
function wikibar_genPaletteSelector(){
  try{
  	var cpTiddlers = store.getTaggedTiddlers('ColorPalettes');
  	if(!cpTiddlers) { return; }
  	var palettes=[];
  	palettes.push(wikibarColorTool.defaultPaletteName);
  	for(var i=0; i<cpTiddlers.length; i++){
  		palettes.push(cpTiddlers[i].title.trim());
  	}
    var toolset={};
    for(i=0; i<palettes.length; i++){
      toolset[palettes[i]] = {
        TOOLTIP: palettes[i],
        SELECTED: (palettes[i]==wikibarColorTool.paletteName),
        HANDLER: wikibar_doSelectPalette
      };
    }
    return toolset;
  }catch(ex){ return null; }
}
function wikibar_onClickItem(e){
	if(!e){ e = window.event; }
	var theTarget = resolveTarget(e);
	if(theTarget.tagName=='INPUT'){
    e.cancelBubble = true;
    if (e.stopPropagation){ e.stopPropagation(); }
    return;
	}
	var theButton = wikibar_resolveTargetButton(theTarget);
	if(!theButton){ return(false);  }
  	var o = theButton.toolItem;
    if(!o) { return; }
    var param = {
      event: e,
      button: theButton
    };
    if(o.HANDLER){ o.HANDLER(param);  }
  if(o.DISABLED){
    e.cancelBubble = true;
    if (e.stopPropagation){ e.stopPropagation(); }
  }
	return(false);
}
function wikibar_onClickMenuItem(e){
	if(!e){ e = window.event; }
	var theButton = wikibar_resolveTargetButton(resolveTarget(e));
	if(!theButton){ return(false);  }
	e.cancelBubble = true;
	if (e.stopPropagation){ e.stopPropagation(); }
    var title = theButton.tiddlerTitle;
    var editor = theButton.editor;
    var tool = theButton.toolItem;
    if(!tool) { return; }
    var popup = wikibarPopup.create(this);
  	if(popup){
      wikibar_createMenu(popup,tool,title,editor);
      if(!popup.hasChildNodes()){
        wikibarPopup.remove();
      }else{
        wikibarPopup.show(popup, false);
      }
    }
	return(false);
}
var wikibarColorTool = {
  defaultPaletteName : 'default',
  defaultColumns : 16,
  defaultPalette : [
    '#FFF','#DDD','#CCC','#BBB','#AAA','#999','#666','#333','#111','#000','#FC0','#F90','#F60','#F30','#C30','#C03',
    '#9C0','#9D0','#9E0','#E90','#D90','#C90','#FC3','#FC6','#F96','#F63','#600','#900','#C00','#F00','#F36','#F03',
    '#CF0','#CF3','#330','#660','#990','#CC0','#FF0','#C93','#C63','#300','#933','#C33','#F33','#C36','#F69','#F06',
    '#9F0','#CF6','#9C3','#663','#993','#CC3','#FF3','#960','#930','#633','#C66','#F66','#903','#C39','#F6C','#F09',
    '#6F0','#9F6','#6C3','#690','#996','#CC6','#FF6','#963','#630','#966','#F99','#F39','#C06','#906','#F3C','#F0C',
    '#3F0','#6F3','#390','#6C0','#9F3','#CC9','#FF9','#C96','#C60','#C99','#F9C','#C69','#936','#603','#C09','#303',
    '#0C0','#3C0','#360','#693','#9C6','#CF9','#FFC','#FC9','#F93','#FCC','#C9C','#969','#939','#909','#636','#606',
    '#060','#3C3','#6C6','#0F0','#3F3','#6F6','#9F9','#CFC','#9CF','#FCF','#F9F','#F6F','#F3F','#F0F','#C6C','#C3C',
    '#030','#363','#090','#393','#696','#9C9','#CFF','#39F','#69C','#CCF','#C9F','#96C','#639','#306','#90C','#C0C',
    '#0F3','#0C3','#063','#396','#6C9','#9FC','#9CC','#06C','#369','#99F','#99C','#93F','#60C','#609','#C3F','#C0F',
    '#0F6','#3F6','#093','#0C6','#3F9','#9FF','#699','#036','#039','#66F','#66C','#669','#309','#93C','#C6F','#90F',
    '#0F9','#6F9','#3C6','#096','#6FF','#6CC','#366','#069','#36C','#33F','#33C','#339','#336','#63C','#96F','#60F',
    '#0FC','#6FC','#3C9','#3FF','#3CC','#399','#033','#39C','#69F','#00F','#00C','#009','#006','#003','#63F','#30F',
    '#0C9','#3FC','#0FF','#0CC','#099','#066','#3CF','#6CF','#09C','#36F','#0CF','#09F','#06F','#03F','#03C','#30C'
  ],
	colorPicker : null,
  pickColorHandler: null,
  userData: null
};
wikibarColorTool.paletteName = wikibarColorTool.defaultPaletteName;
wikibarColorTool.columns = wikibarColorTool.defaultColumns;
wikibarColorTool.palette = wikibarColorTool.defaultPalette;
wikibarColorTool.onPickColor = function(e){
	if (!e){ e = window.event; }
	var theCell = resolveTarget(e);
	if(!theCell){ return(false); }
    color = theCell.bgColor.toLowerCase();
    if(!color)  { return; }
    wikibarColorTool.displayColorPicker(false);
    if(wikibarColorTool.pickColorHandler){
      wikibarColorTool.pickColorHandler(color, wikibarColorTool.userData);
    }
	return(false);
};
wikibarColorTool.onMouseOver = function(e){
	if (!e){ e = window.event; }
	var theButton = resolveTarget(e);
	if(!theButton){ return(false);  }
  	if(!wikibarColorTool)  { return; }
    color = theButton.bgColor.toUpperCase();
    if(!color)  { return; }
    td=document.getElementById('colorPickerInfo');
  	if(!td) { return; }
  	td.bgColor = color;
  	td.innerHTML = '<span style=\"color:#000;\">'+color+'</span>&nbsp;&nbsp;&nbsp;' +
  	               '<span style=\"color:#fff;\">'+color+'</span>';
	e.cancelBubble = true;
	if (e.stopPropagation){ e.stopPropagation(); }
	return(false);
};
wikibarColorTool.openColorPicker = function(theTarget, pickColorHandler, userData){
  wikibarColorTool.skipClickDocumentEvent = true;
  wikibarColorTool.pickColorHandler = pickColorHandler;
  wikibarColorTool.userData = userData;
  wikibarColorTool.moveColorPicker(theTarget);
};
wikibarColorTool.convert3to6HexColor = function(c){
  c=c.trim();
  var rx=/^\#(\d|[a-f])(\d|[a-f])(\d|[a-f])$/gi;
  return (rx.test(c)? c.replace(rx, '#$1$1$2$2$3$3') : c);
};
wikibarColorTool.numToHexColor = function (n){
  if(typeof(n)=='number' && (n>=0 && n<=255)) {
  		s = n.toString(16).toLowerCase();
  		return ((s.length==1)? '0'+s : s);
  }else{
	 return null;
	}
};
wikibarColorTool.renderColorPalette = function(){
	if(wikibarColorTool.paletteName==wikibarColorTool.defaultPaletteName){
		wikibarColorTool.palette=wikibarColorTool.defaultPalette;
		wikibarColorTool.columns=wikibarColorTool.defaultColumns;
		return;
	}
	tiddlerText = (store.getTiddlerText(wikibarColorTool.paletteName, '')).trim();
	if(tiddlerText.length<=0) { return; }
	var cpContents = tiddlerText.split('\n');
	var colors=[];
	columns = wikibarColorTool.defaultColumns;
	var tmpArray=null;
	errCount=0;
	for(var i=0; i<cpContents.length; i++){
		cpLine=cpContents[i].trim();
    if( (!cpLine) || (cpLine.length<=0) || (cpLine.charAt(0) == '#') ){ continue; }
		if(cpLine.substring(0,8).toLowerCase()=='columns:'){
			tmpArray = cpLine.split(':');
			try{
				columns = parseInt(tmpArray[1],10);
			}catch(ex){
				columns = wikibarColorTool.defaultColumns;
			}
		}else{
			tmpArray = cpLine.replace('\t', ' ').split(/[ ]{1,}/);
			try{
				color='';
				for(var j=0; j<3; j++){
          c=parseInt(tmpArray[j].trim(), 10);
          if(isNaN(c)){
						break;
          }else{
						c=wikibarColorTool.numToHexColor(c);
						if(!c) {break;}
            color+=c;
					}
				}
				if(color.length==6){
					colors.push('#'+color);
				}	else {
					throw 'error';
				}
			}catch(ex){
			}
		}
	}
	if(colors.length>0){
		wikibarColorTool.palette = colors;
		wikibarColorTool.columns = columns;
	}else{
		throw 'renderColorPalette(): No color defined in the palette.';
	}
};
wikibarColorTool.displayColorPicker = function(visible){
  if(wikibarColorTool.colorPicker){
    wikibarColorTool.colorPicker.style.display = (visible? 'block' : 'none');
  }
};
wikibarColorTool.moveColorPicker = function(theTarget){
  if(!wikibarColorTool.colorPicker){
  	wikibarColorTool.createColorPicker();
  }
	var cp = wikibarColorTool.colorPicker;
	var rootLeft = findPosX(theTarget);
  var rootTop = findPosY(theTarget);
  var popupLeft = rootLeft;
  var popupTop = rootTop;
  var popupWidth = cp.offsetWidth;
  var winWidth = findWindowWidth();
  if(popupLeft + popupWidth > winWidth){
	  popupLeft = winWidth - popupWidth;
	}
  cp.style.left = popupLeft + 'px';
  cp.style.top = popupTop + 'px';
  wikibarColorTool.displayColorPicker(true);
};
wikibarColorTool.createColorPicker = function(unused, palette){
  if(palette){	wikibarColorTool.paletteName=palette; }
	wikibarColorTool.renderColorPalette();
	wikibarColorTool.colorPicker = document.createElement('div');
	wikibarColorTool.colorPicker.id = 'colorPicker';
	document.body.appendChild(wikibarColorTool.colorPicker);
  var theTable = document.createElement('table');
  wikibarColorTool.colorPicker.appendChild(theTable);
  var theTR = document.createElement('tr');
	theTable.appendChild(theTR);
	var theTD = document.createElement('td');
	theTD.className = 'header';
	theTD.colSpan = wikibarColorTool.columns;
	theTD.innerHTML = wikibarColorTool.paletteName;
  theTR.appendChild(theTD);
  for(var i=0; i<wikibarColorTool.palette.length; i++){
    if((i%wikibarColorTool.columns)===0){
      theTR = document.createElement('tr');
      theTable.appendChild(theTR);
    }
    theTD = document.createElement('td');
    theTD.className = 'cell';
    theTD.bgColor = wikibarColorTool.convert3to6HexColor(wikibarColorTool.palette[i]);
    theTD.onclick = wikibarColorTool.onPickColor;
    theTD.onmouseover = wikibarColorTool.onMouseOver;
    theTR.appendChild(theTD);
  }
  rest = wikibarColorTool.palette.length % wikibarColorTool.columns;
  if(rest>0){
    theTD = document.createElement('td');
		theTD.colSpan = wikibarColorTool.columns-rest;
    theTD.bgColor = '#000000';
    theTR.appendChild(theTD);
  }
  theTR = document.createElement('tr');
	theTable.appendChild(theTR);
	theTD = document.createElement('td');
	theTD.colSpan = wikibarColorTool.columns;
	theTD.id = 'colorPickerInfo';
  theTR.appendChild(theTD);
};
wikibarColorTool.onDocumentClick = function(e){
	if (!e){ e = window.event; }
	if(wikibarColorTool.skipClickDocumentEvent) {
	  wikibarColorTool.skipClickDocumentEvent = false;
    return true;
	}
	if((!e.eventPhase) || e.eventPhase == Event.BUBBLING_PHASE || e.eventPhase == Event.AT_TARGET){
    wikibarColorTool.displayColorPicker(false);
  }
	return true;
};
function wikibar_doSelectPalette(param){
	clearMessage();
	var theButton = param.button;
	if(!theButton.toolItem.key)  { return; }
	var palette = theButton.toolItem.key;
	var oldPaletteName = wikibarColorTool.paletteName;
	if(oldPaletteName != palette){
		try{
			wikibarColorTool.createColorPicker(theButton, palette);
			displayMessage('Palette \"'+palette+'\" ('+ wikibarColorTool.palette.length +' colors) is selected');
		}catch(ex){
			errMsg = ex;
			if(errMsg.substring(0,18)=='renderColorPalette'){
				displayMessage('Invalid palette \"' + palette + '\", please check it out!');
				wikibarColorTool.createColorPicker(theButton, oldPaletteName);
			}
		}
	}
}
var wikibarPopup = {
  skipClickDocumentEvent: false,
	stack: []
};
wikibarPopup.resolveRootPopup = function(o){
  if(o.isOnMainMenu){  return null; }
  if(o.className.substring(0,12)=='wikibarPopup'){  return o;}
  return wikibarPopup.resolveRootPopup(o.parentNode);
};
wikibarPopup.create = function(root){
  for(var i=0; i<wikibarPopup.stack.length; i++){
    var p=wikibarPopup.stack[i];
    if(p.root==root){
      wikibarPopup.removeFrom(i+1);
      return null;
    }
  }
  var rootPopup = wikibarPopup.resolveRootPopup(root);
  if(!rootPopup){
    wikibarPopup.remove();
  }else{
    wikibarPopup.removeFromRootPopup(rootPopup);
  }
	var popup = createTiddlyElement(document.body,'div','wikibarPopup'+root.toolItem.key,'wikibarPopup');
	var pop = createTiddlyElement(popup,'table','','');
	wikibarPopup.stack.push({rootPopup: rootPopup, root: root, popup: popup});
	return pop;
};
wikibarPopup.show = function(unused,slowly){
	var curr = wikibarPopup.stack[wikibarPopup.stack.length-1];
	var overlayWidth = 1;
  var rootLeft, rootTop, rootWidth, rootHeight, popupLeft, popupTop, popupWidth;
  if(curr.rootPopup){
  	rootLeft = findPosX(curr.rootPopup);
  	rootTop = findPosY(curr.root);
  	rootWidth = curr.rootPopup.offsetWidth;
  	popupLeft = rootLeft + rootWidth - overlayWidth;
  	popupTop = rootTop;
  }else{
  	rootLeft = findPosX(curr.root);
  	rootTop = findPosY(curr.root);
  	rootHeight = curr.root.offsetHeight;
  	popupLeft = rootLeft;
  	popupTop = rootTop + rootHeight;
  }
	var winWidth = findWindowWidth();
	popupWidth = curr.popup.offsetWidth;
	if(popupLeft + popupWidth > winWidth){
		popupLeft = rootLeft - popupWidth + overlayWidth;
	}
	curr.popup.style.left = popupLeft + 'px';
	curr.popup.style.top = popupTop + 'px';
	curr.popup.style.display = 'block';
	addClass(curr.root, 'highlight');
	if(config.options.chkAnimate){
		anim.startAnimating(new Scroller(curr.popup,slowly));
	}else{
		window.scrollTo(0,ensureVisible(curr.popup));
	}
};
wikibarPopup.remove = function(){
	if(wikibarPopup.stack.length > 0){
		wikibarPopup.removeFrom(0);
  }
};
wikibarPopup.removeFrom = function(from){
	for(var t=wikibarPopup.stack.length-1; t>=from; t--){
		var p = wikibarPopup.stack[t];
		removeClass(p.root,'highlight');
		p.popup.parentNode.removeChild(p.popup);
  }
	wikibarPopup.stack = wikibarPopup.stack.slice(0,from);
};
wikibarPopup.removeFromRootPopup = function(from){
  for(var t=0; t<wikibarPopup.stack.length; t++){
    var p = wikibarPopup.stack[t];
    if(p.rootPopup==from){
      wikibarPopup.removeFrom(t);
      break;
    }
  }
};
wikibarPopup.onDocumentClick = function(e){
	if (!e){ e = window.event; }
	if(wikibarPopup.skipClickDocumentEvent){
	 wikibarPopup.skipClickDocumentEvent=false;
	 return true;
	}
	if((!e.eventPhase) || e.eventPhase == Event.BUBBLING_PHASE || e.eventPhase == Event.AT_TARGET){
		wikibarPopup.remove();
	}
	return true;
};
var wikibarStore = {
  TYPE: 'MAIN_MENU',
  help:{
    TYPE:'MENU',
    CAPTION: '<font face=\"verdana\">?</font>',
    TOOLTIP:     'about WikiBar',
    options:{
      TYPE:'MENU',
      DYNAITEM: wikibar_genWikibarOptions
    },
    about:{
      TYPE:'MENU',
      DYNAITEM: wikibar_genWikibarAbout
    }
  },
  preview:{
    TOOLTIP:     'preview this tiddler',
    CAPTION: '<font face=\"verdana\">&infin;</font>',
    HANDLER: wikibar_doPreview
  },
	line:{
		TOOLTIP:    'horizontal line',
		CAPTION: '<font face=\"verdana\">&mdash;</font>',
		syntax: '\n----\n',
		HANDLER: wikibar_editFormatByCursor
	},
	crlf:{
		TOOLTIP:    'new line',
		CAPTION: '<font face=\"verdana\">&para;</font>',
		syntax: '\n',
		HANDLER: wikibar_editFormatByCursor
	},
	selectAll:{
		TOOLTIP:    'select all',
		CAPTION: '<font face=\"verdana\">&sect;</font>',
		HANDLER: wikibar_editSelectAll
	},
	deleteSelected:{
		TOOLTIP:    'delete selected',
		CAPTION: '<font face=\"verdana\">&times;</font>',
		syntax: '',
		HANDLER: wikibar_editFormat
	},
  textFormat:{
    TYPE: 'MENU',
    CAPTION: 'text',
    TOOLTIP: 'text formatters',
    ignore:{
			TOOLTIP:     'ignore wiki word',
			CAPTION: 'ignore wikiWord',
			syntax:  '~user_text',
			hint:    'wiki_word',
			HANDLER:    wikibar_editFormatByWord
		},
		bolder:{
			TOOLTIP:     'bolder text',
			CAPTION: '<strong>bolder</strong>',
			syntax:  "''user_text''",
			hint:		 'bold_text',
			HANDLER:    wikibar_editFormatByWord
		},
		italic:{
			TOOLTIP:    'italic text',
			CAPTION: '<em>italic</em>',
			syntax: '\/\/user_text\/\/',
			hint:		'italic_text',
			HANDLER: wikibar_editFormatByWord
		},
		underline:{
			TOOLTIP:    'underline text',
			CAPTION: '<u>underline</u>',
			syntax: '__user_text__',
			hint:		'underline_text',
			HANDLER: wikibar_editFormatByWord
		},
		strikethrough:{
			TOOLTIP:    'strikethrough text',
			CAPTION: '<strike>strikethrough</strike>',
			syntax: '==user_text==',
			hint:		'strikethrough_text',
			HANDLER: wikibar_editFormatByWord
		},
		superscript:{
			TOOLTIP:    'superscript text',
			CAPTION: 'X<sup>superscript</sup>',
			syntax: '^^user_text^^',
			hint:		'superscript_text',
			HANDLER: wikibar_editFormatByWord
		},
		subscript:{
			TOOLTIP:    'subscript text',
			CAPTION: 'X<sub>subscript</sub>',
			syntax: '~~user_text~~',
			hint:		'subscript_text',
			HANDLER: wikibar_editFormatByWord
		},
		comment:{
			TOOLTIP:    'comment text',
			CAPTION: 'comment text',
			syntax: '/%user_text%/',
			hint:		'comment_text',
			HANDLER: wikibar_editFormatByWord
		},
		monospaced:{
			TOOLTIP:    'monospaced text',
			CAPTION: '<code>monospaced</code>',
			syntax: '{{{user_text}}}',
			hint:		'monospaced_text',
			HANDLER: wikibar_editFormatByWord
		}
  },
  paragraph:{
    TYPE: 'MENU',
    TOOLTIP: 'paragarph formatters',
    list:{
      TYPE: 'MENU',
      TOOLTIP: 'list tools',
      bullet:{
  			TOOLTIP:    'bullet point',
  			syntax: '*user_text',
  			hint:		'bullet_text',
  			HANDLER: wikibar_editFormatByLine
  		},
  		numbered:{
  			TOOLTIP:    'numbered list',
  			syntax: '#user_text',
  			hint:		'numbered_text',
  			HANDLER: wikibar_editFormatByLine
  		}
    },
    heading:{
      TYPE: 'MENU',
      heading1:{
  		  CAPTION:'<h1>Heading 1</h1>',
  			TOOLTIP:    'Heading 1',
  			syntax: '!user_text',
  			hint:		'heading_1',
  			HANDLER: wikibar_editFormatByLine
  		},
  		heading2:{
  		  CAPTION:'<h2>Heading 2<h2>',
  			TOOLTIP:    'Heading 2',
  			syntax: '!!user_text',
  			hint:		'heading_2',
  			HANDLER: wikibar_editFormatByLine
  		},
  		heading3:{
  		  CAPTION:'<h3>Heading 3</h3>',
  			TOOLTIP:    'Heading 3',
  			syntax: '!!!user_text',
  			hint:		'heading_3',
  			HANDLER: wikibar_editFormatByLine
  		},
  		heading4:{
  		  CAPTION:'<h4>Heading 4</h4>',
  			TOOLTIP:    'Heading 4',
  			syntax: '!!!!user_text',
  			hint:		'heading_4',
  			HANDLER: wikibar_editFormatByLine
  		},
  		heading5:{
  		  CAPTION:'<h5>Heading 5</h5>',
  			TOOLTIP:    'Heading 5',
  			syntax: '!!!!!user_text',
  			hint:		'heading_5',
  			HANDLER: wikibar_editFormatByLine
  		}
    },
    comment:{
      TYPE: 'MENU',
      commentByLine:{
  			CAPTION:'comment by line',
  			TOOLTIP:    'line comment',
  			syntax: '/%user_text%/',
  			hint:		'comment_text',
  			HANDLER: wikibar_editFormatByLine
  		},
  		commentByBlock:{
  			CAPTION:'comment by block',
  			TOOLTIP:    'block comment',
  			syntax: '/%\nuser_text\n%/',
  			hint:		'comment_text',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		}
    },
    monospaced:{
      TYPE: 'MENU',
  		monosByLine:{
  			CAPTION: 	'monospaced by line',
  			TOOLTIP:    'line monospaced',
  			syntax: '{{{\nuser_text\n}}}',
  			hint:		'monospaced_text',
  			HANDLER: wikibar_editFormatByLine
  		},
  		monosByBlock:{
  			CAPTION: 	'monospaced by block',
  			TOOLTIP:    'block monospaced',
  			syntax: '{{{\nuser_text\n}}}',
  			hint:		'monospaced_text',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		}
    },
    quote:{
      TYPE: 'MENU',
  		quoteByLine:{
  			CAPTION: 	'quote by line',
  			TOOLTIP:    'line quote',
  			syntax: '>user_text',
  			hint:		'quote_text',
  			HANDLER: wikibar_editFormatByLine
  		},
  		quoteByBlcok:{
  			CAPTION: 	'quote by block',
  			TOOLTIP:    'block quote',
  			syntax: '<<<\nuser_text\n<<<',
  			hint:		'quote_text',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		}
    },
    plugin:{
      TYPE: 'MENU',
      code:{
  			CAPTION: 	'code area',
  			TOOLTIP:    'block monospaced for plugin',
  			syntax: '\n\/\/{{{\nuser_text\n\/\/}}}\n',
  			hint:		'monospaced_plugin_code',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		},
  		commentByLine:{
  			CAPTION: 	'comment by line',
  			TOOLTIP:    'line comment',
  			syntax: '\/\/user_text',
  			hint:		'plugin_comment',
  			HANDLER: wikibar_editFormatByLine
  		},
  		commentByBlock:{
  			CAPTION: 	'comment by block',
  			TOOLTIP:    'block comment',
  			syntax: '\/\***\nuser_text\n***\/',
  			hint:		'plugin_comment',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		}
    },
    css:{
      TYPE: 'MENU',
      code:{
  			CAPTION: 	'code area',
  			TOOLTIP:    'block monospaced for css',
  			syntax: '\n\nuser_text\n\n',
  			hint:		'monospaced_css_code',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		},
  		commentByLine:{
  			CAPTION: 	'comment by line',
  			TOOLTIP:    'line comment',
  			syntax: '',
  			hint:		'css_comment',
  			HANDLER: wikibar_editFormatByLine
  		},
  		commentByBlock:{
  			CAPTION: 	'comment by block',
  			TOOLTIP:    'block comment',
  			syntax: '',
  			hint:		'css_comment',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		}
    }
  },
  color:{
    TYPE: 'MENU',
    TOOLTIP: 'color tools',
    highlight:{
		  CAPTION:'highlight text',
			TOOLTIP:    'highlight text',
			syntax: '@@user_text@@',
			hint:		'highlight_text',
			HANDLER: wikibar_editFormatByWord
		},
		color:{
		  CAPTION:'text color',
			TOOLTIP:    'text color',
			hint:		'your_text',
			syntax: '@@color(%1):user_text@@',
			HANDLER:   wikibar_getColorCode,
			doMore: wikibar_editFormatByWord
		},
		bgcolor:{
		  CAPTION:'background color',
			TOOLTIP:    'background color',
			hint:		'your_text',
			syntax: '@@bgcolor(%1):user_text@@',
			HANDLER: wikibar_getColorCode,
			doMore: wikibar_editFormatByWord
		},
		colorcode:{
      CAPTION:'color code',
      TOOLTIP:    'insert color code',
      syntax: '%1',
      HANDLER: wikibar_getColorCode,
      doMore: wikibar_editFormatByCursor
    },
    'color palette':{
      TYPE:'MENU',
      DYNAITEM: wikibar_genPaletteSelector,
  		SEPERATOR:{},
  		morePalette:{
  		  CAPTION:'more palettes',
  		  TOOLTIP:'get more palettes',
  		  HANDLER: wikibar_getMorePalette
  		}
    }
  },
  link:{
    TYPE: 'MENU',
    TOOLTIP: 'insert link',
    wiki:{
		  CAPTION:'wiki link',
			TOOLTIP:    'wiki link',
			syntax: '[[user_text]]',
			hint:		'wiki_word',
			HANDLER: wikibar_editFormatByWord
		},
		pretty:{
			CAPTION: 	'pretty link',
			TOOLTIP:    'pretty link',
			syntax: '[[user_text|%1]]',
			hint:		'pretty_word',
			param:	'PrettyLink Target',
			HANDLER:   wikibar_getLinkUrl,
			doMore: wikibar_editFormatByWord
		},
		url:{
			TOOLTIP:    'url link',
			syntax: '[[user_text|%1]]',
			hint:		'your_text',
			param:	'http:\/\/...',
			HANDLER:   wikibar_getLinkUrl,
			doMore: wikibar_editFormatByWord
		},
		image:{
			TOOLTIP:    'image link',
			syntax: '[img[user_text|%1]]',
			hint:		'alt_text',
			param:	'image/icon.jpg',
			HANDLER:   wikibar_getLinkUrl,
			doMore: wikibar_editFormatByWord
		}
  },
  macro:{},
  more:{
    TYPE: 'MENU',
    TOOLTIP: 'more tools',
    table:{
      TYPE: 'MENU',
      TOOLTIP: 'table',
      table:{
  		  CAPTION:'create table',
  			TOOLTIP:    'create a new table',
  			syntax: '\n%1\n',
  			HANDLER: wikibar_getTableRowCol,
  			doMore: wikibar_editFormatByWord
  		},
  		header:{
  			TOOLTIP:    'table header text',
  			syntax: '|user_text|c',
  			hint:		'table_header',
  			HANDLER: wikibar_editFormatByWord
  		},
  		cell:{
  			TOOLTIP:    'create a tabel cell',
  			syntax: '|user_text|',
  			hint:		'your_text',
  			HANDLER: wikibar_editFormatByWord
  		},
  		columnHeader:{
  		  CAPTION:'column header',
  			TOOLTIP:    'create a column header cell',
  			syntax: '|!user_text|',
  			hint:		'column_header',
  			HANDLER: wikibar_editFormatByWord
  		},
  	  cell:{
  	    TYPE: 'MENU',
        CAPTION: 'cell options',
    		bgcolor:{
    			CAPTION: 	'background color',
    			TOOLTIP:    'cell bgcolor',
    			syntax: '|bgcolor(%1):user_text|',
    			hint:		'your_text',
    			HANDLER: wikibar_getColorCode,
    			doMore: wikibar_editFormatByTableCell
    		},
    		alignLeft:{
    			CAPTION: 	'align left',
    			TOOLTIP:    'left align cell text',
    			syntax: '|user_text|',
    			hint:		'your_text',
    			HANDLER: wikibar_editFormatByTableCell
    		},
    		alignCenter:{
    			CAPTION: 	'align center',
    			TOOLTIP:    'center align cell text',
    			syntax: '| user_text |',
    			hint:		'your_text',
    			HANDLER: wikibar_editFormatByTableCell
    		},
    		alignRight:{
    			CAPTION: 	'align right',
    			TOOLTIP:    'right align cell text',
    			syntax: '| user_text|',
    			hint:		'your_text',
    			HANDLER: wikibar_editFormatByTableCell
    		}
    	}
    },
    html:{
      TYPE: 'MENU',
      html:{
  			CAPTION: 	'&lt;html&gt;',
  			TOOLTIP:    'html tag',
  			syntax: '<html>\nuser_text\n</html>',
  			hint:		'html_content',
  			byBlock: true,
  			HANDLER: wikibar_editFormatByLine
  		}
    }
  },
  addon:{
    TYPE: 'MENU',
    TOOLTIP:'3rd party tools',
    'about addons':{
      TOOLTIP: 'list loaded addons',
      HANDLER: wikibar_doListAddons
    },
    SEPERATOR:{}
  }
};
addEvent(document, 'click', wikibarColorTool.onDocumentClick);
addEvent(document, 'click', wikibarPopup.onDocumentClick);
wikibar_install();
//}}}


