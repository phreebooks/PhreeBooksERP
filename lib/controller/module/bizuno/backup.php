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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-05-20
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
        if (!$security = validateSecurity('bizuno', 'backup', 1)) { return; }
		$data = [
            'pageTitle'=> lang('bizuno_backup'),
			'toolbar'  => ['tbBackup' => ['icons' => [
                'restore'=> ['order'=>20, 'hidden'=>$security==4?false:true, 'events'=>  ['onClick'=>"hrefClick('bizuno/backup/managerRestore');"]],
				'help'   => ['order'=>99, 'index'=>'']]]],
			'divs' => [
                'submenu'=> ['order'=>10,'type'=>'html',   'html'=>viewSubMenu('tools')],
                'toolbar'=> ['order'=>20,'type'=>'toolbar','key'=>'tbBackup'],
				'heading'=> ['order'=>30,'type'=>'html',   'html'=>"<h1>".lang('bizuno_backup')."</h1>\n"],
				'backup' => ['order'=>50,'src'=>BIZUNO_LIB."view/module/bizuno/divBackup.php"]],
			'form'   => [
                'frmBackup'=> ['attr'=>  ['type'=>'form', 'action'=>BIZUNO_AJAX."&p=bizuno/backup/save"]],
				'frmAudit' => ['attr'=>  ['type'=>'form', 'action'=>BIZUNO_AJAX."&p=bizuno/backup/cleanAudit"]]],
			'fields' => [
                'incFiles' => ['label'=>$this->lang['desc_backup_all'], 'position'=>'after', 'attr'=>  ['type'=>'checkbox', 'value'=>'all']],
				'btnBackup'=> ['icon'=>'backup', 'size'=>'large','label'=>lang('go'), 'events'=> ['onClick'=>"jq('body').addClass('loading'); jq('#frmBackup').submit();"]],
				'btnAudit' => ['icon'=>'backup', 'size'=>'large','label'=>lang('go'), 'events'=> ['onClick'=>"jq('body').addClass('loading'); jsonAction('bizuno/backup/saveAudit');"]],
				'dateClean'=> ['attr'=>  ['type'=>'date', 'value'=>localeCalculateDate(date('Y-m-d'), 0, -1)]],
				'btnClean' => ['icon'=>'next', 'size'=>'large','label'=>lang('go'), 'events'=> ['onClick'=>"jq('body').addClass('loading'); jq('#frmAudit').submit();"]]],
			'datagrid' => ['backup' => $this->dgBackup('dgBackup')],
            'lang' => $this->lang];
		$layout = array_replace_recursive($layout, viewMain(), $data);
	}

	/**
	 * Load stored backup files through ajax call
     * @param array $layout - structure coming in
	 */
	public function mgrRows(&$layout=[])
    {
        $io = new \bizuno\io();
		$rows = $io->fileReadGlob($this->dirBackup);
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>json_encode(['total'=>sizeof($rows), 'rows'=>$rows])]);
	}
	
	/**
	 * This method executes a backup and download
     * @param array $layout - structure coming in
	 * @return Doesn't return if successful, returns messageStack error if not.
	 */
	public function save(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 2)) { return; }
		$incFiles = clean('data', 'text', 'post');
		// set execution time limit to a large number to allow extra time
        if (ini_get('max_execution_time') < $this->max_execution_time) { set_time_limit($this->max_execution_time); }
		// @todo add include files capability
        dbDump("bizuno-".date('Ymd-His'), $this->dirBackup);
        msgLog($this->lang['msg_backup_success']);
		msgAdd($this->lang['msg_backup_success'], 'success');
		$layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>"jq('#dgBackup').datagrid('reload');"]]);
	}
	
	/**
     * Datagrid to create the list of backup files from the backup folder
	 * @param string $name - html element id of the datagrid
	 * @return array $data - datagrid structure
	 */
	private function dgBackup($name)
    {
		$data = [
            'id'   => $name,
			'title'=> lang('files'),
			'attr' => [
                'url'     => BIZUNO_AJAX."&p=bizuno/backup/mgrRows",
				'pageSize'=> getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField' => 'title'],
			'events'=> ['onLoadSuccess'=> "function(data) { jq('#$name').datagrid('fitColumns', true); }"],
			'columns' => [
                'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>50],
					'events' => ['formatter'=>"function(value,row,index) { return {$name}Formatter(value,row,index); }"],
					'actions'=> [
                        'download'=>  ['icon'=>'download','size'=>'small','order'=>30,
                            'events'=>  ['onClick'=>"jq('#attachIFrame').attr('src','".BIZUNO_AJAX."&p=bizuno/main/fileDownload&pathID={$this->dirBackup}&fileID=idTBD');"]],
						'trash'   =>  ['icon'=>'trash',   'size'=>'small','order'=>70,
							'events'=>  ['onClick'=>"if (confirm('".lang('msg_confirm_delete')."')) jsonAction('bizuno/main/fileDelete','$name','{$this->dirBackup}idTBD');"]]]],
				'title'=> ['order'=>10,'label'=>lang('filename'),'attr'=>  ['width'=>200,'align'=>'center','resizable'=>true]],
				'size' => ['order'=>20,'label'=>lang('size'),    'attr'=>  ['width'=> 75,'align'=>'center','resizable'=>true]],
				'date' => ['order'=>30,'label'=>lang('date'),    'attr'=>  ['width'=> 75,'align'=>'center','resizable'=>true]]]];
		return $data;
	}
	
	/**
	 * This method backs up the audit log database sends the result to the backups folder.
     * @param array $layout - structure coming in
	 * @return json to reload datagrid
	 */
	public function saveAudit(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 2)) { return; }
        dbDump("bizuno_log-".date('Ymd-His'), $this->dirBackup, BIZUNO_DB_PREFIX."audit_log");
		msgAdd($this->lang['msg_backup_success'], 'success');
		$layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"jq('#dgBackup').datagrid('reload');"]]);
	}

	/**
     * Cleans old entries from the audit_log table prior to user specified data
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function cleanAudit(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
		$toDate = isset($_POST['dateClean']) ? clean($_POST['dateClean'], 'date') : localeCalculateDate(date('Y-m-d'), 0, -1); // default to -1 month from today
		$data['dbAction'] = [BIZUNO_DB_PREFIX."audit_log"=>"DELETE FROM ".BIZUNO_DB_PREFIX."audit_log WHERE date<='$toDate 23:59:59'"];
		$layout = array_replace_recursive($layout, $data);
	}

    public function managerUpgrade(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
        $btnUpgrade = ['icon'=>'next', 'size'=>'large','label'=>lang('go'), 'events'=> ['onClick'=>"jq('body').addClass('loading'); jsonAction('bizuno/backup/bizunoUpgradeGo');"]];
        $html  = "<h1>".lang('bizuno_upgrade')."</h1>\n";
        $html .= "<fieldset><legend>".lang('bizuno_upgrade')."</legend>";
        $html .= "<p>Click here to start your upgrade. Please make sure all users are not using the system. Once complete, all users will need to log out and back in to reset their cache.</p>";
        $html .= html5('', $btnUpgrade);
        $html .= "</fieldset>";
        $data = ['pageTitle'=> lang('bizuno_upgrade'),
			'toolbar'=> ['tbUpgrade'=>['icons'=>['cancel'=>['order'=>10,'events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=bizuno/backup/manager'"]]]]],
			'divs'   => [
                'toolbar'=> ['order'=>20,'type'=>'toolbar','key'=>'tbUpgrade'],
				'content'=> ['order'=>50,'type'=>'html',   'html'=>$html]]];
		$layout = array_replace_recursive($layout, viewMain(), $data);
    }
    
    public function bizunoUpgradeGo(&$layout=[])
    {
        $pathLocal= BIZUNO_DATA."temp/bizuno.zip";
        $bizID    = getUserCache('profile', 'biz_id');
        $bizUser  = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_user');
        $bizPass  = getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_pass');
        $data     = http_build_query(['bizID'=>$bizID, 'bizUser'=>$bizUser, 'bizPass'=>$bizPass]);
        $context  = stream_context_create(['http'=>[
            'method' =>'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n"."Content-Length: ".strlen($data)."\r\n",
            'content'=> $data]]);
        try {
            $io = new io();
            $source = "https://www.phreesoft.com/wp-admin/admin-ajax.php?action=bizuno_ajax&p=myPortal/admin/upgradeBizuno&host=".BIZUNO_HOST;
            $dest   = $pathLocal;
            msgDebug("\nReady to fetch $source to myFiles/temp/bizuno.zip");
            @copy($source, $dest, $context);
            if (@mime_content_type($pathLocal) == 'text/plain') { // something went wrong
                $msg = json_decode(file_get_contents($pathLocal), true);
                if (is_array($msg)) { return msgAdd("Unknown Exception: ".print_r($msg, true)); }
                else                { return msgAdd("Unknown Error: ".print_r($msg, true)); }
            }
            if (file_exists($pathLocal) && $io->zipUnzip($pathLocal, BIZUNO_ROOT, false)) {
                // see if an upgrade file is present, if so execute it and delete
                if (file_exists(BIZUNO_ROOT."bizunoUPG.php")) {
                    require (BIZUNO_ROOT."bizunoUPG.php");
                    unlink(BIZUNO_ROOT."bizunoUPG.php");
                }
            } else {
                return msgAdd('There was a problem retrieving the upgrade, please visit PhreeSoft community forum for assistance.');
            }
        } catch (Exception $e) {
            return msgAdd("We had an exception upgrading Bizuno: ". print_r($e, true));
        }
        @unlink($pathLocal);
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"alert('".$this->lang['msg_upgrade_success']."'); window.location='".BIZUNO_AJAX."&p=bizuno/portal/logout';"]]);
    }
    
	/**
     * Entry point for Bizuno db Restore page
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerRestore(&$layout)
    {
        $delFile = clean('del', 'text', 'get');
        if (!$security = validateSecurity('bizuno', 'backup', 4)) { return; }
		$data = [
            'pageTitle'=> lang('bizuno_restore'),
			'toolbar'  => ['tbRestore' => ['icons' => [
                'cancel'=> ['order'=>10, 'events'=>  ['onClick'=>"location.href='".BIZUNO_HOME."&p=bizuno/backup/manager'"]]]]],
			'divs'     => [
                'toolbar'=> ['type'=>'toolbar', 'order'=>20, 'key'=>'tbRestore'],
				'heading'=> ['type'=>'html',    'order'=>30, 'html'=>"<h1>".lang('bizuno_restore')."</h1>\n"],
				'restore'=> ['type'=>'template','order'=>50, 'src' =>BIZUNO_LIB."view/module/bizuno/divRestore.php"]],
			'content' => [
                'file_upload'=> ['attr'=>  ['type'=>'file', 'name'=>'files[]', 'multiple'=>true]],
				'btn_upload' => ['styles'=>  ['display'=>'none'], 'attr'=>  ['type'=>'button', 'value'=>lang('upload')]]],
			'datagrid' => ['restore' => $this->dgRestore('dgRestore')]];
        $io = new \bizuno\io();
        if ($delFile) { $io->fileDelete($this->dirBackup.$delFile); }
		$data['bkFiles'] = $io->folderRead($this->dirBackup);
		msgDebug('found files: '.print_r($data['bkFiles'], true));
		$layout = array_replace_recursive($layout, viewMain(), $data);
	}

	/**
     * Datagrid to list files to restore 
	 * @param string $name - html element id of the datagrid
	 * @return array $data - datgrid structure
	 */
	private function dgRestore($name='dgRestore')
    {
		$data = [
            'id'    => $name,
			'title' => lang('files'),
			'attr'  => [
                'url'     => BIZUNO_AJAX."&p=bizuno/backup/mgrRows",
				'pageSize'=> getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField' => 'title'],
			'events'=> ['onLoadSuccess'=>"function(data) { jq('#$name').datagrid('fitColumns', true); }"],
			'columns' => [
                'action' => ['order'=>1, 'attr'=>  ['width'=>50],
					'events' => ['formatter'=>"function(value,row,index) { return {$name}Formatter(value,row,index); }"],
					'actions'=> [
                        'start' =>  ['icon'=>'import','size'=>'small','order'=>30,
							'events'=>  ['onClick'=>"if(confirm('".$this->lang['msg_restore_confirm']."')) { jq('body').addClass('loading'); jsonAction('bizuno/backup/saveRestore', 0, '{$this->dirBackup}idTBD'); }"]],
						'trash' =>  ['icon'=>'trash', 'size'=>'small','order'=>70,
							'events'=>  ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/main/fileDelete','$name','{$this->dirBackup}idTBD');"]]]],
				'title'=> ['order'=>10,'label'=>lang('filename'),'attr'=>['width'=>200,'align'=>'center','resizable'=>true]],
				'size' => ['order'=>20,'label'=>lang('size'),    'attr'=>['width'=> 75,'align'=>'center','resizable'=>true]],
				'date' => ['order'=>30,'label'=>lang('date'),    'attr'=>['width'=> 75,'align'=>'center','resizable'=>true]]]];
		return $data;
	}

	/**
     * Method to receive a file to upload into the backup folder for db restoration
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function uploadRestore(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'backup', 2)) { return; }
		$io = new io();
		$io->options = [
            'script_url' => BIZUNO_ROOT."apps/jquery-file-upload/server/php/index.php",
			'upload_dir' => BIZUNO_DATA.$this->dirBackup,
			'upload_url' => BIZUNO_AJAX.'&p=bizuno/backup/uploadRestore',
			'param_name' => 'file_upload',
			'image_versions' => []]; // supresses creation of thumbnail folder 
        if (!isset($_SERVER['CONTENT_TYPE'])) { $_SERVER['CONTENT_TYPE'] = null; }
		$io->fileUpload();
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>null]); // content generated by jquery file upload plugin 
	}

    /**
     * This method restores a gzip db backup file to the database, replacing the current tables
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
        $layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>"alert('".$this->lang['msg_restore_success']."'); window.location='".BIZUNO_AJAX."&p=bizuno/portal/logout';"]]);
	}
}
