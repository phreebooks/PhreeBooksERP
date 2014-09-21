<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /includes/classes/currencies.php
//
namespace core\classes;
define('CURRENCY_SERVER_PRIMARY', 'oanda');
define('CURRENCY_SERVER_BACKUP',  'yahoo');
require_once(DIR_FS_MODULES . 'phreedom/config.php');
gen_pull_language('phreedom', 'admin');

class currencies {
	private $default_currency 	= false;
  	public  $currencies			= array();
  	public  $db_table      		= TABLE_CURRENCIES;
	public  $title         		= TEXT_CURRENCIES;
    public  $extra_buttons 		= true;
	public  $help_path     		= '07.08.02';
	public  $def_currency  		= DEFAULT_CURRENCY;

  	function __construct() {
  		$this->security_id   = \core\classes\user::security_level(SECURITY_ID_CONFIGURATION);
  	}

  	function load_currencies(){
  		global $admin;
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  		$currencies = $admin->DataBase->query("select * from " .$this->db_table);
  		die(print_r($currencies));
    	while (!$currencies->EOF) {
	  		$this->currencies[$currencies->fields['code']] = array(
	    	  'title'           => $currencies->fields['title'],
	    	  'symbol_left'     => $currencies->fields['symbol_left'],
	    	  'symbol_right'    => $currencies->fields['symbol_right'],
	    	  'decimal_point'   => $currencies->fields['decimal_point'],
	    	  'thousands_point' => $currencies->fields['thousands_point'],
	    	  'decimal_places'  => $currencies->fields['decimal_places'],
	    	  'decimal_precise' => $currencies->fields['decimal_precise'],
	    	  'value'           => $currencies->fields['value'],
	  		);
      		$currencies->MoveNext();
    	}
    	$this->default_currency_code(); // set default currecy code.
  	}

	// omits the symbol_left and symbol_right (just the formattted number))
	function format($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '') {
	    if ($calculate_currency_value) {
	      	$rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
	      	$format_string = number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
	    } else {
	    	$format_string = number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
	    }
	    return $format_string;
	}

