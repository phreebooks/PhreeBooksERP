<?php
/*
 * Tools for contacts module
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
 * @version    3.x Last Update: 2019-01-22
 * @filesource /lib/controller/module/contacts/tools.php
 */

namespace bizuno;

class contactsTools
{
    public $moduleID = 'contacts';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }

    /**
     * Closes all customer quotes (Journal 9) before the supplied date
     * @return success message with number of records closed
     */
    public function j9Close()
    {
        if (!$security = validateSecurity('phreebooks', 'j9_mgr', 3)) { return; }
        $def = localeCalculateDate(date('Y-m-d'), 0, -1);
        $date= clean('data', ['format'=>'date', 'default'=>$def], 'get');
        $cnt = dbWrite(BIZUNO_DB_PREFIX."journal_main", ['closed'=>'1'], 'update', "journal_id=9 AND post_date<'$date'");
        msgAdd(sprintf($this->lang['close_j9_success'], $cnt), 'success');
    }

    /**
     * Generates a pop up bar chart for monthly sales
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function chartSales(&$layout=[])
    {
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('err_bad_id')); }
        $type  = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'type', "id=$rID");
        $struc = $this->chartSalesData($rID, $type);
        $title = $type=='v' ? $this->lang['purchases_by_month'] : $this->lang['sales_by_month'];
        $iconExp= ['attr'=>['type'=>'button','value'=>'Download Data'],'events'=>['onClick'=>"jq('#frmContactsChart').submit();"]];
        $output= ['divID'=>"chartContactsChart",'type'=>'column','attr'=>['legend'=>'none','title'=>$title],
            'data'=>array_values($struc)];
        $action= BIZUNO_AJAX."&p=contacts/tools/chartSalesGo&rID=$rID&type=$type";
        $js    = "jq.cachedScript('".BIZUNO_URL."../apps/jquery-file-download.js?ver=".MODULE_BIZUNO_VERSION."');\n";
        $js   .= "ajaxDownload('frmContactsChart');\n";
        $js   .= "var dataContactsChart = ".json_encode($output).";\n";
        $js   .= "function funcContactsChart() { drawBizunoChart(dataContactsChart); };";
        $js   .= "google.charts.load('current', {'packages':['corechart']});\n";
        $js   .= "google.charts.setOnLoadCallback(funcContactsChart);\n";

        $html  = '<div style="width:100%" id="chartContactsChart"></div>';
        $html .= '<div style="text-align:right"><form id="frmContactsChart" action="'.$action.'">'.html5('', $iconExp).'</form></div>';
        $html .= htmlJS($js);
        $layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>$html]);
    }

    private function chartSalesData($rID, $type)
    {
        $dates= localeGetDates(localeCalculateDate(date('Y-m-d'), 0, 0, -1));
        $jIDs = $type=='v' ? '(6,7)' : '(12,13)';
        msgDebug("\nDates = ".print_r($dates, true));
          $sql = "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year, SUM(total_amount) AS total
            FROM ".BIZUNO_DB_PREFIX."journal_main WHERE contact_id_b=$rID and journal_id IN $jIDs AND post_date>='{$dates['ThisYear']}-{$dates['ThisMonth']}-01'
              GROUP BY year, month LIMIT 12";
        msgDebug("\nSQL = $sql");
        if (!$stmt = dbGetResult($sql)) { return msgAdd(lang('err_bad_sql')); }
        $result= $stmt->fetchAll(\PDO::FETCH_ASSOC);
        msgDebug("\nresult = ".print_r($result, true));
        $precision = getModuleCache('phreebooks', 'currency', 'iso')[getUserCache('profile', 'currency', false, 'USD')]['dec_len'];
        $struc[] = [lang('date'), lang('total')];
        for ($i = 0; $i < 12; $i++) { // since we have 12 months to work with we need 12 array entries
            $struc[$dates['ThisYear'].$dates['ThisMonth']] = [$dates['ThisYear'].'-'.$dates['ThisMonth'], 0];
            $dates['ThisMonth']++;
              if ($dates['ThisMonth'] == 13) {
                  $dates['ThisYear']++;
                  $dates['ThisMonth'] = 1;
              }
        }
        foreach ($result as $row) {
            if (isset($struc[$row['year'].$row['month']])) { $struc[$row['year'].$row['month']][1] = round($row['total'], $precision); }
          }
        return $struc;
    }

    public function chartSalesGo()
    {
        global $io;
        $rID   = clean('rID', 'integer','get');
        $type  = clean('type','char',   'get');
        $title = dbGetValue(BIZUNO_DB_PREFIX.'address_book', 'primary_name', "ref_id=$rID");
        $struc = $this->chartSalesData($rID, $type);
        $output= [];
        foreach ($struc as $row) { $output[] = implode(",", $row); }
        $io->download('data', implode("\n", $output), "Contact-Sales-$title.csv");
    }

    /**
     * Extends the PhreeBooks module close fiscal year function to handle contacts operations
     * @param array $layout - current working structure
     */
    public function fyCloseHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $html  = "<p>"."Closing the fiscal year for the contacts module consist of deleting contacts (all contact types) that are not referenced in the general journal during or before the fiscal year being closed. "
                . "For customers, only active records will be removed. For vendors, only inacitve records will be removed. "
                . "Address books entries for deleted contacts will be removed, contact log entries for ALL contacts will be removed. Expired stored credit cards for all periods will be removed."
                . "To prevent the these contact records from being removed, check the box below."."</p>";
        $html .= html5('contacts_keep', ['label' => 'Do not delete contact records that have no journal reference during or before this closing fiscal year', 'position'=>'after','attr'=>['type'=>'checkbox','value'=>'1']]);
        $layout['tabs']['tabFyClose']['divs'][$this->lang['title']] = ['order'=>50,'label'=>$this->lang['title'],'type'=>'html','html'=>$html];
    }

    /**
     * Hook to PhreeBooks Close FY method, adds tasks to the queue to execute AFTER PhreeBooks processes the journal
     * @param array $layout - current working structure
     */
    public function fyClose(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $skip = clean('contacts_keep', 'boolean', 'post');
        if ($skip) { return; } // user wants to keep all records, nothing to do here, move on
        $cron = getUserCache('cron', 'fyClose');
        $cron['taskPost'][] = ['mID'=>$this->moduleID, 'settings'=>['type'=>'c','cnt'=>1,'rID'=>0]];
        setUserCache('cron', 'fyClose', $cron);
    }

    /**
     * Executes the next step in fiscal year close for module contacts
     * @param array $settings - working state/status of close process
     * @return string - HTML message with status
     */
    public function fyCloseNext($settings=[], &$cron=[])
    {
        $blockSize = 25;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        if (!isset($cron[$this->moduleID]['total'])) {
            foreach (['c','v'] as $type) {
                $total = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'COUNT(*) AS cnt', "type='$type'", false);
                $cron[$this->moduleID]['total'][$type] = $total;
            }
        }
        $totalBlock= ceil($cron[$this->moduleID]['total'][$settings['type']] / $blockSize);
        $thisBlock = $settings['cnt'];
        $origType  = $settings['type'];
        $deleted   = $this->fyCloseStep($settings['cnt'], $settings['type'], $settings['rID'], $blockSize);
        if ($settings['type']) { // more to process, re-queue
            $settings['cnt']++;
            msgDebug("\nRequeuing contacts with rID = {$settings['rID']}");
            array_unshift($cron['taskPost'], ['mID'=>$this->moduleID, 'settings'=>['cnt'=>$settings['cnt'],'type'=>$settings['type'],'rID'=>$settings['rID']]]);
        } else { // we're done, run the sync attachments tool
            msgDebug("\nFinished contacts, checking attachments");
            $this->syncAttachments();
        }
        // Need to add these results to a log that can be downloaded from the backup folder.
        msgDebug("\nReturned from contacts step with type = $origType and rID = {$settings['rID']} and number of deleted records = $deleted");
        return "Finished processing block $thisBlock of $totalBlock for module $this->moduleID type $origType: deleted $deleted records";
    }

    /**
     * Deletes a block of contacts that meet the criteria from the user input
     * @param integer $cnt - current block counter start for given type
     * @param char $type - contact type
     * @param integer $rID - database table contacts first record to start looking for next block
     * @param integer $blockSize - number of records to delete per step
     * @return integer - number of records deleted in this step
     */
    private function fyCloseStep(&$cnt, &$type, &$rID, $blockSize, &$msg=[])
    {
        $crit = "id>$rID AND type='$type' AND inactive<>'2'"; // inactive<>2 prevents the locked records from being deleted
//      if ($type == 'c') { $crit .= " AND inactive='0'"; } // for customers, the inactive flag must be set also
        if ($type == 'v') { $crit .= " AND inactive='1'"; } // for vendors, the inactive flag must be set also
        $result = dbGetMulti(BIZUNO_DB_PREFIX.'contacts', $crit, '', ['id','short_name'], $blockSize);
        $count = 0;
        foreach ($result as $row) {
            $rID = $row['id']; // set the highest rID for next iteration
            $exists = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'id', "contact_id_b={$row['id']} OR contact_id_s={$row['id']}");
            if (!$exists) {
                $msg[] = "Deleting contact id={$row['id']}, {$row['short_name']}";
                msgDebug("\nDeleting contact id={$row['id']}, {$row['short_name']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."contacts_log  WHERE contact_id={$row['id']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."data_security WHERE module='contacts' AND ref_1={$row['id']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."address_book  WHERE ref_id={$row['id']}");
                dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."contacts      WHERE id={$row['id']}");
                $count++;
            }
        }
        if (sizeof($result) < $blockSize) {
            if     ($type == 'c') { $cnt=0; $type = 'v'; $rID=0; } // move on to next vendors
            elseif ($type == 'v') { $cnt=0; $type = '';  $rID=0; } // finished
        }
        return $count;
    }

    /**
     * Synchronizes attachments with contacts database flag and actual attachment files
     */
    public function syncAttachments()
    {
        global $io;
        $verbose = clean('verbose', 'integer', 'get');
        $deleted = $repaired = 0;
        $files = $io->folderRead(getModuleCache('contacts', 'properties', 'attachPath'));
        foreach ($files as $attachment) {
            $tID = substr($attachment, 4); // remove rID_
            $rID = substr($tID, 0, strpos($tID, '_'));
            $exists = dbGetRow(BIZUNO_DB_PREFIX.'contacts', "id=$rID");
            if (!$exists) {
                $deleted++;
                msgDebug("\nDeleting attachment for rID = $rID and file: $attachment");
                $io->fileDelete(getModuleCache('contacts', 'properties', 'attachPath')."/$attachment");
            } elseif (!$exists['attach']) {
                $repaired++;
                msgDebug("\nSetting attachment flag for id = $rID and file: $attachment");
                dbWrite(BIZUNO_DB_PREFIX.'contacts', ['attach'=>'1'], 'update', "id=$rID");
            }
        }
        if ($verbose) {
            msgAdd("Done! Deleted $deleted attachments and repaired $repaired links to contact records.", 'caution');
        }
    }
}
