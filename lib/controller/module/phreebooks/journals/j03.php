<?php
/*
 * PhreeBooks journal class for Journal 3, Vendor Quote
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
 * @copyright  2008-2018, PhreeSoft Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-05-30
 * @filesource /lib/controller/module/phreebooks/journals/j03.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j03 extends jCommon
{
    public $journalID = 3;

	function __construct($main=[], $item=[])
    {
		parent::__construct();
        $this->main = $main;
		$this->item = $item;
	}

/*******************************************************************************************************************/
// START Edit Methods
/*******************************************************************************************************************/
    /**
     * Pulls the data for the specified journal and populates the structure
     * @param array $data - current working structure
     * @param array $structure - table structures
     * @param integer $rID - record id of the transaction to load from the database
     */
    public function getDataMain(&$data, $structure, $rID=0)
    {
        if ($rID) { $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=$rID"); }
        else      { $dbData = []; } // should never happen but just in case
        if ($dbData['currency'] <> getUserCache('profile', 'currency', false, 'USD')) { // convert to posted currency
            $mainFields = ['discount','sales_tax','total_amount'];
            foreach ($mainFields as $field) { $dbData[$field] = $dbData[$field] * $dbData['currency_rate']; }
        }
        dbStructureFill($data['fields']['main'], $dbData);
        if ($rID) { $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID"); }
        else      { $data['items'] = []; } // should never happen but just in case
        if ($dbData['currency'] <> getUserCache('profile', 'currency', false, 'USD')) { // convert to posted currency
            $itemFields = ['debit_amount','credit_amount','full_price'];
            foreach ($data['items'] as $idx => $row) { foreach ($itemFields as $field) {
                $data['items'][$idx][$field] = $data['items'][$idx][$field] * $dbData['currency_rate'];
            } }
        }
        $debitCredit = in_array($this->journalID, [3,4,6,13,21]) ? 'debit' : 'credit';
        $dbData = [];
        if (sizeof($data['items']) > 0) {
            foreach ($data['items'] as $row) {
                if ($row['gl_type'] <> 'itm') { continue; } // not an item record, skip
                if (empty($row['bal'])) { $row['bal'] = 0; }
                if (empty($row['qty'])) { $row['qty'] = 0; }
                if (is_null($row['sku'])) { $row['sku'] = ''; } // bug fix for easyui combogrid, doesn't like null value
                $row['description'] = str_replace("\n", " ", $row['description']); // fixed bug with \n in description field
                if (!isset($row['price'])) { $row['price'] = $row['qty'] ? (($row['credit_amount']+$row['debit_amount'])/$row['qty']) : 0; }
                if ($row['item_ref_id']) {
                    $filled    = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(qty)", "item_ref_id={$row['item_ref_id']} AND gl_type='itm'", false);
                    $row['bal']= $row['bal'] - $filled + $row['qty']; // so/po - filled prior + this order
                }
                if ($row['sku']) { // now fetch some inventory details for the datagrid
                    $inv     = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['qty_stock', 'item_weight'], "sku='{$row['sku']}'");
                    $inv_adj = in_array($this->journalID, [3,4,6,13,21]) ? -$row['qty'] : $row['qty'];
                    $row['qty_stock']  = $inv['qty_stock'] + $inv_adj;
                    $row['item_weight']= $inv['item_weight'];
                }
                $dbData[] = $row;
            }
        }
        if ($debitCredit=='credit') { $map['credit_amount']= ['type'=>'field','index'=>'total']; }
        if ($debitCredit=='debit')  { $map['debit_amount'] = ['type'=>'field','index'=>'total']; }
        $data['jsHead']['datagridData'] = formatDatagrid($dbData, 'datagridData', $structure['journal_item'], $map);
    }
    
    /**
     * Tailors the structure for the specific journal
     * @param array $data - current working structure
     * @param integer $rID - Database record id of the journal main record
     * @param integer $security - Users security level
     */
    public function getDataItem(&$data)
    {
        $data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'v');
        $data['fields']['main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');
		$isWaiting = isset($data['fields']['main']['waiting']['attr']['checked']) && $data['fields']['main']['waiting']['attr']['checked'] ? '1' : '0';
		$data['fields']['main']['waiting'] = ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]]; // field not used
        $data['divs']['divDetail'] = ['order'=>50,'type'=>'divs','classes'=>['areaView'],'attr'=>['id'=>'pbDetail'],'divs'=>[
            'billAD' => ['order'=>20,'type'=>'address','label'=>lang('bill_to'),'classes'=>['blockView'],'attr'=>['id'=>'address_b'],'content'=>$this->cleanAddress($data['fields']['main'], '_b'),
                'settings'=>['suffix'=>'_b','search'=>true,'copy'=>true,'update'=>true,'validate'=>true,'fill'=>'both','required'=>true,'store'=>false]],
            'shipAD' => ['order'=>30,'type'=>'address','label'=>lang('ship_to'),'classes'=>['blockView'],'attr'=>['id'=>'address_s'],'content'=>$this->cleanAddress($data['fields']['main'], '_s'),
                'settings'=>['suffix'=>'_s','update'=>true,'validate'=>true,'drop'=>true]],
            'props'  => ['order'=>40,'type'=>'fields','classes'=>['blockView'], 'attr'=>['id'=>'pbProps'],'fields'=>$this->getProps($data)],
            'totals' => ['order'=>50,'type'=>'totals','classes'=>['blockViewR'],'attr'=>['id'=>'pbTotals'],'content'=>$data['totals_methods']]]];
        $data['divs']['dgItems']= ['order'=>60,'type'=>'datagrid','key'=>'item'];
        $data['divs']['other']  = ['order'=>70,'type'=>'html','html'=>'<div id="shippingEst"></div><div id="shippingVal"></div>'];
    }

    /**
     * Configures the journal entry properties (other than address and items)
     * @param array $data - current working structure
     * @return array - List of fields to show with the structure
     */
    private function getProps($data)
    {
        $data['fields']['main']['sales_order_num'] = ['label'=>lang('journal_main_invoice_num_10'),'attr'=>['value'=>isset($this->soNum)?$this->soNum:'','readonly'=>'readonly']];
        return [
            'id'             => $data['fields']['main']['id'],
            'journal_id'     => $data['fields']['main']['journal_id'],
            'so_po_ref_id'   => $data['fields']['main']['so_po_ref_id'],
            'terms'          => $data['fields']['main']['terms'],
            'override_user'  => $data['override_user'],
            'override_pass'  => $data['override_pass'],
            'recur_id'       => $data['fields']['main']['recur_id'],
            'recur_frequency'=> $data['recur_frequency'],
            'item_array'     => $data['item_array'],
            'xChild'         => ['attr'=>['type'=>'hidden']],
            'xAction'        => ['attr'=>['type'=>'hidden']],
            // Displayed
            'purch_order_id' => array_merge(['break'=>true], $data['fields']['main']['purch_order_id']),
            'terms_text'     => $data['terms_text'],
            'terms_edit'     => array_merge(['break'=>true], $data['terms_edit']),
            'invoice_num'    => array_merge(['break'=>true], $data['fields']['main']['invoice_num']),
            'waiting'        => array_merge(['break'=>true], $data['fields']['main']['waiting']),
            'post_date'      => array_merge(['break'=>true], $data['fields']['main']['post_date']),
            'terminal_date'  => array_merge(['break'=>true], $data['fields']['main']['terminal_date']),
            'store_id'       => array_merge(['break'=>true], $data['fields']['main']['store_id']),
            'rep_id'         => array_merge(['break'=>true], $data['fields']['main']['rep_id']),
            'currency'       => array_merge(['break'=>true], $data['fields']['main']['currency']),
            'closed'         => array_merge(['break'=>true], $data['fields']['main']['closed'])];
    }
