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
	<table id="cdg" title="<?php echo sprintf(TEXT_MANAGER_ARGS, $contact);?>" class="easyui-datagrid" style="height:500px;padding:50px;">
    	<thead>
       		<tr>
       			<th data-options="field:'contact_last',sortable:true"><?php echo TEXT_LAST_NAME;?></th>
          		<th data-options="field:'contact_first',sortable:true"><?php echo TEXT_FIRST_NAME?></th>
	           	<th data-options="field:'contact_middle',sortable:true"><?php echo TEXT_TITLE?></th>
	           	<th data-options="field:'telephone1',sortable:true"><?php echo TEXT_TELEPHONE?></th>
	           	<th data-options="field:'telephone4',sortable:true"><?php echo TEXT_MOBILE_PHONE?></th>
	           	<th data-options="field:'email',sortable:true"><?php echo TEXT_EMAIL?></th>
	        </tr>
	     </thead>
	</table>
	<div id="ContactsToolbar">
    	<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editContacts()"><?php echo sprintf(TEXT_EDIT_ARGS, $contact);?></a>
	    <a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newContacts()"><?php echo sprintf(TEXT_NEW_ARGS, $contact);?></a>
    	<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteContacts()"><?php echo sprintf(TEXT_DELETE_ARGS, $contact);?></a>
    	<span style="margin-left: 100px;"><?php echo  TEXT_SHOW_INACTIVE . ' :'?></span>
    	<?php echo html_checkbox_field('contact_show_inactive', '1', false,'', 'onchange="contactdoSearch()"' );?>
    	<div style="float: right;">
      		<span><?php echo TEXT_SEARCH?> : </span>
    		<input class="easyui-searchbox" data-options="prompt:'<?php TEXT_PLEASE_INPUT_VALUE; ?>',searcher:contactdoSearch" id="Contacts_search_text" >
    	</div>
    </div>
<script type="text/javascript">
	    	function contactdoSearch(value){
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
	        	$.messager.progress();
	            $('#contactsWindow').window('open').window('center').window('setTitle','<?php echo sprintf(TEXT_NEW_ARGS, $contact);?>');
	            $('#contactsWindow').window('refresh', "index.php?action=newContact");
	            $('#contactsWindow').window('resize');
	        }
	        
	        function editContact(){
		        $('#win').window('open').window('center').window('setTitle','<?php echo sprintf(TEXT_EDIT_ARGS, $contact);?>');
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
				sortName:	"short_name",
				sortOrder: 	"asc",
				loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				toolbar: 	"#ContactsToolbar",
				rowStyler: function(index,row){
					if (row.inactive == '1')return 'background-color:pink;';
				},
			});
			$('#contactsWindow').window({
	        	href:		"index.php?action=editContact",
				closed: true,
				title:	"<?php echo sprintf(TEXT_EDIT_ARGS, $contact);?>",
				fit:	true,
				queryParams: {
					type: '<?php echo $basis->cInfo->type;?>',
					dataType: 'html',
	                contentType: 'text/html',
	                async: false
				},
				onLoadError: function(){
					console.log('the loading of the window resulted in a error');
					$.messager.alert('<?php echo TEXT_ERROR?>');
					$.messager.progress('close');
				},
				onOpen: function(){
					$.messager.progress('close');
				},
				onBeforeLoad: function(param){
					var row = $('#cdg').datagrid('getSelected');
					param.contactid = row.contactid;
				},
			});
			$('#contactDetails').form({
			    url: "index.php?action=saveContact",
			    onSubmit: function(param){
					var isValid = $(this).form('validate');
					if (!isValid){
						console.log('the form field are not validated');
						$.messager.progress('close');	// hide progress bar while the form is invalid
					}
					console.log('the form field are validated');
					return isValid;	// return false will stop the form submission
				},  
				success: function(data){
					console.log('successfully saved contact info. close window and refresh datagrid.');
					console.log('data received = '+ data);
					$.messager.progress('close');	// hide progress bar while submit successfully
					$('#contactsWindow').window('close');
					$('#cdg').datagrid('reload'); 
				},
				onLoadSuccess: function(data){
					console.log('successfully loaded the form.');
				}, 
				onLoadError: function(){
					console.log('there was a error during loading of the form.');
				}, 
//				
			});

			
</script>
</div>
