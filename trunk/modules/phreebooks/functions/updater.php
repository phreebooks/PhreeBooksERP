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
//  Path: /modules/phreebooks/functions/updater.php
//

function execute_upgrade($db_release) {
  global $db, $messageStack;
  $code_release = MODULE_PHREEBOOKS_VERSION;
//  $db_release   = PROJECT_DB_VERSION_MAJOR . '.' . PROJECT_DB_VERSION_MINOR;

  if ($db_release <= '1.3.1') { // upgrade db to Release 1.4
	include (DIR_FS_MODULES . 'phreebooks/updates/R131toR14.php');
	update_version_db('1', '4');
	$db_release = '1.4';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '1.4') { // upgrade db to Release 1.5
	include (DIR_FS_MODULES . 'phreebooks/updates/R14toR15.php');
	update_version_db('1', '5');
	$db_release = '1.5';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '1.5') { // upgrade db to Release 1.6
	include (DIR_FS_MODULES . 'phreebooks/updates/R15toR16.php');
	update_version_db('1', '6');
	$db_release = '1.6';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '1.6') { // upgrade db to Release 1.7
	include (DIR_FS_MODULES . 'phreebooks/updates/R16toR17.php');
	update_version_db('1', '7');
	$db_release = '1.7';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '1.7') { // upgrade db to Release 1.8
	include (DIR_FS_MODULES . 'phreebooks/updates/R17toR18.php');
	update_version_db('1', '8');
	$db_release = '1.8';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '1.8') { // upgrade db to Release 1.9
	include (DIR_FS_MODULES . 'phreebooks/updates/R18toR19.php');
	update_version_db('1', '9');
	$db_release = '1.9';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '1.9' || $db_release <= '1.9p') { // upgrade db to Release 2.0
	include (DIR_FS_MODULES . 'phreebooks/updates/R19toR20.php');
	update_version_db('2', '0');
	$db_release = '2.0';
    if ($code_release == $db_release) return false;
  }

  if ($db_release <= '2.0') { // upgrade db to Release 2.1
	include (DIR_FS_MODULES . 'phreebooks/updates/R20toR21.php');
	update_version_db('2', '1');
	$db_release = '2.1';
    if ($code_release == $db_release) return false;
  }
  return true; // error
}

function update_version_db($major = PROJECT_VERSION_MAJOR, $minor = PROJECT_VERSION_MINOR) {
  global $db;
  $sql = "update " . TABLE_PROJECT_VERSION . " set 
	project_version_major = '" . $major . "', 
	project_version_minor = '" . $minor . "' 
	where project_version_key = 'PhreeBooks Database'";
  $result = $db->Execute($sql);
}
?>