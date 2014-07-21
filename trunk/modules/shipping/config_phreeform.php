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
//  Path: /modules/shipping/config_phreeform.php
// 

$FormProcessing['ship_name'] = PB_PF_SHIP_METHOD;
// Extra form processing operations
function pf_process_shipping($strData, $Process) {
  switch ($Process) {
	case "ship_name": return shipping_get_name($strData);
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}

function shipping_get_name($id) {
  if (!$id) return '';
  $parts = explode(':', $id);
  if (!$parts[0]) return '';
  load_specific_method('shipping', $parts[0]);
//echo 'id = ' . $id . ' and method = ' . constant($parts[0] . '_' . $parts[1]) . '<br>';
  return defined($parts[0] . '_' . $parts[1]) ? constant($parts[0] . '_' . $parts[1]) : $id;
}

?>