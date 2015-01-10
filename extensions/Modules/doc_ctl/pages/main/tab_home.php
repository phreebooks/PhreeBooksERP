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
//  Path: /modules/doc_ctl/pages/main/tab_home.php
//

$fieldset_content = NULL;

// build the tab contents
$fieldset_content .= '<table width="100%" cellspacing="0" cellpadding="4"><tr><td width="50%" valign="top">' . chr(10);
// column 1
$fieldset_content .= '  <table width="100%"><tr>' . chr(10);
$fieldset_content .= '    <th>' . TEXT_MY_BOOKMARKS . '</th>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . load_bookmarks() . '</td>' . chr(10);
$fieldset_content .= '  </tr>' . chr(10);
$fieldset_content .= '  <tr>' . chr(10);
$fieldset_content .= '    <th>' . TEXT_CHECKED_OUT_FILES . '</th>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . load_checked_out() . '</td>' . chr(10);
$fieldset_content .= '  </tr></table>' . chr(10);
$fieldset_content .= '</td><td width="50%" valign="top">' . chr(10);
// column 2
$fieldset_content .= '  <table width="100%"><tr>' . chr(10);
$fieldset_content .= '    <th>' . TEXT_RECENTLY_ADDED . '</th>' . chr(10);
$fieldset_content .= '  </tr><tr>' . chr(10);
$fieldset_content .= '    <td>' . load_recently_added() . '</td>' . chr(10);
$fieldset_content .= '  </tr></table>' . chr(10);
$fieldset_content .= '</td></tr></table>' . chr(10);

?>