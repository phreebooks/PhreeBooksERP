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
//  Path: /modules/work_orders/classes/wo_builder.php
//
namespace work_orders\classes;
class wo_builder {
  function __construct() {
  }

  function load_query_results($tableKey = 'id', $tableValue = 0) {
	global $db, $report;
	if (!$tableValue) return false;
	$sql = "select * from " . TABLE_WO_JOURNAL_MAIN . " where id = " . $tableValue;
	$result = $db->Execute($sql);
	while (list($key, $value) = each($result->fields)) $this->$key = db_prepare_input($value);
	$this->load_item_details($this->id);
	$this->build_bom_list($this->sku_id);
	$this->build_ref_lists();

	// convert particular values indexed by id to common name
	$result = $db->Execute("select sku, image_with_path from " . TABLE_INVENTORY . " where id = " . $this->sku_id);
	$this->sku             = $result->fields['sku'];
	$this->bar_code        = $result->fields['sku'];
	$this->image_with_path = $result->fields['image_with_path'];
	// sequence the results per Prefs[Seq]
	$output = array();
	foreach ($report->fieldlist as $OneField) { // check for a data field and build sql field list
	  if (in_array($OneField->type, array('Data','ImgLink','BarCode'))) { // then it's data field, include it
		$field = $OneField->boxfield[0]->fieldname;
	  	switch($field) {
		  case 'bar_code':  $output[] = $this->bar_code; break;
		  case 'sku_image': $output[] = $this->image_with_path; break;
		  default:
			$output[] = $this->$field; 
			break;
		}
	  }
	}
	// return results
	return $output;
  }

  function load_table_data($fields = '') {
	// fill the return data array
	$output = array();
	if (is_array($this->line_items) && is_array($fields)) {
	  foreach ($this->line_items as $key => $row) {
		$row_data = array();
		foreach ($fields as $idx => $element) {
		  $row_data['r' . $idx] = $this->line_items[$key][$element->fieldname];
		}
		$output[] = $row_data;
	  }
	}
	return $output;
  }

  function load_total_results($Params) {
	
  }

  function load_text_block_data($Params) {
	$TextField = '';
	foreach($Params as $Temp) {
	  $fieldname  = $Temp->fieldname;
      $temp = $Temp->formatting ? ProcessData($this->$fieldname, $Temp->formatting) : $this->$fieldname;
      $TextField .= AddSep($temp, $Temp->processing);
	}
	return $TextField;
  }

  function load_item_details($id) {
	global $db;
	// fetch the sales order and build the item list
	$this->invoice_subtotal = 0;
	$sql = "select i.id, i.step, i.task_name, t.description, i.mfg, i.qa, i.complete, i.data_entry,
	  t.ref_doc, t.ref_spec 
	  from " . TABLE_WO_JOURNAL_ITEM . " i inner join " . TABLE_WO_TASK . " t on i.task_id = t.id
	  where i.ref_id = " . $id . " order by i.step";
	$result = $db->Execute($sql);
	while (!$result->EOF) {
	  $index = $result->fields['id'];
	  $this->line_items[$index]['id']          = $result->fields['id'];
	  $this->line_items[$index]['step']        = $result->fields['step'];
	  $this->line_items[$index]['task_name']   = $result->fields['task_name'];
	  $this->line_items[$index]['description'] = $result->fields['description'];
	  $this->line_items[$index]['mfg']         = $result->fields['mfg'];
	  $this->line_items[$index]['qa']          = $result->fields['qa'];
	  $this->line_items[$index]['complete']    = $result->fields['complete'];
	  $this->line_items[$index]['data_entry']  = $result->fields['data_entry'];
	  $this->line_items[$index]['ref_doc']     = $result->fields['ref_doc'];
	  $this->line_items[$index]['ref_spec']    = $result->fields['ref_spec'];
	  $result->MoveNext();
	}
  }

