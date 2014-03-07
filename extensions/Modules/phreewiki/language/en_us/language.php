<?php
//short words
define('TEXT_TAGS',										'Tags');
define('TEXT_MORE',										'More');
define('TEXT_SYNC',										'Sync');
define('TEXT_IMPORT',									'Import');
define('TEXT_TWEAK',									'Tweak');
define('TEXT_PLUGINS',									'Plugins');
define('PHREEWIKI_WORD_REVISION',  						'revision');	

define('PHREEWIKI_SAVE_CHANGES_UPLOAD',	 				'Upload');
define('PHREEWIKI_SAVE_CHANGES_UPLOAD_PROMPT'	,		'upload RSS');
define('PHREEWIKI_OPTIONPANEL_AUTOUPLOAD',	 			'auto upload RSS');

//warning msg
define('PHREEWIKI_WARNING_TIDDLER_NOT_FOUND' , 			'Tiddler not found');
define('PHREEWIKI_WARNING_TIDDLER_NEED_RELOAD', 		'Tiddler have been changed since you last reload. Please copy down your changes and refresh your tiddler or pick the latest revision.');
define('PHREEWIKI_WARNING_NO_REVISION', 				'no revision available');
	
//notice
define('PHREEWIKI_NOTICE_UPLOAD_RSS', 					'upload RSS');
define('PHREEWIKI_NOTICE_UPLOAD_STORE_AREA',			'upload storeArea');
define('PHREEWIKI_NOTICE_TIMEOUT', 						'Time out! Action not complete');
define('PHREEWIKI_NOTICE_RSS_CREATED', 					'RSS created');
define('PHREEWIKI_NOTICE_UPLOAD_STORE_AREA_COMPLETE',	'storeArea upload completed this is the result');
	
//error msg
define('PHREEWIKI_ERROR_RSS_FILE_CREATE',			 	'Cannot create rss file');
define('PHREEWIKI_ERROR_RSS_FILE_WRITE', 				'Cannot write to rss file');
define('PHREEWIKI_ERROR_REVISION_NOT_FOUND', 			'revision not found');
	
//misc
define('PHREEWIKI_MISC_REVISION_TOOLTIP', 				'view revision of this tiddler');
define('PHREEWIKI_NO_TITLE', 							'no title');
define('PHREEWIKI_CAN_NOT_PROCESS', 					'can not process this request');
define('PHREEWIKI_WARNING_IN_LOCKED_ARRAY',				'Tiddler is in locked array your can\'t delete or modify this tiddler');
//javascript translations
define('PHREEWIKI_TEXT_SAVE_TOOLTIP',					'Save your changes to this TiddlyWiki');
define('PHREEWIKI_TEXT_SYNC_TOOLTIP',					'Synchronise changes with other TiddlyWiki files and servers');
define('PHREEWIKI_TEXT_IMPORT_TOOLTIP',					'Import tiddlers and plugins from other TiddlyWiki files and servers');
define('PHREEWIKI_TEXT_TWEAK_TOOLTIP',					'Tweak the appearance and behaviour of TiddlyWiki');
define('PHREEWIKI_TEXT_PLUGINS_TOOLTIP',				'Manage installed plugins');
define('PHREEWIKI_CONFIG_TEXT_USERNAME',				'Username for signing your edits');
define('PHREEWIKI_CONFIG_REGEXPSEARCH',					'Enable regular expressions for searches');			
define('PHREEWIKI_CONFIG_CASE_SENSITIVE_SEARCH',		'Case-sensitive searching');
define('PHREEWIKI_CONFIG_ANIMATE',						'Enable animations');
define('PHREEWIKI_CONFIG_KEEP_BACKUP',					'Keep backup file when saving changes');
define('PHREEWIKI_CONFIG_AUTO_SAVE',					'Automatically save changes');
define('PHREEWIKI_CONFIG_GENERATE_RSS',					'Generate an RSS feed when saving changes');
define('PHREEWIKI_CONFIG_EMPTY_TEMPLATE',				'Generate an empty template when saving changes');
define('PHREEWIKI_CONFIG_LINKS_NEW_WINDOW',				'Open external links in a new window');
define('PHREEWIKI_CONFIG_TROGGLE_LINKS',				'Clicking on links to open tiddlers causes them to close');
define('PHREEWIKI_CONFIG_HIDE_EDITING_HTTP',			'Hide editing features when viewed over HTTP');
define('PHREEWIKI_CONFIG_DONT_UPDATE',					'Don\'t update modifier username and date when editing tiddlers');
define('PHREEWIKI_CONFIG_CONFIRM_DELETE',				'Require confirmation before deleting tiddlers');
define('PHREEWIKI_CONFIG_INSERT_TABS',					'Use the tab key to insert tab characters instead of moving between fields');
define('PHREEWIKI_CONFIG_BACKUP_FOLDER',				'Name of folder to use for backups');
define('PHREEWIKI_CONFIG_MAX_EDIT_ROWS',				'Maximum number of rows in edit boxes');
define('PHREEWIKI_CONFIG_DEFAULT_CHARACTER_SET',		'Default character set for saving changes (Firefox/Mozilla only)');
//javascript config.messages
define('PHREEWIKI_CONFIG_ERROR',						'Problems were encountered loading plugins. See PluginManager for details');
define('PHREEWIKI_CONFIG_PLUGIN_DISABLED',				'Not executed because disabled via \'systemConfigDisable\' tag');
define('PHREEWIKI_CONFIG_PLUGIN_FORCED',				'Executed because forced via \'systemConfigForce\' tag');
define('PHREEWIKI_CONFIG_PLUGIN_VERSION_ERROR',			'Not executed because this plugin needs a newer version of TiddlyWiki');
define('PHREEWIKI_CONFIG_NOTHING_SELECTED',				'Nothing is selected. You must select one or more items first');
define('PHREEWIKI_CONFIG_SAVED_SNAPSHOT',				'It appears that this TiddlyWiki has been incorrectly saved.');
define('PHREEWIKI_CONFIG_SUBTITLE',						'(unknown)');
define('PHREEWIKI_CONFIG_UNDEFINED_TIDDLER_TOOL_TIP',	'Tiddler \'%0\' doesn\'t yet exist');
define('PHREEWIKI_CONFIG_SHADOWED_TIDDLER_TOOL_TIP',	'Tiddler \'%0\' doesn\'t yet exist, but has a pre-defined shadow value');
define('PHREEWIKI_CONFIG_ERROR',						'');


?>