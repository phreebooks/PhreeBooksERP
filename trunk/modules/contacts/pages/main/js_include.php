<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/contacts/pages/main/js_include.php
//
?>
<script type="text/javascript">

// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var attachment_path = '<?php echo urlencode($basis->cInfo->contact->dir_attachments); ?>';
var default_country = '<?php echo COMPANY_COUNTRY; ?>';
var account_type    = '<?php echo $basis->cInfo->contact->type; ?>';

// Insert other page specific functions here.
function loadContacts() {
//  var guess = document.getElementById('dept_rep_id').value;
  var guess = document.getElementById('dept_rep_id').value;
//  document.getElementById('dept_rep_id').options[0].text = guess;
  if (guess.length < 3) return;
  $.ajax({
    type: "GET",
    url: 'index.php?module=contacts&page=ajax&op=load_contact_info&guess='+guess,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillContacts
  });
}

function contactdoSearch(value){
	console.log('A contact search was requested.');
	$.messager.progress();
	$('#cdg').datagrid('load',{
		dept_rep_id: $('#id').val(),
		search_text: $('#Contacts_search_text').val(),
		dataType: 'json',
        contentType: 'application/json',
        type: '<?php echo $basis->cInfo->type;?>',
        contact_show_inactive: $('#contacts_show_inactive').is(":checked") ? 1 : 0,
	});
}

function newContact(){
    $('#cdg').datagrid('appendRow',{isNewRecord:true});
    var index = $('#dg').datagrid('getRows').length - 1;
    $('#cdg').datagrid('expandRow', index);
    $('#cdg').datagrid('selectRow', index);
}

$('#cdg').datagrid({
	url:		"index.php?action=GetAllContacts",
	queryParams: {
		dept_rep_id: $('#id').val(),
		type: '<?php echo $basis->cInfo->type;?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false
	},
	onLoadSuccess:function(data){
		console.log('the loading of the contacts datagrid was succesfull');
		$.messager.progress('close');
	},
	onLoadError: function(){
		console.log('the loading of the contacts datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error:'+arguments.responseText);
	},
	onDblClickRow: function(index , row){
		console.log('a row in the datagrid was double clicked');
		$('#contactsWindow').window('open').window('center').window('setTitle',"<?php echo TEXT_EDIT?>"+ ' ' + row.name);
	},
	remoteSort:	false,
	idField:	"contactid",
	fitColumns:	true,
	singleSelect:true,
	sortName:	"name",
	sortOrder: 	"asc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	toolbar: 	"#ContactsToolbar",
	rowStyler: function(index,row){
		if (row.inactive == '1')return 'background-color:pink;';
	},
    onExpandRow: function(index,row){
        var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
        ddv.panel({
            border:false,
            cache:true,
            onBeforeLoad: function(param){
				var row = $('#cdg').datagrid('getSelected');
				param.contactid = row.contactid;
			},
            href:'index.php?action=editContact',
            queryParams: {
				type: '<?php echo $basis->cInfo->type;?>',
				dataType: 'html',
                contentType: 'text/html',
                async: false
			},
            onLoad:function(){
                $('#cdg').datagrid('fixDetailRowHeight',index);
                $('#cdg').datagrid('selectRow',index);
                $('#cdg').datagrid('getRowDetail',index).find('form').form('load',row);
            }
        });
        $('#cdg').datagrid('fixDetailRowHeight',index);
    },
    view: detailview,
    detailFormatter:function(index,row){
        return '<div class="ddv"></div>';
    },
});

// ajax response handler call back function
function fillContacts(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  while (document.getElementById('comboseldept_rep_id').options.length) document.getElementById('comboseldept_rep_id').remove(0);
  var iIndex = 0;
  $(xml).find("guesses").each(function() {
	newOpt = document.createElement("option");
	newOpt.text = $(this).find("guess").text() ? $(this).find("guess").text() : '<?php echo TEXT_FIND. ' ...'; ?>';
	document.getElementById('comboseldept_rep_id').options.add(newOpt);
	document.getElementById('comboseldept_rep_id').options[iIndex].value = $(this).find("id").text();
	if (!fActiveMenu) cbMmenuActivate('dept_rep_id', 'combodivdept_rep_id', 'comboseldept_rep_id', 'imgNamedept_rep_id');
	document.getElementById('dept_rep_id').focus();
	iIndex++;
  });
}


</script>
<script type="text/javascript" src="modules/contacts/javascript/contacts.js"></script>