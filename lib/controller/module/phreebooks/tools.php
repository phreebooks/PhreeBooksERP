<?php
/*
 * Module PhreeBooks - Tools
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
 * @version    3.x Last Update: 2019-03-08
 * @filesource /lib/controller/module/phreebooks/tools.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'processPhreeBooks', 'function');

class phreebooksTools
{
    public $moduleID = 'phreebooks';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $this->dirUploads = 'data/phreebooks/uploads';
    }

    public function jrnlData(&$layout=[])
    {
        global $io;
        $total_v= $total_c= 0;
        $output = [];
//      $code   = clean('code', 'text', 'get'); // not used yet // 6_12 : dashbaord summary_6_12
        $range  = clean('range','cmd',  'get');
        $fqdn   = "\\bizuno\\summary_6_12";
        bizAutoLoad(BIZUNO_LIB.'controller/module/phreebooks/dashboards/summary_6_12/summary_6_12.php', $fqdn);
        $dash   = new $fqdn();
        $data   = $dash->dataSales($range);
        $raw[]  = [jslang('Date'), jsLang('purchases'), jsLang('sales')];
        foreach ($data as $date => $values) {
            $total_v += $values['v'];
            $total_c += $values['c'];
            $raw[]    = [viewFormat($date, 'date'), viewFormat($values['v'],'currency'), viewFormat($values['c'],'currency')];
        }
        $raw[] = [jslang('total'), viewFormat($total_v,'currency'), viewFormat($total_c,'currency')];
        if (sizeof($raw) < 2) { return msgAdd('There are no sales this period!'); }
        foreach ($raw as $row) { $output[] = '"'.implode('","', $row).'"'; }
        $io->download('data', implode("\n", $output), "JournalData-".date('Y-m-d').".csv");
    }

    /**
     * This function adds a fiscal year to the books, it defaults to a 12 period year starting on the next available date
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function fyAdd(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'admin', 3)) { return; }
        $maxFY    = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MAX(fiscal_year)",'', false);
        $maxPeriod= dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MAX(period)",     '', false);
        $maxDate  = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", "MAX(end_date)",   '', false);
        $nextDate = localeCalculateDate($maxDate, $day_offset=1);
        $maxFY++;
        $maxPeriod++;
        setNewFiscalYear($maxFY, $maxPeriod, $nextDate);
        $fy_max = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", ['MAX(fiscal_year) AS fiscal_year', 'MAX(period) AS period'], "", false);
        setModuleCache('phreebooks', 'fy', 'fy_max',       $fy_max['fiscal_year']);
        setModuleCache('phreebooks', 'fy', 'fy_period_max',$fy_max['period']);
        $this->setChartHistory($maxPeriod, $fy_max['period']);
        periodAutoUpdate(false);
        msgLog(lang('phreebooks_fiscal_year')." - ".lang('add').": $maxFY");
        $layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>"location.reload();"]]);
    }

    /**
     * Updates the journal_history database table from the specified firstPeriod to maxPeriod
     * @param integer $firstPeriod - First period to create the history record
     * @param integer $maxPeriod - highest period to load data
     */
    private function setChartHistory($firstPeriod, $maxPeriod)
    {
        $re_acct = getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[44];
        $acct_string = $this->getGLtoClose(); // select list of accounts that need to be closed, adjusted
        $records = $carryOver = [];
        $lastPriorPeriod = $firstPeriod - 1;
        foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $glAccount) {
            if (isset($glAccount['heading']) && $glAccount['heading']) { continue; }
            if (!in_array($glAccount['id'], $acct_string)) { $carryOver[] = $glAccount['id']; }
            for ($i = $firstPeriod; $i <= $maxPeriod; $i++) {
                $records[] = "('{$glAccount['id']}', '{$glAccount['type']}', '$i', NOW())";
            }
        }
        if (sizeof($records) > 0) {
            dbGetResult("INSERT INTO ".BIZUNO_DB_PREFIX."journal_history (gl_account, gl_type, period, last_update) VALUES ".implode(",\n",$records));
        }
        foreach ($carryOver as $glAcct) { // get carry over gl account beginning balances and fill new FY
            $bb = dbGetValue(BIZUNO_DB_PREFIX."journal_history", "beginning_balance+debit_amount-credit_amount", "gl_account='$glAcct' AND period=$lastPriorPeriod", false);
            dbWrite(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance'=>$bb], 'update', "gl_account='$glAcct' AND period>=$firstPeriod");
        }
        $closedGL = implode("','",$acct_string);
        $re = dbGetValue(BIZUNO_DB_PREFIX."journal_history", "SUM(beginning_balance+debit_amount-credit_amount) AS bb", "gl_account IN ('$closedGL') AND period=$lastPriorPeriod", false);
        dbWrite(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance'=>$re], 'update', "gl_account='$re_acct' AND period>=$firstPeriod");
    }

    /**
     * This function saves the updated fiscal calendar dates.
     * NOTE: The dates cannot be changed unless there are no journal entries in the period being altered.
     */
    public function fySave()
    {
        if (!validateSecurity('bizuno', 'admin', 3)) { return; }
        $pStart = $_POST['pStart'];
        $pEnd   = $_POST['pEnd'];
        foreach ($pStart as $period => $value) {
            $dateStart= clean($pStart[$period],'date');
            $dateEnd  = clean($pEnd[$period],  'date');
            if ($dateStart && $dateEnd) {
                dbWrite(BIZUNO_DB_PREFIX."journal_periods", ['start_date'=>$dateStart, 'end_date'=>$dateEnd, 'last_update'=>date('Y-m-d')], 'update', "period='$period'");
            }
        }
        msgLog(lang('phreebooks_fiscal_year')." - ".lang('edit'));
        msgAdd(lang('msg_settings_saved'), 'success');
    }

    /**
     * Fiscal year close structure to solicit user input to get year to close
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function fyCloseValidate(&$layout=[])
    {
        $icnFyGo = ['attr'=>['type'=>'button', 'value'=>$this->lang['fy_del_btn_go']],
            'events'=>  ['onClick'=>"jq('#tabAdmin').tabs('add',{title:'Close FY',href:'".BIZUNO_AJAX."&p=phreebooks/tools/fyCloseHome'}); bizWindowClose('winFyClose');"]];
        $icnCancel = ['attr'=>['type'=>'button', 'value'=>$this->lang['fy_del_btn_cancel']],
            'events'=>  ['onClick'=>"bizWindowClose('winFyClose');"]];
        $html  = '<p>'.$this->lang['fy_del_desc'] .'</p><div style="float:right">'.html5('', $icnFyGo).'</div><div>'.html5('', $icnCancel).'</div>';
        $data = ['type'=>'popup','title'=>$this->lang['fy_del_title'],'attr'=>['id'=>'winFyClose'],
            'divs' => ['body'=>['order'=>50,'type'=>'html','html'=>$html]]];
        $layout= array_replace_recursive($layout, $data);
    }

    /**
     * Sets the main structure for closing/deleting Fiscal Years, The tab for PhreeBooks settings is also included
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function fyCloseHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $title = getModuleCache('phreebooks', 'properties', 'title');
        $fy = dbGetValue(BIZUNO_DB_PREFIX."journal_periods", 'fiscal_year', '', false);
        $this->lang['fy_del_instr'] = sprintf($this->lang['fy_del_instr'], $fy);
        $layout = array_replace_recursive($layout, ['type'=>'divHTML',
            'divs' => ['fyClose'=>['order'=>10,'type'=>'divs','attr'=>['id'=>"divCloseFY"],'divs'=>[
                'tbClose'=> ['order'=>10,'type'=>'toolbar','key' =>'tbFyClose'],
                'head'   => ['order'=>20,'type'=>'html',   'html'=>"<p>".$this->lang['fy_del_instr']."</p>"],
                'body'   => ['order'=>50,'type'=>'tabs',   'key' =>'tabFyClose'],
            ]]],
            'toolbars'=> ['tbFyClose' =>['icons'=>['start'=>['order'=>10,'title'=>lang('Start'),'icon'=>'next','type'=>'menu','events'=>['onClick'=>"divSubmit('phreebooks/tools/fyClose', 'divCloseFY');"]]]]],
            'tabs'    => ['tabFyClose'=>['attr'=>['tabPosition'=>'left', 'headerWidth'=>200]],'divs'=>[
                'phreebooks' => ['order'=>50,'label'=>$title,'type'=>'html','html'=>$this->getViewFyClose()],
            ]],
            'lang'    => $this->lang]);
    }

    private function getViewFyClose()
    {
        $html = '<h2><u>What will happen when a Fiscal Year is Closed</u></h2>
<p>The following is a summary of the tasks performed while closing a fiscal year. The fiscal year being closed is indicated above.</p>
<h3>Pre-flight Check</h3>
<p>All journal entries will be tested to make sure they are in a completed state. You have an option to skip this test and remove them unconditionally by checking the box below.
If any journal entries are not in a closed state, this process will terminate. There may be other modules that will terminate the close process, the conditions for other modules
is described in the tab of the module.</p>
<b>Close Process</b><br />
<p>The close process will remove all general journal records for the closing fiscal year. Fiscal calendar periods that are vacated during this process will be removed and
the fiscal calendar will be re-sequenced starting with period 1 being the first period of the first remaining fiscal year.</p>
The following is a summary of the PhreeBooks module closing task list:
<ul>
<li>Delete all journal entries for the closing fiscal year, tables journal_main and journal_item and associated attachments</li>
<li>Delete table journal_history records for the closing fiscal year</li>
<li>Clean up COGS owed table for closing fiscal year</li>
<li>Clean up journal_cogs_usage table for closing fiscal year</li>
<li>Delete journal_periods for fiscal year</li>
<li>Re-sequence journal periods in journal_history table</li>
<li>Delete all gl chart of accounts if inactive no journal entries exits against the account</li>
<li>Delete tax_rates that have end date within the closing fiscal year</li>
<li>Delete bank reconciliation records within the range of closed fiscal year, re-sequence periods periods</li>
</ul>
<h3>Post-close Clean-up</h3><br />
<p>Following the journal deletion and other PhreeBooks module close tasks discussed above, each module will clean orphaned table records. See the instructions
within each module tab for details on what is performed.</p>
<p>The PhreeBooks post close process will be to re-run the journal tools to validate the journal balance and history table are in sync.
Other tools are also run to removed orphaned transactions, attachments and other general maintenance activities.
Most of these are available in the Journal Tools tab in the PhreeBooks module settings.</p>';
        $html .= "<p>"."To prevent the pre-flight test from halting the close process, check the box below."."</p>";
        $html .= html5('phreebooks_skip', ['label'=>'Do not perform the pre-flight check, I understand that this may affect my financial statements and inventory balances', 'position'=>'after','attr'=>['type'=>'checkbox','value'=>'1']]);
    }

    /**
     * Adds to the cron cache for all PhreeBooks module tasks associated with closing a Fiscal Year
     *
     * Journal entries are kept but the period is reduced by the number of accounting periods.
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function fyClose(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $fyInfo    = getPeriodInfo(1);
        $minPeriod = $fyInfo['period_min'];
        $maxPeriod = $fyInfo['period_max'];
//      $firstDate = dbGetValue(BIZUNO_DB_PREFIX.'journal_periods', 'start_date', "period=$minPeriod");
        $lastDate  = dbGetValue(BIZUNO_DB_PREFIX.'journal_periods', 'end_date',   "period=$maxPeriod");
        // make sure the FY selected is not the current or future FY
        if ($fyInfo['fiscal_year'] == getModuleCache('phreebooks', 'fy', 'fiscal_year')) { return; }
        // generate three parts, preflight check [taskPre], action (phreebooks first) [taskClose], post action [taskPost] validations (journal validation, inventory tools, etc)
        $cron = ['fy'=>$fyInfo['fiscal_year'], 'fyStartDate'=>$fyInfo['period_start'], 'fyEndDate'=>$lastDate,
            'periodStart'=>$minPeriod, 'periodEnd'=>$maxPeriod, 'taskPre'=>[], 'taskClose'=>[], 'taskPost'=>[]];
        $cron['taskPre'][]   = ['mID'=>$this->moduleID, 'method'=>'fYCloseStart'];
        $cron['taskClose'][] = ['mID'=>$this->moduleID, 'method'=>'fYCloseJournal']; // ,'page'=>'tools', 'method'=>'fyCloseNext']; // assumed page=tools, method=fyCloseNext, settings=[]
        $cron['taskPost'][]  = ['mID'=>$this->moduleID, 'method'=>'fYClosePost'];
        $msg = "Log file for closing fiscal year {$fyInfo['fiscal_year']}. Bizuno release ".MODULE_BIZUNO_VERSION.", generated ".date('Y-m-d H:i:s');
        setUserCache('cron', 'fyClose', $cron);
        $io = new \bizuno\io();
        $io->fileWrite("$msg\n\n", "backups/fy_{$cron['fy']}_close_log.txt", false, false, true);
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval', 'actionData'=>"cronInit('fyClose', 'phreebooks/tools/fyCloseNext');"]]);
//      msgDebugWrite('fyClose.txt', false); // start a new trace file [false] erases the old file
    }

    /**
     * Executes the next step in closing the fiscal year for the PhreeBooks module
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function fyCloseNext(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        set_time_limit(1800); // 30 minutes
        $finished  = false;
        $cron = getUserCache('cron', 'fyClose');
        $numTasks  = sizeof($cron['taskPre']);
        $numTasks += sizeof($cron['taskClose']);
        $numTasks += sizeof($cron['taskPost']);
        if     (sizeof($cron['taskPre']))  { $taskID = 'taskPre'; }
        elseif (sizeof($cron['taskClose'])){ $taskID = 'taskClose'; }
        elseif (sizeof($cron['taskPost'])) { $taskID = 'taskPost'; }
        // load the module/method and execute method
        $task = array_shift($cron[$taskID]);
        if (!isset($task['settings'])) { $task['settings'] = []; }
        if (!isset($task['page']))     { $task['page']     = 'tools'; }
        $admin = $task['mID'].ucfirst($task['page']);
        $method= isset($task['method']) ? $task['method'] : 'fyCloseNext';
        $fqcn = "\\bizuno\\$admin";
        bizAutoLoad(getModuleCache($task['mID'], 'properties', 'path')."/{$task['page']}.php", $fqcn);
        msgDebug("\n********************* Starting taskID $taskID, admin $admin and method $method and task details: ".print_r($task, true));
        unset($cron['msg']); // clear the message queue
        $thisTask = new $fqcn();
        $msg = $thisTask->$method($task['settings'], $cron);

        if (!isset($cron['cnt'])) {
            $cron['total'] = $numTasks;
            $cron['cnt']   = 1;
            $cron['curMod']= $task['mID'];
            msgDebug("\nFirst pass through, session[cron][fyClose] = ".print_r($cron, true));
        } elseif ($cron['curMod'] <> $task['mID']) { // new module bump the counter and set curMod
            $cron['cnt']++;
            $cron['curMod']= $task['mID'];
        }
        if (!sizeof($cron['taskPost'])) { $finished = true; }
        if ($finished) {
            msgDebug("\n************** Finsihed all tasks, sending final message and changing heading.");
            msgLog("PhreeBooks Tools - Close Fiscal Year {$cron['fy']}");
            $msg  = "The fiscal year close is complete!<br />The log file can be found in the Tools -> Business Backup file list.<br />Please sign off and back in to refresh your cache.<br />";
            $cron['msg'][] = $msg;
            $msg .= '<p style="text-align:center">'.html5('', ['attr'=>['type'=>'button', 'value'=>lang('logout')], 'events'=>['onClick'=>"jsonAction('bizuno/portal/logout');"]])."</p>";
            $data = ['content'=>['percent'=>100,'msg'=>$msg,'baseID'=>'fyClose','urlID'=>'phreebooks/tools/fyCloseNext']];
        } else { // return to update progress bar and start next step
            $percent = min(99, floor(100*$cron['cnt']/$cron['total'])); // don't allow 100% of ajax won't trigger next step
            $msg = "Processing step {$cron['cnt']} of {$cron['total']}<br />".$msg;
            $cron['msg'][] = "Console Message for method $method: $msg";
            $data = ['content'=>['percent'=>$percent,'msg'=>$msg,'baseID'=>'fyClose','urlID'=>'phreebooks/tools/fyCloseNext']];
        }
        $io = new \bizuno\io();
        $io->fileWrite("\n".implode("\n", $cron['msg']), "backups/fy_{$cron['fy']}_close_log.txt", false, true, false);
        if ($finished) { clearUserCache('cron', 'fyClose'); } else { setUserCache('cron', 'fyClose', $cron); }
//      msgDebugWrite('fyClose.txt', true);
        $layout = array_replace_recursive($layout, $data);
    }

    public function fYCloseStart($settings=[], &$cron=[])
    {
        return "Starting to clean fiscal year, now cleaning journals. This may take a while!";
    }

    /**
     * Executes fiscal year close through a specified period
     * @return string - HTML user status message
     */
    public function fYCloseJournal($settings=[], &$cron=[])
    {
        if (!$cron['fyEndDate']) { return; }
        $cron['msg'][] = "Looking at journal tables to prune for fiscal year ending date: {$cron['fyEndDate']} and ending period = {$cron['periodEnd']}";
        dbTransactionStart();
        $this->fyCloseDbAction('journal_cogs_owed',"post_date<='{$cron['fyEndDate']}'", $cron);
        $this->fyCloseDbAction('journal_main',     "post_date<='{$cron['fyEndDate']}'", $cron);
        $this->fyCloseDbAction('journal_item',     "post_date<='{$cron['fyEndDate']}'", $cron);
        $this->fyCloseDbAction('journal_history',  "period   <= {$cron['periodEnd']}",  $cron);
        $this->fyCloseDbAction('journal_periods',  "period   <= {$cron['periodEnd']}",  $cron);
        $this->fyCloseDbAction('tax_rates',        "end_date <='{$cron['fyEndDate']}'", $cron);
        // renumber periods
        dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_main    SET period = period - {$cron['periodEnd']}");
        dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_periods SET period = period - {$cron['periodEnd']}");
        dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_history SET period = period - {$cron['periodEnd']}");
        dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_item    SET reconciled = reconciled - {$cron['periodEnd']} WHERE reconciled > 0");
        dbTransactionCommit();
        $props = getPeriodInfo(getModuleCache('phreebooks', 'fy', 'period') - $cron['periodEnd']);
        setModuleCache('phreebooks', 'fy', false, $props);
        array_unshift($cron['taskClose'], ['mID'=>$this->moduleID, 'method'=>'fyCloseHistory', 'settings'=>['cnt'=>1]]);
        return "Finished closing journal.";
    }

    /**
     * Generically executes a delete SQL based on specified criteria
     * @param string $table - database table name
     * @param string $crit - SQL criteria to append to the SQL
     */
    private function fyCloseDbAction($table, $crit, &$cron)
    {
        $cnt = dbGetValue(BIZUNO_DB_PREFIX.$table, 'COUNT(*) AS cnt', $crit, false);
        $cron['msg'][] = "Read $cnt records to delete from table: $table";
        $cron['msg'][] = "Executing SQL: DELETE FROM ".BIZUNO_DB_PREFIX."$table WHERE $crit";
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."$table WHERE $crit");
    }

    /**
     * Closes the fiscal year in the database
     * @param array $settings - operating constraints and settings
     * @return string - HTML status to user
     */
    public function fyCloseHistory($settings=[], &$cron=[])
    {
        $blockSize = 1000;
        $finished  = false;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        if (!isset($cron['fyPBHistCnt'])) {
            $count = dbGetValue(BIZUNO_DB_PREFIX."journal_cogs_usage", 'count(*) AS cnt', '', false);
            $cron['fyPBHistCnt'] = ceil($count/$blockSize);
        }
        $totalBlock = $cron['fyPBHistCnt'];
        $thisBlock  = ceil($settings['cnt']/$blockSize);
        $result = dbGetMulti(BIZUNO_DB_PREFIX.'journal_cogs_usage', '', '', '*', "{$settings['cnt']}, $blockSize");
        if (!sizeof($result)) { $finished = true; }
        else                  { $settings['cnt'] += $blockSize;}
        $toClose = [];
        foreach ($result as $row) {
            $exists = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'id', "id={$row['journal_main_id']}");
            if (!$exists) { $toClose[] = $row['journal_main_id']; }
        }
        if (sizeof($toClose)) {
            msgDebug("\nDeleting journal_cogs_usage ids = ".implode(',', $toClose));
            dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_cogs_owed WHERE journal_main_id IN (".implode(',', $toClose).")");
        }
