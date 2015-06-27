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
//  Path: /modules/linkpoint/config.php
//

// REMEMBER TO CHECK PERMISSIONS AFTER ADDING A NEW MENU ITEM. THEY DEFAULT TO NO ACCESS AND 
// DO NOT SHOW UP ON THE MENU UNITL PERMISSION HAS BEEN GRANTED AND THE USER HAS RE-LOGGED IN

// Security id's
define('SECURITY_ID_LINKPOINT_INTERFACE', 310);

// Menu Locations
$menu[] = array(
  'text'        => BOX_BANKING_LINK_POINT_CC_REVIEW, 
  'heading'     => MENU_HEADING_BANKING,
  'rank'        => 50, 
  'security_id' => SECURITY_ID_LINKPOINT_INTERFACE, 
  'link'        => html_href_link(FILENAME_DEFAULT, 'module=linkpoint&amp;page=ccreview&amp;jID=18&amp;type=v', 'SSL'),
);

// New Database Tables
define('TABLE_LINKPOINT_API', DB_PREFIX . 'linkpoint_api'); 

?>