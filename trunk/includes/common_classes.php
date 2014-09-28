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
//  Path: /modules/general/classes/toolbar.php
//

// General functions used across modules. Divided into the following sections:
// Section 1. Class toolbar
// Section 2. Class splitPageResults
// Section 3. Class objectInfo
// Section 4. Class messageStack
// Section 5. Class ctlPanel
// Section 6. Class currencies
// Section 7. Class encryption

/**************************************************************************************************************/
// Section 1. Class toolbar
/**************************************************************************************************************/
class toolbar {
	public $id            = 0;
	public $search_text   = '';
	public $search_period = CURRENT_ACCOUNTING_PERIOD;
	public $period_strict = true; // if set to true, the 'All' option is included
	public $search_prefix = '';
    public $icon_size     = 'large';	// default icon size (choice are small, medium, large)
  	public $icon_list     = array();

  function __construct($id = '0') {
    // set up the default toolbar
	$this->id            = $id;
	$this->icon_list['cancel'] = array('show' => true, 'icon' => 'actions/edit-undo.png',        'params' => '', 'text' => TEXT_CANCEL, 'order' => 1);
	$this->icon_list['open']   = array('show' => true, 'icon' => 'actions/document-open.png',    'params' => '', 'text' => TEXT_OPEN,   'order' => 2);
	$this->icon_list['save']   = array('show' => true, 'icon' => 'devices/media-floppy.png',     'params' => '', 'text' => TEXT_SAVE,   'order' => 3);
	$this->icon_list['delete'] = array('show' => true, 'icon' => 'actions/edit-delete.png',      'params' => '', 'text' => TEXT_DELETE, 'order' => 4);
	$this->icon_list['print']  = array('show' => true, 'icon' => 'phreebooks/pdficon_large.gif', 'params' => '', 'text' => TEXT_PRINT,  'order' => 5);
  }

  function add_icon($name, $params = '', $order = 98) { // adds some common icons, per request
	switch ($name) {
	  case 'back':
	  case 'previous':   $image = 'actions/go-previous.png';            $text = TEXT_BACK;       break;
	  case 'continue':
	  case 'next':       $image = 'actions/go-next.png';                $text = TEXT_CONTINUE;   break;
	  case 'copy':       $image = 'actions/edit-copy.png';              $text = TEXT_COPY;       break;
	  case 'edit':       $image = 'actions/edit-find-replace.png';      $text = TEXT_EDIT;       break;
	  case 'email':      $image = 'apps/internet-mail.png';             $text = GEN_EMAIL;       break;
	  case 'export':     $image = 'actions/format-indent-more.png';     $text = TEXT_EXPORT;     break;
	  case 'export_csv': $image = 'mimetypes/x-office-spreadsheet.png'; $text = TEXT_EXPORT_CSV; break;
	  case 'finish':     $image = 'actions/document-save.png';          $text = TEXT_FINISH;     break;
	  case 'import':     $image = 'actions/format-indent-less.png';     $text = TEXT_IMPORT;     break;
	  case 'new':        $image = 'actions/document-new.png';           $text = TEXT_NEW;        break;
	  case 'recur':      $image = 'actions/go-jump.png';                $text = TEXT_RECUR;      break;
	  case 'rename':     $image = 'apps/accessories-text-editor.png';   $text = TEXT_RENAME;     break;
	  case 'payment':    $image = 'apps/accessories-calculator.png';    $text = TEXT_PAYMENT;    break;
	  case 'ship_all':   $image = 'mimetypes/package-x-generic.png';    $text = TEXT_SHIP_ALL;   break;
	  case 'search':     $image = 'actions/system-search.png';          $text = TEXT_SEARCH;     break;
	  case 'update':     $image = 'apps/system-software-update.png';    $text = TEXT_UPDATE;     break;
	  default:           $image = 'emblems/emblem-important.png';       $text = $name . ' ICON NOT FOUND';
	}
	if ($image) $this->icon_list[$name] = array('show' => true, 'icon' => $image, 'params' => $params, 'text' => $text, 'order' => $order);
  }

