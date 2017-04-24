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
//  Path: /modules/inventory/pages/main/js_include.php
//
?>
<script type="text/javascript" src="includes/easyui/plugins/datagrid-detailview.js"></script>
<script type="text/javascript" src="includes/easyui/plugins/datagrid-groupview.js"></script>
<script type="text/javascript" src="includes/easyui/plugins/jquery.edatagrid.js"></script>
<script type="text/javascript">
document.title = '<?php echo sprintf(TEXT_MANAGER_ARGS, TEXT_INVENTORY); ?>';
// pass some php variables
var image_delete_text 	= '<?php echo TEXT_DELETE; ?>';
var image_delete_msg  	= '<?php echo INV_MSG_DELETE_INV_ITEM; ?>';
var text_sku          	= '<?php echo TEXT_SKU; ?>';
var text_properties   	= '<?php echo TEXT_PROPERTIES;?>';
var default_tax 	  	= '<?php echo $basis->cInfo->inventory->purch_taxable;?>';

<?php echo $basis->cInfo->inventory->js_tax_rates;?>
// required function called with every page load
function init() {
	<?php
	$action_array = array('edit','properties','create');
  	if(in_array($_REQUEST['action'], $action_array)&& empty($basis->cInfo->inventory->purchase_array)) {
  		echo "  addVendorRow();";
  	}
  	?>
  	$('#search_text').focus();
  	$('#search_text').select();
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  if (error == 1) {
	$.messager.alert("Error",error_message,"error");
	return false;
  } else {
	return true;
  }
}

//Insert other page specific functions here.
function tax(id, text, rate) {
  this.id   = id;
  this.text = text;
  this.rate = rate;
}

function check_sku() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var sku = document.getElementById('sku').value;
  if (sku == "") {
	error_message = error_message + "<?php echo JS_SKU_BLANK; ?>";
	error = 1;
  }

  if (error == 1) {
	$.messager.alert("Error",error_message,"error");
	return false;
  } else {
	return true;
  }
}

function setSkuLength() {
	var sku_val = document.getElementById('sku').value;
	if (document.getElementById('inventory_type').value == 'ms') {
		sku_val.substr(0, <?php echo (MAX_INVENTORY_SKU_LENGTH - 5); ?>);
		document.getElementById('sku').value = sku_val.substr(0, <?php echo (MAX_INVENTORY_SKU_LENGTH - 5); ?>);
		document.getElementById('sku').maxLength = <?php echo (MAX_INVENTORY_SKU_LENGTH - 5); ?>;
	} else {
		document.getElementById('sku').maxLength = <?php echo MAX_INVENTORY_SKU_LENGTH; ?>;
	}
}

function showImage() {
	$('#inv_image').window('open');
}


function printOrder(id) {
	var printWin = window.open("index.php?module=phreeform&page=popup_gen&gID=cust:inv&date=a&xfld=journal_main.id&xcr=EQUAL&xmin="+id,"reportFilter","width=700px,height=550px,resizable=1,scrollbars=1,top=150px,left=200px");
	printWin.focus();
}

function product_margin_change(){
	var highest = 0;
	var x=document.getElementsByName("item_cost_array[]");
	for (var i = 0; i < x.length; i++) {
       	var current = cleanCurrency(x.item(i).value);
       	if(current > highest){
       		 highest = current;
       	}
    }
	margin = cleanCurrency(document.getElementById('product_margin' ).value);
	document.getElementById('full_price_with_tax' ).value = formatCurrency(highest * margin );
	update_full_price_incl_tax(false, false, true);
}


function what_to_update(){
	margin = cleanCurrency(document.getElementById('product_margin' ).value);
	var highest = 0;
	var x=document.getElementsByName("item_cost_array[]");
	for (var i = 0; i < x.length; i++) {
    	var temp = x.item(i).value;
    	var calculate = cleanCurrency(temp);
    	if(calculate > highest){
    		 highest = calculate;
    	}
    }
    if(document.getElementById('full_price_with_tax' ).value != formatCurrency(highest * margin )){
		var anwser = prompt ("<?php echo INV_WHAT_TO_CALCULATE; ?>",'1');
		if (anwser == '1'){
			update_full_price_incl_tax(true, true, false);
		}else if (anwser == '2'){
			document.getElementById('full_price_with_tax' ).value = formatCurrency(highest * margin );
			update_full_price_incl_tax(false, false, true);
		}
    }
}

