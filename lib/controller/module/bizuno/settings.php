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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-08-26
 * @filesource /lib/controller/module/bizuno/settings.php
 */

namespace bizuno;

class bizunoSettings
{
    public  $notes       = [];
    public  $moduleID    = 'bizuno';
    private $phreesoftURL= 'https://www.phreesoft.com';
    private $core        = ['bizuno','contacts','inventory','payment','phreebooks','phreeform']; // core modules

    function __construct()
    {
        $this->lang     = getLang($this->moduleID);
        $this->helpIndex= "module-settings";
    }

    /**
     * Main Settings page, builds a list of all available modules and puts into groups
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 2)) { return; }
        $data = ['title'=>lang('settings'),
            'divs'   => [
                'heading'=> ['order'=>10,'type'=>'html','html'=>"<h1>".lang('settings')."</h1>"],
                'manager'=> ['order'=>50,'type'=>'tabs','key' =>'tabSettings']],
            'tabs'   => ['tabSettings'=> ['divs'=>[
                'main'=> ['order'=>10,'label'=>lang('installed'),'type'=>'divs','classes'=>['areaView'],'divs'=>[
                    'head' => ['order'=>10,'type'=>'html','html'=>"<p>&nbsp;</p>"]]],
                'ext' => ['order'=>20,'label'=>lang('extensions'),'type'=>'html','html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=bizuno/settings/getExtensions'"]]]]]];
        $order  = 30;
        $modList= $this->getLocal();
        $store  = getModuleCache('bizuno', 'shop');
        msgDebug("\nStore settings = ".print_r($store, true));
        foreach ($modList as $mID => $settings) {
            msgDebug("\nSettings for module $mID = ".print_r(getModuleCache($mID, 'properties'), true));
            if (in_array(BIZUNO_HOST, ['phreesoft']) && !dbGetValue(BIZUNO_DB_PREFIX.'configuration', 'config_key', "config_key='$mID'")) { continue; } // no config record
            $data['tabs']['tabSettings']['divs']['main']['divs'][$mID] = ['order'=>$order,'type'=>'panel','classes'=>['block99'],'key'=>$mID];
            $data['panels'][$mID] = ['label'=>$settings['title'],'type'=>'html','html'=>$this->buildModProps($mID, $settings, $store)];
            $order++;
        }
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * Builds the panel for installed modules, either active or inactive
     * @param type $mID
     * @param type $settings
     * @return string
     */
    private function buildModProps($mID, $settings, $store) {
        $security = validateSecurity('bizuno', 'admin', 2);
        msgDebug("\nsecurity = $security");
        if  (empty($settings['version'])) { $settings['version'] = MODULE_BIZUNO_VERSION; }
        if (!empty($settings['devStatus']) && $settings['devStatus']=='dev') { $store[$mID] = ['paid'=>true,'url'=>'','version'=>MODULE_BIZUNO_VERSION]; }
        if ( empty($store[$mID])) { $store[$mID] = ['version'=>MODULE_BIZUNO_VERSION]; }
        $version = in_array($mID, $this->core) ? MODULE_BIZUNO_VERSION : $store[$mID]['version'];
        $hasDash= getModuleCache($mID, 'dashboards') ? 1 : 0;
        $html  = '<div style="float:left">'."\n";
        if (!empty($settings['logo'])){ $html .= html5('', ['styles'=>['cursor'=>'pointer','max-height'=>'50px'],'attr'=>['type'=>'img','src'=>$settings['logo']]]); }
        else                          { $html .= html5('', ['styles'=>['cursor'=>'pointer','max-height'=>'50px'],'attr'=>['type'=>'img','src'=>BIZUNO_URL.'images/phreesoft.png']]); }
        $html .= '</div><div style="float:right">'."\n";
        if (version_compare($settings['version'], $version)<0 && !in_array(BIZUNO_HOST, ['phreesoft'])
                && (($mID=='bizuno' && in_array(BIZUNO_HOST, ['phreebooks'])) || (!empty($store[$mID]['paid']) && !in_array($mID, $this->core)))) { // if paid and needs upgrade
            $html .= $this->btnDownload($mID);
        }
        if ( empty($store[$mID]['paid']) && !in_array($mID, $this->core) && !empty($store[$mID]['sku'])) { // if not paid but had been installed and has a store SKU, show purchase button
            $html .= $this->btnPurchase($mID, $store[$mID]['priceUSD'], $store[$mID]['url']);
        }
        if (!empty($settings['hasAdmin']) || !empty($settings['settings']) || $hasDash || !empty($settings['dirMethods'])) { // check to see if the module has admin settings
            $html .= html5("prop_$mID", ['icon'=>'settings','events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=$mID/admin/adminHome'"]]);
        }
        $html .= '</div>'."\n";
        $html .= "<div><p>{$settings['description']}</p>";
        $html .= "<p>Installed Version: ".$settings['version']."; Current Version: $version</p>";
        if ( empty($settings['status']) && !in_array($mID, $this->core) && $store[$mID]['paid']) { $html .= $this->btnInstall($mID, $settings['path']); } // activate
        if (!empty($settings['status']) && !in_array($mID, $this->core)) { $html .= $this->btnDeactivate($mID); }
        if ( empty($settings['status']) && !in_array($mID, $this->core) && $security>3) { $html .= $this->btnDelete($mID); }
        $infoLink = in_array($mID, $this->core) && !empty($store[$mID]['url']) ? $store[$mID]['url'] : $this->phreesoftURL;
        $html .= '<a href="'.$infoLink.'" target="_blank">More Info</a>';
        $html .= '</div>';
        return $html;
    }

    public function getExtensions(&$layout=[])
    {
        global $io;
        if (!$security = validateSecurity('bizuno', 'admin', 2)) { return; }
        $order = 30;
        $data  = ['type'=>'divHTML','divs'=>['heading'=>['order'=>10,'type'=>'html','html'=>"<h1>".'Here is where you can purchase extensions and download them if you are not using the PhreeSoft cloud'."</h1>"]]];
        $myMods= $this->getLocal();
        msgDebug("\nmyMods = ".print_r($myMods, true));
        $myAcct= $io->apiPhreeSoft('getMyExtensions');
        $store = sortOrder($this->reSortExtensions($myAcct), 'title');
        setModuleCache('bizuno', 'shop', false, $store);
        msgDebug("\nstore = ".print_r($store, true));
        foreach ($store as $mID => $settings) {
            if ((!in_array(BIZUNO_HOST, ['phreesoft']) && array_key_exists($mID, $myMods))
                    || (in_array(BIZUNO_HOST, ['phreesoft']) && !empty(getModuleCache($mID, 'properties', 'status')))) { continue; }
            $html = '<div style="float:left">'."\n";
            if (!empty($settings['logo'])){ $html .= html5('', ['styles' =>['cursor'=>'pointer','max-height'=>'50px'],'attr'=>['type'=>'img','src'=>$settings['logo']]]); }
            else                          { $html .= html5('', ['styles' =>['cursor'=>'pointer','max-height'=>'50px'],'attr'=>['type'=>'img','src'=>BIZUNO_URL.'images/phreesoft.png']]); }
            $html .= '</div><div style="float:right">'."\n";
            if       ( empty($settings['paid']) && !in_array($mID, $this->core)) { // if not paid but had been installed, show purchase button
                $html .= $this->btnPurchase($mID, $settings['priceUSD'], $settings['url']);
            } elseif (!empty($settings['paid']) && !in_array($mID, $this->core)) { // if not paid but had been installed, show download icon
                $html .= in_array(BIZUNO_HOST, ['phreesoft']) ? $this->btnInstall($mID, $myMods[$mID]['path'], 'button') : $this->btnDownload($mID);
            }
            $html .= '</div>';
            $html .= "<div><p>{$settings['description']}</p>";
            $html .= "<p>Current Version: ".$settings['version']."</p>";
            $infoLink = !in_array($mID, $this->core) && !empty($settings['url']) ? $settings['url'] : $this->phreesoftURL;
            $html .= '<a href="'.$infoLink.'" target="_blank">More Info</a>';
            $html .= '</div>';
            $data['divs'][$mID]  = ['order'=>$order,'type'=>'panel','classes'=>['block99'],'key'=>$mID];
            $data['panels'][$mID]= ['label'=>$settings['title'],'type'=>'html','html'=>$html];
            $order++;
        }
        $layout = array_replace_recursive($layout, $data);
    }

    public function loadExtension(&$layout=[])
    {
        global $io;
        $moduleID= clean('data', 'filename', 'get');
        if ($moduleID == 'bizuno') { // core Bizuno upgrade
            bizAutoLoad(BIZUNO_LIB."controller/module/bizuno/backup.php", 'bizunoBackup');
            $bizUpgrade = new bizunoBackup();
            $bizUpgrade->bizunoUpgradeGo($layout);
            return;
        }
        $bizID   = getUserCache('profile', 'biz_id');
        $bizUser = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_user');
        $bizKey  = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_key');
//      $bizPass = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_pass');
        $bizPost = ['bizID'=>$bizID, 'UserID'=>$bizUser, 'UserKey'=>$bizKey]; // , 'UserPW'=>$bizPass
        try {
            $source = "https://www.phreesoft.com/wp-admin/admin-ajax.php?action=bizuno_ajax&p=myPortal/admin/loadExtension&mID=$moduleID";
            $dest   = "temp/$moduleID.zip";
            msgDebug("\nSource = $source");
            msgDebug("\nDest = BIZUNO_DATA/$dest");
            if (ini_get('allow_url_fopen')) {
                $data   = http_build_query($bizPost);
                $context= stream_context_create(['http'=>['method'=>'POST', 'content'=>$data,
                    'header'=>"Content-type: application/x-www-form-urlencoded\r\n"."Content-Length: ".strlen($data)."\r\n"]]);
                msgDebug("\nReady to fetch zip, context = ".print_r($context, true));
                msgDebug("\nTrying copy because allow_url_fopen is enabled.");
                copy ($source, BIZUNO_DATA.$dest, $context);
            }
            if (!file_exists(BIZUNO_DATA.$dest)) {
                msgDebug("\nTrying cURL because allow_url_fopen is disabled or copy failed.");
                $result = $io->cURLGet($source, $bizPost, 'post'); //, $opts);
                if (!empty($result)) { $io->fileWrite($result, $dest, false, false, true); }
            }
            // @todo - If there was an error, e.g. not authoroized, then the file will contain the message in text and not the zipped extension, doesn't fail very well
            if (file_exists(BIZUNO_DATA.$dest)) {
                $io->folderDelete(BIZUNO_EXT.$moduleID); // remove all current contents
                $io->zipUnzip(BIZUNO_DATA.$dest, BIZUNO_EXT.$moduleID, false);
                if (file_exists(BIZUNO_EXT."$moduleID/bizunoUPG.php")) {
                    require(BIZUNO_EXT."$moduleID/bizunoUPG.php"); // handle any local db or file changes
                    unlink(BIZUNO_EXT."$moduleID/bizunoUPG.php");
                }
                dbClearCache();
                $layout = array_replace_recursive($layout, ['content'=>['action'=>'href','link'=>BIZUNO_HOME."&p=bizuno/settings/manager"]]);
            } else { msgAdd('There was a problem retrieving your extension, please contact PhreeSoft for assistance.', 'trap'); }
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

    public function moduleDeactivate(&$layout=[])
    {
        global $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $mID     = clean('rID', 'cmd', 'get');
        $props   = dbGetValue(BIZUNO_DB_PREFIX.'configuration', 'config_value',  "config_key='$mID'");
        if (empty($props)) { return msgAdd("Could not find module to deactivate!"); }
        $settings= json_decode($props, true);
        $settings['properties']['status'] = 0;
        $bizunoMod[$mID] = $settings;
        dbWrite(BIZUNO_DB_PREFIX.'configuration', ['config_value'=>json_encode($settings)], 'update', "config_key='$mID'");
        $layout = array_replace_recursive($layout, ['content'=>['rID'=>$mID,'action'=>'href','link'=>BIZUNO_HOME."&p=bizuno/settings/manager"]]);
    }

    /**
     * Handles the installation of a module
     * @global array $msgStack - working messages to be returned to user
     * @param array $layout - structure coming in
     * @param string $module - name of module to install
     * @param string $path -
     * @return modified $layout
     */
    public function moduleInstall(&$layout=[], $module=false, $path='')
    {
        global $msgStack, $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        if (!$module) {
            $module = clean('rID', 'cmd', 'get');
            $path   = clean('data','filename', 'get');
        }
        if (!$module || !$path) { return msgAdd("Error installing module: unknown. No name/path passed!"); }
        $installed = dbGetValue(BIZUNO_DB_PREFIX.'configuration', 'config_value',  "config_key='$module'");
        if ($installed) {
            $settings = json_decode($installed, true);
            if (!$settings['properties']['status']) {
                $settings['properties']['status'] = 1;
                $bizunoMod[$module] = $settings;
                dbWrite(BIZUNO_DB_PREFIX.'configuration', ['config_value'=>json_encode($settings)], 'update', "config_key='$module'");
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
            $bizunoMod[$module]['properties']['status']= 1;
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
        dbClearCache();
        $cat    = getModuleCache($module, 'properties', 'category', false, 'bizuno');
        $layout = array_replace_recursive($layout, ['content'=>['rID'=>$module,'action'=>'href','link'=>BIZUNO_HOME."&p=bizuno/settings/manager&cat=$cat"]]);
    }

    /**
     * Sets security for the menu items into the database
     * @param array $menu - menu structure
     */
    private function setSecurity($menu)
    {
        $roleID  = getUserCache('profile', 'role_id', false, 1);
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
    public function moduleDelete(&$layout=[])
    {
        global $io;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $module = clean('rID', 'text', 'get');
        msgDebug("\n removing module: $module with properties = ".print_r(getModuleCache($module, 'properties'), true));
        if (empty($module)) { return; }
        $path = getModuleCache($module, 'properties', 'path');
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
        if (!empty($path) && !in_array(BIZUNO_HOST, ['phreesoft'])) {
            $modPath = str_replace(BIZUNO_DATA, '', $path);
            msgDebug("\nDeleting folder BIZUNO_DATA/$modPath");
            $io->folderDelete($modPath);
        }
        msgLog("Removed module: $module");
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."configuration WHERE config_key='$module'");
        dbClearCache(); // force reload of all users cache with next page access, menus and permissions, etc.
        $layout= array_replace_recursive($layout, ['content'=>['rID'=>$module, 'action'=>'href', 'link'=>BIZUNO_HOME."&p=bizuno/settings/manager"]]);
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
        $settings = [];
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
        global $io;
        if (!isset($adm->dirlist)) { return; }
        if (is_array($adm->dirlist)) { foreach ($adm->dirlist as $dir) { $io->validatePath($dir); } }
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
                if (!@rmdir(BIZUNO_DATA . $dir)) { msgAdd(sprintf(lang('err_io_dir_remove'), $dir)); }
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
     *
     * @param type $mID
     * @return type
     */
    private function btnDeactivate($mID)
    {
        return html5('', ['styles'=>['cursor'=>'pointer'],'attr'=>['type'=>'a','value'=>lang('deactivate')],
            'events'=>['onClick'=>"jsonAction('bizuno/settings/moduleDeactivate','$mID');"]]).' | ';
    }

    /**
     *
     * @param type $mID
     * @return type
     */
    private function btnDownload($mID)
    {
        return html5("download_$mID", ['icon'=>'download',
            'events'=>['onClick'=>"jsonAction('bizuno/settings/loadExtension', 0, '$mID');"]]);
    }

    /**
     *
     * @param type $mID
     * @param type $path
     * @param type $type
     * @return type
     */
    private function btnInstall($mID, $path, $type='text')
    {
        if ($type=='button') {
             return html5('', ['attr'=>['type'=>'button','value'=>lang('install')],
                'events'=>['onClick'=>"jsonAction('bizuno/settings/moduleInstall','$mID','$path');"]]);
        } else {
            return html5('', ['styles'=>['cursor'=>'pointer'],'attr'=>['type'=>'a','value'=>lang('activate')],
                'events'=>['onClick'=>"jsonAction('bizuno/settings/moduleInstall','$mID','$path');"]]).' | ';
        }
    }

    /**
     *
     * @param type $mID
     * @param type $price
     * @param type $link
     * @return type
     */
    private function btnPurchase($mID, $price, $link)
    {
        return html5("buy_$mID", ['attr'=>['type'=>'button','value'=>$price],'events'=>['onClick'=>"winHref('$link');"]]);
    }

    /**
     *
     * @param type $mID
     * @return type
     */
    private function btnDelete($mID)
    {
        return html5("remove_$mID", ['attr'=>['type'=>'button','value'=>lang('delete')],
            'events'=>['onClick'=>"if (confirm('".$this->lang['msg_module_delete_confirm']."')) jsonAction('bizuno/settings/moduleDelete', '$mID');"]]).' | ';
    }

    /**
     * merge in the loaded/custom modules
     */
    private function getLocal()
    {
        global $bizunoMod;
        bizAutoLoad(BIZUNO_ROOT."portal/guest.php", 'guest');
        $output = [];
        $guest  = new guest();
        $modList= $guest->getModuleList();
        foreach ($modList as $module => $path) {
            if (!empty($bizunoMod[$module]['properties'])) {
                $output[$module] = $bizunoMod[$module]['properties'];
            } else {
                $fqcn = "\\bizuno\\{$module}Admin";
                if (!bizAutoLoad("{$path}admin.php", $fqcn)) { continue; } // happens when an extension is removed from the file system manually
                $admin = new $fqcn();
                $output[$module] = [
                    'title'      => $admin->lang['title'],
                    'description'=> $admin->lang['description'],
                    'path'       => $path,
//                  'version'    => isset($admin->structure['version']) ? $admin->structure['version'] : '', // if uncommented, replaces newest version with installed version, breaks upgrade
                    'loaded'     => true,
                    'devStatus'  => !empty($admin->devStatus) ? $admin->devStatus : false,
                    'status'     => getModuleCache($module, 'properties', 'status'),
                    'settings'   => !empty($admin->settings) ? true : false];
            }
        }
        return sortOrder($output, 'title');
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

    /**
     * Updates the current_status db table with a modified values set by user in Settings
     */
    public function statusSave()
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX."current_status", '', 'stat_');
        $values = requestData($structure);
        dbWrite(BIZUNO_DB_PREFIX."current_status", $values, 'update');
        msgAdd(lang('msg_settings_saved'), 'success');
    }
}