	// omits the symbol_left and symbol_right (just the formattted number to the precision number of decimals))
	function precise($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '') {
	    if ($calculate_currency_value) {
		  $rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
		  $format_string = number_format($number * $rate, $this->currencies[$currency_type]['decimal_precise'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
	    } else {
		  $format_string = number_format($number, $this->currencies[$currency_type]['decimal_precise'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
	    }
	    return $format_string;
	}

	function format_full($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '', $output_format = PDF_APP) {
	    if ($calculate_currency_value) {
		  	$rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
		  	$format_number = number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
	    } else {
		  	$format_number = number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
	    }
		$zero = number_format(0, $this->currencies[$currency_type]['decimal_places']); // to handle -0.00
		if ($format_number == '-'.$zero) $format_number = $zero;
		$format_string = $this->currencies[$currency_type]['symbol_left'] . ' ' . $format_number . ' ' . $this->currencies[$currency_type]['symbol_right'];
	    switch ($output_format) {
		  	case 'FPDF': // assumes default character set
		    	$format_string = str_replace('&euro;', chr(128),  $format_string); // Euro
		    	break;
		  	default:
	    }
	    return $format_string;
	}

	function get_value($currency_type = DEFAULT_CURRENCY) {
	    return $this->currencies[$code]['value'];
	}

	function clean_value($number, $currency_type = DEFAULT_CURRENCY) {
	    // converts the number to standard float format (period as decimal, no thousands separator)
	    $temp  = str_replace($this->currencies[$currency_type]['thousands_point'], '', trim($number));
	    $value = str_replace($this->currencies[$currency_type]['decimal_point'], '.', $temp);
	    return preg_replace("/[^-0-9.]+/","",$value);
	}

	function build_js_currency_arrays() {
		$js_codes  = 'var js_currency_codes = new Array(';
		$js_values = 'var js_currency_values = new Array(';
		foreach ($this->currencies as $code => $values) {
			$js_codes  .= "'" . $code . "',";
			$js_values .= $this->currencies[$code]['value'] . ",";
		}
		$js_codes  = substr($js_codes, 0, -1) . ");";
		$js_values = substr($js_values, 0, -1) . ");";
		return $js_codes . chr(10) . $js_values . chr(10);
	}

  	function default_currency_code(){
  		if(!defined('DEFAULT_CURRENCY')) throw new \core\classes\userException(ERROR_NO_DEFAULT_CURRENCY_DEFINED); // check for default currency defined
  		return $this->default_currency = DEFAULT_CURRENCY;
  	}


  	function btn_save($id = '') {
	  	global $admin, $messageStack;
	  	\core\classes\user::validate_security($this->security_id, 3); // security check
		$title = db_prepare_input($_POST['title']);
		$code = strtoupper(db_prepare_input($_POST['code']));
		if ($_POST['decimal_precise'] == '') $_POST['decimal_precise'] = $_POST['decimal_places'];
		$sql_data_array = array(
			'title'           => $title,
			'code'            => $code,
			'symbol_left'     => db_prepare_input($_POST['symbol_left']),
			'symbol_right'    => db_prepare_input($_POST['symbol_right']),
			'decimal_point'   => db_prepare_input($_POST['decimal_point']),
			'thousands_point' => db_prepare_input($_POST['thousands_point']),
			'decimal_places'  => db_prepare_input($_POST['decimal_places']),
			'decimal_precise' => db_prepare_input($_POST['decimal_precise']),
			'value'           => db_prepare_input($_POST['value']),
		);
	    if ($id) {
		  db_perform($this->db_table, $sql_data_array, 'update', "currencies_id = " . (int)$id);
	      gen_add_audit_log(TEXT_CURRENCIES . ' - ' . TEXT_UPDATE, $title);
		} else  {
	      db_perform($this->db_table, $sql_data_array);
	      gen_add_audit_log(TEXT_CURRENCIES . ' - ' . TEXT_ADD, $title);
		}

		if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
			// first check to see if there are any general ledger entries
		  	$result = $admin->DataBase->query("SELECT id FROM " . TABLE_JOURNAL_MAIN . " LIMIT 1");
		  	if ($result->RecordCount() > 0) throw new \core\classes\userException(SETUP_ERROR_CANNOT_CHANGE_DEFAULT);
		  	write_configure('DEFAULT_CURRENCY', db_input($code));
			db_perform($this->db_table, array('value' => 1), 'update', "code='$code'"); // change default exc rate to 1
		    $admin->DataBase->query("alter table " . TABLE_JOURNAL_MAIN . "
				change currencies_code currencies_code CHAR(3) NOT NULL DEFAULT '" . db_input($code) . "'");
			$this->def_currency = db_input($code);
			$this->btn_update();
		}
		return true;
	}

	//below used to be located in phreedom\classes\currency
    /**
     * this functions updates currency values
     */
  	function btn_update() { // updates the currency rates
	  	global $admin, $messageStack;
		$message = array();
	/* commented out so everyone can update currency exchange rates
	  	\core\classes\user::validate_security($security_level, 1);
	*/
		$server_used = CURRENCY_SERVER_PRIMARY;
		$currency = $admin->DataBase->query("select currencies_id, code, title from " . $this->db_table);
		while (!$currency->EOF) {
		  if ($currency->fields['code'] == $this->def_currency) { // skip default currency
		    $currency->MoveNext();
			continue;
		  }
		  $quote_function = 'quote_'.CURRENCY_SERVER_PRIMARY;
		  $rate = $this->$quote_function($currency->fields['code'], $this->def_currency);
		  if (empty($rate) && (gen_not_null(CURRENCY_SERVER_BACKUP))) {
			$message[] = sprintf(SETUP_WARN_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency->fields['title'], $currency->fields['code']);
			$messageStack->add(sprintf(SETUP_WARN_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency->fields['title'], $currency->fields['code']), 'caution');
			$quote_function = 'quote_'.CURRENCY_SERVER_BACKUP;
			$rate = $this->$quote_function($currency->fields['code'], $this->def_currency);
			$server_used = CURRENCY_SERVER_BACKUP;
		  }
		  if ($rate <> 0) {
			$admin->DataBase->query("update " . $this->db_table . " set value = '" . $rate . "', last_updated = now()
			  where currencies_id = '" . (int)$currency->fields['currencies_id'] . "'");
			$message[] = sprintf(SETUP_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], $server_used);
			$messageStack->add(sprintf(SETUP_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], $server_used), 'success');
		  } else {
			$message[] = sprintf(SETUP_ERROR_CURRENCY_INVALID, $currency->fields['title'], $currency->fields['code'], $server_used);
			throw new \core\classes\userException(sprintf(SETUP_ERROR_CURRENCY_INVALID, $currency->fields['title'], $currency->fields['code'], $server_used));
		  }
		  $currency->MoveNext();
		}
		if (sizeof($message) > 0) $this->message = implode("\n", $message);
		return true;
	}

	function quote_oanda($code, $base = DEFAULT_CURRENCY) {
	  	$page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch='.$code.'&format=CSV&dest=Get+Table&sel_list=' . $base);
	  	$match = array();
	  	preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);
	  	return (sizeof($match) > 0) ? $match[3] : false;
	}

	function quote_yahoo($to, $from = DEFAULT_CURRENCY) {
	  	if (($page = @file_get_contents('http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s='.$from.$to.'=X')) === false) throw new \core\classes\userException("can not open 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=$from$to=X'");
	  	if ($page) $parts = explode(',', trim($page));
	  	return ($parts[1] > 0) ? $parts[1] : false;
	}

	function btn_delete($id = 0) {
	  	global $admin;
	  	\core\classes\user::validate_security($this->security_id, 4); // security check
		// Can't delete default currency or last currency
		$result = $admin->DataBase->query("select currencies_id from " . $this->db_table . " where code = '" . DEFAULT_CURRENCY . "'");
		if ($result->fields['currencies_id'] == $id) throw new \core\classes\userException(ERROR_CANNOT_DELETE_DEFAULT_CURRENCY);
		$result = $admin->DataBase->query("select code, title from " . $this->db_table . " where currencies_id = '" . $id . "'");
		$test_1 = $admin->DataBase->query("select id from " . TABLE_JOURNAL_MAIN . " where currencies_code = '" . $result->fields['code'] . "' limit 1");
		if ($test_1->RecordCount() > 0) throw new \core\classes\userException(ERROR_CURRENCY_DELETE_IN_USE);
		$admin->DataBase->query("delete from " . $this->db_table . " where currencies_id = '" . $id . "'");
		gen_add_audit_log(TEXT_CURRENCIES . ' - ' . TEXT_DELETE, $result->fields['title']);
		return true;
	}

	function build_main_html() {
	  	global $admin;
	    $content = array();
		$content['thead'] = array(
		  'value'  => array(TEXT_CURRENCY, TEXT_CURRENCY_CODE, TEXT_VALUE, TEXT_ACTION),
		  'params' => 'width="100%" cellspacing="0" cellpadding="1"',
		);
	    $rowCnt = 0;
	    foreach ($this->currencies as $code => $value) {
		  	$actions = '';
		  	if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\'currency_edit\', ' . $code . ')"') . chr(10);
		  	if ($this->security_id > 3 && $result->fields['code'] <> DEFAULT_CURRENCY) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_CURR_DELETE_INTRO . '\')) subjectDelete(\'currency\', ' . $code . ')"') . chr(10);
		  	$content['tbody'][$rowCnt] = array(
		      array('value' => DEFAULT_CURRENCY == $code ? '<b>'.htmlspecialchars($value['title']).' ('.TEXT_DEFAULT.')</b>' : htmlspecialchars($value['title']),
				    'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'currency_edit\',\''.$code.'\')"'),
			  array('value' => $code,
				    'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'currency_edit\',\''.$code.'\')"'),
			  array('value' => number_format($value['value'], 8),
				    'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'currency_edit\',\''.$code.'\')"'),
			  array('value' => $actions,
				    'params'=> 'align="right"'),
		    );
		  	$rowCnt++;
	    }
	    return html_datatable('currency_table', $content);
	}

