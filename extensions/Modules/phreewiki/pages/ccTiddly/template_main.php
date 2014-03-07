
<script type="text/javascript">
//<![CDATA[
  var version = {title: "TiddlyWiki", major: 2, minor: 2, revision: 5, date: new Date("Aug 24, 2007"), extensions: {}};
  
//]]>
</script>

<!--PRE-HEAD-START-->
<?php 
  if( isset( $tiddlers['MarkupPreHead'] )) {
    print tiddler_bodyDecode($tiddlers['MarkupPreHead']['body']);
  }

?>


<!--PRE-HEAD-END-->
<script type="text/javascript" src="./modules/phreewiki/javascript/TiddlyWiki.js"></script>

<?php include_once( DIR_FS_WORKING.'javascript/plugins/variables.php')?>
		
<!--script below are ccT plugins-->
<script type="text/javascript">
//<![CDATA[
//cctPlugin
	
	var serverside={
		debug: <?php print DEBUG ?>,		//debug mode, display alert box for each action
		passwordTime: 0,		//defines how long password variable store in cookie. 0 = indefinite
		messageDuration: 5000,				//displayMessage autoclose duration (in milliseconds), 0=leave open
		lingo:{		//message for different language
			uploadStoreArea: "<?php print PHREEWIKI_NOTICE_UPLOAD_STORE_AREA ?>",
			rss: "<?php print PHREEWIKI_NOTICE_UPLOAD_RSS  ?>",
			timeOut: "<?php print PHREEWIKI_NOTICE_TIMEOUT ?>",
			anonymous: "anonymous", 
			login:{
				login: "login",
				loginFailed: "login unsuccessful",
				loginPrompt: "login",
				logout: "logout",
				logoutPrompt: "logout",
			},
			revision:{
				text: "<?php print PHREEWIKI_WORD_REVISION ?>",
				tooltip: "<?php print PHREEWIKI_MISC_REVISION_TOOLTIP?>",
				popupNone: "<?php print PHREEWIKI_WARNING_NO_REVISION ?>",
				notExist: "<?php print PHREEWIKI_ERROR_REVISION_NOT_FOUND ?>"
			}
		},
		loggedIn:true,
		fn:{}		//server-side function
	};
	
	cctPlugin = {
		lingo:{
			autoUpload:"<?php print PHREEWIKI_OPTIONPANEL_AUTOUPLOAD ?>"
		}
	};
	
	window.cct_tweak = function(){
		//add new option to options panel
		config.shadowTiddlers.OptionsPanel = "<<ssUploadStoreArea>>\n<<ssUploadRSS>>\n<<option chkAutoSave>> "+cctPlugin.lingo.autoUpload+"\n<<option chkRegExpSearch>>"+config.shadowTiddlers.OptionsPanel.substring(config.shadowTiddlers.OptionsPanel.search(/<<option chkRegExpSearch>>/)+26);
		//change SideBarOption panel to add login panel
		config.shadowTiddlers.SideBarOptions = "<<search>><<closeAll>><<permaview>><<newTiddler>><<newJournal 'DD MMM YYYY'>>";
		config.shadowTiddlers.ViewTemplate = config.shadowTiddlers.ViewTemplate.replace(/references jump/,'references revisions jump');
		//change saveChange label to upload
		config.macros.saveChanges.label = "<?php print PHREEWIKI_SAVE_CHANGES_UPLOAD ?>";
		config.macros.saveChanges.prompt = "<?php print PHREEWIKI_SAVE_CHANGES_UPLOAD_PROMPT ?>";
	
	
		//force [[link|url]] to open in [- = current window, + = new window]
		window.createExternalLink_cct = window.createExternalLink;
		window.createExternalLink = function (place,url)
		{
			//save previous config
			var tmp = config.options.chkOpenInNewWindow;
			
			//change chkOpenInNewWindow
			if( url.substring(0,1) == "\-" ){
				config.options.chkOpenInNewWindow = false;
				url = url.substring(1,url.length);
			}else{
				if( url.substring(0,1) == "\+" ){
					config.options.chkOpenInNewWindow = true;
					url = url.substring(1,url.length);
				}
			}
				
			var theLink = window.createExternalLink_cct(place,url);
			
			//restore chkOpenInNewWindow
			config.options.chkOpenInNewWindow = tmp;
			return(theLink);
		}
				
		//login panel
		config.options.txtUserName = "<?php print user_getUsername() ?>";
	};
	
	
	//]]>
	</script>
	
	<script type="text/javascript" src="./modules/phreewiki/javascript/serverside.js"></script>


