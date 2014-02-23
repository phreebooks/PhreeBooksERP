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
//  Path: /modules/inventory/classes/fields.php
//

namespace inventory\classes;
class fields extends \core\classes\fields{
	public  $help_path   = '07.04.05';
	public  $title       = TEXT_EXTRA_FIELDS;
	public  $module      = 'inventory';
	public  $db_table    = TABLE_INVENTORY;
	public  $type_params = 'inventory_type';
	public  $extra_buttons = '';
  
  public function __construct(){
  	gen_pull_language('inventory');
  	require(DIR_FS_MODULES . 'inventory/defaults.php');
  	foreach ($inventory_types_plus as $key => $value) $this->type_array[] = array('id'=>$key, 'text'=>$value);
    $this->type_desc    = INV_ENTRY_INVENTORY_TYPE;
    parent::__construct();
  }

  function btn_save($id = '') {
  	if(parent::btn_save($id = '')){
  		$sql_data_array['use_in_inventory_filter'] = db_prepare_input($_POST['use_in_inventory_filter']);
  		db_perform(TABLE_EXTRA_FIELDS, $sql_data_array, 'update', "id = $this->id");
  		return true;
  	}
  	return false;
  }
  public function build_form_html($action, $id = '') {
  	$output  = parent::build_form_html($action, $id = '');
  	$output .= '<table style="border-collapse:collapse;width:100%;">' . chr(10);
  	$output .= '  <thead class="ui-widget-header"><tr><th>'.TEXT_OPTIONS."</th></tr></thead>\n";
  	$output .= '  <tbody class="ui-widget-content"><tr><td>'."\n";
  	$output .= html_checkbox_field('use_in_inventory_filter', true,  $this->use_in_inventory_filter, '').'&nbsp;'.TEXT_USE_IN_FILTER."<br />\n";
  	$output .= '  </td></tr></tbody>'."\n";
  	$output .= "</table>\n";
  	return $output;
  }
}

?>