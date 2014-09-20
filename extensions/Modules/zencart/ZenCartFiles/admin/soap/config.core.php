<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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

//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003-2005 The zen-cart developers                      |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: config.core.php 3009 2006-02-11 15:41:10Z wilt $
//
/**
 * autoloader array for catalog application_top.php
 *
 * @package admin
 * @copyright Copyright 2003-2005 zen-cart Development Team
**/
  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'class.base.php', 'classPath'=>DIR_FS_CATALOG . 'includes/classes/');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'class.notifier.php');
//  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate', 'className'=>'notifier', 'objectName'=>'zco_notifier');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'sniffer.php');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'logger.php', 'classPath'=>DIR_FS_CATALOG . 'admin/includes/classes/');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'shopping_cart.php');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'products.php', 'classPath'=>DIR_FS_CATALOG . 'includes/classes/');
  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=> 'table_block.php', 'classPath'=>DIR_FS_ADMIN . 'includes/classes/');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=> 'box.php', 'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'message_stack.php', 'classPath'=>DIR_FS_ADMIN . 'includes/classes/');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'split_page_results.php', 'classPath'=>DIR_WS_CLASSES);
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'object_info.php', 'classPath'=>DIR_WS_CLASSES);
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'class.phpmailer.php');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'class.smtp.php');
//  $autoLoadConfig[0][] = array('autoType'=>'class', 'loadFile'=>'upload.php', 'classPath'=>DIR_WS_CLASSES);
/**
 * Breakpoint 10.
 * 
 * require('includes/init_includes/init_file_db_names.php');
 * require('includes/init_includes/init_database.ph');
 * require('includes/version.php');
 * 
 */
  $autoLoadConfig[10][] = array('autoType'=>'require', 'loadFile'=> DIR_FS_ADMIN . 'includes/init_includes/init_file_db_names.php');
  $autoLoadConfig[10][] = array('autoType'=>'require', 'loadFile'=> DIR_FS_ADMIN . 'includes/init_includes/init_database.php');
//  $autoLoadConfig[10][] = array('autoType'=>'require', 'loadFile'=> DIR_FS_CATALOG . DIR_WS_INCLUDES . 'version.php');
/**
 * Breakpoint 20.
 * 
 * require('includes/init_includes/init_db_config_read.php');
 * 
 */
  $autoLoadConfig[20][] = array('autoType'=>'require', 'loadFile'=> DIR_FS_ADMIN . 'includes/init_includes/init_db_config_read.php');
/**
 * Breakpoint 30.
 * 
 * require('includes/init_includes/init_gzip.php');
 * $sniffer = new sniffer();

 * 
 */
//  $autoLoadConfig[30][] = array('autoType'=>'init_script', 'loadFile'=> 'init_gzip.php');
//  $autoLoadConfig[30][] = array('autoType'=>'classInstantiate', 'className'=>'sniffer', 'objectName'=>'sniffer');
/**
 * Breakpoint 40.
 * 
 * require('includes/init_includes/init_general_funcs.php');
 * require('includes/init_includes/init_tlds.php');
 * 
 */
//  $autoLoadConfig[40][] = array('autoType'=>'require', 'loadFile'=> DIR_FS_CATALOG . 'admin/includes/init_includes/init_general_funcs.php');
//  $autoLoadConfig[40][] = array('autoType'=>'init_script', 'loadFile'=> 'init_tlds.php');
/**
 * Breakpoint 60.
 * 
 * require('includes/init_includes/init_sessions.php');
 * 
 */
//  $autoLoadConfig[60][] = array('autoType'=>'init_script', 'loadFile'=> 'init_sessions.php');
/**
 * Breakpoint 70.
 * 
 * require('includes/init_includes/init_languages.php');
 * 
 */
//  $autoLoadConfig[70][] = array('autoType'=>'init_script', 'loadFile'=> 'init_languages.php');
/**
 * Breakpoint 80.
 * 
 * require('includes/init_includes/init_templates.php');
 * 
 */
//  $autoLoadConfig[80][] = array('autoType'=>'init_script', 'loadFile'=> 'init_templates.php');
/**
 * Breakpoint 90.
 * 
 * $zc_products = new products();
 * require(DIRWS_FUNCTIONS . 'localization.php');
 * require(DIRWS_FUNCTIONS . 'validations.php');
 * 
 */
//  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate', 'className'=>'products', 'objectName'=>'zc_products');
//  $autoLoadConfig[90][] = array('autoType'=>'require', 'loadFile'=> DIR_WS_FUNCTIONS . 'localization.php');
/**
 * Breakpoint 100.
 * 
 * $messageStack = new messageStack();
 * 
 */
  $autoLoadConfig[100][] = array('autoType'=>'classInstantiate', 'classPath'=>DIR_WS_CLASSES, 'className'=>'messageStack', 'objectName'=>'messageStack');
/**
 * Breakpoint 120.
 * 
 * require('includes/init_includes/init_special_funcs.php');
 * 
 */
//  $autoLoadConfig[120][] = array('autoType'=>'init_script', 'loadFile'=> 'init_special_funcs.php');

/**
 * Breakpoint 130.
 * 
 * require('includes/init_includes/init_category_path.php');
 * 
 */
//  $autoLoadConfig[130][] = array('autoType'=>'init_script', 'loadFile'=> 'init_category_path.php');
/**
 * Breakpoint 140.
 * 
 * require('includes/init_includes/init_errors.php');
 * 
 */
//  $autoLoadConfig[140][] = array('autoType'=>'init_script', 'loadFile'=> 'init_errors.php');
/**
 * Breakpoint 150.
 * 
 * require('includes/init_includes/init_admin_auth.php');
 * 
 */
//  $autoLoadConfig[150][] = array('autoType'=>'init_script', 'loadFile'=> 'init_admin_auth.php');
/**
 * Breakpoint 160.
 * 
 * require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php');
 * 
 */
//  $autoLoadConfig[160][] = array('autoType'=>'require', 'loadFile'=> DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php');
/**
 * Breakpoint 170.
 * 
 * require('includes/init_includes/init_admin_history.php');
 * 
 */
//  $autoLoadConfig[170][] = array('autoType'=>'init_script', 'loadFile'=> DIR_FS_CATALOG . 'admin/includes/init_includes/init_admin_history.php');
/**
 * Breakpoint 180.
 * 
 * require('includes/init_includes/init_html_editor.php);
 * 
 */
//  $autoLoadConfig[180][] = array('autoType'=>'init_script', 'loadFile'=> 'init_html_editor.php');
?>