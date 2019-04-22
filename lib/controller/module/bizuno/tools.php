<?php
/*
 * Bizuno Tools methods
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
 * @filesource /lib/controller/module/bizuno/tools.php
 */

namespace bizuno;

class bizunoTools {
    public $moduleID = 'bizuno';
    public $supportEmail;
    public $reasons;

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $this->supportEmail = defined('BIZUNO_SUPPORT_EMAIL') ? BIZUNO_SUPPORT_EMAIL : '';
        $this->reasons = [
            'question'  => $this->lang['ticket_question'],
            'bug'       => $this->lang['ticket_bug'],
            'suggestion'=> $this->lang['ticket_suggestion'],
            'account'   => $this->lang['ticket_my_account']];
    }

    /**
     * Support ticket page structure
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function ticketMain(&$layout=[])
    {
        $reasons = [['id'=>'none', 'text' => lang('select')]];
        foreach ($this->reasons as $key => $value) { $reasons[] = ['id'=>$key, 'text'=>$value]; }
        $values  = dbGetRow(BIZUNO_DB_PREFIX."users", "admin_id=".getUserCache('profile', 'admin_id', false, 0));
        $machines= [['id'=>'pc','text'=>'PC'],['id'=>'mac','text'=>'Mac'],['id'=>'mobile','text'=>'Mobile Phone'],['id'=>'tablet','text'=>'Tablet'],['id'=>'other','text'=>'Other (list below)']];
        $os      = [['id'=>'windows','text'=>'Windows'],['id'=>'osx','text'=>'Apple OSX'],['id'=>'ios','text'=>'iPhone IOS'],['id'=>'android','text'=>'Android'],['id'=>'other','text'=>'Other (list below)']];
        $browsers= [['id'=>'firefox','text'=>'Firefox'],['id'=>'chrome','text'=>'Chrome'],['id'=>'safari','text'=>'Safari'],['id'=>'edge','text'=>'MS Edge'],['id'=>'ie','text'=>'Internet Explorer'],['id'=>'other','text'=>'Other (list below)']];
        $fields = [
            'ticketURL'  => ['order'=>15,'break'=>true,'attr'=>['type'=>'hidden','value'=>$_SERVER['HTTP_HOST']]],
            'langDesc'   => ['order'=>20,'break'=>true,'html'=>$this->lang['ticket_desc'],'attr'=>['type'=>'raw']],
            'selReason'  => ['order'=>25,'break'=>true,'label'=>lang('reason'), 'values'=>$reasons, 'attr'=>['type'=>'select']],
            'selMachine' => ['order'=>30,'break'=>true,'label'=>lang('Machine'),'values'=>$machines,'attr'=>['type'=>'select']],
            'selOS'      => ['order'=>35,'break'=>true,'label'=>lang('OS'),     'values'=>$os,      'attr'=>['type'=>'select']],
            'selBrowser' => ['order'=>40,'break'=>true,'label'=>lang('Browser'),'values'=>$browsers,'attr'=>['type'=>'select']],
            'ticketUser' => ['order'=>45,'break'=>true,'label'=>lang('address_book_primary_name'),'attr'=>['value'=>$values['title'],'size'=>40]],
            'ticketEmail'=> ['order'=>50,'break'=>true,'label'=>lang('email'),'attr'=>['value'=>$values['email'],'size'=>60]],
            'ticketPhone'=> ['order'=>55,'break'=>true,'label'=>lang('telephone')],
            'ticketDesc' => ['order'=>60,'break'=>true,'label'=>lang('description'),'attr'=>['type'=>'textarea','rows'=>8,'cols'=>60]],
            'ticketFile' => ['order'=>65,'label'=>$this->lang['ticket_attachment'],'attr'=>['type'=>'file']], // break is auto-removed
            'ticketPhone'=> ['order'=>70,'html'=>"<br />",'attr'=>['type'=>'raw']],
            'btnSubmit'  => ['order'=>75,'events'=>['onClick'=>"jq('#frmTicket').submit();"],'attr'=>['type'=>'button','value'=>lang('submit')]]];
        $data = ['type'=>'page','title'=>lang('support'),
            'divs'  => ['tcktMain'=>['order'=>50,'type'=>'divs','divs'=>[
                'head'   => ['order'=>10,'type'=>'html',  'html'=>"<h1>".lang('support')."</h1>"],
                'formBOF'=> ['order'=>15,'type'=>'form',  'key' =>'frmTicket'],
                'body'   => ['order'=>50,'type'=>'fields','keys'=>array_keys($fields)],
                'formEOF'=> ['order'=>85,'type'=>'html',  'html'=>"</form>"]]]],
            'forms' => ['frmTicket'=>['attr'=>['type'=>'form','method'=>'post','action'=>BIZUNO_AJAX."&p=bizuno/tools/ticketSave",'enctype'=>"multipart/form-data"]]],
            'fields'=> $fields];
        $layout = array_replace_recursive($layout, viewMain(), $data);

    }

    /**
     * Support ticket emailed to Bizuno BizNerds
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function ticketSave(&$layout=[])
    {
        global $io;
        bizAutoLoad(BIZUNO_LIB."model/mail.php", 'bizunoMailer');
        $user = clean('ticketUser', 'text', 'post');
        $email= clean('ticketEmail','text', 'post');
        $url  = clean('ticketURL',  'text', 'post');
        $tel  = clean('ticketPhone','text', 'post');
        $type = clean('selReason',  'text', 'post');
        $box  = clean('selMachine', 'text', 'post');
        $os   = clean('selOS',      'text', 'post');
        $brwsr= clean('selBrowser', 'text', 'post');
        $msg  = str_replace("\n", '<br />', clean('ticketDesc', 'text', 'post'));
        $bizName = getModuleCache('bizuno', 'settings', 'company', 'primary_name');
        $subject = "Support Ticket: $bizName - $user ($email)";
        $message = "$msg<br /><br />Reason: $type<br />Phone: $tel<br />Ref: $url ($box; $os; $brwsr)";
        if (!$this->supportEmail) { return msgAdd("You do not have a support email address defined for your business , Please visit the PhreeSoft website for support."); }
        $toName  = defined('BIZUNO_SUPPORT_NAME') ? BIZUNO_SUPPORT_NAME : $this->supportEmail;
        $mail    = new bizunoMailer($this->supportEmail, $toName, $subject, $message, $email, $user);
        if (isset($_FILES['ticketFile']['name']) && $_FILES['ticketFile']['name']) {
            $type= $io->guessMimetype($_FILES['ticketFile']['name']);
            if ($io->validateUpload('ticketFile', $type, ['png', 'jpg', 'jpeg', 'tiff', 'gif', 'pdf', 'txt', 'csv', 'zip'], false)) {
                $mail->attach($_FILES['ticketFile']['tmp_name'], $_FILES['ticketFile']['name']);
            }
        }
        $mail->sendMail();
        msgAdd("Your email has been sent to the PhreeSoft Support team. We'll be in contact with you shortly.", 'success');
        $this->ticketMain($layout);
    }

    /**
     * Creates/changes the encryption key
     */
    public function encryptionChange()
    {
        if (!validateSecurity('bizuno', 'admin', 4)) { return; }
        bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');
        $old_key= clean('orig','password', 'get');
        $new_key= clean('new', 'password', 'get');
        $confirm= clean('dup', 'password', 'get');
        $current= getModuleCache('bizuno', 'encKey');
        if (empty($current)) { $current = ':'; } // key is not set
        $stack  = explode(':', $current);
        if ($current<>':' && md5($stack[1] . $old_key) <> $stack[0]) { return msgAdd(lang('err_login_failed')); }
        if (strlen($new_key) < getModuleCache('bizuno', 'settings', 'general', 'password_min', 8) || $new_key != $confirm) {
            return msgAdd(lang('err_encrypt_failed'));
        }
        $result = dbGetMulti(BIZUNO_DB_PREFIX.'data_security');
        if (sizeof($result) > 0) { // convert old encrypt to new encrypt
            $enc = new encryption();
            foreach ($result as $key => $row) {
                unset($result[$key]['id']);
                $result[$key]['enc_value'] = $enc->encrypt($new_key, $enc->decrypt($old_key, $row['enc_value']));
            }
            $keys = array_keys($result[0]);
            $sql  = "INSERT INTO ".BIZUNO_DB_PREFIX."data_security (`".implode('`, `', array_keys($keys))."`) VALUES ";
            foreach ($result as $row) { $sql .= "(`".implode("`, `", $row)."`),"; }
            $sql .= substr($sql, 0, -1);
            dbTransactionStart();
            dbGetResult("TRUNCATE ".BIZUNO_DB_PREFIX."data_security"); // empty the db
            dbGetResult($sql); // write the table
            dbTransactionCommit();
        }
        $newEnc = encryptValue($new_key);
        setModuleCache('bizuno', 'encKey', false, $newEnc);
        setUserCache('profile', 'admin_encrypt', $new_key);
        msgLog($this->lang['msg_encryption_changed']);
        msgAdd($this->lang['msg_encryption_changed'], 'success');
    }

    /**
     * deletes all encryption rows from the db table that have expired dates
     */
    public function encryptionClean()
    {
        $date = clean('data', ['format'=>'date','default'=>date('Y-m-d')], 'get');
        $output = dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."data_security WHERE exp_date<'$date'", 'delete');
        if ($output === false) {
            msgAdd("There was an error deleting records!");
        } else {
            msgAdd("Success, the number of records removed was: $output", 'success');
        }
    }

    /**
     * Main entry point structure for the import/export operations
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function impExpMain(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'impexp', 1)) { return; }
        $title= lang('bizuno_impexp');
        $data = ['title'=> $title,
            'toolbars' =>['tbImpExp'=>['icons'=>['help'=>['order'=>99,'index'=>'']]]],
            'divs' => [
                'submenu'=> ['order'=>10,'type'=>'html',   'html'=>viewSubMenu('tools')],
                'toolbar'=> ['order'=>20,'type'=>'toolbar','key' =>'tbImpExp'],
                'heading'=> ['order'=>30,'type'=>'html',   'html'=>"<h1>$title</h1>\n"],
                'biz_io' => ['order'=>60,'type'=>'tabs',   'key' =>'tabImpExp']],
            'tabs'=> [
                'tabImpExp'=> ['divs'=>  ['module'=> ['order'=>10,'label'=>lang('module'),'type'=>'tabs', 'key'=>'tabAPI']]],
                'tabAPI'   => ['styles'=>['height'=>'300px'],'attr'=>  ['tabPosition'=>'left', 'fit'=>true, 'headerWidth'=>250]]],
            'lang' => $this->lang];
        $apis = getModuleCache('bizuno', 'api', false, false, []);
        foreach ($apis as $settings) {
            $parts = explode('/', $settings['path']);
            if (file_exists (getModuleCache($parts[0], 'properties', 'path')."/{$parts[1]}.php")) {
                $fqcn = "\\bizuno\\".$parts[0].ucfirst($parts[1]);
                bizAutoLoad(getModuleCache($parts[0], 'properties', 'path')."/{$parts[1]}.php", $fqcn);
                $tmp = new $fqcn();
                $tmp->{$parts[2]}($data); // looks like phreebooksAPI($data)
            }
        }
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * This function extends the PhreeBooks module close fiscal year function to handle Bizuno operations
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function fyCloseHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $html  = "<p>"."Closing the fiscal year for the Bizuno module consist of deleting audit log entries during or before the fiscal year being closed. "
                . "To prevent the these entries from being removed, check the box below."."</p>";
        $html .= html5('bizuno_keep', ['label' => 'Do not delete audit log entries during or before this closing fiscal year', 'position'=>'after','attr'=>['type'=>'checkbox','value'=>'1']]);
        $layout['tabs']['tabFyClose']['divs'][$this->lang['title']] = ['order'=>50,'label'=>$this->lang['title'],'type'=>'html','html'=>$html];
    }

    /**
     * Hook to PhreeBooks Close FY method, adds tasks to the queue to execute AFTER PhreeBooks processes the journal
     */
    public function fyClose()
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $skip = clean('bizuno_keep', 'boolean', 'post');
        if ($skip) { return; } // user wants to keep all records, nothing to do here, move on
        $cron = getUserCache('cron', 'fyClose');
        $cron['taskClose'][] = ['mID'=>$this->moduleID]; // ,'method'=>'fyCloseNext']; // assumed method == fyCloseNext, no settings
        setUserCache('cron', 'fyClose', $cron);
    }

    /**
     * continuation of fiscal year close, db purge and old folder purge, as necessary
     * @return string
     */
    public function fyCloseNext($settings=[], &$cron=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $endDate = $cron['fyEndDate'];
        if (!$endDate) { return; }
        $dateFull = $endDate.' 23:59:59';
        $cnt = dbGetValue(BIZUNO_DB_PREFIX.'audit_log', 'COUNT(*) AS cnt', "`date`<='$dateFull'", false);
        $cron['msg'][] = "Read $cnt records to delete from table: audit_log";
        msgDebug("\nExecuting sql: DELETE FROM ".BIZUNO_DB_PREFIX."audit_log WHERE `date`<='$dateFull'");
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."audit_log WHERE `date`<='$dateFull'");
        return "Finished processing table audit_log";
    }

    public function repairComments($verbose=true)
    {
        $tables = [];
        include(BIZUNO_LIB."controller/module/bizuno/install/tables.php"); // loads $tables
        $this->getExtTables($tables);
        foreach ($tables as $table => $tProps) { // as defined by code
            if (!dbTableExists(BIZUNO_DB_PREFIX.$table)) { continue; }
            $stmt = dbGetResult("SHOW FULL COLUMNS FROM ".BIZUNO_DB_PREFIX."$table");
            if (!$stmt) { return msgAdd("No results for table $table! Bailing"); }
            $structure = $stmt->fetchAll(\PDO::FETCH_ASSOC);
//          msgDebug("\nTable $table returned structure: ".print_r($structure, true));
            foreach ($structure as $field) {
                $default = in_array($field['Default'], ['CURRENT_TIMESTAMP']) ? $field['Default'] : "'{$field['Default']}'"; // don't quote mysql reserved words
                $params  = $field['Type'].' ';
                $params .= $field['Null']=='NO'      ? 'NOT NULL '         : 'NULL ';
                $params .= !empty($field['Default']) ? "DEFAULT $default " : '';
                $params .= $field['Extra']           ? $field['Extra'].' ' : '';
                $newComment = !empty($tProps['fields'][$field['Field']]['comment']) ? $tProps['fields'][$field['Field']]['comment'] : $field['Comment'];
                if ($newComment == $field['Comment']) { continue; } // if not changed, do nothing
//              msgDebug("\nWriting sql to db: ALTER TABLE `".BIZUNO_DB_PREFIX."$table` CHANGE `{$field['Field']}` `{$field['Field']}` $params COMMENT '$newComment'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."$table` CHANGE `{$field['Field']}` `{$field['Field']}` $params COMMENT '$newComment'");
            }
        }
        if ($verbose) { msgAdd("finished!"); }
    }

    private function getExtTables(&$tables=[])
    {
        if (!is_dir(BIZUNO_EXT)) { return; }
        $dirs = scandir(BIZUNO_EXT);
        msgDebug("\nScanning extensions returned: ".print_r($dirs, true));
        foreach ($dirs as $ext) {
            if (in_array($ext, ['.', '..']) || !is_dir(BIZUNO_EXT.$ext)) { continue; }
            include_once(BIZUNO_EXT."$ext/admin.php");
            if (!class_exists("\\bizuno\\{$ext}Admin")) { continue; }
            $fqcn  = "\\bizuno\\{$ext}Admin";
            $admin = new $fqcn();
            if (!empty($admin->tables)) {
                msgDebug("\nUpdating table list for extension $ext");
                $tables = array_merge($tables, $admin->tables); }
        }
    }
}
