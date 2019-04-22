<?php
/*
 * PhreeBooks 5 - main entry controller
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
 * @version    3.x Last Update: 2019-04-08
 * @filesource /portal/main.php
 */

namespace bizuno;

// set some sitewide constants
define('COG_ITEM_TYPES', 'ma,mi,ms,sa,si,sr');

require_once(BIZUNO_ROOT."portal/functions.php");
require_once(BIZUNO_LIB ."controller/functions.php");
require_once(BIZUNO_LIB ."locale/cleaner.php");
require_once(BIZUNO_LIB ."locale/currency.php");
require_once(BIZUNO_LIB ."model/db.php");
require_once(BIZUNO_LIB ."model/io.php");
require_once(BIZUNO_LIB ."model/msg.php");
require_once(BIZUNO_LIB ."view/main.php");
require_once(BIZUNO_LIB ."view/easyUI/html5.php");

class main //extends controller
{
    public $layout = [];

    function __construct()
    {
        global $msgStack, $cleaner, $html5, $io;
        $msgStack = new messageStack();
        $cleaner  = new cleaner();
        $html5    = new html5();
        $io       = new io();
        $GLOBALS['myDevice'] = detectDevice(); // 'desktop' or 'mobile';
//      $GLOBALS['myDevice'] = 'mobile'; // for testing mobile behavior on desktop devices
        $this->initDB();
        $this->validateUser();
        $this->validateBusiness();
        $this->initBusiness();
        $this->initUserCache();
        $this->initModuleCache();
        clean('p', ['format'=>'command','default'=>'bizuno/main/bizunoHome'], 'get');
        if (getUserCache('profile', 'biz_id', false, 0)) { // keep going
        } elseif (!in_array($GLOBALS['bizunoModule'], ['bizuno'])) { // not logged in or not installed, restrict to parts of module bizuno
            $_GET['p'] = '';
            clean('p', ['format'=>'command','default'=>'bizuno/main/bizunoHome'], 'get');
        }
        msgDebug("\n compose lang = ".getUserCache('profile','language'));
        compose($GLOBALS['bizunoModule'], $GLOBALS['bizunoPage'], $GLOBALS['bizunoMethod'], $this->layout);
        return $this->layout;
    }

    private function initDB()
    {
        global $db;
        msgDebug("\nEntering initDB"); // with dbCreds = ".print_r($GLOBALS['dbBizuno'], true));
        $db = new db($GLOBALS['dbBizuno']);
        msgDebug(" ... after db connection connected = ".($db->connected?'true':'false'));
    }

    private function validateUser()
    {
        global $bizunoUser, $bizunoLang;
        $bizunoUser = $this->setGuestCache();
        $this->setLanguage($bizunoUser);
        $session = clean('bizunoSession', 'json', 'cookie');
        msgDebug("\nEntering validateUser with session = ".print_r($session, true)." and lang = ".$bizunoUser['profile']['language']);
        if ($session && constant('BIZUNO_DB_NAME') !== '') { $this->setSession($session); }
        else { // not logged in, try to log in
            if (!$email= clean('UserID','email','post')) { return; }
            if (!$pass = clean('UserPW','text', 'post')) { return; }
            if (!biz_validate_user_creds($email, $pass)) { return; }
            $bizunoUser['profile']['email'] = $email;
            $cookie = "[\"{$bizunoUser['profile']['email']}\",0,".time()."]";
            $_COOKIE['bizunoSession'] = $cookie;
            setcookie('bizunoSession', $cookie, time()+(60*60*1), "/"); // 1 hour
        }
        // refresh cookies
        setcookie('bizunoUser', $bizunoUser['profile']['email'],   time()+(60*60*24*7)); // 7 days
        msgDebug("\nLeaving validateUser with email = {$bizunoUser['profile']['email']}");
        return true;
    }

    private function setSession($creds)
    {
        global $bizunoUser;
        msgDebug("\nEntering setSession");
        if (isset($creds[0])) { $bizunoUser['profile']['email']  = $creds[0]; }
        if (isset($creds[1])) { $bizunoUser['profile']['biz_id'] = $creds[1]; }
        if (isset($creds[2])) { $bizunoUser['profile']['session']= $creds[2]; }
        $GLOBALS['updateUserCache'] = false;
        $GLOBALS['updateModuleCache'] = [];
    }

