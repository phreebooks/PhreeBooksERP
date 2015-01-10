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
//  Path: /modules/phreedom/functions/translator.php
//

function build_mod_list() {
	global $admin;
	$sel_modules = array(
	  array('id' => 'all',     'text' => TEXT_ALL),
	  array('id' => 'install', 'text' => 'install'),
	  array('id' => 'soap',    'text' => 'soap'),
	);
	$dirs = @scandir(DIR_FS_MODULES);
	if($dirs === false) throw new \core\classes\userException("couldn't read or find directory ".DIR_FS_MODULES);
	foreach($basis->classes as $key => $class) {
		$sel_modules[] = array('id' => $key, 'text' => $key);
		foreach ($class->methods as $method_key => $method) 		 $sel_modules[] = array('id' => $key.'-'.$method_key, 'text' => $key.'-'.$method_key);
		foreach ($class->dashboards as $dashboard_key => $dashboard) $sel_modules[] = array('id' => $key.'-'.$dashboard_key, 'text' => $key.'-'.$dashboard_key);

	}
  	return $sel_modules;
}

function build_ver_list() {
  global $admin;
  $sel_version = array(
    array('id' => '0', 'text' => TEXT_ALL),
    array('id' => 'L', 'text' => TEXT_LATEST),
  );
  $result = $admin->DataBase->query("select distinct version from " . TABLE_TRANSLATOR . " order by version DESC");
  while (!$result->EOF) {
    $sel_version[] = array('id' => $result->fields['version'], 'text' => $result->fields['version']);
    $result->MoveNext();
  }
  return $sel_version;
}

function build_lang_list() {
  global $admin;
  $sel_language = array(array('id' => '0', 'text' => TEXT_ALL));
  $result = $admin->DataBase->query("select distinct language from " . TABLE_TRANSLATOR);
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