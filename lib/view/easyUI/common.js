/*
 * Common javascript file loaded with all pages 
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-04-15
 * @filesource lib/view/easyUI/common.js
 */

/* **************************** Variables loaded as needed ********************************* */
//initialize some variables
var bizDefaults= new Array();
var icnAction  = '';
var glAccounts = new Array();
var countries  = new Array();
var addressFields = ['address_id','primary_name','contact','address1','address2','city','state','postal_code',
    'telephone1','telephone2','telephone3','telephone4','email','website'];
/* **************************** Initialization Functions *********************************** */
jq.ajaxSetup({ // Set defaults for ajax requests
//	contentType: "application/json; charset=utf-8", // this breaks easyUI, datagrid operations
//	dataType: (jq.browser.msie) ? "text" : "json", // not needed for jquery 2.x
	dataType: "json",
	error: function(XMLHttpRequest, textStatus, errorThrown) {
            if(textStatus==="timeout") { jq('body').removeClass('loading'); }
            if (errorThrown) {
                jq('body').removeClass('loading');
                var errMessage = XMLHttpRequest.responseText.substring(0, 500); // tuncate the message
                jq.messager.alert('Info', "Bizuno Ajax Error: "+errorThrown+' - '+errMessage+"<br />Status: "+textStatus, 'info');
            }
        // go to home page/login screen in 5 seconds
//        window.setTimeout(function() { window.location = bizunoHome; }, 3000);
    }
});

// LOAD BROWSER USER DEFAULTS
if (typeof sessionStorage.bizuno != 'undefined') {
	bizDefaults = JSON.parse(sessionStorage.getItem('bizuno'));
} else if (bizID >= 0) { // this happens when first logging in OR opening a new tab in the browser manually
    reloadSessionStorage();
}

if (typeof bizDefaults.dictionary != 'undefined') {
    if (bizDefaults.language.length != 5) { bizDefaults.language = 'en'; }
    jq.i18n().load(bizDefaults.dictionary, bizDefaults.language);
}

jq.cachedScript = function( url, options ) {
    options = jq.extend( options || {}, { dataType: "script", cache: true, url: url });
    return jq.ajax( options );
};
 
