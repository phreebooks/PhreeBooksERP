
<script>
// variables 

config.options.chkSaveBackups = false;	//disable save backup
config.options.chkAutoSave = false;		//disable autosave
config.options.chkRegExpSearch= <?php echo PHREEWIKI_REG_EXP_SEARCH ? "true;" : "false;"; ?>;
config.options.chkCaseSensitiveSearch= <?php echo PHREEWIKI_CASE_SENSITIVE_SEARCH ? "true;" : "false;"; ?>;
config.options.chkAnimate= <?php echo PHREEWIKI_ANIMATE ? "true;" : "false;";; ?>;
config.options.chkGenerateAnRssFeed= <?php echo PHREEWIKI_GENERATE_RSS ? "true;" : "false;";?>;
config.options.chkOpenInNewWindow= <?php echo PHREEWIKI_OPEN_NEW_WINDOW ? "true;" : "false;";?>;
config.options.chkToggleLinks= <?php echo PHREEWIKI_TROGGLE_LINKS ? "true;" : "false;"; ?>;
config.options.chkConfirmDelete= <?php echo PHREEWIKI_CONFIRM_DELETE ? "true;" : "false;";?>;
config.options.chkInsertTabs= <?php echo PHREEWIKI_INSERT_TABS? "true;" : "false;";?>;
config.options.txtMaxEditRows=<?php echo defined(PHREEWIKI_MAX_EDIT_ROWS)? PHREEWIKI_MAX_EDIT_ROWS: 30; ?>;
<?php
if(isset($_SESSION['admin_security'][SECURITY_PHREEWIKI_MGT]) && $_SESSION['admin_security'][SECURITY_PHREEWIKI_MGT] < 2 ){
	echo 'config.options.chkHttpReadOnly = true;'.chr(13);
}else{
	echo 'config.options.chkHttpReadOnly = false;'.chr(13);
}
if(isset($_SESSION['admin_security'][SECURITY_ID_CONFIGURATION]) && $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION] != 0){
	echo 'var is_admin = true;'.chr(13);
}else{
	echo 'var is_admin = false;'.chr(13);
}

?>
//{{{
//--
//-- Translateable strings
//--

// Strings in "double quotes" should be translated; strings in 'single quotes' should be left alone

config.locale = "en"; // W3C language tag

if (config.options.txtUserName == 'YourName') // do not translate this line, but do translate the next line
	merge(config.options,{txtUserName: "YourName"});

merge(config.tasks,{
	save:       {text: "<?php echo TEXT_SAVE; 	?>", tooltip: "<?php echo PHREEWIKI_TEXT_SAVE_TOOLTIP;		?>", action: saveChanges},
	sync:       {text: "<?php echo TEXT_SYNC; 	?>", tooltip: "<?php echo PHREEWIKI_TEXT_SYNC_TOOLTIP;		?>", content: '<<sync>>'},
	importTask: {text: "<?php echo TEXT_IMPORT; ?>", tooltip: "<?php echo PHREEWIKI_TEXT_IMPORT_TOOLTIP;	?>", content: '<<importTiddlers>>'},
	tweak:      {text: "<?php echo TEXT_TWEAK;	?>", tooltip: "<?php echo PHREEWIKI_TEXT_TWEAK_TOOLTIP;		?>", content: '<<options>>'},
	plugins:    {text: "<?php echo TEXT_PLUGINS;?>", tooltip: "<?php echo PHREEWIKI_TEXT_PLUGINS_TOOLTIP;	?>", content: '<<plugins>>'}
});