function update_full_price_incl_tax(margin, inclTax, fullprice) {
//calculate margin
	var highest = 0;
	if(margin){
		var x=document.getElementsByName("item_cost_array[]");
		for (var i = 0; i < x.length; i++) {
        	var temp = x.item(i).value;
        	var calculate = cleanCurrency(temp);
        	if(parseFloat(calculate) > parseFloat(highest)){
        		 highest = calculate;
        	}
        }
        document.getElementById('product_margin' ).value = formatCurrency(cleanCurrency(document.getElementById('full_price_with_tax' ).value) / highest);
	}
//calculate full_price_with_tax
	if(inclTax){
		if(document.getElementById('full_price' ).value!== '' && document.getElementById('item_taxable' ).value!== ''){
			tax_index = document.getElementById('item_taxable' ).value;
			document.getElementById('full_price_with_tax' ).value = formatCurrency(cleanCurrency(document.getElementById('full_price' ).value)* (1+(tax_rates[tax_index].rate / 100)));
		}else{
			document.getElementById('full_price_with_tax' ).value = '';
		}
	}
//calculate full_price
	if(fullprice){
		if(document.getElementById('full_price_with_tax' ).value !== '' && document.getElementById('item_taxable' ).value!== ''){
			tax_index = document.getElementById('item_taxable' ).value;
			document.getElementById('full_price' ).value = formatCurrency(cleanCurrency(document.getElementById('full_price_with_tax').value) / (1+(tax_rates[tax_index].rate / 100)));
		}else{
			document.getElementById('full_price' ).value = '';
		}
	}
//check to see if phreebooks would calculate the same full_price_with_tax if this is not the case high lite full_price_with_tax red.
	document.getElementById('full_price_with_tax' ).value = formatCurrency(cleanCurrency(document.getElementById('full_price_with_tax' ).value));
	var tax_index = document.getElementById('item_taxable' ).value;
	var text = formatCurrency(cleanCurrency(document.getElementById('full_price' ).value)* (1+(tax_rates[tax_index].rate / 100)));
	var full = document.getElementById('full_price_with_tax' ).value;
	if(full !== text ){
		$("#full_price_with_tax").css({
			"background":"#FF3300"
		});
		$("#full_price_with_tax").attr("title","<?php echo INV_CALCULATING_ERROR?>" + text);
	}else {
		$("#full_price_with_tax").css({
			"background":"#FFFFFF"
		});
		$("#full_price_with_tax").removeAttr("title");
	}
}

function priceMgr(id, cost, price, type) {
  if (!cost)  cost  = document.getElementById('item_cost')  ? cleanCurrency(document.getElementById('item_cost').value)  : 0;
  if (!price) price = document.getElementById('full_price') ? cleanCurrency(document.getElementById('full_price').value) : 0;
  window.open('index.php?module=inventory&page=popup_price_mgr&iID='+id+'&cost='+cost+'&price='+price+'&type='+type,"price_mgr","width=860,height=500,resizable=1,scrollbars=1,top=150,left=200");
}

function InventoryList(rowCnt) {
	if (rowCnt == '') return;
	var url = "index.php?module=inventory&page=popup_inv&rowID="+rowCnt;
	if ($('#sku_'+rowCnt).val() != '') url += "&search_text="+$('#sku_'+rowCnt).val();
  	window.open(url,"inventory","width=700,height=550,resizable=1,scrollbars=1,top=150,left=200");
}
// ******* BOF - MASTER STOCK functions *********/

function masterStockTitle(id) {
  if(document.all) { // IE browsers
    document.getElementById('sku_list').rows[1].cells[id+1].innerText = document.getElementById('attr_name_'+id).value;
  } else { //firefox
    document.getElementById('sku_list').rows[1].cells[id+1].textContent = document.getElementById('attr_name_'+id).value;
  }
}

