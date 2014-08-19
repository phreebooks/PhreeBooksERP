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
//  Path: /modules/translator/language/en_us/language.php
//
// Headings
define('BOX_TRANSLATOR_MAINTAIN','Translator Assistant');
define('TEXT_NEW_TRANSLATION','New Translation');
define('TEXT_IMPORT_TRANSLATION','Import Translation');
define('TEXT_EXPORT_TRANSLATION','Export Translation');
define('TEXT_UPLOAD_TRANSLATION','Upload Translation');
define('TEXT_IMPORT_CURRENT_LANGUAGE','Import from Current Installation');
define('TEXT_EXPORT_CURRENT_LANGUAGE','Export All for a language and version');
define('TEXT_UPLOAD_FROM_ZIPPED_FILE','Upload from Zipped File');
define('TEXT_TRANSLATE_MODULE','Translate Module');
// General defines
define('TEXT_CHECK_ALL_CHECKBOXES','Check All Checkboxes');
define('TEXT_LANGUAGE','Language');
define('TEXT_LATEST','Latest');
define('TEXT_UPLOAD','Upload');
define('TEXT_LANGUAGE_CODE','ISO Language Code');
define('TEXT_TRANSLATION','Translation');
define('TEXT_TRANSLATIONS','Translations');
define('TEXT_TRANSLATED','Translated');
define('TEXT_CREATE_NEW_TRANSLATION','Create New Translation');
define('TRANSLATOR_NEW_DESC','This form creates a new translation release. If you want translation guesses from prior releases to override the source language check the Overwrite box and enter an ISO language to use. Note that this language must be loaded into the translator database. The source module and language must also be in the translator database. (Release # will be created automatically)');
define('TEXT_SOURCE_MODULE','Source Module');
define('TEXT_SOURCE_LANGUAGE','Source Language');
define('TRANSLATOR_NEW_OVERRIDE','Then overwrite (if available) from installed language:');
define('TRANSLATOR_IMPORT_DESC','This page imports loaded languages from the currently installed module or modules into the translator database. If the install module is selected and the directory has been renamed, the new directory needs to be entered into the form below.');
define('TRANSLATOR_EXPORT_DESC','This page exports all modules from a given translated language and version to a single .zip file.');
define('TRANSLATOR_ISO_IMPORT','ISO language to import (form xx_xx):');
define('TRANSLATOR_ISO_EXPORT','ISO language to export:');
define('TEXT_MODULE_NAME_TO_IMPORT','Module name to import:');
define('TRANSLATOR_INSTALL_IMPORT','Directory name of install directory (if moved after install):');
define('TRANSLATOR_UPLOAD_DESC','This form will upload a zipped language file and import all defines into the database. It should be used for assisting in upconverting older versions to new or modifying translations to new languages.');
define('TRANSLATOR_ISO_CREATE','ISO language to create (form xx_xx):');
define('TRANSLATOR_MODULE_CREATE','Module to assign translation to:');
define('TEXT_RELEASE_NUMBER_TO_CREATE','Release number to create:');
define('TRANSLATOR_UPLOAD_ZIPFILE','Select a zipped file to upload and insert into the translator database:');
define('MESSAGE_DELETE_TRANSLATION','Are you sure you want to delete this translation?');
define('TEXT_DEFINED_CONSTANT','Defined Constant');
define('TEXT_DEFAULT_TRANSLATION','Current Translation');
define('TEXT_STATS_VALUES','%s of %s translated (%s percent)');
define('TEXT_TRANSLATION_RECORDS_SAVED','Translation records saved.');
define('TEXT_PHREEDOM_LANGUAGE_TRANSLATION_FILE','Phreedom Language Translation File');
// Error Messages
define('TRANS_ERROR_NO_SOURCE','No available versions of the source language were found! Please import the source language.');
// Javascrpt defines
?>