// Options that can be set in the options panel and/or cookies
merge(config.optionsDesc,{
	txtUserName: "<?php 		 	echo PHREEWIKI_CONFIG_TEXT_USERNAME;?>",
	chkRegExpSearch: "<?php 		echo PHREEWIKI_CONFIG_REGEXPSEARCH;?>",
	chkCaseSensitiveSearch: "<?php	echo PHREEWIKI_CONFIG_CASE_SENSITIVE_SEARCH;?>",
	chkAnimate: "<?php 				echo PHREEWIKI_CONFIG_ANIMATE;?>",
	chkSaveBackups: "<?php         	echo PHREEWIKI_CONFIG_KEEP_BACKUP; ?>",
	chkAutoSave: "<?php            	echo PHREEWIKI_CONFIG_AUTO_SAVE;?>",
	chkGenerateAnRssFeed: "<?php  	echo PHREEWIKI_CONFIG_GENERATE_RSS;?>",
	chkSaveEmptyTemplate: "<?php   	echo PHREEWIKI_CONFIG_EMPTY_TEMPLATE;?>",
	chkOpenInNewWindow: "<?php     	echo PHREEWIKI_CONFIG_LINKS_NEW_WINDOW;?>",
	chkToggleLinks: "<?php 			echo PHREEWIKI_CONFIG_TROGGLE_LINKS;?>",
	chkHttpReadOnly: "<?php 		echo PHREEWIKI_CONFIG_HIDE_EDITING_HTTP;?>",
	chkForceMinorUpdate: "<?php 	echo PHREEWIKI_CONFIG_DONT_UPDATE;?>",
	chkConfirmDelete: "<?php 		echo PHREEWIKI_CONFIG_CONFIRM_DELETE;?>",
	chkInsertTabs: "<?php 			echo PHREEWIKI_CONFIG_INSERT_TABS;?>",
	txtBackupFolder: "<?php 		echo PHREEWIKI_CONFIG_BACKUP_FOLDER;?>",
	txtMaxEditRows: "<?php 			echo PHREEWIKI_CONFIG_MAX_EDIT_ROWS;?>",
	txtFileSystemCharSet: "<?php   	echo PHREEWIKI_CONFIG_DEFAULT_CHARACTER_SET;?>"});

merge(config.messages,{
	customConfigError: "<?php 		echo PHREEWIKI_CONFIG_ERROR;?>",
	pluginError: "<?php 			echo TEXT_ERROR;?>: %0",
	pluginDisabled: "<?php 			echo PHREEWIKI_CONFIG_PLUGIN_DISABLED;?>",
	pluginForced: "<?				echo PHREEWIKI_CONFIG_PLUGIN_FORCED;?>",
	pluginVersionError: "<?			echo PHREEWIKI_CONFIG_PLUGIN_VERSION_ERROR;?>",
	nothingSelected: "<?			echo PHREEWIKI_CONFIG_NOTHING_SELECTED;?>",
	savedSnapshotError: "<?			echo PHREEWIKI_CONFIG_SAVED_SNAPSHOT;?>",
	subtitleUnknown: "<?			echo PHREEWIKI_CONFIG_SUBTITLE;?>",
	undefinedTiddlerToolTip: "<?	echo PHREEWIKI_CONFIG_UNDEFINED_TIDDLER_TOOL_TIP?>",
	shadowedTiddlerToolTip: "<?		echo PHREEWIKI_CONFIG_SHADOWED_TIDDLER_TOOL_TIP;?>",
	tiddlerLinkTooltip: "%0 - %1, %2",
	externalLinkTooltip: "External link to %0",
	noTags: "There are no tagged tiddlers",
	notFileUrlError: "You need to save this TiddlyWiki to a file before you can save changes",
	cantSaveError: "It's not possible to save changes. Possible reasons include:\n- your browser doesn't support saving (Firefox, Internet Explorer, Safari and Opera all work if properly configured)\n- the pathname to your TiddlyWiki file contains illegal characters\n- the TiddlyWiki HTML file has been moved or renamed",
	invalidFileError: "The original file '%0' does not appear to be a valid TiddlyWiki",
	backupSaved: "Backup saved",
	backupFailed: "Failed to save backup file",
	rssSaved: "RSS feed saved",
	rssFailed: "Failed to save RSS feed file",
	emptySaved: "Empty template saved",
	emptyFailed: "Failed to save empty template file",
	mainSaved: "Main TiddlyWiki file saved",
	mainFailed: "Failed to save main TiddlyWiki file. Your changes have not been saved",
	macroError: "Error in macro <<\%0>>",
	macroErrorDetails: "Error while executing macro <<\%0>>:\n%1",
	missingMacro: "No such macro",
	overwriteWarning: "A tiddler named '%0' already exists. Choose OK to overwrite it",
	unsavedChangesWarning: "WARNING! There are unsaved changes in TiddlyWiki\n\nChoose OK to save\nChoose CANCEL to discard",
	confirmExit: "--------------------------------\n\nThere are unsaved changes in TiddlyWiki. If you continue you will lose those changes\n\n--------------------------------",
	saveInstructions: "SaveChanges",
	unsupportedTWFormat: "Unsupported TiddlyWiki format '%0'",
	tiddlerSaveError: "Error when saving tiddler '%0'",
	tiddlerLoadError: "Error when loading tiddler '%0'",
	wrongSaveFormat: "Cannot save with storage format '%0'. Using standard format for save.",
	invalidFieldName: "Invalid field name %0",
	fieldCannotBeChanged: "Field '%0' cannot be changed",
	loadingMissingTiddler: "Attempting to retrieve the tiddler '%0' from the '%1' server at:\n\n'%2' in the workspace '%3'"});

