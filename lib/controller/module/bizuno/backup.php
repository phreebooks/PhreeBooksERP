<?php
/*
 * Handles the backup and restore functions
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
 * @version    4.x Last Update: 2020-08-15
 * @filesource /lib/controller/module/bizuno/backup.php
 */

namespace bizuno;

class bizunoBackup
{
    public $moduleID = 'bizuno';
    private $update_queue = [];

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $this->max_execution_time = 20000;
        $this->dirBackup = "backups/";
    }

    /**
     * Page entry point for the backup methods
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        global $io;
        if (!$security = validateSecurity('bizuno', 'backup', 1)) { return; }
        $data = ['title'=>lang('bizuno_backup'),
            'divs'   => ['body'=>['classes'=>['areaView'],'type'=>'divs','divs'=>[
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbBackup'],
                'manager'=> ['order'=>50,'type'=>'divs','classes'=>['areaView'],'divs'=>[
                    'backup' => ['order'=>20,'type'=>'panel','key'=>'backup', 'classes'=>['block33']],
                    'divAtch'=> ['order'=>30,'type'=>'panel','key'=>'divAtch','classes'=>['block66']],
                    'audit'  => ['order'=>40,'type'=>'panel','key'=>'audit',  'classes'=>['block33']]]]]]],
            'toolbars'=> ['tbBackup'=>['icons'=>[
                'restore'=> ['order'=>20,'hidden'=>$security==4?false:true,'events'=>['onClick'=>"hrefClick('bizuno/backup/managerRestore');"]],
                'help'   => ['order'=>99,'icon'=>'help','label' =>lang('help'),'align'=>'right','hideLabel'=>true,'index'=>'backup-restore']]]],
            'panels' => [
                'backup' => ['label'=>lang('bizuno_backup'),'type'=>'divs','divs'=>[
                    'formBOF'=> ['order'=>10,'type'=>'form',  'key' =>'frmBackup'],
                    'body'   => ['order'=>30,'type'=>'fields','keys'=>['backupDesc','btnBackup']], // 'incFiles' is a later feature ???
                    'formEOF'=> ['order'=>90,'type'=>'html',  'html'=>"</form>"]]],
                'audit'  => ['label'=>$this->lang['audit_log_backup'],'type'=>'divs','divs'=>[
                    'formBOF'=> ['order'=>10,'type'=>'form',  'key' =>'frmAudit'],
                    'body'   => ['order'=>30,'type'=>'fields','keys'=>['auditDesc','btnAudit','audClnDesc','dateClean','btnClean']],
                    'formEOF'=> ['order'=>90,'type'=>'html',  'html'=>"</form>"]]],
                'divAtch'=> ['type'=>'attach','defaults'=>['dgName'=>'dgBackup','path'=>$this->dirBackup,'title'=>lang('files'),'url'=>BIZUNO_AJAX."&p=bizuno/backup/mgrRows",'ext'=>$io->getValidExt('backup')]]],
            'forms'   => [
                'frmBackup'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/backup/save"]],
                'frmAudit' => ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/backup/cleanAudit"]]],
            'fields'  => [
                'backupDesc'=> ['order'=>10,'html'=>$this->lang['desc_backup'],     'attr'=>['type'=>'raw']],
//              'incFiles'  => ['order'=>20,'label'=>$this->lang['desc_backup_all'],'attr'=>['type'=>'checkbox', 'value'=>'all']],
                'btnBackup' => ['order'=>30,'icon'=>'backup','label'=>lang('go'),'align'=>'right','events'=>['onClick'=>"jq('body').addClass('loading'); jq('#frmBackup').submit();"]],
                'auditDesc' => ['order'=>10,'html'=>$this->lang['audit_log_backup_desc'],'attr'=>['type'=>'raw']],
                'btnAudit'  => ['order'=>20,'icon'=>'backup','label'=>lang('go'),'align'=>'right','events'=>['onClick'=>"jq('body').addClass('loading'); jsonAction('bizuno/backup/saveAudit');"]],
                'audClnDesc'=> ['order'=>30,'html'=>"<br /><hr />".$this->lang['desc_audit_log_clean'],'attr'=>['type'=>'raw']],
                'dateClean' => ['order'=>40,'attr'=>['type'=>'date', 'value'=>localeCalculateDate(date('Y-m-d'), 0, -1)]],
                'btnClean'  => ['order'=>50,'icon'=>'next',  'label'=>lang('go'),'align'=>'right','events'=>['onClick'=>"jq('body').addClass('loading'); jq('#frmAudit').submit();"]]],
            'jsReady' => ['init'=>"ajaxForm('frmBackup'); ajaxForm('frmAudit');"]];
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * Entry point for Bizuno db Restore page
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerRestore(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
        $upload_mb= min((int)(ini_get('upload_max_filesize')), (int)(ini_get('post_max_size')), (int)(ini_get('memory_limit')));
        $data     = ['title'=>lang('bizuno_restore'),
            'divs'    => ['body'=>['classes'=>['areaView'],'type'=>'divs','divs'=>[
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbRestore'],
                'heading'=> ['order'=>15,'type'=>'html',   'html'=>"<h1>".lang('bizuno_restore')."</h1>"],
                'manager'=> ['order'=>50,'type'=>'divs','classes'=>['areaView'],'divs'=>[
                    'restore'=> ['order'=>30,'type'=>'panel','key'=>'restore','classes'=>['block66']]]]]]],
            'panels' => [
                'restore'=> ['type'=>'divs','divs'=>[
                    'dgRstr' => ['order'=>40,'type'=>'datagrid','key' =>'dgRestore'],
                    'formBOF'=> ['order'=>50,'type'=>'form',    'key' =>'frmRestore'],
                    'body'   => ['order'=>60,'type'=>'fields',  'keys'=>['txtFile','fldFile','btnFile'],
                    'formEOF'=> ['order'=>90,'type'=>'html',    'html'=>"</form>"]]]]],
            'toolbars'=> ['tbRestore' => ['icons'=>['cancel'=>['order'=>10,'events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=bizuno/backup/manager'"]]]]],
            'datagrid'=> ['dgRestore' => $this->dgRestore('dgRestore')],
            'forms'   => ['frmRestore'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/backup/uploadRestore",'enctype'=>"multipart/form-data"]]],
            'fields'  => [
                'txtFile'=> ['order'=>10,'html'=>lang('msg_io_upload_select')." ".sprintf(lang('max_upload'), $upload_mb)."<br />",'attr'=>['type'=>'raw']],
                'fldFile'=> ['order'=>15,'attr'=>['type'=>'file']],
                'btnFile'=> ['order'=>20,'events'=>['onClick'=>"jq('#frmRestore').submit();"],'attr'=>['type'=>'button','value'=>lang('upload')]]],
            'jsReady' => ['init'=>"ajaxForm('frmRestore');"]];
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * Load stored backup files through AJAX call
     * @param array $layout - structure coming in
     */
    public function mgrRows(&$layout=[])
    {
        global $io;
        $rows   = $io->fileReadGlob($this->dirBackup, $io->getValidExt('backup'));
        $totRows= sizeof($rows);
        $rowNum = clean('rows',['format'=>'integer','default'=>10],'post');
        $pageNum= clean('page',['format'=>'integer','default'=>1], 'post');
        $output = array_slice($rows, ($pageNum-1)*$rowNum, $rowNum);
        $layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>json_encode(['total'=>$totRows, 'rows'=>$output])]);
    }

    /**
     * This method executes a backup and download
     * @todo add include files capability
     * @param array $layout - structure coming in
     * @return Doesn't return if successful, returns messageStack error if not.
     */
    public function save(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 2)) { return; }
        // @todo - Need to implement this, perhaps make sure file size is not too big or drop option
