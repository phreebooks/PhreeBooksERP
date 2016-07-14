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
       			<th data-options="field:'contact_middle',sortable:true"><?php echo TEXT_TITLE?></th>
	           	<th data-options="field:'telephone1',sortable:true"><?php echo TEXT_TELEPHONE?></th>
	           	<th data-options="field:'telephone4',sortable:true"><?php echo TEXT_MOBILE_PHONE?></th>
	           	<th data-options="field:'email',sortable:true"><?php echo TEXT_EMAIL?></th>
	        </tr>
	     </thead>
	</table>
	<div id="ContactsToolbar" >
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
    	return 	'<table>'+
    				'<tr>'+
    					'<td align="right"><?php echo TEXT_INACTIVE;?></td>'+ 
    					'<td><input type="checkbox" name="inactive" value="'+row.inactive+'"></input></td>'+
    				'</tr>'+
		      		'<tr>'+
		       			'<td align="right"><?php echo TEXT_CONTACT_ID; ?><input type="hidden" name="id" value="'+row.contactid+'"></input></td>'+
		       			'<td><input type="text" name="short_name" size="21" maxlength="20" class="easyui-textbox" required="required" value="'+row.short_name+'"></input></td>'+
		       			'<td align="right"><?php echo TEXT_TITLE; ?></td>'+
		       			'<td><input type="text" name="contact_middle" size="33" maxlength="32" class="easyui-textbox" value="'+row.contact_middle+'"> </input></td>'+
		      		'</tr>'+
		      		'<tr>'+
		        		'<td align="right"><?php echo TEXT_FIRST_NAME; ?></td>'+
		        		'<td><input type="text" name="contact_first" size="33" maxlength="32" class="easyui-textbox" value="'+row.contact_first+'"></input></td>'+
		        		'<td align="right"><?php echo TEXT_LAST_NAME; ?></td>'+
		        		'<td><input type="text" name="contact_last" size="33" maxlength="32" class="easyui-textbox" value="'+row.contact_last+'"></input></td>'+
		      		'</tr>'+
		      		'<tr>'+
		        		'<td align="right"><?php echo TEXT_FACEBOOK_ID; ?></td>'+
		        		'<td><input type="text" name="account_number" size="17" maxlength="16" class="easyui-textbox" value="'+row.account_number+'"></input></td>'+
		        		'<td align="right"><?php echo TEXT_TWITTER_ID; ?></td>'+
		        		'<td><input type="text" name="gov_id_number" size="17" maxlength="16" class="easyui-textbox" value="'+row.gov_id_number+'"></input></td>'+
		      		'</tr>'+
					'<tr>'+
  						'<td align="right"><input type="hidden" name="address_id" value="'+row.address_id+'"></input> <?php echo TEXT_ATTENTION; ?></td>'+
  						'<td><input type="text" name="contact" size="33" maxlength="32" class="easyui-textbox"  value="'+row.contact+'"></input></td>'+
  						'<td align="right"><?php echo TEXT_ALTERNATIVE_TELEPHONE_SHORT ?></td>'+
  						'<td><input type="text" name="telephone2" size="22" maxlength="21" class="easyui-textbox" value="'+row.telephone2+'"></input></td>'+
  					'</tr>'+
  					'<tr>'+
  						'<td align="right"><?php echo TEXT_ADDRESS1 ?></td>'+
  						'<td><input type="text" name="adress1" size="33" maxlength="32" class="easyui-textbox" <?php if(ADDRESS_BOOK_ADDRESS1_REQUIRED == true) echo'required="required"'?> value="'+row.address1+'"></input></td>'+
  						'<td align="right"><?php echo TEXT_FAX ?></td>'+
  						'<td><input type="text" name="telephone3" size="22" maxlength="21" class="easyui-textbox" value="'+row.telephone3+'"</input></td>'+
  					'</tr>'+
  					'<tr>'+
  						'<td align="right"><?php echo TEXT_ADDRESS2 ?></td>'+
  						'<td><input type="text" name="adress2" size="33" maxlength="32" class="easyui-textbox" <?php if(ADDRESS_BOOK_ADDRESS2_REQUIRED == true) echo'required="required"'?> value="'+row.address2+'"></input></td>'+
  						'<td align="right"><?php echo TEXT_MOBILE_PHONE ?></td>'+
  						'<td><input type="text" name="telephone4" size="22" maxlength="21" class="easyui-textbox"  value="'+row.telephone4+'"></input></td>'+
  					'</tr>'+
  					'<tr>'+
  						'<td align="right"><?php echo TEXT_CITY_TOWN ?></td>'+
  						'<td><input type="text" name="city_town" size="33" maxlength="32" class="easyui-textbox" <?php if(ADDRESS_BOOK_CITY_TOWN_REQUIRED == true) echo'required="required"'?> value="'+row.city_town+'"></input></td>'+
  						'<td align="right"><?php echo TEXT_EMAIL_ADDRESS ?></td>'+
  						'<td><input type="text" name="email" size="51" maxlength="50" class="easyui-textbox" data-options="validType:\'email\'" value="'+row.email+'"></input></td>'+
			  		'</tr>'+
			  		'<tr>'+
  						'<td align="right"><?php echo TEXT_STATE_PROVINCE ?></td>'+
  						'<td><input type="text" name="state_province" size="25" maxlength="24" class="easyui-textbox" <?php if(ADDRESS_BOOK_STATE_PROVINCE_REQUIRED == true) echo'required="required"'?> value="'+row.state_province+'"></input></td>'+
  						'<td align="right"><?php echo TEXT_WEBSITE ?></td>'+
  						'<td><input type="text" name="website" size="51" maxlength="50" class="easyui-textbox" data-options="validType:\'url\'" value="'+row.website+'"></input></td>'+
			  		'</tr>'+
			  		'<tr>'+
  						'<td align="right"><?php echo TEXT_POSTAL_CODE ?></td>'+
  						'<td><input type="text" name="postal_code" size="11" maxlength="10" class="easyui-textbox" <?php if(ADDRESS_BOOK_POSTAL_CODE_REQUIRED == true) echo'required="required"'?> value="'+row.postal_code+'"></input></td>'+
  						'<td align="right"><?php echo TEXT_COUNTRY ?></td>'+
  						'<td><select id="country_code" value="'+row.country_code+'" class="easyui-combobox" ><?php  foreach(gen_get_countries() as  $key => $choice) echo "<option value\"{$choice['id']}\">{$choice['text']}</option>"; ?> </select></td>'+
//  						"<td><?php echo html_pull_down_menu("country_code", gen_get_countries(), COMPANY_COUNTRY);?></td>"+
  					'</tr>'+
	    		'</table>';
//    			$('#country_code').combogrid('setValue', row.country_code);
    },
});

function contactdoSearch(value){
	console.log('A contact search was requested.');
	$.messager.progress();
	$('#cdg').datagrid('load',{
		dept_rep_id: $('#id').val(),
		search_text: $('#Contacts_search_text').val(),
		dataType: 'json',
		async: false,
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

</script>