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
//  Path: /modules/contacts/pages/main/template_addbook.php
//
?>
<div title="<?php echo TEXT_ADDRESS_BOOK;?>" >
<?php // *********************** SHIPPING ADDRESSES  *************************************
  if (defined('MODULE_SHIPPING_STATUS')) { // show shipping address for customers and vendors ?>
    <table id='shipping_address' title="<?php echo sprintf( TEXT_SHIPPING_ARGS, TEXT_ADDRESS); ?>">
		<thead>
			<tr>
	        	<th data-options="field:'primary_name',sortable:true, align:'left'"><?php echo TEXT_NAME_OR_COMPANY;?></th>
	   	        <th data-options="field:'contact',sortable:true, align:'left'"><?php echo TEXT_ATTENTION?></th>	
	   	        <th data-options="field:'address1',sortable:true, align:'left'"><?php echo TEXT_ADDRESS1?></th>	
	   	        <th data-options="field:'city_town',sortable:true, align:'left'"><?php echo TEXT_CITY_TOWN?></th>	
	   	        <th data-options="field:'state_province',sortable:true, align:'left'"><?php echo TEXT_STATE_PROVINCE?></th>	
	   	        <th data-options="field:'postal_code',sortable:true, align:'left'"><?php echo TEXT_POSTAL_CODE?></th>
	   	        <th data-options="field:'country_code',sortable:true, align:'left'"><?php echo TEXT_COUNTRY?></th>			        
	    	</tr>
	   	</thead>
	</table>
	<div id="shipping_toolbar">
	    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newShipping()"><?php echo sprintf(TEXT_NEW_ARGS, sprintf( TEXT_SHIPPING_ARGS, TEXT_ADDRESS)); ?></a>
	    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editShipping()"><?php echo sprintf(TEXT_EDIT_ARGS, sprintf( TEXT_SHIPPING_ARGS, TEXT_ADDRESS));?></a>
		<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteShipping()"><?php echo sprintf(TEXT_DELETE_ARGS, sprintf( TEXT_SHIPPING_ARGS, TEXT_ADDRESS)); ?></a>
	</div>
  <?php }
  // *********************** BILLING/BRANCH ADDRESSES  ********************************* ?>
    
    <table id='billing_address' title="<?php echo sprintf(TEXT_BILLING_ARGS, TEXT_ADDRESS); ?>">
		<thead>
			<tr>
	        	<th data-options="field:'primary_name',sortable:true, align:'left'"><?php echo TEXT_NAME_OR_COMPANY;?></th>
	   	        <th data-options="field:'contact',sortable:true, align:'left'"><?php echo TEXT_ATTENTION?></th>	
	   	        <th data-options="field:'address1',sortable:true, align:'left'"><?php echo TEXT_ADDRESS1?></th>	
	   	        <th data-options="field:'city_town',sortable:true, align:'left'"><?php echo TEXT_CITY_TOWN?></th>	
	   	        <th data-options="field:'state_province',sortable:true, align:'left'"><?php echo TEXT_STATE_PROVINCE?></th>	
	   	        <th data-options="field:'postal_code',sortable:true, align:'left'"><?php echo TEXT_POSTAL_CODE?></th>
	   	        <th data-options="field:'country_code',sortable:true, align:'left'"><?php echo TEXT_COUNTRY?></th>			        
	    	</tr>
	   	</thead>
	</table>
	<div id="billing_toolbar">
	    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newBilling()"><?php echo sprintf(TEXT_NEW_ARGS, sprintf(TEXT_BILLING_ARGS, TEXT_ADDRESS)); ?></a>
	    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editBilling()"><?php echo sprintf(TEXT_EDIT_ARGS, sprintf( TEXT_BILLING_ARGS, TEXT_ADDRESS));?></a>
		<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteBilling()"><?php echo sprintf(TEXT_DELETE_ARGS, sprintf(TEXT_BILLING_ARGS, TEXT_ADDRESS)); ?></a>
	</div>
</div>

<script type="text/javascript">
$('#shipping_address').datagrid({
	url:		"index.php?action=loadAddresses",
	queryParams: {
		contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		address_type: 's',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '45%',
	height: '500px',
	style:{
		float:'right',
		margin:'30px',
	},
	onBeforeLoad:function(){
		console.log('loading of the shipping address datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the shipping address datagrid was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the shipping address datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table shipping address');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the shipping address was double clicked');
		$('#shipping_address').datagrid('expandRow', index);
	},
	onCollapseRow: function(index , row){
		if (row.isNewRecord){
	        $('#shipping_address').datagrid('deleteRow',index);
	    }
	},
	toolbar: '#shipping_toolbar',
	remoteSort:	false,
	fitColumns:	true,
	idField:	"address_id",
	singleSelect:true,
	sortName:	"primary_name",
	sortOrder: 	"asc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
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
		        table: 'shipping_address',
		        contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		        type: '<?php echo $basis->cInfo->contact->type;?>s',
			},
            cache:false,
            href:'index.php?action=editAddress',
            loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
            onLoad:function(){
                $('#shipping_address').datagrid('fixDetailRowHeight',index);
                $('#shipping_address').datagrid('selectRow',index);
                $('#shipping_address').datagrid('getRowDetail',index).find('form').form('load',row);
            },
            onBeforeLoad:function(param){
        		console.log('loading the shipping_address form');
        	},
        	onLoadSuccess:function(data){
        		console.log('the loading the shipping_address form was succesfull');
        		$.messager.progress('close');
        		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
        	},
            onLoadError: function(){
        		console.error('the loading of the shipping_address form resulted in a error');
        		$.messager.progress('close');
        		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for shipping_address form');
        	},
        });
    }
});

