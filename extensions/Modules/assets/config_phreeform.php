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
//  Path: /modules/assets/config_phreeform.php
//

// Extra processing on reports/forms (add to the processing pull down menu)
$FormProcessing['NewUsed'] = sprintf(TEXT_NEW_ARGS, " / "  TEXT_USED);
// Extra form processing operations
function pf_process_assets($strData, $Process) {
  switch ($Process) {
	case 'NewUsed': return ($strData == 'u') ? TEXT_USED : TEXT_NEW;
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}
?>