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
//  Path: /includes/classes/toolbar.php
//
namespace core\classes;
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
	$this->id                  = $id;
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
	  	if ($icon['show']) $output .= html_icon($icon['icon'], $icon['text'], $this->icon_size, "id ='tb_icon_$id' ". $icon['params']) . "\n";
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
