<?php
/*
 * Bizuno Settings methods
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
 * @version    3.x Last Update: 2019-03-21
 * @filesource /lib/controller/module/bizuno/settings.php
 */

namespace bizuno;

class bizunoSettings
{
    public $notes        = [];
    public $moduleID     = 'bizuno';
    private $phreesoftURL= 'https://www.phreesoft.com';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }

    /**
     * Main Settings page, builds a list of all available modules and puts into groups
     * @param array $layout - structure coming in
     * @return modified $ayout
     */
    public function manager(&$layout=[])
    {
        global $io, $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 2)) { return; }
        $focus    = clean('cat', ['format'=>'text', 'default'=>'bizuno'], 'get');
        $myMods   = array_keys($bizunoMod);
        $libMods  = $io->apiPhreeSoft('getMyExtensions', ['usrMods'=>$myMods]); // pull the master list/subscribed list of modules from phreesoft.com
        msgDebug("\nReceived from PhreeSoft: ".print_r($libMods, true));
        if (!$libMods || !isset($libMods['extensions'])) { $libMods = ['extensions'=>[]]; }
        $modules  = array_replace_recursive($libMods['extensions'], $this->getLocal()); // merge in the loaded/custom modules
        msgDebug("\nAfter merge with local: ".print_r($modules, true));
        ksort($modules);
        $devStatus= in_array(getUserCache('profile','level', false, ''), ['developer']) ? true : false;
        $data     = [
            'divs' => [
                'heading' => ['type'=>'html', 'order'=>30, 'html'=>"<h1>".lang('settings')."</h1>\n"],
                'adminSet'=> ['type'=>'tabs', 'order'=>60, 'key'=>'tabSettings']],
            'tabs'=>['tabSettings'=>['attr'=>['tabPosition'=>'left','focus'=>$focus]]]]; // removed 'fit'=>true, for WordPress
        $order = 1;
        foreach ($modules as $cat => $catList) {
            $modlist = sortOrder($catList, 'title');
            $tplMod  = $this->getDivStructure(); // get the base module div structure
            $idx = 0;
            foreach ($modlist as $module_id => $settings) {
                $modActive = 0;
                msgDebug("\nBuilding settings row with cat = $cat and module: $module_id and settings: ".print_r($settings, true));
                if ('bizuno'==$cat || (!empty($settings['paid']) && !empty($settings['active']))) { // show the uninstall and settings icons
                    $required = getModuleCache($module_id, 'properties', 'required', false, false) ? true : false;
                    $modActive = 1;
                    $modStatus = '';
                    $hasDashboards = getModuleCache($module_id, 'dashboards') ? 1 : 0;
                    $props = getModuleCache($module_id, 'properties');
                    msgDebug("\nproperties = ".print_r($props, true));
                    if ('bizuno'<>$cat && $settings['paid'] && BIZUNO_HOST<>'phreesoft' && version_compare($settings['version'], $props['version']) > 0) {
                        $modStatus .= html5("download_$module_id", ['icon'=>'download','events'=>['onClick'=>"jsonAction('bizuno/settings/loadExtension', 0, '$module_id');"]]);
                    }
                    if (!empty($settings['settings']) || $hasDashboards || !empty($props['dirMethods'])) { // check to see if the module has admin settings
                        $modStatus .= html5("prop_$module_id", ['icon'=>'settings','events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=$module_id/admin/adminHome'"]]);
                    }
                    if ($security == 5 && !$required) {
                        $modStatus .= html5("remove_$module_id", ['attr'=>['type'=>'button','value'=>lang('remove')],
                           'events'=>['onClick'=>"if (confirm('".$this->lang['msg_module_delete_confirm']."')) jsonAction('bizuno/settings/moduleRemove', '$module_id');"]]);
                    }
                } elseif (!empty($settings['paid']) && !empty($settings['loaded']) && empty($settings['devStatus'])) { // if subscribed and loaded, show install button
                    $modStatus = html5("install_$module_id", ['attr'=>['type'=>'button','value'=>lang('install')],
                        'events'=>['onClick'=>"jsonAction('bizuno/settings/moduleInstall', '$module_id', '{$settings['path']}');"]]);
                } elseif (!empty($settings['paid']) &&  empty($settings['loaded']) && empty($settings['devStatus'])) { // if subscribed and not loaded, show download button
                    $modStatus = html5("download_$module_id", ['icon'=>'download','events'=>['onClick'=>"jsonAction('bizuno/settings/loadExtension', 0, '$module_id');"]]);
                } elseif (!empty($settings['devStatus']) && $devStatus) { // developer, show install button
                    $modStatus = html5("install_$module_id", ['attr'=>['type'=>'button','value'=>lang('install')],
                        'events'=>['onClick'=>"jsonAction('bizuno/settings/moduleInstall', '$module_id', '{$settings['path']}');"]]);
                } else { // show purchase button
                    $modStatus = '';
                    if ($security == 5 && in_array($module_id, $myMods)) { // if expired and installed, the remove button will be shown, i.e. no longer used or wanted
                        $modStatus .= html5("remove_$module_id", ['attr'=>['type'=>'button','value'=>lang('remove')],
                           'events'=>['onClick'=>"if (confirm('".$this->lang['msg_module_delete_confirm']."')) jsonAction('bizuno/settings/moduleRemove', '$module_id');"]]);
                    }
                    $price = isset($settings['price']) && $settings['price'] ? viewFormat($settings['price'], 'currency').' '.lang('buy') : lang('See Website');
                    if (empty($settings['url'])) { $settings['url'] = $this->phreesoftURL; }
                    $modStatus .= html5("buy_$module_id", ['attr'=>['type'=>'button','value'=>$price],'events'=>['onClick'=>"window.open('{$settings['url']}');"]]);
                }
                $tplMod['tbody']['tr'][$idx] = ['attr'=>['type'=>'tr'],'td'=>[
                    ['attr'=>['type'=>'td','value'=>$settings['title']],'styles'=>['background-color'=>$modActive?'lightgreen':'']],
                    ['attr'=>['type'=>'td','value'=>$settings['description']]],
                    ['styles'=>['text-align'=>'right'],'attr'=>['type'=>'td','value'=>$modStatus,'nowrap'=>'nowrap']]]];
                $idx++;
                $tplMod['tbody']['tr'][$idx] = ['attr'=>['type'=>'tr'],'td'=>[['attr'=>['type'=>'td','value'=>'<hr />','colspan'=>4]]]]; //seperator
                $idx++;
            }
            $data['tables']['tbl_'.$cat] = $tplMod;
            $data['tabs']['tabSettings']['divs']['div_'.$cat] = ['order'=>$order,'label'=>lang($cat),'type'=>'table','key'=>'tbl_'.$cat];
            $order++;
        }
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * merge in the loaded/custom modules
     */
    private function getLocal()
    {
        $output = [];
        bizAutoLoad(BIZUNO_ROOT."portal/guest.php", 'guest');
        $guest   = new guest();
        $modList = $guest->getModuleList();
        foreach ($modList as $module => $path) {
            $fqcn = "\\bizuno\\{$module}Admin";
            bizAutoLoad("{$path}admin.php", $fqcn);
            $admin= new $fqcn();
            $cat  = !empty($admin->structure['category']) ? $admin->structure['category'] : 'misc';
            $output[$cat][$module] = [
                'title'      => $admin->lang['title'],
                'description'=> $admin->lang['description'],
                'path'       => $path,
//              'version'    => isset($admin->structure['version']) ? $admin->structure['version'] : '', // if uncommented, replaces newest version with installed version, breaks upgrade
                'loaded'     => true,
                'devStatus'  => !empty($admin->devStatus) ? $admin->devStatus : false,
                'active'     => getModuleCache($module, 'properties', 'status'),
                'settings'   => !empty($admin->settings) && is_array($admin->settings) ? true : false];
            if (strpos($path, BIZUNO_CUSTOM."$module/")===0) {
                $output[$cat][$module] = array_merge($output[$cat][$module], ['paid'=>true,'version'=>-1]);
            }
            if (!empty($admin->devStatus)){ $output[$cat][$module] = array_merge($output[$cat][$module], ['paid'=>true]); }
            if ($cat=='bizuno')           { $output[$cat][$module] = array_merge($output[$cat][$module], ['paid'=>true,'version'=>MODULE_BIZUNO_VERSION]); }
        }
        return $output;
    }

    public function loadExtension(&$layout=[])
    {
        global $io;
        $moduleID= clean('data', 'filename', 'get');
        $bizID   = getUserCache('profile', 'biz_id');
        $bizUser = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_user');
        $bizPass = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_pass');
        $data    = http_build_query(['bizID'=>$bizID, 'UserID'=>$bizUser, 'UserPW'=>$bizPass]);
        $context = stream_context_create(['http'=>['method'=>'POST', 'content'=>$data,
            'header'=>"Content-type: application/x-www-form-urlencoded\r\n"."Content-Length: ".strlen($data)."\r\n"]]);
        try {
            $source = "https://www.phreesoft.com/wp-admin/admin-ajax.php?action=bizuno_ajax&p=myPortal/admin/loadExtension&mID=$moduleID";
            $dest   = BIZUNO_DATA."temp/$moduleID.zip";
            copy ($source, $dest, $context);
            if (file_exists(BIZUNO_DATA."temp/$moduleID.zip")) {
                $io->folderDelete(BIZUNO_EXT.$moduleID); // remove all current contents
                $io->zipUnzip(BIZUNO_DATA."temp/$moduleID.zip", BIZUNO_EXT.$moduleID, false);
                if (file_exists(BIZUNO_EXT."$moduleID/bizunoUPG.php")) {
                    require(BIZUNO_EXT."$moduleID/bizunoUPG.php"); // handle any local db or file changes
                    unlink(BIZUNO_EXT."$moduleID/bizunoUPG.php");
                }
                dbClearCache();
                $layout = array_replace_recursive($layout, ['content'=>['action'=>'href','link'=>BIZUNO_HOME."&p=bizuno/settings/manager"]]);
            } else { msgAdd('There was a problem retrieving your extension, please contact PhreeSoft for assistance.'); }
        } catch (Exception $e) { msgAdd("We had an exception: ". print_r($e, true)); }
        @unlink(BIZUNO_DATA."temp/$moduleID.zip");
    }

    /**
     * get the base module div structure
     */
    private function getDivStructure()
    {
        return ['classes'=>[],'styles'=>['border-collapse'=>'collapse','width'=>'100%'],'attr'=>['type'=>'table'],
            'thead'=>['classes'=>['panel-header'],'styles'=>[],'attr'=>['type'=>'thead'],'tr'=>[['attr'=>['type'=>'tr'],
                'td'=>[
                    ['classes'=>[],'styles'=>[],'attr'=>['type'=>'th','value'=>lang('module')]],
                    ['classes'=>[],'styles'=>[],'attr'=>['type'=>'th','value'=>lang('description')]],
                    ['classes'=>[],'styles'=>[],'attr'=>['type'=>'th','value'=>'&nbsp;']]]]]],
            'tbody'=>['attr'=>['type'=>'tbody']]];
    }

    /**
     * Handles the installation of a module
     * @global array $msgStack - working messages to be returned to user
     * @param array $layout - structure coming in
     * @param string $module - name of module to install
     * @return modified layout
     */
    public function moduleInstall(&$layout=[], $module=false, $path='')
    {
        global $msgStack, $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        if (!$module) {
            $module = clean('rID', 'text', 'get');
            $path   = clean('data','text', 'get');
        }
        if (!$module || !$path) { return msgAdd("Error installing module: unknown. No name/path passed!"); }
        $installed = dbGetValue(BIZUNO_DB_PREFIX.'configuration', 'config_value',  "config_key='$module'");
        if ($installed) {
            $settings = json_decode($installed, true);
            if (!$settings['properties']['status']) {
                $settings['properties']['status'] = 1;
                $bizunoMod[$module] = $settings;
                dbWrite(BIZUNO_DB_PREFIX.'configuration', ['config_value'=>json_encode($settings)], 'update', "config_key='$module'");
                msgAdd("Extension $module has been reactivated!");
            } else { return msgAdd(sprintf($this->lang['err_install_module_exists'], $module), 'caution'); }
        } else {
            $path = rtrim($path, '/') . '/';
            msgDebug("\n\nInstalling module: $module at path: $path");
            if (!file_exists("{$path}admin.php")) { return msgAdd(sprintf("There was an error finding file %s", "{$path}admin.php")); }
            $fqcn = "\\bizuno\\{$module}Admin";
            bizAutoLoad("{$path}admin.php", $fqcn);
            $adm = new $fqcn();
            $bizunoMod[$module]['settings']            = isset($adm->settings) ? $adm->settings : [];
            $bizunoMod[$module]['properties']          = $adm->structure;
            $bizunoMod[$module]['properties']['id']    = $module;
            $bizunoMod[$module]['properties']['title'] = $adm->lang['title'];
            $bizunoMod[$module]['properties']['status']= getModuleCache($module, 'properties', 'version');
            $bizunoMod[$module]['properties']['path']  = $path;
            $this->adminInstDirs($adm);
            if (isset($adm->tables)) { $this->adminInstTables($adm->tables); }
            $this->adminAddRptDirs($adm);
            $this->adminAddRpts($module=='bizuno' ? BIZUNO_LIB : $path);
            if (method_exists($adm, 'install')) { $adm->install(); }
            if (isset($adm->notes)) { $this->notes = array_merge($this->notes, $adm->notes); }
            // create the initial configuration table record
            dbWrite(BIZUNO_DB_PREFIX.'configuration', ['config_key'=>$module, 'config_value'=>json_encode($bizunoMod[$module])]);
            if (!empty($adm->structure['menuBar']['child'])) { $this->setSecurity($adm->structure['menuBar']['child']); }
            msgLog  ("Installed module: $module");
            msgDebug("\nInstalled module: $module");
            if (isset($msgStack->error['error']) && sizeof($msgStack->error['error']) > 0) { return; }
        }
        dbClearCache(getUserCache('profile','email'));
        $cat    = getModuleCache($module, 'properties', 'category', false, 'bizuno');
        $layout = array_replace_recursive($layout, ['content'=>['rID'=>$module,'action'=>'href','link'=>BIZUNO_HOME."&p=bizuno/settings/manager&cat=$cat"]]);
    }

    private function setSecurity($menu)
    {
        $roleID  = getUserCache('profile', 'role_id');
        $dbData  = dbGetRow(BIZUNO_DB_PREFIX."roles", "id=$roleID");
        $settings= !empty($dbData['settings']) ? json_decode($dbData['settings'], true) : [];
        foreach ($menu as $catChild) {
            $subMenus = array_keys($catChild['child']);
            foreach ($subMenus as $item) { $settings['security'][$item] = 4; }
        }
        dbWrite(BIZUNO_DB_PREFIX."roles", ['settings'=>json_encode($settings)], 'update', "id=$roleID");
    }

    /**
     * Removes a module from Bizuno
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function moduleRemove(&$layout=[])
    {
        global $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $module = clean('rID', 'text', 'get');
        msgDebug("\n removing module: $module");
        if (!$module) { return; }
        $path = getModuleCache($module, 'properties', 'path');
        $cat  = getModuleCache($module, 'properties', 'category', false, 'bizuno');
        if (file_exists("$path/admin.php")) {
            $fqcn = "\\bizuno\\{$module}Admin";
            bizAutoLoad("$path/admin.php", $fqcn);
            $mod_admin = new $fqcn();
            $this->adminDelDirs($mod_admin);
            if (isset($mod_admin->tables)) { $this->adminDelTables($mod_admin->tables); }
            if (method_exists($mod_admin, 'remove')) { if (!$mod_admin->remove()) {
                return msgAdd("There was an error removing module: $module");
            } }
        }
        if (is_dir("$path/$module/dashboards/")) {
            $dBoards = scandir("$path/$module/dashboards/");
            foreach ($dBoards as $dBoard) { if (!in_array($dBoard, ['.', '..'])) {
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."users_profiles WHERE dashboard_id='$dBoard'");
            } }
        }
        msgLog("Removed module: $module");
        msgDebug("\n removed module: $module");
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."configuration WHERE config_key='$module'");
        dbClearCache(); // force reload of all users cache with next page access, menus and permissions, etc.
        $data = ['content'=>  ['rID'=>$module, 'action'=>'href', 'link'=>BIZUNO_HOME."&p=bizuno/settings/manager&cat=$cat"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Installs a method associated with a module
     * @param array $layout - structure coming in
     * @param array $attrs - details of the module to add method
     * @param boolean $verbose - [default true] true to send user message, false to just install method
     * @return type
     */
    public function methodInstall(&$layout=[], $attrs=[], $verbose=true)
    {
        if (!$security=validateSecurity('bizuno', 'admin', 3)) { return; }
        $module = isset($attrs['module']) ? $attrs['module'] : clean('module','text', 'get');
        $subDir = isset($attrs['path'])   ? $attrs['path']   : clean('path',  'text', 'get');
        $method = isset($attrs['method']) ? $attrs['method'] : clean('method','text', 'get');
        if (!$module || !$subDir || !$method) { return msgAdd("Bad data installing method!"); }
        msgDebug("\nInstalling method $method with methodDir = $subDir");
        $path = getModuleCache($module, 'properties', 'path')."$subDir/$method/$method.php";
        if (file_exists(BIZUNO_CUSTOM."$module/$subDir/$method/$method.php")) { $path = BIZUNO_CUSTOM."$module/$subDir/$method/$method.php"; }
        $fqcn = "\\bizuno\\$method";
        bizAutoLoad($path, $fqcn);
        $methSet = getModuleCache($module,$subDir,$method,'settings');
        $clsMeth = new $fqcn($methSet);
        if (method_exists($clsMeth, 'install')) { $clsMeth->install($layout); }
        $properties = getModuleCache($module, $subDir, $method);
        $properties['status'] = 1;
        setModuleCache($module, $subDir, $method, $properties);
        dbClearCache();
        $data = $verbose ? ['content'=>['action'=>'eval','actionData'=>"location.reload();"]] : [];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves user settings for a specific method
     * @param $layout - structure coming in
     * @return modified structure
     */
    public function methodSettingsSave(&$layout=[])
    {
        if (!$security=validateSecurity('bizuno', 'admin', 3)) { return; }
        $module = clean('module','text', 'get');
        $subDir = clean('type',  'text', 'get');
        $method = clean('method','text', 'get');
        if (!$module || !$subDir || !$method) { return msgAdd("Not all the information was provided!"); }
        $properties = getModuleCache($module, $subDir, $method);
        $fqcn = "\\bizuno\\$method";
        bizAutoLoad("{$properties['path']}$method.php", $fqcn);
        $methSet = getModuleCache($module,$subDir,$method,'settings');
        $objMethod = new $fqcn($methSet);
        msgDebug('received raw data = '.print_r(file_get_contents("php://input"), true));
        $structure = method_exists($objMethod, 'settingsStructure') ? $objMethod->settingsStructure() : [];
        foreach ($structure as $key => $values) {
            if (isset($values['attr']['multiple'])) {
                $settings[$key] = implode(':', clean($method.'_'.$key, 'array', 'post'));
            } else {
                $processing = isset($values['attr']['type']) ? $values['attr']['type'] : 'text';
                $settings[$key] = clean($method.'_'.$key, $processing, 'post');
            }
        }
        msgDebug("\nSettings is now: ".print_r($settings, true));
        $properties['settings'] = $settings;
        setModuleCache($module, $subDir, $method, $properties);
        dbClearCache();
        if (method_exists($objMethod, 'settingSave')) { $objMethod->settingSave(); }
        msgAdd(lang('msg_settings_saved'), 'success');
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"jq('#divMethod_$method').hide('slow');"]]);
    }

    /**
     * Removes a method from the db and session cache
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function methodRemove(&$layout=[]) {
        if (!$security=validateSecurity('bizuno', 'admin', 4)) { return; }
        $module = clean('module', 'text', 'get');
        $subDir = clean('type',   'text', 'get');
        $method = clean('method', 'text', 'get');
        if (!$module || !$subDir) { return msgAdd("Bad method data provided!"); }
        $properties = getModuleCache($module, $subDir, $method);
        if ($properties) {
            $fqcn = "\\bizuno\\$method";
            bizAutoLoad("{$properties['path']}$method.php", $fqcn);
            $methSet = getModuleCache($module,$subDir,$method,'settings');
            $clsMeth = new $fqcn($methSet);
            if (method_exists($clsMeth, 'remove')) { $clsMeth->remove(); }
            $properties['status'] = 0;
            $properties['settings'] = [];
            setModuleCache($module, $subDir, $method, $properties);
            dbClearCache();
        }
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"location.reload();"]]);
    }

    /**
     * Installs the file structure for a module, if any
     * @param array $dirlist - list for folders to create
     * @param string $path - folder path to start
     * @return boolean, false on error, true on success
     */
    function adminInstDirs($adm)
    {
        if (!isset($adm->dirlist)) { return; }
        if (is_array($adm->dirlist)) {
            foreach ($adm->dirlist as $dir) {
                if (!file_exists(BIZUNO_DATA . $dir)) {
                    if ( !@mkdir(BIZUNO_DATA . $dir, 0755, true)) { msgAdd(sprintf(lang('err_io_dir_create'), $dir)); }
                }
            }
        }
    }

    /**
     * Removes folders when a module is removed
     * @param array $dirlist - folder list to remove
     * @param string $path - root path where folders can be found
     * @return boolean true
     */
    function adminDelDirs($mod_admin)
    {
        if (!isset($mod_admin->dirlist)) { return; }
        if (is_array($mod_admin->dirlist)) {
            $temp = array_reverse($mod_admin->dirlist);
            foreach($temp as $dir) {
                if (!@rmdir(BIZUNO_DATA . $dir)) { msgAdd(sprintf(lang('err_io_dir_remove'), BIZUNO_DATA . $dir)); }
            }
        }
        return true;
    }

    /**
     * Installs db tables when a module is installed
     * @param array $tables - list of tables to create
     * @return boolean true on success, false on error
     */
    public function adminInstTables($tables=[])
    {
        foreach ($tables as $table => $props) {
            $fields = [];
            foreach ($props['fields'] as $field => $values) {
                $temp = "`$field` ".$values['format']." ".$values['attr'];
                if (isset($values['comment'])) { $temp .= " COMMENT '".$values['comment']."'"; }
                $fields[] = $temp;
            }
            msgDebug("\n    Creating table: $table");
            $sql = "CREATE TABLE IF NOT EXISTS `".BIZUNO_DB_PREFIX."$table` (".implode(', ', $fields).", ".$props['keys']." ) ".$props['attr'];
            dbGetResult($sql);
        }
    }

    /**
     * Removes tables from the db
     * @param array $tables - list of tables to drop
     */
    function adminDelTables($tables=[])
    {
        foreach ($tables as $table =>$values) {
            dbGetResult("DROP TABLE IF EXISTS `".BIZUNO_DB_PREFIX."$table`");
        }
    }

    /**
     * Adds new folders to the PhreeForm tree, used when installing a new module
     * @param array $adm -
     * @return boolean true on success, false on error
     */
    private function adminAddRptDirs($adm)
    {
        global $bizunoMod;
        $date = date('Y-m-d');
        if (isset($adm->reportStructure)) { foreach ($adm->reportStructure as $heading => $settings) {
            $parent_id = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'id', "title='".$settings['title']."' and mime_type='dir'");
            if (!$parent_id) { // make the heading
                $parent_id = dbWrite(BIZUNO_DB_PREFIX."phreeform", ['group_id'=>$heading, 'mime_type'=>'dir', 'title'=>$settings['title'], 'create_date'=>$date, 'last_update'=>$date]);
            }
            if (is_array($settings['folders'])) { foreach ($settings['folders'] as $gID => $values) {
                if (!$result = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'id', "group_id='$gID' and mime_type='{$values['type']}'")) {
                    dbWrite(BIZUNO_DB_PREFIX."phreeform", ['parent_id'=>$parent_id, 'group_id'=>$gID, 'mime_type'=>$values['type'], 'title'=>$values['title'], 'create_date'=>$date, 'last_update'=>$date]);
                }
            } }
        } }
        if (isset($adm->phreeformProcessing)) {
            if (!isset($bizunoMod['phreeform']['processing'])) { $bizunoMod['phreeform']['processing'] = []; }
            $temp = array_merge_recursive($bizunoMod['phreeform']['processing'], $adm->phreeformProcessing);
            $bizunoMod['phreeform']['processing'] = sortOrder($temp, 'group'); // sort phreeform processing
        }
        if (isset($adm->phreeformFormatting)) {
            if (!isset($bizunoMod['phreeform']['formatting'])) { $bizunoMod['phreeform']['formatting'] = []; }
            $temp = array_merge_recursive($bizunoMod['phreeform']['formatting'], $adm->phreeformFormatting);
            $bizunoMod['phreeform']['formatting'] = sortOrder($temp, 'group'); // sort phreeform formatting
        }
        if (isset($adm->phreeformSeparators)) {
            if (!isset($bizunoMod['phreeform']['separators'])) { $bizunoMod['phreeform']['separators'] = []; }
            $temp = array_merge_recursive($bizunoMod['phreeform']['separators'], $adm->phreeformSeparators);
            $bizunoMod['phreeform']['separators'] = sortOrder($temp, 'group'); // sort phreeform separators
        }
    }

    /**
     * Adds reports to PhreeForm, typically during a module install
     * @param string $module - module name to look for reports
     * @param boolean $core - true if a core Bizuno module, false otherwise
     * @return boolean
     */
    function adminAddRpts($path='')
    {
        bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/functions.php", 'phreeformImport', 'function');
        $error = false;
        msgDebug("\nAdding reports to path = $path");
        if ($path <> BIZUNO_LIB) { $path = "$path/"; }
        if (file_exists($path."locale/".getUserCache('profile', 'language', false, 'en_US')."/reports/")) {
            $read_path = $path."locale/".getUserCache('profile', 'language', false, 'en_US')."/reports/";
        } elseif (file_exists($path."locale/en_US/reports/")) {
            $read_path = $path."locale/en_US/reports/";
        } else { msgDebug(" ... returning with no reports found!"); return true; } // nothing to import
        $files = scandir($read_path);
        foreach ($files as $file) {
            if (strtolower(substr($file, -4)) == '.xml') {
                msgDebug("\nImporting report name = $file at path $read_path");
                if (!phreeformImport('', $file, $read_path, false)) { $error = true; }
            }
        }
        return $error ? false : true;
    }

    /**
     * Fill security values in the menu structure
     * @param integer $role_id - role id of the user
     * @param integer $level - level to set security value
     * @return boolean true
     */
    function adminFillSecurity($role_id=0, $level=0)
    {
        global $bizunoMod;
        $security = [];
        foreach ($bizunoMod as $settings) {
            if (!isset($settings['properties']['menuBar']['child'])) { continue; }
            foreach ($settings['properties']['menuBar']['child'] as $key1 => $menu1) {
                $security[$key1] = $level;
                if (!isset($menu1['child'])) { continue; }
                foreach ($menu1['child'] as $key2 => $menu2) {
                    $security[$key2] = $level;
                    if (!isset($menu2['child'])) { continue; }
                    foreach ($menu2['child'] as $key3 => $menu3) { $security[$key3] = $level; }
                }
            }
        }
        foreach ($bizunoMod as $settings) {
            if (!isset($settings['properties']['quickBar']['child'])) { continue; }
            foreach ($settings['properties']['quickBar']['child'] as $key => $menu) {
                $security[$key] = $level;
                if (!isset($menu['child'])) { continue; }
                foreach ($menu['child'] as $skey => $smenu) { $security[$skey] = $level; }
            }
        }
        $result = dbGetRow(BIZUNO_DB_PREFIX."roles", "id='$role_id'");
        if ($result) {
            $settings = json_decode($result['settings'], true);
            $settings['security'] = $security;
            setUserCache('security', false, $security);
            dbWrite(BIZUNO_DB_PREFIX."roles", ['settings'=>json_encode($settings)], 'update', "id='$role_id'");
        }
        return true;
    }

    /**
     * Updates the current_status db table with a modified values set by user in Settings
     */
    public function statusSave()
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX."current_status");
        $values = requestData($structure);
        $result = dbWrite(BIZUNO_DB_PREFIX."current_status", $values, 'update');
        msgAdd(lang('msg_settings_saved'), 'success');
    }
}