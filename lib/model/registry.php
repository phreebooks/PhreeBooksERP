<?php
/*
 * Registry class used to manage user/business environmental variables and settings
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
 * @copyright  2008-2019, PhreeSoft Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-02-08
 * @filesource /lib/model/registry.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_ROOT."portal/guest.php", 'guest');

final class bizRegistry
{
    /**
     * Initializes the registry class
     */
    function __construct() {
        $this->dbVersion = MODULE_BIZUNO_VERSION;
        $this->guest = new guest();
    }

    /**
     * takes basic module properties and builds interdependencies
     */
    public function initRegistry($usrEmail='', $bizID=0)
    {
        global $bizunoMod, $bizunoUser; //, $bizunoLang;
        msgDebug("\nEntering initRegistry with email = $usrEmail");
        $bizunoMod = $this->initSettings();
        $this->initModules($bizunoMod);
        if (!$this->initUser($usrEmail, $bizID)) { return; }
        $this->initAccount($bizunoMod);
        $this->setUserSecurity(getUserCache('profile', 'email'));
        $this->setUserMenu('quickBar');
        $bizunoUser['quickBar']['child']['home']['label'] = getUserCache('profile', 'title', false, lang('bizuno_company'));
        $this->setUserMenu('menuBar');
        // Unique module initializations
        $this->initBizuno($bizunoMod);
        $this->initPhreeBooks($bizunoMod); // taxes
        $this->initPhreeForm($bizunoMod); // report structure
        dbWriteCache($usrEmail, true);
        msgDebug("\nReturning from initRegistry"); // with bizunoUser: ".print_r($bizunoUser,true));
//      msgDebug("\nReturning from initRegistry with bizunoMod: " .print_r($bizunoMod, true));
    }

    /**
     * Load original configuration, properties get reloaded but other
     * @return type
     */
    private function initSettings()
    {
        $modSettings = [];
        $rows = dbGetMulti(BIZUNO_DB_PREFIX.'configuration');
        foreach ($rows as $row) {
            $bizData = json_decode($row['config_value'], true);
            if (!empty($bizData['properties']['status'])) {
                $modSettings[$row['config_key']] = $bizData;
                unset($modSettings[$row['config_key']]['hooks']); // will clear hooks to be rebuilt later
            }
        }
        // get the Bizuno database version and retain for upgrade check
        $this->dbVersion = !empty($modSettings['bizuno']['properties']['version']) ? $modSettings['bizuno']['properties']['version'] : '1.0';
        msgDebug("\ndbVersion has been stored for bizuno module with $this->dbVersion");
        return $modSettings;
    }

    /**
     * get all available modules and paths, guess default language
     * @param type $bizunoMod
     */
    private function initModules(&$bizunoMod)
    {
        $modList = $this->guest->getModuleList();
        foreach ($modList as $module => $path) {
            if (isset($bizunoMod[$module])) { $this->initModule($module, $path); }
        }
    }

    /**
     * Initializes a single module
     * @global array $bizunoMod - working module registry
     * @param string $module - module to initialize
     * @param string $path - path to module
     * @return updated $bizunoMod
     */
    public function initModule($module, $path)
    {
        global $bizunoMod;
        if (!file_exists("{$path}admin.php")) {
            unset($bizunoMod[$module]);
            // @todo delete the configuration db entry as the module has been removed manually and cannot be found
            return msgAdd("initModule cannot find module $module at path: $path");
        }
        msgDebug("\nBuilding registry for module $module and path $path");
        $fqcn  = "\\bizuno\\{$module}Admin";
        bizAutoLoad("{$path}admin.php", $fqcn);
        $admin = new $fqcn();
        if (isset($admin->structure['settings'])) {
            $bizunoMod[$module]['settings'] = getStructureValues($admin->structure['settings']); // for legacy Bizuno 1.x
        } else {
            $bizunoMod[$module]['settings'] = isset($admin->settings) ? $admin->settings : []; // user settings, will be defaults at first
        }
        // set some system properties
        $admin->structure['id']         = $module;
        $admin->structure['title']      = $admin->lang['title'];
        $admin->structure['description']= $admin->lang['description'];
        $admin->structure['path']       = $path;
        if (!isset($admin->structure['status'])) { $admin->structure['status'] = 1; }
        $this->setGlobalLang($admin->structure);
        $this->setHooks($admin->structure, $module, $path);
        $this->setAPI($admin->structure);
        $this->initMethods($admin->structure);
        if (method_exists($admin, 'initialize')) { $admin->initialize(); }
        unset($admin->structure['lang']);
        unset($admin->structure['hooks']);
        unset($admin->structure['api']);
        $bizunoMod[$module]['properties']= $admin->structure;
        // Restore Bizuno database version for upgrade check. If the dbVersion is the same, then nothing is done
        if ($module=='bizuno') { $bizunoMod['bizuno']['properties']['version'] = $this->dbVersion; }
//      msgDebug("\nbizunoMod for module $module has properties: ".print_r($bizunoMod[$module]['properties'], true));
        msgDebug("\ndbVersion has been set in bizuno module to $this->dbVersion");
        $GLOBALS['updateModuleCache'][$module] = true;
    }

    /**
     * Initializes user registry
     * @global array $bizunoUser
     * @param string $usrEmail
     * @param integer $bizID
     * @return boolean
     */
    private function initUser($usrEmail, $bizID)
    {
        global $bizunoUser;
        msgDebug("\ninitUser with email = $usrEmail biz_id = $bizID");
        $lang = $bizunoUser['profile']['language']; // preserve the language selection
        if (!$row = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$usrEmail'", true, false)) { return; }
        msgDebug("\nRead original row from users table: ".print_r($row, true));
        $settings = json_decode($row['settings'], true);
        if (!isset($settings['profile'])) { $settings['profile'] = []; }
        $output = array_replace_recursive($bizunoUser['profile'], $settings['profile']);
        unset($row['settings']);
        $bizunoUser = ['profile' => array_replace_recursive($output, $row)];
        // set some known facts
        $bizunoUser['profile']['email']   = $usrEmail;
        $bizunoUser['profile']['biz_id']  = $bizID;
        $bizunoUser['profile']['language']= $lang;

        // OLD WAY - DEPRECATED SETTING CAN BE DELETED AFTER 30 DAYS FROM 9/15/2018 also in view.php
        $colors = getUserCache('profile', 'colors');
        if (!empty($colors)) {
            $icons = getUserCache('profile', 'theme', false, 'default');
            setUserCache('profile', 'icons', $icons);
            setUserCache('profile', 'theme', $colors);
            clearUserCache('profile', 'colors');
        } // END OLD WAY

        // Check to make sure the themes and icons folders are still there
        $theme = getUserCache('profile', 'theme');
        if ('default' != $theme) {
            if (!defined('BIZUNO_THEMES') || !is_dir(BIZUNO_THEMES.$theme)) { setUserCache('profile', 'theme', 'default'); }
        }
        $icons = getUserCache('profile', 'icons');
        if ('default' != $icons) {
            if (!defined('BIZUNO_ICONS')  || !is_dir(BIZUNO_ICONS.$icons))  { setUserCache('profile', 'icons', 'default'); }
        }
        $GLOBALS['updateUserCache'] = true;
        return true;
    }

    /**
     * Contacts PhreeSoft with module list and get subscription status
     */
    private function initAccount(&$bizunoMod)
    {
        if (!empty($GLOBALS['skipUpgradeCheck'])) { return; }
        $io      = new io(); // needs to be here as global may not be set up yet
        $myAcct  = $io->apiPhreeSoft('getMyExtensions');
        $messages= [];
        // check for new version of Bizuno
        msgDebug("\nComparing this version: ".MODULE_BIZUNO_VERSION." with Phreesoft.com version: {$myAcct['bizuno']['version']}");
        // Check for new version AND add message/download icon ONLY if host doen't handle the upgrade
        if (in_array(BIZUNO_HOST, ['phreebooks']) && version_compare(MODULE_BIZUNO_VERSION, $myAcct['bizuno']['version']) < 0) {
            $messages[] = ['msg_id'=>'BIZ:'.$myAcct['bizuno']['version'], 'subject'=>"Bizuno Library Version {$myAcct['bizuno']['version']} Released!"];
            $this->addUpgrade = true; // add the download icon
        }
        $myPurchases = $this->reSortExtensions($myAcct);
        msgDebug("\nmyPurchases = ".print_r($myPurchases, true));
        foreach ($myPurchases as $mID => $settings) {
            msgDebug("\nChecking status for business ID = ".getUserCache('profile', 'biz_id')." and module: $mID with installed status = ".getModuleCache($mID, 'properties', 'status', false, 0));
            if (empty($settings['paid']) && getModuleCache($mID, 'properties', 'status', false, 0)) { // set status to disabled
                msgDebug("\nDisabling status for business ID = ".getUserCache('profile', 'biz_id')." and module: $mID");
                $this->setModuleStatus($mID, 0);
                continue;
            } elseif (!empty($settings['paid']) && !getModuleCache($mID, 'properties', 'status', false, 0)) { // set status to disabled
                msgDebug("\nEnabling status for business ID = ".getUserCache('profile', 'biz_id')." and module: $mID");
                $this->setModuleStatus($mID, $settings['version']);
                continue;
            }
//          if (!empty($myAcct[$mID]['msg'])) { $messages[] = $myAcct[$mID]['msg']; } // check for messages and add to msgSys, expirations, news updates
            msgDebug("\ncomparing extension $mID local version ".getModuleCache($mID, 'properties', 'version', false, 0)." with PhreeSoft.com version {$settings['version']}");
            if (!$settings['paid'] || !getModuleCache($mID, 'properties', 'version')) { continue; }
            if (!in_array(BIZUNO_HOST, ['phreesoft']) && version_compare($settings['version'], getModuleCache($mID, 'properties', 'version', false, 0))) { // compare versions, add messages if reminder to renew or expired
                $messages[] = ['msg_id'=>"EXT:$mID:{$settings['version']}", 'subject'=>"Extension: $mID Version {$settings['version']} is Available!"];
            }
        }
        msgSysWrite($messages);
    }

    private function setModuleStatus($mID, $status=0)
    {
        global $bizunoMod;
        if ($status) {
            $props= dbGetValue(BIZUNO_DB_PREFIX.'configuration', 'config_value', "config_key='$mID'");
            $vals = json_decode($props, true);
            $vals['properties']['status'] = $status;
            $bizunoMod[$mID] = $vals;
            $GLOBALS['updateModuleCache'][$mID] = true;
        } else { setModuleCache($mID, 'properties', 'status', 0); }
    }

    /**
     * Load any system wide language to the registry language cache
     * @global type $structure
     */
    public function setGlobalLang($structure)
    {
        global $bizunoLang;
        if (!isset($structure['lang'])) { return; }
        foreach ($structure['lang'] as $key => $value) { $bizunoLang[$key] = $value; }
    }


    /**
     * Sets the hooks array from a given module, if present
     * @param array $structure - array of hooks for the requested module
     * @param string $hookID -
     * @return type
     */
    public function setHooks($structure, $module, $path)
    {
        global $bizunoMod;
        if (!isset($structure['hooks'])) { return; }
        foreach ($structure['hooks'] as $mod => $page) {
            foreach ($page as $pageID => $pageProps) {
                foreach ($pageProps as $method => $methodProps) {
                    $methodProps['path'] = $path;
                    $bizunoMod[$mod]['hooks'][$pageID][$method][$module] = $methodProps;
                }
            }
        }
    }

    /**
     *
     * @global array $bizunoMod
     * @param type $structure
     * @return type
     */
    private function setAPI($structure)
    {
        global $bizunoMod;
        if (!isset($structure['api'])) { return; }
        $bizunoMod['bizuno']['api'][$structure['id']] = $structure['api'];
    }

    /**
     *
     * @global array $bizunoUser
     * @param array $bizunoMod
     */
    private function initBizuno(&$bizunoMod)
    {
        global $bizunoUser;
        $bizunoMod['bizuno']['stores'] = dbGetStores();
    }

    /**
     *
     * @param array $bizunoMod
     */
    private function initPhreeBooks(&$bizunoMod)
    {
        $date    = date('Y-m-d');
        $output  = [];
        $taxRates= dbGetMulti(BIZUNO_DB_PREFIX."tax_rates");
        foreach ($taxRates as $row) { // Needs to be auto indexed so the javascript doesn't break
            if     (!empty($row['inactive'])) { $row['status'] = 2;}
            elseif ($row['start_date']>=$date || $row['end_date']<=$date) { $row['status'] = 1; }
            else   { $row['status'] = 0; }
            $row['rate'] = $row['tax_rate'];
//            unset($row['tax_rate']); // This can be deleted after 4/15/2018
            $row['settings'] = json_decode($row['settings'], true);
            $output[] = $row;
        }
        $byTitle = sortOrder($output, 'text');
        $byLock  = sortOrder($byTitle,'status');
        // spilt by type
        $bizunoMod['phreebooks']['sales_tax'] = [];
        foreach ($byLock as $row) { $bizunoMod['phreebooks']['sales_tax'][$row['type']][] = $row; }
    }

    /**
     *
     * @param array $bizunoMod
     */
    private function initPhreeForm(&$bizunoMod)
    {
        $bizunoMod['phreeform']['rptGroups'] = [];
        $bizunoMod['phreeform']['frmGroups'] = [];
        $result = dbGetMulti(BIZUNO_DB_PREFIX.'phreeform', "mime_type='dir'", "group_id, title");
        foreach ($result as $row) {
            if (strpos($row['group_id'], ':')===false) {
                $cat = lang($row['title']);
            } elseif (strpos($row['group_id'], ':rpt')) { // report folder
                $bizunoMod['phreeform']['rptGroups'][] = ["id"=>$row['group_id'], "text"=>$cat." - ".lang('reports')];
            } else { // form folder
                $bizunoMod['phreeform']['frmGroups'][] = ["id"=>$row['group_id'], "text"=>$cat." - ".lang($row['title'])];
            }
        }
        $processing = $formatting = $separators = [];
        foreach (array_keys($bizunoMod) as $module) {
            if (!class_exists("\\bizuno\\{$module}Admin")) { continue; }
            $fqcn  = "\\bizuno\\{$module}Admin";
            $admin = new $fqcn();
            if (isset($admin->phreeformProcessing)) { $processing = array_merge($processing, $admin->phreeformProcessing); }
            if (isset($admin->phreeformFormatting)) { $formatting = array_merge($formatting, $admin->phreeformFormatting); }
            if (isset($admin->phreeformSeparators)) { $separators = array_merge($separators, $admin->phreeformSeparators); }
        }
        $bizunoMod['phreeform']['processing'] = $processing;
        $bizunoMod['phreeform']['formatting'] = $formatting;
        $bizunoMod['phreeform']['separators'] = $separators;
    }

    /**
     *
     * @param type $usrEmail
     */
    private function setUserSecurity($usrEmail)
    {
        $roleID= getUserCache('profile', 'role_id');
        $role  = dbGetRow(BIZUNO_DB_PREFIX."roles", "id='$roleID'");
        if ($role) {
            $role['settings'] = json_decode($role['settings'], true);
            setUserCache('security', false, is_array($role['settings']['security']) ? $role['settings']['security'] : []);
        }
    }

    /**
     * organizes and sorts the quick links in the menu bar
     * @return array links sorted and organized
     */
    public function setUserMenu($menuID)
    {
        global $bizunoUser, $bizunoMod;
        msgDebug("\nSetting menu ID = $menuID");
        $links = [];
        foreach ($bizunoMod as $module => $data) {
            if (!isset($data['properties'][$menuID])) { continue; }
            $links = array_replace_recursive($links, $data['properties'][$menuID]);
            unset($bizunoMod[$module]['properties'][$menuID]);
        }
        if ($menuID=='quickBar' && validateSecurity('bizuno', 'admin', 1, false)) {
            $sysMsgs = dbGetMulti(BIZUNO_DB_PREFIX."phreemsg", "status='0'");
            if (sizeof($sysMsgs)) { $links['child']['sysMsg']['attr']['value'] = sizeof($sysMsgs); }
            if (!empty($this->addUpgrade) && (!defined('BIZUNO_HOST_UPGRADE') || !constant('BIZUNO_HOST_UPGRADE'))) {
                $links['child']['upgrade'] = ['order'=>0,'label'=>lang('bizuno_upgrade'),'icon'=>'download','required'=>true,'hideLabel'=>true,
                    'events'=>['onClick'=>"hrefClick('bizuno/backup/managerUpgrade');"]];
            }
        }
        $this->removeOrphanMenus($links['child'], getUserCache('security', false, false, []));
        $bizunoUser[$menuID] = sortOrder($links);
    }

    /**
     * Removes main menu heading if there are no sub menus underneath
     * @param array $menu - working menu
     * @return integer - maximum security value found during the removal process
     */
    public function removeOrphanMenus(&$menu, $userSecurity)
    {
        $security = 0;
        foreach ($menu as $key => $props) {
            if (isset($props['child'])) {
                $menu[$key]['security'] = $this->removeOrphanMenus($menu[$key]['child'], $userSecurity);
            } elseif (!empty($menu[$key]['required'])) {
                $menu[$key]['security'] = 4;
                setUserCache('security', $key, $menu[$key]['security']);
            } else {
                $menu[$key]['security'] = array_key_exists($key, $userSecurity) ? $userSecurity[$key] : 0;
            }
            if (!$menu[$key]['security']) {
                unset($menu[$key]);
                continue;
            }
            $security = max($security, $menu[$key]['security']);
        }
        return $security;
    }

    /**
     * Creates a list of available stores, including main store for use in views
     * @param boolean $addAll - [default false] Adds option All at top of list
     * @return arrray - ready to render as pull down
     */
    public function setUserStores()
    {
        global $bizunoUser;
        $output[] = ['id'=>0, 'text'=>getModuleCache('bizuno', 'settings', 'company', 'id')];
        $result = dbGetMulti(BIZUNO_DB_PREFIX."contacts", "type='b'", "short_name");
        foreach ($result as $row) { $output[] = ['id'=>$row['id'], 'text'=>$row['short_name']]; }
        $bizunoUser['stores'] = $output;
    }

    /**
     *
     */
    public function initMethods($structure)
    {
        if (!isset($structure['dirMethods']))    { $structure['dirMethods'] = []; }
        if (!is_array($structure['dirMethods'])) { $structure['dirMethods'] = [$structure['dirMethods']]; }
        $structure['dirMethods'][] = 'dashboards'; // auto-add dashboards
        $methods = [];
        foreach ($structure['dirMethods'] as $folderID) {
            msgDebug("\ninitMethods is looking at module: {$structure['id']} and path {$structure['path']} and folder $folderID");
            if (!file_exists($structure['path']."$folderID/")) { continue; }
            msgDebug("\nreading methods");
            $this->methodRead($methods, $structure['path']."$folderID/");
            if (defined('BIZUNO_CUSTOM') && $folderID <> 'dashboards') {
                msgDebug("\ninitMethods is looking at customizations for module: {$structure['id']} and folder $folderID");
                $this->methodRead($methods, BIZUNO_CUSTOM."{$structure['id']}/$folderID/");
            }
            $this->cleanMissingMethods($structure['id'], $folderID, $methods);
            $this->initMethodList($structure, $folderID, $methods);
        }
    }

    /**
     *
     * @global array $bizunoMod
     * @param type $structure
     * @param type $folderID
     * @param type $methods
     */
    private function initMethodList($structure, $folderID, $methods)
    {
        global $bizunoMod;
        $module = $structure['id'];
        msgDebug("\ninitMethodList is looking at number of methods = ".(sizeof($methods)-2));
        foreach ($methods as $method => $path) {
            if (in_array($method, ['.', '..'])) { continue; }
            $settings = getModuleCache($module, $folderID, $method);
            if (empty($settings['settings'])) { $settings['settings'] = []; }
            if ($folderID=='dashboards') { $settings['status'] = 1;} // all dashboards are loaded into cache and user decide which to enable and where
            if (defined('BIZUNO_CUSTOM') && strpos($path, BIZUNO_CUSTOM) !== false) {
                $bizID = getUserCache('profile', 'biz_id');
                $myPath= str_replace(BIZUNO_DATA, '', BIZUNO_CUSTOM);
                $url   = BIZUNO_URL_FS."&src=$bizID/{$myPath}$module/$folderID/$method/";
            } else {
                $url   = isset($structure['url']) ? "{$structure['url']}$folderID/$method/" : '';
            }
            $fqcn = "\\bizuno\\$method";
            bizAutoLoad("{$path}$method.php", $fqcn);
            $clsMeth = new $fqcn($settings['settings']);
            $bizunoMod[$module][$folderID][$method] = [
                'id'         => $method,
                'title'      => $clsMeth->lang['title'],
                'description'=> $clsMeth->lang['description'],
                'path'       => $path,
                'url'        => $url,
                'status'     => 0];
            if (!empty($settings['status'])) {
                $bizunoMod[$module][$folderID][$method] = array_replace_recursive($bizunoMod[$module][$folderID][$method], [
                    'status'     => 1,
                    'acronym'    => isset($clsMeth->lang['acronym']) ? $clsMeth->lang['acronym']: $clsMeth->lang['title'],
                    'default'    => isset($clsMeth->settings['default']) && $clsMeth->settings['default'] ? 1 : 0,
                    'order'      => isset($clsMeth->settings['order']) ? $clsMeth->settings['order'] : 50,
                    'settings'   => isset($clsMeth->settings) ? $clsMeth->settings : []]);
            } else { continue; }
            if (isset($clsMeth->structure)) { $this->setHooks($clsMeth->structure, $method, $path); }
            unset($bizunoMod[$module][$folderID][$method]['hooks']);
        }
        $bizunoMod[$module][$folderID] = sortOrder($bizunoMod[$module][$folderID], 'title');
    }

    public function methodRead(&$methods, $path)
    {
        $output = [];
        if (!@is_dir($path)) { return $output; }
        $temp = scandir($path);
        foreach ($temp as $fn) {
            if ($fn == '.' || $fn == '..') { continue; }
            $methods[$fn] = "{$path}$fn/";
        }
        return $output;
    }

    /**
    * This function cleans out stored registry values that have be orphaned in the configuration database table.
    * @param string $module - Module ID
    * @param string $folderID - Method ID
    * @param array $methods - List of all available methods in the specified folder
    * @return null
    */
    public function cleanMissingMethods($module, $folderID, $methods=[])
    {
        global $bizunoMod;
        if (!isset($bizunoMod[$module][$folderID]) || !is_array($methods)) { return; }
        $cache = array_keys($bizunoMod[$module][$folderID]);
        $allMethods = array_keys($methods);
        foreach ($cache as $method) {
            if (!in_array($method, $allMethods)) {
                msgAdd("Module: $module, folder: $folderID, Deleting missing method: $method");
                unset($bizunoMod[$module][$folderID][$method]);
            }
        }
    }

    private function reSortExtensions($myAcct)
    {
        $output = [];
        if (empty($myAcct['extensions'])) { return []; }
        foreach ($myAcct['extensions'] as $cat) {
            foreach ($cat as $mID => $props) { $output[$mID] = $props; }
        }
        return $output;
    }
}