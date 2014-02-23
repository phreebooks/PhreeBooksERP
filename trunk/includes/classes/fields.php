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
//  Path: /includes/classes/fields.php
//
namespace core\classes;
class fields {
	public  $help_path      = '';
	public  $title          = '';
	public  $module         = '';
	public  $db_table       = '';
	public  $type_desc      = '';
	public  $type_array     = array(); 
    public  $type_params    = '';
    public  $extra_buttons  = '';
	public  $extra_tab_html = ''; 
    
  public function __construct($sync = true){ 
  	$this->security_id = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
	require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
  	foreach ($_REQUEST as $key => $value) $this->$key = $value;
  	$this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
	if ($sync) xtra_field_sync_list($this->module, $this->db_table);
  }

  function btn_save($id = '') {
  	global $db, $currencies;
  	\core\classes\user::validate_security($this->security_id, 2); // security check
    // clean out all non-allowed values and then check if we have a empty string 
	$this->field_name   = preg_replace("[^A-Za-z0-9_]", "", $this->field_name); 
	if ($this->field_name == '') throw new \Exception(EXTRA_ERROR_FIELD_BLANK);
	// check if the field name belongs to one of the mysql reserved names
	$reserved_names = array('select', 'delete', 'insert', 'update', 'to', 'from', 'where', 'and', 'or',
		'alter', 'table', 'add', 'change', 'in', 'order', 'set', 'inner');
	if (in_array($this->field_name, $reserved_names)) throw new \Exception(EXTRA_FIELD_RESERVED_WORD);
	// if the id is empty then check for duplicate field names
	if($this->id == ''){
	   $result = $db->Execute("SELECT id FROM ".TABLE_EXTRA_FIELDS." WHERE module_id='$this->module' AND field_name='$this->field_name'");
	   if ($result->RecordCount() > 0 && $this->id =='') throw new \Exception(EXTRA_FIELD_ERROR_DUPLICATE);
	}
	// condense the type array to a single string.
    while ($type = array_shift($this->type_array)){
        if (db_prepare_input($_POST['type_'. $type['id']]) == true) $temp_type .= $type['id'].':';
    }
	$values = array();
	$params = array(
	  'type'             => $this->entry_type,
	  $this->type_params => $temp_type,
	);
	switch ($this->entry_type) {
	  case 'text':
	  case 'html':
		$params['length']  = intval(db_prepare_input($_POST['length']));
		$params['default'] = db_prepare_input($_POST['text_default']);
		if ($params['length'] < 1) $params['length'] = DEFAULT_TEXT_LENGTH;
		if ($params['length'] < 256) {
			$values['entry_type'] = 'varchar(' . $params['length'] . ')';
			$values['entry_params'] = " default '" . $params['default'] . "'";
		} elseif ($_POST['TextLength'] < 65536) { 
			$values['entry_type'] = 'text';
		} elseif ($_POST['TextLength'] < 16777216) {
			$values['entry_type'] = 'mediumtext';
		} elseif ($_POST['TextLength'] < 65535) {
			$values['entry_type'] = 'longtext';
		}
		break;
	  case 'hyperlink':
	  case 'image_link':
	  case 'inventory_link':
		$params['default']      = db_prepare_input($_POST['link_default']);
		$values['entry_type']   = 'varchar(255)';
		$values['entry_params'] = " default '".$params['default']."'";
		break;
	  case 'integer':
		$params['select']  = db_prepare_input($_POST['integer_range']);
		$params['default'] = (int)db_prepare_input($_POST['integer_default']);
		switch ($params['select']) {
			case "0": $values['entry_type'] = 'tinyint';   break;
			case "1": $values['entry_type'] = 'smallint';  break;
			case "2": $values['entry_type'] = 'mediumint'; break;
			case "3": $values['entry_type'] = 'int';       break;
			case "4": $values['entry_type'] = 'bigint';
		}
		$values['entry_params'] = " default '" . $params['default'] . "'";
		break;
	  case 'decimal':
		$params['select']  = db_prepare_input($_POST['decimal_range']);
		$params['display'] = db_prepare_input($_POST['decimal_display']);
		$params['default'] = $currencies->clean_value(db_prepare_input($_POST['decimal_default']));
		switch ($params['select']) {
			case "0": 
				$values['entry_type'] = 'float(' . $params['display'] . ')'; 
                break;
            case "1":
            	$values['entry_type'] = 'double';
                break;
            case "2": 
            	$values['entry_type'] = 'decimal(' . $params['display'] .')';
                break;
            
		}
		$values['entry_params'] = " default '" . $params['default'] . "'";
		break;
	  case 'drop_down':
	  case 'radio':
		$params['default'] = db_prepare_input($_POST['radio_default']);
		$choices = explode(',',$params['default']);
		$max_choice_size = 0;
		while ($choice = array_shift($choices)) {
			$a_choice = explode(':',$choice);
			if ($a_choice[2] == 1) $values['entry_params'] = " default '" . $a_choice[0] . "'";
			if (strlen($a_choice[0]) > $max_choice_size) $max_choice_size = strlen($a_choice[0]);
		}
		$values['entry_type'] = 'char(' . $max_choice_size . ')';
		break;
	  case 'multi_check_box':	
		$params['default']    = db_prepare_input($_POST['radio_default']);
		$values['entry_type'] = 'text';
		break;	
	  case 'date':
		$values['entry_type'] = 'date';
		break;
	  case 'time':
		$values['entry_type'] = 'time';
		break;
	  case 'date_time':
		$values['entry_type'] = 'datetime';
		break;
	  case 'check_box':
		$params['select']       = db_prepare_input($_POST['check_box_range']);
		$values['entry_type']   = 'enum("0","1")';
		$values['entry_params'] = " default '" . $params['select'] . "'";
		break;
	  case 'time_stamp':
		$values['entry_type'] = 'timestamp';
		break;
	  default:
	}
	$sql_data_array = array(
	  'module_id'   => $this->module,
	  'description' => $this->description,
	  'params'      => serialize($params),
	);
	if ($this->tab_id <> '') {
	  $sql_data_array['group_by']  	 = $this->group_by;
	  $sql_data_array['sort_order']  = $this->sort_order;
	  $sql_data_array['entry_type']  = $this->entry_type;
	  $sql_data_array['field_name']  = $this->field_name;
	  $sql_data_array['tab_id']      = $this->tab_id;
	}
	
	if (!$this->id == 0) {
	  // load old field name as it may have been changed.
	  if ($this->tab_id <> '') {
		  $result = $db->Execute("select field_name from " . TABLE_EXTRA_FIELDS . " where id = " . $this->id );
		  if (isset($values['entry_type']) || $this->field_name <> $result->fields['field_name']) {
			$sql = "alter table " . $this->db_table . " change " . $result->fields['field_name'] . " " . $this->field_name . " 
			  " . $values['entry_type'] . (isset($values['entry_params']) ? $values['entry_params'] : '');
			$result = $db->Execute($sql);
		  }
	  }
	  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array, 'update', "id = " . $this->id );
	  gen_add_audit_log($this->module .' '. sprintf(EXTRA_FIELDS_LOG , TEXT_UPDATE), $this->id  . ' - ' . $this->field_name);
	} else {
	  $sql = "alter table " . $this->db_table . " 
		add column " . $this->field_name . " " . $values['entry_type'] . (isset($values['entry_params']) ? $values['entry_params'] : '');
	  $db->Execute($sql);
	  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array, 'insert');
	  $this->id  = db_insert_id();
	  gen_add_audit_log($this->module .' '. sprintf(EXTRA_FIELDS_LOG , TEXT_NEW), $this->id  . ' - ' . $this->field_name);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db;
  	\core\classes\user::validate_security($this->security_id, 4); // security check
	$result = $db->Execute("SELECT * FROM ".TABLE_EXTRA_FIELDS." WHERE id=$id");
	foreach ($result->fields as $key => $value) $this->$key = $value;
	if ($this->tab_id == '0') throw new \Exception (INV_CANNOT_DELETE_SYSTEM); // don't allow deletion of system fields
	$db->Execute("DELETE FROM ".TABLE_EXTRA_FIELDS." WHERE id=$this->id");
	$db->Execute("ALTER TABLE $this->db_table DROP COLUMN $this->field_name");
	gen_add_audit_log ($this->module.' '.sprintf(EXTRA_FIELDS_LOG, TEXT_DELETE), "$id - $this->field_name");
	return true;
  }

  function build_main_html() {
  	global $db;
	$tab_array = $this->get_tabs($this->module);
    $content = array();
	$content['thead'] = array(
	  'value' => array(TEXT_DESCRIPTION, TEXT_FLDNAME, TEXT_TAB_NAME, TEXT_TYPE, $this->type_desc, TEXT_SORT_ORDER, TEXT_GROUP, TEXT_ACTION),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
	$field_list = array('id', 'field_name', 'entry_type', 'description', 'tab_id', 'params', 'sort_order', 'group_by');
    $result = $db->Execute("select ".implode(', ', $field_list)." from ".TABLE_EXTRA_FIELDS." where module_id='" . $this->module ."' order by group_by, sort_order");
    $rowCnt = 0;
	while (!$result->EOF) {
	  $params  = unserialize($result->fields['params']);
	  $actions = '';
	  if ($this->security_id > 1)									  $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="loadPopUp(\'fields_edit\', ' . $result->fields['id'] . ')"') . chr(10);
	  if ($result->fields['tab_id'] <> '0' && $this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . EXTRA_FIELD_DELETE_INTRO . '\')) subjectDelete(\'fields\', ' . $result->fields['id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['description']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $result->fields['field_name'], 
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $tab_array[$result->fields['tab_id']],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $result->fields['entry_type'],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => isset($params[$this->type_params])?$params[$this->type_params]:'',
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $result->fields['sort_order'],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $result->fields['group_by'],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'fields_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable('field_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db, $currencies, $integer_lengths, $decimal_lengths, $check_box_choices;
	if ($action <> 'new') {
	   $result = $db->Execute("select * from ".TABLE_EXTRA_FIELDS." where id='$this->id'");
	   $params = unserialize($result->fields['params']);
	   foreach ($result->fields as $key => $value) $this->$key = $value;
	   if (is_array($params)) foreach ($params as $key => $value) $this->$key = $value;
	   switch ($this->entry_type){
	       case 'multi_check_box':
	  	   case 'drop_down':
	  	   case 'radio' :
	  	        $this->radio_default = $this->default;
	  	        break;
	       case 'hyperlink':
	       case 'image_link':
	       case 'inventory_link':
	  	        $this->link_default = $this->default;
	  	        break;
	       case 'text':
	  	   case 'html':
	  	        $this->text_default = $this->default;
	  	        break;
	       case 'decimal':
	       	    $this->decimal_range   = $this->select;
	  	        $this->decimal_default = number_format($this->default, $this->display, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);
	  	        $this->decimal_display = $this->display;
	  	        break;
	       case 'integer':
	       	    $this->integer_range   = $this->select;
	  	        $this->integer_default = $this->default;
	  	        break;
	       case 'check_box':
	  	        $this->check_box_range = $this->select;
	  	        break;
	   }
	}
	// build the tab list
	$tab_list = gen_build_pull_down($this->get_tabs($this->module));
	array_shift($tab_list);
	if ($action == 'new' && sizeof($tab_list) < 1) throw new \Exception(EXTRA_FIELDS_ERROR_NO_TABS);
    $choices  =  explode(':',$params[$this->type_params]);
	$disabled = ($this->tab_id !== '0') ? '' : 'disabled="disabled" ';
	$readonly = ($this->tab_id !== '0') ? '' : 'readonly="readonly" ';
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? TEXT_NEW_FIELD : TEXT_SETTINGS) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>' . INV_FIELD_NAME . '</td>' . chr(10);
	$output .= '	<td>' . html_input_field('field_name', $this->field_name, $readonly . 'size="33" maxlength="32"') . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td colspan="2">' . INV_FIELD_NAME_RULES . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
	$output .= '	<td>' . html_input_field('description', $this->description, 'size="65" maxlength="64"') . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>' . TEXT_SORT_ORDER . '</td>' . chr(10);
	$output .= '	<td>' . html_input_field('sort_order', $this->sort_order, 'size="65" maxlength="64"') . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>' . TEXT_GROUP . '</td>' . chr(10);
	$output .= '	<td>' . html_input_field('group_by', $this->group_by, 'size="65" maxlength="64"') . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	
	if (is_array($this->type_array)){
		$output .= '  <tr>' . chr(10);
		$output .= '	<td>' . $this->type_desc . '</td>' . chr(10);
		$output .= '	<td>' ;
		while ($type = array_shift($this->type_array)){
			if (!is_array($choices)){
				$output .= html_checkbox_field('type_'. $type['id'] , true , false,'', ''). $type['text'] ;
				$output .= '<br />';
			}elseif(in_array($type['id'],$choices)){
				$output .= html_checkbox_field('type_'. $type['id'],  true , true ,'', ''). $type['text'] ;
				$output .= '<br />';
			}else{
				$output .= html_checkbox_field('type_'. $type['id'],  true , false,'', ''). $type['text'] ;
				$output .= '<br />';
			}
		}
		$output .= '	</td>' ;
		$output .= '</tr>' . chr(10);
	}
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>' . INV_CATEGORY_MEMBER . '</td>' . chr(10);
	$output .= '	<td>' . html_pull_down_menu('tab_id', $tab_list, $this->tab_id, $disabled) . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr class="ui-widget-header">' . chr(10);
	$output .= '	<th colspan="2">' . TEXT_PROPERTIES . '</th>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>';
	$output .= html_radio_field('entry_type', 'text', ($this->entry_type=='text' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_TEXT_FIELD . '<br />';
	$output .= html_radio_field('entry_type', 'html', ($this->entry_type=='html' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_HTML_TEXT_FIELD . '</td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_MAX_NUM_CHARS;
	$output .= '<br />' . html_input_field('length', ($this->length ? $this->length : DEFAULT_TEXT_LENGTH), $readonly . 'size="10" maxlength="9"');
	$output .= '<br />' . INV_LABEL_DEFAULT_TEXT_VALUE . '<br />' . INV_LABEL_MAX_255;
	$output .= '<br />' . html_textarea_field('text_default', 35, 6, $this->text_default, $readonly);
	$output .= '	</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr class="ui-widget-content">' . chr(10);
	$output .= '	<td>';
	$output .= html_radio_field('entry_type', 'hyperlink',      ($this->entry_type=='hyperlink'      ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_HYPERLINK  . '<br />';
	$output .= html_radio_field('entry_type', 'image_link',     ($this->entry_type=='image_link'     ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_IMAGE_LINK . '<br />';
	$output .= html_radio_field('entry_type', 'inventory_link', ($this->entry_type=='inventory_link' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_INVENTORY_LINK;
	$output .= '	</td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_FIXED_255_CHARS;
	$output .= '<br />' . INV_LABEL_DEFAULT_TEXT_VALUE;
	$output .= '<br />' . html_textarea_field('link_default', 35, 3, $this->link_default, $readonly);
	$output .= '	</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>' . html_radio_field('entry_type', 'integer', ($this->entry_type=='integer' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_INTEGER_FIELD . '</td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_INTEGER_RANGE;
	$output .= '<br />' . html_pull_down_menu('integer_range', gen_build_pull_down($integer_lengths), $this->integer_range, $disabled);
	$output .= '<br />' . INV_LABEL_DEFAULT_TEXT_VALUE . html_input_field('integer_default', $this->integer_default, $readonly . 'size="16"');
	$output .= '	</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr class="ui-widget-content">' . chr(10);
	$output .= '	<td>' . html_radio_field('entry_type', 'decimal', ($this->entry_type=='decimal' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_DECIMAL_FIELD . '</td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_DECIMAL_RANGE;
	$output .= html_pull_down_menu('decimal_range', gen_build_pull_down($decimal_lengths), $this->decimal_range, $disabled);
	$output .= '<br />' . INV_LABEL_DEFAULT_DISPLAY_VALUE . html_input_field('decimal_display', ($this->decimal_display ? $this->decimal_display : DEFAULT_REAL_DISPLAY_FORMAT), $readonly . 'size="6" maxlength="5"');
	$output .= '<br />' . INV_LABEL_DEFAULT_TEXT_VALUE . html_input_field('decimal_default', $this->decimal_default, $readonly . 'size="16"');
	$output .= '	</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '	<td>';
	$output .= html_radio_field('entry_type', 'multi_check_box', ($this->entry_type=='multi_check_box' ? true : false),'', $disabled) . '&nbsp;' . INV_LABEL_MULTI_SELECT_FIELD . '<br />';	
	$output .= html_radio_field('entry_type', 'drop_down', ($this->entry_type=='drop_down' ? true : false),'', $disabled)             . '&nbsp;' . INV_LABEL_DROP_DOWN_FIELD . '<br />';
	$output .= html_radio_field('entry_type', 'radio',     ($this->entry_type=='radio'     ? true : false),'', $disabled)             . '&nbsp;' . INV_LABEL_RADIO_FIELD;
	$output .= '	</td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_CHOICES . '<br />' . html_textarea_field('radio_default', 35, 6, $this->radio_default, $readonly) . '<br />'; 
	$output .= INV_LABEL_RADIO_EXPLANATION . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr class="ui-widget-content">' . chr(10);
	$output .= '	<td>' . html_radio_field('entry_type', 'check_box', ($this->entry_type=='check_box' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_CHECK_BOX_FIELD . '</td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_DEFAULT_TEXT_VALUE . html_pull_down_menu('check_box_range', gen_build_pull_down($check_box_choices), $this->check_box_range, $disabled) . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '   <td>';
	$output .= html_radio_field('entry_type', 'date',       ($this->entry_type=='date'       ? true : false), '', $disabled) . '&nbsp;' . TEXT_DATE . '<br />';  
	$output .= html_radio_field('entry_type', 'time',       ($this->entry_type=='time'       ? true : false), '', $disabled) . '&nbsp;' . TEXT_TIME . '<br />';  
	$output .= html_radio_field('entry_type', 'date_time',  ($this->entry_type=='date_time'  ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_DATE_TIME_FIELD . '<br />';  
	$output .= html_radio_field('entry_type', 'time_stamp', ($this->entry_type=='time_stamp' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_TIME_STAMP_FIELD ;
	$output .= '   </td>' . chr(10);
	$output .= '	<td>' . INV_LABEL_TIME_STAMP_VALUE . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
	$output .= '</table>' . chr(10);
    return $output;
  }
  
  /*
   * returns a array to the caller with the info what to store in the table contact / inventory
   */
  
  public function what_to_save(){
  	global $db, $currencies;
  	$sql_data_array = array();
    $xtra_db_fields = $db->Execute("select field_name, entry_type, params 
        from " . TABLE_EXTRA_FIELDS . " where module_id='$this->module'");
    while (!$xtra_db_fields->EOF) {
    	if ($xtra_db_fields->fields['field_name'] == 'id' )  $xtra_db_fields->MoveNext();
        $field_name = $xtra_db_fields->fields['field_name'];
        if ($xtra_db_fields->fields['entry_type'] == 'multi_check_box') {
            $temp ='';
            $params = unserialize($xtra_db_fields->fields['params']);
            $choices = explode(',',$params['default']);
            while ($choice = array_shift($choices)) {
                $values = explode(':',$choice);
                if(isset($_POST[$field_name.$values[0]])){
                    $temp.= $_POST[$field_name.$values[0]].',';
            }}
            $sql_data_array[$field_name] = $temp;
        }elseif (!isset($_POST[$field_name]) && $xtra_db_fields->fields['entry_type'] == 'check_box') {
            $sql_data_array[$field_name] = '0'; // special case for unchecked check boxes
        }elseif (isset($_POST[$field_name]) && $field_name <> 'id') {
            $sql_data_array[$field_name] = db_prepare_input($_POST[$field_name]);
        }
        if ($xtra_db_fields->fields['entry_type'] == 'date_time') {
            $sql_data_array[$field_name] = ($sql_data_array[$field_name]) ? gen_db_date($sql_data_array[$field_name]) : '';
        }
    	if ($xtra_db_fields->fields['entry_type'] == 'decimal') {
            $sql_data_array[$field_name] = ($sql_data_array[$field_name]) ? $currencies->clean_value($sql_data_array[$field_name]) : '';
        }
        $xtra_db_fields->MoveNext();
    }
    return $sql_data_array;
  }
  
  public function set_fields_to_display($type = null){
  	global $db, $cInfo;
  	$tab_array = array();
	$result = $db->Execute("select fields.tab_id, tabs.tab_name as tab_name, fields.description as description, fields.params as params, fields.group_by, fields.field_name, fields.entry_type from ".TABLE_EXTRA_FIELDS." as fields join ".TABLE_EXTRA_TABS." as tabs on (fields.tab_id = tabs.id) where fields.module_id='".$this->module."' order by tabs.sort_order asc, fields.group_by asc, fields.sort_order asc");
  	while (!$result->EOF) {
  		$tab_id = $result->fields['tab_id'];
  		if (!in_array($tab_id, $tab_array)){
  			if (!empty($tab_array)){
  				$this->extra_tab_html .= '  </table>';
	  			$this->extra_tab_html .= '</div>' . chr(10);
  			}
  			$tab_array[] = $tab_id;
  			$this->extra_tab_html .= '<div title="'. $result->fields['tab_name'] .'" id="tab_' . $tab_id . '">' . chr(10);
	  		$this->extra_tab_html .= '  <table>' . chr(10);
  		}else if($previous_group <> $result->fields['group_by']){
  			$this->extra_tab_html .= '<tr class="ui-widget-header" height="5px"><td colspan="2"></td></tr>' . chr(10);
  		}
	    $xtra_params = unserialize($result->fields['params']);
	    if($this->type_params && !$type == null ){
	    	$temp = explode(':',$xtra_params[$this->type_params]);
	    	while ($value = array_shift($temp)){
	    		if ($value == $type) {
					$this->extra_tab_html .= $this->build_field($result->fields, $cInfo) . chr(10);
				}
			}
	    }else{
	    	$this->extra_tab_html .= $this->build_field($result->fields, $cInfo) . chr(10);
	    }
	    $previous_group = $result->fields['group_by'];
		$result->MoveNext();
	}
	$this->extra_tab_html .= '  </table>';
	$this->extra_tab_html .= '</div>' . chr(10); 
  }
  
  /**
   * this function returns the fields that shouldn't be displayed for that type.
   * allowing us to remove the field for objects. 
   * @param string $type
   */
  
  public function unwanted_fields($type = null){
  	global $db;
  	$values = array();
  	if($this->type_params == '' && $type == null ) return $values;
	$result = $db->Execute("SELECT params, field_name FROM ".TABLE_EXTRA_FIELDS." WHERE module_id='".$this->module."'");
	while (!$result->EOF) {
		$xtra_params = unserialize($result->fields['params']);
  		$temp = explode(':',$xtra_params[$this->type_params]);
	    if(!in_array($type,$temp)) $values [] = $result->fields['field_name'];
  		$result->MoveNext();
	}
	return $values;
  }
  
  	function get_tabs($module = '') {
    	global $db;
    	$tab_array = array(0 => TEXT_SYSTEM);
		if (!$module) return $tab_array;
    	$result = $db->Execute("select id, tab_name from " . TABLE_EXTRA_TABS . " where module_id = '" . $module . "' order by tab_name");
    	while (!$result->EOF) {
      		$tab_array[$result->fields['id']] = $result->fields['tab_name'];
      		$result->MoveNext();
    	}
    	return $tab_array;
  	}
  
  	/**
   	 * 
   	 * this will return the html output for a field.
   	 * @param string $param_array
   	 * @param object $cInfo
   	 */  
  	function build_field($param_array, $cInfo) {
		$output = '<tr><td>' . $param_array['description'] . '</td>';
		$params = unserialize($param_array['params']);
		switch ($params['type']) {
			case 'text':
			case 'html':
				if ($params['length'] < 256) {
					$length = ($params['length'] > 120) ? 'size="120"' : ('size="' . $params['length'] . '"');
					$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], $length) . '</td></tr>';
				} else {
					$output .= '<td>' . html_textarea_field($param_array['field_name'], DEFAULT_INPUT_FIELD_LENGTH, 4, $cInfo->$param_array['field_name']) . '</td></tr>';
				}
				break;
			case 'hyperlink':
			case 'image_link':
			case 'inventory_link':
				$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], 'size="' . DEFAULT_INPUT_FIELD_LENGTH . '"') . '</td></tr>';
				break;
			case 'integer':
			case 'decimal':
				$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], 'size="13" maxlength="12" style="text-align:right"') . '</td></tr>';
				break;
			case 'date':
			case 'time':
			case 'date_time':
				$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], 'size="21" maxlength="20"') . '</td></tr>';
				break;
			case 'drop_down':
			case 'enum':
				$choices = explode(',',$params['default']);
				$pull_down_selection = array();
				$default_selection = '';
				while ($choice = array_shift($choices)) {
					$values = explode(':',$choice);
					$pull_down_selection[] = array('id' => $values[0], 'text' => $values[1]);
					if ($cInfo->$param_array['field_name'] == $values[0]) $default_selection = $values[0];
				}
				$output .= '<td>' . html_pull_down_menu($param_array['field_name'], $pull_down_selection, $default_selection) . '</td></tr>';
				break;
			case 'radio':
				$output .= '<td>';
				$choices = explode(',',$params['default']);
				while ($choice = array_shift($choices)) {
					$values = explode(':',$choice);
					$output .= html_radio_field($param_array['field_name'], $values[0], ($cInfo->$param_array['field_name']==$values[0]) ? true : false);
					$output .= '<label for="' . $param_array['field_name']. '_' . $values[0] . '"> ' . $values[1] . '</label>';
				}
				$output .= '</td></tr>';
				break;
			case 'multi_check_box':	
				$output  .= '<td>';
				$output  .= '<table frame="border"><tr>';
				$choices  = explode(',',$params['default']);
				$selected = explode(',',$cInfo->$param_array['field_name']);
				$i = 1;
				while ($choice = array_shift($choices)) {
					$values = explode(':', $choice);
					$output .= '<td>';
					$output .= html_checkbox_field($param_array['field_name'] . $values[0] , $values[0], in_array($values[0], $selected) ? true : false);
					$output .= '<label for="' . $param_array['field_name'] . $values[0] . '"> ' . $values[1] . '</label>';
					$output .= '</td>';
					if ($i == 4){
						$output .= '</tr><tr>';
						$i=0;
					}
					$i++;
				}
				$output .= '</tr></table>';
				$output .= '</td></tr>';
				break;	
			case 'check_box':
				$output .= '<td>' . html_checkbox_field($param_array['field_name'], '1', ($cInfo->$param_array['field_name']==1) ? true : false) . '</td></tr>';
				break;
			case 'time_stamp':
			default:
				$output = '';
		}
		return $output;
  	}
  
}
?>