//if ($thisBlock > 5) { $finished = true; }
        if (!$finished) { // more to process, re-queue
            array_unshift($cron['taskClose'], ['mID'=>$this->moduleID, 'method'=>'fyCloseHistory', 'settings'=>['cnt'=>$settings['cnt']]]);
            return "Finished processing block $thisBlock of $totalBlock for module $this->moduleID: deleted ".sizeof($toClose)." records";
        }
        return "Finished processing COGS Usage history table";
    }

    /**
     * THIS ROUTINE NEEDS TO BE WRITTEN
     * @return string - HTML status message
     */
    public function fYClosePost($settings=[], &$cron=[])
    {
        // run the journal validation tools
        // need to call glRepair($layout=[]) from browser with auto close window.
        return "Running glRepair tool to validate journal values.";
    }

    /**
     * This method reposts a single journal entry
     * @param integer $rID - Record id from the table journal_main
     * @return boolean - true on success, false (with msg) on error.
     */
    public function glRepost($rID=0)
    {
        ini_set("max_execution_time", 300); // 5 minutes per post
        if (!validateSecurity('phreebooks', 'j2_mgr', 3)) { return; }
        bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journal.php", 'journal');
        dbTransactionStart();
        $repost = new journal($rID, false);
        if ($repost->Post()) {
            dbTransactionCommit();
            return true;
        }
    }

    /**
     * Method to initiate reposting of journal records for a specified date range
     * @param array $layout - structure of view
     * @return array - modified $layout
     */
    public function glRepostBulk(&$layout=[])
    {
        $jIDs     = array_keys(clean('jID', 'array', 'post'));
        $dateStart= clean('repost_begin','date', 'post');
        $dateEnd  = clean('repost_end',  'date', 'post');
        if (sizeof($jIDs) == 0) { return msgAdd($this->lang['err_pb_repost_empty']); }
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "journal_id IN (".implode(',', $jIDs).") AND post_date>='$dateStart' AND post_date<='$dateEnd'");
        if (sizeof($result) == 0) { return msgAdd(lang('no_results')); }
        foreach ($result as $row) { $rows[] = $row['id']; }
        msgDebug("\nRows to process = ".print_r($rows, true));
        setUserCache('cron', 'glRepost', ['cnt'=>0, 'total'=>sizeof($rows), 'rows'=>$rows]);
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval', 'actionData'=>"cronInit('glRepost', 'phreebooks/tools/glRepostBulkNext');"]]);
    }

    /**
     * Ajax continuation of glRepostBulk
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function glRepostBulkNext(&$layout=[])
    {
        $cron = getUserCache('cron', 'glRepost');
        $id = array_shift($cron['rows']);
        if ($id) { $this->glRepost($id); }
        $cron['cnt']++;
        if (sizeof($cron['rows']) == 0) {
            msgLog("PhreeBooks Tools (Repost Journals) - ({$cron['total']} records)");
            $data = ['content'=>['percent'=>100,'msg'=>"Processed {$cron['total']} Journal Entries",'baseID'=>'glRepost','urlID'=>'phreebooks/tools/glRepostBulkNext']];
            $allCron = getUserCache('cron');
            unset($allCron['glRepost']);
            setUserCache('cron', false, $allCron);
        } else { // return to update progress bar and start next step
            $percent = floor(100*$cron['cnt']/$cron['total']);
            setUserCache('cron', 'glRepost', $cron);
            $data = ['content'=>['percent'=>$percent,'msg'=>"Completed journal record id = $id",'baseID'=>'glRepost','urlID'=>'phreebooks/tools/glRepostBulkNext']];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Tool to repair the journal_history table with journal_main actuals
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function glRepair(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'admin', 3)) { return; }
        $tmp = dbGetMulti(BIZUNO_DB_PREFIX.'journal_periods', '', 'period');
        foreach ($tmp as $row) { $rows['p'.$row['period']] = ['period'=>$row['period'], 'fy'=>$row['fiscal_year']]; }
        setUserCache('cron', 'repairGL', ['cnt'=>0, 'total'=>sizeof($rows), 'rows'=>$rows]);
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"cronInit('repairGL', 'phreebooks/tools/glRepairNext');"]]);
    }

    /**
     * Ajax continuation of glRepair
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function glRepairNext(&$layout=[])
    {
        $cron = getUserCache('cron', 'repairGL');
        $fatalError = false;
        $nextPeriod= array_shift($cron['rows']);
        $period    = $nextPeriod['period'];
        $curPerFY  = $nextPeriod['fy'];
        $re_acct = getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[44];
        if (isset($cron['rows']['p'.($period+1)]['fy'])) {
            $nextPerFY = $cron['rows']['p'.($period+1)]['fy'];
        } else {
            $nextPerFY = false;
        }
        msgDebug("\nWorking with period $period and fy $curPerFY and next period fy $nextPerFY");
        $tolerance = .0001;
        // get journal_history for given period
        $curHistory = dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "period=$period");
        msgDebug("\nFound ".sizeof($curHistory)." history records (GL Accounts) in period $period");
        // set beginning balance values for current period, test for zero
        $trialBalance = 0;
        foreach ($curHistory as $row) {
            $nextBB = $row['beginning_balance'] + $row['debit_amount'] - $row['credit_amount'];
            $history[$row['gl_account']] = ['begbal'=>$row['beginning_balance'], 'debit'=>$row['debit_amount'], 'credit'=>$row['credit_amount'], 'nextBB'=>$nextBB];
            $trialBalance += $row['beginning_balance']; // zero test gathering
        }
        if (abs($trialBalance) > $tolerance) {
            // sometimes this will happen from results in FY roll-over where prior year agregate exceeds tolerance, only show message if amount is large
            // enough to warrant concern which means there is a bigger problem
            if (abs($trialBalance) > 100*$tolerance) {
                msgAdd("Trial balance for period $period is out of balance by $trialBalance. Retained earnings account $re_acct will be adjusted and testing will continue.");
            }
            // Make the correction anyway as when FY's are closed this will eventually become the newe actual
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_history SET beginning_balance = beginning_balance - $trialBalance WHERE period=$period AND gl_account='$re_acct'");
        }
        // get all from journal_item for given period
        $stmt = dbGetResult("SELECT m.id, m.journal_id, i.gl_account, i.debit_amount, i.credit_amount
            FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id WHERE period=$period");
        $glPosts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        msgDebug("\nFound ".sizeof($glPosts)." posts in period $period");
        // for each ref_id, make sure each main record is in balance, add to beginning_balance, flag ID's not in balance, these need to be fixed manually
        $mID = 0;
        $mains = [];
        $rID = ['debit'=>0, 'credit'=>0];
        foreach ($glPosts as $row) {
            if (in_array($row['journal_id'], [3,4,9,10])) { continue; }
            if ($row['id'] <> $mID) { // if new main id, test for balance of previous id and reset values
                if (abs($rID['debit']-$rID['credit']) > $tolerance) {
                    if (!$this->glRepairEntry($mID)) {
                        $fatalError = true;
                        msgAdd("Journal record $mID is out of balance, this results from a database corruption issue and needs to be corrected manually in the db! The repair has been halted");
                    }
                }
                $mID = $row['id'];
                $rID = ['debit'=>0, 'credit'=>0];
            }
            // add to running total for this main record
            $rID['debit']  += $row['debit_amount'];
            $rID['credit'] += $row['credit_amount'];
            // add debits and credits to running total for period
            if (!isset($mains[$row['gl_account']])) { $mains[$row['gl_account']] = ['debit'=>0, 'credit'=>0]; }
            $mains[$row['gl_account']]['debit']  += $row['debit_amount'];
            $mains[$row['gl_account']]['credit'] += $row['credit_amount'];
        }
        // get gl accounts that close at end of FY
        $closedGL = $this->getGLtoClose();
        // get journal_history beginning balances for next period
        $nextHistory= dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "period=".($period+1));
        // test ending balance with next period beginning balance (except FY boundaries), correct next beginning balance here if out of whack
        $retainedEarnings = 0;
        $updateEndFY = false;
        $historyRE = '';
        $endFY = $curPerFY <> $nextPerFY ? true : false;
        if ($nextPerFY) { foreach ($nextHistory as $row) {
            $fixBB = false;
            if (!isset($mains[$row['gl_account']])) { $mains[$row['gl_account']] = ['debit'=>0, 'credit'=>0]; }
            $actualBal = $history[$row['gl_account']]['begbal'] + $mains[$row['gl_account']]['debit'] - $mains[$row['gl_account']]['credit'];
            if ($row['gl_account'] == $re_acct) { $historyRE = $row['beginning_balance']; }
            msgDebug("\nPeriod $period, glAcct={$row['gl_account']}, history bb={$history[$row['gl_account']]['begbal']}, debit={$history[$row['gl_account']]['debit']}, credit={$history[$row['gl_account']]['credit']}, nextbb={$history[$row['gl_account']]['nextBB']} - historyNextBB={$row['beginning_balance']} - mains: debit={$mains[$row['gl_account']]['debit']}, credit={$mains[$row['gl_account']]['credit']}, ");
            // check posted debits and credits to history
            if (abs($history[$row['gl_account']]['debit'] - $mains[$row['gl_account']]['debit']) > $tolerance) {
                msgAdd("Historical debit amount for period ".($period+1)." ({$history[$row['gl_account']]['debit']}), gl_account {$row['gl_account']} doesn't match the journal postings for period $period ({$mains[$row['gl_account']]['debit']}). The balance will be repaired to actuals.");
                $fixBB = true;
            }
            if (abs($history[$row['gl_account']]['credit'] - $mains[$row['gl_account']]['credit']) > $tolerance) {
                msgAdd("Historical credit amount for period ".($period+1)." ({$history[$row['gl_account']]['debit']}), gl_account {$row['gl_account']} doesn't match the journal postings for period $period ({$mains[$row['gl_account']]['debit']}). The balance will be repaired to actuals.");
                $fixBB = true;
            }
            // Check next beginning balance
            if ($endFY && in_array($row['gl_account'], $closedGL)) {
                $retainedEarnings += $actualBal;
            } elseif ($re_acct <> $row['gl_account']) { // check next period bb with history table calculation, except RE account which is the collection account
                if (abs($history[$row['gl_account']]['nextBB'] - $row['beginning_balance']) > 10*$tolerance) { // need 10x to allow corrections to rounding carry-over
                    msgAdd("Beginning balance in history table for period ".($period+1)." (gl: {$row['gl_account']}) doesn't match the journal postings for period $period. The beginning balance will be repaired to actuals.");
                    $fixBB = true;
                }
            }
            if ($fixBB) {
                // repair debits and credit in current period to actuals
                msgDebug("\nWriting to journal_history gl_account={$row['gl_account']} and period=$period debit amount: {$mains[$row['gl_account']]['debit']} and credit {$mains[$row['gl_account']]['credit']}");
                dbWrite(BIZUNO_DB_PREFIX.'journal_history', ['debit_amount'=>$mains[$row['gl_account']]['debit'], 'credit_amount'=>$mains[$row['gl_account']]['credit']], 'update', "period=$period AND gl_account='{$row['gl_account']}'");
                // repair beginning balance for next period
                msgDebug("\nWriting to journal_history gl_account={$row['gl_account']} and period=".($period+1)." beginning balance amount: $actualBal");
                dbWrite(BIZUNO_DB_PREFIX.'journal_history', ['beginning_balance'=>$actualBal], 'update', "period=".($period+1)." AND gl_account='{$row['gl_account']}'");
                $updateEndFY = true;
            }
        } }
        if ($endFY) {
            $acct_string = implode("','", $closedGL);
            msgDebug("\nUpdating end of FY balances for period $period, gl account=$re_acct and history was $historyRE and will be set to = $retainedEarnings");
            if ($nextPerFY) { dbWrite(BIZUNO_DB_PREFIX."journal_history", ['beginning_balance'=>0], 'update', "period=".($period+1)." AND gl_account IN ('$acct_string')"); }
            if ($nextPerFY) { dbWrite(BIZUNO_DB_PREFIX.'journal_history', ['beginning_balance'=>$retainedEarnings], 'update', "period=".($period+1)." AND gl_account='$re_acct'"); }
        }
        $cron['cnt']++;
        if ($fatalError || sizeof($cron['rows']) == 0) {
            msgLog("PhreeBooks Tools (repairGL) - ({$cron['total']} records)");
            $data = ['content'=>['percent'=>100,'msg'=>"Processed {$cron['total']} Periods",'baseID'=>'repairGL','urlID'=>'phreebooks/tools/glRepairNext']];
            $allCron = getUserCache('cron');
            unset($allCron['repairGL']);
            setUserCache('cron', false, $allCron);
        } else { // return to update progress bar and start next step
            $percent = floor(100*$cron['cnt']/$cron['total']);
            setUserCache('cron', 'repairGL', $cron);
            $data = ['content'=>['percent'=>$percent,'msg'=>"Completed period $period",'baseID'=>'repairGL','urlID'=>'phreebooks/tools/glRepairNext']];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Tests and repairs a single journal entry that is causing balance errors fond during tests
     * @param integer $mID - database journal_main record id
     * @return boolean true
     */
    private function glRepairEntry($mID)
    {
        $precision = getModuleCache('phreebooks', 'currency', 'iso')[getUserCache('profile', 'currency', false, 'USD')]['dec_len'];
        $tolerance = 50 / pow(10, $precision); // i.e. 50 cent in USD
        $rows = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$mID");
        $diff = $ttlRow = $ttlDbt = $ttlCrt = 0;
        foreach ($rows as $row) {
            $diff += $row['debit_amount'] - $row['credit_amount'];
            if ($row['gl_type'] == 'ttl') {
                $ttlRow = $row['id'];
                $ttlDbt = $row['debit_amount'];
                $ttlCrt = $row['credit_amount'];
            }
        }
        if (abs($diff) > $tolerance) { return false; }
        $adjDbt = $ttlDbt ? ($ttlDbt - $diff) : 0;
        $adjCrt = $ttlCrt ? ($ttlCrt + $diff) : 0;
        msgAdd("Corrected main: $mID item: record $ttlRow, debit: $ttlDbt and credit: $ttlCrt diff: $diff, adjustment debit: $adjDbt, credit: $adjCrt");
        dbWrite(BIZUNO_DB_PREFIX.'journal_item', ['debit_amount'=>$adjDbt, 'credit_amount'=>$adjCrt], 'update', "id=$ttlRow");
        return true;
    }

    /**
     * Retrieves the list of GL accounts to close
     * @return array - gl accounts that are closed at the end of a Fiscal Year
     */
    private function getGLtoClose()
    {
        $acct_list = [];
        foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $row) {
            if (in_array($row['type'], [30,32,34,42,44])) { $acct_list[] = $row['id']; }
        }
        return $acct_list;
    }

    /**
     * Purge all journal entries and associated tables. THIS IS A COMPLETE WIPE OF ANY JOURNAL RELATED DATABASE TABLES. Contacts and Inventory are unaffected.
     * @return null - user message will be created with status
     */
    public function glPurge()
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $data = clean('data', 'text', 'get');
        if ('purge' == $data) {
            dbGetResult("TRUNCATE TABLE ".BIZUNO_DB_PREFIX."inventory_history");
            dbGetResult("TRUNCATE TABLE ".BIZUNO_DB_PREFIX."journal_cogs_owed");
            dbGetResult("TRUNCATE TABLE ".BIZUNO_DB_PREFIX."journal_cogs_usage");
            dbGetResult("TRUNCATE TABLE ".BIZUNO_DB_PREFIX."journal_item");
            dbGetResult("TRUNCATE TABLE ".BIZUNO_DB_PREFIX."journal_main");
            if (getModuleCache('extShipping', 'properties', 'status')) {
                dbGetResult("TRUNCATE TABLE ".BIZUNO_DB_PREFIX."extShipping");
            }
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_history SET beginning_balance=0, debit_amount=0, credit_amount=0, budget=0, stmt_balance=0, last_update=''");
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."inventory SET qty_stock=0, qty_po=0, qty_so=0");
            $io = new \bizuno\io(); // purge the files
            $io->folderDelete("data/phreebooks/uploads");
            msgAdd($this->lang['phreebooks_purge_success'], 'success');
            msgLog($this->lang['phreebooks_purge_success']);
        } else {
            msgAdd("You must type the word 'purge' in the field and press the purge button!");
        }
    }

    /**
     * Prunes the COGS owed table by reposting purchase and then sales, done through ajax steps
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function pruneCogs(&$layout=[])
    {
        $result = dbGetMulti(BIZUNO_DB_PREFIX."journal_cogs_owed");
        if (sizeof($result) == 0) { return msgAdd("No rows to process!"); }
        foreach ($result as $row) { $rows[$row['sku']] = $row['journal_main_id']; }
        msgDebug("\nRows to process = ".print_r($rows, true));
        setUserCache('cron', 'pruneCogs', ['cnt'=>0, 'total'=>sizeof($rows), 'rows'=>array_keys($rows)]);
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval', 'actionData'=>"cronInit('pruneCogs', 'phreebooks/tools/pruneCogsNext');"]]);
    }

    /**
     * Controller for cogs pruning, manages a block to prune
     * @param type $layout
     */
    public function pruneCogsNext(&$layout=[])
    {
        $cron = getUserCache('cron', 'pruneCogs');
        $sku = array_shift($cron['rows']);
        // find the last inventory increase that included this SKU
        $stmt = dbGetResult("SELECT m.id FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
            WHERE m.journal_id IN (6,14,15,16) AND i.qty>0 AND i.sku='$sku' ORDER BY m.post_date DESC LIMIT 1");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result['id']) { $this->glRepost($result['id']); }
        $cron['cnt']++;
        if (sizeof($cron['rows']) == 0) {
            msgLog("PhreeBooks Tools (prune COGS owed) - ({$cron['total']} records)");
            $data = ['content'=>['percent'=>100,'msg'=>"Processed {$cron['total']} SKUs",'baseID'=>'pruneCogs','urlID'=>'phreebooks/tools/pruneCogsNext']];
            $allCron = getUserCache('cron');
            unset($allCron['pruneCogs']);
            setUserCache('cron', false, $allCron);
        } else { // return to update progress bar and start next step
            $percent = floor(100*$cron['cnt']/$cron['total']);
            setUserCache('cron', 'pruneCogs', $cron);
            $data = ['content'=>['percent'=>$percent,'msg'=>"Completed journal record id = {$result['id']}",'baseID'=>'pruneCogs','urlID'=>'phreebooks/tools/pruneCogsNext']];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * This method deletes all attachments prior to a specified date and clears the attach flag in the db
     * @return user status message
     */
    public function cleanAttach()
    {
        if (!$security = validateSecurity('bizuno', 'admin', 3)) { return; }
        $post_date = clean('data', 'date', 'get');
        if (!$post_date) { return msgAdd('Bad Date!'); }
        $io = new \bizuno\io();
        $files = $io->folderRead($this->dirUploads);
        msgDebug("\nFound total number of attachments = ".sizeof($files));
        $theList = [];
        foreach ($files as $fn) {
            $attr = date('Y-m-d', filemtime(BIZUNO_DATA."$this->dirUploads/$fn"));
            if ($attr < $post_date) {
                msgDebug("Deleting file: $fn modified on $attr");
                unlink(BIZUNO_DATA."$this->dirUploads/$fn");
                $fn = str_replace('rID_', '', $fn);
                $theList[] = substr($fn, 0, strpos($fn, '_'));
            }
        }
        if (sizeof($theList) > 0) {
            msgDebug("\nUpdating table phreebooks, removing attach for id = ".print_r($theList, true));
            dbWrite(BIZUNO_DB_PREFIX.'journal_main', ['attach'=>'0'], 'update', "id IN (".implode(',', $theList).")");
        }
        if (sizeof($theList) > 0) {
            msgAdd(sprintf($this->lang['msg_attach_clean_success'], sizeof($theList)), 'success');
        } else {
            msgAdd($this->lang['msg_attach_clean_empty'], 'caution');
        }
    }
}
