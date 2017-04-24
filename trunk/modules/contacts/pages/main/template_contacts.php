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
//  Path: /modules/contacts/pages/main/template_contacts.php
//
?>
<div title="<?php echo TEXT_CONTACTS;?>" >
	<table id="cdg" title="<?php echo sprintf(TEXT_MANAGER_ARGS, $contact);?>"  style="height:500px;padding:50px;">
    	<thead>
       		<tr>
       			<th data-options="field:'name',sortable:true"><?php echo TEXT_NAME;?></th>
       			<th data-options="field:'title',sortable:true"><?php echo TEXT_TITLE?></th>
	           	<th data-options="field:'telephone1',sortable:true"><?php echo TEXT_TELEPHONE?></th>
	           	<th data-options="field:'telephone4',sortable:true"><?php echo TEXT_MOBILE_PHONE?></th>
	           	<th data-options="field:'email',sortable:true"><?php echo TEXT_EMAIL?></th>
	        </tr>
	     </thead>
	</table>
	<div id="ContactsToolbar" >
	    <a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newContact()"><?php echo sprintf(TEXT_NEW_ARGS, $contact);?></a>
    	<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editContact()"><?php echo sprintf(TEXT_EDIT_ARGS, $contact);?></a>
    	<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteContact()"><?php echo sprintf(TEXT_DELETE_ARGS, $contact);?></a>
    	<?php echo \core\classes\htmlElement::checkbox('contact_show_inactive', TEXT_SHOW_INACTIVE,'1', false, 'onchange="contactdoSearch()"' );?>
    	<div style="float: right;"><?php echo \core\classes\htmlElement::search('Contacts_search_text','contactdoSearch');?></div>
    </div>
</div>
<script type="text/javascript">
$('#cdg').datagrid({
	url:		"index.php?action=GetAllContacts",
	queryParams: {
		dept_rep_id: '<?php echo $basis->cInfo->contact->id;?>',
		type: '<?php echo $basis->cInfo->type;?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false
	},
	onBeforeLoad:function(){
		console.log('loading of the contacts datagrid');
	},
	onLoadSuccess:function(data){
		console.log('the loading of the contacts datagrid was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the contacts datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table contacts');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the contacts datagrids was double clicked');
		$('#cdg').datagrid('expandRow', index);
	},
	onCollapseRow: function(index , row){
		if (row.isNewRecord){
	        $('#cdg').datagrid('deleteRow',index);
	    }
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
	view: detailview,
	detailFormatter:function(index,row){
        return '<div class="ddv"></div>';
    },
    onExpandRow: function(index, row){
        var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
        ddv.panel({
            border:false,
            queryParams: {
            	contentType:'inlineForm',
		        async: false,
			},
			width: '600px',
            cache: false,
            href:'index.php?action=editContactRelation&contact_id=<?php echo $basis->cInfo->contact->id;?>',
            loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
            onLoad:function(){
                $('#cdg').datagrid('fixDetailRowHeight',index);
                $('#cdg').datagrid('selectRow',index);
                $('#cdg').datagrid('getRowDetail',index).find('form').form('load',row);
            },
            onBeforeLoad:function(param){
                param.contactid = row.contactid;
        		console.log('loading the contacts form');
        	},
        	onLoadSuccess:function(data){
        		console.log('the loading the contacts form was succesfull');
        		$.messager.progress('close');
        		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
        	},
            onLoadError: function(){
        		console.error('the loading of the contacts form resulted in a error');
        		$.messager.progress('close');
        		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for contacts form');
        	},
        });
    }
});

function contactdoSearch(value){
	console.log('A search was requested.');
	$('#cdg').datagrid('load',{
		dept_rep_id: $('#id').val(),
		search_text: $('#Contacts_search_text').val(),
		dataType: 'json',
		async: false,
        contentType: 'application/json',
        type: '<?php echo $basis->cInfo->type;?>',
        contact_show_inactive: document.getElementById('contact_show_inactive').checked ? 1 : 0,
	});
}

function newContact(){
	console.log('new contact was clicked');
    $('#cdg').datagrid('appendRow',{isNewRecord:true});
    var index = $('#cdg').datagrid('getRows').length - 1;
    $('#cdg').datagrid('expandRow', index);
    $('#cdg').datagrid('selectRow', index);
    $('#cdg').datagrid('getRowDetail',index).find('form').form('reset');
}

function editContact(){
	console.log('edit contact was clicked');
    var row = $('#cdg').datagrid('getSelected');
    var index = $('#cdg').datagrid('getRowIndex', row);
    $('#cdg').datagrid('expandRow', index);
    $('#cdg').datagrid('selectRow', index);
}

function cancelContact(index){
	console.log('cancel contact was clicked');
    var row = $('#cdg').datagrid('getRows')[index];
    if (row.isNewRecord){
        $('#cdg').datagrid('deleteRow',index);
    } else {
        $('#cdg').datagrid('collapseRow',index);
    }
}

function deleteContact(){
	console.log('delete contact was clicked');
    var row = $('#cdg').datagrid('getSelected');
    var index = $('#cdg').datagrid('getRowIndex', row);
    if (row){
    	$.messager.confirm('<?php echo TEXT_CONFORM?>','<?php echo sprintf(TEXT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ARGS, TEXT_CONTACT)?>',function(r){
            if (r){
                $.post('index.php?action=DeleteContact',{contact_id:row.contactid,dataType: 'json', async: false, contentType: 'application/json'},function(result){
                    if (result.success){
                        $('#cdg').datagrid('deleteRow', index);
                    } else {
                        $.messager.show({    // show error message
                        	title: '<?php echo TEXT_ERROR?>',
                            msg: result.error_message
                        });
                    }
                },'json')  
	          	.fail(function(xhr, status, error) {
		          		console.error('we received a error from the server returned = '+ JSON.stringify(xhr));
					$.messager.alert('<?php echo TEXT_ERROR?>',error);
			    });
            }
        });
    }
}
</script>