merge(config.messages.messageClose,{
	text: "<?php echo TEXT_CLOSE;?>",
	tooltip: "close this message area"});

config.messages.backstage = {
	open: {text: "backstage", tooltip: "Open the backstage area to perform authoring and editing tasks"},
	close: {text: "<?php echo TEXT_CLOSE;?>", tooltip: "Close the backstage area"},
	prompt: "backstage: ",
	decal: {
		edit: {text: "<?php echo TEXT_EDIT;?>", tooltip: "Edit the tiddler '%0'"}
	}
};

config.messages.listView = {
	tiddlerTooltip: "Click for the full text of this tiddler",
	previewUnavailable: "(preview not available)"
};

config.messages.dates.months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November","December"];
config.messages.dates.days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
config.messages.dates.shortMonths = ["<?php echo TEXT_JAN?>", "<?php echo TEXT_FEB?>", "<?php echo TEXT_MAR?>", "<?php echo TEXT_APR?>", "<?php echo TEXT_MAY?>", "<?php echo TEXT_JUN?>", "<?php echo TEXT_JUL?>", "<?php echo TEXT_AUG?>", "<?php echo TEXT_SEP?>", "<?php echo TEXT_OCT?>", "<?php echo TEXT_NOV?>", "<?php echo TEXT_DEC?>"];
config.messages.dates.shortDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
// suffixes for dates, eg "1st","2nd","3rd"..."30th","31st"
config.messages.dates.daySuffixes = ["st","nd","rd","th","th","th","th","th","th","th",
		"th","th","th","th","th","th","th","th","th","th",
		"st","nd","rd","th","th","th","th","th","th","th",
		"st"];
config.messages.dates.am = "am";
config.messages.dates.pm = "pm";

merge(config.messages.tiddlerPopup,{
	});

merge(config.views.wikified.tag,{
	labelNoTags: "<?php echo TEXT_NO.' '.TEXT_TAGS;?>",
	labelTags: "<?php echo TEXT_TAGS;?>: ",
	openTag: "Open tag '%0'",
	tooltip: "Show tiddlers tagged with '%0'",
	openAllText: "Open all",
	openAllTooltip: "Open all of these tiddlers",
	popupNone: "No other tiddlers tagged with '%0'"});