<!--End of ccT plugins-->

<style type="text/css">
#saveTest           {display:none;}
#messageArea        {display:none;}
#copyright          {display:none;}
#storeArea          {display:none;}
#storeArea div      {padding:0.5em; margin:1em 0em 0em 0em; border-color:#fff #666 #444 #ddd; border-style:solid; border-width:2px; overflow:auto;}
#shadowArea         {display:none;}
#javascriptWarning  {width:100%; text-align:center; font-weight:bold; background-color:#dd1100; color:#fff; padding:1em 0em;}
</style>
<!--POST-HEAD-START-->
<?php 
	if( isset( $tiddlers['MarkupPostHead'] ) )	{
		print tiddler_bodyDecode($tiddlers['MarkupPostHead']['body']);
	}
?>
<!--POST-HEAD-END-->

<!--PRE-BODY-START-->
<?php 
	if( isset( $tiddlers['MarkupPreBody'] )) {
		print tiddler_bodyDecode($tiddlers['MarkupPreBody']['body']);
	}
?>
<!--PRE-BODY-END-->
<div id="copyright">
	Welcome to TiddlyWiki created by Jeremy Ruston, Copyright &copy; 2007 UnaMesa Association
</div>
<noscript>
	<div id="javascriptWarning">This page requires JavaScript to function properly.<br /><br />If you are using Microsoft Internet Explorer you may need to click on the yellow bar above and select 'Allow Blocked Content'. You must then click 'Yes' on the following security warning.</div>
</noscript>
<div id="saveTest"></div>
<div id="backstageCloak"></div>
<div id="backstageButton"></div>
<div id="backstageArea"><div id="backstageToolbar"></div></div>
<div id="backstage">
	<div id="backstagePanel"></div>
