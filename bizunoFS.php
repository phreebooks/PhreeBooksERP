<?php
/*
 * PhreeBooks 5 - Pulls a file from the data folder and outputs it
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
 * @version    3.x Last Update: 2018-09-14
 * @filesource /bizunoFS.php
 */

namespace bizuno;

@include("bizunoCFG.php");

// Host Information/source paths
define('BIZUNO_HOST',      'phreebooks'); // PhreeBooks 5 hosted
define('BIZUNO_HOME',      'index.php?'); // filename of the main entry index script
define('BIZUNO_AJAX',      'index.php?'); // root path for AJAX requests
// URL paths
$path = pathinfo($_SERVER["SCRIPT_NAME"], PATHINFO_DIRNAME);
define('BIZUNO_SRVR',      "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER["SERVER_NAME"].$path.'/'); // url to server with trailing slash
define('BIZUNO_LOGO',      BIZUNO_SRVR.'phreebooks.png');
define('BIZUNO_URL',       BIZUNO_SRVR.'lib/'); // full url to Bizuno plugin library folder
define('BIZUNO_URL_FS',    BIZUNO_SRVR.'bizunoFS.php?'); // full url to Bizuno plugin extensions folder
define('BIZUNO_URL_EXT',   BIZUNO_SRVR.'ext/'); // full url to Bizuno plugin extensions folder
define('BIZUNO_URL_CUSTOM',BIZUNO_SRVR.'myExt/'); // full url to Bizuno plugin custom extensions folder
// File system paths
define('BIZUNO_ROOT',      dirname(__FILE__).'/'); // relative path to bizuno root index file
define('BIZUNO_LIB',       BIZUNO_ROOT.'lib/'); // file system path to Bizuno Library
define('BIZUNO_EXT',       BIZUNO_ROOT.'ext/'); // file system path to Bizuno Extensions
define('BIZUNO_DATA',      BIZUNO_ROOT.'myFiles/'); // myFolder
define('BIZUNO_CUSTOM',    BIZUNO_ROOT.'myExt/'); // file system path to Bizuno custom extensions
// Database
define('PORTAL_DB_PREFIX', BIZUNO_DB_PREFIX); // Portal table prefix
$GLOBALS['dbBizuno'] = $GLOBALS['dbPortal'] = ['type'=>'mysql','host'=>BIZUNO_DB_HOST,'name'=>BIZUNO_DB_NAME,'user'=>BIZUNO_DB_USER,'pass'=>BIZUNO_DB_PASS,'prefix'=>BIZUNO_DB_PREFIX];

require("portal/functions.php");
require_once(BIZUNO_LIB."controller/functions.php");
require_once(BIZUNO_LIB."locale/cleaner.php");
require_once(BIZUNO_LIB."model/db.php");
require_once(BIZUNO_LIB."model/msg.php");
$msgStack= new messageStack();
$cleaner = new cleaner;
$parts   = explode('/', clean('src', 'path_rel', 'get'), 2);
$fn      = BIZUNO_DATA.$parts[1];
if (!file_exists($fn)) { 
    $fn = BIZUNO_ROOT . str_replace(BIZUNO_SRVR, '', BIZUNO_LOGO);
}
header("Accept-Ranges: bytes");
header("Content-Type: "  .getMimeType($fn));
header("Content-Length: ".filesize($fn));
header("Last-Modified: " .date(DATE_RFC2822, filemtime($fn)));
readfile($fn);
die;

function getMimeType($filename)
{
    $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
    switch ($ext) {
        case "aiff":
        case "aif":  return "audio/aiff";
        case "avi":  return "video/msvideo";
        case "bmp":
        case "gif":
        case "png":
        case "tiff": return "image/$ext";
        case "css":  return "text/css";
        case "csv":  return "text/csv";
        case "doc":
        case "dot":  return "application/msword";
        case "docx": return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        case "dotx": return "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
        case "docm": return "application/vnd.ms-word.document.macroEnabled.12";
        case "dotm": return "application/vnd.ms-word.template.macroEnabled.12";
        case "gz":
        case "gzip": return "application/x-gzip";
        case "html":
        case "htm":
        case "php":  return "text/html";
        case "jpg":
        case "jpeg":
        case "jpe":  return "image/jpg";
        case "js":   return "application/x-javascript";
        case "json": return "application/json";
        case "mp3":  return "audio/mpeg3";
        case "mov":  return "video/quicktime";
        case "mpeg":
        case "mpe":
        case "mpg":  return "video/mpeg";
        case "pdf":  return "application/pdf";
        case "pps":
        case "pot":
        case "ppa":
        case "ppt":  return "application/vnd.ms-powerpoint";
        case "pptx": return "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        case "potx": return "application/vnd.openxmlformats-officedocument.presentationml.template";
        case "ppsx": return "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
        case "ppam": return "application/vnd.ms-powerpoint.addin.macroEnabled.12";
        case "pptm": return "application/vnd.ms-powerpoint.presentation.macroEnabled.12";
        case "potm": return "application/vnd.ms-powerpoint.template.macroEnabled.12";
        case "ppsm": return "application/vnd.ms-powerpoint.slideshow.macroEnabled.12";
        case "rtf":  return "application/rtf";
        case "swf":  return "application/x-shockwave-flash";
        case "txt":  return "text/plain";
        case "tar":  return "application/x-tar";
        case "wav":  return "audio/wav";
        case "wmv":  return "video/x-ms-wmv";
        case "xla":
        case "xlc":
        case "xld":
        case "xll":
        case "xlm":
        case "xls":
        case "xlt":
        case "xlt":
        case "xlw":  return "application/vnd.ms-excel";
        case "xlsx": return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        case "xltx": return "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
        case "xlsm": return "application/vnd.ms-excel.sheet.macroEnabled.12";
        case "xltm": return "application/vnd.ms-excel.template.macroEnabled.12";
        case "xlam": return "application/vnd.ms-excel.addin.macroEnabled.12";
        case "xlsb": return "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
        case "xml":  return "application/xml";
        case "zip":  return "application/zip";
        default:
            if (function_exists(__NAMESPACE__.'\mime_content_type')) { # if mime_content_type exists use it.
                $m = mime_content_type($filename);
            } else {    # if nothing left try shell
                if (strstr($_SERVER[HTTP_USER_AGENT], "Windows")) { # Nothing to do on windows
                    return ""; # Blank mime display most files correctly especially images.
                }
                if (strstr($_SERVER[HTTP_USER_AGENT], "Macintosh")) { $m = trim(exec('file -b --mime '.escapeshellarg($filename))); }
                else { $m = trim(exec('file -bi '.escapeshellarg($filename))); }
            }
            $m = explode(";", $m);
            return trim($m[0]);
    }
}
