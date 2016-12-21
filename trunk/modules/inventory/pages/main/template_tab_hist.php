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
	  <?php echo TEXT_CREATION_DATE .' '. 	\core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->inventory->creation_date). ' '.
	  			 TEXT_LAST_UPDATE .' '. 	\core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->inventory->last_update). ' '.  
	  			 TEXT_LAST_ENTRY_DATE .' '. \core\classes\DateTime::createFromFormat(DATE_FORMAT, $basis->cInfo->inventory->last_journal_date)?> <br>
  </fieldset>
  <fieldset>
	<legend><?php echo TEXT_SKU_ACTIVITY; ?></legend>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
	  		<tr><td valign="top" width="50%">
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
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="4"><?php echo TEXT_AVERAGE_USAGE_EXCLUDING_THIS_MONTH; ?></th></tr>
		  <tr>
		    <th width="25%"><?php echo TEXT_LAST_MONTH; ?></th>
		    <th width="25%"><?php echo TEXT_3_MONTHS; ?></th>
		    <th width="25%"><?php echo TEXT_6_MONTHS; ?></th>
		    <th width="25%"><?php echo TEXT_12_MONTHS; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <tr>
		    <td align="center" width="25%"><?php echo $basis->cInfo->inventory->history['averages']['1month']; ?></td>
		    <td align="center" width="25%"><?php echo $basis->cInfo->inventory->history['averages']['3month']; ?></td>
		    <td align="center" width="25%"><?php echo $basis->cInfo->inventory->history['averages']['6month']; ?></td>
		    <td align="center" width="25%"><?php echo $basis->cInfo->inventory->history['averages']['12month']; ?></td>
		  </tr>
		</tbody>
		</table>
	  </td>
	  <td valign="top" width="25%">
	  	<?php if(isset($basis->cInfo->inventory->purchases_history)){?>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="4"><?php echo TEXT_PURCHASES_BY_MONTH; ?></th></tr>
		  <tr>
		    <th><?php echo TEXT_YEAR; ?></th>
		    <th><?php echo TEXT_THIS_MONTH; ?></th>
		    <th><?php echo TEXT_QUANTITY; ?></th>
		    <th><?php echo TEXT_PURCHASE_COST; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <?php
		if ($basis->cInfo->inventory->purchases_history) {
		  $odd = true;
		  foreach ($basis->cInfo->inventory->purchases_history as $value) {
		    echo '<tr class="' . ($odd?'odd':'even') . '">' . chr(10);
		    echo '  <td align="center">' . $value['ThisYear']. '</td>' . chr(10);
			echo '  <td align="center">' . $value['MonthName']. '</td>' . chr(10);
		    echo '  <td align="center">' . ($value['qty'] ? $value['qty'] : '&nbsp;') . '</td>' . chr(10);
		    echo '  <td align="right">' . ($value['total_amount'] ? $admin->currencies->format($value['total_amount']) : '&nbsp;') . '</td>' . chr(10);
			echo '</tr>' . chr(10);
			$odd = !$odd;
		  }
		} else {
		  echo '<tr><td align="center" colspan="4">' . TEXT_NO_RESULTS_FOUND . '</td></tr>' . chr(10);
		}
	  ?>
		</tbody>
		</table>
		<?php }?>
	  </td>
	  <td valign="top" width="25%">
	  	<?php if(isset($basis->cInfo->inventory->sales_history)){?>
		<table class="ui-widget" style="border-collapse:collapse;width:100%">
		 <thead class="ui-widget-header">
		  <tr><th colspan="4"><?php echo TEXT_SALES_BY_MONTH; ?></th></tr>
		  <tr>
		    <th><?php echo TEXT_YEAR; ?></th>
		    <th><?php echo TEXT_THIS_MONTH; ?></th>
		    <th><?php echo TEXT_QUANTITY; ?></th>
		    <th><?php echo TEXT_SALES_INCOME; ?></th>
		  </tr>
		 </thead>
		 <tbody class="ui-widget-content">
		  <?php
		if ($basis->cInfo->inventory->sales_history) {
		  $odd = true;
		  foreach ($basis->cInfo->inventory->sales_history as $value) {
		    echo '<tr class="' . ($odd?'odd':'even') . '">' . chr(10);
			echo '  <td align="center">' . $value['ThisYear']. '</td>' . chr(10);
			echo '  <td align="center">' . $value['MonthName']. '</td>' . chr(10);
		    echo '  <td align="center">' . ($value['qty'] ? $value['qty'] : '&nbsp;') . '</td>' . chr(10);
		    echo '  <td align="right">' . ($value['total_amount'] ? $admin->currencies->format($value['total_amount']) : '&nbsp;') . '</td>' . chr(10);
			echo '</tr>' . chr(10);
			$odd = !$odd;
		  }
		} else {
		  echo '<tr><td align="center" colspan="4">' . TEXT_NO_RESULTS_FOUND . '</td></tr>' . chr(10);
		}
	  ?>
		</tbody>
		</table>
		<?php }?>
	  </td>
	  </tr>
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
	width: '500px',
	style:{
		float:'left',
		margin:'50px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory open purchase orders datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory open purchase orders was succesfull');
		$.messager.progress('close');
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
	width: '500px',
	style:{
		float:'left',
		margin:'50px',
	},
	onBeforeLoad:function(){
		console.log('loading of the inventory open sales orders datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the inventory open sales orders was succesfull');
		$.messager.progress('close');
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
</script>