//      $incFiles = clean('data', 'text', 'post');
        // set execution time limit to a large number to allow extra time
        if (ini_get('max_execution_time') < $this->max_execution_time) { set_time_limit($this->max_execution_time); }
        if (!dbDump("bizuno-".date('Ymd-His'), $this->dirBackup)) { return msgAdd(lang('err_io_write_failed')); }
        msgLog($this->lang['msg_backup_success']);
        msgAdd($this->lang['msg_backup_success'], 'success');
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"bizGridReload('dgBackup');"]]);
    }

    /**
     * This method backs up the audit log database sends the result to the backups folder.
     * @param array $layout - structure coming in
     * @return json to reload grid
     */
    public function saveAudit(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 2)) { return; }
        if (!dbDump("bizuno_log-".date('Ymd-His'), $this->dirBackup, BIZUNO_DB_PREFIX."audit_log")) { return msgAdd(lang('err_io_write_failed')); }
        msgAdd($this->lang['msg_backup_success'], 'success');
        $layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"bizGridReload('dgBackup');"]]);
    }

    /**
     * Cleans old entries from the audit_log table prior to user specified data
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function cleanAudit(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
        $toDate = clean('dateClean', ['format'=>'date', 'default'=>localeCalculateDate(date('Y-m-d'), 0, -1)], 'post'); // default to -1 month from today
        $data['dbAction'] = [BIZUNO_DB_PREFIX."audit_log"=>"DELETE FROM ".BIZUNO_DB_PREFIX."audit_log WHERE date<='$toDate 23:59:59'"];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Generates the page view (confirmation) to start the upgrade script
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerUpgrade(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
        $btnUpgrade = ['icon'=>'next', 'size'=>'large','label'=>lang('go'), 'events'=>['onClick'=>"jq('body').addClass('loading'); jsonAction('bizuno/backup/bizunoUpgradeGo');"]];
        $html  = "<p>Click here to start your upgrade. Please make sure all users are not using the system. Once complete, all users will need to sign off and back in to reset their cache.</p>";
        $html .= html5('', $btnUpgrade);
        $data = ['title'=> lang('bizuno_upgrade'),
            'toolbars'=> ['tbUpgrade'=>['icons'=>['cancel'=>['order'=>10,'events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=bizuno/backup/manager'"]]]]],
            'divs'    => [
                'toolbars'=> ['order'=>20,'type'=>'toolbar','key'=>'tbUpgrade'],
                'body'    => ['order'=>50,'type'=>'divs','classes'=>['areaView'],'divs'=>[
                    'upgrade'=> ['order'=>20,'type'=>'panel','classes'=>['block33'],'key'=>'upgrade']]]],
            'panels'  => ['upgrade'=>['label'=>lang('bizuno_upgrade'),'type'=>'html','html'=>$html]]];
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     *
     * @global class $io -  needed for the call to download the current version from PhreeSoft
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function bizunoUpgradeGo(&$layout=[])
    {
        global $io;
        $pathLocal= BIZUNO_DATA."temp/";
        $zipFile  = $pathLocal."bizuno.zip";
        $bizID    = getUserCache('profile', 'biz_id');
        $bizUser  = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_user');
        $bizPass  = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_pass');
        $data     = http_build_query(['bizID'=>$bizID, 'UserID'=>$bizUser, 'UserPW'=>$bizPass]);
        $context  = stream_context_create(['http'=>[
            'method' =>'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n",
            'content'=> $data]]);
        try {
            $source = "https://www.phreesoft.com/wp-admin/admin-ajax.php?action=bizuno_ajax&p=myPortal/admin/upgradeBizuno&host=".BIZUNO_HOST;
            $dest   = $zipFile;
            msgDebug("\nReady to fetch $source to $zipFile");
            @copy($source, $dest, $context);
            if (@mime_content_type($zipFile) == 'text/plain') { // something went wrong
                $msg = json_decode(file_get_contents($zipFile), true);
                if (is_array($msg)) { return msgAdd("Unknown Exception: ".print_r($msg, true)); }
                else                { return msgAdd("Unknown Error: "    .print_r($msg, true)); }
            }
            if (file_exists($zipFile) && $io->zipUnzip($zipFile, $pathLocal, false)) {
                msgDebug("\nUnzip successful, removing downloaded zipped file: $zipFile");
                @unlink($zipFile);
                $srcFolder = $this->guessFolder("temp/");
                if (!$srcFolder) { return msgAdd("Could not find downloaded upgrade folder, aborting!"); }
                $io->folderMove("temp/$srcFolder/", '', true);
                rmdir($pathLocal.$srcFolder);
            } else {
                return msgAdd('There was a problem retrieving the upgrade, please visit PhreeSoft community forum for assistance.');
            }
        } catch (Exception $e) {
            return msgAdd("We had an exception upgrading Bizuno: ". print_r($e, true));
        }
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"alert('".$this->lang['msg_upgrade_success']."'); jsonAction('bizuno/portal/logout');"]]);
    }

    /**
     * Tries to determine the file name of the latest version that has been uploaded
     * @param string $path - location of where the uploaded version file is placed
     * @return string - File
     */
    private function guessFolder($path)
    {
        global $io;
        $files = $io->folderRead($path);
        msgDebug("\nTrying to read folder $path and got results: ".print_r($files, true));
        foreach ($files as $file) {
            if (!is_dir(BIZUNO_DATA.$path.$file)) { continue; }
            $found = filemtime(BIZUNO_DATA.$path.$file) > time()-3600 ? true : false;
            msgDebug("\nGuessing folder $path$file with timestamp: ".filemtime(BIZUNO_DATA.$path.$file)." compared to a minute ago: ".(time()-60)." to be within 60 seconds and result = ".($found ? 'ture' : 'false'));
            if ($found) { return $file; }
        }
        msgAdd("Looking for unzipped upgrade files in folder ".BIZUNO_DATA."$path but could not find any. Please delete all folders in the directory and retry the upgrade.");
    }

    /**
     * Method to receive a file to upload into the backup folder for db restoration
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function uploadRestore(&$layout)
    {
        global $io;
        $io->uploadSave('fldFile', $this->dirBackup);
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"bizGridReload('dgRestore');"]]);
    }

    /**
     * This method restores a .gzip db backup file to the database, replacing the current tables
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function saveRestore(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
        $dbFile = clean('data', 'text', 'get');
        if (!file_exists(BIZUNO_DATA.$dbFile)) { return msgAdd("Bad filename passed! ".BIZUNO_DATA.$dbFile); }
        // set execution time limit to a large number to allow extra time
        dbRestore($dbFile);
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"alert('".$this->lang['msg_restore_success']."'); jsonAction('bizuno/portal/logout');"]]);
    }

    /**
     * Grid to list files to restore
     * @param string $name - HTML element id of the grid
     * @return array $data - grid structure
     */
    private function dgRestore($name='dgRestore')
    {
        $data = ['id'=>$name, 'title'=>lang('files'),
            'attr'   => ['idField'=>'title', 'url'=>BIZUNO_AJAX."&p=bizuno/backup/mgrRows"],
            'columns'=> [
                'action'=> ['order'=> 1,'label'=>lang('action'),  'attr'=>['width'=>60],
                    'events' =>['formatter'=>"function(value,row,index) { return {$name}Formatter(value,row,index); }"],
                    'actions'=> [
                        'start' => ['order'=>30,'icon'=>'import','label'=>lang('restore'),'events'=>['onClick'=>"if(confirm('".$this->lang['msg_restore_confirm']."')) { jq('body').addClass('loading'); jsonAction('bizuno/backup/saveRestore', 0, '{$this->dirBackup}idTBD'); }"]],
                        'trash' => ['order'=>70,'icon'=>'trash','events'=>['onClick'=>"if(confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/main/fileDelete','$name','{$this->dirBackup}idTBD');"]]]],
                'title' => ['order'=>10,'label'=>lang('filename'),'attr'=>['width'=>200,'align'=>'center','resizable'=>true]],
                'size'  => ['order'=>20,'label'=>lang('size'),    'attr'=>['width'=>100,'align'=>'center','resizable'=>true]],
                'date'  => ['order'=>30,'label'=>lang('date'),    'attr'=>['width'=>100,'align'=>'center','resizable'=>true]]]];
        return $data;
    }
}
