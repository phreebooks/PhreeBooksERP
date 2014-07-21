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
//  Path: /modules/phreedom/functions/translator.php
//

function build_mod_list() {
  $sel_modules = array(
    array('id' => 'all',     'text' => TEXT_ALL),
    array('id' => 'install', 'text' => 'install'),
    array('id' => 'soap',    'text' => 'soap'),
  );
  $dirs = scandir(DIR_FS_MODULES);
  foreach ($dirs as $value) {
    if ($value == '.' || $value == '..') continue;
    if (is_dir(DIR_FS_MODULES . $value . '/dashboards')) { // there are dashboards to load languages
	  $meths = scandir(DIR_FS_MODULES . $value . '/dashboards');
	  foreach ($meths as $val) {
	    if ($val == '.' || $val == '..') continue;
	    $sel_modules[] = array('id' => $value.'-'.$val, 'text' => $value.'-'.$val);
	  }
	}
	if (is_dir(DIR_FS_MODULES . $value . '/methods')) { // there are methods to load languages
	  $meths = scandir(DIR_FS_MODULES . $value . '/methods');
	  foreach ($meths as $val) {
		if ($val == '.' || $val == '..') continue;
		$sel_modules[] = array('id' => $value.'-'.$val, 'text' => $value.'-'.$val);
	  }
	}
	$sel_modules[] = array('id' => $value, 'text' => $value);
  }
  return $sel_modules;
}

function build_ver_list() {
  global $db;
  $sel_version = array(
    array('id' => '0', 'text' => TEXT_ALL),
    array('id' => 'L', 'text' => TEXT_LATEST),
  );
  $result = $db->Execute("select distinct version from " . TABLE_TRANSLATOR . " order by version DESC");
  while (!$result->EOF) {
    $sel_version[] = array('id' => $result->fields['version'], 'text' => $result->fields['version']);
    $result->MoveNext();
  }
  return $sel_version;
}

function build_lang_list() {
  global $db;
  $sel_language = array(array('id' => '0', 'text' => TEXT_ALL));
  $result = $db->Execute("select distinct language from " . TABLE_TRANSLATOR);
  while (!$result->EOF) {
    $sel_language[] = array('id' => $result->fields['language'], 'text' => $result->fields['language']);
    $result->MoveNext();
  }
  return $sel_language;
}

function build_trans_list() {
  $sel_translated = array(
    array('id' => '0', 'text' => TEXT_ALL),
    array('id' => 'n', 'text' => TEXT_NO),
    array('id' => 'y', 'text' => TEXT_YES),
  );
  return $sel_translated;
}
?>