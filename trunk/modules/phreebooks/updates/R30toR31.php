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
//  Path: /modules/phreebooks/install/updates/R30toR31.php
//
// This script updates PhreeBooks release 3.0 to release 3.1, it is included as part of the update script
// *************************** IMPORTANT UPDATE INFORMATION *********************************//

//********************************* END OF IMPORTANT ****************************************//

if (db_field_exists(TABLE_CURRENT_STATUS, 'next_po_desc'))       $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_po_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_so_desc'))       $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_so_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_inv_desc'))      $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_inv_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_check_desc'))    $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_check_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_deposit_desc'))  $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_deposit_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_cm_desc'))       $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cm_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_vcm_desc'))      $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vcm_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_ap_quote_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_ap_quote_desc");
if (db_field_exists(TABLE_CURRENT_STATUS, 'next_ar_quote_desc')) $db->Execute("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_ar_quote_desc");

?>