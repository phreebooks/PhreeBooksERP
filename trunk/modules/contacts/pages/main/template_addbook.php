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
	},
	onLoadError: function(){
		console.error('the loading of the shipping address datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table shipping address');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the shipping address was double clicked');
		$('#shipping_address').datagrid('expandRow', index);
		//@todo edit address
	},
	toolbar: shipping_toolbar,
	remoteSort:	false,
	fitColumns:	true,
	idField:	"address_id",
	singleSelect:true,
	sortName:	"primary_name",
	sortOrder: 	"asc",
	loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
	view: detailview,
    detailFormatter:function(index,row){
    	if (row == null) var row = {};
    	return AddressDetailFormatter(index, row, 'Shipping');
    },
    onExpandRow: function(index,row){
    	$('#shipping_address').datagrid('selectRow', index);
    	//$('#country_code').combobox('setValue', row.country_code);
    },
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
	},
	onLoadError: function(){
		console.error('the loading of the billing address datagrid resulted in a error');
		$.messager.progress('close');
		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table billing address');
	},
	onDblClickRow: function(index , row){
		console.log('a row in the billing address was double clicked');
		$('#billing_address').datagrid('expandRow', index);
		//@todo edit address
	},
	toolbar: billing_toolbar,
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
            cache:true,
            href:'index.php?action=editAddress',
            loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
            onLoad:function(){
                $('#billing_address').datagrid('fixDetailRowHeight',index);
                $('#billing_address').datagrid('selectRow',index);
                $('#billing_address').datagrid('getRowDetail',index).find('form').form('load',row);
            },
            onBeforeLoad:function(param){
        		console.log('loading the billing_address form');
        	},
        	onLoadSuccess:function(data){
        		console.log('the loading the billing_address form was succesfull');
        		$.messager.progress('close');
        	},
            onLoadError: function(){
        		console.error('the loading of the billing_address form resulted in a error');
        		$.messager.progress('close');
        		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for billing_address form');
        	},
        });
    }
});

function AddressDetailFormatter(index, row, field){
	return  '<form id="'+field+'_'+index+'"><table id="'+field+'_'+index+'">'+
			'<tr>'+
				'  <td align="right"> <?php echo TEXT_NAME_OR_COMPANY ?> </td>'+
				'  <td><input name="primary_name" type="text" value="' +row.primary_name+'"  size="49" maxlength="48" ></td>'+
				'  <td align="right"> <?php echo TEXT_TELEPHONE ?> </td>'+
				'  <td><input name="telephone1" type="tel" value="' +row.telephone1+'"  size="21" maxlength="20"  <?php if (ADDRESS_BOOK_TELEPHONE1_REQUIRED) echo ' required="required" ' ?> ></td>'+
			'</tr>'+
			'<tr>'+
				'  <td align="right"> <?php echo TEXT_ATTENTION ?> </td>'+
		 		'  <td><input name="contact" type="text" value="' +row.contact+'"  size="33" maxlength="32"  <?php if (ADDRESS_BOOK_CONTACT_REQUIRED) echo ' required="required" ' ?> ></td>'+
		 		'  <td align="right"> <?php echo TEXT_TELEPHONE ?> </td>'+
		 		'  <td><input name="telephone2" type="tel" value="' +row.telephone2+'"  size="21" maxlength="20" ></td>'+
		 	'</tr>'+

		 	'<tr>'+
		 		'  <td align="right"> <?php echo TEXT_ADDRESS1 ?> </td>'+
		 		'  <td><input name="address1" type="text" value="' +row.address1+'"  size="33" maxlength="32"  <?php if (ADDRESS_BOOK_ADDRESS1_REQUIRED) echo ' required="required" ' ?> ></td>'+
		 		'  <td align="right"> <?php echo TEXT_FAX ?> </td>'+
		 		'  <td><input name="telephone3" type="tel" value="' +row.telephone3+'"  size="21" maxlength="20" ></td>'+
		 	'</tr>'+

		 	'<tr>'+
		 		'  <td align="right"> <?php echo TEXT_ADDRESS2 ?> </td>'+
		 		'  <td><input name="address2" type="text" value="' +row.address2+'"  size="33" maxlength="32"  <?php if (ADDRESS_BOOK_ADDRESS2_REQUIRED) echo ' required="required" ' ?> ></td>'+
		 		'  <td align="right"> <?php echo TEXT_MOBILE_PHONE ?> </td>'+
		 		'  <td><input name="telephone4" type="tel" value="' +row.telephone4+'"  size="21" maxlength="20" ></td>'+
		 	'</tr>'+

		 	'<tr>'+
		 		'  <td align="right"> <?php echo TEXT_CITY_TOWN ?> </td>'+
		 		'  <td><input name="city_town" type="text" value="' +row.city_town+'"  size="25" maxlength="24"  <?php if (ADDRESS_BOOK_CITY_TOWN_REQUIRED) echo ' required="required" ' ?> ></td>'+
		 		'  <td align="right"> <?php echo TEXT_EMAIL ?> </td>'+
		 		'  <td><input name="email" type="email" value="' +row.email+'"  size="51" maxlength="50" ></td>'+
		 	'</tr>'+
		 
			'<tr>'+
	 			'  <td align="right"> <?php echo TEXT_STATE_PROVINCE ?> </td>'+
	 			'  <td><input name="state_province" type="text" value="' +row.state_province+'"  size="25" maxlength="24"  <?php if (ADDRESS_BOOK_STATE_PROVINCE_REQUIRED) echo ' required="required" ' ?> ></td>'+
	 			'  <td align="right"> <?php echo TEXT_WEBSITE ?> </td>'+
		 		'  <td><input name="website" type="url" value="' +row.website+'"  size="51" maxlength="50"> </td>'+
		 	'</tr>'+
			'<tr>'+
	 			'  <td align="right"> <?php echo TEXT_POSTAL_CODE ?> </td>'+
 				'  <td><input name="postal_code" type="text" value="' +row.postal_code+'"  size="11" maxlength="10"  <?php if (ADDRESS_BOOK_POSTAL_CODE_REQUIRED) echo ' required="required" ' ?> ></td>'+
 				'  <td align="right"> <?php echo TEXT_COUNTRY ?> </td>'+
	 			'  <td><select class="easyui-combobox" id="country_code" ><?php foreach ($_SESSION['language']->get_countries_dropdown() as $key => $value) echo "<option value=\"{$value['id']}\">{$value['text']}</option>";?> </select></td>'+
	 		'</tr>'+
	 		'<tr>'+
	 		'<td></td><td><a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save"   plain="true" onclick="saveShipping(this)"><?php echo TEXT_SAVE; ?></a></td>'+
	 		'<td></td><td><a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="cancelShipping(this)"><?php echo TEXT_CANCEL; ?></a></td>'+
	 		'</table></form>'
}