function masterStockBuildList(action, id) {
  switch (action) {
    case 'add':
	  if (document.getElementById('attr_id_'+id).value == '' || document.getElementById('attr_id_'+id).value == '') {
		  $.messager.alert('error','<?php echo JS_MS_INVALID_ENTRY; ?>','error');
		return;
	  }
	  var str = document.getElementById('attr_desc_'+id).value ;
	  if(str.search(",") == true){
		  $.messager.alert('error','<?php echo TEXT_COMMA_IS_NOT_ALLOWED_IN_THE_DESCRIPTION; ?>','error');
		  return;
	  }
	  if(str.search(":") == true){
		  $.messager.alert('error','<?php echo TEXT_COLON_IS_NOT_ALLOWED_IN_THE_DESCRIPTION; ?>','error');
		  return;
	  }
	  var newOpt = document.createElement("option");
	  newOpt.text = document.getElementById('attr_id_'+id).value + ' : ' + document.getElementById('attr_desc_'+id).value;
	  newOpt.value = document.getElementById('attr_id_'+id).value + ':' + document.getElementById('attr_desc_'+id).value;
	  document.getElementById('attr_index_'+id).options.add(newOpt);
	  document.getElementById('attr_id_'+id).value = '';
	  document.getElementById('attr_desc_'+id).value = '';
	  break;

	case 'delete':
	  if (confirm('<?php echo INV_MSG_DELETE_INV_ITEM; ?>')) {
        var elementIndex = document.getElementById('attr_index_'+id).selectedIndex;
	    document.getElementById('attr_index_'+id).remove(elementIndex);
	  } else {
	    return;
	  }
	  break;

	default:
  }
  masterStockBuildSkus();
}

function masterStockBuildSkus() {
  var newRow, newCell, newValue0, newValue1, newValue2, attrib0, attrib1;
  var ms_attr_0 = '';
  var ms_attr_1 = '';
  var sku = document.getElementById('sku').value;
  newValue0 = '';
  newValue1 = '';
  newValue2 = '';
  if (document.getElementById('attr_index_0').length) {
    for (i=0; i<document.getElementById('attr_index_0').length; i++) {
	  attrib0 = document.getElementById('attr_index_0').options[i].value;
	  ms_attr_0 += attrib0 + ',';
	  attrib0 = attrib0.split(':');
  	  newValue0 = sku + '-' + attrib0[0];
	  newValue1 = attrib0[1];
      if (document.getElementById('attr_index_1').length) {
        for (j=0; j<document.getElementById('attr_index_1').length; j++) {
	      attrib1 = document.getElementById('attr_index_1').options[j].value;
	      attrib1 = attrib1.split(':');
  	      newValue0 = sku + '-' + attrib0[0] + attrib1[0];
	      newValue2 = attrib1[1];
          insertTableRow(newValue0, newValue1, newValue2);
        }
	  } else {
        insertTableRow(newValue0, newValue1, newValue2);
	  }
    }
  } else { // blank row
    insertTableRow(newValue0, newValue1, newValue2);
  }

  for (j=0; j<document.getElementById('attr_index_1').length; j++) {
    attrib1 = document.getElementById('attr_index_1').options[j].value;
	ms_attr_1 += attrib1 + ',';
  }

  document.getElementById('ms_attr_0').value = ms_attr_0;
  document.getElementById('ms_attr_1').value = ms_attr_1;
}