merge(config.views.wikified,{
	defaultText: "The tiddler '%0' doesn't yet exist. Double-click to create it",
	defaultModifier: "(missing)",
	shadowModifier: "(built-in shadow tiddler)",
	dateFormat: "DD MMM YYYY", // use this to change the date format for your locale, eg "YYYY MMM DD", do not translate the Y, M or D
	createdPrompt: "created"});

merge(config.views.editor,{
	tagPrompt: "Type tags separated with spaces, [[use double square brackets]] if necessary, or add existing",
	defaultText: "Type the text for '%0'"});

merge(config.views.editor.tagChooser,{
	text: "<?php echo TEXT_TAGS;?>",
	tooltip: "Choose existing tags to add to this tiddler",
	popupNone: "There are no tags defined",
	tagTooltip: "Add the tag '%0'"});

merge(config.messages,{
	sizeTemplates:
		[
		{unit: 1024*1024*1024, template: "%0\u00a0GB"},
		{unit: 1024*1024, template: "%0\u00a0MB"},
		{unit: 1024, template: "%0\u00a0KB"},
		{unit: 1, template: "%0\u00a0B"}
		]});

merge(config.macros.search,{
	label:  		"<?php echo TEXT_SEARCH; ?>",
	prompt: 		"<?php echo TEXT_SEARCH; ?>",
	accessKey: 	"F",
	successMsg: "%0 tiddlers found matching %1",
	failureMsg: "No tiddlers found matching %0"});

merge(config.macros.tagging,{
	label: "tagging: ",
	labelNotTag: "not tagging",
	tooltip: "List of tiddlers tagged with '%0'"});

merge(config.macros.timeline,{
	dateFormat: "DD MMM YYYY"});// use this to change the date format for your locale, eg "YYYY MMM DD", do not translate the Y, M or D

merge(config.macros.allTags,{
	tooltip: "Show tiddlers tagged with '%0'",
	noTags: "There are no tagged tiddlers"});

config.macros.list.all.prompt = "All tiddlers in alphabetical order";
config.macros.list.missing.prompt = "Tiddlers that have links to them but are not defined";
config.macros.list.orphans.prompt = "Tiddlers that are not linked to from any other tiddlers";
config.macros.list.shadowed.prompt = "Tiddlers shadowed with default contents";
config.macros.list.touched.prompt = "Tiddlers that have been modified locally";

merge(config.macros.closeAll,{
	label: "close all",
	prompt: "Close all displayed tiddlers (except any that are being edited)"});

merge(config.macros.permaview,{
	label: "permaview",
	prompt: "Link to an URL that retrieves all the currently displayed tiddlers"});

merge(config.macros.saveChanges,{
	label: "save changes",
	prompt: "Save all tiddlers to create a new TiddlyWiki",
	accessKey: "S"});

merge(config.macros.newTiddler,{
	label: "<?php echo TEXT_NEW ?> tiddler",
	prompt: "Create a new tiddler",
	title: "<?php echo TEXT_NEW ?> Tiddler",
	accessKey: "N"});

merge(config.macros.newJournal,{
	label: "<?php echo TEXT_NEW ?> journal",
	prompt: "Create a new tiddler from the current date and time",
	accessKey: "J"});

merge(config.macros.options,{
	wizardTitle: "Tweak advanced options",
	step1Title: "These options are saved in cookies in your browser",
	step1Html: "<input type='hidden' name='markList'></input><br><input type='checkbox' checked='false' name='chkUnknown'>Show unknown options</input>",
	unknownDescription: "//(unknown)//",
	listViewTemplate: {
		columns: [
			{name: 'Option', field: 'option', title: "Option", type: 'String'},
			{name: 'Description', field: 'description', title: "Description", type: 'WikiText'},
			{name: 'Name', field: 'name', title: "Name", type: 'String'}
			],
		rowClasses: [
			{className: 'lowlight', field: 'lowlight'} 
			]}
	});

