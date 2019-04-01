<?php
/*
 * API inteface for PhreeBooks module
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
 * @version    3.x Last Update: 2019-03-18
 * @filesource /lib/controller/module/phreebooks/api.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/contacts/main.php", 'contactsMain');
bizAutoLoad(BIZUNO_LIB."controller/module/inventory/main.php", 'inventoryMain');
bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journal.php", 'journal');
bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'processPhreeBooks', 'function');

class phreebooksApi
{
    public $moduleID = 'phreebooks';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
    /**
     * This method builds the div for operating the API to import data, information includes import templates and forms, export forms
     * @param integer jID - journal to post to, passed as $_GET variable
     * @param array $layout - input data passed as array of tags, may also be passed as $_POST variables
     */
    public function journalAPI(&$layout)
    {
        $layout = array_replace_recursive($layout, [
            'tabs'=>['tabImpExp'=>['divs'=>['begBal'=>['order'=>90,'type'=>'divs','label'=>lang('beginning_balances'),'divs'=>[
                'formBOF' => ['order'=>15,'type'=>'form','key'=>'frmBegBal'],
                'body'    => ['order'=>50,'type'=>'html','html'=>$this->getViewBB()],
                'formEOF' => ['order'=>95,'type'=>'html','html'=>"</form>"]]]]]],
            'forms'=>[
                'frmBegBal'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/api/begBalSave"]],
                'frmImpInv'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/api/importJournal&id=inv"]],
                'frmImpJ4' => ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/api/importJournal&id=j4"]],
                'frmImpJ6' => ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/api/importJournal&id=j6"]],
                'frmImpJ10'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/api/importJournal&id=j10"]],
                'frmImpJ12'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/api/importJournal&id=j12"]]],
            'jsBody' => ['init'=>$this->getViewBBJS()],
            'jsReady'=> ['init'=>"ajaxForm('frmBegBal');"]]);
        $layout['jsReady']['phreebooksImport'] = "ajaxForm('frmImpInv');\najaxForm('frmImpJ4');\najaxForm('frmImpJ6');\najaxForm('frmImpJ10');\najaxForm('frmImpJ12');";
        $layout['tabs']['tabAPI']['divs'][$this->moduleID] = ['order'=>80,'label'=>getModuleCache($this->moduleID, 'properties', 'title'),'type'=>'html','html'=>$this->render($layout)];
    }

    private function getViewBB()
    {
        $beg_bal = $coa_asset = [];
        foreach (selGLTypes() as $type) { $coa_asset[$type['id']] = $type['asset']; }
        $precision       = getModuleCache('phreebooks', 'currency', 'iso')[getUserCache('profile', 'currency', false, 'USD')]['dec_len'];
        $bb_value        = ['styles'=>["text-align"=>"right"],'attr'=>['size'=>"13", 'value'=>0],'events'=>['onChange'=>"begBalTotal();"]];
        $bb_debit_total  = ['styles'=>["text-align"=>"right"],'attr'=>['readonly'=>'readonly', 'size'=>13, 'value'=>0]];
        $bb_credit_total = ['styles'=>["text-align"=>"right"],'attr'=>['readonly'=>'readonly', 'size'=>13, 'value'=>0]];
        $bb_balance_total= ['styles'=>["text-align"=>"right"],'attr'=>['readonly'=>'readonly', 'size'=>13, 'value'=>0]];
        $btnSaveBegBal   = ['icon'=>'save','size'=>'large','events'=>['onClick'=>"jq('body').addClass('loading'); jq('#frmBegBal').submit();"]];
        $result          = dbGetMulti(BIZUNO_DB_PREFIX."journal_history", "period=1", "gl_account");
        foreach ($result as $row) {
            $balance = round($row['beginning_balance'], $precision);
            $beg_bal[$row['gl_account']] = [
                'desc'     => getModuleCache('phreebooks', 'chart', 'accounts')[$row['gl_account']]['title'],
                'type'     => $row['gl_type'],
                'desc_type'=> lang('gl_acct_type_'.$row['gl_type']),
                'value'    => empty($balance) ? $balance : ($coa_asset[$row['gl_type']] ? $balance : -$balance),
                'asset'    => $coa_asset[$row['gl_type']]];
            }
        $output = '<table style="border-style:none;margin-left:auto;margin-right:auto;"><thead class="panel-header"><tr>
    <th>'.lang('journal_main_gl_acct_id').'</th>
    <th nowrap="nowrap">'.lang('description')               .'</th>
    <th nowrap="nowrap">'.lang('journal_item_gl_type')      .'</th>
    <th nowrap="nowrap">'.lang('journal_item_debit_amount') .'</th>
    <th nowrap="nowrap">'.lang('journal_item_credit_amount').'</th>
</tr></thead><tbody>'."\n";
        foreach ($beg_bal as $glAcct => $values) {
            $output .= "  <tr>\n";
            $output .= '   <td align="center">'.$glAcct."</td>\n";
            $output .= "   <td>".$values['desc']."</td>\n";
            $output .= "   <td>".$values['desc_type']."</td>\n";
            $bb_value['attr']['value'] = $values['value'];
            if ($values['asset']) {
                $output .= '<td style="text-align:center">'.html5("debits[$glAcct]", $bb_value)."</td>\n";
                $output .= '<td style="background-color:#CCCCCC">&nbsp;</td>'."\n";
            } else { // credit
                $output .= '<td style="background-color:#CCCCCC">&nbsp;</td>'."\n";
                $output .= '<td style="text-align:center">'.html5("credits[$glAcct]", $bb_value)."</td>\n";
            }
            $output .= "</tr>\n";
        }
        $output .= '</tbody><tfoot class="panel-header"><tr>
    <td colspan="3" align="right">'.lang('total').'</td>
    <td style="text-align:right">'.html5('bb_debit_total',  $bb_debit_total) .'</td>
    <td style="text-align:right">'.html5('bb_credit_total', $bb_credit_total).'</td>
</tr><tr>
    <td colspan="4" style="text-align:right">'.lang('balance').'</td>
    <td style="text-align:right">'.html5('bb_balance_total', $bb_balance_total).'</td>
    <td colspan="4" style="text-align:right">'.html5('btnSaveBegBal', $btnSaveBegBal)."</td>
</tr></tfoot></table>";
        return $output;
    }

    private function getViewBBJS()
    {
        return "function begBalTotal() {
    var debits = 0;
    var credits= 0;
    var balance= 0;
    jq('input[name^=debits]').each(function() { debits += cleanCurrency(jq(this).val()); });
    jq('input[name^=credits]').each(function(){ credits+= cleanCurrency(jq(this).val()); });
    balance = debits - credits;
    bizTextSet('bb_debit_total',  debits,  'currency');
    bizTextSet('bb_credit_total', credits, 'currency');
    bizTextSet('bb_balance_total',balance, 'currency');
    if (balance == 0) jq('#bb_balance_total').css({color:'#000000'});
    else jq('#bb_balance_total').css({color:'red'});
}";
    }

    /**
     * Generates the HTML for the beginning balance import for PhreeBooks journal presets
     * @param array $data - structure to build HTML
     * @return string - DOM HTML for importing beginning balances
     */
    public function render($data)
    {
        $import_inv= ['attr'=>['type'=>'file']];
        $import_j4 = ['attr'=>['type'=>'file']];
        $import_j6 = ['attr'=>['type'=>'file']];
        $import_j10= ['attr'=>['type'=>'file']];
        $import_j12= ['attr'=>['type'=>'file']];
        $btn_inv   = ['attr'=>['type'=>'button','value'=>lang('import')],'events'=>['onClick'=>"jq('#frmImpInv').submit();"]];
        $btn_j4    = ['attr'=>['type'=>'button','value'=>lang('import')],'events'=>['onClick'=>"jq('#frmImpJ4').submit();"]];
        $btn_j6    = ['attr'=>['type'=>'button','value'=>lang('import')],'events'=>['onClick'=>"jq('#frmImpJ6').submit();"]];
        $btn_j10   = ['attr'=>['type'=>'button','value'=>lang('import')],'events'=>['onClick'=>"jq('#frmImpJ10').submit();"]];
        $btn_j12   = ['attr'=>['type'=>'button','value'=>lang('import')],'events'=>['onClick'=>"jq('#frmImpJ12').submit();"]];
        return "<fieldset><legend>".lang('phreebooks_import_journal_title')."</legend>
 <p>".$this->lang['desc_import_journal'].'</p>
 <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
  <tbody>
   <tr><td>'.$this->lang['phreebooks_import_inv']."</td><td>".html5('frmImpInv',$data['forms']['frmImpInv']).
    html5('import_inv',$import_inv).html5('btn_inv',$btn_inv).'</form></td></tr>
   <tr><td colspan="2"><hr /></td></tr>
   <tr><td>'.$this->lang['phreebooks_import_po'] ."</td><td>".html5('frmImpJ4',$data['forms']['frmImpJ4']).
    html5('import_j4', $import_j4) .html5('btn_j4', $btn_j4) .'</form></td></tr>
   <tr><td colspan="2"><hr /></td></tr>
   <tr><td>'.$this->lang['phreebooks_import_ap'] ."</td><td>".html5('frmImpJ6',$data['forms']['frmImpJ6']).
    html5('import_j6', $import_j6) .html5('btn_j6', $btn_j6) .'</form></td></tr>
   <tr><td colspan="2"><hr /></td></tr>
   <tr><td>'.$this->lang['phreebooks_import_so'] ."</td><td>".html5('frmImpJ10',$data['forms']['frmImpJ10']).
    html5('import_j10',$import_j10).html5('btn_j10',$btn_j10).'</form></td></tr>
   <tr><td colspan="2"><hr /></td></tr>
   <tr><td>'.$this->lang['phreebooks_import_ar'] ."</td><td>".html5('frmImpJ12',$data['forms']['frmImpJ12']).
    html5('import_j12',$import_j12).html5('btn_j12',$btn_j12)."</form></td></tr>\n</tbody>\n</table>\n</fieldset>";
    }

    /**
     * Executes the beginning balance import operations
     * @return null
     */
    public function begBalSave()
    {
        if (!$security = validateSecurity('bizuno', 'impexp', 3)) { return; }
        $request = $_POST;
        $today   = date('Y-m-d');
        $dbData  = [];
        $credits = $debits = 0;
        foreach ($request['debits'] as $glAcct => $value) {
            $amount = clean($value, 'currency');
            $debits += $amount;
            $dbData[$glAcct] = ['beginning_balance'=>$amount, 'last_update'=>$today];
        }
        foreach ($request['credits'] as $glAcct => $value) {
            $amount = clean($value, 'currency');
            $credits += $amount;
            $dbData[$glAcct] = ['beginning_balance'=>-$amount, 'last_update'=>$today];
        }
        $balance = abs(round($debits-$credits, getModuleCache('bizuno', 'settings', 'locale', 'number_precision', 2)));
        msgDebug("\nCalculated balance (expecting zero after rounding) = $balance");
        if ($balance <> 0) { return msgAdd("Cannot update beginning balances as the debits are not equal to the credits."); }
        foreach ($dbData as $gl => $sql) { dbWrite(BIZUNO_DB_PREFIX.'journal_history', $sql, 'update', "period=1 AND gl_account='$gl'"); }
        $phreebooks = new journal();
        $phreebooks->affectedGlAccts = array_keys($dbData);
        if (!$phreebooks->updateJournalHistory(1)) { return; }
        msgAdd("Beginning Balances Updated!", 'success');
    }

    /**
     * Executes an import of one or more journal entries
     * @param $layout - structure coming in
     * @return modified $layout, user messages are generated with results
     */
    public function importJournal(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $action = clean('id', 'text', 'get');
        switch ($action) { // put it in the correct journal format
            case  'j4':
                $cType = 'v'; // Contact Type
                $jID = 4; // PhreeBooks journal
                $filename = 'import_j4';
                $glAcct= getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables'); // gl account to post to
                $desc = "Purchase Order"; // description for main and item records
                break;
            case  'j6':
                $cType = 'v';
                $jID = 6;
                $filename = 'import_j6';
                $glAcct= getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');
                $desc = "Purchase";
                break;
            case 'j10':
                $cType = 'c';
                $jID = 10;
                $filename = 'import_j10';
                $desc = "Sales Order";
                $glAcct= getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
                break;
            case 'j12':
                $cType = 'c';
                $jID = 12;
                $filename = 'import_j12';
                $desc = "Sale";
                $glAcct= getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
                break;
            case 'inv':
                $filename = 'import_inv';
                break;
        }
        $upload = new \bizuno\io();
        $upload->validateUpload($filename);
        // ***************************** START TRANSACTION *******************************
        dbTransactionStart();
        $itemCnt = 1;
        $items   = [];
        $orderCnt= 0;
        $runaway = 0;
        $rows    = array_map('str_getcsv', file($_FILES[$filename]['tmp_name']));
        $head    = array_shift($rows); // heading
        $row     = array_shift($rows); // first line to import
        if (!$row) { return msgAdd("There were no rows to process!", 'caution'); }
        $data    = array_combine($head, $row);
        while (true) {
            if (!$row) { break; }
            msgDebug("\nWorking with input data row = ".print_r($data, true));
            if ($action=='inv') { // for inventory just update the history table and inventory 
                $inv = $this->getInventory($data['SKU'], $data['Qty'], $data['Total']);
                if (!$inv['id'])   { return msgAdd("Cannot locate SKU: {$data['SKU']}, Aborting!"); }
                if (!$data['Qty']) { return msgAdd("Quantity for SKU: {$data['SKU']} is zero, please verify your data. Aborting!"); }
                dbWrite(BIZUNO_DB_PREFIX.'inventory', ['qty_stock'=>$data['Qty']], 'update', "id={$inv['id']}");
                $invHistory = [
                    'journal_id'=> 16,
                    'sku'       => $data['SKU'],
                    'qty'       => $data['Qty'],
                    'remaining' => $data['Qty'],
                    'unit_cost' => $data['Total']/$data['Qty'],
                    'avg_cost'  => $data['Total']/$data['Qty'],
                    'post_date' => date('Y-m-d'),
                ];
                dbWrite(BIZUNO_DB_PREFIX.'inventory_history', $invHistory, 'insert');
                $row  = array_shift($rows);
                if (!$row) { break; }
                $data = array_combine($head, $row);
                $itemCnt++;
                continue; // next SKU
            }
            $postDate = clean($data['PostDate'], 'date');
            if (!$postDate) { $postDate = date('Y-m-d'); }
            $main = [
                'post_date'     => $postDate,
                'terminal_date' => $postDate,
                'invoice_num'   => $data['InvoiceNumber'],
                'purch_order_id'=> isset($data['PONumber']) ? $data['PONumber'] : '',
                'gl_acct_id'    => $glAcct,
                ];
            if (in_array($jID, [4,10])) { // build the item, check stock if auto_journal for Sales Orders and Purchase Orders
                $inv = $this->getInventory($data['SKU'], $data['Qty'], $data['LineTotal']);
                if (!$inv) { return msgAdd("SKU {$data['SKU']} not found in your database. It would have been automatically added if you have inventory autoadd set to Yes in PhreeBooks settings. The process has been termminated, no data was saved."); }
                $items[] = [
                    'item_cnt'      => $itemCnt,
                    'gl_type'       => 'itm',
                    'sku'           => $data['SKU'],
                    'qty'           => $data['Qty'],
                    'description'   => $desc,
                    'debit_amount'  => in_array($jID, [4]) ? $data['LineTotal'] : 0,
                    'credit_amount' => in_array($jID, [10])? $data['LineTotal'] : 0,
                    'gl_account'    => in_array($jID, [4]) ? $inv['gl_inv'] : $inv['gl_sales'],
                    'tax_rate_id'   => 0,
                    'full_price'    => $inv['full_price'],
                    'post_date'     => $postDate];
            }
            // check for continuation order
            $row = array_shift($rows);
            if ($runaway++ > 1000) { msgAdd("runaway reached, exiting!"); break; }
            if ($row) { // check for continuation order
                $nextData = array_combine($head, $row);
                msgDebug("\nContinuing order check, Next order = {$nextData['InvoiceNumber']} and this order = {$data['InvoiceNumber']}");
                if ($nextData['InvoiceNumber'] == $main['invoice_num']) {
                    $data = $nextData;
                    $itemCnt++;
                    continue; // more items for the same order
                }
            } else { $nextData = []; }
            // finish main and item to post
            $this->setContactInfo($main, $data['BillContactID'], $cType, '_b');
            if (!isset($data['ShipContactID'])) { $data['ShipContactID'] = $data['BillContactID']; }
            $this->setContactInfo($main, in_array($jID, [10,12]) ? $data['ShipContactID'] : false, 'c', '_s');
            $main['total_amount'] = $data['Total'];
            if (isset($data['Shipping']) && $data['Shipping']) { $items[] = [
                'qty'          => 1,
                'gl_type'      => 'frt',
                'description'  => "Shipping Invoice # ".$main['invoice_num'],
                'debit_amount' => in_array($jID, [4]) ? $data['Shipping'] : 0,
                'credit_amount'=> in_array($jID, [10])? $data['Shipping'] : 0,
                'gl_account'   => getModuleCache('extShipping', 'settings', 'general', 'gl_shipping_c'),
                'tax_rate_id'  => 0,
                'post_date'    => $postDate];
            }
            if (in_array($jID, [6,12])) { // for sales, purchases just need a gl account to offset total
                if (!$data['HoldingGLAccount']) { return msgAdd("Expecting a holding GL account for importing to, didn't find it in the csv file! Aborting..."); }
                $items[] = [
                    'qty'          => 1,
                    'gl_type'      => 'itm',
                    'description'  => "Total $desc # ".$data['InvoiceNumber'],
                    'debit_amount' => in_array($jID, [6]) ? $data['Total'] : 0,
                    'credit_amount'=> in_array($jID, [12])? $data['Total'] : 0,
                    'gl_account'   => $data['HoldingGLAccount'],
                    'post_date'    => $postDate,
                    ];                
            }
            $items[] = [
                'qty'          => 1,
                'gl_type'      => 'ttl',
                'description'  => "Total $desc # ".$data['InvoiceNumber'],
                'debit_amount' => in_array($jID, [10,12])? $data['Total'] : 0,
                'credit_amount'=> in_array($jID, [4,6])  ? $data['Total'] : 0,
                'gl_account'   => $glAcct, // either payables or receivables
                'post_date'    => $postDate,
                ];
            // set some specific journal information, first post journal
            $dup = dbGetValue(BIZUNO_DB_PREFIX."journal_main", "id", "invoice_num='{$main['invoice_num']}'");
            if ($dup) {
                msgDebug("duplicate order id = $dup and main = ".print_r($main, true));
                msgAdd(sprintf($this->lang['err_dup_order'], $main['invoice_num']), 'caution');
            } else {
                $ledger = new journal(0, $jID, $main['post_date']);
                $ledger->main = array_merge($ledger->main, $main);
                $ledger->item = $items;
                if (!$ledger->Post()) { return; }
                $orderCnt++;
            }
            // prepare for next line.
            $data   = $nextData;
            $itemCnt= 1;
            $items  = [];
        }
        if ($orderCnt) { if (!$ledger->updateJournalHistory(1)) { return; } }
        dbTransactionCommit();
        // ***************************** END TRANSACTION *******************************
        msgAdd(sprintf(lang('import')." ($action) - successfully imported %i entries.", $orderCnt), 'success');
        msgLog(sprintf(lang('import')." ($action) - %i successfully imported", $orderCnt));
    }
    
    /**
     * Creates a new contact record to support journal import
     * @param array $main - data from a line of the import file
     * @param string $contact - Contact short name to locate record in database
     * @param char $cType - Contact type
     * @param strign $suffix - suffix of fields to extract contact info
     */
    private function setContactInfo(&$main, $contact='', $cType='c', $suffix='_b')
    {
        $_GET['type']= $cType;
        $_GET['rID'] = 0;
        $thisContact = new contactsMain();
        if ($contact) {
            $dbContact = clean($contact, 'db_string');
            $_GET['rID'] = $_POST['id'] = $cID = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'id', "short_name='$dbContact'");
            if (!$cID) { // make a new record
                $_POST['short_name'] = $_POST['primary_namem'] = $contact;
                $_POST['address_idm']= 0;
                msgDebug("\nCreating a new contact: $contact");
                $layout=[];
                $thisContact->save($layout, false);
                $cID = $_GET['rID'];
            }
            $details=[];
            $thisContact->details($details);
            msgDebug("\nContact Details returned:".print_r($details, true));
            foreach ($details['content']['address'] as $row) { if ($row['type'] == 'm') { $data['address'] = $row; break; } }
        } else { // else populate main array with my business data
            $cID = 0;
            $data['address'] = addressLoad(0);
        }
        msgDebug("\nContact details building with id = $cID and address:".print_r($data['address'], true));
        // populate contact info
        $main['contact_id'  .$suffix] = $cID;
        $main['address_id'  .$suffix] = $data['address']['address_id'];
        $main['primary_name'.$suffix] = $data['address']['primary_name'];
        $main['contact'     .$suffix] = $data['address']['contact'];
        $main['address1'    .$suffix] = $data['address']['address1'];
        $main['address2'    .$suffix] = $data['address']['address2'];
        $main['city'        .$suffix] = $data['address']['city'];
        $main['state'       .$suffix] = $data['address']['state'];
        $main['postal_code' .$suffix] = $data['address']['postal_code'];
        $main['country'     .$suffix] = $data['address']['country'];
        $main['telephone1'  .$suffix] = $data['address']['telephone1'];
        $main['email'       .$suffix] = $data['address']['email'];
    }
    
    /**
     * 
     * @param string $sku - SKU being searched for
     * @param float $qty - number of units, used to calculate line item cost 
     * @param float $total - total for qty units, used to calculate line item cost 
     * @return array - inventory database record information
     */
    private function getInventory($sku='', $qty=1, $total=0)
    {
        $thisInventory = new inventoryMain();
        $dbSKU = clean($sku, 'db_string');
        $sID = dbGetValue(BIZUNO_DB_PREFIX.'inventory', 'id', "sku='$dbSKU'");
        if (!$sID) { // make a new record
            $_POST['id'] = 0;
            $_POST['sku'] = $_POST['description_short'] = $_POST['description_sales'] = $_POST['description_purchase'] = $sku;
            $_POST['creation_date'] = $_POST['last_update'] = $_POST['last_journal_date'] = viewFormat(date('Y-m-d'), 'date');
            $_POST['item_cost'] = $qty ? $total/$qty : 0;
            $_POST['gl_sales']= getModuleCache('inventory', 'settings', 'phreebooks', 'sales_si');
            $_POST['gl_inv']  = getModuleCache('inventory', 'settings', 'phreebooks', 'inv_si');
            $_POST['gl_cogs'] = getModuleCache('inventory', 'settings', 'phreebooks', 'cogs_si');
            $_POST['type'] = 'si';
            msgDebug("\nCreating a new SKU: $sku");
            $layout=[];
            $thisInventory->save($layout, false);
        }
        $inv = dbGetRow(BIZUNO_DB_PREFIX.'inventory', "sku='$dbSKU'");
        msgDebug("\nSKU properties returned: ".print_r($inv, true));
        return $inv;
    }
}