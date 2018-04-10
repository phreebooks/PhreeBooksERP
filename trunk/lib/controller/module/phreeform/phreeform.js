/*
 * JavaScript functions specific to PhreeForm module
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
 * @version    2.x Last Update: 2017-10-15
 * @filesource /controller/module/phreeform/phreeform-2.0.4.js
 * 
 */

var fieldEvent = ''; // tracks index clicked to set field properties

function sessionTables() {
	var table  = '';
  	var rowData= jq('#dgTables').edatagrid('getData');
  	for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) table += rowData.rows[rowIndex].tablename + ':';
	jsonAction('phreeform/design/getTablesSession', 0, table);
}

function phreeformBody() {
	var rID = jq('input[name=rID]:checked', '#frmPhreeform').val();
	var vars = [], hash;
	var q = document.URL.split('?')[1];
	if (q != undefined) {
	    q = q.split('&');
	    for (var i = 0; i < q.length; i++) {
	        hash = q[i].split('=');
	        vars.push(hash[1]);
	        vars[hash[0]] = hash[1];
	    }
	}
	var params = '&rID='+rID;
	if (vars['xfld']) params += '&xfld='+vars['xfld'];
	if (vars['xcr'])  params += '&xcr=' +vars['xcr'];
	if (vars['xmin']) params += '&xmin='+vars['xmin'];
	if (vars['xmax']) params += '&xmax='+vars['xmax'];
	jq.ajax({ // pull the report body
		url:     bizunoAjax+'&p=phreeform/render/phreeformBody'+params,
		success: function(json) { 
			processJson(json);
			if (json.msgBody) {
                jq('#fromName').val(json.fromName);
                jq('#fromEmail').val(json.fromEmail);
				jq('#msgSubject').val(json.msgSubject);
				jq('#msgBody').val(json.msgBody.replace(/<br \/>/g, "\n"));
			}
		}
	});
}

function comboFields(field, type) {
	switch (type) {
	case 'CDta':
	case 'CBlk':
		jq(field).combobox({  
			editable:   false,
			data:       bizData,
			valueField: 'id',
			textField : 'text',
			formatter : function(row){ var opts = jq(this).combobox('options'); return row[opts.textField]; }
		});
		break;
	default:
		jq(field).combobox({  
			editable:   true,
			mode:       'remote',
			url:        bizunoAjax+'&p=phreeform/design/getFields',
			valueField: 'id',
			textField : 'text',
			onLoadSuccess: function() { jq(this).combobox('resize', 200); },
			formatter :    function(row){ var opts = jq(this).combobox('options'); return row[opts.textField]; }
		});
	}
}

function comboBarCodes(field) {
	jq(field).combobox({
		editable:   false,
		data:       dataBarCodes,
		valueField: 'id',
		textField:  'text',
		formatter:  function(row){ var opts = jq(this).combobox('options'); return row[opts.textField]; }
	});
}

function comboProcessing(field) {
	jq(field).combobox({
		editable:   false,
		data:       dataProcessing,
		valueField: 'id',
		textField:  'text',
		groupField: 'group',
		formatter:  function(row){ var opts = jq(this).combobox('options'); return row[opts.textField]; }
	});
}

function comboFormatting(field) {
	jq(field).combobox({
		editable:   false,
		data:       dataFormatting,
		valueField: 'id',
		textField:  'text',
		groupField: 'group',
		formatter:  function(row){ var opts = jq(this).combobox('options'); return row[opts.textField]; }
	});
}

function importSearch() {
	alert('The feature is not yet available. Please fill out a support ticket to check on development status.');
}
