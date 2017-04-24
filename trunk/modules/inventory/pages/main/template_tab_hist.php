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
//  Path: /modules/inventory/pages/main/template_tab_hist.php
//
// start the history tab html
?>
<div title="<?php echo TEXT_HISTORY;?>" id="tab_history">
	<fieldset>
    	<legend><?php echo TEXT_SKU_HISTORY; ?></legend>
	  	<?php 
	  		$creation_date 		= new 	\core\classes\DateTime($basis->cInfo->inventory->creation_date);
	  		$last_update 		= new 	\core\classes\DateTime($basis->cInfo->inventory->last_update);
	  		$last_journal_date 	= new 	\core\classes\DateTime($basis->cInfo->inventory->last_journal_date);
	  
	  		echo TEXT_CREATION_DATE 	.' : <output>'.	$creation_date->format(DATE_TIME_FORMAT) 		. "</output>   ". 
	  			 TEXT_LAST_UPDATE 		.' : <output>'. $last_update->format(DATE_TIME_FORMAT)   		. "</output>   ".
	  			 TEXT_LAST_ENTRY_DATE 	.' : <output>'.	$last_journal_date->format(DATE_TIME_FORMAT)	. "</output>";
	  		?> <br>
  	</fieldset>
	<fieldset>
		<legend><?php echo TEXT_SKU_ACTIVITY; ?></legend>
		<?php if(in_array('purchase',$basis->cInfo->inventory->posible_transactions)){?>
			<table id='open_purchase_orders' title="<?php echo TEXT_OPEN_PURCHASE_ORDERS; ?>">
			   	<thead>
			   		<tr>
			        	<th data-options="field:'purchase_invoice_id',sortable:true, align:'center'"><?php echo TEXT_PO_NUMBER;?></th>
			   	        <th data-options="field:'post_date',sortable:true, align:'center', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_DATE?></th>
			       	    <th data-options="field:'qty',sortable:true, align:'center', formatter: function(value,row,index){ return formatQty(value)}"><?php echo TEXT_QUANTITY?></th>
			            <th data-options="field:'date_1',sortable:true, align:'right', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_RECEIVE_DATE?></th>
			   	    </tr>
			   	</thead>
			</table>
		<?php }?>
		<?php if(in_array('sell',$basis->cInfo->inventory->posible_transactions)){?>
			<table id='sales_by_month' title="<?php echo TEXT_SALES_BY_MONTH; ?>">
			   	<thead>
			   		<tr>
			        	<th data-options="field:'year',sortable:true, align:'center'"><?php echo TEXT_YEAR;?></th>
		    	        <th data-options="field:'month',sortable:true, align:'center'"><?php echo TEXT_THIS_MONTH?></th>
		        	    <th data-options="field:'qty',sortable:true, align:'center', formatter: function(value,row,index){ return formatQty(value)}"><?php echo TEXT_QUANTITY?></th>
			            <th data-options="field:'post_date',sortable:true, align:'right', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_PURCHASE_COST?></th>
			   	    </tr>
			   	</thead>
			</table>
		<?php }
		if(in_array('purchase',$basis->cInfo->inventory->posible_transactions)){?>
			<table id='purchases_by_month' title="<?php echo TEXT_PURCHASES_BY_MONTH; ?>">
		    	<thead>
		    		<tr>
			        	<th data-options="field:'year',sortable:true, align:'center'"><?php echo TEXT_YEAR;?></th>
		    	        <th data-options="field:'month',sortable:true, align:'center'"><?php echo TEXT_THIS_MONTH?></th>
		        	    <th data-options="field:'qty',sortable:true, align:'center', formatter: function(value,row,index){ return formatQty(value)}"><?php echo TEXT_QUANTITY?></th>
			            <th data-options="field:'post_date',sortable:true, align:'right', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_PURCHASE_COST?></th>
		    	    </tr>
		    	</thead>
		    </table>
		<?php }
		if(in_array('sell',$basis->cInfo->inventory->posible_transactions)){?>
			<table id='open_sales_orders' title="<?php echo TEXT_OPEN_SALES_ORDERS; ?>">
			   	<thead>
			   		<tr>
			        	<th data-options="field:'purchase_invoice_id',sortable:true, align:'center'"><?php echo TEXT_SO_NUMBER;?></th>
			   	        <th data-options="field:'post_date',sortable:true, align:'center', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_DATE?></th>
			       	    <th data-options="field:'qty',sortable:true, align:'center', formatter: function(value,row,index){ return formatQty(value)}"><?php echo TEXT_QUANTITY?></th>
			            <th data-options="field:'date_1',sortable:true, align:'right', formatter: function(value,row,index){ return formatDate(new Date(value))}"><?php echo TEXT_REQUIRED_DATE?></th>
			   	    </tr>
			   	</thead>
			</table>
		<?php }?>
		<table id='average_use' title="<?php echo TEXT_AVERAGE_USAGE_EXCLUDING_THIS_MONTH; ?>">
			<thead>
			  	<tr>
			       	<th data-options="field:'period',sortable:true, align:'center'"><?php echo TEXT_PERIOD;?></th>
			      	<th data-options="field:'average',sortable:true, align:'center'"><?php echo TEXT_AVERAGE?></th>
			    </tr>
			</thead>
		</table>
	</fieldset>
