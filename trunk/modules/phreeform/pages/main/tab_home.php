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
//  Path: /modules/phreeform/pages/main/tab_home.php
//

$fieldset_content = NULL;
// build the tab contents
$fieldset_content .= '<table class="ui-widget" style="border-style:none;width:100%">' . chr(10);
$fieldset_content .= '<tr><td width="50%" valign="top">' . chr(10);
// column 1
$fieldset_content .= '  <table class="ui-widget" style="border-style:none;width:100%">' . chr(10);
$fieldset_content .= '   <thead class="ui-widget-header">' . chr(10);
$fieldset_content .= '    <tr><th>' . TEXT_MY_REPORTS . '</th></tr>' . chr(10);
$fieldset_content .= '   </thead>' . chr(10);
$fieldset_content .= '   <tbody class="ui-widget-content">' . chr(10);
$fieldset_content .= '    <tr><td>' . load_my_reports() . '</td></tr>' . chr(10);
$fieldset_content .= '   </tbody></table>' . chr(10);
$fieldset_content .= '</td><td width="50%" valign="top">' . chr(10);
// column 2
$fieldset_content .= '  <table class="ui-widget" style="border-style:none;width:100%">' . chr(10);
$fieldset_content .= '   <thead class="ui-widget-header">' . chr(10);
$fieldset_content .= '    <tr><th>' . TEXT_RECENTLY_ADDED . '</th></tr>' . chr(10);
$fieldset_content .= '   </thead>' . chr(10);
$fieldset_content .= '   <tbody class="ui-widget-content">' . chr(10);
$fieldset_content .= '    <tr><td>' . load_recently_added() . '</td></tr>' . chr(10);
$fieldset_content .= '  </tbody></table>' . chr(10);
$fieldset_content .= '</td></tr></table>' . chr(10);

?>