jq.fn.serializeObject = function() {
	var o = {};
	var a = this.serializeArray();
	jq.each(a, function() {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};

jq.fn.textWidth = function(text, font) {
	if (!jq.fn.textWidth.fakeEl) jq.fn.textWidth.fakeEl = jq('<span>').hide().appendTo(document.body);
	jq.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
	return jq.fn.textWidth.fakeEl.width();
};

// add clear button to datebox, need to add following to each datebox after init: jq('#dd').datebox({ buttons:buttons });
var buttons = jq.extend([], jq.fn.datebox.defaults.buttons);
buttons.splice(1, 0, {
	text: 'Clear',
	handler: function(target){ jq(target).datebox('clear'); }
});
jq.fn.datebox.defaults.formatter  = function(date) { return formatDate(date); };
jq.fn.datebox.defaults.parser     = function(sDate){
	if (!sDate) { return new Date(); }
    if (typeof sDate === 'integer' || typeof sDate === 'object') {
        sDate = formatDate(sDate);
    }
	var sep = '.'; // determine the separator, choices are /, -, and . 
	var idx = bizDefaults.calendar.format.indexOf('.');
	if (idx === -1) {
		sep = '-';
		idx = bizDefaults.calendar.format.indexOf('-');
		if (idx === -1) sep = '/';
	}
	var pos = bizDefaults.calendar.format.split(sep);
	var ss  = sDate.split(sep);
	d = [];
	for (var i=0; i<3; i++) d[pos[i]] = parseInt(ss[i],10);
	if (!isNaN(d['Y']) && !isNaN(d['m']) && !isNaN(d['d'])){
		return new Date(d['Y'],d['m']-1,d['d']);
	} else {
		return new Date();
	}
};
//jq.fn.datagrid.defaults.striped   = true; // causes row formatter to skip every other row. bad for using color for status
jq.fn.datagrid.defaults.fitColumns  = true;
jq.fn.datagrid.defaults.pagination  = true;
jq.fn.datagrid.defaults.singleSelect= true;
jq.fn.window.defaults.minimizable   = false;
jq.fn.window.defaults.collapsible   = false, 
jq.fn.window.defaults.maximizable   = false;
if (typeof bizDefaults.locale !== 'undefined') {
	jq.fn.numberbox.defaults.precision       = bizDefaults.locale.precision;
	jq.fn.numberbox.defaults.decimalSeparator= bizDefaults.locale.decimal;
	jq.fn.numberbox.defaults.groupSeparator  = bizDefaults.locale.thousand;
	jq.fn.numberbox.defaults.prefix          = bizDefaults.locale.prefix;
	jq.fn.numberbox.defaults.suffix          = bizDefaults.locale.suffix;
}
jq.extend(jq.fn.datagrid.defaults.editors, {
	color: {
		init: function(container, options){
			var input = jq('<input type="text">').appendTo(container);
			return input.color(options);
		},
		destroy:  function(target){ jq(target).color('destroy'); },
		getValue: function(target){ return jq(target).color('getValue'); },
		setValue: function(target, value){ jq(target).color('setValue',value); },
	},
	numberbox: {
		init: function(container, options){
			var input = jq('<input type="text">').appendTo(container);
			return input.numberbox(options);
		},
		destroy:  function(target){ jq(target).numberbox('destroy'); },
		getValue: function(target){ return jq(target).numberbox('getValue'); },
		setValue: function(target, value){ jq(target).numberbox('setValue',value); },
//		resize:   function(target, width){ jq(target).numberbox('resize',width); }
	},
	numberspinner: {
		init: function(container, options){
			var input = jq('<input type="text">').appendTo(container);
			return input.numberspinner(options);
		},
		destroy:  function(target){ jq(target).numberspinner('destroy'); },
		getValue: function(target){ return jq(target).numberspinner('getValue'); },
		setValue: function(target, value){ jq(target).numberspinner('setValue',value); },
		resize:   function(target, width){ jq(target).numberspinner('resize',width); }
	},
	combogrid: {
		init: function(container, options){
			var input = jq('<input type="text" class="datagrid-editable-input">').appendTo(container); 
			input.combogrid(options);
			return input;
		},
		destroy:  function(target)       { jq(target).combogrid('destroy'); },
		getValue: function(target)       { return jq(target).combogrid('getValue'); },
		setValue: function(target, value){ jq(target).combogrid('setValue', value); },
		resize:   function(target, width){ jq(target).combogrid('resize',width); }
	}
});

function pagerFilter(data){
	if (jq.isArray(data)) data = {total: data.length, rows: data };
	var dg = jq(this);
	var state = dg.data('datagrid');
	var opts = dg.datagrid('options');
	if (!state.allRows) { state.allRows = (data.rows); }
	var start = (opts.pageNumber-1)*parseInt(opts.pageSize);
	var end = start + parseInt(opts.pageSize);
	data.rows = jq.extend(true,[],state.allRows.slice(start, end));
	return data;
}

var loadDataMethod = jq.fn.datagrid.methods.loadData;
jq.extend(jq.fn.datagrid.methods, {
	disableDnd: function(jqy,index) {
		return jqy.each(function() {
			var trs;
			var target = this;
			var opts = jq(this).datagrid('options');
			if (index != undefined){
				trs = opts.finder.getTr(this, index);
			} else {
				trs = opts.finder.getTr(this, 0, 'allbody');
			}
			trs.draggable('disable');
		});
	},
	clientPaging: function(jqy) {
		return jqy.each(function() {
			var dg = jq(this);
			var state = dg.data('datagrid');
			var opts = state.options;
			opts.loadFilter = pagerFilter;
			var onBeforeLoad = opts.onBeforeLoad;
			opts.onBeforeLoad = function(param){
				state.allRows = null;
				return onBeforeLoad.call(this, param);
			};
			dg.datagrid('getPager').pagination({
				onSelectPage:function(pageNum, pageSize){
					opts.pageNumber = pageNum;
					opts.pageSize = pageSize;
					jq(this).pagination('refresh',{
						pageNumber:pageNum,
						pageSize:pageSize
					});
					dg.datagrid('loadData',state.allRows);
				}
			});
			jq(this).datagrid('loadData', state.data);
			if (opts.url){
                jq(this).datagrid('reload');
			}
		});
	},
	loadData: function(jqy, data) {
		jqy.each(function(){ jq(this).data('datagrid').allRows = null; });
		return loadDataMethod.call(jq.fn.datagrid.methods, jqy, data);
	},
	getAllRows: function(jqy) {
		return jqy.data('datagrid').allRows;
	}
});

jq.extend(jq.fn.combogrid.methods, {
	attachEvent: function(jqy, param){
		return jqy.each(function(){
			var grid = jq(this).combogrid('grid');
			var opts = jq(this).combogrid('options');
			opts.handlers = opts.handlers || {};
			var cbs = opts.handlers[param.event];
			if (cbs){
				cbs.push(param.handler);
			} else {
				cbs = [opts[param.event], param.handler];
			}
			opts.handlers[param.event] = cbs;
			opts[param.event] = grid.datagrid('options')[param.event] = function(){
				var target = this;
				var args = arguments;
				jq.each(opts.handlers[param.event], function(i,h){
					h.apply(target, args);
				});
			};
		});
	}
});

function viewNumberDefaults(ISO) {
//	alert('setting new defaults for numberbox.');
	jq.fn.numberbox.defaults.precision       = bizDefaults.currency.currencies[ISO].dec_len;
	jq.fn.numberbox.defaults.decimalSeparator= bizDefaults.currency.currencies[ISO].dec_pt;
	jq.fn.numberbox.defaults.groupSeparator  = bizDefaults.currency.currencies[ISO].sep;
	if (bizDefaults.currency.currencies.length > 1) {
		jq.fn.numberbox.defaults.prefix      = bizDefaults.currency.currencies[ISO].prefix;
		jq.fn.numberbox.defaults.suffix      = bizDefaults.currency.currencies[ISO].suffix;
	}
}

/*
 * This function will search all columns in a combo in place of the standard search only by text field
 * @param array data - original data to search (need to go backwards)
 * @param string q - search string
 * @returns array - filtered data
 */
function glComboSearch(q) {
    var newRows = [];
    jq.map(bizDefaults.glAccounts.rows, function(row) {
        for (var p in row) {
            var v = row[p];
            var regExp = new RegExp(q, 'i'); // i - makes the search case-insensitive.
            if (regExp.test(String(v))) {
                newRows.push(row);
                break;
            }
        }
    });
    var comboEd = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'gl_account'});
    var g = jq(comboEd.target).combogrid('grid');
    g.datagrid('loadData', newRows);
    jq(comboEd.target).combogrid('showPanel');
    jq(comboEd.target).combogrid('setText', q);
}

/**
 * This function will load into session storage for common Bizuno data that tends to be static once logged in
 */
function loadSessionStorage() {
	jq.ajax({
		url:     bizunoAjax+'&p=bizuno/admin/loadBrowserSession',
		success: function(json) { 
            processJson(json); 
            sessionStorage.setItem('bizuno', JSON.stringify(json));
        }
	});
	window.location = bizunoHome; // done, load the homepage
}

function reloadSessionStorage(callBackFunction) {
	jq.ajax({
		url:     bizunoAjax+'&p=bizuno/admin/loadBrowserSession',
		success: function(json) { 
            processJson(json);
            sessionStorage.removeItem('bizuno');
            sessionStorage.setItem('bizuno', JSON.stringify(json));
            bizDefaults=json;
            if (typeof callBackFunction == 'function') {
                alert('executing callback');
                callBackFunction();
            }
        }
	});
}