  function add_help($index = '', $order = 99) { // adds some common icons, per request
	$this->icon_list['help'] = array(
	  'show'   => true,
	  'icon'   => 'apps/help-browser.png',
	  'params' => 'onclick="window.open(\'' . FILENAME_DEFAULT . '.php?module=phreehelp&amp;page=main&amp;idx=' . $index . '\',\'help\',\'width=800,height=600,resizable=1,scrollbars=1,top=100,left=100\')"',
	  'text'   => TEXT_HELP,
	  'order'  => $order,
	);
  }

  function build_toolbar($add_search = false, $add_period = false, $cal_props = false) { // build the main toolbar
	global $messageStack;
    $output = '';
	if ($add_search) $output .= $this->add_search();
	if ($add_period) $output .= $this->add_period();
	if ($cal_props)  $output .= $this->add_date($cal_props);
	$output .= '<div id="tb_main_' . $this->id . '" class="ui-state-hover" style="border:0px;">' . "\n";
	// Sort the icons by designated order
	$sort_arr = array();
    foreach($this->icon_list as $uniqid => $row) foreach($row as $key => $value) $sort_arr[$key][$uniqid] = $value;
	array_multisort($sort_arr['order'], SORT_ASC, $this->icon_list);
	foreach ($this->icon_list as $id => $icon) {
	  if ($icon['show']) $output .= html_icon($icon['icon'], $icon['text'], $this->icon_size, 'id ="tb_icon_' . $id . '" style="cursor:pointer;" ' . $icon['params']) . "\n";
	}
	$output .= '</div>' . "\n"; // end of the right justified icons
	// display alerts/error messages, if any
    $output .= $messageStack->output();
    return $output;
  }

  function add_search() {
  	if($this->search_text == '') $this->search_text = $_REQUEST['search_text'];
	$output = '<div id="tb_search_' . $this->id . '" class="ui-state-hover" style="float:right; border:0px;">' . "\n";
	$output .= HEADING_TITLE_SEARCH_DETAIL . '<br />';
	$output .= html_input_field('search_text', $this->search_text, $params = 'onkeypress="checkEnter(event);"');
	if ($this->search_text) $output .= '&nbsp;' . html_icon('actions/view-refresh.png', TEXT_RESET, 'small', 'onclick="location.href = \'index.php?' . gen_get_all_get_params(array('search_text', 'search_period', 'search_date', 'so', 'sf', 'list', 'action')) . '&reset=1\';" style="cursor:pointer;"');
    $output .= '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'onclick="searchPage(\'' . gen_get_all_get_params(array('search_text', 'list', 'action')) . '\')" style="cursor:pointer;"');
	$output .= '</div>' . "\n";
	return $output;
  }

  function add_period() {
	$output = '<div id="tb_period_' . $this->id . '" class="ui-state-hover" style="float:right; border:0px;">' . "\n";
	$output .= TEXT_INFO_SEARCH_PERIOD_FILTER . '<br />' . "\n";
	$output .= html_pull_down_menu('search_period', gen_get_period_pull_down($this->period_strict), $this->search_period, 'onchange="periodPage(\'' . gen_get_all_get_params(array('action', 'list')) . '\')"');
	$output .= '</div>' . "\n";
	return $output;
  }

  function add_date($cal_props) {
	$output = '<div id="tb_date_' . $this->id . '" class="ui-state-hover" style="float:right; border:0px;">' . "\n";
	$output .= TEXT_DATE . '<br />' . "\n";
	$output .= html_calendar_field($cal_props) . "\n";
	$output .= '</div>' . "\n";
	return $output;
  }

}

/**************************************************************************************************************/
// Section 2. Class splitPageResults
/**************************************************************************************************************/
class splitPageResults {
  	public	$current_page_number	= 1;
	public	$jump_page_displayed 	= false;
	public	$max_rows_per_page 		= MAX_DISPLAY_SEARCH_RESULTS;
	public	$page_prefix         	= '';
	public	$page_start				= 0;
	public	$total_num_rows			= 0;
	public  $total_num_pages		= 1;

	function __construct($current_page_number, $query_num_rows) {
    	global $db, $messageStack;
    	if($query_num_rows == '') {
    		$temp = $db->Execute('SELECT FOUND_ROWS() AS found_rows;');
    		$query_num_rows = $temp->fields['found_rows'];
    	}
    	$this->total_num_rows		= $query_num_rows;
      	$this->current_page_number 	= max(1, $current_page_number);
		$this->total_num_pages		= ceil($this->total_num_rows / $this->max_rows_per_page);
		if ($this->total_num_pages == 0) $this->total_num_pages = 1;
      	if ($this->total_num_pages < $this->current_page_number) $this->current_page_number = max(1, $this->total_num_pages);
    }

