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
//  Initially Written By: Harry Lu @ 2009/08/01
//  Path: /modules/payment/methods/linkpoint/pages/ccreview/pre_process.php
//
$security_level = validate_user(SECURITY_ID_PAY_BILLS);
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$error = false;
history_filter('linkpoint');
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
// build the list header
if (!isset($_REQUEST['sf'])) $_REQUEST['sf'] = 'post_date'; 
if (!isset($_REQUEST['so'])) $_REQUEST['so'] = 'desc';// default to descending by postdate

$heading_array = array(
	'id'            => GEN_LINK_POINT_ID,	
	'short_name'    => GEN_CUSTOMER,
	'contact_last'  => GEN_LAST_NAME,
	'contact_first' => GEN_FIRST_NAME,
	'empty_1'       => '',
	'empty_2'       => '',
	'empty_3'       => '',
	'empty_4'       => '',
	'first_date'    => GEN_ACCOUNT_CREATED,
	'empty_5'       => '',	
);
		
$result      = html_heading_bar($heading_array, $extra_headings=array());
$list_header = $result['html_code'];
$disp_order  = $result['disp_order'];

// build the list for the page selected
if ($acct_period == 'all') {
	$period_filter = '';
} else {
	$periods       = $db->Execute("select * from " . TABLE_ACCOUNTING_PERIODS . " where period = $acct_period");
	$start_date    = $periods->fields['start_date'];
	$end_date      =  $periods->fields['end_date'];
	$period_filter = " and DATE_FORMAT(lp.date_added,'%Y-%m-%d' ) BETWEEN  '$start_date' AND '$end_date' "; 
}

if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
    $search_fields = array('short_name');
    // hook for inserting new search fields to the query criteria.  
    $search = ' and (' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
} else {
   $search = '';
}

$query_raw  = "select SQL_CALC_FOUND_ROWS lp.*, c.short_name, c.contact_last, c.contact_first, c.first_date 
                from  " . TABLE_CONTACTS . " c,  linkpoint_api lp 
                where c.id = lp.customer_id $search  $period_filter order by $disp_order ";
$customers = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
// the splitPageResults should be run directly after the query that contains SQL_CALC_FOUND_ROWS
$query_split  = new splitPageResults($_REQUEST['list'], '');						  
if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
	$_REQUEST['list'] = $query_split->current_page_number;
	$query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	$query_split      = new splitPageResults($_REQUEST['list'], '');
}
history_save('linkpoint');

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_BANKING_LINK_POINT_CC_REVIEW);

?>