  function build_bom_list($sku_id) {
    global $db;
	$this->bom_list = NULL;
	$result = $db->Execute("select sku, description, qty from " . TABLE_INVENTORY_ASSY_LIST . " 
	  where ref_id = '" . $sku_id . "' order by sku");
	$bom_list = array('Qty - SKU - Description');
	while (!$result->EOF) {
	  $bom_list[] = ($this->qty * $result->fields['qty']) . ' - ' . $result->fields['sku'] . ' - ' . $result->fields['description'];
	  $result->MoveNext();
	}
	$this->bom_list = implode(chr(10), $bom_list);
  }

  function build_ref_lists() {
    global $db;
	$result = $db->Execute("select ref_doc, ref_spec from " . TABLE_WO_MAIN . " where id = " . $this->id);
	$ref_docs  = ($result->fields['ref_doc']  ) ? explode(',', $result->fields['ref_doc'])   : array();
	$ref_specs = ($result->fields['ref_specs']) ? explode(',', $result->fields['ref_specs']) : array();
	if (is_array($this->line_items)) {
	  foreach ($this->line_items as $step) {
		$docs = explode(',', $step['ref_doc']);
		if (sizeof($docs > 0)) foreach ($docs as $doc) {
		  $doc = trim($doc);
		  if ($doc && !in_array($doc, $ref_docs)) $ref_docs[] = $doc;
		}
		$specs = explode(',', $step['ref_spec']);
		if (sizeof($specs > 0)) foreach ($specs as $spec) {
		  $spec = trim($spec);
		  if ($spec && !in_array($spec, $ref_specs)) $ref_specs[] = $spec;
		}
	  }
	  sort($ref_docs);
	  sort($ref_specs);
	}
	$this->ref_docs  = implode(', ', $ref_docs);
	$this->ref_specs = implode(', ', $ref_specs);
  }

  function build_selection_dropdown() {
	// build user choices for this class with the current and newly established fields
	$output = array();
	$output[] = array('id' => '',                            'text' => TEXT_SELECT);
	$output[] = array('id' => 'wo_journal_main.id',          'text' => 'Record ID');
	$output[] = array('id' => 'wo_journal_main.wo_id',       'text' => 'Work Order Num');
	$output[] = array('id' => 'wo_journal_main.wo_title',    'text' => 'Work Order Title');
	$output[] = array('id' => 'wo_journal_main.priority',    'text' => 'Priority');
	$output[] = array('id' => 'wo_journal_main.sku',         'text' => 'SKU');
	$output[] = array('id' => 'wo_journal_main.qty',         'text' => 'Quantity');
	$output[] = array('id' => 'wo_journal_main.post_date',   'text' => 'Post Date');
	$output[] = array('id' => 'wo_journal_main.closed',      'text' => 'Closed');
	$output[] = array('id' => 'wo_journal_main.closed_date', 'text' => 'Closed Date');
	$output[] = array('id' => 'wo_journal_main.notes',       'text' => 'Notes');
	$output[] = array('id' => 'wo_journal_main.bom_list',    'text' => 'BOM List');
	$output[] = array('id' => 'wo_journal_main.ref_specs',   'text' => 'Reference Specs');
	$output[] = array('id' => 'wo_journal_main.ref_docs',    'text' => 'Reference Docs');
	$output[] = array('id' => 'bar_code',                    'text' => 'SKU Bar Code');
	$output[] = array('id' => 'image_with_path',             'text' => 'SKU Image');
	return $output;
  }

  function build_table_drop_down() {
	// build the drop down choices
	$output = array();
	$output[] = array('id' => '',            'text' => TEXT_SELECT);
	$output[] = array('id' => 'step',        'text' => 'Step');
	$output[] = array('id' => 'task_name',   'text' => 'Task Name');
	$output[] = array('id' => 'description', 'text' => 'Task Description');
	$output[] = array('id' => 'mfg',         'text' => 'Mfg');
	$output[] = array('id' => 'qa',          'text' => 'QA');
	$output[] = array('id' => 'complete',    'text' => 'Complete');
	$output[] = array('id' => 'data_entry',  'text' => 'Data Entry Req');
	return $output;
  }  
 
}
?>