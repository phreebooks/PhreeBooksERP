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
//  Path: /modules/contacts/pages/main/template_notes.php
//

?>

<div title="<?php echo TEXT_NOTES;?>">
  <fieldset>
	<div style="float:right;width:50%">
	  	<div id="dlg" class="easyui-dialog" style="width:600px;height:400px;padding:10px 20px" closed="true" buttons="#dlg-buttons">
	  	        <div class="ftitle"><?php echo TEXT_CRM; ?></div>
       			<form id="fm" method="post" novalidate>
            		<div class="fitem">
                		<label style="display:inline-block; width:80px;"><?php echo TEXT_SALES_REP; ?></label>
		                <?php echo html_pull_down_menu('crm_rep_id', $basis->cInfo->all_employees, $basis->cInfo->contact->crm_rep_id ? $basis->cInfo->contact->crm_rep_id : $_SESSION['user']->account_id); ?>
        		    </div>
            		<div class="fitem">
		                <label style="display:inline-block; width:80px;"><?php echo TEXT_DATE; ?></label>
        		    	<?php echo html_calendar_field('crm_date', \core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->contact->crm_date)); ?>
            		</div>
            		<div class="fitem">
		                <label style="display:inline-block; width:80px;"><?php echo TEXT_ACTION; ?></label>
                		<?php echo html_pull_down_menu('crm_action', $basis->cInfo->contact->crm_actions, $basis->cInfo->contact->crm_action); ?>
            		</div>
            		<div class="fitem">
		                <label style="display:inline-block; width:80px;"><?php echo TEXT_NOTE; ?></label>
                		<?php echo html_textarea_field('crm_note', 60, 1, $basis->cInfo->contact->crm_note, ''); ?>
            		</div>
        		</form>
		</div>
		<table id='notes' title="<?php echo TEXT_HISTORY; ?>">
		    <thead>
		   		<tr>
		        	<th data-options="field:'name',sortable:true, align:'center'"><?php echo TEXT_WITH;?></th>
	    	        <th data-options="field:'user_name',sortable:true, align:'center'"><?php echo TEXT_ENTERED_BY?></th>	
	    	        <th data-options="field:'log_date',sortable:true, align:'center', formatter: function(value,row,index){ return formatDateTime(value)}"><?php echo TEXT_DATE?></th>
			        <th data-options="field:'action',sortable:true, align:'right', formatter: function(value,row,index){ return ConvertCrmAction(value)}"><?php echo TEXT_ACTION?></th>
			        <th data-options="field:'notes',sortable:false, align:'right'"><?php echo TEXT_NOTE?></th>		        
		    	</tr>
		   	</thead>
		</table>
		<div id="notes_toolbar">
	        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newCRM()"><?php echo sprintf(TEXT_NEW_ARGS, TEXT_CRM); ?></a>
	        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit User</a>
	        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove User</a>
	    </div>
	</div>
    <div style="width:50%"><?php echo html_textarea_field("address[{$basis->cInfo->contact->type}m][notes]", 60, 30, $basis->cInfo->contact->address[$basis->cInfo->contact->type.'m']['notes']); ?></div>
  </fieldset>
</div>

<script type="text/javascript">
$('#notes').datagrid({
	url:		"index.php?action=loadCRMHistory",
	queryParams: {
		contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '100%',
	style:{
		float:'right',
		margin:'50px',
	},
	onBeforeLoad:function(){
		console.log('loading of the crm history datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the crm history datagrid was succesfull');
		$.messager.progress('close');
	},
	onLoadError: function(){
		console.error('the loading of the crm history datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table crm notes');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the crm history was double clicked');
		//@todo open order
	},
	toolbar: notes_toolbar,
	remoteSort:	false,
	fitColumns:	true,
	idField:	"log_id",
	singleSelect:true,
	sortName:	"log_date",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
});

function ConvertCrmAction (value){
    if( value == '0') 			return '<?php echo TEXT_NONE ?>';
    if( value == 'new') 		return '<?php echo sprintf(TEXT_NEW_ARGS, TEXT_CALL) ?>';
    if( value == 'ret') 		return '<?php echo TEXT_RETURNED_CALL ?>';
    if( value == 'flw') 		return '<?php echo TEXT_FOLLOW_UP_CALL ?>';
    if( value == 'inac') 		return '<?php echo TEXT_INACTIVE ?>';
    if( value == 'lead') 		return '<?php echo sprintf(TEXT_NEW_ARGS, TEXT_LEAD) ?>';
    if( value == 'mail_in') 	return '<?php echo TEXT_EMAIL_RECEIVED ?>';
    if( value == 'mail_out') 	return '<?php echo TEXT_EMAIL_SEND ?>';
}

function newCRM(){
	$('#dlg').dialog('open').dialog('center').dialog('setTitle','<?php echo sprintf(TEXT_NEW_ARGS, TEXT_CRM); ?>');
	$('#fm').form('clear');
	url = 'save_user.php';
}

</script>