function newShipping(){
	console.log('new shipping address was clicked');
    $('#shipping_address').datagrid('appendRow',{isNewRecord:true});
    var index = $('#shipping_address').datagrid('getRows').length - 1;
    $('#shipping_address').datagrid('selectRow', index);
    $('#shipping_address').datagrid('expandRow', index);
    $('#shipping_address').datagrid('getRowDetail',index).find('form').form('reset');
}

function saveShipping (target){
	var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	var index = parseInt(tr.attr('datagrid-row-index'));
	var row = $('#shipping_address').datagrid('getSelected');
    var url = row.isNewRecord == true ? 'index.php?action=saveNewAddress' : 'index.php?action=saveAddress&id='+row.address_id;
    $('#shipping_address').datagrid('getRowDetail',index).find('form').form('submit',{
        url: url,
        onSubmit: function(param){
        	console.log('sending');
            param.ref_id = '<?php echo $basis->cInfo->contact->id;?>';
            return $(this).form('validate');
        },
        success: function(data){
        	console.log('shipping saved successfully');
            data = eval('('+data+')');
            data.isNewRecord = false;
            $('#shipping_address').datagrid('collapseRow',index);
            $('#shipping_address').datagrid('updateRow',{
                index: index,
                row: data
            });
        }
    });
}

function cancelShipping (target){
	var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	var index = parseInt(tr.attr('datagrid-row-index'));
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
    if (row){
        $.messager.confirm('<?php echo TEXT_CONFORM?>','<?php echo sprintf(TEXT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ARGS, TEXT_SHIPPING_ADDRESS)?>',function(r){
            if (r){
                var index = $('#shipping_address').datagrid('getRowIndex',row);
                $.post("index.php?action=deleteAddress",{id:row.id},function(){
                    $('#shipping_address').datagrid('deleteRow',index);
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
}

function saveBilling (target){
	var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	var index = parseInt(tr.attr('datagrid-row-index'));
	console.log('save billing address was clicked');
    var row = $('#billing_address').datagrid('getRows')[index];
    var url = row.isNewRecord ? 'index.php?action=saveNewAddress' : 'index.php?action=saveNewAddress&id='+row.id;
    $('#billing_address').datagrid('getRowDetail',index).find('form').form('submit',{
        url: url,
        onSubmit: function(param){
            param.ref_id =  '<?php echo $basis->cInfo->contact->id;?>';
            return $(this).form('validate');
        },
        success: function(data){
            data = eval('('+data+')');
            data.isNewRecord = false;
            $('#billing_address').datagrid('collapseRow',index);
            $('#billing_address').datagrid('updateRow',{
                index: index,
                row: data
            });
        }
    });
}

function cancelBilling (target){
	var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	var index = parseInt(tr.attr('datagrid-row-index'));
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
    if (row){
        $.messager.confirm('<?php echo TEXT_CONFORM?>','<?php echo sprintf(TEXT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ARGS, TEXT_BILLLING_ADDRESS)?>',function(r){
            if (r){
                var index = $('#billing_address').datagrid('getRowIndex',row);
                $.post("index.php?action=deleteAddress",{id:row.id},function(){
                    $('#billing_address').datagrid('deleteRow',index);
                });
            }
        });
    }
}
</script>