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
//  Path: /includes/classes/fields.php
//
namespace core\classes;
class fields {
	public  $help_path;
	public  $title;
	public  $current_module;
	public  $db_table;
	public  $type_desc;
	public  $type_array     = array();
    public  $type_params;
    public  $extra_buttons;
	public  $extra_tab_html;

	public function __construct($sync = true, $type = null){
	  	$this->security_id = \core\classes\user::security_level(SECURITY_ID_CONFIGURATION);
		require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
	  	foreach ($_REQUEST as $key => $value) $this->$key = $value;
	  	$this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
	  	$this->type = $type;
		if ($sync) $this->sync_fields($this->current_module, $this->db_table);
	}

	function btn_save($id = '') {
	  	global $admin;
	  	\core\classes\user::validate_security($this->security_id, 2); // security check
	    // clean out all non-allowed values and then check if we have a empty string
		$this->field_name   = preg_replace("[^A-Za-z0-9_]", "", $this->field_name);
		if ($this->field_name == '') throw new \core\classes\userException(EXTRA_ERROR_FIELD_BLANK);
		// check if the field name belongs to one of the mysql reserved names
		$reserved_names = array('select', 'delete', 'insert', 'update', 'to', 'from', 'where', 'and', 'or',
			'alter', 'table', 'add', 'change', 'in', 'order', 'set', 'inner');
		if (in_array($this->field_name, $reserved_names)) throw new \core\classes\userException(EXTRA_FIELD_RESERVED_WORD);
		// if the id is empty then check for duplicate field names
		if($this->id == ''){
		   $result = $admin->DataBase->query("SELECT id FROM ".TABLE_EXTRA_FIELDS." WHERE module_id='{$this->current_module}' AND field_name='{$this->field_name}'");
		   if ($result->fetch(\PDO::FETCH_NUM) > 0 && $this->id =='') throw new \core\classes\userException(EXTRA_FIELD_ERROR_DUPLICATE);
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
				$values['entry_type'] = "varchar({$params['length']})";
				$values['entry_params'] = " default '{$params['default']}'";
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
			$values['entry_params'] = " default '{$params['default']}'";
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
			$values['entry_params'] = " default '{$params['default']}'";
			break;
		  case 'decimal':
			$params['select']  = db_prepare_input($_POST['decimal_range']);
			$params['display'] = db_prepare_input($_POST['decimal_display']);
			$params['default'] = $admin->currencies->clean_value(db_prepare_input($_POST['decimal_default']));
			switch ($params['select']) {
				case "0":
					$values['entry_type'] = "float({$params['display']})";
	                break;
	            case "1":
	            	$values['entry_type'] = 'double';
	                break;
	            case "2":
	            	$values['entry_type'] = "decimal({$params['display']})";
	                break;

			}
			$values['entry_params'] = " default '{$params['default']}'";
			break;
		  case 'drop_down':
		  case 'radio':
			$params['default'] = db_prepare_input($_POST['radio_default']);
			$choices = explode(',',$params['default']);
			$max_choice_size = 0;
			while ($choice = array_shift($choices)) {
				$a_choice = explode(':',$choice);
				if ($a_choice[2] == 1) $values['entry_params'] = " default '{$a_choice[0]}'";
				if (strlen($a_choice[0]) > $max_choice_size) $max_choice_size = strlen($a_choice[0]);
			}
			$values['entry_type'] = "char({$max_choice_size})";
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
			$values['entry_params'] = " default '{$params['select']}'";
			break;
		  case 'time_stamp':
			$values['entry_type'] = 'timestamp';
			break;
		  default:
		}
		$sql_data_array = array(
		  'module_id'   => $this->current_module,
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
			  $result = $admin->DataBase->query("SELECT field_name FROM " . TABLE_EXTRA_FIELDS . " WHERE id = {$this->id}" );
			  if (isset($values['entry_type']) || $this->field_name <> $result['field_name']) {
				$sql = "ALTER TABLE {$this->db_table} CHANGE {$result['field_name']} {$this->field_name} {$values['entry_type']} " . (isset($values['entry_params']) ? $values['entry_params'] : '');
				$admin->DataBase->exec($sql);
			  }
		  }
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array, 'update', "id = " . $this->id );
		  gen_add_audit_log($this->current_module .' '. TEXT_CUSTOM_FIELDS . ' - ' . TEXT_UPDATE, $this->id  . ' - ' . $this->field_name);
		} else {
		  $sql = "ALTER TABLE {$this->db_table} ADD COLUMN {$this->field_name} {$values['entry_type']} " . (isset($values['entry_params']) ? $values['entry_params'] : '');
		  $admin->DataBase->query($sql);
		  db_perform(TABLE_EXTRA_FIELDS, $sql_data_array, 'insert');
		  $this->id  = \core\classes\PDO::lastInsertId('id');
		  gen_add_audit_log($this->current_module .' '. TEXT_CUSTOM_FIELDS . ' - ' . TEXT_NEW, $this->id  . ' - ' . $this->field_name);
		}
		return true;
	}

	function btn_delete($id = 0) {
	  	global $admin;
	  	\core\classes\user::validate_security($this->security_id, 4); // security check
		$result = $admin->DataBase->query("SELECT * FROM ".TABLE_EXTRA_FIELDS." WHERE id=$id");
		foreach ($result as $key => $value) $this->$key = $value;
		if ($this->tab_id == '0') throw new \core\classes\userException (INV_CANNOT_DELETE_SYSTEM); // don't allow deletion of system fields
		$admin->DataBase->exec("DELETE FROM ".TABLE_EXTRA_FIELDS." WHERE id=$this->id");
		$admin->DataBase->query("ALTER TABLE $this->db_table DROP COLUMN $this->field_name");
		gen_add_audit_log ($this->current_module.' '. TEXT_CUSTOM_FIELDS . ' - ' . TEXT_DELETE, "$id - $this->field_name");
		return true;
	}

  	function build_main_html() {
  		global $admin;
		$tab_array = $this->get_tabs($this->current_module);
    	$content = array();
		$content['thead'] = array(
	  		'value' => array(TEXT_DESCRIPTION, TEXT_FIELD_NAME, TEXT_TAB_TITLE, TEXT_TYPE, $this->type_desc, TEXT_SORT_ORDER, TEXT_GROUP, TEXT_ACTION),
	  		'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
		);
		$field_list = array('id', 'field_name', 'entry_type', 'description', 'tab_id', 'params', 'sort_order', 'group_by');
    	$sql = $admin->DataBase->prepare("SELECT ".implode(', ', $field_list)." FROM ".TABLE_EXTRA_FIELDS." WHERE module_id='{$this->current_module}' ORDER BY group_by, sort_order");
    	$sql->execute();
    	$rowCnt = 0;
    	while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
	  		$params  = unserialize($result['params']);
			$actions = '';
			if ($this->security_id > 1)									  $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="loadPopUp(\'fields_edit\', ' . $result['id'] . ')"') . chr(10);
			if ($result['tab_id'] <> '0' && $this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . EXTRA_FIELD_DELETE_INTRO . '\')) subjectDelete(\'fields\', ' . $result['id'] . ')"') . chr(10);
			$content['tbody'][$rowCnt] = array(
			    array('value' => htmlspecialchars($result['description']),
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => $result['field_name'],
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => $tab_array[$result['tab_id']],
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => $result['entry_type'],
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => isset($params[$this->type_params])?$params[$this->type_params]:'',
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => $result['sort_order'],
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => $result['group_by'],
					  'params'=> "style='cursor:pointer' onclick='loadPopUp(\"fields_edit\",\"{$result['id']}\")'"),
				array('value' => $actions,
					  'params'=> 'align="right"'),
			);
			$rowCnt++;
    	}
    	return html_datatable('field_table', $content);
  	}

  	function build_form_html($action, $id = '') {
    	global $admin, $integer_lengths, $decimal_lengths, $check_box_choices;
		if ($action <> 'new') {
	   		$result = $admin->DataBase->query("SELECT * FROM ".TABLE_EXTRA_FIELDS." WHERE id='$this->id'");
	   		$params = unserialize($result['params']);
	   		foreach ($result as $key => $value) $this->$key = $value;
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
		  	        $this->decimal_default = number_format($this->default, $this->display, $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $admin->currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);
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
		$tab_list = gen_build_pull_down($this->get_tabs($this->current_module));
		array_shift($tab_list);
		if ($action == 'new' && sizeof($tab_list) < 1) throw new \core\classes\userException(EXTRA_FIELDS_ERROR_NO_TABS);
	    $choices  =  explode(':',$params[$this->type_params]);
		$disabled = ($this->tab_id !== '0') ? '' : 'disabled="disabled" ';
		$readonly = ($this->tab_id !== '0') ? '' : 'readonly="readonly" ';
		$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
		$output .= '  <thead class="ui-widget-header">' . "\n";
		$output .= '  <tr>' . chr(10);
		$output .= '    <th colspan="2">' . ($action=='new' ? sprintf(TEXT_NEW_ARGS, TEXT_FIELD) : TEXT_SETTINGS) . '</th>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  </thead>' . "\n";
		$output .= '  <tbody class="ui-widget-content">' . "\n";
		$output .= '  <tr>' . chr(10);
		$output .= '	<td>' . TEXT_FIELD_NAME . ':</td>' . chr(10);
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
		$output .= '  <tr>' . chr(10);
		$output .= '	<td>' . TEXT_REQUIRED . '</td>' . chr(10);
		$output .= '	<td>' . html_checkbox_field('required' , true , false,'', '') . '</td>' . chr(10);
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
		$output .= '	<td>' . TEXT_TAB_MEMBER . ': </td>' . chr(10);
		$output .= '	<td>' . html_pull_down_menu('tab_id', $tab_list, $this->tab_id, $disabled) . '</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr class="ui-widget-header">' . chr(10);
		$output .= '	<th colspan="2">' . TEXT_PROPERTIES . '</th>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '	<td>';
		$output .= html_radio_field('entry_type', 'text', ($this->entry_type=='text' ? true : false), '', $disabled) . '&nbsp;' . TEXT_TEXT_FIELD . '<br />';
		$output .= html_radio_field('entry_type', 'html', ($this->entry_type=='html' ? true : false), '', $disabled) . '&nbsp;' . TEXT_HTML_CODE . '</td>' . chr(10);
		$output .= '	<td>' . INV_LABEL_MAX_NUM_CHARS;
		$output .= '<br />' . html_input_field('length', ($this->length ? $this->length : DEFAULT_TEXT_LENGTH), $readonly . 'size="10" maxlength="9"');
		$output .= '<br />' . TEXT_DEFAULT_VALUE . ' :<br />(' . TEXT_FOR_LENGTHS_LESS_THAN_256_CHARACTERS. ')';
		$output .= '<br />' . html_textarea_field('text_default', 35, 6, $this->text_default, $readonly);
		$output .= '	</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr class="ui-widget-content">' . chr(10);
		$output .= '	<td>';
		$output .= html_radio_field('entry_type', 'hyperlink',      ($this->entry_type=='hyperlink'      ? true : false), '', $disabled) . '&nbsp;' . TEXT_HYPER-LINK  . '<br />';
		$output .= html_radio_field('entry_type', 'image_link',     ($this->entry_type=='image_link'     ? true : false), '', $disabled) . '&nbsp;' . TEXT_IMAGE_FILE_NAME . '<br />';
		$output .= html_radio_field('entry_type', 'inventory_link', ($this->entry_type=='inventory_link' ? true : false), '', $disabled) . '&nbsp;' . INV_LABEL_INVENTORY_LINK;
		$output .= '	</td>' . chr(10);
		$output .= '	<td>' . INV_LABEL_FIXED_255_CHARS;
		$output .= '<br />' . TEXT_DEFAULT_VALUE ." :";
		$output .= '<br />' . html_textarea_field('link_default', 35, 3, $this->link_default, $readonly);
		$output .= '	</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '	<td>' . html_radio_field('entry_type', 'integer', ($this->entry_type=='integer' ? true : false), '', $disabled) . '&nbsp;' . TEXT_INTEGER_NUMBER . '</td>' . chr(10);
		$output .= '	<td>' . TEXT_INTEGER_RANGE;
		$output .= '<br />' . html_pull_down_menu('integer_range', gen_build_pull_down($integer_lengths), $this->integer_range, $disabled);
		$output .= '<br />' . TEXT_DEFAULT_VALUE." : " . html_input_field('integer_default', $this->integer_default, $readonly . 'size="16"');
		$output .= '	</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr class="ui-widget-content">' . chr(10);
		$output .= '	<td>' . html_radio_field('entry_type', 'decimal', ($this->entry_type=='decimal' ? true : false), '', $disabled) . '&nbsp;' . TEXT_DECIMAL_NUMBER . '</td>' . chr(10);
		$output .= '	<td>' . TEXT_DECIMAL_RANGE;
		$output .= html_pull_down_menu('decimal_range', gen_build_pull_down($decimal_lengths), $this->decimal_range, $disabled);
		$output .= '<br />' . INV_LABEL_DEFAULT_DISPLAY_VALUE . html_input_field('decimal_display', ($this->decimal_display ? $this->decimal_display : DEFAULT_REAL_DISPLAY_FORMAT), $readonly . 'size="6" maxlength="5"');
		$output .= '<br />' . TEXT_DEFAULT_VALUE." : " . html_input_field('decimal_default', $this->decimal_default, $readonly . 'size="16"');
		$output .= '	</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '	<td>';
		$output .= html_radio_field('entry_type', 'multi_check_box', ($this->entry_type=='multi_check_box' ? true : false),'', $disabled) . '&nbsp;' . TEXT_MULTIPLE_OPTIONS_CHECKBOXES . '<br />';
		$output .= html_radio_field('entry_type', 'drop_down', ($this->entry_type=='drop_down' ? true : false),'', $disabled)             . '&nbsp;' . TEXT_DROPDOWN_LIST . '<br />';
		$output .= html_radio_field('entry_type', 'radio',     ($this->entry_type=='radio'     ? true : false),'', $disabled)             . '&nbsp;' . TEXT_RADIO_BUTTON;
		$output .= '	</td>' . chr(10);
		$output .= '	<td>' . TEXT_ENTER_SELECTION_STRING . '<br />' . html_textarea_field('radio_default', 35, 6, $this->radio_default, $readonly) . '<br />';
		$output .= INV_LABEL_RADIO_EXPLANATION . '</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr class="ui-widget-content">' . chr(10);
		$output .= '	<td>' . html_radio_field('entry_type', 'check_box', ($this->entry_type=='check_box' ? true : false), '', $disabled) . '&nbsp;' . TEXT_CHECK_BOX_FIELD . '</td>' . chr(10);
		$output .= '	<td>' . TEXT_DEFAULT_VALUE. " : " . html_pull_down_menu('check_box_range', gen_build_pull_down($check_box_choices), $this->check_box_range, $disabled) . '</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '   <td>';
		$output .= html_radio_field('entry_type', 'date',       ($this->entry_type=='date'       ? true : false), '', $disabled) . '&nbsp;' . TEXT_DATE . '<br />';
		$output .= html_radio_field('entry_type', 'time',       ($this->entry_type=='time'       ? true : false), '', $disabled) . '&nbsp;' . TEXT_TIME . '<br />';
		$output .= html_radio_field('entry_type', 'date_time',  ($this->entry_type=='date_time'  ? true : false), '', $disabled) . '&nbsp;' . TEXT_DATE_AND_TIME . '<br />';
		$output .= html_radio_field('entry_type', 'time_stamp', ($this->entry_type=='time_stamp' ? true : false), '', $disabled) . '&nbsp;' . TEXT_TIME_STAMP ;
		$output .= '   </td>' . chr(10);
		$output .= '	<td>' . INV_LABEL_TIME_STAMP_VALUE . '</td>' . chr(10);
		$output .= '  </tr>' . chr(10);
		$output .= '  </tbody>' . "\n";
		$output .= '</table>' . chr(10);
	    return $output;
  	}

	/**
	 * returns a array to the caller with the info what to store in the table contact / inventory / assets
	 */
  	public function what_to_save(){
  		global $admin;
  		$sql_data_array = array();
    	$sql = $admin->DataBase->prepare("SELECT field_name, entry_type, params, required, field_name FROM " . TABLE_EXTRA_FIELDS . " WHERE module_id='{$this->current_module}' and field_name NOT IN ('id','last_update','last_journal_date','first_date','creation_date') ");
    	$sql->execute();
    	while ($xtra_db_fields = $sql->fetch(\PDO::FETCH_LAZY)) {
        	$field_name = $xtra_db_fields['field_name'];
        	switch ($xtra_db_fields['entry_type']){
        		case 'multi_check_box':
	            	$temp ='';
	            	$params = unserialize($xtra_db_fields['params']);
	            	$choices = explode(',',$params['default']);
	          	  	while ($choice = array_shift($choices)) {
	                	$values = explode(':',$choice);
	                	if(property_exists($admin->cInfo, $field_name.$values[0]) === true){
	                    	$temp.= $admin->cInfo->$field_name.$values[0].',';
	            	}}
	            	$sql_data_array[$field_name] = $temp;
	            	break;
        		case 'check_box':
            		$sql_data_array[$field_name] = property_exists($admin->cInfo, $field_name) === true ? '1' : '0'; // special case for unchecked check boxes
            		break;
        		case 'date_time':
            		$sql_data_array[$field_name] = property_exists($admin->cInfo, $field_name) != false ? \core\classes\DateTime::db_date_format($admin->cInfo->$field_name) : '';
            		break;
        		case 'decimal':
            		$sql_data_array[$field_name] = property_exists($admin->cInfo, $field_name) != false ? $admin->currencies->clean_value($admin->cInfo->$field_name) : '';
            		break;
        		default:
	        		if (property_exists($admin->cInfo, $field_name) != false ) $sql_data_array[$field_name] = db_prepare_input($admin->cInfo->$field_name);
    		}
    		if (db_prepare_input($sql_data_array[$field_name], $xtra_db_fields['required'] == '1') === false) throw new \core\classes\userException(sprintf(TEXT_FIELD_IS_REQUIRED_BUT_HAS_BEEN_LEFT_BLANK_ARGS, $field_name));
    	}
    	return $sql_data_array;
  	}

  	/**
  	 * displays form fields.
  	 */
  	public function display($fields){
  		global $admin;
  		$tab_array = array();
		$sql = $admin->DataBase->prepare("SELECT fields.tab_id, tabs.tab_name as tab_name, fields.description as description, fields.params as params, fields.group_by, fields.field_name, fields.entry_type FROM ".TABLE_EXTRA_FIELDS." AS fields JOIN ".TABLE_EXTRA_TABS." AS tabs ON (fields.tab_id = tabs.id) WHERE fields.module_id='{$this->current_module}' ORDER BY tabs.sort_order ASC, fields.group_by ASC, fields.sort_order ASC");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
  			if (!in_array($result['tab_id'], $tab_array)){
  				if (!empty($tab_array)){
  					$this->extra_tab_html .= '  </table>';
	  				$this->extra_tab_html .= '</div>' . chr(10);
  				}
	  			$tab_array[] = $result['tab_id'];
	  			$this->extra_tab_html .= "<div title='{$result['tab_name']}' >" . chr(10);
		  		$this->extra_tab_html .= '  <table>' . chr(10);
	  		}else if($previous_group <> $result['group_by']){
	  			$this->extra_tab_html .= '<tr class="ui-widget-header" height="5px"><td colspan="2"></td></tr>' . chr(10);
	  		}
		    $xtra_params = unserialize($result['params']);
		    if($this->type_params && !$this->type == null ){
		    	$temp = explode(':',$xtra_params[$this->type_params]);
		    	while ($value = array_shift($temp)){
		    		if ($value == $this->type) {
						$this->extra_tab_html .= $this->build_field($result, $fields) . chr(10);
					}
				}
		    }else{
		    	$this->extra_tab_html .= $this->build_field($result, $fields) . chr(10);
		    }
		    $previous_group = $result['group_by'];
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
	  	global $admin;
	  	$values = array();
	  	if($this->type_params == '' && $type == null ) return $values;
		$sql = $admin->DataBase->prepare("SELECT params, field_name FROM ".TABLE_EXTRA_FIELDS." WHERE module_id='{$this->current_module}'");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
			$xtra_params = unserialize($result['params']);
	  		$temp = explode(':',$xtra_params[$this->type_params]);
		    if(!in_array($type,$temp)) $values [] = $result['field_name'];
		}
		return $values;
	}

  	function get_tabs($module = '') {
    	global $admin;
    	$tab_array = array(0 => TEXT_SYSTEM);
		if (!$module) return $tab_array;
    	$sql = $admin->DataBase->query("select id, tab_name from " . TABLE_EXTRA_TABS . " where module_id = '{$module}' order by tab_name");
    	$sql->execute();
    	while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
      		$tab_array[$result['id']] = $result['tab_name'];
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

  	/**
  	 * Syncronizes the fields in the module db with the field parameters
  	 * (usually only needed for first entry to inventory field builder)
  	 */
  	static function sync_fields ($module = '', $db_table = '') {
  		global $admin;
  		if (!$module || !$db_table) throw new \core\classes\userException('Sync fields called without all necessary parameters!');
  		// First check to see if inventory field table is synced with actual inventory table
  		$sql = $admin->DataBase->prepare("DESCRIBE " . $db_table);
  		$sql->execute();
  		while ($column = $sql->fetch(\PDO::FETCH_LAZY)){
  			$table_fields[] = $column['Field'];
  		}
  		sort($table_fields);
  		$sql = $admin->DataBase->prepare("SELECT field_name FROM " . TABLE_EXTRA_FIELDS . " WHERE module_id = '$module' ORDER BY field_name");
  		$sql->execute();
  		while ($column = $sql->fetch(\PDO::FETCH_LAZY)){
  			$field_list[] = $column['field_name'];
  		}
  		$needs_sync = false;
  		foreach ($table_fields as $key => $value) {
  			if ($value <> $field_list[$key]) {
  				$needs_sync = true;
  				break;
  			}
  		}
  		if ($needs_sync) {
  			if (is_array($field_list)) {
  				$add_list = array_diff($table_fields, $field_list);
  			} else {
  				$add_list = $table_fields;
  			}
  			$delete_list = '';
  			if (is_array($field_list)) $delete_list = array_diff($field_list, $table_fields);
  			if (isset($add_list)) {
  				foreach ($add_list as $value) { // find the field attributes and copy to field list table
  					$sql = $admin->DataBase->prepare("SHOW fields FROM $db_table like '$value'");
  					$sql->execute();
  					$myrow = $sql->fetch(\PDO::FETCH_LAZY);
  					$Params = array('default' => $myrow['Default']);
  					$type = $myrow['Type'];
  					if (strpos($type,'(') === false) {
  						$data_type = strtolower($type);
  					} else {
  						$data_type = strtolower(substr($type,0,strpos($type,'(')));
  					}
  					switch ($data_type) {
  						case 'date':      $Params['type'] = 'date'; 		break;
  						case 'time':      $Params['type'] = 'time'; 		break;
  						case 'datetime':  $Params['type'] = 'date_time'; 	break;
  						case 'timestamp': $Params['type'] = 'time_stamp';	break;
  						case 'year':      $Params['type'] = 'date'; 		break;

  						case 'bigint':
  						case 'int':
  						case 'mediumint':
  						case 'smallint':
  						case 'tinyint':
  							$Params['type'] = 'integer';
  							if ($data_type=='tinyint')   $Params['default'] = '0';
  							if ($data_type=='smallint')  $Params['default'] = '1';
  							if ($data_type=='mediumint') $Params['default'] = '2';
  							if ($data_type=='int')       $Params['default'] = '3';
  							if ($data_type=='bigint')    $Params['default'] = '4';
  							break;
  						case 'decimal':
  						case 'double':
  						case 'float':
  							$Params['type'] = 'decimal';
  							if ($data_type=='float')  	$Params['default'] = '0';
  							if ($data_type=='double') 	$Params['default'] = '1';
  							break;
  						case 'tinyblob':
  						case 'tinytext':
  						case 'char':
  						case 'varchar':
  						case 'longblob':
  						case 'longtext':
  						case 'mediumblob':
  						case 'mediumtext':
  						case 'blob':
  						case 'text':
  							$Params['type'] = 'text';
  							if ($data_type=='varchar' OR $data_type=='char') { // find the actual db length
  								$Length = trim(substr($type, strpos($type,'(')+1, strpos($type,')')-strpos($type,'(')-1));
  								$Params['length'] = $Length;
  							}
  							if ($data_type=='tinytext'   OR $data_type=='tinyblob')   $Params['length'] = '255';
  							if ($data_type=='text'       OR $data_type=='blob')       $Params['length'] = '65,535';
  							if ($data_type=='mediumtext' OR $data_type=='mediumblob') $Params['length'] = '16,777,215';
  							if ($data_type=='longtext'   OR $data_type=='longblob')   $Params['length'] = '4,294,967,295';
  							break;
  						case 'enum':
  						case 'set':
  							$Params['type'] = 'drop_down';
  							$temp = trim(substr($type, strpos($type,'(')+1, strpos($type,')')-strpos($type,'(')-1));
  							$selections = explode(',', $temp);
  							$defaults = '';
  							foreach($selections as $selection) {
  								$selection = preg_replace("/'/", '', $selection);
  								if ($myrow['Default'] == $selection) $set = 1; else $set = 0;
  								$defaults .= $selection . ':' . $selection .':' . $set . ',';
  							}
  							$defaults = substr($defaults, 0, -1);
  							$Params['default'] = $defaults;
  							break;
  						default:
  					}
  					$temp = $admin->DataBase->exec("INSERT INTO " . TABLE_EXTRA_FIELDS . " SET
  					  module_id = '$module',
  					  tab_id = 0,
  					  entry_type = '{$Params['type']}',
  					  field_name = '$value',
  					  description = '$value',
  					  params = '" . serialize($Params) . "'");  // tab_id = 0 for System category
  				}
  			}
  			if ($delete_list) {
  				foreach ($delete_list as $value) {
  					$temp = $admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_FIELDS . " WHERE module_id='$module' AND field_name='$value'");
  				}
  			}
  		}
  		return;
  	}

}
?>