merge(config.macros.plugins,{
	wizardTitle: "Manage plugins",
	step1Title: "Currently loaded plugins",
	step1Html: "<input type='hidden' name='markList'></input>", // DO NOT TRANSLATE
	skippedText: "(This plugin has not been executed because it was added since startup)",
	noPluginText: "There are no plugins installed",
	confirmDeleteText: "Are you sure you want to delete these plugins:\n\n%0",
	removeLabel: "remove systemConfig tag",
	removePrompt: "Remove systemConfig tag",
	deleteLabel: "<?php echo TEXT_DELETE;?>",
	deletePrompt: "Delete these tiddlers forever",
	listViewTemplate: {
		columns: [
			{name: 'Selected', field: 'Selected', rowName: 'title', type: 'Selector'},
			{name: 'Tiddler', field: 'tiddler', title: "Tiddler", type: 'Tiddler'},
			{name: 'Size', field: 'size', tiddlerLink: 'size', title: "Size", type: 'Size'},
			{name: 'Forced', field: 'forced', title: "Forced", tag: 'systemConfigForce', type: 'TagCheckbox'},
			{name: 'Disabled', field: 'disabled', title: "Disabled", tag: 'systemConfigDisable', type: 'TagCheckbox'},
			{name: 'Executed', field: 'executed', title: "Loaded", type: 'Boolean', trueText: "Yes", falseText: "No"},
			{name: 'Startup Time', field: 'startupTime', title: "Startup Time", type: 'String'},
			{name: 'Error', field: 'error', title: "Status", type: 'Boolean', trueText: "Error", falseText: "OK"},
			{name: 'Log', field: 'log', title: "Log", type: 'StringList'}
			],
		rowClasses: [
			{className: 'error', field: 'error'},
			{className: 'warning', field: 'warning'}
			]}
	});

merge(config.macros.toolbar,{
	moreLabel: "<?php echo TEXT_MORE;?>",
	morePrompt: "Reveal further commands"
	});

merge(config.macros.refreshDisplay,{
	label: "refresh",
	prompt: "Redraw the entire TiddlyWiki display"
	});

merge(config.macros.importTiddlers,{
	readOnlyWarning: "You cannot import if you do not have administrative rights",
	wizardTitle: "Import tiddlers from another file or server",
	step1Title: "Step 1: Locate the server or TiddlyWiki file",
	step1Html: "Specify the type of the server: <select name='selTypes'><option value=''>Choose...</option></select><br>Enter the URL or pathname here: <input type='text' size=50 name='txtPath'><br>...or browse for a file: <input type='file' size=50 name='txtBrowse'><br><hr>...or select a pre-defined feed: <select name='selFeeds'><option value=''>Choose...</option></select>",
	openLabel: "<?php echo TEXT_OPEN;?>",
	openPrompt: "Open the connection to this file or server",
	openError: "There were problems fetching the tiddlywiki file",
	statusOpenHost: "Opening the host",
	statusGetWorkspaceList: "Getting the list of available workspaces",
	step2Title: "Step 2: Choose the workspace",
	step2Html: "Enter a workspace name: <input type='text' size=50 name='txtWorkspace'><br>...or select a workspace: <select name='selWorkspace'><option value=''>Choose...</option></select>",
	cancelLabel: "<?php echo TEXT_CANCEL;?>",
	cancelPrompt: "Cancel this import",
	statusOpenWorkspace: "Opening the workspace",
	statusGetTiddlerList: "Getting the list of available tiddlers",
	step3Title: "Step 3: Choose the tiddlers to import",
	step3Html: "<input type='hidden' name='markList'></input><br><input type='checkbox' checked='true' name='chkSync'>Keep these tiddlers linked to this server so that you can synchronise subsequent changes</input><br><input type='checkbox' name='chkSave'>Save the details of this server in a 'systemServer' tiddler called:</input> <input type='text' size=25 name='txtSaveTiddler'>",
	importLabel: "<?php echo TEXT_IMPORT;?>",
	importPrompt: "Import these tiddlers",
	confirmOverwriteText: "Are you sure you want to overwrite these tiddlers:\n\n%0",
	step4Title: "Step 4: Importing %0 tiddler(s)",
	step4Html: "<input type='hidden' name='markReport'></input>", // DO NOT TRANSLATE
	doneLabel: "done",
	donePrompt: "Close this wizard",
	statusDoingImport: "Importing tiddlers",
	statusDoneImport: "All tiddlers imported",
	systemServerNamePattern: "%2 on %1",
	systemServerNamePatternNoWorkspace: "%1",
	confirmOverwriteSaveTiddler: "The tiddler '%0' already exists. Click 'OK' to overwrite it with the details of this server, or 'Cancel' to leave it unchanged",
	serverSaveTemplate: "|''Type:''|%0|\n|''URL:''|%1|\n|''Workspace:''|%2|\n\nThis tiddler was automatically created to record the details of this server",
	serverSaveModifier: "(System)",
	listViewTemplate: {
		columns: [
			{name: 'Selected', field: 'Selected', rowName: 'title', type: 'Selector'},
			{name: 'Tiddler', field: 'tiddler', title: "Tiddler", type: 'Tiddler'},
			{name: 'Size', field: 'size', tiddlerLink: 'size', title: "<?php echo TEXT_SIZE;?>", type: 'Size'},
			{name: 'Tags', field: 'tags', title: "<?php echo TEXT_TAGS;?>", type: 'Tags'}
			],
		rowClasses: [
			]}
	});