function refreshSessionClock() {
    setInterval(function(){ jsonAction('bizuno/main/sessionRefresh'); }, 240000);
}

function hrefClick(path, rID, data) {
    if  (typeof path == 'undefined') return alert('ERROR: The destination path is required, no value was provided.');
    var pathClean = path.replace(/&amp;/g,"&");
    var remoteURL = bizunoHome+'&p='+pathClean;
    if (typeof rID   != 'undefined') remoteURL += '&rID='+rID;
    if (typeof jData != 'undefined') remoteURL += '&data='+encodeURIComponent(jData);
    window.location = remoteURL;
}

function jsonAction(path, rID, jData) {
    if  (typeof path == 'undefined') return alert('ERROR: The destination path is required, no value was provided.');
    var pathClean = path.replace(/&amp;/g,"&");
    var remoteURL = bizunoAjax+'&p='+pathClean;
    if (typeof rID   != 'undefined') remoteURL += '&rID='+rID;
    if (typeof jData != 'undefined') remoteURL += '&data='+encodeURIComponent(jData);
    jq.ajax({ type:'GET', url:remoteURL, success:function (data) { processJson(data); } });
    return false;
}

function tabOpen(id, path) {
    var popupWin = window.open(bizunoHome+"&p="+path, id);
    popupWin.focus();
}

function winOpen(id, path, w, h) {
    if (!w) w = 800;
    if (!h) h = 650;
    var popupWin = window.open(bizunoHome+"&p="+path, id, 'width='+w+', height='+h+', resizable=1, scrollbars=1, top=150, left=200');
    popupWin.focus();
}

function winHref(id, path, w, h) {
    if (!w) w = 800;
    if (!h) h = 650;
    var popupWin = window.open(path, id, 'width='+w+', height='+h+', resizable=1, scrollbars=1, top=150, left=200');
	popupWin.focus();
}

function accordionEdit(accID, dgID, divID, title, path, rID, action) {
    //alert('accID = '+accID+' and dgID = '+dgID+' and divID = '+divID+' and title = '+title+' and path = '+path+' and rID = '+rID+' and action = '+action);
    if (typeof tinymce !== 'undefined') { tinymce.EditorManager.editors=[]; }
    var xVars = path+'&rID='+rID;
    if (typeof action != 'undefined') { xVars += '&bizAction='+action; } // do not know if this is used?
    jq('#'+dgID).datagrid('loaded');
    jq('#'+accID).accordion('select', title);
    jq('#'+divID).panel('refresh', bizunoAjax+'&p='+xVars);
}

/**
 * This function opens a window and then loads the contents remotely
 */
function windowEdit(href, id, winTitle, width, height) {
    processJson( { action:'window', id:id, title:winTitle, href:bizunoAjax+'&p='+href, height:height, width:width } );
}

/**
 * This function prepares a form to be submited via ajax
 */
function ajaxForm(formID) {
    jq('#'+formID).submit(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // may have to uncomment this to prevent double submits
        if ('function' == typeof preSubmit) {
            var passed = preSubmit();
            if (!passed) return false; // pre-submit js checking
        }
        var frmData = new FormData(this);
        if (navigator.userAgent.indexOf('11.1 Safari') !== -1) {
//          jq.messager.alert('Capture', 'Timestamp = '+new Date().getTime());
            jq('#'+formID).find("input[type=file]").each(function(index, field){
                alert('This version of Safari cannot upload files, if you have added attachments, you will need to use a different browser or version of Safari to upload your file. The rest of the fields will not be affected. PhreeSoft is working with Apple to resolve this issue.');
                frmData.delete(field.id);
            });
        }
        jq.ajax({
            url:        jq('#'+formID).attr('action'),
            type:       'post', // breaks with GET
            data:       frmData,
            mimeType:   'multipart/form-data',
            contentType:false,
            processData:false,
            cache:      false,
            success:    function (data) { processJson(data); }
//          error:      function(XMLHttpRequest, textStatus, errorThrown) { if (errorThrown) { jq.messager.alert('Info', "Bizuno Ajax Error: "+errorThrown+' - '+XMLHttpRequest.responseText+"<br />Status: "+textStatus, 'info'); } }
        });
        return false;
    });
}

/**
 * This function uses the jquery plugin filedownload to perform a controlled file download with error messages if a failure occurs
 */
function ajaxDownload(formID) {
    jq('#'+formID).submit(function (e) {
        jq.fileDownload(jq(this).attr('action'), {
            failCallback: function (response, url) { processJson(JSON.parse(response)); },
            httpMethod: 'POST',
            data: jq(this).serialize()
        });
        e.preventDefault();
    });
}

/**
 * This function submits the input fields within a given div element
 */
function divSubmit(path, id) { 
    divData = jq('#'+id+' :input').serializeObject();
    jq.ajax({
        url:     bizunoAjax+'&p='+path,
        type:    'post',
        data:    divData,
        mimeType:'multipart/form-data',
        cache:   false,
        success: function (data) { processJson(data); }
    });
}

/**
 * This function processes the returned json data array
 */
