<?php
/*
 * PhreeBooks 5 - Generates the css file for all icons, including from custom modules
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
 * @version    3.x Last Update: 2019-03-20
 * @filesource /bizunoCSS.php
 */

namespace bizuno;

//@include('bizunoCFG.php'); // only has db info not needed

// Host Information/source paths
define('BIZUNO_HOST',      'phreebooks'); // PhreeBooks 5 hosted
define('BIZUNO_HOME',      'index.php?'); // filename of the main entry index script
define('BIZUNO_AJAX',      'index.php?'); // root path for AJAX requests
// URL paths
$pathSrvr = pathinfo($_SERVER["SCRIPT_NAME"], PATHINFO_DIRNAME);
define('BIZUNO_SRVR',      "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER["SERVER_NAME"].$pathSrvr.'/'); // url to server with trailing slash
define('BIZUNO_LOGO',      BIZUNO_SRVR.'phreebooks.png');
define('BIZUNO_URL',       BIZUNO_SRVR.'lib/'); // full url to Bizuno plugin library folder
define('BIZUNO_URL_FS',    BIZUNO_SRVR.'bizunoFS.php?'); // full url to Bizuno plugin extensions folder
define('BIZUNO_URL_EXT',   BIZUNO_SRVR.'ext/'); // full url to Bizuno plugin extensions folder
define('BIZUNO_URL_CUSTOM',BIZUNO_SRVR.'myExt/'); // full url to Bizuno plugin custom extensions folder
// File system paths
define('BIZUNO_ROOT',      ''); // relative path to bizuno root index file
define('BIZUNO_LIB',       BIZUNO_ROOT.'lib/'); // file system path to Bizuno Library
define('BIZUNO_EXT',       BIZUNO_ROOT.'ext/'); // file system path to Bizuno Extensions
define('BIZUNO_CUSTOM',    BIZUNO_ROOT.'myExt/'); // file system path to Bizuno custom extensions
define('BIZUNO_DATA',      BIZUNO_ROOT.'myFiles/'); // myFolder
define('BIZUNO_ICONS',     BIZUNO_ROOT.'icons/'); // file system path to extra icon sets
define('BIZUNO_THEMES',    BIZUNO_ROOT.'themes/'); // file system path to extra themes

require_once(BIZUNO_LIB."controller/functions.php");
require_once(BIZUNO_LIB."locale/cleaner.php");
bizAutoLoad(BIZUNO_LIB."model/msg.php", 'messageStack');
$msgStack= new messageStack();
$cleaner= new cleaner();
$icnSet = clean('icons',['format'=>'cmd','default'=>$style],   'get');
$creds  = explode('.', clean('code', ['format'=>'float','default'=>'0'], 'get')); // bizID.userID
$bizID  = $creds[0];
$userID = !empty($creds[1]) ? $creds[1] : 0;

$path   = $icnSet=='default' ? BIZUNO_LIB."view/icons/" : BIZUNO_ICONS;
$pathURL= $icnSet=='default' ? BIZUNO_URL."view/icons/" : BIZUNO_ICONS;
if (!file_exists("{$path}$icnSet.php")) { // icons cannot be found, use default
    $icnSet  = 'default';
    $path    = BIZUNO_LIB."view/icons/";
    $pathURL = BIZUNO_URL."view/icons/";
}
$output = '';
if ($userID) { // fetch the users Profile for icon set and font stlye
    $output .= "a, div, body, html, table{ font:normal normal 11px Comic Sans MS; }\n";
}
$icons = [];
require("{$path}$icnSet.php");
foreach ($icons as $idx => $icon) {
    $output .= ".icon-$idx  { background:url('{$pathURL}$icnSet/16x16/{$icon['path']}') no-repeat; }\n";
    $output .= ".iconM-$idx { background:url('{$pathURL}$icnSet/24x24/{$icon['path']}') no-repeat; }\n";
    $output .= ".iconL-$idx { background:url('{$pathURL}$icnSet/32x32/{$icon['path']}') no-repeat; }\n";
}
$output .= dirCSS(BIZUNO_EXT);// Now extensions
$output .= dirCSS(BIZUNO_CUSTOM); // Now custom

header("Content-type: text/css; charset: UTF-8");
header("Content-Length: ".strlen($output));
echo $output;
die;

function dirCSS($dir) {
    $output = '';
    $extenions = is_dir($dir) ? scandir($dir) : [];
    foreach ($extenions as $extension) {
        if ($extension == '.' || $extension == '..' || !is_dir("$dir/$extension")) { continue; }
        $output .= addCSS($dir, $extension);
    }
    return $output;
}

function addCSS($dir, $ext) {
    $output = '';
    if ($ext == '.' || $ext == '..' || !is_dir("$dir/$ext")) { return $output; }
    $files = scandir("$dir/$ext");
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') { continue; }
        $path_parts = pathinfo($file);
        if (!isset($path_parts['extension'])) { continue; }
        if (!in_array(strtolower($path_parts['extension']), ['png','jpg','jpeg', 'gif'])) { continue; }
        if ($path_parts['filename'] == 'icon16') { $output .= ".icon-$ext  { background:url('".BIZUNO_SRVR."{$dir}$ext/$file') no-repeat; }\n"; }
        if ($path_parts['filename'] == 'icon24') { $output .= ".iconM-$ext { background:url('".BIZUNO_SRVR."{$dir}$ext/$file') no-repeat; }\n"; }
        if ($path_parts['filename'] == 'icon32') { $output .= ".iconL-$ext { background:url('".BIZUNO_SRVR."{$dir}$ext/$file') no-repeat; }\n"; }
    }
    return $output;
}