    private function validateBusiness()
    {
        global $bizunoUser;
        msgDebug("\nEntering validateBusiness with biz_id = ".getUserCache('profile', 'biz_id'));
        if (getUserCache('profile', 'biz_id')) { return true; } // logged in and business selected
        if (constant('BIZUNO_DB_NAME')=='') { $this->setInstallView(); } // logged in but configure.php file is not set, install
        if (!$email = getUserCache('profile', 'email')) { return; } // not logged in
        setUserCache('profile', 'biz_id', 1);
        portalWrite('users', ['last_login'=>date('Y-m-d h:i:s')], 'update', "biz_user='$email'");
        $cookie = "[\"{$bizunoUser['profile']['email']}\",1,".time()."]";
        $_COOKIE['bizunoSession'] = $cookie;
        setcookie('bizunoSession', $cookie, time()+(60*60*12), "/"); // 12 hours
        msgDebug("\nReturning from validateBusiness");
   }

    private function initBusiness()
    {
        msgDebug("\nEntering initBusiness");
        if (!getUserCache('profile', 'biz_id')) { return; }
        msgDebug("\nReturning from initBusiness");
    }

    private function initUserCache()
    {
        global $bizunoUser, $bizunoLang, $currencies;
        $email = getUserCache('profile', 'email');
        msgDebug("\nEntering initUserCache with email = $email");
        if ($email && getUserCache('profile', 'biz_id')) {
            $usrData = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'");
            if ($usrData && $this->sessionExpired($usrData)) { // logged in, cache stale, need to reload
                $this->reloadCache($email);
            } elseif ($usrData) { // logged in, normal just get settings
                $bizunoUser = json_decode($usrData['settings'], true);
            } elseif (!$usrData && dbTableExists(BIZUNO_DB_PREFIX.'users')) {
                msgAdd("You do not have an account, please see your Bizuno administrator!");
                unset($_GET['p']);
            }
            $bizunoLang = $this->loadBaseLang(getUserCache('profile', 'language')); // load the environment language file, includes module add-ons
            setlocale(LC_ALL, getUserCache('profile', 'language').'.UTF-8');
        } elseif (!dbTableExists(BIZUNO_DB_PREFIX.'users')) { $this->setInstallView(); }
        $currencies = new currency();
    }

    private function initModuleCache()
    {
        global $bizunoMod;
        msgDebug("\nEntering initModuleCache");
        $bizunoMod = [];
        if (getUserCache('profile','biz_id') && empty($GLOBALS['noBizunoDB'])) { // logged in, fetch the cache from db
            $rows = dbGetMulti(BIZUNO_DB_PREFIX.'configuration');
            if (empty($rows)) { $this->setGuestRegistry(); }
            else {
                foreach ($rows as $row) { $bizunoMod[$row['config_key']] = json_decode($row['config_value'], true); }
                $this->validateVersion();
            }
        } else { // set guest registry
            $this->setGuestRegistry();
        }
        date_default_timezone_set(getModuleCache('bizuno', 'settings', 'locale', 'timezone', 'EST'));
    }

    private function setGuestRegistry()
    {
        msgDebug("\nSetting Guest module cache");
        require_once(BIZUNO_LIB ."model/registry.php");
        $registry = new bizRegistry();
        $registry->initModule('bizuno', BIZUNO_LIB."controller/module/bizuno/"); // load the bizuno structure
        if (file_exists(BIZUNO_EXT."myPortal/admin.php")) { $registry->initModule('myPortal', BIZUNO_EXT."myPortal/"); }
    }

    private function validateVersion()
    {
        global $io;
        $bizVer = getModuleCache('bizuno', 'versions', 'bizuno', false, ['time_last'=>time(),'version'=>MODULE_BIZUNO_VERSION]);
        if ($bizVer['time_last'] < (time()-(60*60*24*1))) { // only phone home once a day (week?)
            $libMods = $io->apiPhreeSoft('getMyExtensions'); // pull the master list/subscribed list of modules from phreesoft.com
            if (!empty($libMods['bizuno']['version']) && version_compare($libMods['bizuno']['version'], MODULE_BIZUNO_VERSION) > 0) {
                msgMerge("bizuno_upgrade: An upgrade to PhreeBooks 5 Version {$libMods['bizuno']['version']} is available! Automatic upgrade can be started in Bizuno Settings.");
            }
            $bizVer['time_last'] = time();
            setModuleCache('bizuno', 'versions', 'bizuno', $bizVer); // update cache
        }
        if (version_compare(MODULE_BIZUNO_VERSION, $bizVer['version']) > 0) {
            msgDebug("\nRetrieving file: ".BIZUNO_ROOT.'portal/upgrade.php');
            require(BIZUNO_ROOT.'portal/upgrade.php');
            bizunoUpgrade($bizVer['version']);
            msgDebug("\nUpdating cache with version ".MODULE_BIZUNO_VERSION);
            $bizVer['version'] = MODULE_BIZUNO_VERSION;
            setModuleCache('bizuno', 'versions', 'bizuno', $bizVer); // update cache
        }
    }