function insertTableRow(newValue0, newValue1, newValue2) {
	var add = true;
	$('#sku_list_body tr').each(function() {
   		 if (newValue0 == $(this).find("td").eq(0).html()){
   			add = false;
   		 }
	});
	if(add){
		newRow = document.getElementById('sku_list_body').insertRow(-1);
		var odd = ((newRow.rowIndex)%2 == 0) ? 'even' : 'odd';
		newRow.setAttribute("className", odd);
		newRow.setAttribute("class", odd);
	   	if(document.all) { // IE browsers
	   		newCell = newRow.insertCell(-1);
	   	    newCell.innerText = newValue0;
	   	    newCell.style.paddingBottom 	='1px';
	   	 	newCell.style.paddingTop		='1px';
	   	 	newCell.style.paddingLeft		='15px';
	   	 	newCell.style.paddingRight		='15px';
	   	    newCell = newRow.insertCell(-1);
	   	    newCell = newRow.insertCell(-1);
	   	    newCell.innerText = newValue1;
	   	 	newCell.style.paddingBottom 	='1px';
	   	 	newCell.style.paddingTop		='1px';
	   	 	newCell.style.paddingLeft		='15px';
	   	 	newCell.style.paddingRight		='15px';
	   	    newCell = newRow.insertCell(-1);
	   	    newCell.innerText = newValue2;
	   	 	newCell.style.paddingBottom 	='1px';
	   	 	newCell.style.paddingTop		='1px';
	   	 	newCell.style.paddingLeft		='15px';
	   	 	newCell.style.paddingRight		='15px';
	   	 	newCell = newRow.insertCell(-1);
		   	newCell.innerText = 0;
		   	newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.innerText = 0;
	   		newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.innerText = 0;
	   		newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.innerText = 0;
	   		newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.innerText = 0;
	   		newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.innerText = 0;
	   		newCell.style.textAlign= 'right';
	   		newCell = newRow.insertCell(-1);
	   		newCell.innerText = 0;
	   		newCell.style.textAlign= 'right';
	   	} else { //firefox
	   	    newCell = newRow.insertCell(-1);
	   	 	newCell.style.paddingBottom 	='1px';
	   	 	newCell.style.paddingTop		='1px';
	   	 	newCell.style.paddingLeft		='15px';
	   	 	newCell.style.paddingRight		='15px';
	   	    newCell.textContent = newValue0;
	   	    newCell = newRow.insertCell(-1);
	   	    newCell = newRow.insertCell(-1);
	   	    newCell.textContent = newValue1;
	   	 	newCell.style.paddingBottom 	='1px';
	   	 	newCell.style.paddingTop		='1px';
	   	 	newCell.style.paddingLeft		='15px';
	   	 	newCell.style.paddingRight		='15px';
	   	    newCell = newRow.insertCell(-1);
	   	    newCell.textContent = newValue2;
	   	 	newCell.style.paddingBottom 	='1px';
	   	 	newCell.style.paddingTop		='1px';
	   	 	newCell.style.paddingLeft		='15px';
	   	 	newCell.style.paddingRight		='15px';
	   	 	newCell = newRow.insertCell(-1);
	   	 	newCell.textContent = 0;
	   	 	newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.textContent = 0;
	   		newCell.style.textAlign= 'center';
			newCell = newRow.insertCell(-1);
		   	newCell.textContent = 0;
		   	newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.textContent = 0;
	   		newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.textContent = 0;
	   		newCell.style.textAlign= 'center';
	   		newCell = newRow.insertCell(-1);
	   		newCell.textContent = 0;
	   		newCell.style.textAlign= 'right';
	   		newCell = newRow.insertCell(-1);
	   		newCell.textContent = 0;
	   		newCell.style.textAlign= 'right';
		}
	}
  }
//******* BOF - MASTER STOCK functions *********/

// ******* BOF - BOM functions *********/