merge(config.macros.sync,{
	listViewTemplate: {
		columns: [
			{name: 'Selected', field: 'selected', rowName: 'title', type: 'Selector'},
			{name: 'Tiddler', field: 'tiddler', title: "Tiddler", type: 'Tiddler'},
			{name: 'Server Type', field: 'serverType', title: "Server type", type: 'String'},
			{name: 'Server Host', field: 'serverHost', title: "Server host", type: 'String'},
			{name: 'Server Workspace', field: 'serverWorkspace', title: "Server workspace", type: 'String'},
			{name: 'Status', field: 'status', title: "Synchronisation status", type: 'String'},
			{name: 'Server URL', field: 'serverUrl', title: "Server URL", text: "<?php echo TEXT_VIEW;?>", type: 'Link'}
			],
		rowClasses: [
			],
		buttons: [
			{caption: "Sync these tiddlers", name: 'sync'}
			]},
	wizardTitle: "Synchronize with external servers and files",
	step1Title: "Choose the tiddlers you want to synchronize",
	step1Html: "<input type='hidden' name='markList'></input>", // DO NOT TRANSLATE
	syncLabel: "sync",
	syncPrompt: "Sync these tiddlers",
	hasChanged: "Changed while unplugged",
	hasNotChanged: "Unchanged while unplugged",
	syncStatusList: {
		none: {text: "...", color: "none"},
		changedServer: {text: "Changed on server", color: '#80ff80'},
		changedLocally: {text: "Changed while unplugged", color: '#80ff80'},
		changedBoth: {text: "Changed while unplugged and on server", color: '#ff8080'},
		notFound: {text: "Not found on server", color: '#ffff80'},
		putToServer: {text: "Saved update on server", color: '#ff80ff'},
		gotFromServer: {text: "Retrieved update from server", color: '#80ffff'}
		}
	});

merge(config.commands.closeTiddler,{
	text: "<?php echo TEXT_CLOSE;?>",
	tooltip: "Close this tiddler"});

merge(config.commands.closeOthers,{
	text: "close others",
	tooltip: "Close all other tiddlers"});

merge(config.commands.editTiddler,{
	text: "<?php echo TEXT_EDIT;?>",
	tooltip: "Edit this tiddler",
	readOnlyText: "<?php echo TEXT_VIEW;?>",
	readOnlyTooltip: "View the source of this tiddler"});

