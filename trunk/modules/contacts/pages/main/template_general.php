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
	    	<table>
	      		<tr> 
	      			<td><?php echo \core\classes\htmlElement::textbox("short_name",	constant('ACT_' . strtoupper($basis->cInfo->contact->type) . '_SHORT_NAME'), 'size="21" maxlength="20"', $basis->cInfo->contact->short_name, $basis->cInfo->contact->auto_type == false);?></td>
			       	<td><?php 
			       	if (sizeof($basis->cInfo->contact->contacts_levels) > 0) { 
			       		echo \core\classes\htmlElement::combobox("contacts_level", 	TEXT_CONTACT_LEVEL,	$basis->cInfo->contact->contacts_levels , $basis->cInfo->contact->contacts_level );
			       	}
			       	?>
					</td>
					<td><?php echo \core\classes\htmlElement::combobox("dept_rep_id", 	constant('ACT_' . strtoupper($basis->cInfo->contact->type) . '_REP_ID'), $basis->cInfo->contact->sales_rep_array , $basis->cInfo->contact->dept_rep_id ? $basis->cInfo->contact->dept_rep_id : 'r' ); ?></td>
	      		</tr>
	      		<tr>
	      			<td><?php echo \core\classes\htmlElement::checkbox('inactive', TEXT_INACTIVE, '1', $basis->cInfo->contact->inactive );?></td>
	      		</tr>
			    <tr>
			    	<td><?php echo \core\classes\htmlElement::textbox("contact_first",	TEXT_FIRST_NAME,  	'size="33" maxlength="32"', $basis->cInfo->contact->contact_first);?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("contact_middle",	TEXT_MIDDLE_NAME,	'size="33" maxlength="32"', $basis->cInfo->contact->contact_middle);?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("contact_last",	TEXT_LAST_NAME, 	'size="33" maxlength="32"', $basis->cInfo->contact->contact_last);?></td>
			    </tr>
			    <tr>
			    	<td><?php echo \core\classes\htmlElement::combobox("gl_type_account",	constant('ACT_' . strtoupper($basis->cInfo->contact->type) . '_GL_ACCOUNT_TYPE'), gen_coa_pull_down(), $basis->cInfo->contact->gl_type_account == '' ? AR_DEF_GL_SALES_ACCT : $basis->cInfo->contact->gl_type_account );?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("account_number",		constant('ACT_' . strtoupper($basis->cInfo->contact->type) . '_ACCOUNT_NUMBER'), 'size="17" maxlength="16"', $basis->cInfo->contact->account_number);?></td>
			        <td><?php echo \core\classes\htmlElement::combobox("price_sheet",		TEXT_DEFAULT_PRICE_SHEET, 	get_price_sheet_data($basis->cInfo->contact->type), $basis->cInfo->contact->account_number);?></td>
			    </tr>
			     <tr>
			     	<td><?php echo \core\classes\htmlElement::textbox("gov_id_number",		constant('ACT_' . strtoupper($basis->cInfo->contact->type) . '_ID_NUMBER'), 'size="17" maxlength="16"', $basis->cInfo->contact->gov_id_number);?></td>
			    	<td><?php echo \core\classes\htmlElement::combobox("tax_id",	TEXT_DEFAULT_SALES_TAX, $basis->cInfo->tax_rates, $basis->cInfo->contact->tax_id );?></td>
			    	<td><?php echo \core\classes\htmlElement::textbox('terms_text', TEXT_PAYMENT_TERMS, 'readonly="readonly" size="20"', gen_terms_to_language($basis->cInfo->contact->special_terms, true, $basis->cInfo->contact->terms_type)) . '&nbsp;' . chr(10);
			    	  		  echo html_icon('apps/accessories-text-editor.png', TEXT_TERMS_DUE, 'small', 'style="cursor:pointer" onclick="TermsList()"'); ?>
				   	</td>
			    </tr>
	    	</table>
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
	</div>