function addBOMRow() {
	var cell = Array(6);
	var newRow = document.getElementById("bom_table_body").insertRow(-1);
	var newCell;
	rowCnt = newRow.rowIndex;
	// NOTE: any change here also need to be made below for reload if action fails
	cell[0] = '<td align="center">';
	cell[0] += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();bomTotalValues();"')); ?>';
	cell[1] = '<td align="center">';
	// Hidden fields
	cell[1] += '<input type="hidden" name="id_'+rowCnt+'" id="id_'+rowCnt+'" value="">';
	// End hidden fields
	cell[1] += '<input type="text" name="assy_sku[]" id="sku_'+rowCnt+'" value="" size="<?php echo (MAX_INVENTORY_SKU_LENGTH + 1); ?>" onchange="bom_guess('+rowCnt+')" maxlength="<?php echo MAX_INVENTORY_SKU_LENGTH; ?>">&nbsp;';
	cell[1] += buildIcon(icon_path+'16x16/actions/system-search.png', text_sku, 'align="top" style="cursor:pointer" onclick="InventoryList('+rowCnt+')"') + '&nbsp;<\/td>';
	cell[1] += buildIcon(icon_path+'16x16/actions/document-properties.png', text_properties, 'id="sku_prop_'+rowCnt+'" align="top" style="cursor:pointer" onclick="InventoryProp('+rowCnt+')"');
	cell[2] = '<td><input type="text" name="assy_desc[]" id="desc_'+rowCnt+'" value="" size="64" maxlength="64"><\/td>';
	cell[3] = '<td><input type="text" name="assy_qty[]" id="qty_'+rowCnt+'" value="0" size="6" maxlength="5"><\/td>';
	cell[4] = '<td><input type="text" name="assy_item_cost[]" id="item_cost_'+rowCnt+'" value="0" size="6" maxlength="5"><\/td>';
	cell[5] = '<td><input type="text" name="assy_sales_price[]" id="sales_price_'+rowCnt+'" value="0" size="6" maxlength="5"><\/td>';

	for (var i=0; i<cell.length; i++) {
		newCell = newRow.insertCell(-1);
		newCell.innerHTML = cell[i];
	}

	return rowCnt;
}
// ******* BOF - AJAX BOM load sku pair *********/

function loadSkuDetails(iID, rID) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&iID='+iID+'&rID='+rID,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
      },
	  success: processSkuDetails
    });
}

function processSkuDetails(sXml) { // call back function
	var text = '';
	var xml = parseXml(sXml);
	if (!xml) return;
	var rowID = $(xml).find("rID").text();
	var sku   = $(xml).find("sku").text(); // only the first find, avoids bom add-ons
	if (!sku || $(xml).find("inventory_type").text() == 'ms' || $(xml).find("inventory_type").text() == 'mb') {
		InventoryList(rowID);
		return;
	}
	document.getElementById('sku_'+rowID).value              = sku;
	document.getElementById('sku_'+rowID).style.color        = '';
	document.getElementById('desc_'+rowID).value             = $(xml).find("description_short").text();
	if(document.getElementById('qty_'+rowID).value == 0)       document.getElementById('qty_'+rowID).value = 1;
	document.getElementById('item_cost_'+rowID).value        = formatCurrency($(xml).find("item_cost").text());
	document.getElementById('sales_price_'+rowID).value      = formatCurrency($(xml).find("sales_price").text());
	bomTotalValues();
}
// ******* EOF - AJAX BOM load sku pair *********/
// ******* BOF - AJAX BOM item Properties pair *********/
function InventoryProp(rID) {
	var sku = document.getElementById('sku_'+rID).value;
	if (sku != text_search && sku != '') {
		  window.open("index.php?module=inventory&page=main&action=properties&sku="+sku+'&rowID='+rID,"inventory","width=800px,height=600px,resizable=1,scrollbars=1,top=50,left=50");
	}
}
// ******* EOF - AJAX BOM item Properties pair *********/
// ******* BOF - AJAX BOM Cost function pair *********/
function ajaxAssyCost() {
  var id = document.getElementById('rowSeq').value;
  if (id) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=bomCost&iID='+id,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
      },
	  success: showBOMCost
    });
  }
}

function showBOMCost(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("assy_cost").text()) {
	  $.messager.alert('Current Costs','<?php echo JS_INV_TEXT_ASSY_COST; ?>'+formatPrecise($(xml).find("assy_cost").text()),'info');
  }
}
// ******* EOF - AJAX BOM Cost function pair *********/