merge(config.commands.saveTiddler,{
	text: "done",
	tooltip: "Save changes to this tiddler"});

merge(config.commands.cancelTiddler,{
	text: "<?php echo TEXT_CANCEL;?>",
	tooltip: "Undo changes to this tiddler",
	warning: "Are you sure you want to abandon your changes to '%0'?",
	readOnlyText: "done",
	readOnlyTooltip: "View this tiddler normally"});

merge(config.commands.deleteTiddler,{
	text: "<?php echo TEXT_DELETE;?>",
	tooltip: "Delete this tiddler",
	warning: "Are you sure you want to delete '%0'?"});

merge(config.commands.permalink,{
	text: "permalink",
	tooltip: "Permalink for this tiddler"});

merge(config.commands.references,{
	text: "references",
	tooltip: "Show tiddlers that link to this one",
	popupNone: "No references"});

merge(config.commands.jump,{
	text: "jump",
	tooltip: "Jump to another open tiddler"});

merge(config.commands.syncing,{
	text: "syncing",
	tooltip: "Control synchronisation of this tiddler with a server or external file",
	currentlySyncing: "<div>Currently syncing via <span class='popupHighlight'>'%0'</span> to:</"+"div><div>host: <span class='popupHighlight'>%1</span></"+"div><div>workspace: <span class='popupHighlight'>%2</span></"+"div>", // Note escaping of closing <div> tag
	notCurrentlySyncing: "Not currently syncing",
	captionUnSync: "Stop synchronising this tiddler",
	chooseServer: "Synchronise this tiddler with another server:",
	currServerMarker: "\u25cf ",
	notCurrServerMarker: "  "});

merge(config.commands.fields,{
	text: "<?php echo TEXT_FIELDS;?>",
	tooltip: "Show the extended fields of this tiddler",
	emptyText: "There are no extended fields for this tiddler",
	listViewTemplate: {
		columns: [
			{name: 'Field', field: 'field', title: "<?php echo TEXT_FIELD;?>", type: 'String'},
			{name: 'Value', field: 'value', title: "<?php echo TEXT_VALUE;?>", type: 'String'}
			],
		rowClasses: [
			],
		buttons: [
			]}});

merge(config.shadowTiddlers,{
	DefaultTiddlers: "[[TranslatedGettingStarted]]",
	MainMenu: "[[TranslatedGettingStarted]]\n\n\n^^~TiddlyWiki version <<version>>\n© 2007 [[UnaMesa|http://www.unamesa.org/]]^^",
	TranslatedGettingStarted: "To get started with this blank TiddlyWiki, you'll need to modify the following tiddlers:\n* SiteTitle & SiteSubtitle: The title and subtitle of the site, as shown above (after saving, they will also appear in the browser title bar)\n* MainMenu: The menu (usually on the left)\n* DefaultTiddlers: Contains the names of the tiddlers that you want to appear when the TiddlyWiki is opened\nYou'll also need to enter your username for signing your edits: <<option txtUserName>>",
	SiteTitle: "My TiddlyWiki",
	SiteSubtitle: "a reusable non-linear personal web notebook",
	SiteUrl: "http://www.tiddlywiki.com/",
	OptionsPanel: "These Interface Options for customising TiddlyWiki are saved in your browser\n\nYour username for signing your edits. Write it as a WikiWord (eg JoeBloggs)\n<<option txtUserName>>\n\n<<option chkSaveBackups>> Save backups\n<<option chkAutoSave>> Auto save\n<<option chkRegExpSearch>> Regexp search\n<<option chkCaseSensitiveSearch>> Case sensitive search\n<<option chkAnimate>> Enable animations\n\n----\nAlso see [[TranslatedAdvancedOptions|AdvancedOptions]]",
	SideBarOptions: '<<search>><<closeAll>><<permaview>><<newTiddler>><<newJournal "DD MMM YYYY">><<saveChanges>><<slider chkSliderOptionsPanel OptionsPanel "options »" "Change TiddlyWiki advanced options">>',
	SideBarTabs: '<<tabs txtMainTab "Timeline" "Timeline" TabTimeline "All" "All tiddlers" TabAll "Tags" "All tags" TabTags "More" "More lists" TabMore>>',
	TabMore: '<<tabs txtMoreTab "Missing" "Missing tiddlers" TabMoreMissing "Orphans" "Orphaned tiddlers" TabMoreOrphans "Shadowed" "Shadowed tiddlers" TabMoreShadowed>>'});

