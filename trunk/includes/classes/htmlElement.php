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
//  Path: /includes/classes/htmlElement.php
//
namespace core\classes;
class htmlElement {
	
	function html_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = false) {
		global $request_type, $http_domain, $https_domain;
		if ($page == '') throw new \core\classes\userException('Unable to determine the page link!<br />Function used:<br />html_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')');
		if ($connection == 'SSL') {
			$link = DIR_WS_FULL_PATH;
		} else {
			$link = HTTP_SERVER . DIR_WS_ADMIN;
		}
		if (!strstr($page, '.php')) $page .= '.php';
		if ($parameters == '') {
			$link = $link . $page;
			$separator = '?';
		} else {
			$link = $link . $page . '?' . $parameters;
			$separator = '&amp;';
		}
		while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
		// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
		if ( ($add_session_id == true) && (session_status() == PHP_SESSION_ACTIVE) ) {
			if (defined('SID') && gen_not_null(SID)) {
				$sid = SID;
			} elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL_ADMIN == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
				if ($http_domain != $https_domain) {
					$sid = session_name() . '=' . session_id();
				}
			}
		}
		if (isset($sid)) $link .= $separator . $sid;
		return $link;
	}
	
	/**
	 * image
	 * @param string $src
	 * @param string $alt
	 * @param number $width
	 * @param number $height
	 * @param string $params
	 * @return string
	 */
	static function image($src, $alt = '', $width = 0, $height = 0, $params = '') {
		$image = "<img src='{$src}' alt='{$alt}' style='border:none'";
		if (gen_not_null($alt))    $image .= " title='{$alt}'";
		if ($width > 0)            $image .= " width='{$width}'";
		if ($height > 0)           $image .= " height='{$height}'";
		if (gen_not_null($params)) $image .= ' ' . $params;
		$image .= ' />';
		return $image;
	}
	
	function html_icon($image, $alt = '', $size = 'small', $params = NULL, $width = NULL, $height = NULL, $id = NULL) {
		switch ($size) {
			default:
			case 'small':  $subdir = '16x16/'; $height='16'; break;
			case 'medium': $subdir = '22x22/'; $height='22'; break;
			case 'large':  $subdir = '32x32/'; $height='32'; break;
			case 'svg' :   $subdir = 'scalable/';            break;
		}
		$image_html = '<img src="' . DIR_WS_ICONS . $subdir . $image . '" alt="' . $alt . '" class="imgIcon"';
		if (gen_not_null($alt))    $image_html .= ' title="'  . $alt    . '"';
		if (gen_not_null($id))     $image_html .= ' id="'     . $id     . '"';
		if ($width > 0)            $image_html .= ' width="'  . $width  . '"';
		if ($height > 0)           $image_html .= ' height="' . $height . '"';
		if (gen_not_null($params)) $image_html .= ' ' . $params;
		$image_html .= ' />';
		return $image_html;
	}
	
	function html_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = true) {
		$form = "<form name='{$name}' id='{$name}' action='";
		if (gen_not_null($parameters)) {
			$form .= html_href_link($action, $parameters, (($usessl) ? 'SSL' : 'NONSSL'));
		} else {
			$form .= html_href_link($action, '', (($usessl) ? 'SSL' : 'NONSSL'));
		}
		$form .= "' method='{$method}'";
		if (gen_not_null($params)) $form .= ' ' . $params;
		$form .= ">";
		return $form;
	}
	/**
	 * easyui textbox
	 * @param unknown $name
	 * @param string $label
	 * @param string $parameters
	 * @param string $required
	 * @param string $value
	 * @return string
	 */
	static function textbox($name, $label, $parameters = '', $value = '', $required = false) {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input class='easyui-textbox' name='{$name}' label='{$label}: ' labelPosition='before'";
		if ($id)                       	$field .= " id='{$id}'";
		if (gen_not_null($value))      	$field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
		if ($required == true) 			$field .= ' required="required"';
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		$field .= ' />';
		return $field;
	}
	
	/**
	 * this is a hiddenfield.
	 * @param string $id
	 * @param string $value
	 * @param string $parameters
	 */
	static function hidden($name, $value = '', $parameters = '') {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input name='{$name}' class='easyui-textbox' type='hidden'";
		if ($id)                       	$field .= " id='{$id}'";
		if (gen_not_null($value))      	$field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		$field .= ' />';
		return $field;
	}
	 
	function html_calendar_field($name, $label, $value = '', $parameters = '') {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input class='easyui-datebox' name='{$name}' label='{$label}: ' labelPosition='before' data-options='currentText:\"".TEXT_TODAY."\",closeText:\"".TEXT_CLOSE."\",formatter:formatDate' style='width:180px;height:26px;'";
		if ($id)                       	$field .= " id='{$id}'";
		
		if (gen_not_null($value))      	$field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
		if ($required)					$required = ",required:true";
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		$field .= " >";
		return $field;
	}
	
	static function currency($name, $label, $value, $parameters, $currency_code = DEFAULT_CURRENCY, $required = NULL){//@todo test and implement
		global $admin;
		$temp = $admin->currencies->currencies[$currency_code];
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}//@todo maybe remove currency symbool or strip it when field is enterd and also with thousand symbol  
		$field = "<input ";
		if ($id)						$field .= " id='$id' ";
		if (gen_not_null($parameters))	$field .= " $parameters ";
		if ($required)					$required = ",required:true";
		$field .= " class='easyui-numberbox' onfocus='focusCurrency(this)' label='{$label}: ' labelPosition='before' data-options=\"precision:{$temp['decimal_places']},groupSeparator:'{$temp['thousands_point']}',decimalSeparator:'{$temp['decimal_point']}',prefix:'".utf8_decode ($temp['symbol_left'])."', suffix:'{$temp['symbol_right']}', value:'$value' $required\">";
		return $field;
	}
	
	function html_number_field($name, $label, $value, $parameters, $required = NULL){//@todo test and implement
		global $admin;
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input ";
		if ($id)						$field .= " id='$id' ";
		if (gen_not_null($parameters))	$field .= " $parameters ";
		if ($required)					$required = ",required:true";
		$field .=  "class='easyui-numberbox' label='{$label}: ' labelPosition='before' data-options=\"precision:{$temp[DEFAULT_CURRENCY]->decimal_places},groupSeparator:'{$temp[DEFAULT_CURRENCY]->thousands_point}',decimalSeparator:'{$temp[DEFAULT_CURRENCY]->decimal_point}', value:'$value' $required\" />";
		return $field;
	}
	/**
	 * create a date and time field
	 * @param $name
	 * @param $value
	 * @param $required bool
	 */
	 
	static function dateAndTime($name, $label, $value = null, $required = false){//@todo test and implement date format needs to be right
		if ($value == null ){
			$date = new \core\classes\DateTime();
			$value = $date->format('Y-m-d H:i:s');
		}
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input class='easyui-datetimebox' name='$name' label='{$label}: ' labelPosition='before' value='{$value}'";
		if ($id)                    $field .= " id='{$id}'";
		if ($required == true)		$field .= " required='required' ";
		$field .= " />";
		return $field;
	}
	
	/**
	 * create a date field
	 * @param $name
	 * @param $value
	 * @param $required bool
	 */
	
	static function date($name, $label, $value = '', $required = false){//@todo test and implement date format needs to be right
		if ($value == null ){
			$date = new \core\classes\DateTime();
			$value = $date->format('Y-m-d');
		}
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input class='easyui-datebox' name='$name' label='{$label}: ' labelPosition='before' value='{$value}'";
		if ($id)                    $field .= " id='{$id}'";
		if ($required == true)		$field .= " required='required' ";
		$field .= " />";
		return $field;
	}
	
	static function password($name, $label, $value = null, $required = false, $parameters = null) {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input name='{$name}' class='easyui-passwordbox' iconWidth='28' maxlength='40' label='{$label}: ' labelPosition='before'" ;
		if ($id)                    $field .= " id='{$id}'";
		if (gen_not_null($value))   $field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		$field .= ' />';
		return $field;
	}
	
	function html_file_field($name, $required = false) {
		return html_input_field($name, '', '', $required, 'file', false);
	}
	
	static function submit($name, $label, $onclick) {
		$onclick = str_replace("'", '"', $onclick);
//		return "<a href='javascript:void(0)' class='easyui-linkbutton' onclick='$onclick'>$label</a>";
//		return html_input_field($name, $value, 'style="cursor:pointer" ' . $parameters, false, 'submit', false);
		$field = "<input type='submit' name='{$name}' value='$label' ";
		if ($id)                       	$field .= " id='{$id}'";
		if (gen_not_null($value))      	$field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		return $field .= ' />';
	}
	
	function html_button_field($name, $value, $parameters = '') {
		return '<a href="#" id="'.$name.'" class="ui-state-default ui-corner-all" '.$parameters.'>'.$value.'</a>';
	}
	
	/**
	 * easyui ready
	 * @param unknown $name
	 * @param unknown $label
	 * @param string $value
	 * @param string $checked
	 * @param string $parameters
	 * @return string
	 */
	static function checkbox($name, $label, $value = '', $checked = false, $parameters = '') {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<label class='textbox-label textbox-label-before' style='text-align: left; height: 15px; line-height: 15px;' for='{$id}'>{$label} :</label><input type='checkbox' name='{$name}' id='{$id}'";
		if (gen_not_null($value)) 	$field .= " value='{$value}'";
		if (($checked == true) || (gen_not_null($value) && gen_not_null($compare) && ($value == $compare))) {
			$field .= ' checked="checked"';
		}
		if (gen_not_null($parameters)) $field .= " {$parameters} ";
		$field .= ' />';
		return $field;
	}
	
	/**
	 * easyui ready
	 * @param unknown $name
	 * @param unknown $label
	 * @param string $value
	 * @param string $checked
	 * @param string $parameters
	 * @return string
	 */
	static function switchbutton($name, $label, $value = '', $checked = false, $parameters = '', $text_true = TEXT_ACTIVE, $text_false = TEXT_INACTIVE) {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input class='easyui-switchbutton' name='{$name} data-options=\"onText:'{$text_true}',offText:'{$text_false}'\"";
		if ($id)                    $field .= " id='{$id}'";
		if (gen_not_null($value)) 	$field .= " value='{$value}'";
		if (($checked == true) || (gen_not_null($value) && gen_not_null($compare) && ($value == $compare))) {
			$field .= ' checked="checked"';
		}
		if (gen_not_null($parameters)) $field .= " {$parameters} ";
		$field .= ' />';
		return $field;
	}
	
	
	function html_radio_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
		$selection  = '<input type="radio" name="' . $name . '" id="' . $name . '_' . $value . '"';
		$selection .= ' value="' . $value . '"';
		if (($checked == true) || (gen_not_null($value) && gen_not_null($compare) && ($value == $compare)) ) {
			$selection .= ' checked="checked"';
		}
		if (gen_not_null($parameters)) $selection .= ' ' . $parameters;
		$selection .= ' />';
		return $selection;
	}
	
	static function textarea($name, $label, $text = '', $parameters = '') {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input class='easyui-textbox' name='{$name}' label='{$label}: ' labelPosition='before' multiline='true' ";
		if ($id)                    $field .= " id='{$id}'";
		if (gen_not_null($value))	$field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
		if ($required == true)		$field .= " required='required' ";
		if ($parameters) 			$field .= ' ' . $parameters;
		$field .= " />";
		return $field;
	}
	
	/**
	 * easyui combobox @todo maybe change this to the combogrid and then a default dropdown box to combobox
	 * @param unknown $name
	 * @param unknown $label
	 * @param unknown $values
	 * @param string $parameters
	 * @param string $required
	 * @param string $default
	 * @return string
	 */
	static function combobox($name, $label, $values, $default = '', $parameters = '', $required = false, $limitToList = true) {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		if (gen_not_null($label))  $label = "label='{$label}: '"; 
		$field = "<select class='easyui-combobox' name='{$name}' {$label} labelPosition='before' style='width:70%;' data-options='limitToList:{$limitToList}'";
		if ($id)                   	 	$field .= " id='{$id}'";
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		if ($required)					$field .= ' required="required" ';
		$field .= '>';
		foreach ((array) $values as $key => $choice){
			$selected = '';
			if (isset($choice['id'])) {
				if (is_array($default) && in_array($choice['id'], $default) ) $selected = ' selected ';
				if ($default == $choice['id'] ) $selected = ' selected ';
				$field .= "<option value='{$choice['id']}'$selected>" . htmlspecialchars($choice['text']) . '</option>';
			}else{
				if (is_array($default) && in_array($key, $default) ) $selected = ' selected ';
				if ($default == $key ) $selected = ' selected ';
				$field .= "<option value='{$key}'$selected>" . htmlspecialchars($choice) . '</option>';
			}
		}
		$field .= "</select>";
		return $field;
	}
	
	/**
	 * easyui combobox @todo maybe change this to the combogrid and then a default dropdown box to combobox
	 * @param unknown $name
	 * @param unknown $label
	 * @param unknown $values
	 * @param string $parameters
	 * @param string $required
	 * @param string $default
	 * @return string
	 */
	static function period_combobox($name, $label, $default = '', $include_all = false, $parameters = '', $required = false) {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$include_all = ($include_all) ? 'true' : 'false';
		if (gen_not_null($label))  $label = "label='{$label}: '";
		$field = "<input class='easyui-combobox' name='{$name}' {$label} labelPosition='before' data-options=\"limitToList:true,url: 'index.php?action=GetAllPeriods',
                    method: 'get',
                    queryParams: {
  						dataType: 'json',
  		                contentType: 'application/json',
  		                include_all: {$include_all},
  		                async: false,
  		                default: '{$default}',
  					},
                    valueField:'id',
                    textField:'text',
                    groupField:'fiscal_year',
                    loader: customloader,
  		                \"";
		if ($id)                   	 	$field .= " id='{$id}'";
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		if ($required)					$field .= ' required="required" ';
		$field .= '>';
		return $field;
	}
	
	function html_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
		//@todo needs to be replaced by self::combobox
	}
	 
	 
	
	function html_combo_box($name, $values, $default = '', $parameters = '', $width = '220px', $onchange = '', $id = false) {
		//@todo needs to be replaced by self::combobox but with two columns.
	}
	
	static function search ($name, $js_action, $parameters = '') {
		if (strpos($name, '[]')) { // don't show id attribute if generic array
			$id = false;
		} else {
			$id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
			$id = str_replace(']', '',  $id);
		}
		$field = "<input type='search' class='easyui-searchbox' name='{$name}' label='".TEXT_SEARCH.": ' labelPosition='before' data-options='prompt:\"". TEXT_PLEASE_INPUT_VALUE."\",searcher:{$js_action}'";
		if ($id)                   	 	$field .= " id='{$id}'";
		if (gen_not_null($parameters)) 	$field .= ' ' . $parameters;
		$field .= ' />';
		return $field;
	}
	
}