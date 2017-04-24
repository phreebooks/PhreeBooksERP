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
//  Path: /modules/contacts/pages/main/template_general.php
//
echo \core\classes\htmlElement::hidden('terms', $basis->cInfo->contact->special_terms) . chr(10);
?>
	<div title="<?php echo TEXT_GENERAL;?>" style="padding:10px">
		<fieldset>
	    	<legend><?php echo TEXT_CONTACT_INFORMATION; ?></legend>
	    	<?php $basis->cInfo->contact->PageMainTabGeneral();?>
	  	</fieldset>
	
	<?php // *********************** Mailing/Main Address (only one allowed) ****************************** ?>
	  	<div id="address_panel" class="easyui-panel" title="<?php echo TEXT_MAIN_MAILING_ADDRESS; ?>" style= "width:75%"> 	</div>
	  	<script type="text/javascript">
			$('#address_panel').panel({
	            border: true,
	            queryParams: {
	            	contentType:'inlineForm',
			        async: false,
			        contact_id: '<?php echo $basis->cInfo->contact->id;?>',
			        type: '<?php echo $basis->cInfo->contact->type;?>m',
				},
	            cache: false,
	            href:'index.php?action=editAddress',
	            loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	            onLoad: function(){
	           
	            },
	            onBeforeLoad: function(param){
	        		console.log('loading the main_address form');
	        	},
	        	onLoadSuccess: function(data){
	        		console.log('the loading the main_address form was succesfull');
	        		$.messager.progress('close');
	        		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	        	},
	            onLoadError: function(){
	        		console.error('the loading of the main_address form resulted in a error');
	        		$.messager.progress('close');
	        		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for main_address form');
	        	},
	        });
		</script>

	<?php // *********************** Attachments  ************************************* ?>
	  	<div>
	   		<fieldset>
	   			<legend><?php echo TEXT_ATTACHMENTS; ?></legend>
	   			<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
	    			<thead class="ui-widget-header">
	     				<tr><th colspan="3"><?php echo TEXT_ATTACHMENTS; ?></th></tr>
	    			</thead>
	    			<tbody class="ui-widget-content">
	     				<tr><td colspan="3"><?php echo TEXT_SELECT_FILE_TO_ATTACH . ' ' . html_file_field('file_name'); ?></td></tr>
	     				<tr  class="ui-widget-header">
	      					<th><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small'); ?></th>
	      					<th><?php echo TEXT_FILE_NAME; ?></th>
	      					<th><?php echo TEXT_ACTION; ?></th>
	     				</tr>
	<?php
						if (sizeof($basis->cInfo->contact->attachments) > 0) {
						  	foreach ($basis->cInfo->contact->attachments as $key => $value) {
						    	echo '<tr>';
							    echo ' <td>' . \core\classes\htmlElement::checkbox('rm_attach_'.$key, '', '1', false) . '</td>' . chr(10);
							    echo " <td>{$value}</td>" . chr(10);
							    echo ' <td>' . html_button_field('dn_attach_'.$key, TEXT_DOWNLOAD, "onclick='submitSeq({$key}, \"ContactAttachmentDownload\", true)'") . '</td>';
							    echo '</tr>' . chr(10);
						  	}
						} else {
						  	echo '<tr><td colspan="3">' . TEXT_NO_DOCUMENTS_HAVE_BEEN_FOUND . '</td></tr>';
						} ?>
	    			</tbody>
	   			</table>
	   		</fieldset>
	  	</div>
	  	<div id='notes_div' style="width:50%"><?php echo html_textarea_field("notes", 60, 30, $basis->cInfo->contact->notes); ?></div>
	</div>