</div>
<div id="contentWrapper"></div>
<div id="contentStash"></div>
<div id="shadowArea">
	<div tiddler="ColorPalette" tags="">Background: #fff\nForeground: #000\nPrimaryPale: #8cf\nPrimaryLight: #18f\nPrimaryMid: #04b\nPrimaryDark: #014\nSecondaryPale: #ffc\nSecondaryLight: #fe8\nSecondaryMid: #db4\nSecondaryDark: #841\nTertiaryPale: #eee\nTertiaryLight: #ccc\nTertiaryMid: #999\nTertiaryDark: #666\nError: #f88</div>
	<div tiddler="EditTemplate" tags="">&lt;!--{{{--&gt;\n&lt;div class='toolbar' macro='toolbar +saveTiddler -cancelTiddler deleteTiddler'&gt;&lt;/div&gt;\n&lt;div class='title' macro='view title'&gt;&lt;/div&gt;\n&lt;div class='editor' macro='edit title'&gt;&lt;/div&gt;\n&lt;div macro='annotations'&gt;&lt;/div&gt;\n&lt;div class='editor' macro='edit text'&gt;&lt;/div&gt;\n&lt;div class='editor' macro='edit tags'&gt;&lt;/div&gt;&lt;div class='editorFooter'&gt;&lt;span macro='message views.editor.tagPrompt'&gt;&lt;/span&gt;&lt;span macro='tagChooser'&gt;&lt;/span&gt;&lt;/div&gt;\n&lt;!--}}}--&gt;</div>
	<div tiddler="GettingStarted" tags="">To get started with this blank TiddlyWiki, you'll need to modify the following tiddlers:\n* SiteTitle &amp; SiteSubtitle: The title and subtitle of the site, as shown above (after saving, they will also appear in the browser title bar)\n* MainMenu: The menu (usually on the left)\n* DefaultTiddlers: Contains the names of the tiddlers that you want to appear when the TiddlyWiki is opened\nYou'll also need to enter your username for signing your edits: &lt;&lt;option txtUserName&gt;&gt;</div>
	<div tiddler="OptionsPanel" tags="">These InterfaceOptions for customising TiddlyWiki are saved in your browser\n\nYour username for signing your edits. Write it as a WikiWord (eg JoeBloggs)\n\n&lt;&lt;option txtUserName&gt;&gt;\n&lt;&lt;option chkSaveBackups&gt;&gt; SaveBackups\n&lt;&lt;option chkAutoSave&gt;&gt; AutoSave\n&lt;&lt;option chkRegExpSearch&gt;&gt; RegExpSearch\n&lt;&lt;option chkCaseSensitiveSearch&gt;&gt; CaseSensitiveSearch\n&lt;&lt;option chkAnimate&gt;&gt; EnableAnimations\n\n----\nAlso see AdvancedOptions</div>
	<div tiddler="PageTemplate" tags="">&lt;!--{{{--&gt;\n&lt;div class='header' macro='gradient vert [[ColorPalette::PrimaryLight]] [[ColorPalette::PrimaryMid]]'&gt;\n&lt;div class='headerShadow'&gt;\n&lt;span class='siteTitle' refresh='content' tiddler='SiteTitle'&gt;&lt;/span&gt;&amp;nbsp;\n&lt;span class='siteSubtitle' refresh='content' tiddler='SiteSubtitle'&gt;&lt;/span&gt;\n&lt;/div&gt;\n&lt;div class='headerForeground'&gt;\n&lt;span class='siteTitle' refresh='content' tiddler='SiteTitle'&gt;&lt;/span&gt;&amp;nbsp;\n&lt;span class='siteSubtitle' refresh='content' tiddler='SiteSubtitle'&gt;&lt;/span&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;div id='mainMenu' refresh='content' tiddler='MainMenu'&gt;&lt;/div&gt;\n&lt;div id='sidebar'&gt;\n&lt;div id='sidebarOptions' refresh='content' tiddler='SideBarOptions'&gt;&lt;/div&gt;\n&lt;div id='sidebarTabs' refresh='content' force='true' tiddler='SideBarTabs'&gt;&lt;/div&gt;\n&lt;/div&gt;\n&lt;div id='displayArea'&gt;\n&lt;div id='messageArea'&gt;&lt;/div&gt;\n&lt;div id='tiddlerDisplay'&gt;&lt;/div&gt;\n&lt;/div&gt;\n&lt;!--}}}--&gt;</div>
	<div tiddler="StyleSheetColors" tags="">/*{{{*/\nbody {background:[[ColorPalette::Background]]; color:[[ColorPalette::Foreground]];}\n\na {color:[[ColorPalette::PrimaryMid]];}\na:hover {background-color:[[ColorPalette::PrimaryMid]]; color:[[ColorPalette::Background]];}\na img {border:0;}\n\nh1,h2,h3,h4,h5,h6 {color:[[ColorPalette::SecondaryDark]]; background:transparent;}\nh1 {border-bottom:2px solid [[ColorPalette::TertiaryLight]];}\nh2,h3 {border-bottom:1px solid [[ColorPalette::TertiaryLight]];}\n\n.button {color:[[ColorPalette::PrimaryDark]]; border:1px solid [[ColorPalette::Background]];}\n.button:hover {color:[[ColorPalette::PrimaryDark]]; background:[[ColorPalette::SecondaryLight]]; border-color:[[ColorPalette::SecondaryMid]];}\n.button:active {color:[[ColorPalette::Background]]; background:[[ColorPalette::SecondaryMid]]; border:1px solid [[ColorPalette::SecondaryDark]];}\n\n.header {background:[[ColorPalette::PrimaryMid]];}\n.headerShadow {color:[[ColorPalette::Foreground]];}\n.headerShadow a {font-weight:normal; color:[[ColorPalette::Foreground]];}\n.headerForeground {color:[[ColorPalette::Background]];}\n.headerForeground a {font-weight:normal; color:[[ColorPalette::PrimaryPale]];}\n\n.tabSelected{color:[[ColorPalette::PrimaryDark]];\n	background:[[ColorPalette::TertiaryPale]];\n	border-left:1px solid [[ColorPalette::TertiaryLight]];\n	border-top:1px solid [[ColorPalette::TertiaryLight]];\n	border-right:1px solid [[ColorPalette::TertiaryLight]];\n}\n.tabUnselected {color:[[ColorPalette::Background]]; background:[[ColorPalette::TertiaryMid]];}\n.tabContents {color:[[ColorPalette::PrimaryDark]]; background:[[ColorPalette::TertiaryPale]]; border:1px solid [[ColorPalette::TertiaryLight]];}\n.tabContents .button {border:0;}\n\n#sidebar {}\n#sidebarOptions input {border:1px solid [[ColorPalette::PrimaryMid]];}\n#sidebarOptions .sliderPanel {background:[[ColorPalette::PrimaryPale]];}\n#sidebarOptions .sliderPanel a {border:none;color:[[ColorPalette::PrimaryMid]];}\n#sidebarOptions .sliderPanel a:hover {color:[[ColorPalette::Background]]; background:[[ColorPalette::PrimaryMid]];}\n#sidebarOptions .sliderPanel a:active {color:[[ColorPalette::PrimaryMid]]; background:[[ColorPalette::Background]];}\n\n.wizard {background:[[ColorPalette::PrimaryPale]]; border:1px solid [[ColorPalette::PrimaryMid]];}\n.wizard h1 {color:[[ColorPalette::PrimaryDark]]; border:none;}\n.wizard h2 {color:[[ColorPalette::Foreground]]; border:none;}\n.wizardStep {background:[[ColorPalette::Background]]; color:[[ColorPalette::Foreground]];\n	border:1px solid [[ColorPalette::PrimaryMid]];}\n.wizardStep.wizardStepDone {background:[[ColorPalette::TertiaryLight]];}\n.wizardFooter {background:[[ColorPalette::PrimaryPale]];}\n.wizardFooter .status {background:[[ColorPalette::PrimaryDark]]; color:[[ColorPalette::Background]];}\n.wizard .button {color:[[ColorPalette::Foreground]]; background:[[ColorPalette::SecondaryLight]]; border: 1px solid;\n	border-color:[[ColorPalette::SecondaryPale]] [[ColorPalette::SecondaryDark]] [[ColorPalette::SecondaryDark]] [[ColorPalette::SecondaryPale]];}\n.wizard .button:hover {color:[[ColorPalette::Foreground]]; background:[[ColorPalette::Background]];}\n.wizard .button:active {color:[[ColorPalette::Background]]; background:[[ColorPalette::Foreground]]; border: 1px solid;\n	border-color:[[ColorPalette::PrimaryDark]] [[ColorPalette::PrimaryPale]] [[ColorPalette::PrimaryPale]] [[ColorPalette::PrimaryDark]];}\n\n#messageArea {border:1px solid [[ColorPalette::SecondaryMid]]; background:[[ColorPalette::SecondaryLight]]; color:[[ColorPalette::Foreground]];}\n#messageArea .button {color:[[ColorPalette::PrimaryMid]]; background:[[ColorPalette::SecondaryPale]]; border:none;}\n\n.popupTiddler {background:[[ColorPalette::TertiaryPale]]; border:2px solid [[ColorPalette::TertiaryMid]];}\n\n.popup {background:[[ColorPalette::TertiaryPale]]; color:[[ColorPalette::TertiaryDark]]; border-left:1px solid [[ColorPalette::TertiaryMid]]; border-top:1px solid [[ColorPalette::TertiaryMid]]; border-right:2px solid [[ColorPalette::TertiaryDark]]; border-bottom:2px solid [[ColorPalette::TertiaryDark]];}\n.popup hr {color:[[ColorPalette::PrimaryDark]]; background:[[ColorPalette::PrimaryDark]]; border-bottom:1px;}\n.popup li.disabled {color:[[ColorPalette::TertiaryMid]];}\n.popup li a, .popup li a:visited {color:[[ColorPalette::Foreground]]; border: none;}\n.popup li a:hover {background:[[ColorPalette::SecondaryLight]]; color:[[ColorPalette::Foreground]]; border: none;}\n.popup li a:active {background:[[ColorPalette::SecondaryPale]]; color:[[ColorPalette::Foreground]]; border: none;}\n.popupHighlight {background:[[ColorPalette::Background]]; color:[[ColorPalette::Foreground]];}\n.listBreak div {border-bottom:1px solid [[ColorPalette::TertiaryDark]];}\n\n.tiddler .defaultCommand {font-weight:bold;}\n\n.shadow .title {color:[[ColorPalette::TertiaryDark]];}\n\n.title {color:[[ColorPalette::SecondaryDark]];}\n.subtitle {color:[[ColorPalette::TertiaryDark]];}\n\n.toolbar {color:[[ColorPalette::PrimaryMid]];}\n.toolbar a {color:[[ColorPalette::TertiaryLight]];}\n.selected .toolbar a {color:[[ColorPalette::TertiaryMid]];}\n.selected .toolbar a:hover {color:[[ColorPalette::Foreground]];}\n\n.tagging, .tagged {border:1px solid [[ColorPalette::TertiaryPale]]; background-color:[[ColorPalette::TertiaryPale]];}\n.selected .tagging, .selected .tagged {background-color:[[ColorPalette::TertiaryLight]]; border:1px solid [[ColorPalette::TertiaryMid]];}\n.tagging .listTitle, .tagged .listTitle {color:[[ColorPalette::PrimaryDark]];}\n.tagging .button, .tagged .button {border:none;}\n\n.footer {color:[[ColorPalette::TertiaryLight]];}\n.selected .footer {color:[[ColorPalette::TertiaryMid]];}\n\n.sparkline {background:[[ColorPalette::PrimaryPale]]; border:0;}\n.sparktick {background:[[ColorPalette::PrimaryDark]];}\n\n.error, .errorButton {color:[[ColorPalette::Foreground]]; background:[[ColorPalette::Error]];}\n.warning {color:[[ColorPalette::Foreground]]; background:[[ColorPalette::SecondaryPale]];}\n.lowlight {background:[[ColorPalette::TertiaryLight]];}\n\n.zoomer {background:none; color:[[ColorPalette::TertiaryMid]]; border:3px solid [[ColorPalette::TertiaryMid]];}\n\n.imageLink, #displayArea .imageLink {background:transparent;}\n\n.annotation {background:[[ColorPalette::SecondaryLight]]; color:[[ColorPalette::Foreground]]; border:2px solid [[ColorPalette::SecondaryMid]];}\n\n.viewer .listTitle {list-style-type:none; margin-left:-2em;}\n.viewer .button {border:1px solid [[ColorPalette::SecondaryMid]];}\n.viewer blockquote {border-left:3px solid [[ColorPalette::TertiaryDark]];}\n\n.viewer table, table.twtable {border:2px solid [[ColorPalette::TertiaryDark]];}\n.viewer th, .viewer thead td, .twtable th, .twtable thead td {background:[[ColorPalette::SecondaryMid]]; border:1px solid [[ColorPalette::TertiaryDark]]; color:[[ColorPalette::Background]];}\n.viewer td, .viewer tr, .twtable td, .twtable tr {border:1px solid [[ColorPalette::TertiaryDark]];}\n\n.viewer pre {border:1px solid [[ColorPalette::SecondaryLight]]; background:[[ColorPalette::SecondaryPale]];}\n.viewer code {color:[[ColorPalette::SecondaryDark]];}\n.viewer hr {border:0; border-top:dashed 1px [[ColorPalette::TertiaryDark]]; color:[[ColorPalette::TertiaryDark]];}\n\n.highlight, .marked {background:[[ColorPalette::SecondaryLight]];}\n\n.editor input {border:1px solid [[ColorPalette::PrimaryMid]];}\n.editor textarea {border:1px solid [[ColorPalette::PrimaryMid]]; width:100%;}\n.editorFooter {color:[[ColorPalette::TertiaryMid]];}\n\n#backstageArea {background:[[ColorPalette::Foreground]]; color:[[ColorPalette::TertiaryMid]];}\n#backstageArea a {background:[[ColorPalette::Foreground]]; color:[[ColorPalette::Background]]; border:none;}\n#backstageArea a:hover {background:[[ColorPalette::SecondaryLight]]; color:[[ColorPalette::Foreground]]; }\n#backstageArea a.backstageSelTab {background:[[ColorPalette::Background]]; color:[[ColorPalette::Foreground]];}\n#backstageButton a {background:none; color:[[ColorPalette::Background]]; border:none;}\n#backstageButton a:hover {background:[[ColorPalette::Foreground]]; color:[[ColorPalette::Background]]; border:none;}\n#backstagePanel {background:[[ColorPalette::Background]]; border-color: [[ColorPalette::Background]] [[ColorPalette::TertiaryDark]] [[ColorPalette::TertiaryDark]] [[ColorPalette::TertiaryDark]];}\n.backstagePanelFooter .button {border:none; color:[[ColorPalette::Background]];}\n.backstagePanelFooter .button:hover {color:[[ColorPalette::Foreground]];}\n#backstageCloak {background:[[ColorPalette::Foreground]]; opacity:0.6; filter:'alpha(opacity:60)';}\n/*}}}*/</div>
	<div tiddler="StyleSheetLayout" tags="">/*{{{*/\n* html .tiddler {height:1%;}\n\nh1,h2,h3,h4,h5,h6 {font-weight:bold; text-decoration:none;}\nh1,h2,h3 {padding-bottom:1px; margin-top:1.2em;margin-bottom:0.3em;}\nh4,h5,h6 {margin-top:1em;}\nh1 {font-size:1.35em;}\nh2 {font-size:1.25em;}\nh3 {font-size:1.1em;}\nh4 {font-size:1em;}\nh5 {font-size:.9em;}\n\nhr {height:1px;}\n\na {text-decoration:none;}\n\ndt {font-weight:bold;}\n\nol {list-style-type:decimal;}\nol ol {list-style-type:lower-alpha;}\nol ol ol {list-style-type:lower-roman;}\nol ol ol ol {list-style-type:decimal;}\nol ol ol ol ol {list-style-type:lower-alpha;}\nol ol ol ol ol ol {list-style-type:lower-roman;}\nol ol ol ol ol ol ol {list-style-type:decimal;}\n\n.txtOptionInput {width:11em;}\n\n#contentWrapper .chkOptionInput {border:0;}\n\n.externalLink {text-decoration:underline;}\n\n.indent {margin-left:3em;}\n.outdent {margin-left:3em; text-indent:-3em;}\ncode.escaped {white-space:nowrap;}\n\n.tiddlyLinkExisting {font-weight:bold;}\n.tiddlyLinkNonExisting {font-style:italic;}\n\n/* the 'a' is required for IE, otherwise it renders the whole tiddler in bold */\na.tiddlyLinkNonExisting.shadow {font-weight:bold;}\n\n#mainMenu .tiddlyLinkExisting,\n	#mainMenu .tiddlyLinkNonExisting,\n	#sidebarTabs .tiddlyLinkNonExisting {font-weight:normal; font-style:normal;}\n#sidebarTabs .tiddlyLinkExisting {font-weight:bold; font-style:normal;}\n\n.header {position:relative;}\n.header a:hover {background:transparent;}\n.headerShadow {position:relative; padding:4.5em 0em 1em 1em; left:-1px; top:-1px;}\n.headerForeground {position:absolute; padding:4.5em 0em 1em 1em; left:0px; top:0px;}\n\n.siteTitle {font-size:3em;}\n.siteSubtitle {font-size:1.2em;}\n\n#mainMenu {position:absolute; left:0; width:10em; text-align:right; line-height:1.6em; padding:1.5em 0.5em 0.5em 0.5em; font-size:1.1em;}\n\n#sidebar {position:absolute; right:3px; width:16em; font-size:.9em;}\n#sidebarOptions {padding-top:0.3em;}\n#sidebarOptions a {margin:0em 0.2em; padding:0.2em 0.3em; display:block;}\n#sidebarOptions input {margin:0.4em 0.5em;}\n#sidebarOptions .sliderPanel {margin-left:1em; padding:0.5em; font-size:.85em;}\n#sidebarOptions .sliderPanel a {font-weight:bold; display:inline; padding:0;}\n#sidebarOptions .sliderPanel input {margin:0 0 .3em 0;}\n#sidebarTabs .tabContents {width:15em; overflow:hidden;}\n\n.wizard {padding:0.1em 1em 0em 2em;}\n.wizard h1 {font-size:2em; font-weight:bold; background:none; padding:0em 0em 0em 0em; margin:0.4em 0em 0.2em 0em;}\n.wizard h2 {font-size:1.2em; font-weight:bold; background:none; padding:0em 0em 0em 0em; margin:0.4em 0em 0.2em 0em;}\n.wizardStep {padding:1em 1em 1em 1em;}\n.wizard .button {margin:0.5em 0em 0em 0em; font-size:1.2em;}\n.wizardFooter {padding:0.8em 0.4em 0.8em 0em;}\n.wizardFooter .status {padding:0em 0.4em 0em 0.4em; margin-left:1em;}\n.wizard .button {padding:0.1em 0.2em 0.1em 0.2em;}\n\n#messageArea {position:fixed; top:2em; right:0em; margin:0.5em; padding:0.5em; z-index:2000; _position:absolute;}\n.messageToolbar {display:block; text-align:right; padding:0.2em 0.2em 0.2em 0.2em;}\n#messageArea a {text-decoration:underline;}\n\n.tiddlerPopupButton {padding:0.2em 0.2em 0.2em 0.2em;}\n.popupTiddler {position: absolute; z-index:300; padding:1em 1em 1em 1em; margin:0;}\n\n.popup {position:absolute; z-index:300; font-size:.9em; padding:0; list-style:none; margin:0;}\n.popup .popupMessage {padding:0.4em;}\n.popup hr {display:block; height:1px; width:auto; padding:0; margin:0.2em 0em;}\n.popup li.disabled {padding:0.4em;}\n.popup li a {display:block; padding:0.4em; font-weight:normal; cursor:pointer;}\n.listBreak {font-size:1px; line-height:1px;}\n.listBreak div {margin:2px 0;}\n\n.tabset {padding:1em 0em 0em 0.5em;}\n.tab {margin:0em 0em 0em 0.25em; padding:2px;}\n.tabContents {padding:0.5em;}\n.tabContents ul, .tabContents ol {margin:0; padding:0;}\n.txtMainTab .tabContents li {list-style:none;}\n.tabContents li.listLink { margin-left:.75em;}\n\n#contentWrapper {display:block;}\n#splashScreen {display:none;}\n\n#displayArea {margin:1em 17em 0em 14em;}\n\n.toolbar {text-align:right; font-size:.9em;}\n\n.tiddler {padding:1em 1em 0em 1em;}\n\n.missing .viewer,.missing .title {font-style:italic;}\n\n.title {font-size:1.6em; font-weight:bold;}\n\n.missing .subtitle {display:none;}\n.subtitle {font-size:1.1em;}\n\n.tiddler .button {padding:0.2em 0.4em;}\n\n.tagging {margin:0.5em 0.5em 0.5em 0; float:left; display:none;}\n.isTag .tagging {display:block;}\n.tagged {margin:0.5em; float:right;}\n.tagging, .tagged {font-size:0.9em; padding:0.25em;}\n.tagging ul, .tagged ul {list-style:none; margin:0.25em; padding:0;}\n.tagClear {clear:both;}\n\n.footer {font-size:.9em;}\n.footer li {display:inline;}\n\n.annotation {padding:0.5em; margin:0.5em;}\n\n* html .viewer pre {width:99%; padding:0 0 1em 0;}\n.viewer {line-height:1.4em; padding-top:0.5em;}\n.viewer .button {margin:0em 0.25em; padding:0em 0.25em;}\n.viewer blockquote {line-height:1.5em; padding-left:0.8em;margin-left:2.5em;}\n.viewer ul, .viewer ol {margin-left:0.5em; padding-left:1.5em;}\n\n.viewer table, table.twtable {border-collapse:collapse; margin:0.8em 1.0em;}\n.viewer th, .viewer td, .viewer tr,.viewer caption,.twtable th, .twtable td, .twtable tr,.twtable caption {padding:3px;}\ntable.listView {font-size:0.85em; margin:0.8em 1.0em;}\ntable.listView th, table.listView td, table.listView tr {padding:0px 3px 0px 3px;}\n\n.viewer pre {padding:0.5em; margin-left:0.5em; font-size:1.2em; line-height:1.4em; overflow:auto;}\n.viewer code {font-size:1.2em; line-height:1.4em;}\n\n.editor {font-size:1.1em;}\n.editor input, .editor textarea {display:block; width:100%; font:inherit;}\n.editorFooter {padding:0.25em 0em; font-size:.9em;}\n.editorFooter .button {padding-top:0px; padding-bottom:0px;}\n\n.fieldsetFix {border:0; padding:0; margin:1px 0px 1px 0px;}\n\n.sparkline {line-height:1em;}\n.sparktick {outline:0;}\n\n.zoomer {font-size:1.1em; position:absolute; overflow:hidden;}\n.zoomer div {padding:1em;}\n\n* html #backstage {width:99%;}\n* html #backstageArea {width:99%;}\n#backstageArea {display:none; position:relative; overflow: hidden; z-index:150; padding:0.3em 0.5em 0.3em 0.5em;}\n#backstageToolbar {position:relative;}\n#backstageArea a {font-weight:bold; margin-left:0.5em; padding:0.3em 0.5em 0.3em 0.5em;}\n#backstageButton {display:none; position:absolute; z-index:175; top:4em; right:0em;}\n#backstageButton a {padding:0.1em 0.4em 0.1em 0.4em; margin:0.1em 0.1em 0.1em 0.1em;}\n#backstage {position:relative; width:100%; z-index:50;}\n#backstagePanel {display:none; z-index:100; position:absolute; margin:0em 3em 0em 3em; padding:1em 1em 1em 1em;}\n.backstagePanelFooter {padding-top:0.2em; float:right;}\n.backstagePanelFooter a {padding:0.2em 0.4em 0.2em 0.4em;}\n#backstageCloak {display:none; z-index:20; position:absolute; width:100%; height:100px;}\n\n.whenBackstage {display:none;}\n.backstageVisible .whenBackstage {display:block;}\n/*}}}*/</div>
	<div tiddler="StyleSheetLocale" tags="">/***\nStyleSheet for use when a translation requires any css style changes.\nThis StyleSheet can be used directly by languages such as Chinese, Japanese and Korean which use a logographic writing system and need larger font sizes.\n***/\n\n/*{{{*/\nbody {font-size:0.8em;}\n.headerShadow {position:relative; padding:3.5em 0em 1em 1em; left:-1px; top:-1px;}\n.headerForeground {position:absolute; padding:3.5em 0em 1em 1em; left:0px; top:0px;}\n\n#sidebarOptions {font-size:1.05em;}\n#sidebarOptions a {font-style:normal;}\n#sidebarOptions .sliderPanel {font-size:0.95em;}\n\n.subtitle {font-size:0.8em;}\n\n.viewer table.listView {font-size:1em;}\n\n.htmlarea .toolbarHA table {border:1px solid ButtonFace; margin:0em 0em;}\n/*}}}*/</div>
	<div tiddler="StyleSheetPrint" tags="">/*{{{*/\n@media print {\n#mainMenu, #sidebar, #messageArea, .toolbar, #backstageButton, #backstageArea {display: none ! important;}\n#displayArea {margin: 1em 1em 0em 1em;}\n/* Fixes a feature in Firefox 1.5.0.2 where print preview displays the noscript content */\nnoscript {display:none;}\n}\n/*}}}*/</div>
	<div tiddler="ViewTemplate" tags="">&lt;!--{{{--&gt;\n&lt;div class='toolbar' macro='toolbar closeTiddler closeOthers +editTiddler &gt; fields syncing permalink references jump'&gt;&lt;/div&gt;\n&lt;div class='title' macro='view title'&gt;&lt;/div&gt;\n&lt;div class='subtitle'&gt;&lt;span macro='view modifier link'&gt;&lt;/span&gt;, &lt;span macro='view modified date'&gt;&lt;/span&gt; (&lt;span macro='message views.wikified.createdPrompt'&gt;&lt;/span&gt; &lt;span macro='view created date'&gt;&lt;/span&gt;)&lt;/div&gt;\n&lt;div class='tagging' macro='tagging'&gt;&lt;/div&gt;\n&lt;div class='tagged' macro='tags'&gt;&lt;/div&gt;\n&lt;div class='viewer' macro='view text wikified'&gt;&lt;/div&gt;\n&lt;div class='tagClear'&gt;&lt;/div&gt;\n&lt;!--}}}--&gt;</div>
</div> 
<!--POST-SHADOWAREA-->
<div id="storeArea">
<?php
	if( sizeof($tiddlers)>0 ) {
		foreach( $tiddlers as $t )	{
			echo tiddler_outputDIV($t).chr(13);
		}
	}
?>
</div>
<!--POST-STOREAREA-->
<!--POST-BODY-START-->
<?php 
	if( isset( $tiddlers['MarkupPostBody'] )) {
		print tiddler_bodyDecode($tiddlers['MarkupPostBody']['body']);
	}
?>
<!--POST-BODY-END-->
<script type="text/javascript">
//<![CDATA[
if(useJavaSaver)
	document.write("<applet style='position:absolute;left:-1px' name='TiddlySaver' code='TiddlySaver.class' archive='TiddlySaver.jar' width='1' height='1'></applet>");
//]]>
</script>

