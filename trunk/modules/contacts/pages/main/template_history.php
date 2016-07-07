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
//  Path: /modules/contacts/pages/main/template_history.php
//
?>
<div title="<?php echo TEXT_HISTORY;?>">
<?php // ***********************  History Section  ****************************** ?>
	<fieldset>
    	<legend><?php echo TEXT_ACCOUNT_HISTORY; ?></legend>
    	<p><?php echo constant('ACT_'.strtoupper($basis->cInfo->contact->type).'_FIRST_DATE').' '.\core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->contact->first_date); ?></p>
    	<p width="50%"><?php echo constant('ACT_'.strtoupper($basis->cInfo->contact->type).'_LAST_DATE1').' '.\core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->contact->last_update); ?></p>
  	</fieldset>
  	<fieldset>
    	<legend><?php echo TEXT_ORDER_HISTORY; ?></legend>
    	<?php if($basis->cInfo->contact->order_jid != ''){?>
	    <table id='order_history' title="<?php echo TEXT_ORDER_HISTORY ." ". sprintf(TEXT_HISTORY_ARGS, LIMIT_HISTORY_RESULTS); ?>">
	    	<thead>
	    		<tr>
		        	<th data-options="field:'purchase_invoice_id',sortable:true, align:'center'"><?php echo TEXT_SO_NUMBER;?></th>
	    	        <th data-options="field:'purch_order_id',sortable:true, align:'center'"><?php echo TEXT_PO_NUMBER?></th>
	        	    <th data-options="field:'post_date',sortable:true, align:'center', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_DATE?></th>
		            <th data-options="field:'total_amount',sortable:true, align:'right', formatter: function(value,row,index){ return formatCurrency(value)}"><?php echo TEXT_AMOUNT?></th>
	    	    </tr>
	    	</thead>
	    </table>
	    <?php }
	    if($basis->cInfo->contact->journals != ''){ ?>
	    <table id='invoice_history' title="<?php echo TEXT_INVOICE_HISTORY ." ". sprintf(TEXT_HISTORY_ARGS, LIMIT_HISTORY_RESULTS); ?>">
	    	<thead>
	    		<tr>
		        	<th data-options="field:'purchase_invoice_id',sortable:true, align:'center'"><?php echo TEXT_INVOICE_NUMBER;?></th>
	    	        <th data-options="field:'purch_order_id',sortable:true, align:'center'"><?php echo TEXT_PO_NUMBER?></th>
	        	    <th data-options="field:'post_date',sortable:true, align:'center', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_DATE?></th>
	            	<th data-options="field:'closed_date',sortable:true, align:'center', formatter: function(value,row,index){ if ( value == '0000-00-00') {return ''}else{return formatDate(new Date(value))}}"><?php echo TEXT_PAID?></th>
		            <th data-options="field:'total_amount',sortable:true, align:'right', formatter: function(value,row,index){ return formatCurrency(value)}"><?php echo TEXT_AMOUNT?></th>
	    	    </tr>
	    	</thead>
	    </table> 
	    <?php }?>
	</fieldset>
<?php echo RECORD_NUM_REF_ONLY . $basis->cInfo->contact->id; ?>
</div>

<script type="text/javascript">
<?php if($basis->cInfo->contact->order_jid != ''){?>
$('#order_history').datagrid({
	url:		"index.php?action=loadOrders",
	queryParams: {
		contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		journal_id: '<?php echo $basis->cInfo->contact->order_jid?>',
		open_only: false,
		limit:'<?php echo LIMIT_HISTORY_RESULTS?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '500px',
	style:{
		float:'left',
		margin:'50px',
	},
	onBeforeLoad:function(){
		console.log('loading of the order history datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the order history datagrid was succesfull');
		$.messager.progress('close');
	},
	onLoadError: function(){
		console.error('the loading of the order history datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table order history');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the order history was double clicked');
		//@todo open order
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"purchase_invoice_id",
	singleSelect:true,
	sortName:	"post_date",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	rowStyler: function(index,row){
		if (row.closed == '1') return 'background-color:pink;';
	},
});
<?php }
if($basis->cInfo->contact->journals != ''){ ?>
$('#invoice_history').datagrid({
	url:'index.php?action=loadOrders',
	queryParams: {
		contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		journal_id: '<?php echo $basis->cInfo->contact->journals?>',
		open_only: false,
		limit:'<?php echo LIMIT_HISTORY_RESULTS?>',
		dataType: 'json',
		contentType: 'application/json',
		async: false,
	},
	width: '500px',
	style:{
		float:'left',
		margin:'50px',
	},
    onBeforeLoad:function(){
		console.log('loading of the invoice history datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the invoice history datagrid was succesfull');
		$.messager.progress('close');
	},
	onLoadError: function(){
		console.error('the loading of the invoice history datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table invoice history');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the invoice history was double clicked');
		//@todo open order
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"purchase_invoice_id",
	singleSelect:true,
	sortName:	"post_date",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	rowStyler: function(index,row){
		if (row.closed_date != '0000-00-00') return 'background-color:pink;';
	},
});
<?php }?>
</script>