/*******************************************************************************************************************/
// START Post Journal Function
/*******************************************************************************************************************/
	public function Post()
    {
        msgDebug("\n/********* Posting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        $this->setItemDefaults(); // makes sure the journal_item fields have a value
        $this->unSetCOGSRows(); // they will be regenerated during the post
        $this->postMain();
        $this->postItem();
        if (!$this->postInventory())         { return; }
        if (!$this->postJournalHistory())    { return; }
        if (!$this->setStatusClosed('post')) { return; }
        msgDebug("\n*************** end Posting Journal ******************* id = {$this->main['id']}\n\n");
		return true;
	}

	public function unPost()
    {
        msgDebug("\n/********* unPosting Journal main ... id = {$this->main['id']} and journal_id = {$this->main['journal_id']}");
        if (!$this->unPostJournalHistory())    { return; }	// unPost the chart values before inventory where COG rows are removed
        if (!$this->unPostInventory())         { return; }
		$this->unPostMain();
        $this->unPostItem();
        if (!$this->setStatusClosed('unPost')) { return; } // check to re-open predecessor entries 
        msgDebug("\n*************** end unPosting Journal ******************* id = {$this->main['id']}\n\n");
		return true;
	}

    /**
     * Get re-post records - applies to journals 3, 4, 9, 10
     * @return array - journal id's that need to be re-posted as a result of this post
     */
    public function getRepostData()
    {
		msgDebug("\n  j03 - Checking for re-post records ... ");
        return $this->getRepostSale();
	}

	/**
     * Post journal item array to journal history table
     * applies to journal 3, 4, 9, 10
     * @return boolean - true
     */
    private function postJournalHistory()
    {
		msgDebug("\n  Posting Chart Balances... end Posting Chart Balances with no action.");
		return true;
	}

	/**
     * unPosts journal item array from journal history table
     * applies to journal 3, 4, 9, 10
     * @return boolean - true
     */
	private function unPostJournalHistory() {
		msgDebug("\n  unPosting Chart Balances... end unPosting Chart Balances with no action.");
		return true;
	}

	/**
     * Post inventory
     * applies to journal 2, 3, 9, 17, 18, 20, 22
     * @return boolean true on success, null on error
     */
    private function postInventory()
    {
		msgDebug("\n  Posting Inventory ... end Posting Inventory not requiring any action.");
		return true;
	}

	/**
     * unPost inventory
     * applies to journal 2, 3, 9, 17, 18, 20, 22
     * @return boolean true on success, null on error
     */
	private function unPostInventory()
    {
		msgDebug("\n  unPosting Inventory ... end unPosting Inventory with no action.");
		return true;
	}

	/**
     * Checks and sets/clears the closed status of a journal entry
     * Affects journals - 3, 7, 9, 13, 14, 15, 16
     * @param string $action - [default: 'post']
     * @return boolean true
     */
	private function setStatusClosed($action='post')
    {
		msgDebug("\n  Checking for closed entry. action = $action, returning with no action.");
		return true;
	}
}
