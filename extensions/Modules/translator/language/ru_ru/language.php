<?php
// +-----------------------------------------------------------------+
// Phreedom Language Translation File
// Generated: 2014-11-23 10:04:29
// Module/Method: translator
// ISO Language: ru_ru
// Version: 3.7
// +-----------------------------------------------------------------+
// Path: //modules/translator/language/ru_ru/language.php

define('BOX_TRANSLATOR_MAINTAIN','Переводчик');
define('TEXT_NEW_TRANSLATION','новый перевод');
define('TEXT_IMPORT_TRANSLATION','Import Translation');
define('TEXT_UPLOAD_TRANSLATION','Upload Translation');
define('TEXT_IMPORT_CURRENT_LANGUAGE','Import from Current Installation');
define('TEXT_UPLOAD_LANGUAGE_FILE','Upload from Zipped File');
define('TEXT_EDIT_TRANSLATION','Translate Module');
define('TEXT_UPLOAD','Загрузить');
define('TEXT_LANGUAGE_CODE','ISO код');
define('TEXT_TRANSLATION','Перед');
define('TEXT_TRANSLATIONS','Переводы');
define('TEXT_TRANSLATED','Переведено');
define('TEXT_CREATE_NEW_TRANSLATION','Создать новый перевод');
define('TRANSLATOR_NEW_DESC','This form creates a new translation release. If you want translation guesses from prior releases to override the source language check the Overwrite box and enter an ISO language to use. Note that this language must be loaded into the translator database. The source module and language must also be in the translator database. (Release # will be created automatically)');
define('TRANSLATOR_NEW_SOURCE','Source Module:');
define('TEXT_SOURCE_LANGUAGE','Source Language:');
define('TRANSLATOR_NEW_OVERRIDE','Then overwrite (if available) from installed language:');
define('TRANSLATOR_IMPORT_DESC','This form imports loaded languages from the currently installed module or modules into the translator database. If the install module is selected and the directory has been renamed, the new directory needs to be entered into the form below.');
define('TRANSLATOR_ISO_IMPORT','ISO language to import (form xx_xx):');
define('TRANSLATOR_MODULE_IMPORT','Module name to import:');
define('TRANSLATOR_INSTALL_IMPORT','Directory name of install directory (if moved after install):');
define('TRANSLATOR_UPLOAD_DESC','This form will upload a zipped language file and import all defines into the database. It should be used for assisting in upconverting older versions to new or modifying translations to new languages.');
define('TRANSLATOR_ISO_CREATE','ISO language to create (form xx_xx):');
define('TRANSLATOR_MODULE_CREATE','Module to assign translation to:');
define('TRANSLATOR_RELEASE_CREATE','Release number to create:');
define('TRANSLATOR_UPLOAD_ZIPFILE','Select a zipped file to upload and insert into the translator database:');
define('MESSAGE_DELETE_TRANSLATION','Точно хотите удалить перевод?');
define('TEXT_CONSTANT','Defined Constant');
define('TEXT_DEFAULT_TRANSLATION','Current Translation');
define('TEXT_STATS_VALUES','%s of %s translated (%s percent)');
define('TEXT_TRANSLATIONS_SAVED','Translation records saved.');
define('TRANSLATION_HEADER','Phreedom Language Translation File');
define('TRANS_ERROR_NO_SOURCE','No available versions of the source language were found! Please import the source language.');
define('TRANS_ERROR_DUPLICATE','Error importing module: %s, language: %s, version: %s, the language file already exists. Import skipped!');

?>