    private function sessionExpired($usrData)
    {
        $cache_date = substr($usrData['cache_date'], 0, 10);
        if ($cache_date == '') { return true; } // not logged in
        // check for stale cache
        $yesterday = localeCalculateDate(date('Y-m-d'), -1);
        if ($cache_date < $yesterday) { return true; }
    }

    private function reloadCache($usrEmail)
    {
        require_once(BIZUNO_LIB ."model/registry.php");
        $registry = new bizRegistry();
        $registry->initRegistry($usrEmail, 1);
    }

    private function setLanguage(&$bizunoUser)
    {
        global $bizunoLang;
        $getLang  = clean('lang',    'cmd', 'get'); // passed at site entry before login
        $postLang = clean('UserLang','cmd', 'post'); // passed with login
        if (!empty($getLang) && strlen($getLang)==5) {
            $bizunoUser['profile']['language'] = $getLang;
            setcookie('bizunoLang', $getLang,  time()+(60*60*24*7));
        } elseif (!empty($postLang) && strlen($postLang)==5) {
            $bizunoUser['profile']['language'] = $postLang;
            setcookie('bizunoLang', $postLang, time()+(60*60*24*7));
        } else {
            $bizunoUser['profile']['language'] = clean('bizunoLang',['format'=>'cmd','default'=>$bizunoUser['profile']['language']],'cookie');
        }
        $bizunoLang= $this->loadBaseLang($bizunoUser['profile']['language']);
    }

    private function loadBaseLang($lang='en_US')
    {
        msgDebug("\nEntering loadBaseLang with lang = $lang");
        $langCore = $langByRef = [];
        if (strlen($lang) <> 5) { $lang = 'en_US'; }
        if (defined('BIZUNO_DATA') && file_exists(BIZUNO_DATA."cache/lang_{$lang}.json")) {
            msgDebug("\nGetting $lang lang from cache/");
            $langCache = json_decode(file_get_contents(BIZUNO_DATA."cache/lang_{$lang}.json"), true);
        } else {
            require(BIZUNO_LIB."locale/en_US/language.php"); // pulls the current language in English
            include(BIZUNO_LIB."locale/en_US/langByRef.php"); // lang by reference (no translation required)
            $langCache = array_merge($langCore, $langByRef);
        }
        if ($lang <> 'en_US') {
            $otherLang = [];
            if (defined('BIZUNO_DATA') && file_exists(BIZUNO_DATA."cache/lang_{$lang}.json")) {
                $otherLang = json_decode(file_get_contents(BIZUNO_DATA."cache/lang_{$lang}.json"), true);
            } elseif (file_exists(BIZUNO_ROOT."locale/$lang/language.php")) {
                require(BIZUNO_ROOT."locale/$lang/language.php"); // pulls locale overlay
                include(BIZUNO_LIB ."locale/en_US/langByRef.php"); // lang by reference (reset after loading translation)
                $otherLang = array_merge($langCore, $langByRef);
            }
            $langCache = array_merge($langCache, $otherLang);
        }
        return $langCache;
    }

    public function setGuestCache($usrEmail='')
    {
        $settings = [
            'profile'=>[
                'email'    => $usrEmail,
                'admin_id' => 0,
                'biz_id'   => 0,
                'biz_title'=> 'My Business',
                'language' => clean('lang', ['format'=>'cmd','default'=>'en_US'], 'get'),
                'ssl'      => true],
            'dashboards'=> [
                'login' => ['column_id'=>0,'row_id'=>0,'module_id'=>'bizuno','dashboard_id'=>'login'],
                'tip'   => ['column_id'=>1,'row_id'=>2,'module_id'=>'bizuno','dashboard_id'=>'daily_tip'],
                'news'  => ['column_id'=>2,'row_id'=>3,'module_id'=>'bizuno','dashboard_id'=>'ps_news']]];
        if (clean('lost',   'cmd','get')=='true') { $settings['dashboards']['login']['dashboard_id'] = 'reset_password'; }
        if (clean('newuser','cmd','get')=='true') { $settings['dashboards']['login']['dashboard_id'] = 'new_user'; }
        return $settings;
    }

    private function setInstallView()
    {
        global $bizunoUser;
        $GLOBALS['bizuno_install_admin_id']= 1; // set flags used when requesting to install
        $GLOBALS['bizuno_install_biz_id']  = 1;
        $bizunoUser['dashboards']['login']['dashboard_id'] = 'install'; // replace the login dashboard with the install dashboard
    }
}