$('#billing_address').datagrid({
	url:		"index.php?action=loadAddresses",
	queryParams: {
		contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		address_type: 'b',
		dataType: 'json',
        contentType: 'application/json',
        async: false,
	},
	width: '45%',
	height: '500px',
	style:{
		margin:'30px',
	},
	onBeforeLoad:function(){
		console.log('loading of the billing address datagrid');
	},
	onLoadSuccess: function(data){
		console.log('the loading of the billing address datagrid was succesfull');
		$.messager.progress('close');
		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
	},
	onLoadError: function(){
		console.error('the loading of the billing address datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table billing address');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the billing address was double clicked');
		$('#billing_address').datagrid('expandRow', index);
	},
	onCollapseRow: function(index , row){
		if (row.isNewRecord){
	        $('#billing_address').datagrid('deleteRow',index);
	    }
	},
	toolbar: '#billing_toolbar',
	remoteSort:	false,
	fitColumns:	true,
	idField:	"address_id",
	singleSelect:true,
	sortName:	"primary_name",
	sortOrder: 	"asc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	view: detailview,
	detailFormatter: function(index,row){
        return '<div class="ddv"></div>';
    },
    onExpandRow: function(index, row){
        var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
        ddv.panel({
        	border: false,
        	queryParams: {
            	contentType:'inlineForm',
		        async: false,
		        table: 'billing_address',
		        contact_id: '<?php echo $basis->cInfo->contact->id;?>',
		        type: '<?php echo $basis->cInfo->contact->type;?>b',
			},
            cache: false,
            href:'index.php?action=editAddress',
            loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
            onLoad: function(){
                $('#billing_address').datagrid('fixDetailRowHeight',index);
                $('#billing_address').datagrid('selectRow',index);
                $('#billing_address').datagrid('getRowDetail',index).find('form').form('load',row);
            },
            onBeforeLoad: function(param){
        		console.log('loading the billing_address form');
        	},
        	onLoadSuccess: function(data){
        		console.log('the loading the billing_address form was succesfull');
        		$.messager.progress('close');
        		if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
        	},
            onLoadError: function(){
        		console.error('the loading of the billing_address form resulted in a error');
        		$.messager.progress('close');
        		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for billing_address form');
        	},
        });
    }
});

function newShipping(){
	console.log('new shipping address was clicked');
    $('#shipping_address').datagrid('appendRow',{isNewRecord:true});
    var index = $('#shipping_address').datagrid('getRows').length - 1;
    $('#shipping_address').datagrid('selectRow', index);
    $('#shipping_address').datagrid('expandRow', index);
    $('#shipping_address').datagrid('getRowDetail',index).find('form').form('reset');
}

function editShipping(){
	console.log('edit shipping was clicked');
    var row = $('#shipping_address').datagrid('getSelected');
    var index = $('#shipping_address').datagrid('getRowIndex', row);
    $('#shipping_address').datagrid('expandRow', index);
    $('#shipping_address').datagrid('selectRow', index);
}

function cancelShipping (index){
	console.log('cancel shipping address was clicked');
    var row = $('#shipping_address').datagrid('getRows')[index];
    if (row.isNewRecord){
		$('#shipping_address').datagrid('deleteRow',index);
    } else {
        $('#shipping_address').datagrid('collapseRow',index);
    }
}

function deleteShipping (){
	console.log('delete shipping address was clicked');
    var row = $('#shipping_address').datagrid('getSelected');
    var index = $('#shipping_address').datagrid('getRowIndex', row);
    if (row){
        $.messager.confirm('<?php echo TEXT_CONFORM?>','<?php echo sprintf(TEXT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ARGS, TEXT_SHIPPING_ADDRESS)?>',function(r){
            if (r){
            	$.post('index.php?action=deleteAddress',{address_id:row.address_id, dataType: 'json', async: false, contentType: 'application/json'},function(result){
                    if (result.success){
                    	$('#shipping_address').datagrid('deleteRow', index);
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

function newBilling(){
	console.log('new billing address was clicked');
    $('#billing_address').datagrid('appendRow',{isNewRecord:true});
    var index = $('#billing_address').datagrid('getRows').length - 1;
    $('#billing_address').datagrid('selectRow', index);
    $('#billing_address').datagrid('expandRow', index);
    $('#billing_address').datagrid('getRowDetail',index).find('form').form('reset');
}

function editBilling(){
	console.log('edit billing was clicked');
    var row = $('#billing_address').datagrid('getSelected');
    var index = $('#billing_address').datagrid('getRowIndex', row);
    $('#billing_address').datagrid('expandRow', index);
    $('#billing_address').datagrid('selectRow', index);
}

function cancelBilling (index){
	console.log('cancel billing address was clicked');
    var row = $('#billing_address').datagrid('getRows')[index];
    if (row.isNewRecord){
		$('#billing_address').datagrid('deleteRow',index);
    } else {
        $('#billing_address').datagrid('collapseRow',index);
    }
}

function deleteBilling (){
	console.log('delete billing address was clicked');
    var row = $('#billing_address').datagrid('getSelected');
    var index = $('#billing_address').datagrid('getRowIndex', row);
    if (row){
        $.messager.confirm('<?php echo TEXT_CONFORM?>','<?php echo sprintf(TEXT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ARGS, TEXT_BILLLING_ADDRESS)?>',function(r){
            if (r){
            	$.post('index.php?action=deleteAddress',{address_id:row.address_id, dataType: 'json', async: false, contentType: 'application/json'},function(result){
                    if (result.success){
                    	$('#billing_address').datagrid('deleteRow', index);
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