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
//  Path: /modules/phreedom/classes/currency.php
//
// define how do we update currency exchange rates. Possible values are 'oanda' 'yahoo'
// xe no longer works as of 2012-02-01

define('CURRENCY_SERVER_PRIMARY', 'oanda');
define('CURRENCY_SERVER_BACKUP',  'yahoo');

class currency {

  function __construct() {
  	$this->security_id   = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
	$this->db_table      = TABLE_CURRENCIES;
	$this->title         = SETUP_TITLE_CURRENCIES;
    $this->extra_buttons = true;
	$this->help_path     = '07.08.02';
	$this->def_currency  = DEFAULT_CURRENCY;
  }

  function btn_save($id = '') {
  	global $db, $messageStack;
	if ($this->security_id < 3) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		return false;
	}
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
      gen_add_audit_log(SETUP_LOG_CURRENCY . TEXT_UPDATE, $title);
	} else  {
      db_perform($this->db_table, $sql_data_array);
      gen_add_audit_log(SETUP_LOG_CURRENCY . TEXT_ADD, $title);
	}

	if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
	  // first check to see if there are any general ledger entries
	  $result = $db->Execute("SELECT id FROM " . TABLE_JOURNAL_MAIN . " LIMIT 1");
	  if ($result->RecordCount() > 0) {
		$messageStack->add(SETUP_ERROR_CANNOT_CHANGE_DEFAULT,'error');
	  } else {
	    write_configure('DEFAULT_CURRENCY', db_input($code));
		db_perform($this->db_table, array('value' => 1), 'update', "code='$code'"); // change default exc rate to 1
	    $db->Execute("alter table " . TABLE_JOURNAL_MAIN . " 
			change currencies_code currencies_code CHAR(3) NOT NULL DEFAULT '" . db_input($code) . "'");
		$this->def_currency = db_input($code);
		$this->btn_update();
	  }
	}
	return true;
  }

  function btn_update() { // updates the currency rates
  	global $db, $messageStack;
	$message = array();
/* commented out so everyone can update currency exchange rates
  	validate_security($security_level, 1);
*/
	$server_used = CURRENCY_SERVER_PRIMARY;
	$currency = $db->Execute("select currencies_id, code, title from " . $this->db_table);
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
		$db->Execute("update " . $this->db_table . " set value = '" . $rate . "', last_updated = now()
		  where currencies_id = '" . (int)$currency->fields['currencies_id'] . "'");
		$message[] = sprintf(SETUP_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], $server_used);
		$messageStack->add(sprintf(SETUP_INFO_CURRENCY_UPDATED, $currency->fields['title'], $currency->fields['code'], $server_used), 'success');
	  } else {
	  	
		$message[] = sprintf(SETUP_ERROR_CURRENCY_INVALID, $currency->fields['title'], $currency->fields['code'], $server_used);
		$messageStack->add(sprintf(SETUP_ERROR_CURRENCY_INVALID, $currency->fields['title'], $currency->fields['code'], $server_used), 'error');
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
  	$page = file_get_contents('http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s='.$from.$to.'=X');
  	if ($page) $parts = explode(',', trim($page));
  	return ($parts[1] > 0) ? $parts[1] : false;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	if ($this->security_id < 4) {
	  $messageStack->add(ERROR_NO_PERMISSION, 'error');
	  return false;
	}
	// Can't delete default currency or last currency
	$result = $db->Execute("select currencies_id from " . $this->db_table . " where code = '" . DEFAULT_CURRENCY . "'");
	if ($result->fields['currencies_id'] == $id) {
	  $messageStack->add(ERROR_CANNOT_DELETE_DEFAULT_CURRENCY, 'error');
	  return false;
	}
	$result = $db->Execute("select code, title from " . $this->db_table . " where currencies_id = '" . $id . "'");
	$test_1 = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . " where currencies_code = '" . $result->fields['code'] . "' limit 1");
	if ($test_1->RecordCount() > 0) {
	  $messageStack->add(ERROR_CURRENCY_DELETE_IN_USE, 'error');
	  return false;
	}
	$db->Execute("delete from " . $this->db_table . " where currencies_id = '" . $id . "'");
	gen_add_audit_log(SETUP_LOG_CURRENCY . TEXT_DELETE, $result->fields['title']);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack;
    $content = array();
	$content['thead'] = array(
	  'value'  => array(SETUP_CURRENCY_NAME, SETUP_CURRENCY_CODES, TEXT_VALUE, TEXT_ACTION),
	  'params' => 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value 
	    from " . TABLE_CURRENCIES);
    $rowCnt = 0;
	while (!$result->EOF) {
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\'currency_edit\', ' . $result->fields['currencies_id'] . ')"') . chr(10);
	  if ($this->security_id > 3 && $result->fields['code'] <> DEFAULT_CURRENCY) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_CURR_DELETE_INTRO . '\')) subjectDelete(\'currency\', ' . $result->fields['currencies_id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => DEFAULT_CURRENCY==$result->fields['code'] ? '<b>'.htmlspecialchars($result->fields['title']).' ('.TEXT_DEFAULT.')</b>' : htmlspecialchars($result->fields['title']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'currency_edit\',\''.$result->fields['currencies_id'].'\')"'),
		array('value' => $result->fields['code'], 
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'currency_edit\',\''.$result->fields['currencies_id'].'\')"'),
		array('value' => number_format($result->fields['value'], 8),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'currency_edit\',\''.$result->fields['currencies_id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable('currency_table', $content);
  }

  function build_form_html($action, $id) {
    global $db;
    $sql = "select * from " . $this->db_table . " where currencies_id = '" . $id . "'";
    $result = $db->Execute($sql);
	if ($action == 'new') {
	  $cInfo = '';
	} else {
      $cInfo = new objectInfo($result->fields);
	}
	$output  = '<table class="ui-widget" style="border-style:none;width:100%">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? SETUP_INFO_HEADING_NEW_CURRENCY : SETUP_INFO_HEADING_EDIT_CURRENCY) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? SETUP_CURR_INSERT_INTRO : SETUP_CURR_EDIT_INTRO) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_TITLE . '</td>' . chr(10);
	$output .= '    <td nowrap="nowrap">' . html_input_field('title', $cInfo->title, '', true) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_CODE . '</td>' . chr(10);
	$output .= '    <td nowrap="nowrap">' . html_input_field('code', $cInfo->code, '', true) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_SYMBOL_LEFT . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('symbol_left', htmlspecialchars($cInfo->symbol_left)) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_SYMBOL_RIGHT . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('symbol_right', htmlspecialchars($cInfo->symbol_right)) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_DECIMAL_POINT . '</td>' . chr(10);
	$output .= '    <td nowrap="nowrap">' . html_input_field('decimal_point', $cInfo->decimal_point, '', true) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_THOUSANDS_POINT . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('thousands_point', $cInfo->thousands_point) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_DECIMAL_PLACES . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('decimal_places', $cInfo->decimal_places, '', true) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_DECIMAL_PRECISE . '</td>' . chr(10);
	$output .= '    <td nowrap="nowrap">' . html_input_field('decimal_precise', $cInfo->decimal_precise, '', true) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_CURRENCY_VALUE . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('value', $cInfo->value) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	if (DEFAULT_CURRENCY != $cInfo->code) {
	  $output .= '  <tr>' . chr(10);
	  $output .= '    <td colspan="2">' . html_checkbox_field('default', 'on', false) . ' ' . SETUP_INFO_SET_AS_DEFAULT . '</td>' . chr(10);
      $output .= '  </tr>' . chr(10);
	}
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }

}
?>