    function display_links($page_name = 'list') {
	    $pages_array = array();
	    for ($i = 1; $i <= $this->total_num_pages; $i++) $pages_array[] = array('id' => $i, 'text' => $i);
	    if ($this->total_num_pages > 1) {
	        $display_links = '';
	        if ($this->current_page_number > 1) {
			  	$display_links .= html_icon('actions/media-skip-backward.png', TEXT_GO_FIRST, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_first', 'SSL') . '\'" style="cursor:pointer;"');
			  	$display_links .= html_icon('phreebooks/media-playback-previous.png', TEXT_GO_PREVIOUS, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_previous', 'SSL') . '\'" style="cursor:pointer;"');
	        } else {
			  	$display_links .= html_icon('actions/media-skip-backward.png', '', 'small', '');
			  	$display_links .= html_icon('phreebooks/media-playback-previous.png', '', 'small', '');
	        }
	        if (!$this->jump_page_displayed) { // only diplay pull down once (the rest are not read by browser)
			  	$display_links .= sprintf(TEXT_RESULT_PAGE, html_pull_down_menu($page_name, $pages_array, $this->current_page_number, 'onchange="jumpToPage(\'' . gen_get_all_get_params(array('list', 'action')) . '&action=go_page\')"'), $this->total_num_pages);
			  	$this->jump_page_displayed = true;
			} else {
				$display_links .= sprintf(TEXT_RESULT_PAGE, $this->current_page_number, $this->total_num_pages);
			}
	        if (($this->current_page_number < $this->total_num_pages) && ($this->total_num_pages != 1)) {
				$display_links .= html_icon('actions/media-playback-start.png', TEXT_GO_NEXT, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_next', 'SSL') . '\'" style="cursor:pointer;"');
				$display_links .= html_icon('actions/media-skip-forward.png', TEXT_GO_LAST, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_last', 'SSL') . '\'" style="cursor:pointer;"');
	        } else {
				$display_links .= html_icon('actions/media-playback-start.png', '', 'small', '');
				$display_links .= html_icon('actions/media-skip-forward.png', '', 'small', '');
	        }
	    } else {
	        $display_links = sprintf(TEXT_RESULT_PAGE, $this->total_num_pages, $this->total_num_pages);
	    }
	    return $display_links;
    }

    function display_ajax($page_name = 'list', $id = '') {
      	$display_links   = '';
      	$pages_array     = array();
      	for ($i = 1; $i <= $this->total_num_pages; $i++) $pages_array[] = array('id' => $i, 'text' => $i);
      	if ($this->total_num_pages > 1) {
        	if ($this->current_page_number > 1) {
		  		$display_links .= html_icon('actions/media-skip-backward.png', TEXT_GO_FIRST, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_first\')" style="cursor:pointer;"');
		  		$display_links .= html_icon('phreebooks/media-playback-previous.png', TEXT_GO_PREVIOUS, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_previous\')" style="cursor:pointer;"');
        	} else {
		  		$display_links .= html_icon('actions/media-skip-backward.png', '', 'small', '');
				$display_links .= html_icon('phreebooks/media-playback-previous.png', '', 'small', '');
        	}
        	if (!$this->jump_page_displayed) { // only diplay pull down once (the rest are not read by browser)
		  		$display_links .= sprintf(TEXT_RESULT_PAGE, html_pull_down_menu($page_name, $pages_array, $this->current_page_number, 'onchange="tabPage(\'' . $id . '\', \'go_page\')"'), $this->total_num_pages);
		  		$this->jump_page_displayed = true;
			} else {
		  		$display_links .= sprintf(TEXT_RESULT_PAGE, $this->current_page_number, $this->total_num_pages);
			}
        	if (($this->current_page_number < $this->total_num_pages) && ($this->total_num_pages != 1)) {
		  		$display_links .= html_icon('actions/media-playback-start.png', TEXT_GO_NEXT, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_next\')" style="cursor:pointer;"');
		  		$display_links .= html_icon('actions/media-skip-forward.png', TEXT_GO_LAST, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_last\')" style="cursor:pointer;"');
        	} else {
		  	$display_links .= html_icon('actions/media-playback-start.png', '', 'small', '');
		  	$display_links .= html_icon('actions/media-skip-forward.png', '', 'small', '');
        	}
		} else {
        	$display_links .= sprintf(TEXT_RESULT_PAGE, $this->total_num_pages, $this->total_num_pages);
			$display_links .= html_hidden_field($page_name, '1');
      	}
      	return $display_links;
    }