function processJson(json) {
	jq('body').removeClass('loading');
	if (!json) return false;
	if ( json.message) displayMessage(json.message);
	if ( json.extras)  eval(json.extras);
	switch (json.action) {
        case 'chart':   drawBizunoChart(json.actionData); break;
		case 'dialog':  break;
		case 'divHTML': jq('#'+json.divID).html(json.html).text();  break;
		case 'eval':    if (json.actionData) eval(json.actionData); break;
		case 'formFill':if (json.data) for (key in json.data) jq("#"+key).val(json.data[key]); break;
		case 'href':    if (json.link) window.location = json.link.replace(/&amp;/g,"&");      break;
		case 'window':
			var id       = typeof json.id   !== 'undefined' ? json.id   : 'win'+Math.floor((Math.random() * 1000000) + 1);
            var title    = typeof json.title!== 'undefined' ? json.title: ' ';
			var winT     = typeof json.top  !== 'undefined' ? json.top  : 50;
			var winL     = typeof json.left !== 'undefined' ? json.left : 50;
			var winW     = Math.min(typeof json.width !== 'undefined' ? json.width : 600, jq(document).width());
			var winH     = Math.min(typeof json.height!== 'undefined' ? json.height: 400, jq(document).height());
			var wCollapse= typeof json.wCollapse !== 'undefined' ? json.wCollapse : false;
			var wMinimize= typeof json.wMinimize !== 'undefined' ? json.wMinimize : false;
			var wMaximize= typeof json.wMaximize !== 'undefined' ? json.wMaximize : false;
			var wClosable= typeof json.wClosable !== 'undefined' ? json.wClosable : true;
			if (jq('#'+id).length) {
                jq('#'+id).window('open');
            } else {
                var newdiv1 = jq('<div id="'+id+'" title="'+json.title+'" class="easyui-window"></div>');
                jq('body').append(newdiv1);
            }
            jq('#'+id).window({ title:title, top:winT, left:winL, width:winW, height:winH, modal:true, closable:wClosable, collapsible:wCollapse, minimizable:wMinimize, maximizable:wMaximize });
			jq('#'+id).window('center'); // center the window
			if (typeof json.href != 'undefined') jq('#'+id).window('refresh', json.href);
			else {
                jq('#'+id).html(json.html);
                jq.parser.parse('#'+id);
            }
			break;
		default: // if (!json.action) alert('response had no action! Bailing...');
	}
}

/**
 * This function extracts the returned messageStack messages and displays then according to the severity
 */
function displayMessage(message) {
	var msgText = '';
	var imgIcon = '';
    // Process errors and warnings
	if (message.error) for (var i=0; i<message.error.length; i++) {
		msgText += '<span>'+message.error[i].text+'</span><br />';
		imgIcon = 'error';
	}
	if (message.warning) for (var i=0; i<message.warning.length; i++) {
		msgText += '<span>'+message.warning[i].text+'</span><br />';
		if (!imgIcon) imgIcon = 'warning';
	}
	if (message.caution) for (var i=0; i<message.caution.length; i++) {
		msgText += '<span>'+message.caution[i].text+'</span><br />';
		if (!imgIcon) imgIcon = 'warning';
	}
	if (msgText) jq.messager.alert({title:'',msg:msgText,icon:imgIcon,width:600});
    // Now process Info and Success
    if (message.info) {
        msgText = '';
    	msgID   = Math.floor((Math.random() * 1000000) + 1); // random ID to keep boxes from stacking and crashing easyui
        for (var i=0; i<message.info.length; i++) {
            msgTitle = typeof message.info[i].title !== 'undefined' ? message.info[i].title : jq.i18n('INFORMATION');
            msgText += '<span>'+message.info[i].text+'</span><br />';
        }
        processJson( { action:'window', id:msgID, title:msgTitle, html:msgText } );
    }
	if (message.success) {
		msgText = '';
		for (var i=0; i<message.success.length; i++) {
			msgText += '<span>'+message.success[i].text+'</span><br />';
		}
		jq.messager.show({ title: jq.i18n('MESSAGE'), msg: msgText, timeout:5000, width:400, height:200 });
	}
}

function dashboardAttr(path, id) {
	var temp        = path.split(':');
	var moduleID    = temp[0];
	var dashboardID = temp[1];
	var gData = '&menuID='+menuID+'&moduleID='+moduleID+'&dashboardID='+dashboardID;
	if (id) gData += '&rID='+id; // then there was a row action
	var form = jq('#'+dashboardID+'Form');
	jq.ajax({
		type: 'POST',
		url:  bizunoAjax+'&p=bizuno/dashboard/attr'+gData,
		data: form.serialize(),
		success: function(json) { processJson(json); jq('#'+dashboardID).panel('refresh'); }
	});
	return false;
}

/**
 * This function deletes a selected dashboard from the displayed menu
 */
function dashboardDelete(obj) {
	var p = jq(obj).panel('options');
	jq.ajax({
		type: 'GET',
		url:  bizunoAjax+'&p=bizuno/dashboard/delete&menuID='+menuID+'&moduleID='+p.module_id+'&dashboardID='+p.id,
		success: function (json) { processJson(json); }
	});
	return true;
}
// ****************** Multi-submit Operations ***************************************/
function cronInit(baseID, urlID) {
    winHTML = '<p>&nbsp;</p><p style="text-align:center"><progress id="prog'+baseID+'"></progress></p><p style="text-align:center"><span id="msg'+baseID+'">&nbsp;</span></p>';
    processJson({action:'window', id:'win'+baseID, title:jq.i18n('PLEASE_WAIT'), html:winHTML, width:400, height:200});
    jq.ajax({ url:bizunoAjax+'&p='+urlID, async:false, success:cronResponse });
}

function cronRequest(baseID, urlID) {
    jq.ajax({ url:bizunoAjax+'&p='+urlID, async:false, success:cronResponse });
}

function cronResponse(json) {
    if (json.message) displayMessage(json.message);
    jq('#msg' +json.baseID).html(json.msg+' ('+json.percent+'%)');
    jq('#prog'+json.baseID).attr({ value:json.percent,max:100});
    if (json.percent < 100) {
        window.setTimeout("cronRequest('"+json.baseID+"','"+json.urlID+"')", 500);
    } else { // finished
        processJson(json);
        jq('#btn'+json.baseID).show();
        jq('#win'+json.baseID).window({title:jq.i18n('FINISHED')});
    }
}