</div>
<script type="text/javascript">
$('#open_purchase_orders').datagrid({
	url:		"index.php?action=loadOpenOrders",
	queryParams: {
		sku: '<?php echo $basis->cInfo->inventory->sku;?>',
		journal_id: '4',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '400px',
	style:{
		float: 'left',
		margin:'10px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory open purchase orders datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory open purchase orders was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the inventory open purchase orders resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table open purchase orders');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the inventory open purchase orders was double clicked');
		//@todo open order
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"id",
	singleSelect:true,
	sortName:	"post_date",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	rowStyler: function(index,row){
		if (row.closed == '1') return 'background-color:pink;';
	},
});

$('#open_sales_orders').datagrid({
	url:		"index.php?action=loadOpenOrders",
	queryParams: {
		sku: '<?php echo $basis->cInfo->inventory->sku;?>',
		journal_id: '10',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '400px',
	style:{
		float: 'left',
		margin: '10px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory open sales orders datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory open sales orders was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the inventory open sales orders resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table open sales orders');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the inventory open sales orders was double clicked');
		//@todo open order
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"id",
	singleSelect:true,
	sortName:	"post_date",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	rowStyler: function(index,row){
		if (row.closed == '1') return 'background-color:pink;';
	},
});
<?php 
$oneYearBack = new \core\classes\DateTime();
$oneYearBack->modify('- 1 year');
?>
$('#purchases_by_month').datagrid({
	url:		"index.php?action=loadOrders",
	queryParams: {
		sku: '<?php echo $basis->cInfo->inventory->sku;?>',
		journal_id: '6,21',
		post_date_max : '<?php echo $oneYearBack->format('Y-m-d');  ?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '350px',
	style:{
		float: 'right',
		margin: '10px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory purchases by month datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory purchases by month was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the inventory purchases by month resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table purchases by month');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the inventory purchases by month was double clicked');
		//@todo open order
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"id",
	singleSelect:true,
    view:		groupview,
    groupField:	'period',
	sortName:	"period",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	rowStyler: function(index,row){
		if (row.closed == '1') return 'background-color:pink;';
	},
});

$('#sales_by_month').datagrid({
	url:		"index.php?action=loadOrders",
	queryParams: {
		sku: '<?php echo $basis->cInfo->inventory->sku;?>',
		journal_id: '6,21',
		post_date_max : '<?php echo $oneYearBack->format('Y-m-d');  ?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '350px',
	style:{
		float:'right',
		margin:'10px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory sales by month datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory sales by month was succesfull'+JSON.stringify(data));
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the inventory sales by month resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table sales by month');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the inventory sales by month was double clicked');
		//@todo open order
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"id",
	singleSelect:true,
    view:		groupview,
    groupField:	'period',
	sortName:	"period",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	rowStyler: function(index,row){
		if (row.closed == '1') return 'background-color:pink;';
	},
});

$('#average_use').datagrid({
	url:		"index.php?action=GetInventoryAvarages",
	queryParams: {
		sku: '<?php echo $basis->cInfo->inventory->sku;?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '400px',
	style:{
		float: 'left',
		margin:'10px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory average use datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory average use was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the inventory average use resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table average use');
	},
	remoteSort:	false,
	fitColumns:	true,
	idField:	"period",
	sortName:	"period",
	sortOrder: 	"dsc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
});


</script>