    function display_count($text_output){
    	if ($text_output == '' || !is_string($text_output)) $text_output = TEXT_DISPLAY_NUMBER . TEXT_ITEMS;
      	$to_num = ($this->max_rows_per_page * $this->current_page_number);
      	if ($to_num > $this->total_num_rows) $to_num = $this->total_num_rows;
      	$from_num = ($this->max_rows_per_page * ($this->current_page_number - 1));
      	if ($to_num == 0) {
        	$from_num = 0;
      	} else {
        	$from_num++;
      	}
      	return sprintf($text_output, $from_num, $to_num, $this->total_num_rows);
    }

}

/**************************************************************************************************************/
// Section 3. Class objectInfo
/**************************************************************************************************************/
class objectInfo {
  function __construct($object_array = array()) {
    if (is_array($object_array)) {
      reset($object_array);
      while (list($key, $value) = each($object_array)) $this->$key = db_prepare_input($value);
	}
  }
}

/**************************************************************************************************************/
// Section 4. Class messageStack
/**************************************************************************************************************/
  class messageStack {
    public $debug_info 	= NULL;

    function add($message, $type = 'error') {
      	if ($type == 'error') {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-error"', 'text' => html_icon('emblems/emblem-unreadable.png', TEXT_ERROR) . '&nbsp;' . $message);
      	} elseif ($type == 'success') {
	    	if (!HIDE_SUCCESS_MESSAGES) $_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-active"', 'text' => html_icon('emotes/face-smile.png', TEXT_SUCCESS) . '&nbsp;' . $message);
      	} elseif ($type == 'caution' || $type == 'warning') {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-highlight"', 'text' => html_icon('emblems/emblem-important.png', TEXT_CAUTION) . '&nbsp;' . $message);
      	} else {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-error"', 'text' => $message);
      	}
      	$this->debug("\n On screen displaying '$type' message = $message file = $file line = $line");
	  	return true;
    }

    function reset() {
      unset($_SESSION['messageToStack']);
    }

    /**
     * this function will be replaced by the add function.
     * @todo = Remove
     */

    function add_session($message, $type = 'error') {
    	trigger_error('messageStack->add_session will be removed', E_USER_DEPRECATED);
      	if ($type == 'error') {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-error"', 'text' => html_icon('emblems/emblem-unreadable.png', TEXT_ERROR) . '&nbsp;' . $message);
      	} elseif ($type == 'success') {
	    	if (!HIDE_SUCCESS_MESSAGES) $_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-active"', 'text' => html_icon('emotes/face-smile.png', TEXT_SUCCESS) . '&nbsp;' . $message);
      	} elseif ($type == 'caution' || $type == 'warning') {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-highlight"', 'text' => html_icon('emblems/emblem-important.png', TEXT_CAUTION) . '&nbsp;' . $message);
      	} else {
        	$_SESSION['messageToStack'][] = array('type' => $type, 'params' => 'class="ui-state-error"', 'text' => $message);
      	}
      	$this->debug("\n On screen displaying '$type' message = $message file = $file line = $line");
    }

    function output() {
		$output = NULL;
	  	if (! isset($_SESSION['messageToStack'])) return '';
	  	$output .= '<table style="border-collapse:collapse;width:100%">' . chr(10);
		foreach ($_SESSION['messageToStack'] as $value) {
			$output .= '<tr><td ' . $value['params'] . ' style="width:100%">' . $value['text'] . '</td></tr>' . chr(10);
	  	}
	  	$output .= '</table>' . chr(10);
	  	$this->reset();
      	return $output;
    }

	function debug_header() {
	  	$this->debug_info .= "Trace information for debug purposes. Phreedom release " . MODULE_PHREEDOM_VERSION . ", generated " . date('Y-m-d H:i:s') . ".\n\n";
	  	$this->debug_info .= "\nGET     Vars = " . arr2string($_GET);
	  	$this->debug_info .= "\nPOST    Vars = " . arr2string($_POST);
	  	$this->debug_info .= "\nREQUEST Vars = " . arr2string($_REQUEST);
	  	$this->debug_info .= "\nSESSION Vars = " . arr2string($_SESSION);
	}

	function debug($txt) {
	  	global $db;
	  	if (substr($txt, 0, 1) == "\n") {
//echo "\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $db->count_queries . " SQLs " . (int)($db->total_query_time * 1000)." ms => " . substr($txt, 1) . '<br>';
	    	$this->debug_info .= "\nTime: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $db->count_queries . " SQLs " . (int)($db->total_query_time * 1000)." ms => ";
	    	$this->debug_info .= substr($txt, 1);
	  	} else {
	    	$this->debug_info .= $txt;
	  	}
	}

	function write_debug() {
	  	global $db;
	  	if (strlen($this->debug_info) < 1) return;
	  	$this->debug_info .= "\n\nPage trace stats: Execution Time: " . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . " ms, " . $db->count_queries . " queries taking " . (int)($db->total_query_time * 1000)." ms";
      	$filename = DIR_FS_MY_FILES . 'trace.txt';
      	if (!$handle = fopen($filename, 'w')) return $this->add("Cannot open file ($filename)", "error");
      	if (fwrite($handle, $this->debug_info) === false) return $this->add("Cannot write to file ($filename)","error");
      	fclose($handle);
	  	$this->debug_info = NULL;
	  	$this->add("Successfully created trace.txt file.","success");
	}
  }