function bom_guess(rID){
	var sku = document.getElementById('sku_'+rID).value;
	if (sku != text_search && sku != '') {
	  $.ajax({
	      type: "GET",
		  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=skuDetails&sku='+sku+'&strict=1&rID='+rID,
	      dataType: ($.browser.msie) ? "text" : "xml",
	      error: function(XMLHttpRequest, textStatus, errorThrown) {
	    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
	      },
		  success: processSkuDetails
	    });
	}
}

function bomTotalValues(){
	var numRows = document.getElementById('bom_table_body').rows.length;
	var itemCost = null;
	var salesPrice = null;
	for (var i=1; i<numRows+1; i++) {
		var qty             = parseFloat(document.getElementById('qty_' + i ).value);
		var unit_itemCost   = parseFloat(cleanCurrency(document.getElementById('item_cost_'   + i ).value));
		var unit_salesPrice = parseFloat(cleanCurrency(document.getElementById('sales_price_' + i ).value));
		total_itemCost   = qty * unit_itemCost;
		total_salesPrice = qty * unit_salesPrice;
		itemCost   = itemCost + total_itemCost ;
		salesPrice = salesPrice  + total_salesPrice ;
	}
	document.getElementById('total_item_cost').value   = formatCurrency( itemCost );
	document.getElementById('total_sales_price').value = formatCurrency( salesPrice );
}
// ******* EOF - BOM functions *********/

// ******* BOF - AJAX Where Used pair *********/
function ajaxWhereUsed() {
  var id = document.getElementById('rowSeq').value;
  if (id) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=inventory&page=ajax&op=inv_details&fID=whereUsed&iID='+id,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
      },
	  success: showWhereUsed
    });
  }
}

function showWhereUsed(sXml) {
  var text = '';
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("sku_usage").text()) {
    $(xml).find("sku_usage").each(function() {
	  text += $(this).find("text_line").text() + "\n";
    });
    $.messager.alert("Where Used",text,"info");
  }
}
// ******* EOF - AJAX Where Used pair *********/

$(document).ready(function(){
	//event for change of textbox
	$("#description_short").change(function(){
		var value = document.getElementById('description_short').value;
		$("#heading_title").html(<?php echo '"'. TEXT_INVENTORY . ' - ' . TEXT_SKU . '# ' . $basis->cInfo->inventory->sku . ' (" ';?> + value +  ')');
	});
});
// ******* EOF - AJAX BOM Where Used pair *********/

//******** BOF - image carousel *******************/
var slideIndex = 1;

function plusDivs(n) {
	console.log("plusDivs = "+n);
  	showDivs(slideIndex += n);
}

function currentDiv(n) {
	console.log("currentDiv = "+n);
  	showDivs(slideIndex = n);
}

function showDivs(n) {
	console.log("showDivs = "+n);
  	var i;
  	var x = document.getElementsByClassName("inventoryImages");
  	var dots = document.getElementsByClassName("butons");
  	if (n > x.length) {slideIndex = 1}    
  	if (n < 1) {slideIndex = x.length}
  	for (i = 0; i < x.length; i++) {
     	x[i].style.display = "none";  
  	}
  	for (i = 0; i < dots.length; i++) {
    	dots[i].className = dots[i].className.replace(" whitebutton", "");
  	}
  	x[slideIndex-1].style.display = "block";  
  	dots[slideIndex-1].className += " whitebutton";
}

$(document).ready(function() {
	console.log("int");
  	showDivs(1);
});

</script>
<style>
.imagetools{
	display:inline-block;
	width:100%;
	font-size:18px;
	color:#fff;
	text-align:center;
	position:absolute;
	left:50%;
	bottom:0;
	transform:translate(-50%,0%);
	-ms-transform:translate(-50%,0%);
}

.whitebutton{
	color:#000;
	background-color:#fff;
}

.butons{
	cursor:pointer;
	height:13px;
	width:13px;
	padding:0;
	background-color:#000;
	color:#fff;
	display:inline-block;
	padding-left:8px;
	padding-right:8px;
	text-align:center;
	border-radius:50%;
	border:1px solid #ccc;
}

.butons:hover{
	color:#000;
	background-color:#fff;
}

</style>