//*********************************** General Functions *****************************************/
function imgManagerInit(imgID, src, srcPath, myFolder) 
{
	var noImage  = "lib/images/bizuno.png";
	var divInvImg= '';
    var divTB    = '';
	path = src==='' ? noImage : (myFolder+src);
	var viewAction  = "jq('#imdtl_"+imgID+"').window({ width:700,height:540,modal:true,title:'Image' }).window('center');";
    viewAction     += "var q = jq('#img_"+imgID+"').attr('src'); jq('#imdtl_"+imgID+"').html(jq('<img>',{id:'viewImg',src:q}));";
    viewAction     += "jq('#viewImg').css({'max-height':'100%','max-width':'100%'});";
	var editAction  = "jsonAction('bizuno/image/manager&imgMgrPath="+srcPath+"&imgTarget="+imgID+"');";
	var trashAction = "jq('#img_"+imgID+"').attr('src','"+bizunoAjaxFS+"&src=0/"+noImage+"'); jq('#"+imgID+"').val('');";
	divInvImg      += '<div><a id="im_'+imgID+'" href="javascript:void(0)">';
	divInvImg      += '  <img type="img" width="145" src="'+bizunoAjaxFS+'&src=0/'+path+'" name="img_'+imgID+'" id="img_'+imgID+'" /></a></div><div id="imdtl_'+imgID+'"></div>';
    divTB  = '<a href="#" onClick="'+viewAction +'" class="easyui-linkbutton" title="'+jq.i18n('VIEW') +'" data-options="iconCls:\'icon-search\',plain:true"></a>';
	divTB += '<a href="#" onClick="'+editAction +'" class="easyui-linkbutton" title="'+jq.i18n('EDIT') +'" data-options="iconCls:\'icon-edit\',  plain:true"></a>';
	divTB += '<a href="#" onClick="'+trashAction+'" class="easyui-linkbutton" title="'+jq.i18n('TRASH')+'" data-options="iconCls:\'icon-trash\', plain:true"></a>';

	jq('#'+imgID).after(divInvImg);
    jq('#im_'+imgID).tooltip({
        hideEvent: 'none',
        showEvent: 'click',
        position: 'bottom',
        content:  jq('<div></div>'),
        onUpdate: function(content) {
            content.panel({
                width: 100,
                border: false,
                content: divTB
            });
        },
        onShow: function() { 
            var t = jq(this);
            t.tooltip('tip').unbind().bind('mouseenter', function(){
                t.tooltip('show');
            }).bind('mouseleave', function(){
                t.tooltip('hide');
            });
        }
    });
}

function initGLAcct(obj) {
	if (obj.id === "") obj.id = 'tempGL';
	jq('#'+obj.id).combogrid({
		data: bizDefaults.glAccounts,
		width: 300,
		panelWidth: 450,
		idField: 'id',
		textField: 'title',
		columns: [[
			{field:'id',   title:jq.i18n('ACCOUNT'),width: 60},
			{field:'title',title:jq.i18n('TITLE'),  width:200},
			{field:'type', title:jq.i18n('TYPE'),   width:180}
		]]
	});
	// jq('#'+obj.id).combogrid('showPanel'); // displays in upper left corner if instantiated inside hidden div
	jq('#'+obj.id).combogrid('resize',120);
	if (obj.id === "tempGL") obj.id = "";
}

function viewLabels(divID) {
	var max = 0;
	if (jq("#"+divID).length != 0) {
		jq('#'+divID+' label').each(function(){ 
		if (jq(this).textWidth(jq(this).text()) > max) max = jq(this).textWidth(jq(this).text()); });
		jq('#'+divID+' label').attr('style', 'display:inline-block;width:'+(max*1.1)+'px;');
	} else { // all labels on page
		jq('label').each(function(){ if (jq(this).width() > max) max = jq(this).width(); });
		jq('label').attr('style', 'display:inline-block;width:'+max+'px;');
	}
}

function setInnerLabels(arrFields, suffix) {
    jq.each(arrFields, function (index, value) {
        var objField = jq('#'+value+suffix);
        if (objField) {
            var labelText = objField.prev().hide().html();
            objField.focus(function() {
                if (objField.val() == labelText) {
                    objField.val('');
                    objField.removeClass('default-value');
                    objField.css({color:'#000000'});
                }
            });
            objField.blur(function() {
                if (objField.val() == '') { 
                    objField.val(labelText); objField.css({color:'#CCCCCC'});
                }
                if (objField.val() == labelText) {
                    objField.addClass('default-value');
                }
            });
            objField.blur();
        }
	});
}

function clearInnerLabels(arrFields, suffix) {
    jq.each(arrFields, function (index, value) {
        var objField = jq('#'+value+suffix);
        var labelText= objField.prev().hide().html();
        if (objField.val() == labelText) objField.val('');
	});
}

/* ****************************** Currency Functions ****************************************
 * this function takes a locale formatted currency string and formats it into a float value
 * @param string amount - Locale formatted currency value
 * @param string currency - ISO currency code to convert from
 * @return float Converted currency value
 */
function cleanCurrency(amount) {
	if (typeof amount == 'undefined') return 0;
	currency = jq('#currency').val() ? jq('#currency').val() : bizDefaults.currency.defaultCur;
	if (!bizDefaults.currency.currencies[currency]) {
		alert('Error - cannot find currency: '+currency+' to clean! Returning unformattted value!');
		return amount;
	}
	if (bizDefaults.currency.currencies[currency].prefix) amount = amount.toString().replace(bizDefaults.currency.currencies[currency].prefix, '');
	if (bizDefaults.currency.currencies[currency].suffix) amount = amount.toString().replace(bizDefaults.currency.currencies[currency].suffix,'');
	var sep   = bizDefaults.currency.currencies[currency].sep;
	var dec_pt= bizDefaults.currency.currencies[currency].dec_pt;
	amount    = amount.toString().replace(new RegExp("["+sep+"]", "g"), '');
	amount    = parseFloat(amount.replace(new RegExp("["+dec_pt+"]", "g"), '.'));
	return amount;
}