	function build_form_html($action, $code) {
	    global $admin;
	    $this->load_currencies();
	    $value = $this->currencies[$code];
		$output  = '<table class="ui-widget" style="border-style:none;width:100%">' . chr(10);
		$output .= '  <thead class="ui-widget-header">' . "\n";
		$output .= '  <tr>' . chr(10);
		$output .= '    <th colspan="2">' . ($action=='new' ? sprintf(TEXT_NEW_ARGS, TEXT_CURRENCY) : sprintf(TEXT_EDIT_ARGS, TEXT_CURRENCY)) . '</th>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  </thead>' . "\n";
		$output .= '  <tbody class="ui-widget-content">' . "\n";
		$output .= '  <tr>' . chr(10);
		$output .= '    <td colspan="2">' . ($action=='new' ? TEXT_PLEASE_ENTER_THE_NEW_CURRENCY_WITH_ITS_RELATED_DATA : TEXT_PLEASE_MAKE_ANY_NECESSARY_CHANGES) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_TITLE . '</td>' . chr(10);
		$output .= '    <td nowrap="nowrap">' . html_input_field('title', $value['title'], '', true) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_CURRENCY_CODE . ' : </td>' . chr(10);
		$output .= '    <td nowrap="nowrap">' . html_input_field('code', $code, '', true) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_SYMBOL_LEFT . ':</td>' . chr(10);
		$output .= '    <td>' . html_input_field('symbol_left', htmlspecialchars($value['symbol_left'])) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_SYMBOL_RIGHT . ':</td>' . chr(10);
		$output .= '    <td>' . html_input_field('symbol_right', htmlspecialchars($value['symbol_right'])) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_DECIMAL_POINT . ':</td>' . chr(10);
		$output .= '    <td nowrap="nowrap">' . html_input_field('decimal_point', $value['decimal_point'], '', true) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_THOUSANDS_POINT . ':</td>' . chr(10);
		$output .= '    <td>' . html_input_field('thousands_point', $value['thousands_point']) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_DECIMAL_PLACES . ':</td>' . chr(10);
		$output .= '    <td>' . html_input_field('decimal_places', $value['decimal_places'], '', true) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . SETUP_INFO_CURRENCY_DECIMAL_PRECISE . '</td>' . chr(10);
		$output .= '    <td nowrap="nowrap">' . html_input_field('decimal_precise', $value['decimal_precise'], '', true) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_VALUE . ' : </td>' . chr(10);
		$output .= '    <td>' . html_input_field('value', $value['value']) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
		if (DEFAULT_CURRENCY != $code) {
		  $output .= '  <tr>' . chr(10);
		  $output .= '    <td colspan="2">' . html_checkbox_field('default', 'on', false) . ' ' . SETUP_INFO_SET_AS_DEFAULT . '</td>' . chr(10);
	      $output .= '  </tr>' . chr(10);
		}
		$output .= '  </tbody>' . "\n";
	    $output .= '</table>' . chr(10);
	    return $output;
	}

}