<?php
/*
 * PhreeBooks 5 - Main entry point for all scripts, calls controller to handle details
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-04-22
 * @filesource /index.php
 */

namespace bizuno;

define('MODULE_BIZUNO_VERSION','3.2.0');
define('PHREEBOOKS_VERSION','5.2.3'); // keep the sub-rev x.x.# at same level as Bizuno

ini_set('display_errors', true);
//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);
/*******************************/
if (!defined('SCRIPT_START_TIME')) { define('SCRIPT_START_TIME', microtime(true)); }

if (file_exists('bizunoCFG.php')) { include('bizunoCFG.php'); } // fetch the db and host specific credentials

// Host Information/source paths
define('BIZUNO_HOST',      'phreebooks'); // PhreeBooks 5 hosted
define('BIZUNO_HOME',      'index.php?'); // filename of the main entry index script
define('BIZUNO_AJAX',      'index.php?'); // root path for AJAX requests
// URL paths
$path = rtrim(pathinfo($_SERVER["SCRIPT_NAME"], PATHINFO_DIRNAME), '/');
define('BIZUNO_SRVR',      "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER["SERVER_NAME"].$path.'/'); // url to server with trailing slash
define('BIZUNO_LOGO',      BIZUNO_SRVR.'phreebooks.png');
define('BIZUNO_URL',       BIZUNO_SRVR.'lib/'); // full url to Bizuno plugin library folder
define('BIZUNO_URL_FS',    BIZUNO_SRVR.'bizunoFS.php?'); // full url to Bizuno plugin extensions folder
define('BIZUNO_URL_EXT',   BIZUNO_SRVR.'ext/'); // full url to Bizuno plugin extensions folder
define('BIZUNO_URL_CUSTOM',BIZUNO_SRVR.'myExt/'); // full url to Bizuno plugin custom extensions folder
// File system paths
//define('BIZUNO_ROOT',      dirname(__FILE__).'/'); // Used to be this which was absolute path
define('BIZUNO_ROOT',      ''); // relative path to bizuno root index file
define('BIZUNO_LIB',       BIZUNO_ROOT.'lib/'); // file system path to Bizuno Library
define('BIZUNO_EXT',       BIZUNO_ROOT.'ext/'); // file system path to Bizuno Extensions
define('BIZUNO_CUSTOM',    BIZUNO_ROOT.'myExt/'); // file system path to Bizuno custom extensions
define('BIZUNO_DATA',      BIZUNO_ROOT.'myFiles/'); // myFolder
define('BIZUNO_ICONS',     BIZUNO_DATA.'extIcons/'); // file system path to extra icon sets
define('BIZUNO_THEMES',    BIZUNO_DATA.'extThemes/'); // file system path to extra themes
// Database
if (!defined('BIZUNO_DB_HOST'))  { define('BIZUNO_DB_HOST',  'localhost'); }
if (!defined('BIZUNO_DB_NAME'))  { define('BIZUNO_DB_NAME',  ''); }
if (!defined('BIZUNO_DB_USER'))  { define('BIZUNO_DB_USER',  ''); }
if (!defined('BIZUNO_DB_PASS'))  { define('BIZUNO_DB_PASS',  ''); }
if (!defined('BIZUNO_DB_PREFIX')){ define('BIZUNO_DB_PREFIX',''); }
define('PORTAL_DB_PREFIX', BIZUNO_DB_PREFIX); // Portal table prefix
$GLOBALS['dbBizuno'] = $GLOBALS['dbPortal'] = ['type'=>'mysql','host'=>BIZUNO_DB_HOST,'name'=>BIZUNO_DB_NAME,'user'=>BIZUNO_DB_USER,'pass'=>BIZUNO_DB_PASS,'prefix'=>BIZUNO_DB_PREFIX];
// Third Party Apps
define('BIZUNO_3P_QZ_TRAY',BIZUNO_EXT ."extShipping/qz-tray/");
define('BIZUNO_3P_TCPDF',  BIZUNO_ROOT.'apps/TCPDF/');

define('BIZUNO_DEBUG', false);

require("portal/main.php");
$ctl = new main();
new view($ctl->layout);
