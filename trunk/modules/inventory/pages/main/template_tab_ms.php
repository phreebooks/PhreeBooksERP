<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
//  Path: /modules/inventory/pages/main/template_tab_ms.php
//
// start the master stock tab html
?>
<div title="<?php echo INV_MS_ATTRIBUTES;?>" id="tab_master">
<?php echo TEXT_MS_HELP;?>
<div style="margin:auto;min-height:165px;">
 <?php if($cInfo->edit_ms_list){?>
  <div style="float:left;width:50%">
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto" width="250px">
   <thead class="ui-widget-header">
	  <tr><th colspan="2"><?php echo INV_TEXT_ATTRIBUTE_1; ?></th></tr>
    </thead>
    <tbody class="ui-widget-content">
	  <tr>
	    <td><?php echo TEXT_TITLE; ?></td>
	    <td><?php echo html_input_field('attr_name_0', $cInfo->attr_name_0, 'size="11" maxlength="10" onchange="masterStockTitle(0)"'); ?></td>
	  </tr>
	  <tr>
	    <td colspan="2"><?php echo INV_MASTER_STOCK_ATTRIB_ID . ' '; ?>
			<?php echo html_input_field('attr_id_0', '', 'size="3" maxlength="2"', true); ?>
			<?php echo html_button_field('attr_add_0', TEXT_ADD, 'onclick="masterStockBuildList(\'add\', 0)"', 'SSL'); ?>
		</td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_DESCRIPTION; ?></td>
	    <td><?php echo html_input_field('attr_desc_0', '', '', true); ?></td>
	  </tr>
	  <tr>
	    <th colspan="2"><?php echo INV_TEXT_ATTRIBUTES; ?></th>
		  </tr>
		  <tr>
      		<td align="center" colspan="2">
  			  <table>
			    <tr>
				  <td><?php echo html_pull_down_menu('attr_index_0', $cInfo->attr_array0, '', 'size="5" width="200px"'); ?></td>
			  <td valign="top"><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="masterStockBuildList(\'delete\', 0)"') . chr(10); ?></td>
			</tr>
	      </table>
		</td>
	  </tr>
	</tbody>
  </table>
 </div>
 <div style="width:50%">
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto" width="250px">
   <thead class="ui-widget-header">
	  <tr><th colspan="2"><?php echo INV_TEXT_ATTRIBUTE_2; ?></th></tr>
    </thead>
    <tbody class="ui-widget-content">
	  <tr>
	    <td><?php echo TEXT_TITLE; ?></td>
	    <td><?php echo html_input_field('attr_name_1', $cInfo->attr_name_1, 'size="11" maxlength="10" onchange="masterStockTitle(1)"'); ?></td>
	  </tr>
	  <tr>
	    <td colspan="2"><?php echo INV_MASTER_STOCK_ATTRIB_ID . ' '; ?>
			<?php echo html_input_field('attr_id_1', '', 'size="3" maxlength="2"', true); ?>
			<?php echo html_button_field('attr_add_1', TEXT_ADD, 'onclick="masterStockBuildList(\'add\', 1)"', 'SSL'); ?>
		</td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_DESCRIPTION; ?></td>
	    <td><?php echo html_input_field('attr_desc_1', '', '', true); ?></td>
	  </tr>
	  <tr>
	    <th colspan="2"><?php echo INV_TEXT_ATTRIBUTES; ?></th>
		  </tr>
		  <tr>
      		<td align="center" colspan="2">
  			  <table>
			    <tr>
				  <td><?php echo html_pull_down_menu('attr_index_1', $cInfo->attr_array1, '', 'size="5" width="200px"'); ?></td>
			  <td valign="top"><?php echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="masterStockBuildList(\'delete\', 1)"') . chr(10); ?></td>
			</tr>
	      </table>
		</td>
	  </tr>
	</tbody>
  </table>
 </div>
 <?php } ?>
 <div>
 
  <table id="sku_list" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;margin-top:20px">
   <thead class="ui-widget-header">
    <tr>
	  <th colspan="11" align="center"><?php if($cInfo->edit_ms_list) echo INV_MS_CREATED_SKUS; ?></th>
    </tr>
    <tr>
	  <th><?php echo TEXT_SKU; ?></th>
	  <th><?php echo INV_ENTRY_INVENTORY_DESC_SHORT; ?></th>
	  <th><?php echo $cInfo->attr_name_0; ?></th>
	  <th><?php echo $cInfo->attr_name_1; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_QTY_ON_HAND; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_QTY_ON_ORDER; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_QTY_ON_SALES_ORDER; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_ENTRY_ITEM_MINIMUM_STOCK; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_ENTRY_ITEM_REORDER_QUANTITY; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_ENTRY_INV_ITEM_COST; ?></th>
	  <th width="20px" style="padding:0px 5px"><?php echo INV_ENTRY_FULL_PRICE; ?></th>
    </tr>
    </thead>
    <tbody id="sku_list_body" class="ui-widget-content">
    	<?php 
    	$odd = false;
    	 if(!empty($cInfo->child_array)){
    		foreach ($cInfo->child_array as $value) {
		  		if($odd) echo '<tr class="odd" style="cursor:pointer"  onclick="window.open(\''. html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=main&amp;cID=' . $value['id'] . '&amp;action=edit', 'SSL').'\',\'_blank\')">' . chr(10);
		  		else     echo '<tr class="even" style="cursor:pointer" onclick="window.open(\''. html_href_link(FILENAME_DEFAULT, 'module=inventory&amp;page=main&amp;cID=' . $value['id'] . '&amp;action=edit', 'SSL').'\',\'_blank\')">' . chr(10);
			  	if($value['inactive'] == 1) echo '<td style="background-color:pink;padding:1px 15px;">' . $value['sku'] . '</td>' . chr(10);
			  	else echo '<td style="padding:1px 15px;">' . $value['sku'] . '</td>' . chr(10);
			  	echo '<td style="padding:1px 15px;">' . $value['desc'] . '</td>' . chr(10);
			  	echo '<td style="padding:1px 15px;">' . $value['0'] . '</td>'. chr(10);
			  	echo '<td style="padding:1px 15px;">' . $value['1'] . '</td>'. chr(10);
			  	if($value['on_hand'] < $value['min_stock'] && $value['inactive'] != 1 && $value['min_stock'] != 0) echo '<td style="background-color:green;" width="20px" align="center">' . $value['on_hand'] . '</td>'. chr(10);
			  	else echo '<td width="20px" align="center">' . $value['on_hand'] . '</td>'. chr(10);
				echo '<td width="20px" align="center">' . $value['on_order'] . '</td>'. chr(10);
				echo '<td width="20px" align="center">' . $value['on_sales'] . '</td>'. chr(10);
				echo '<td width="20px" align="center">' . $value['min_stock'] . '</td>'. chr(10);
				echo '<td width="20px" align="center">' . $value['reorder_qty'] . '</td>'. chr(10);
				echo '<td width="20px" align="right">' . $currencies->precise($value['cost']) . '</td>'. chr(10);
				echo '<td width="20px" align="right">' . $currencies->precise($value['price']) . '</td>'. chr(10);
		      	echo '</tr>' . chr(10);
		      	$odd = !$odd;
    		}
    	} ?>
    </tbody>
  </table>
 </div>
 
</div>
</div>