/**************************************************************************************************************/
// Section 5. Class ctlPanel
/**************************************************************************************************************/
class ctl_panel {
	public $dashboard_id 		= '';
	public $default_num_rows 	= 20;
	public $description	 		= '';
	public $max_length   		= 20;
	public $menu_id				= 'index';
	public $module_id 			= '';
	public $params				= '';
	public $security_id  		= '';
	public $title		 		= '';
	public $version      		= 1;
	public $valid_user			= false;
	public $size_params			= 0;
	public $default_params 		= array();

  	function __construct() {
  		if ($this->security_id <> '' ) $this->valid_user = ($_SESSION['admin_security'][$this->security_id] > 0)? true : false;
  		else $this->valid_user = true;
  	}

  	function pre_install($odd, $my_profile){
  		if(!$this->valid_user) return false;
		$output  = '<tr class="'.($odd?'odd':'even').'"><td align="center">';
		$checked = (in_array($this->dashboard_id, $my_profile)) ? ' selected' : '';
		$output .=  html_checkbox_field($this->dashboard_id, '1', $checked, '', $parameters = '');
		$output .=' </td><td>' . $this->title . '</td><td>' . $this->description . '</td></tr>';
		return $output;
	}

  	function Install($column_id = 1, $row_id = 0) {
		global $db;
		if (!$row_id) $row_id 		= $this->get_next_row();
		//$this->params['num_rows']   = $this->default_num_rows;	// defaults to unlimited rows
		$result = $db->Execute("insert into " . TABLE_USERS_PROFILES . " set
			user_id = "       . $_SESSION['admin_id'] . ",
			menu_id = '"      . $this->menu_id . "',
		  	module_id = '"    . $this->module_id . "',
		  	dashboard_id = '" . $this->dashboard_id . "',
		  	column_id = "     . $column_id . ",
		  	row_id = "        . $row_id . ",
		  	params = '"       . serialize($this->default_params) . "'");
  	}

  	function Remove() {
		global $db;
		$result = $db->Execute("delete from " . TABLE_USERS_PROFILES . "
	  	where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' and dashboard_id = '" . $this->dashboard_id . "'");
  	}

  	function Update() {
  		global $db;
  		$db->Execute("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($this->params) . "'
	  		where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "'
	    	and dashboard_id = '" . $this->dashboard_id . "'");
  	}

  	function build_div($title, $contents, $controls) {
	  	if(!$this->valid_user) return false;
	  	$output = '';
	  	if($this->version < 3.5 || ! $this->version ) $output .= 'update dashboard ' . $this->title . '<br/>';
		$output .= '<!--// start: ' . $this->dashboard_id . ' //-->' . chr(10);
		$output .= '<div id="' . $this->dashboard_id . '" style="position:relative;">' . chr(10);
		$output .= '<table class="ui-widget" style="border-collapse:collapse;width:100%">' . chr(10);
		$output .= '<thead class="ui-widget-header">' . chr(10);
		$output .= '<tr>' . chr(10);
		// heading text
		$output .= '<td style="width:90%">' . $this->title . '&nbsp;</td>' . chr(10);
		// edit/cancel image (text)
		$output .= '<td>' . chr(10);
		$output .= '  <div id="'.$this->dashboard_id.'_add"><a href="javascript:void(0)" onclick ="return box_edit(\''.$this->dashboard_id.'\');">';
		$output .= html_icon('categories/preferences-system.png', TEXT_PROPERTIES, $size = 'small', '', '16', '16');
		$output .= '  </a></div>' . chr(10);
		$output .= '  <div id="'.$this->dashboard_id . '_can" style="display:none"><a href="javascript:void(0)" onclick ="return box_cancel(\'' . $this->dashboard_id . '\');">';
		$output .= html_icon('status/dialog-error.png', TEXT_CANCEL, $size = 'small', '', '16', '16');
		$output .= '  </a></div>' . chr(10);
		$output .= '</td>' . chr(10);
		// minimize/maximize image
		$output .= '<td>' . chr(10);
		$output .= '<a href="javascript:void(0)" id="' . $this->dashboard_id . '_min" onclick="this.blur(); return min_box(\'' . $this->dashboard_id . '\')">' . chr(10);
		$output .= html_icon('actions/list-remove.png', TEXT_COLLAPSE, $size = 'small', '', '16', '16', $this->dashboard_id . '_exp');
		$output .= '</a></td>' . chr(10);
		// delete image
		$output .= '<td>' . chr(10);
		$output .= '<a href="javascript:void(0)" id="' . $this->dashboard_id . '_del" onclick="return del_box(\'' . $this->dashboard_id . '\')">';
		$output .= html_icon('emblems/emblem-unreadable.png', TEXT_REMOVE, $size = 'small');
		$output .= '</a>' . chr(10);
		$output .= '</td></tr>' . chr(10);
		$output .= '</thead>' . chr(10);
		// properties contents
		$output .= '<tbody class="ui-widget-content">' . chr(10);
		$output .= '<tr id="' . $this->dashboard_id . '_prop" style="display:none"><td colspan="4">' . chr(10);
		$output .= html_form($this->dashboard_id . '_frm', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
		$output .= $this->build_move_buttons($this->column_id, $this->row_id);
		$output .= $controls . chr(10);
		$output .= '<input type="hidden" name="dashboard_id" value="' . $this->dashboard_id . '" />' . chr(10);
		$output .= '<input type="hidden" name="column_id" value="' . $this->column_id . '" />' . chr(10);
		$output .= '<input type="hidden" name="row_id" value="' . $this->row_id . '" />' . chr(10);
		$output .= '<input type="hidden" name="action" id="' . $this->dashboard_id . '_action" value="save" />' . chr(10);
		$output .= '</form></td></tr>' . chr(10);
		$output .= '<tr id="' . $this->dashboard_id . '_hr" style="display:none"><td colspan="4"><hr /></td></tr>' . chr(10);
		// box contents
		$output .= '<tr><td colspan="4">' . chr(10);
		$output .= '<div id="' . $this->dashboard_id . '_body">' . chr(10);
		$output .= $contents;
		$output .= '</div>';
		$output .= '</td></tr></tbody></table>' . chr(10);
		// finish it up
		$output .= '</div>' . chr(10);
		$output .= '<!--// end: ' . $this->dashboard_id . ' //--><br />' . chr(10) . chr(10);
		return $output;
  	}

  	function build_move_buttons($column_id, $row_id) {
		$output = '<table style="border-collapse:collapse"><tr>' . chr(10);
		// move button - Left
		if ($column_id > 1) {
			$output .= '<td>' . chr(10);
		  	$output .= '<a href="javascript:void(0)" onclick="return move_box(\'' . $this->dashboard_id . '\', \'move_left\')">';
		  	$output .= html_icon('actions/go-previous.png', TEXT_MOVE_LEFT, $size = 'small');
		  	$output .= '</a>' . chr(10);
		  	$output .= '</td>' . chr(10);
		}
		// move button - Right
		if ($column_id < MAX_CP_COLUMNS) {
		  	$output .= '<td>' . chr(10);
		  	$output .= '<a href="javascript:void(0)" onclick="return move_box(\'' . $this->dashboard_id . '\', \'move_right\')">';
		  	$output .= html_icon('actions/go-next.png', TEXT_MOVE_RIGHT, $size = 'small');
		  	$output .= '</a>' . chr(10);
		  	$output .= '</td>' . chr(10);
		}
		// move button - Up
		if ($row_id > 1) {
		  	$output .= '<td>' . chr(10);
		  	$output .= '<a href="javascript:void(0)" onclick="return move_box(\'' . $this->dashboard_id . '\', \'move_up\')">';
		  	$output .= html_icon('actions/go-up.png', TEXT_MOVE_UP, $size = 'small');
		  	$output .= '</a>' . chr(10);
		  	$output .= '</td>' . chr(10);
		}
		// move button - Down
		if ($row_id < $this->get_next_row($column_id) - 1) {
		  	$output .= '<td>' . chr(10);
		  	$output .= '<a href="javascript:void(0)" onclick="return move_box(\'' . $this->dashboard_id . '\', \'move_down\')">';
		  	$output .= html_icon('actions/go-down.png', TEXT_MOVE_DOWN, $size = 'small');
		  	$output .= '</a>' . chr(10);
		  	$output .= '</td>' . chr(10);
		}
		$output .= '</tr></table>';
		return $output;
  	}

	function get_next_row($column_id = 1) {
		global $db;
		$result = $db->Execute("select max(row_id) as max_row from " . TABLE_USERS_PROFILES . "
		  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' and column_id = " . $column_id);
		return ($result->fields['max_row'] + 1);
	}

	function Upgrade($params){
		foreach ($this->default_params as $key => $value){
			if(in_array($key, $params, false)){
				$this->params[$key] =  $params[$key];
			}else{
				$this->params[$key] =  $value;
			}
		}
		$this->Update();
		return $this->params;
	}
}

/**************************************************************************************************************/
// Section 6. Class currencies
/**************************************************************************************************************/
class currencies {
  public $currencies = array();

  function __construct() {
    global $db, $messageStack;
    $currencies = $db->Execute("select * from " . TABLE_CURRENCIES);
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
	if (DEFAULT_CURRENCY == '') { // do not put this in the translation file, it is loaded before the language file is loaded.
	  $messageStack->add('You do not have a default currency set, PhreeBooks requires a default currency to operate properly! Please set the default currency in Setup -> Currencies.', 'error');
	}
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

  function get_value($code) {
    return $this->currencies[$code]['value'];
  }

  function clean_value($number, $currency_type = DEFAULT_CURRENCY) {
    // converts the number to standard float format (period as decimal, no thousands separator)
    $temp  = str_replace($this->currencies[$currency_type]['thousands_point'], '', trim($number));
    $value = str_replace($this->currencies[$currency_type]['decimal_point'], '.', $temp);
    $value = preg_replace("/[^-0-9.]+/","",$value);
    if( $value == null || $value == '') $value = 0;
    return $value;
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
}

/**************************************************************************************************************/
// Section 7. Class encryption
/**************************************************************************************************************/
class encryption {
  private $scramble1	= '';
  private $scramble2	= '';
  public  $errors		= array();
  private $adj			= 1.75;
  private $mod			= 3;

  function __construct() {
	$this->scramble1 = "! #$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~";
	$this->scramble2 = "f^jAE]okIOzU[2&q1{3`h5w_794p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0X";
	if (strlen($this->scramble1) <> strlen($this->scramble2)) {
		trigger_error('** SCRAMBLE1 is not same length as SCRAMBLE2 **', E_USER_ERROR);
	}
  }

  function encrypt_cc($params) {
  	global $messageStack;
	if (strlen($_SESSION['admin_encrypt']) < 1) {
	  $messageStack->add(ACT_NO_KEY_EXISTS,'error');
	  return false;
	}
	if ($params['number']) {
	  $params['number'] = preg_replace("/[^0-9]/", "", $params['number']);
	  $hint  = substr($params['number'], 0, 4);
	  for ($a = 0; $a < (strlen($params['number']) - 8); $a++) $hint .= '*';
	  $hint .= substr($params['number'], -4);
	  $payment = array(); // the sequence is important!
		$payment[] = $params['name'];
		$payment[] = $params['number'];
		$payment[] = $params['exp_mon'];
		$payment[] = $params['exp_year'];
		$payment[] = $params['cvv2'];
		if (isset($params['alt1'])) $payment[] = $params['alt1'];
		if (isset($params['alt2'])) $payment[] = $params['alt2'];
		$val = implode(':', $payment).':';
	  if (!$enc_value = $this->encrypt($_SESSION['admin_encrypt'], $val, 128)) {
		$messageStack->add('Encryption error - ' . implode('. ', $encrypt->errors), 'error');
		return false;
	  }
	}
	if (strlen($params['exp_year']) == 2) $params['exp_year'] = '20'.$params['exp_year'];
	$exp_date = $params['exp_year'].'-'.$params['exp_mon'].'-01';
	return array('hint' => $hint, 'encoded' => $enc_value, 'exp_date' => $exp_date);
  }

  function decrypt ($key, $source) {
	$this->errors = array();
	$fudgefactor = $this->_convertKey($key);
	if ($this->errors) return;
	if (empty($source)) {
	  $this->errors[] = 'No value has been supplied for decryption';
	  return;
	}
	$target  = null;
	$factor2 = 0;
	for ($i = 0; $i < strlen($source); $i++) {
	  $char2 = substr($source, $i, 1);
	  $num2 = strpos($this->scramble2, $char2);
	  if ($num2 === false) {
		$this->errors[] = "Source string contains an invalid character ($char2)";
		return;
	  }
	  $adj     = $this->_applyFudgeFactor($fudgefactor);
	  $factor1 = $factor2 + $adj;
	  $num1    = $num2 - round($factor1);
	  $num1    = $this->_checkRange($num1);
	  $factor2 = $factor1 + $num2;
	  $char1 = substr($this->scramble1, $num1, 1);
	  $target .= $char1;
//echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
	}
	return rtrim($target);
  }

  function encrypt ($key, $source, $sourcelen = 0) {
	$this->errors = array();
	$fudgefactor  = $this->_convertKey($key);
	if ($this->errors) return;
	if (empty($source)) {
	  $this->errors[] = 'No value has been supplied for encryption';
	  return;
	}
	while (strlen($source) < $sourcelen) $source .= ' ';
	$target = null;
	$factor2 = 0;
	for ($i = 0; $i < strlen($source); $i++) {
	  $char1 = substr($source, $i, 1);
	  $num1 = strpos($this->scramble1, $char1);
	  if ($num1 === false) {
		$this->errors[] = "Source string contains an invalid character ($char1)";
		return;
	  }
	  $adj     = $this->_applyFudgeFactor($fudgefactor);
	  $factor1 = $factor2 + $adj;
	  $num2    = round($factor1) + $num1;
	  $num2    = $this->_checkRange($num2);
	  $factor2 = $factor1 + $num2;
	  $char2   = substr($this->scramble2, $num2, 1);
	  $target .= $char2;
//echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
	}
	return $target;
  }

  function getAdjustment () {
	return $this->adj;
  }

  function getModulus () {
	return $this->mod;
  }

  function setAdjustment ($adj) {
    $this->adj = (float)$adj;
  }

  function setModulus ($mod) {
    $this->mod = (int)abs($mod);
  }

  function _applyFudgeFactor (&$fudgefactor) {
	$fudge = array_shift($fudgefactor);
	$fudge = $fudge + $this->adj;
	$fudgefactor[] = $fudge;
	if (!empty($this->mod)) if ($fudge % $this->mod == 0) $fudge = $fudge * -1;
	return $fudge;
  }

  function _checkRange ($num) {
	$num = round($num);
	$limit = strlen($this->scramble1);
	while ($num >= $limit) $num = $num - $limit;
	while ($num < 0) $num = $num + $limit;
	return $num;
  }

  function _convertKey ($key) {
	if (empty($key)) {
	  $this->errors[] = 'No value has been supplied for the encryption key';
	  return;
	}
	$array[] = strlen($key);
	$tot = 0;
	for ($i = 0; $i < strlen($key); $i++) {
	  $char = substr($key, $i, 1);
	  $num = strpos($this->scramble1, $char);
	  if ($num === false) {
		$this->errors[] = "Key contains an invalid character ($char)";
		return;
	  }
	  $array[] = $num;
	  $tot = $tot + $num;
	}
	$array[] = $tot;
	return $array;
  }
}

?>