merge(config.annotations,{
	AdvancedOptions: "This shadow tiddler provides access to several advanced options",
	ColorPalette: "These values in this shadow tiddler determine the colour scheme of the ~TiddlyWiki user interface",
	DefaultTiddlers: "The tiddlers listed in this shadow tiddler will be automatically displayed when ~TiddlyWiki starts up",
	EditTemplate: "The HTML template in this shadow tiddler determines how tiddlers look while they are being edited",
	GettingStarted: "This shadow tiddler provides basic usage instructions",
	ImportTiddlers: "This shadow tiddler provides access to importing tiddlers",
	MainMenu: "This shadow tiddler is used as the contents of the main menu in the left-hand column of the screen",
	MarkupPreHead: "This tiddler is inserted at the top of the <head> section of the TiddlyWiki HTML file",
	MarkupPostHead: "This tiddler is inserted at the bottom of the <head> section of the TiddlyWiki HTML file",
	MarkupPreBody: "This tiddler is inserted at the top of the <body> section of the TiddlyWiki HTML file",
	MarkupPostBody: "This tiddler is inserted at the end of the <body> section of the TiddlyWiki HTML file immediately before the script block",
	OptionsPanel: "This shadow tiddler is used as the contents of the options panel slider in the right-hand sidebar",
	PageTemplate: "The HTML template in this shadow tiddler determines the overall ~TiddlyWiki layout",
	PluginManager: "This shadow tiddler provides access to the plugin manager",
	SideBarOptions: "This shadow tiddler is used as the contents of the option panel in the right-hand sidebar",
	SideBarTabs: "This shadow tiddler is used as the contents of the tabs panel in the right-hand sidebar",
	SiteSubtitle: "This shadow tiddler is used as the second part of the page title",
	SiteTitle: "This shadow tiddler is used as the first part of the page title",
	SiteUrl: "This shadow tiddler should be set to the full target URL for publication",
	StyleSheetColours: "This shadow tiddler contains CSS definitions related to the color of page elements",
	StyleSheet: "This tiddler can contain custom CSS definitions",
	StyleSheetLayout: "This shadow tiddler contains CSS definitions related to the layout of page elements",
	StyleSheetLocale: "This shadow tiddler contains CSS definitions related to the translation locale",
	StyleSheetPrint: "This shadow tiddler contains CSS definitions for printing",
	TabAll: "This shadow tiddler contains the contents of the 'All' tab in the right-hand sidebar",
	TabMore: "This shadow tiddler contains the contents of the 'More' tab in the right-hand sidebar",
	TabMoreMissing: "This shadow tiddler contains the contents of the 'Missing' tab in the right-hand sidebar",
	TabMoreOrphans: "This shadow tiddler contains the contents of the 'Orphans' tab in the right-hand sidebar",
	TabMoreShadowed: "This shadow tiddler contains the contents of the 'Shadowed' tab in the right-hand sidebar",
	TabTags: "This shadow tiddler contains the contents of the 'Tags' tab in the right-hand sidebar",
	TabTimeline: "This shadow tiddler contains the contents of the 'Timeline' tab in the right-hand sidebar",
	ViewTemplate: "The HTML template in this shadow tiddler determines how tiddlers look"
	});

//}}}
</script>