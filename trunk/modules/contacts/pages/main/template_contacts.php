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