/**
 * Rounds a number to the proper number of decimal places for currency values
 * @returns float
 */
function roundCurrency(value)
{
    var decLen  = parseInt(bizDefaults.currency.currencies[currency].dec_len);
	var adj     = Math.pow(10, (decLen+3));
    var newValue= value + (1/adj);
    var newTmp  = parseFloat(newValue.toFixed(decLen));
    return newTmp;
}

/**
 * This function formats a decimal value into the currency format specified in the form
 * @param decimal amount - decimal amount to format
 * @param boolean pfx_sfx - [default: true] determines whether or not to include the prefix and suffix, setting to false will just return number
 * @returns formatted string to ISO currency format
 */
function formatCurrency(amount, pfx_sfx, isoCur, excRate) { // convert to expected currency format
	if (typeof pfx_sfx == 'undefined') { pfx_sfx= true; }
    if (typeof isoCur  == 'undefined') { isoCur = bizDefaults.currency.defaultCur; }
    if (typeof excRate == 'undefined') { excRate = 1; }
	currency = jq('#currency').val() ? jq('#currency').val() : isoCur;
	if (!bizDefaults.currency.currencies[currency]) {
		alert('Error - cannot find currency: '+currency+' to format! Returning unformattted value!');
		return amount;
	}
	var dec_len= parseInt(bizDefaults.currency.currencies[currency].dec_len);
	var sep    = bizDefaults.currency.currencies[currency].sep;
	var dec_pt = bizDefaults.currency.currencies[currency].dec_pt;
	var pfx    = bizDefaults.currency.currencies[currency].prefix;
	var sfx    = bizDefaults.currency.currencies[currency].suffix;
//alert('found currency = '+currency+' and decimal point = '+dec_pt+' and separator = '+sep);
	if (typeof dec_len === 'undefined') dec_len = 2;
	// amount needs to be a string type with thousands separator ',' and decimal point dot '.'
	var factor  = Math.pow(10, dec_len);
	var adj     = Math.pow(10, (dec_len+3)); // to fix rounding (i.e. .1499999999 rounding to 0.14 s/b 0.15)
	var wholeNum = parseFloat(amount * excRate);
	if (isNaN(wholeNum)) return amount;
	var numExpr = Math.round((wholeNum * factor) + (1/adj));
    var calcAmt = (wholeNum * factor) + (1/adj);
//if (amount) alert('original amount = '+amount+' and parsed float to '+wholeNum+' multiplied by '+factor+' and adjusted by 1/'+adj+' calculated to '+calcAmt+' which rounded to: '+numExpr);
	var minus   = (numExpr < 0) ? '-' : '';
	numExpr     = Math.abs(numExpr);
	var decimal = (numExpr % factor).toString();
	while (decimal.length < dec_len) decimal = '0' + decimal;
	var whole   = Math.floor(numExpr / factor).toString();
	for (var i = 0; i < Math.floor((whole.length-(1+i))/3); i++)
		whole = whole.substring(0,whole.length-(4*i+3)) + sep + whole.substring(whole.length-(4*i+3));
    if (!pfx_sfx) {
		if (dec_len > 0) return minus + whole + dec_pt + decimal;
		return minus + whole;
	}
	if (dec_len > 0) return pfx + minus + whole + dec_pt + decimal + sfx;
	return minus + pfx + whole + sfx;
}

function formatPrecise(amount) { // convert to expected currency format with the additional precision
	currency = jq('#currency').val() ? jq('#currency').val() : bizDefaults.currency.defaultCur;
	if (!bizDefaults.currency.currencies[currency]) {
		alert('Error - cannot find currency: '+currency+' to format precise! Returning unformattted value!');
		return amount;
	}
	var sep   = bizDefaults.currency.currencies[currency].sep;
	var dec_pt= bizDefaults.currency.currencies[currency].dec_pt;
	var decimal_precise = bizDefaults.locale.precision;
	if (typeof decimal_precise === 'undefined') decimal_precise = 4;
	// amount needs to be a string type with thousands separator ',' and decimal point dot '.'
	var factor  = Math.pow(10, decimal_precise);
	var adj     = Math.pow(10, (decimal_precise+2)); // to fix rounding (i.e. .1499999999 rounding to 0.14 s/b 0.15)
	var numExpr = parseFloat(amount);
	if (isNaN(numExpr)) return amount;
	numExpr     = Math.round((numExpr * factor) + (1/adj));
	var minus   = (numExpr < 0) ? '-' : '';
	numExpr     = Math.abs(numExpr);
	var decimal = (numExpr % factor).toString();
	while (decimal.length < decimal_precise) decimal = '0' + decimal;
	var whole   = Math.floor(numExpr / factor).toString();
	for (var i = 0; i < Math.floor((whole.length-(1+i))/3); i++)
		whole = whole.substring(0,whole.length-(4*i+3)) + sep + whole.substring(whole.length-(4*i+3));
	if (decimal_precise > 0) return minus + whole + dec_pt + decimal;
	return minus + whole;
}

/**
 * 
 * This function takes a value and converts it from one ISO to another
 * @param float value - Value to convert
 * @param string destISO - destination ISO to convert to
 * @param string sourceISO - [default bizDefaults.currency.defaultCur] ISO code to use to convert from
 * @returns float - converted to destISO code
 */
