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
//  Path: /modules/assets/defaults.php
//
// default directory for contact attachments
define('ASSETS_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $_SESSION['company'] . '/assets/main/');
// the asset type indexes should not be changed or the asset module won't work.
$assets_types = array(
  'vh' => TEXT_VEHICLE,
  'bd' => TEXT_BUILDING,
  'fn' => TEXT_FURNITURE,
  'pc' => TEXT_COMPUTER,
  'te' => TEXT_TOOLS_AND_EQUIPMENT,
  'ld' => TEXT_LAND,
  'sw' => TEXT_SOFTWARE,
);
?>