function convertCurrency(value, destISO, sourceISO) {
	var defaultISO = bizDefaults.currency.defaultCur;
	if (typeof sourceISO == 'undefined') sourceISO = bizDefaults.currency.defaultCur;
	if (!bizDefaults.currency.currencies[sourceISO]) {
		alert('Error - cannot find source currency to format! Returning unformattted value!');
		return value;
	}
	if (!bizDefaults.currency.currencies[destISO]) {
		alert('Error - cannot find destination currency to format! Returning unformattted value!');
		return value;
	}
	var srcVal = parseFloat(value);
	if (isNaN(srcVal)) {
		alert('Error - the value submitted is not a number! Returning unformattted value!');
		return value;
	}
	if (sourceISO != defaultISO) srcVal = srcVal * parseFloat(bizDefaults.currency.currencies[sourceISO].value); // convert to defaultISO
	if (parseFloat(bizDefaults.currency.currencies[destISO].value) == 0) {
		alert('currenct exchange rate is zero! This should not happen.');
		return value;
	}
	newValue = srcVal != 0 ? srcVal / parseFloat(bizDefaults.currency.currencies[destISO].value) : 0; // convert to destISO
	return newValue;
}

/******************************* Number Functions ****************************************
 * This function takes a locale formatted number string and formats it into a float value
 * @param string amount - Locale formatted value
 * @return float Converted value
 */
function cleanNumber(amount) {
	if (typeof amount == 'undefined') return 0;
	var sep = bizDefaults.locale.thousand;
	amount = amount.toString().replace(new RegExp("["+sep+"]", "g"), '');
	var dec = bizDefaults.locale.decimal;
	amount = parseFloat(amount.replace(new RegExp("["+dec+"]", "g"), '.'));
	return amount;
}

function formatNumber(amount) {
	var dec_len= (typeof bizDefaults.locale.precision !== 'undefined') ? bizDefaults.locale.precision : 2;
	var sep    = (typeof bizDefaults.locale.thousand !== 'undefined') ? bizDefaults.locale.thousand : '.';
	var dec_pt = (typeof bizDefaults.locale.decimal !== 'undefined') ? bizDefaults.locale.decimal : ',';
	var pfx    = (typeof bizDefaults.locale.prefix !== 'undefined') ? bizDefaults.locale.prefix : '';
	var sfx    = (typeof bizDefaults.locale.suffix !== 'undefined') ? bizDefaults.locale.suffix : '';
	var negpfx = (typeof bizDefaults.locale.neg_pfx !== 'undefined') ? bizDefaults.locale.neg_pfx : '-';
	var negsfx = (typeof bizDefaults.locale.neg_sfx !== 'undefined') ? bizDefaults.locale.neg_sfx : '';
	var is_negative = false;
//alert('found decimal point = '+dec_pt+' and seprator = '+sep);
	if (typeof dec_len === 'undefined') dec_len = 2;
	// amount needs to be a string type with thousands separator ',' and decimal point dot '.'
	var factor  = Math.pow(10, dec_len);
	var adj     = Math.pow(10, (dec_len+2)); // to fix rounding (i.e. .1499999999 rounding to 0.14 s/b 0.15)
	var numExpr = parseFloat(amount);
	if (isNaN(numExpr)) return amount;
	if (numExpr < 0) is_negative = true;
	numExpr     = Math.round((numExpr * factor) + (1/adj));
	numExpr     = Math.abs(numExpr);
	var decimal = (numExpr % factor).toString();
	while (decimal.length < dec_len) decimal = '0' + decimal;
	var whole   = Math.floor(numExpr / factor).toString();
	for (var i = 0; i < Math.floor((whole.length-(1+i))/3); i++)
		whole = whole.substring(0,whole.length-(4*i+3)) + sep + whole.substring(whole.length-(4*i+3));
	if (is_negative) {
		if (dec_len > 0) return negpfx + whole + dec_pt + decimal + negsfx;
		return negpfx + whole + negsfx;
	}
	if (dec_len > 0) return pfx + whole + dec_pt + decimal + sfx;
	return pfx + whole + sfx;
}

/******************************* Date Functions ****************************************
 * Formats a database date (YYYY-MM-DD) date to local format, the datebox calls this to format the date
 * @todo broken needs to take into account UTC, returns a day earlier
 * @param str - db date in string format YYYY-MM-DD
 * @returns formatted date by users locale definition
 */
function formatDate(str) {
	var output = bizDefaults.calendar.format;
    if (typeof str !== 'string' || typeof sDate === 'object') { // easyui date formatter, or full ISO date
        var objDate = new Date(str);
        var Y = objDate.getFullYear();
        var m = ("0" + (objDate.getMonth() + 1)).slice(-2);
        var d = ("0" + objDate.getDate()).slice(-2);
    } else {
        var Y = str.substr(0,4);
        var m = str.substr(5,2);
        var d = str.substr(8,2);
    }
	output = output.replace("Y", Y);
	output = output.replace("m", m);
	output = output.replace("d", d);
//	alert('started with date = '+str+' and ended with = '+output);
	return output;
}

/**
 * Convert the users locale date to db format to use with Date() object
 * @returns integer
 */
function dbDate(str) {
    var fmt  = bizDefaults.calendar.format;
    var delim= bizDefaults.calendar.delimiter;
    var parts= fmt.split(delim);
    var src  = str.split(delim);
    for (var i=0; i < parts.length; i++) {
        if (parts[i] == 'Y') { var Y = src[i]; }
        if (parts[i] == 'm') { var m = src[i]; }
        if (parts[i] == 'd') { var d = src[i]; }
    }
    return Y+'-'+m+'-'+d;
}

/**
 * 
 * @param {type} ref
 * @returns integer, -1 if less, 0 if equal, 1 if greater
 */
function compareDate(ref) {
    var d1 = new Date(ref);
    var d2 = new Date();
    if (d1 <  d2) return -1;
    if (d1 > d2) return 1;
    return 0;
}

/* ****************************** General Functions ****************************************/
function getTextValue(arrList, index) {
	if (index === 'undefined') return '';
	if (!arrList.length) return index;
	for (var i in arrList) { if (arrList[i].id == index) return arrList[i].text; } // must use == NOT === or doesn't work
	return index;
}

function tinymceInit(fldID) {
    if (typeof tinymce == 'undefined') return;
    tinymce.init({ 
        selector:'textarea#'+fldID,
        height: 400,
        width: 600,
        plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table contextmenu paste code'],
        setup: function (editor) { editor.on('change', function () { editor.save(); }); }
    });
}

function encryptChange() {
	var gets = "&orig="+jq("#encrypt_key_orig").val()+"&new=" +jq("#encrypt_key_new").val()+"&dup=" +jq("#encrypt_key_dup").val();
	jq.ajax({ 
		url: bizunoAjax+'&p=bizuno/tools/encryptionChange'+gets, 
		success: function(json) {
			processJson(json);
			jq("#encrypt_key_orig").val('');
			jq("#encrypt_key_new").val('');
			jq("#encrypt_key_dup").val('');
		}
	});
}

function makeRequest(url) { jq.ajax({ url: url, success: server_response }); }
function server_response(json) {
	processJson(json);
  jq('progress').attr({value:json.percent,max:100});
  jq('#pl').html(json.pl);
  jq('#pq').html(json.pq);
  jq('#ps').html(json.ps);
  if (json.percent == '100') {
	  jq("#divRestoreCancel").toggle('slow');
	  jq("#divRestoreFinish").toggle('slow');
  } else {
	if (restoreCancel == 'cancel') {
	  jq("#divRestoreCancel").toggle('slow');
	} else {
	  url_request = bizunoAjax+"&p=bizuno/main/restoreAjax&start="+json.linenumber+"&fn="+json.fn+"&foffset="+json.foffset+"&totalqueries="+json.totalqueries;
	  window.setTimeout("makeRequest(url_request)",500);
	}
  }
}

// *********************************** Contact Functions *****************************************/
function crmDetail(rID, suffix) {
	jq.ajax({
		url:bizunoAjax+'&p=contacts/main/details&rID='+rID,
		success: function(json) {
			processJson(json);
			jq('#id'            +suffix).val(json.contact.id);
			jq('#short_name'    +suffix).val(json.contact.short_name);
			jq('#contact_first' +suffix).val(json.contact.contact_first);
			jq('#contact_last'  +suffix).val(json.contact.contact_last);
			jq('#flex_field_1'  +suffix).val(json.contact.flex_field_1);
			jq('#account_number'+suffix).val(json.contact.account_number);
			jq('#gov_id_number' +suffix).val(json.contact.gov_id_number);
			jq('#terms'         +suffix).val(json.contact.terms);
			jq('#terms' +suffix+'_text').val(json.contact.terms_text);
			for (var i = 0; i < json.address.length; i++) if (json.address[i].type == 'm') addressFill(json.address[i], suffix);
		}
	});
}

function addressFill(address, suffix) {
  for (key in address) {
	  jq('#'+key+suffix).val(address[key]).css({color:'#000000'}).blur();
	  if (key == 'country') jq('#country'+suffix).combogrid('setValue', address[key]);
  }
  jq('#contact_id'+suffix).val(address['ref_id']);
}

function clearAddress(suffix) {
	jq('#address'+suffix).find('input, select').each(function(){
		jq(this).val('');
		jq(this).attr('checked',false);
	});
	jq('#country'+suffix).combogrid('setValue',bizDefaults.country.iso).combogrid('setText', bizDefaults.country.title);
}

function addressClear(suffix) {
    jq.each(addressFields, function (index, value) { 
        jq('#'+value+suffix).val('');
        jq('#'+value+suffix).blur();
    });
	if (suffix != '_s') { jq('#addressDiv'+suffix).hide(); }
	jq('#country'+suffix).combogrid('setValue', bizDefaults.country.iso).combogrid('setText',  bizDefaults.country.title);
}

function addressCopy(fromSuffix, toSuffix) {
    jq.each(addressFields, function (index, value) {
        jq('#'+value+toSuffix).val(jq('#'+value+fromSuffix).val()).css({color:'#000000'}).blur();
    });
	jq('#country'+toSuffix).combogrid('setValue', jq('#country'+fromSuffix).combogrid('getValue'));
	// Clear the ID's so Add/Updates don't erase the source record
	jq('#id'+toSuffix).val('');
	jq('#address_id'+toSuffix).val('');
  }

function shippingValidate(suffix) {
	var temp = {};
	jq('#address'+suffix+' input').each(function() {
		var labelText = jq(this).prev().html();
		if (jq(this).val() != labelText) {
			fld = jq(this).attr('id');
			if (typeof fld != 'undefined') {
				fld = fld.slice(0, - suffix.length);
				temp[fld] = jq(this).val();
			}
		}
	});
	setInnerLabels(addressFields, suffix);
	temp['country'] = jq('#country'+suffix).combobox('getValue');
	temp['method_code'] = jq('#method_code').val();
	temp['suffix'] = suffix;
	var ship = encodeURIComponent(JSON.stringify(temp));
	jsonAction('extShipping/address/validateAddress', 0, ship);
}

//*********************************** Chart functions *****************************************/
jq.cachedScript('https://www.gstatic.com/charts/loader.js');
function drawBizunoChart(json) {
    var divWidth = parseInt(jq('#'+json.divID).width());
    var divHeight = parseInt(divWidth * 3 / 4);
    var data = google.visualization.arrayToDataTable(json.data);
    var options = {width:divWidth};
    for (var idx in json.attr) { options[idx] = json.attr[idx]; }
    switch (json.type) {
        default:
        case 'pie':   var chart = new google.visualization.PieChart(document.getElementById(json.divID));   break;
        case 'bar':   var chart = new google.visualization.BarChart(document.getElementById(json.divID));   break;
        case 'column':var chart = new google.visualization.ColumnChart(document.getElementById(json.divID));break;
        case 'guage': var chart = new google.visualization.Guage(document.getElementById(json.divID));      break;
        case 'line':  var chart = new google.visualization.LineChart(document.getElementById(json.divID));  break;
    }
    chart.draw(data, options);
}
