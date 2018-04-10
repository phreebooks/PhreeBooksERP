<?php
/*
 * Module PhreeBooks, main functions
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
 * @version    2.x Last Update: 2018-04-05
 * @filesource /lib/controller/module/phreebooks/main.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/functions.php");
require_once(BIZUNO_LIB."controller/module/phreebooks/journal.php");
require_once(BIZUNO_LIB."controller/module/payment/main.php");

class phreebooksMain
{
    public  $moduleID = 'phreebooks';
    public  $journalID= 0;
	public  $gl_type  = '';
    private $assets   = [0,2,4,6,8,12,32,34];

	function __construct()
    {
		$this->lang   = getLang($this->moduleID);
        $this->rID    = clean('rID', 'integer', 'get');
		$this->action = clean('bizAction', 'text', 'get');
        if ($this->rID && $this->action <> 'inv') { $_GET['jID'] = $this->journalID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'journal_id', "id=$this->rID"); }
        else { $this->journalID = clean('jID', 'integer', 'get'); }
        if (!defined('JOURNAL_ID')) { define('JOURNAL_ID', $this->journalID); }
        $this->totals = $this->loadTotals($this->journalID);
        $typeInferred = in_array($this->journalID, [2,3,4,6,7,17,20,21]) ? 'v' : 'c';
		$this->type   = clean('type', ['format'=>'char', 'default'=>$typeInferred], 'get');
        if (!defined('CONTACT_TYPE')) { define('CONTACT_TYPE', $this->type); }
		$this->helpIndex = "phreebooks.main.intro.$this->journalID";
		switch ($this->journalID) {
            case  2: $this->gl_type= 'gl';   break; // General Journal
			case  3: // Vendor RFQ
			case  4: // Vendor PO
			case  6: // Vendor Purchases
			case  7: // Vendor Credit Memos
			case  9: // Customer RFQ
			case 10: // Customer SO
			case 12: // Customer Sales
			case 13: // Customer Credit Memos
			case 19: // Point of Sale
			case 21: $this->gl_type = 'itm'; break; // Point of Purchase
			case 14: $this->gl_type = 'asy'; break; // Inventory Assemblies
            case 15: // Inventory Store Transfers
			case 16: $this->gl_type = 'adj'; break; // Inventory Adjustments
			case 17: // Vendor Receipts
			case 18: $this->gl_type = 'pmt'; break; // Customer Receipts
			case 20: // Vendor Payments
			case 22: $this->gl_type = 'pmt'; break; // Customer Payments
		}
	}

	/**
     * Entry point structure for the PhreeBooks journal manager, handles all journals
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
		$rID = clean('rID', 'integer', 'get');
		$cID = clean('cID', 'integer', 'get');
		$mgr = clean('mgr', 'boolean', 'get');
        $jsBody = $jsReady = '';
        $detail = lang('journal_main_journal_id', $this->journalID);
        if     (in_array($this->journalID, [3, 4, 6, 7])) { $manager = sprintf(lang('tbd_manager'), lang('journal_main_journal_id_6')); }
        elseif (in_array($this->journalID, [9,10,12,13])) { $manager = sprintf(lang('tbd_manager'), lang('journal_main_journal_id_12')); }
        else   { $manager= sprintf(lang('tbd_manager'), lang('journal_main_journal_id', $this->journalID)); }
        if ($this->journalID == 0) { // search
			$jsBody = "viewLabels();
jq('#postDateMin').datebox({onChange:function (newDate) { jq('#postDateMin').val(newDate); } });
jq('#postDateMax').datebox({onChange:function (newDate) { jq('#postDateMax').val(newDate); } });
jq('#search').focus();";
		} elseif (!$mgr || $rID || $cID) { // get the detail screen
			$jsReady = "setTimeout(function () { journalEdit($this->journalID, $rID, $cID, '$this->action') }, 500);";
		} else {
            $jsReady = "jq('#search').next().find('input').focus();";
        }
        if     (in_array($this->journalID, [ 3, 4, 6, 7]))       { $submenu = viewSubMenu('vendors'); }
        elseif (in_array($this->journalID, [ 9,10,12,13]))       { $submenu = viewSubMenu('customers'); }
        elseif (in_array($this->journalID, [0]))                 { $submenu = viewSubMenu('tools'); }
        elseif (in_array($this->journalID, [2]))                 { $submenu = viewSubMenu('ledger'); }
        elseif (in_array($this->journalID, [14,16]))             { $submenu = viewSubMenu('inventory'); }
        elseif (in_array($this->journalID, [17,18,19,20,21,22])) { $submenu = viewSubMenu('banking'); }
        else   { $submenu = ''; } 
		$data = [
            'pageTitle'=> $detail,
			'divs'     => [
                'submenu'   => ['order'=>10, 'type'=>'html','html'=>$submenu],
                'phreebooks'=> ['order'=>60, 'type'=>'accordion','key'=>'accJournal']],
			'accordion'=> ['accJournal'=> ['divs'=>  [
                'divJournalManager'=> ['order'=>30,'label'=>$manager,'type'=>'datagrid','key' =>'manager'],
				'divJournalDetail' => ['order'=>60,'label'=>$detail, 'type'=>'html', 'html'=>lang('msg_edit_new')]]]],
            'jsHead'   => ['phreebooks'=> "jq.cachedScript('".BIZUNO_URL."controller/module/phreebooks/phreebooks.js?ver=".MODULE_BIZUNO_VERSION."');"],
            'jsBody'   => ['phreebooks'=> $jsBody],
            'jsReady'  => ['phreebooks'=> $jsReady],
			'datagrid' => ['manager'=> $this->dgPhreeBooks('dgPhreeBooks', $security)]];
        if ($this->journalID == 0) { unset($data['accordion']['accJournal']['divs']['divJournalDetail']); }
		$layout = array_replace_recursive($layout, viewMain(), $data);
	}

	/**
     * List the journals filter by users selections
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
		$structure = $this->dgPhreeBooks('dgPhreeBooks', $security);
        if ($this->journalID==0) { $structure['strict'] = true; } // needed to search journal_item and limit rows per id
		$layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
	}

	/**
     * Special datagrid list request for orders independent of period
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRowsOrder(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
		$waiting = clean('waiting', 'integer', 'get');
        $_POST['search'] = getSearch();
		$data = $this->dgPhreeBooks('dgPhreeBooks', $security);
        unset($data['source']['filters']['period']);
		// reset some search criteria
		$data['source']['search'] = [BIZUNO_DB_PREFIX.'journal_main.invoice_num'];
		$data['source']['filters']['search'] = ['order'=>90,'html'=>['label'=>lang('search'),'attr'=>['type'=>'input','value'=>getSearch()]]];
		if ($waiting) {
			$data['source']['filters']['waiting']    = ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."journal_main.waiting='1'"];
			$data['source']['filters']['method_code']= ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."journal_main.method_code<>''"];
		}
		$data['source']['sort'] = ['s0'=> ['order'=>10, 'field'=>BIZUNO_DB_PREFIX."journal_main.invoice_num"]];
		$layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$data]);
	}

	/**
     * Builds a list of matches to a search for customers/vendors with outstanding unpaid invoices, also provides the invoiced total (not necessarily the outstanding amount)
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRowsBank(&$layout=[])
    {
        $output = [];
        $_POST['search'] = $search_text = getSearch();
		switch ($this->journalID) {
			case 17:
			case 20: $jID= '6,7'; break;
			case 18:
			case 22: $jID='12,13'; break;
		}
        if ($this->journalID==20 && validateSecurity('phreebooks', 'j2_mgr', 1)) { $jID .= ',2'; }
		$searchFields = [
            BIZUNO_DB_PREFIX.'journal_main.primary_name_b',
			BIZUNO_DB_PREFIX.'journal_main.primary_name_s',
			BIZUNO_DB_PREFIX.'journal_main.postal_code_b',
			BIZUNO_DB_PREFIX.'journal_main.postal_code_s',
			BIZUNO_DB_PREFIX.'journal_main.invoice_num',
			BIZUNO_DB_PREFIX.'journal_main.purch_order_id'];
		$search = $search_text ? "AND (".implode(" like '%$search_text%' or ", $searchFields)." like '%$search_text%')" : '';
        $rows = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", "closed='0' AND contact_id_b>0 AND journal_id IN ($jID) $search", 'primary_name_b',
            ['id','journal_id','contact_id_b','primary_name_b','city_b','state_b','total_amount']);
		foreach ($rows as $row) {
            if (in_array($row['journal_id'], [7,13])) { $row['total_amount'] = -$row['total_amount']; }
            $row['total_amount'] += getPaymentInfo($row['id'], $row['journal_id']);
            if (!isset($output[$row['contact_id_b']])) { 
                $output[$row['contact_id_b']] = $row; }
            else {
                $output[$row['contact_id_b']]['total_amount'] += $row['total_amount'];
            }
        }
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>json_encode(['total'=>sizeof($output), 'rows'=>array_values($output)])]);
	}

    /**
     * Loads the filters from the query to populate the datagrid
     */
    private function managerSettings()
    {
		$data = ['path'=>'phreebooks'.$this->journalID, 'values'=>  [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX.'journal_main.post_date'],
            ['index'=>'order', 'clean'=>'text',   'default'=>'DESC'],
            ['index'=>'period','clean'=>'text',   'default'=>getModuleCache('phreebooks', 'fy', 'period')],
            ['index'=>'jID',   'clean'=>'integer','default'=>'a'],
            ['index'=>'status','clean'=>'char',   'default'=>'a'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
        if (clean('clr', 'boolean', 'get')) { clearUserCache($data['path']); }
		$this->defaults = updateSelection($data);
	}

	/**
     * Structure to edit a journal entry
     * @param array $layout - Structure coming in
     * @param string $divID - DOM field id
     * @return modified $layout
     */
    public function edit(&$layout=[], $divID='divJournalDetail')
    {
        $rID       = $this->rID = clean('rID', 'integer', 'get');
		$cID       = clean('cID',   'integer', 'get'); // contact record for banking stuff
		$references= (array)explode(":", clean('iID', 'text', 'get'));
		$xAction   = clean('xAction','text', 'get');
		$min_level = $this->action=='inv' || !$rID? 2 : 1; // if converting SO/PO then add else read-only and above
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", $min_level)) { return []; }
		if (!$cID && sizeof($references) && !empty($references[0])) { // attempt to pull contact_id from prechecked records
			$cID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'contact_id_b', "id={$references[0]}");
		}
		$structure = [
            'journal_main' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_main', $this->journalID),
			'journal_item' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_item', $this->journalID),
			'address_book' => dbLoadStructure(BIZUNO_DB_PREFIX.'address_book', $this->journalID)];
		// fix map to address links
		$structure['address_book']['contact_id'] = $structure['address_book']['ref_id'];
		unset($structure['address_book']['ref_id']);
		$structure['address_book']['country']  ['attr']['value'] = getModuleCache('bizuno', 'settings', 'company', 'country', 'USA');
        $structure['journal_main']['country_b']['attr']['value'] = getModuleCache('bizuno', 'settings', 'company', 'country', 'USA');
        $structure['journal_main']['country_s']['attr']['value'] = getModuleCache('bizuno', 'settings', 'company', 'country', 'USA');
		switch ($this->journalID) {
			case 14: $template = 'accInvAssyDetail'; break;
            case 15:
			case 16: $template = 'accDetail'; break;
			case 17:
			case 18:
			case 20:
			case 22: $template = 'accBankDetail'; break;
			default: $template = 'accDetail'; break;
		}
		$data = ['type'=>'divHTML',
			'divs'   => [
                'divDetail' => ['order'=>50, 'src'=>BIZUNO_LIB."view/module/phreebooks/$template.php"],
                'divAttach' => ['order'=>90, 'src'=>BIZUNO_LIB."view/module/bizuno/divAttach.php",'attr'=>['delPath'=>"$this->moduleID/main/deleteAttach"]],
                'divEOF'    => ['order'=>99, 'type'=>'html', 'html'=>'</form>']],
			'toolbar' => ['tbPhreeBooks'=>  ['icons'=>  [
                'jSave'  => ['order'=>10,'label'=>lang('save'),'icon'=>'save','type'=>'menu','hidden'=>$security>1?false:true,
					'events'=>  ['onClick'=>"jq('#frmJournal').submit();"],'child'=>$this->renderMenuSave($security)],
				'recur'  => ['order'=>50,'label'=>lang('recur'),'tip'=>lang('recur_new'),'hidden'=>$security>1?false:true,'events'=>['onClick'=>"var data=jq('input[name=radioRecur]:checked').val()+':'+jq('#recur_id').val(); windowEdit('phreebooks/main/popupRecur&data='+data, 'winRecur', '".jsLang('recur')."', 450, 300);"]],
				'new'    => ['order'=>60,'label'=>lang('new'),'hidden'=>$security>1?false:true,'events'=>['onClick'=>"journalEdit($this->journalID, 0);"]],
				'trash'  => ['order'=>70,'label'=>lang('delete'),'hidden'=>$rID && $security==4?false:true,'events'=>  ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/main/delete&jID=$this->journalID', $rID);"]],
				'help'   => ['order'=>99,'label'=>lang('help'),'index' =>$this->helpIndex]]]],
			'form' => ['frmJournal'=>  ['attr'=>  ['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/main/save&jID=$this->journalID"]]],
            'fields' => ['address_book'=>$structure['address_book']],
			'journal_main' => $structure['journal_main'],
			'journal_item' => $structure['journal_item'],
			'journal_msg'  => ''];
		// see if there are any extra actions
		if (substr($xAction, 0, 8) == 'journal:') {
			$temp = explode(':', $xAction);
			if (isset($temp[1])) {
				$data['toolbar']['tbPhreeBooks']['icons']['jSave']['events']['onClick'] = "jq('#xAction').val('journal:{$temp[1]}'); jq('#frmJournal').submit();";
				$data['toolbar']['tbPhreeBooks']['icons']['cancel'] = ['order'=>5,'events'=>  ['onClick'=>"journalEdit({$temp[1]}, 0);"]];
				unset($data['toolbar']['tbPhreeBooks']['icons']['new']);
			}
		}
		// customize the content depending on the journal being used, first set some defaults
		$data['journal_main']['journal_id']['attr']['value'] = $this->journalID;
		$data['journal_main']['post_date']['attr']['value']  = date('Y-m-d');
        if (in_array($this->journalID, [3, 6, 9])) {
            $data['journal_main']['terminal_date']['attr']['value'] = getTermsDate('', $this->type);
        } else {
            $data['journal_main']['terminal_date']['attr']['value'] = date('Y-m-d');
        }
		$termsType = in_array($this->journalID, [3,4,6,7,17,20,21]) ? 'vendors' : 'customers';
		$data['journal_main']['terms']['attr']['value']      = getModuleCache('phreebooks', 'settings', $termsType, 'terms');
		$data['journal_main']['gl_acct_id']['jsBody']        = htmlComboGL('gl_acct_id');
		if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1 && !in_array($this->journalID, [2,17,18,20,22])) {
			$data['journal_main']['currency']['attr']['type']= 'select';
			$data['journal_main']['currency']['values']      = viewDropdown(getModuleCache('phreebooks', 'currency', 'iso'), "code", "title");
			unset($data['journal_main']['currency']['attr']['size']);
			unset($data['journal_main']['currency_rate']['attr']['type']);
			$data['journal_main']['currency_rate']['attr']['readonly'] = 'readonly';
			$data['journal_main']['currency_rate']['label']  = '';
		} else {
			$data['journal_main']['currency']['attr']['type']= 'hidden';
		}
		$data['journal_main']['rep_id']['attr']['value']= getUserCache('profile', 'contact_id', false, 0);
		$data['journal_main']['rep_id']['values']       = viewRoleDropdown();
		$data['journal_main']['currency_rate']['break'] = true;
		$data['journal_main']['waiting']['break']       = true;
		$data['journal_main']['closed']['break']        = true;
		$data['journal_main']['invoice_num']['tooltip'] = lang('msg_leave_null_to_assign_ref');

		$map = []; // map of db field names to form fields
		if ($rID > 0 || $cID > 0 || $this->action=='bulk') {
			switch ($this->journalID) { // merge data with structure
				case 14: break; // handled later when rest of unique fields have been set
				case  2:
					$dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id='$rID'");
					dbStructureFill($data['journal_main'], $dbData);
					$data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id='$rID'");
                    $this->addGLNotes($data['items']);
					msgDebug("\n read line items = ".print_r($data['items'], true));
					$data['jsHead']['datagridData'] = formatDatagrid($data['items'], 'datagridData', $structure['journal_item']);
					break;
				case 15:
				case 16:
					$dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id='$rID'");
					dbStructureFill($data['journal_main'], $dbData);
					$data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id='$rID'");
					$dbData = [];
					if (sizeof($data['items']) > 0) { // calculate some form fields that are not in the db
						foreach ($data['items'] as $key => $row) {
                            if ($row['gl_type'] <> 'adj') { continue; } // not an adjustment record
							$values = dbGetRow(BIZUNO_DB_PREFIX."inventory", "sku='{$row['sku']}'");
							$row['qty_stock'] = $values['qty_stock']-$row['qty'];
                            $row['balance']   = $values['qty_stock'];
							$row['price']     = viewFormat($values['item_cost'], 'currency');
							$dbData[$key]     = $row;
						}
					}
					if (in_array($this->journalID, [15])) {
                        $map['credit_amount']= ['type'=>'field','index'=>'total'];
					} else {
                        $map['debit_amount'] = ['type'=>'field','index'=>'total'];
                    }
					$data['jsHead']['datagridData'] = formatDatagrid($dbData, 'datagridData', $structure['journal_item'], $map);
					break;
				case 17: // Vendor Receipts
				case 18: // Customer Receipts
				case 20: // Vendor Payments
				case 22: // Customer Refunds
					$dgStructure = $this->action=='bulk' ? $this->dgBankingBulk('dgJournalItem') : $this->dgBanking('dgJournalItem');
					$content = $this->action=='bulk' ? jrnlGetBulkData() : jrnlGetPaymentData($rID, $cID, $references);
                    if (sizeof($content['main']) > 0) { foreach ($content['main'] as $field => $value) { $data['journal_main'][$field]['attr']['value'] = $value; } }
					$data['items'] = (sizeof($content['items']) > 0) ? $content['items'] : [];
					// pull out just the pmt rows to build datagrid
					$dgData = [];
                    foreach ($data['items'] as $row) { if ($row['gl_type'] == 'pmt') { $dgData[] = $row; } }
					$map['credit_amount']= ['type'=>'field', 'index'=>'amount'];
//					msgDebug("before formatDatagrid items = ".print_r($dgData, true));
					$data['jsHead']['datagridData'] = formatDatagrid($dgData, 'datagridData', $dgStructure['columns'], $map);
					break;
				default: // for journal ID 3, 4, 6, 7, 9, 10, 12, 13
                    if ($rID) {
                        $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=$rID");
                    } elseif (sizeof($references)) {
                        $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id={$references[0]}");
                    } else {
                        $dbData = []; // should never happen but just in case
                    }
					if ($dbData['currency'] <> getUserCache('profile', 'currency', false, 'USD')) { // convert to posted currency
						$mainFields = ['discount','sales_tax','total_amount'];
                        foreach ($mainFields as $field) { $dbData[$field] = $dbData[$field] * $dbData['currency_rate']; }
					}
					if ($this->action == 'inv') { // clear some fields to convert purchase/sales order or quote to receive/invoice
						$dbData['journal_id']   = $this->journalID;
						$dbData['so_po_ref_id'] = $dbData['id'];
						$dbData['id']           = '';
						$dbData['post_date']    = date('Y-m-d');
                        $dbData['terminal_date']= date('Y-m-d'); // get default based on type
                        if (in_array($this->journalID, [6]))  { 
                            $dbData['purch_order_id']= $dbData['invoice_num'];
                            $dbData['terminal_date'] = localeCalculateDate(date('Y-m-d'), 30);
                        }
						if (in_array($this->journalID, [12])) {
							$soNum = $dbData['invoice_num'];
                            if (getModuleCache('extShipping', 'properties', 'status')) { $dbData['waiting'] = '1'; } // set waiting to ship flag
						}
						$dbData['invoice_num']= '';
// @todo this should be a setting as some want the rep to flow from the Sales Order for commissions while others just care about who fills the order.
//						$dbData['rep_id']     = getUserCache('profile', 'contact_id', false, '0');
					}
					dbStructureFill($data['journal_main'], $dbData);
					// now work the line items
                    if ($rID) {
                        $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID");
                    } elseif (sizeof($references)) {
                        $data['items'] = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id={$references[0]}");
                    } else {
                        $data['items'] = []; // should never happen but just in case
                    }
					if ($dbData['currency'] <> getUserCache('profile', 'currency', false, 'USD')) { // convert to posted currency
						$itemFields = ['debit_amount','credit_amount','full_price'];
						foreach ($data['items'] as $idx => $row) { foreach ($itemFields as $field) {
							$data['items'][$idx][$field] = $data['items'][$idx][$field] * $dbData['currency_rate'];
                        } }
					}
					if ($dbData['so_po_ref_id'] || $this->action == 'inv') { // complex merge the two by item, keep the rest from the rID only
						if ($this->action == 'inv') {
							$sopo = $data['items'];
							foreach ($data['items'] as $idx => $row) {
								unset($data['items'][$idx]['id']);
								unset($data['items'][$idx]['ref_id']);
                                if ($row['gl_type'] == 'itm') { unset($data['items'][$idx]); }
							}
						} else {
							$sopo = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "ref_id={$dbData['so_po_ref_id']}");
						}
						foreach ($sopo as $row) {
                            if ($row['gl_type'] <> 'itm') { continue; } // not an item record, skip
							$inList = false;
							foreach ($data['items'] as $idx => $item) {
								if ($row['item_cnt'] == $item['item_cnt']) {
									$data['items'][$idx]['bal'] = $row['qty'];
									$inList = true;
									break;
								}
							}
							if (!$inList) { // add unposted so/po row, create row with no quantity on this record
								$row['price']        = ($row['credit_amount']+$row['debit_amount'])/$row['qty'];
								$row['credit_amount']= 0;
								$row['debit_amount'] = 0;
								$row['total']        = 0;
								$row['bal']          = $row['qty'];
								$row['qty']          = '';
								$row['item_ref_id']  = $row['id'];
								$row['id']           = '';
								$data['items'][]     = $row;
							}
						}
						$temp = []; // now sort to get item_cnt in order
                        foreach ($data['items'] as $key => $value) { $temp[$key] = $value['item_cnt']; }
						array_multisort($temp, SORT_ASC, $data['items']);
					}
					if (in_array($this->journalID, [4,10])) { // fill qty received for SO and PO
						foreach ($data['items'] as $idx => $row) {
							$data['items'][$idx]['bal'] = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(qty)", "item_ref_id={$row['id']} AND gl_type='itm'", false);
						}
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
					if (!in_array($this->journalID, [2])) {
                        if ($debitCredit=='credit') { $map['credit_amount']= ['type'=>'field','index'=>'total']; }
                        if ($debitCredit=='debit')  { $map['debit_amount'] = ['type'=>'field','index'=>'total']; }
					}
					$data['jsHead']['datagridData'] = formatDatagrid($dbData, 'datagridData', $structure['journal_item'], $map);
					break;
			}
		} else { // new entry
			$data['jsHead']['datagridData'] = "var datagridData = ".json_encode(['total'=>0, 'rows'=>  []]).";\n";
			$dbData = [];
			if (in_array($this->journalID, [3,4,6,13,15,21])) { // pre-set the ship to address
				$dbData = addressLoad($aID=0, $suffix='_s', $ap=true);
                foreach ($dbData as $field => $value) { $data['journal_main'][$field]['attr']['value'] = $value; }
			}
            if (in_array($this->journalID, [17,18,20,22])) { // unset attachments since new entry
                unset($data['divs']['divAttach']);
            }
		}
		$data['terms_text'] = ['label'=>lang('terms'),
			'attr'=>  ['type'=>'text', 'value'=>viewTerms($data['journal_main']['terms']['attr']['value'], true, $this->type), 'readonly'=>'readonly']];
		$data['terms_edit'] = ['icon'=>'settings', 'size'=>'small', 'label'=>lang('terms'),
			'events'=> ['onClick'=>"jsonAction('contacts/main/editTerms&type=$this->type', $cID, jq('#terms').val());"]];
		$data['item_add'] = ['icon'=>'add', 'size'=>'small',
			'events'=> ['onClick' => "itemAddShort();"]];
		$data['override_user']  = ['label'=>'', 'attr'=>  ['type'=>'hidden']];
		$data['override_pass']  = ['label'=>'', 'attr'=>  ['type'=>'hidden']];
		$data['recur_frequency']= ['label'=>'', 'attr'=>  ['type'=>'hidden']];
		$data['item_array']     = ['label'=>'', 'attr'=>  ['type'=>'hidden']];

		$data['totals_methods'] = $this->totals;
        if (in_array($this->journalID, [17,18,19])) { $data['payment_methods'] = getModuleCache('payment', 'methods'); }
		$jsTotals  = "var taxRunning = 0;\n";
        $jsTotals .= "function totalUpdate() {\n";
		$jsTotals .= "  var curTotal = 0;\n";
        foreach ($this->totals as $methID) { $jsTotals .= "  curTotal = totals_{$methID}(curTotal);\n"; }
		$jsTotals .= "}";
		$data['jsHead']['phreebooks']   = "bizDefaults.phreebooks = { journalID:$this->journalID,type:'$this->type' };";
		$data['jsHead']['totalsMethods']= "var totalsMethods = ".json_encode($this->totals).";";
		$data['jsHead']['totalUpdate']  = $jsTotals;
		// build the attachment structure
		$data['attachPath']  = getModuleCache('phreebooks', 'properties', 'attachPath');
		$data['attachPrefix']= $rID && $this->action<>'inv' ? "rID_{$rID}_" : "rID_0_";
		switch ($this->journalID) { // now specialize by journal ID, default orders
			case  0: // General Journal Search
				$data['itemDGSrc'] = false;
				break;
			case  2: // General Journal
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divJournalDetail.php";
				$data['datagrid']['item'] = $this->dgLedger('dgJournalItem');
				unset($data['toolbar']['tbPhreeBooks']['icons']['print']);
				unset($data['toolbar']['tbPhreeBooks']['icons']['payment']);
                if ($rID) { unset($data['toolbar']['tbPhreeBooks']['icons']['recur']); }
				break;
			case  3: // Vendor Quotes
			case  4: // Vendor Purchase Orders
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divOrdersDetail.php";
				$data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'v');
				$data['journal_main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');
				$isWaiting = isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] ? '1' : '0';
				$data['journal_main']['waiting'] = ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]]; // field not used
				break;
			case  6: // Vendor Purchases
			case  7: // Vendor Credit Memo
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divOrdersDetail.php";
				$data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'v');
				$data['journal_main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');
				if (!$rID) { // new order
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
				} elseif (isset($data['journal_main']['closed']['attr']['checked']) && $data['journal_main']['closed']['attr']['checked'] == 'checked') {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
					$data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('paid')."</span><br />";
				} else {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
					$data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('unpaid')."</span><br />";
				}
                if ($this->journalID == 7) { break; } // that's all for VCM, more for Purchases 
                if ($rID || $this->action=='inv') { 
                    $data['datagrid']['item']['source']['actions']['fillAll'] = ['order'=>10, 'html'=>  ['icon'=>'select_all','size'=>'large','hidden'=>$security>1?false:true,'events'=>  ['onClick'=>"phreebooksSelectAll();"]]];
                }
				$data['journal_main']['invoice_num']['tooltip'] = lang('err_gl_invoice_num_empty');
				unset($data['toolbar']['tbPhreeBooks']['icons']['print']);
				$data['journal_main']['purch_order_id']['attr']['readonly'] = 'readonly';
				break;
			case  9: // Customer Quotes
			case 10: // Customer Sales Orders
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divOrdersDetail.php";
				$data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'c');
				$data['journal_main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
				$isWaiting = isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] ? '1' : '0';
				$data['journal_main']['waiting'] = ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]]; // field not used
				break;
			case 12:
			case 13:
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divOrdersDetail.php";
				$data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'c');
				if ($this->action=='inv') {
                    $data['datagrid']['item']['source']['actions']['fillAll'] = ['order'=>10,'html'=>['icon'=>'select_all','size'=>'large','hidden'=>$security>1?false:true,'events'=>['onClick'=>"phreebooksSelectAll();"]]];
                }
                $data['journal_main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
				if (!$rID) { // new order
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
				} elseif (isset($data['journal_main']['closed']['attr']['checked']) && $data['journal_main']['closed']['attr']['checked'] == 'checked') {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
					$data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('paid')."</span><br />";
				} else {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
					$data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('unpaid')."</span><br />";
				}
				$isWaiting = isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] ? '1' : '0';
				if ($this->journalID == 13) {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]];
					break; // that's all for CCM, more for Sales
				}
				if (!$rID) { // new order
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
				} elseif (isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] == 'checked') {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
					$data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('unshipped')."</span><br />";
				} else {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
					$data['journal_msg'] .= '<span style="font-size:20px;color:red">'.lang('shipped')."</span><br />";
				}
				if (!isset($soNum) && isset($data['journal_main']['so_po_ref_id']['attr']['value']) && $data['journal_main']['so_po_ref_id']['attr']['value']) {
					$soNum = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'invoice_num', "id={$data['journal_main']['so_po_ref_id']['attr']['value']}");
				}
				$data['journal_main']['sales_order_num'] = ['label'=>lang('journal_main_invoice_num_10'),'attr'=>  ['value'=>isset($soNum)?$soNum:'','readonly'=>'readonly']];
				break;
			case 14: // Inventory Assemblies
				$data['datagrid']['item'] = $this->dgAssy('dgJournalItem'); // place different as this dg is on the right, not bottom
				unset($data['journal_item']['sku']['attr']['size']);
                $data['journal_item']['sku']['classes']['combogrid'] = 'easyui-combogrid';
                $data['journal_item']['sku']['attr']['data-options'] = "url:'".BIZUNO_AJAX."&p=inventory/main/managerRows&filter=assy&clr=1&bID='+jq('#store_id').val(),
                    width:150, panelWidth:550, delay:500, idField:'sku', textField:'sku', mode:'remote',
                    onClickRow: function (id, data) { 
                            jq('#description').val(data.description_short);
                            jq('#qty').val('1');
                            jq('#gl_account').val(data.gl_inv);
                            jq('#gl_acct_id').val(data.gl_inv);
                            jq('#qty_stock').val(data.qty_stock);
                            jq('#dgJournalItem').datagrid({ url:'".BIZUNO_AJAX."&p=inventory/main/managerBOMList&rID='+data.id });
                            jq('#dgJournalItem').datagrid('reload');
                            assyUpdateBalance();
                        },
                    columns:[[
                            {field:'sku',              title:'".jsLang('sku')."',                width:100},
                            {field:'description_short',title:'".jsLang('description')."',        width:200},
                            {field:'qty_stock',        title:'".jsLang('inventory_qty_stock')."',width:100,align:'right'},
                            {field:'qty_po',           title:'".jsLang('inventory_qty_po')."',   width:100,align:'right'}
                        ]]";
				unset($data['toolbar']['tbPhreeBooks']['icons']['print']);
				unset($data['toolbar']['tbPhreeBooks']['icons']['recur']);
				unset($data['toolbar']['tbPhreeBooks']['icons']['payment']);
                $data['journal_main']['gl_acct_id'] = ['attr'=>['type'=>'hidden']];
				$data['journal_item']['gl_account']['attr']['type'] = 'hidden';
				$data['journal_item']['qty']['label']  = lang('qty_to_assemble');
				$data['journal_item']['qty']['events'] = ['onChange'=>"assyUpdateBalance()"];
				$data['journal_item']['qty']['styles'] = ['text-align'=>'right'];
				$data['qty_stock']= ['label'=>pullTableLabel('inventory', 'qty_stock'), 'styles'=>  ['text-align'=>'right'], 'attr'=>  ['size'=>'10', 'readonly'=>'readonly']];
				$data['balance']         = ['label'=>lang('balance'), 'styles'=>  ['text-align'=>'right'], 'attr'=>  ['size'=>'10', 'readonly'=>'readonly']];
				$isWaiting = isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] ? '1' : '0';
				$data['journal_main']['waiting'] = ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]];
				if ($rID) { // merge the data
					$dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id='$rID'");
					$data['journal_main']['id']['attr']['value']          = $rID;
					$data['journal_main']['store_id']['attr']['value']    = $dbData['store_id'];
					$data['journal_main']['post_date']['attr']['value']   = $dbData['post_date'];
					$data['journal_main']['invoice_num']['attr']['value'] = $dbData['invoice_num'];
					$dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_item', "ref_id='$rID' AND gl_type='asy'");
					$data['journal_item']['gl_account']['attr']['value']  = $dbData['gl_account'];
					$data['journal_item']['sku']['attr']['value']         = $dbData['sku'];
					$data['journal_item']['qty']['attr']['value']         = $dbData['qty'];
					$data['journal_item']['trans_code']['attr']['value']  = $dbData['trans_code'];
					$data['journal_item']['description']['attr']['value'] = $dbData['description'];
					$stock = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'qty_stock', "sku='{$dbData['sku']}'");
					$data['qty_stock']['attr']['value'] = $stock - $dbData['qty'];
					$data['balance']['attr']['value']   = $stock;
				}
				break;
            case 15: // Inventory Store Transfers
                if (!$rID && getModuleCache('extShipping', 'properties', 'status')) { 
                    $data['journal_main']['waiting']['attr']['value'] ='1';
                } // to be seen by ship manager to print label
			case 16: // Inventory Adjustments
                $data['datagrid']['item'] = $this->dgAdjust('dgJournalItem');
                $data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/accInvAdjDetail.php";
                unset($data['toolbar']['tbPhreeBooks']['icons']['print']);
				unset($data['toolbar']['tbPhreeBooks']['icons']['recur']);
				unset($data['toolbar']['tbPhreeBooks']['icons']['payment']);
				$isWaiting = isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] ? '1' : '0';
				$data['journal_main']['waiting'] = ['attr'=>  ['type'=>'hidden', 'value'=>$isWaiting]];
				break;
			/**************************** BANKING ****************************/
			case 17: // Vendor Receipts
			case 18: // Customer Receipts
				unset($data['toolbar']['tbPhreeBooks']['icons']['recur']);
				unset($data['toolbar']['tbPhreeBooks']['icons']['payment']);
				if ($rID || $cID) {
					$temp = new paymentMain();
					$temp->render($data); // add payment methods and continue
				}
				$data['journal_main']['terminal_date']['attr']['type'] = 'hidden';
                if ($rID || $cID) { $data['datagrid']['item'] = $dgStructure; }
				if (isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] == 'checked') {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
				} else {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
				}
				if (isset($data['journal_main']['closed']['attr']['checked']) && $data['journal_main']['closed']['attr']['checked'] == 'checked') {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
				} else {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
				}
				break;
			case 20: // Vendor Payments
			case 22: // Customer Refunds
                if (!$rID) { $data['journal_main']['invoice_num']['attr']['value'] = dbGetValue(BIZUNO_DB_PREFIX."current_status", "next_ref_j20"); }
				$data['journal_main']['terminal_date']['attr']['type'] = 'hidden';
                if ($rID || $cID || $this->action=='bulk') { $data['datagrid']['item'] = $dgStructure; }
				if (isset($data['journal_main']['waiting']['attr']['checked']) && $data['journal_main']['waiting']['attr']['checked'] == 'checked') {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
				} else {
					$data['journal_main']['waiting']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
				}
				if (isset($data['journal_main']['closed']['attr']['checked']) && $data['journal_main']['closed']['attr']['checked'] == 'checked') {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'1']];
				} else {
					$data['journal_main']['closed']= ['attr'=>  ['type'=>'hidden', 'value'=>'0']];
				}
				if ($this->action=='bulk') {
					unset($data['toolbar']['tbPhreeBooks']['icons']['new']);
					unset($data['toolbar']['tbPhreeBooks']['icons']['recur']);
					$data['divs']['divDetail'] = ['order'=>60, 'src'=>BIZUNO_LIB."view/module/phreebooks/accBankBulk.php"];
					$data['form']['frmJournal']['attr']['action'] = BIZUNO_AJAX."&p=phreebooks/main/saveBulk&jID=$this->journalID";
				}
				break;
			/**************************** POS / POP ****************************/
			case 19: // Point of Sale
				$data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'v');
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divOrdersDetail.php";
				$data['journal_main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'customers', 'gl_receivables');
				$data['journal_main']['sales_order_num'] = ['label'=>pullTableLabel('journal_main','invoice_num','10'), 'attr'=>  ['type'=>'input', 'readonly'=>'readonly']];
				break;
			case 21: // Point of Purchase
				$data['datagrid']['item'] = $this->dgOrders('dgJournalItem', 'c');
				$data['itemDGSrc'] = BIZUNO_LIB."view/module/phreebooks/divOrdersDetail.php";
				unset($data['journal_main']['invoice_num']['tooltip']);
				$data['journal_main']['invoice_num']['attr']['value'] = dbGetValue(BIZUNO_DB_PREFIX."current_status", "next_ref_j20");
				$data['journal_main']['gl_acct_id']['attr']['value'] = getModuleCache('phreebooks', 'settings', 'vendors', 'gl_payables');
				break;
		}
        msgDebug("\nFinished edit processing with method_code = ".print_r($data['journal_main']['method_code'], true));
//		if (isset($data['datagrid']['item']['events']['view']) && !$rID) unset ($data['datagrid']['item']['events']['view']); // prevents js error when no data
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Posts a journal entry to the db
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        $request = $_POST;
		$rID = clean('id', 'integer', 'post');
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", $rID?3:2)) { return; }
        if (empty($request['closed']))  { $request['closed']  = '0'; } // test/define all check boxes
        if (empty($request['waiting'])) { $request['waiting'] = '0'; }
		$xChild  = clean('xChild', 'text', 'post');
		$xAction = clean('xAction','text', 'post');
		$structure = [
            'journal_main' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_main', $this->journalID),
			'journal_item' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_item', $this->journalID)];
		// ***************************** START TRANSACTION *******************************
		// Assume all transactions are bulk transactions, or if no contact id set, treat as bulk.
		// iterate through the ref_id's and check for multiple contacts, perhaps pre-processing
		msgDebug("\n  Started order post invoice_num = {$request['invoice_num']} and id = {$rID}");
		dbTransactionStart();
        if (!$rID) { clearUserCache('phreebooks'.$this->journalID); } // clear the manager history for new saves, will then show new post on top.  
		$ledger = new journal($rID, $this->journalID);
		$values = requestData($structure['journal_main'], '', true);
		$values['period']   = calculatePeriod($values['post_date'] ? $values['post_date'] : date('Y-m-d')); // recalc period as post date may have changed
		$values['drop_ship']= isset($request['drop_ship_s']) ? '1' : '0';
		$ledger->main = array_replace($ledger->main, $values);
		// add/update address book, address updates need to be here so recur doesn't keep making new contacts
		// @todo this is duplicated at the start of journal class, if checked add/update here and clear the flag so it's not done again in the journal class
        if (isset($request['AddUpdate_b']) && $request['AddUpdate_b']) { if (!$ledger->updateContact('b')) { return; } }
		if (isset($request['AddUpdate_s']) && $request['AddUpdate_s']) {
            if (!$ledger->main['contact_id_s']) { $ledger->main['contact_id_s'] = $ledger->main['contact_id_b']; }
            if (!$ledger->updateContact('s')) { return; }
		}
		// pull items
		$map = [
            'ref_id'   => ['type'=>'constant', 'value'=>$ledger->main['id']],
			'gl_type'  => ['type'=>'constant', 'value'=>$this->gl_type],
			'post_date'=> ['type'=>'constant', 'value'=>$ledger->main['post_date']]];
		if (!in_array($this->journalID, [2])) {
			$debitCredit = in_array($this->journalID, [3,4,6,13,16,20,21,22]) ? 'debit' : 'credit';
			$map['credit_amount']= $debitCredit=='credit'? ['type'=>'field','index'=>'total'] : ['type'=>'constant','value'=>'0'];
			$map['debit_amount'] = $debitCredit=='debit' ? ['type'=>'field','index'=>'total'] : ['type'=>'constant','value'=>'0'];
		}
		if (in_array($this->journalID, [17,18,20,22])) {
			$map['date_1']     = ['type'=>'field','index'=>'post_date'];
			$map['trans_code'] = ['type'=>'field','index'=>'invoice_num'];
		}
		$ledger->items= requestDataGrid(clean('item_array', 'json', 'post'), $structure['journal_item'], $map);
		$skipList     = ['sku', 'description', 'credit_amount', 'debit_amount']; // if qty=0 or all these are not set or null, row is blank
		$ledger->item = [];
		$item_cnt     = 1;
		foreach ($ledger->items as $row) {
			if (!isBlankRow($row, $skipList)) {
				$row['item_cnt']     = $item_cnt;
                $row['debit_amount'] = roundAmount($row['debit_amount']);
                $row['credit_amount']= roundAmount($row['credit_amount']);
				$ledger->item[] = $row;
			}
			$item_cnt++;
		}
		// check to make sure there is at least one row
        if (sizeof($ledger->item) == 0) { return msgAdd("There are no items to post for this order!"); }
		unset($ledger->items); // don't need anymore
        // a little more pre-processing 
		$description = pullTableLabel('journal_main', 'journal_id', $ledger->main['journal_id']);
		switch ($this->journalID) {
			case  2: $description .= ": {$ledger->item[0]['description']} ..."; break;
			case 14: $description .= " ({$request['qty']}) {$request['sku']} - {$request['description']}";
                $ledger->main['closed'] = '1';
                $ledger->main['closed_date'] = $ledger->main['post_date'];
                break;
            case 15: if (!$this->journalTransfer($ledger->item)) { return; } // dup item rows negated for dest store, then continue to treat like adjustment
			         $description .= " ({$ledger->item[0]['qty']}) {$ledger->item[0]['description']}".(sizeof($ledger->item)>2 ? ' +++' : ''); break;
			case 16: $description .= " ({$ledger->item[0]['qty']}) {$ledger->item[0]['description']}".(sizeof($ledger->item)>1 ? ' +++' : ''); break;
			default: $description .= isset($ledger->main['primary_name_b']) ? ": {$ledger->main['primary_name_b']}" : ''; break;
		}
		$ledger->main['description'] = $description;
		// pull totals
		$current_total = 0;
        foreach ($ledger->item as $row) { $current_total += $row['debit_amount'] + $row['credit_amount']; } // subtotal of all rows
		msgDebug("\nStarting to build total GL rows, starting subtotal = $current_total");
		$ledger->main['sales_tax'] = 0; // clear the sales tax before building new values
		foreach ($this->totals as $methID) {
			require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
            $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
            $fqcn = "\\bizuno\\$methID";
            $totalEntry = new $fqcn($totSet);
            if (method_exists($totalEntry, 'glEntry')) { $totalEntry->glEntry($request, $ledger->main, $ledger->item, $current_total); }
		}
		msgDebug("\nMapped journal rows = ".print_r($ledger->item, true));
		// check calculated total against submitted total, course error check
        // @todo Probably don't need this check anymore as it is handled in the Post class
		if (!in_array($this->journalID, array(2,14,15,16)) && number_format($current_total, 2) <> number_format($ledger->main['total_amount'], 2)) {
			msgDebug("\nFailed comparing calc total =  ".number_format($current_total, 2)." with submitted total = ".number_format($ledger->main['total_amount'], 2));
			return msgAdd(sprintf($this->lang['err_total_not_match'], number_format($current_total, 2), number_format($ledger->main['total_amount'], 2)), 'trap');
		}
		// ************* POST journal entry *************
		if ($ledger->main['recur_id'] > 0) { // if new record, will contain count, if edit will contain recur_id
			$first_invoice_num   = $ledger->main['invoice_num'];
			if ($ledger->main['id']) { // it's an edit, fetch list of affected records to update if roll is enabled
				$affected_ids = $ledger->get_recur_ids($ledger->main['recur_id'], $ledger->main['id']);
                msgDebug("\nAffected ID's for recurring entry: ".print_r($affected_ids, true));
				for ($i = 0; $i < count($affected_ids); $i++) {
					$ledger->main = array_replace($ledger->main, $affected_ids[$i]);
					if ($i > 0) { // Remove row id's for future posts, keep if re-posting single entry
                        for ($j = 0; $j < count($ledger->item); $j++) { $ledger->item[$j]['id'] = ''; }
					}
					msgDebug("\n************ Re-posting recur id = {$ledger->main['id']} ******************");
                    if (!$ledger->Post()) { return; }
                    $ledger->postList = []; // reset the postList to prevent reposting prior recurs
					// test for single post versus rolling into future posts, terminate loop if single post
                    if (empty($request['recur_frequency'])) { break; }
				}
			} else { // it's an insert, fetch the next recur id
				$forceInv = $ledger->main['invoice_num'] != '' ? $ledger->main['invoice_num'] : false;
				$ledger->main['recur_id'] = time(); // time stamp the transaction to link together
				$origPost = clone $ledger;
				for ($i=1; $i<=$request['recur_id']; $i++) { // number of recurs
                    if (!$ledger->Post()) { return; }
                    if ($i == 1) { $first_invoice_num = $ledger->main['invoice_num']; }
                    if ($i == $request['recur_id']) { continue; } // we're done, skip the prep
					// prepare the next post or prepare to exit if finished
					$ledger = clone $origPost;
					switch ($request['recur_frequency']) {
						default:
						case '1': $day_offset = $i*7;  $month_offset = 0; break; // Weekly
						case '2': $day_offset = $i*14; $month_offset = 0; break; // Bi-weekly
						case '3': $day_offset = 0; $month_offset = $i;    break; // Monthly
						case '4': $day_offset = 0; $month_offset = $i*3;  break; // Quarterly
					}
					$ledger->main['post_date']    = localeCalculateDate($ledger->main['post_date'],     $day_offset, $month_offset);
					$ledger->main['terminal_date']= localeCalculateDate($ledger->main['terminal_date'], $day_offset, $month_offset);
					$ledger->main['period']       = calculatePeriod($ledger->main['post_date'], false);
					if ($forceInv) { $forceInv++; $ledger->main['invoice_num'] = $forceInv; }
					foreach ($ledger->item as $key => $row) {
						$ledger->item[$key]['post_date'] = $ledger->main['post_date'];
						$ledger->item[$key]['date_1']    = $ledger->main['terminal_date'];
					}
				}
			}
			// restore the first values to continue with post process
			$ledger->main['invoice_num'] = $first_invoice_num;
		} else {
            if (!$ledger->Post()) { return; }
		}
		// ************* post-POST processing *************
		if (in_array($this->journalID, array(17, 18, 19))) { // process the payment, must be at end since it's hard to undo, no transaction support
			$processor = new paymentMain();
            if (!$processor->sale($ledger->main['method_code'], $ledger)) { return; }
		}
		msgDebug("\n  Committing order invoice_num = {$ledger->main['invoice_num']} and id = {$ledger->main['id']}");
		dbTransactionCommit();
		// ***************************** END TRANSACTION *******************************
        $_POST['rID'] = $ledger->main['id']; // set the record ID as we now have a successfult transaction
        $this->getAttachments('file_attach', $ledger->main['id'], $ledger->main['so_po_ref_id']);
		$invoiceRef = pullTableLabel('journal_main', 'invoice_num', $ledger->main['journal_id']);
		$billName   = isset($ledger->main['primary_name_b']) ? $ledger->main['primary_name_b'] : $ledger->main['description'];
		$journalRef = pullTableLabel('journal_main', 'journal_id', $ledger->main['journal_id']);
		msgAdd(sprintf(lang('msg_gl_post_success'), $invoiceRef, $ledger->main['invoice_num']), 'success');
		msgLog($journalRef.'-'.lang('save')." $invoiceRef ".$ledger->main['invoice_num']." - $billName (rID={$ledger->main['id']}) ".lang('total').": ".viewFormat($ledger->main['total_amount'], 'currency'));
		$jsonAction = "jq('#accJournal').accordion('select',0); jq('#dgPhreeBooks').datagrid('reload'); jq('#divJournalDetail').html('&nbsp;');";
		switch ($xAction) { // post processing extra stuff to do
			case 'payment':
				switch ($this->journalID) {
					case  6: $next_journal = 20; break;
					case  7: $next_journal = 17; break;
					case 12: $next_journal = 18; break;
					case 13: $next_journal = 22; break;
                    default: $next_journal = false;
				}
                if ($next_journal) {
                    $jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit($next_journal, 0, {$ledger->main['contact_id_b']}, 'inv', '', {$ledger->main['id']});";
                }
				break;
            case 'invoice':
				switch ($this->journalID) {
                    case  4: $next_journal =  6; break;
					case 10: $next_journal = 12; break;
                    default: $next_journal = false;
				}
                if ($next_journal) {
                    $jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit($next_journal, {$ledger->main['id']}, 0, 'inv');";
                }
				break;
			case 'journal:12':
				$jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit(12, 0);"; break;
			case 'journal:6':
				$jsonAction = "jq('#dgPhreeBooks').datagrid('reload'); journalEdit(6, 0);"; break;
		}
		switch ($xChild) { // child screens to spawn
			case 'print':
				$formID     = getDefaultFormID($this->journalID);
				$jsonAction .= " winOpen('phreeformOpen', 'phreeform/render/open&group=$formID&date=a&xfld=journal_main.id&xcr=equal&xmin={$ledger->main['id']}&rName=primary_name_b&rEmail=email_b');";
				break;
		}
		$layout = array_replace_recursive($layout, ['rID'=>$ledger->main['id'], 'content'=>  ['action'=>'eval','actionData'=>$jsonAction]]);
	}

	/**
     * Structure to pay bills to multiple vendors from a single page view
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function saveBulk(&$layout=[])
    {
        $request = $_POST;
        if (!$security = validateSecurity('phreebooks', "j20_bulk", 2)) { return; }
		$xChild = clean('xChild', 'text', 'post');
		$structure= [
            'journal_main' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_main', $this->journalID),
			'journal_item' => dbLoadStructure(BIZUNO_DB_PREFIX.'journal_item', $this->journalID)];
		msgDebug("\n  Started order post invoice_num = {$request['invoice_num']}");
		clearUserCache('phreebooks'.$this->journalID); // clear the manager history for new saves, will then show new post on top.
		// organize selections by contact_id create mini $request['item_data'] for each contact
		$rows = clean('item_array', 'json', 'post');
		$cIDs = array();
		foreach ($rows as $row) {
            if (!isset($cIDs[$row['contact_id']]['dsc'])) { $cIDs[$row['contact_id']]['dsc'] = 0; }
            if (!isset($cIDs[$row['contact_id']]['ttl'])) { $cIDs[$row['contact_id']]['ttl'] = 0; }
			$cIDs[$row['contact_id']]['dsc']   += $row['discount'];
			$cIDs[$row['contact_id']]['ttl']   += $row['total'];
			$cIDs[$row['contact_id']]['rows'][] = $row;
		}
		$post_date = clean($request['post_date'], 'date');
		$first_chk = $current_chk = $next_chk = clean($request['invoice_num'], 'text'); // save the first check number for printing
		$first_id  = 0;
		$pmt_total = clean($request['total_amount'], 'currency');
        if (!$first_chk) { return msgAdd("Ref # cannot be blank!"); }
		// ***************************** START TRANSACTION *******************************
		dbTransactionStart();
		foreach ($cIDs as $cID => $items) {
			$address= dbGetRow(BIZUNO_DB_PREFIX.'address_book', "ref_id=$cID AND type='m'");
			$ledger = new journal(0, $this->journalID, $post_date);
			// Fill mains and items
			$request['totals_discount']= viewFormat($items['dsc'], 'currency'); // need to fake out form total for each vendor
			$request['total_amount']   = viewFormat($items['ttl'], 'currency');
            $request['item_array']     = json_encode($items['rows']); // just the rows for this contact
			$current_chk = $next_chk;
			$main = [
                'gl_acct_id'    => $request['gl_acct_id'],
				'invoice_num'   => $current_chk,
				'purch_order_id'=> $request['purch_order_id'],
				'rep_id'        => $request['rep_id'],
				'contact_id_b'  => $cID,
				'address_id_b'  => $address['address_id'],
				'primary_name_b'=> $address['primary_name'],
				'contact_b'     => $address['contact'],
				'address1_b'    => $address['address1'],
				'address2_b'    => $address['address2'],
				'city_b'        => $address['city'],
				'state_b'       => $address['state'],
				'postal_code_b' => $address['postal_code'],
				'country_b'     => $address['country'],
				'telephone1_b'  => $address['telephone1'],
				'email_b'       => $address['email']];
			$ledger->main = array_replace($ledger->main, $main);
			// pull items
			$map = [
                'ref_id'        => ['type'=>'constant','value'=>$ledger->main['id']],
				'gl_type'       => ['type'=>'constant','value'=>$this->gl_type],
				'post_date'     => ['type'=>'constant','value'=>$ledger->main['post_date']],
				'credit_amount' => ['type'=>'constant','value'=>'0'],
				'debit_amount'  => ['type'=>'field',   'index'=>'total'],
				'date_1'        => ['type'=>'field',   'index'=>'inv_date'],
				'trans_code'    => ['type'=>'field',   'index'=>'inv_num']];
			$ledger->items= requestDataGrid($items['rows'], $structure['journal_item'], $map);
			$ledger->item = [];
			$item_cnt     = 1;
			foreach ($ledger->items as $row) {
			    $row['item_cnt'] = $item_cnt;
			    $ledger->item[] = $row;
			    $item_cnt++;
			}
			$ledger->main['description'] = pullTableLabel('journal_main', 'journal_id', $ledger->main['journal_id']);
			$ledger->main['description'].= isset($ledger->main['primary_name_b']) ? ": {$ledger->main['primary_name_b']}" : '';
			// pull totals
			$current_total = 0;
            foreach ($ledger->item as $row) { $current_total += $row['debit_amount'] + $row['credit_amount']; } // subtotal of all rows
			msgDebug("\n\nStarting to build total GL rows, starting subtotal = $current_total");
			foreach ($this->totals as $methID) {
				require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
                $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
                $fqcn = "\\bizuno\\$methID";
                $totalEntry = new $fqcn($totSet);
                if (method_exists($totalEntry, 'glEntry')) { $totalEntry->glEntry($request, $ledger->main, $ledger->item, $current_total); }
			}
			msgDebug("\n\nMapped journal main = ".print_r($ledger->main, true));
			msgDebug("\n\nMapped journal item = ".print_r($ledger->item, true));
			// check calculated total against submitted total, course error check
			if (number_format($current_total, 2) <> number_format($ledger->main['total_amount'], 2)) {
				msgDebug("\nFailed comparing calc total =  ".number_format($current_total, 2)." with submitted total = ".number_format($ledger->main['total_amount'], 2));
				return msgAdd(sprintf($this->lang['err_total_not_match'], number_format($current_total, 2), number_format($ledger->main['total_amount'], 2)), 'trap');
			}
            if (!$ledger->Post()) { return; }
			msgDebug("\n  Committing order invoice_num = $current_chk and id = {$ledger->main['id']}");
			$next_chk++; // increment the invoice number
            if (!$first_id) { $first_id = $ledger->main['id']; } // save the first main ['id'] for printing
		}
		dbTransactionCommit();
		// ***************************** END TRANSACTION *******************************
		$invRef = pullTableLabel('journal_main', 'invoice_num', $ledger->main['journal_id']);
		msgAdd(sprintf(lang('msg_gl_post_success'), $invRef, "$first_chk - $current_chk"), 'success');
		msgLog(lang('phreebooks_manager_bulk').'-'.lang('journal_main_invoice_num_20')." $first_chk - $current_chk ".lang('total').": ".viewFormat($pmt_total, 'currency'));
//		$jsonAction = "journalEdit(6, 0);";
		$jsonAction = "jq('#accJournal').accordion('select',0); jq('#dgPhreeBooks').datagrid('reload'); jq('#divJournalDetail').html('&nbsp;');";
		switch ($xChild) { // post processing extra stuff to do
			case 'print':
				$formID     = getDefaultFormID($this->journalID);
				$jsonAction.= " winOpen('phreeformOpen', 'phreeform/render/open&group=$formID&date=a&xfld=journal_main.id&xcr=range&xmin=$first_id&xmax={$ledger->main['id']}');";
				break;
		}
		$layout = array_replace_recursive($layout, ['rID'=>$ledger->main['id'],'content'=>['action'=>'eval','actionData'=>$jsonAction]]);
	}

	/**
     * Structure to delete a single PhreeBooks journal entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!validateSecurity('phreebooks', "j{$this->journalID}_mgr", 4)) { return; }
		$rID     = clean('rID', 'integer', 'get');
		$delRecur= clean('delAll', 'integer', 'get');
		$delOrd  = new journal($rID);
		// Error Check
        if (!$rID) { return msgAdd(lang('err_copy_name_prompt')); }
		if (getUserCache('profile', 'restrict_period') && $delOrd->period <> getModuleCache('phreebooks', 'fy', 'period')) {
			return msgAdd(lang('ORD_ERROR_DEL_NOT_CUR_PERIOD'));
		}
		switch ($this->journalID) { // some rules checking
			case  4: // Purchase Order Journal
			case 10: // Sales Order Journal
                if ($xID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', "id", "so_po_ref_id=$rID")) { return msgAdd(sprintf($this->lang['err_journal_delete'], "(id=$xID) ".lang('journal_main_journal_id_'.$this->journalID))); }
                break;
			case  6: // Purchase Journal
			case  7: // Vendor Credit Memo Journal
			case 12: // Sales/Invoice Journal
			case 13: // Customer Credit Memo Journal
				// first check for main entries that refer to delete id (credit memos)
                if ($xID = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', "id", "so_po_ref_id=$rID")) { return msgAdd(sprintf($this->lang['err_journal_delete'], "(id=$xID) ".lang('journal_main_journal_id_'.$this->journalID))); }
				// next check for payments that link to deleted id (payments)
                if ($xID = dbGetValue(BIZUNO_DB_PREFIX.'journal_item', "id", "gl_type='pmt' AND item_ref_id=$rID")) { return msgAdd(sprintf($this->lang['err_journal_delete'], "(id=$xID) ".lang('journal_main_journal_id_'.$this->journalID))); }
				break;
			default:
		}
		// *************** START TRANSACTION *************************
		dbTransactionStart();
		// ************* pre-unPOST processing *************
		if (in_array($this->journalID, [17, 18, 19])) { // refund the payment, must be at start since record is deleted with trans_code
			$method = $delOrd->main['method_code'];
            $methPath = getModuleCache('payment', 'methods', $method, 'path');
			if (!$methPath) { return msgAdd("Cannot apply credit since the method is not installed!"); }
            require_once("$methPath{$method}.php");
            $pmtSet = getModuleCache('payment','methods',$method,'settings');
            $fqcn = "\\bizuno\\$method";
            $processor = new $fqcn($pmtSet);
			if ($delOrd->main['post_date'] == date('Y-m-d')) {
                if (method_exists($processor, 'paymentVoid')) { if (!$processor->paymentVoid($delOrd->main['id'])) { return; } }
			} else {
                if (method_exists($processor, 'paymentRefund')) { if (!$processor->paymentRefund($delOrd->main['id'])) { return; } }
			}
		}
		if (isset($delOrd->recur_id) && $delOrd->recur_id > 0 && $delRecur) { // will contain recur_id
			$affected_ids = $delOrd->get_recur_ids($delOrd->recur_id, $delOrd->id);
			foreach  ($affected_ids as $mID) {
                $delRecur = new journal($mID['id']);
                msgDebug("\nunPosting recur id = {$delRecur->main['id']}");
                if (!$delRecur->unPost()) { return dbTransactionRollback(); }
			}
		} else {
            msgDebug("\nunPosting id = {$delOrd->main['id']}");
            if (!$delOrd->unPost()) { return dbTransactionRollback(); }
		}
		dbTransactionCommit();
		// *************** END TRANSACTION *************************
		msgLog(lang('journal_main_journal_id', $this->journalID).' '.lang('delete')." - {$delOrd->main['invoice_num']} (rID=$rID)");
		$files = glob(getModuleCache('phreebooks', 'properties', 'attachPath')."rID_".$rID."_*.*");
        if (is_array($files)) { foreach ($files as $filename) { @unlink($filename); } } // remove attachments
		$layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#accJournal').accordion('select',0); jq('#dgPhreeBooks').datagrid('reload'); jq('#divJournalDetail').html('&nbsp;');"]]);
	}
	
    /**
     * Deletes an attachment tied to a PhreeBooks journal entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function deleteAttach(&$layout=[])
    {
        $io = new io;
        $io->attachDelete($layout, $this->moduleID, $pfxID='rID_', 'journal_main');
    }
    
	/**
     * Retrieves the detailed status for a single contact, typically used as a pop up
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function detailStatus(&$layout=[])
    {
		$cID    = clean('rID', 'integer', 'get');
		$stmt   = dbGetResult("SELECT c.type, c.inactive, c.terms, a.notes FROM ".BIZUNO_DB_PREFIX."contacts"." c JOIN ".BIZUNO_DB_PREFIX."address_book"." a ON c.id=a.ref_id WHERE c.id=$cID AND a.type LIKE '%m'");
		$contact= $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($contact['type'] == 'v') {
			$idx = 'vendors';
			$jQuote = 3;
			$jOrder = 4;
			$jSale  = 6;
			$jRtn   = 7;
		} else {
			$idx = 'customers';
			$jQuote = 9;
			$jOrder = 10;
			$jSale  = 12;
			$jRtn   = 13;
		}
		$new_data = calculate_aging($cID);
		// set the customer/vendor status in order of importance
		if     ($contact['inactive'])                           { $statusBg = 'red';    $statusMsg = lang('inactive');	}
		elseif ($new_data['past_due'] > 0)                      { $statusBg = 'yellow'; $statusMsg = $this->lang['msg_contact_status_past_due']; }
		elseif ($new_data['total'] > $new_data['credit_limit']) { $statusBg = 'yellow'; $statusMsg = $this->lang['msg_contact_status_over_limit']; }
		else                                                    { $statusBg = 'green';  $statusMsg = $this->lang['msg_contact_status_good']; }

		$data = [
            'text' => [
                'alertMsg'=> $statusMsg,
				'alertBg' => $statusBg,
				'age_1'   => '0-30',
				'age_2'   => '31-60',
				'age_3'   => '61-90',
				'age_4'   => $this->lang['over_90'],
				'past_due'=> $new_data['past_due'],
                ],
			'values' => [
                'text_terms'=> $new_data['terms_lang'],
				'bal_1'     => viewFormat($new_data['balance_0'], 'currency'),
				'bal_2'     => viewFormat($new_data['balance_30'], 'currency'),
				'bal_3'     => viewFormat($new_data['balance_60'], 'currency'),
				'bal_4'     => viewFormat($new_data['balance_90'], 'currency'),
				'total'     => viewFormat($new_data['total']),
				'notes'     => $contact['notes'] ? str_replace("\n", "<br />", $contact['notes']) : '&nbsp;',
                ],
			'divs'   => ['divStatus'=> ['order'=>50, 'src'=>BIZUNO_LIB."view/module/phreebooks/divContactStatus.php"]],
            'lang'   => $this->lang,
			'content'=> ['action'=>'window','id'=>'winStatus','title'=>sprintf(lang('tbd_summary'), lang('contacts_type', $contact['type']))],
            ];
		if (sizeof($new_data['inv_orders']) >1) { $data['fields']['inv_orders'] = ['values'=>$new_data['inv_orders'], 
            'attr'=>  ['type'=>'select'], 'events'=>  ['onChange'=>"jq('winStatus').window('close'); journalEdit($jSale, jq(this).val(), 0, 'inv')"]]; }
		if (sizeof($new_data['open_quotes'])>1) { $data['fields']['open_quotes']= ['values'=>$new_data['open_quotes'],
             'attr'=>  ['type'=>'select'], 'events'=>  ['onChange'=>"jq('winStatus').window('close'); journalEdit($jQuote, jq(this).val())"]]; }
		if (sizeof($new_data['open_orders'])>1) { $data['fields']['open_orders']= ['values'=>$new_data['open_orders'],
             'attr'=>  ['type'=>'select'], 'events'=>  ['onChange'=>"jq('winStatus').window('close'); journalEdit($jOrder, jq(this).val())"]]; }
		if (sizeof($new_data['unpaid_inv']) >1) { $data['fields']['unpaid_inv'] = ['values'=>$new_data['unpaid_inv'],
            'attr'=>  ['type'=>'select'], 'events'=>  ['onChange'=>"jq('winStatus').window('close'); journalEdit($jSale, jq(this).val())"]]; }
		if (sizeof($new_data['unpaid_crd']) >1) { $data['fields']['unpaid_crd'] = ['values'=>$new_data['unpaid_crd'],
            'attr'=>  ['type'=>'select'], 'events'=>  ['onChange'=>"jq('winStatus').window('close'); journalEdit($jRtn, jq(this).val())"]]; }
		$layout = array_replace_recursive($layout, $data);
	}
	
	/**
	 * This method formats a db search field to the proper mysql syntax.
	 * @param string $index - Database field name to search
	 * @param array $values - full db path to field, i.e. BIZUNO_DB_PREFIX.journal_main.field_name
	 * @param string $type -  type of field to properly create SQL syntax
	 * @param string $defSel - Default selection value if not set through $_POST
	 * @param string $defMin - Default minimum/equal value if not set through $_POST
	 * @param string $defMax - Default maximum value if not set through $_POST
	 * @return SQL formatted syntax to be used in search query
	 */
	private function searchCriteriaSQL($index, $values=[], $type='text', $defSel='all', $defMin='', $defMax='')
    {
        if (!is_array($values)) { $values = [$values]; }
		$sel = clean($index, ['format'=>'cmd', 'default'=>$defSel], 'post');
		$min = clean($index.'Min', ['format'=>$type, 'default'=>$defMin], 'post');
		$max = clean($index.'Max', ['format'=>$type, 'default'=>$defMax], 'post');
		$sql = [];
		switch ($sel) {
			default:
			case 'all':  break;
            case 'band': foreach ($values as $field) { $sql[] = "($field >= '$min'" . ($max ? " AND $field <= '$max')" : ")"); } break;
            case 'eq':   foreach ($values as $field) { $sql[] = "$field  = '$min'"; }     break;
            case 'not':  foreach ($values as $field) { $sql[] = "$field <> '$min'"; }     break;
            case 'inc':  foreach ($values as $field) { $sql[] = "$field LIKE '%$min%'"; } break;
		}
		msgDebug("\nFinished with index $index and defSel = $defSel and defMin = $defMin and defMax = $defMax and returning sql = ".implode(' AND ', $sql));
		$this->defaults[$index] = "$sel:$min:$max";
		return sizeof($sql) > 1 ? "(".implode(' OR ', $sql).")" : array_shift($sql);
	}

	/**
     * Datagrid manager structure for all PhreeBooks journals
     * @param string $name - datagrid HTML id
     * @param integer $security - Security level to set tool bar and access permissions
     * @return array -Data structure ready to render
     */
    public function dgPhreeBooks($name, $security=0)
    {
        $this->managerSettings();
        $formID     = explode(':', getDefaultFormID($this->journalID));
		$formGroup  = $this->journalID==18 ? 'cust:j19' : $formID[0].':jjrnlTBD'; // special case for customer receipts, make like POS
		$jHidden    = true;
		$jID_values = [];
        $valid_jIDs = [$this->journalID];
		$jrnl_sql   = BIZUNO_DB_PREFIX."journal_main.journal_id={$this->journalID}";
        $jID_statuses = [['id'=>'a','text'=>lang('all')],['id'=>'0','text'=>lang('open')],['id'=>'1','text'=>lang('closed')]];
		$jrnl_status= '';
		switch ($this->journalID) {
			case  0: $valid_jIDs = []; break; // search
			case  3:
			case  4:
			case  6:
			case  7:
				$jHidden = false;
				switch ($this->defaults['jID']) {
					default:$valid_jIDs = [3,4,6,7]; break;
					case 3: $valid_jIDs = [3]; break;
					case 4: $valid_jIDs = [4]; break;
					case 6: $valid_jIDs = [6]; break;
					case 7: $valid_jIDs = [7]; break;
				}
                
				$jID_values = [['id'=>'a','text'=>lang('all')]];
                if (getUserCache('security', 'j3_mgr')) { $jID_values[] = ['id'=>'3','text'=>lang('journal_main_journal_id_3')]; }
                if (getUserCache('security', 'j4_mgr')) { $jID_values[] = ['id'=>'4','text'=>lang('journal_main_journal_id_4')]; }
                if (getUserCache('security', 'j6_mgr')) { $jID_values[] = ['id'=>'6','text'=>lang('journal_main_journal_id_6')]; }
                if (getUserCache('security', 'j7_mgr')) { $jID_values[] = ['id'=>'7','text'=>lang('journal_main_journal_id_7')]; }
				switch ($this->defaults['status']) {
					case '0': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='0'"; break;
					case '1': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='1'"; break;
				}
				break;
			case  9:
			case 10:
			case 12:
			case 13: 
				$jHidden = false;
				switch ($this->defaults['jID']) {
					default: $valid_jIDs = [9,10,12,13]; break;
                    case  9: $valid_jIDs = [9];  break;
					case 10: $valid_jIDs = [10]; break;
					case 12: $valid_jIDs = [12]; break;
					case 13: $valid_jIDs = [13]; break;
				}
				$jID_values = [['id'=>'a','text'=>lang('all')]];
                if (getUserCache('security', 'j9_mgr')) { $jID_values[]  = ['id'=> '9','text'=>lang('journal_main_journal_id_9')];  }
                if (getUserCache('security', 'j10_mgr')) { $jID_values[] = ['id'=>'10','text'=>lang('journal_main_journal_id_10')]; }
                if (getUserCache('security', 'j12_mgr')) { $jID_values[] = ['id'=>'12','text'=>lang('journal_main_journal_id_12')]; }
                if (getUserCache('security', 'j13_mgr')) { $jID_values[] = ['id'=>'13','text'=>lang('journal_main_journal_id_13')]; }
				switch ($this->defaults['status']) {
					case '0': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='0'"; break;
					case '1': $jrnl_status = BIZUNO_DB_PREFIX."journal_main.closed='1'"; break;
					default:  break;
				}
				break;
			default:
		}
        $jrnl_sql = $this->setAllowedJournals($valid_jIDs);
		$data  = [
            'id'  => $name,
			'rows'=> $this->defaults['rows'],
			'page'=> $this->defaults['page'],
			'attr'=> [
                'url'      => BIZUNO_AJAX."&p=phreebooks/main/managerRows&jID=$this->journalID&type=$this->type",
				'toolbar'  => "#{$name}Toolbar",
				'pageSize' => getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField'  => 'id',
				'xtraField'=> [['key'=>'jrnlTBD','value'=>"journal_id"],['key'=>'cIDTBD','value'=>"contact_id_b"]]],
			'events' => [
                'onLoadSuccess'=> "function(data) { 
                    jq('#search').focus();
                    jq('#{$name}Toolbar input').keypress(function (e) { if (e.keyCode == 13) { {$name}Reload(); } }); }",
				'onDblClickRow'=> "function(idx, data){ journalEdit(data.journal_id, data.id); }"],
			'source' => [
                'tables' => ['journal_main'=>['table'=>BIZUNO_DB_PREFIX.'journal_main']],
				'search' => [
                    BIZUNO_DB_PREFIX.'journal_main.description',
					BIZUNO_DB_PREFIX.'journal_main.primary_name_b',
					BIZUNO_DB_PREFIX.'journal_main.primary_name_s',
					BIZUNO_DB_PREFIX.'journal_main.postal_code_b',
					BIZUNO_DB_PREFIX.'journal_main.postal_code_s',
					BIZUNO_DB_PREFIX.'journal_main.invoice_num',
					BIZUNO_DB_PREFIX.'journal_main.purch_order_id',
					BIZUNO_DB_PREFIX.'journal_main.total_amount'],
				'actions' => [
                    'newJournal'=>['order'=>10,'html'=>['icon'=>'new',    'events'=>['onClick'=>"journalEdit($this->journalID, 0);"]]],
                    'clrSearch' =>['order'=>50,'html'=>['icon'=>'refresh','events'=>['onClick'=>"jq('#search').val(''); jq('#jID').val('a'); jq('#status').val('a'); jq('#period').val('".getModuleCache('phreebooks','fy','period')."'); {$name}Reload();"]]]],
				'filters' => [
                    'period'=> ['order'=>10, 'sql'=>$this->defaults['period']=='all' ? '' : BIZUNO_DB_PREFIX."journal_main.period={$this->defaults['period']}",
						'html'=>  ['label'=>lang('period'), 'values'=>dbPeriodDropDown(), 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['period']]]],
					"jID" => ['order'=>20, 'sql'=>$jrnl_sql, 'hidden'=>$jHidden,
						'html' => ['label'=>lang('journal_main_journal_id'), 'values'=>$jID_values, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['jID']]]],
					"status" => ['order'=>30, 'sql'=>$jrnl_status, 'hidden'=>$jHidden,
						'html' => ['label'=>lang('status'), 'values'=>$jID_statuses, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['status']]]],
					'search'=> ['order'=>90,'html'=>  ['label'=>lang('search'),'attr'=>  ['value'=>$this->defaults['search']]]]],
				'sort' => ['s0'=>  ['order'=>10, 'field'=>("{$this->defaults['sort']} {$this->defaults['order']}, ".BIZUNO_DB_PREFIX."journal_main.id DESC")]]],
			'columns' => [
                'id'           => ['order'=>0, 'field'=>'DISTINCT '.BIZUNO_DB_PREFIX.'journal_main.id','attr'=>['hidden'=>true]],
				'contact_id_b' => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'journal_main.contact_id_b',  'attr'=>['hidden'=>true]],
				'journal_id'   => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'journal_main.journal_id',    'attr'=>['hidden'=>true]],
				'currency'     => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'journal_main.currency',      'attr'=>['hidden'=>true]],
				'currency_rate'=> ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'journal_main.currency_rate', 'attr'=>['hidden'=>true]],
				'attach'       => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'journal_main.attach',        'attr'=>['hidden'=>true]],//'alias'=>'id', 'format'=>'attch:'.getModuleCache('phreebooks', 'properties', 'attachPath')."rID_idTBD_"],
				'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>160],
					'events' => ['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
					'actions'=> [
                        'print'      => ['order'=>10,'icon'=>'print','size'=>'small',
							'events' => ['onClick'=>"var jID=jq('#journal_id').val(); winOpen('phreeformOpen', 'phreeform/render/open&group={$formGroup}&date=a&xfld=journal_main.id&xcr=equal&xmin=idTBD&rName=primary_name_b&rEmail=email_b');"]],
						'edit'       => ['order'=>20,'icon'=>'edit', 'size'=>'small',  'label'=>lang('edit'),
							'events' => ['onClick' => "journalEdit(jrnlTBD, idTBD);"]],
						'trash'      => ['order'=>30,'icon'=>'trash','size'=>'small','label'=>lang('delete'),'hidden'=>$security==4?false:true,
							'display'=>"(row.journal_id!='12' && row.journal_id!='6') || (row.journal_id=='12' && (row.closed=='0' || row.total_amount==0)) || (row.journal_id=='6' && (row.closed=='0' || row.total_amount==0))",
							'events' => ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreebooks/main/delete&jID={$this->journalID}', idTBD);"]],
						'toggle'     => ['order'=>40,'icon'=>'toggle','size'=>'small','label'=>lang('toggle_status'),'hidden'=> $security>2?false:true, 
							'events' => ['onClick' => "jsonAction('phreebooks/main/toggleWaiting&jID=jrnlTBD', idTBD);"],
							'display'=> "row.journal_id=='4' || row.journal_id=='10' || row.journal_id=='12'"],
						'dates'      => ['order'=>50,'icon'=>'date','size'=>'small','label'=>$this->lang['expected_delivery_dates'],
							'events' => ['onClick' => "windowEdit('phreebooks/main/deliveryDates&rID=idTBD', 'winDelDates', '".$this->lang['expected_delivery_dates']."', 500, 400);"],
							'display'=> "row.journal_id=='4' || row.journal_id=='10'"],
						'purchase'   => ['order'=>80,'icon'=>'purchase','size'=>'small','label'=>$this->lang['fill_purchase'],
							'events' => ['onClick' => "journalEdit(6, 0, cIDTBD, 'inv', '', idTBD);"],
							'display'=> "row.closed=='0' && (row.journal_id=='3' || row.journal_id=='4')"],
						'sale'       => ['order'=>80,'icon'=>'sales', 'size'=>'small',  'label'=>$this->lang['fill_sale'],
							'events' => ['onClick' => "journalEdit(12, 0, cIDTBD, 'inv', '', idTBD);"],
							'display'=> "row.closed=='0' && (row.journal_id=='9' || row.journal_id=='10')"],
						'vcred'     => ['order'=>80,'icon'=>'credit', 'size'=>'small',  'label'=>$this->lang['create_credit'],
							'events' => ['onClick' => "setCrJournal(jrnlTBD, cIDTBD, idTBD);"],
							'display'=> "row.closed=='0' && (row.journal_id=='6' || row.journal_id=='12')"],
						'payment'    => ['order'=>80,'icon'=>'payment','size'=>'small','label'=>lang('payment'),
							'events' => ['onClick' => "setPmtJournal(jrnlTBD, cIDTBD, idTBD);"],
							'display'=> "row.closed=='0' && (row.journal_id=='6' || row.journal_id=='7' || row.journal_id=='12' || row.journal_id=='13')"],
						'attach'     => ['order'=>90,'icon'=>'attachment','size'=>'small','display'=>"row.attach=='1'",
							'events' => ['onClick'=>"icnAction='';"]]], // info only
                    ],
				'post_date' => ['order'=>10, 'field'=>BIZUNO_DB_PREFIX.'journal_main.post_date', 'format'=>'date',
					'label' => pullTableLabel('journal_main', 'post_date'),'attr'=>['width'=>100, 'sortable'=>true, 'resizable'=>true]],
				'invoice_num' => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX.'journal_main.invoice_num',
					'label' => pullTableLabel('journal_main', 'invoice_num', $this->journalID),'attr'=>['width'=>100, 'sortable'=>true, 'resizable'=>true]],
				'so_po_ref_id' => ['order'=>25, 'field'=>BIZUNO_DB_PREFIX.'journal_main.so_po_ref_id','format'=>'storeID',
					'label' => pullTableLabel('journal_main', 'so_po_ref_id', $this->journalID),
					'attr'  => ['width'=>120, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [15]) ? false : true]],
				'purch_order_id' => ['order'=>30, 'field'=>BIZUNO_DB_PREFIX.'journal_main.purch_order_id',
					'label' => pullTableLabel('journal_main', 'purch_order_id', $this->journalID),
					'attr'  => ['width'=>120, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [2,14,15,16]) ? true : false]],
				'description' => ['order'=>40, 'field'=>BIZUNO_DB_PREFIX.'journal_main.description',
					'label' => pullTableLabel('journal_main', 'description', $this->journalID),
					'attr'  => ['width'=>240, 'sortable'=>true, 'resizable'=>true, 'hidden'=> !in_array($this->journalID, [0,2,14,15,16]) ? true : false]],
				'primary_name_b' => ['order'=>50, 'field'=>BIZUNO_DB_PREFIX.'journal_main.primary_name_b',
					'label' => pullTableLabel('address_book', 'primary_name', $this->type),
					'attr'  => ['width'=>240, 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [0,2,14,15,16]) || $this->type=='e' ? true : false]],
				'email_b' => ['order'=>60, 'field'=>BIZUNO_DB_PREFIX.'journal_main.email_b',
					'label' => pullTableLabel('contacts', 'email', $this->type),
					'attr'  => ['width'=>220, 'sortable'=>true, 'resizable'=>true, 'hidden'=>true]],
				'total_amount'=> ['order'=>70, 'field' => BIZUNO_DB_PREFIX.'journal_main.total_amount',
					'label' => pullTableLabel('journal_main', 'total_amount'), 'format'=>'currency', 
					'attr'  => ['width'=>80, 'align'=>'right', 'sortable'=>true, 'resizable'=>true, 'hidden'=> in_array($this->journalID, [14,15,16]) ? true : false]],
				'closed'    => ['order'=>90, 'field'=>BIZUNO_DB_PREFIX.'journal_main.closed',
					'label' => lang('status'),
					'attr'  => ['width'=>60, 'align'=>'center', 'resizable'=>true, 'hidden'=>in_array($this->journalID,[2,14,15,16])?true:false]],
                ],
            ];
		switch ($this->journalID) {
			case 0: // search journal
				$data['events']['onDblClickRow'] = "function(rowIndex, rowData){ tabOpen('_blank', 'phreebooks/main/manager&rID='+rowData.id); }";
				$data['source']['tables']['journal_item'] = ['table'=>BIZUNO_DB_PREFIX.'journal_item', 'join'=>'JOIN', 'links'=>BIZUNO_DB_PREFIX."journal_main.id=".BIZUNO_DB_PREFIX."journal_item.ref_id"];
				$data['source']['search'][] = BIZUNO_DB_PREFIX.'journal_main.id';
				$data['source']['search'][] = BIZUNO_DB_PREFIX.'journal_item.sku';
				unset($data['source']['actions']['newJournal']);
				unset($data['source']['actions']['clrSearch']);
				unset($data['columns']['id']['attr']['hidden']);
				$data['columns']['action']['actions']['edit']['events']['onClick'] = "tabOpen('_blank', 'phreebooks/main/manager&rID=idTBD');";
				unset($data['columns']['action']['actions']['print']);
				$data['columns']['journal_id'] = ['order'=>15, 'field'=>BIZUNO_DB_PREFIX.'journal_main.journal_id', 'format'=>'j_desc',
					'label' => pullTableLabel('journal_main', 'journal_id'), 'attr'=>  ['width'=>120, 'resizable'=>true]];

				$journalID = clean('journalID', 'integer', 'post');
				$html = ['label'=>pullTableLabel('journal_main', 'journal_id'), 'values'=>$this->selJournals(), 'break'=>true, 'attr'=>  ['type'=>'select','value'=>$journalID]];
                if     ($journalID)              { $sql = BIZUNO_DB_PREFIX."journal_main.journal_id=$journalID"; }
                elseif ($this->blocked_journals) { $sql = BIZUNO_DB_PREFIX."journal_main.journal_id NOT IN ($this->blocked_journals)"; }
                else                             { $sql = ''; }
				$data['source']['filters']['journalID']= ['order'=>55, 'sql'=>$sql, 'html'=>$html];

				$data['source']['filters']['hdcol1'] = ['order'=>56, 'html'=>['attr'=>['type'=>'span','value'=>lang('fieldname').' - ']]];
				$data['source']['filters']['hdcol2'] = ['order'=>57, 'html'=>['attr'=>['type'=>'span','value'=>lang('operator').' - ']]];
				$data['source']['filters']['hdMin']  = ['order'=>58, 'html'=>['attr'=>['type'=>'span','value'=>lang('minimum').' - ']]];
				$data['source']['filters']['hdMax']  = ['order'=>59, 'html'=>['attr'=>['type'=>'span','value'=>lang('maximum')], 'break'=>true]];

				$sql = $this->searchCriteriaSQL('postDate', BIZUNO_DB_PREFIX.'journal_main.post_date', 'date', 'band', getModuleCache('phreebooks', 'fy', 'period_start'), getModuleCache('phreebooks', 'fy', 'period_end'));
				$temp = explode(':', $this->defaults['postDate']);
                if (!$temp[0]) { $temp[0] = 'band'; }
				$html = ['label'=>pullTableLabel('journal_main', 'post_date'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$temp[0]]];
				$data['source']['filters']['postDate']    = ['order'=>60, 'sql'=>$sql, 'html'=>$html];
				$data['source']['filters']['postDateMin'] = ['order'=>61, 'html'=>  ['attr'=>  ['type'=>'date', 'value'=>$temp[1]]]];
				$data['source']['filters']['postDateMax'] = ['order'=>62, 'html'=>  ['break'=>true, 'attr'=>  ['type'=>'date', 'value'=>$temp[2]]]];

				$sql = $this->searchCriteriaSQL('refID', [BIZUNO_DB_PREFIX.'journal_main.invoice_num', BIZUNO_DB_PREFIX.'journal_main.purch_order_id']);
				$temp = explode(':', $this->defaults['refID']);
				$html = ['label'=>pullTableLabel('journal_main', 'invoice_num'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$temp[0]]];
				$data['source']['filters']['refID']    = ['order'=>63, 'sql'=>$sql, 'html'=>$html];
				$data['source']['filters']['refIDMin'] = ['order'=>64, 'html'=>  ['attr'=>  ['value'=>$temp[1]]]];
				$data['source']['filters']['refIDMax'] = ['order'=>65, 'html'=>  ['break'=>true, 'attr'=>  ['value'=>$temp[2]]]];

				$sql = $this->searchCriteriaSQL('contactID', BIZUNO_DB_PREFIX.'journal_main.primary_name_b');
				$temp = explode(':', $this->defaults['contactID']);
				$html = ['label'=>pullTableLabel('address_book', 'primary_name'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$temp[0]]];
				$data['source']['filters']['contactID']    = ['order'=>66, 'sql'=>$sql, 'html'=>$html];
				$data['source']['filters']['contactIDMin'] = ['order'=>67, 'html'=>  ['attr'=>  ['value'=>$temp[1]]]];
				$data['source']['filters']['contactIDMax'] = ['order'=>68, 'html'=>  ['break'=>true, 'attr'=>  ['value'=>$temp[2]]]];

				$sqlSKU = $this->searchCriteriaSQL('sku', BIZUNO_DB_PREFIX.'journal_item.sku');
				$tempSKU = explode(':', $this->defaults['sku']);
				$htmlSKU = ['label'=>pullTableLabel('journal_item', 'sku'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$tempSKU[0]]];
				$data['source']['filters']['sku']    = ['order'=>69, 'sql'=>$sqlSKU, 'html'=>$htmlSKU];
				$data['source']['filters']['skuMin'] = ['order'=>70, 'html'=>  ['attr'=>  ['value'=>$tempSKU[1]]]];
				$data['source']['filters']['skuMax'] = ['order'=>71, 'html'=>  ['break'=>true, 'attr'=>  ['value'=>$tempSKU[2]]]];

				$sql = $this->searchCriteriaSQL('amount',  [BIZUNO_DB_PREFIX.'journal_main.total_amount', BIZUNO_DB_PREFIX.'journal_item.debit_amount', BIZUNO_DB_PREFIX.'journal_item.credit_amount']);
				$temp = explode(':', $this->defaults['amount']);
				$html = ['label'=>lang('amount'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$temp[0]]];
				$data['source']['filters']['amount']    = ['order'=>72, 'sql'=>$sql, 'html'=>$html];
				$data['source']['filters']['amountMin'] = ['order'=>73, 'html'=>  ['attr'=>  ['value'=>$temp[1]]]];
				$data['source']['filters']['amountMax'] = ['order'=>74, 'html'=>  ['break'=>true, 'attr'=>  ['value'=>$temp[2]]]];

				$sql = $this->searchCriteriaSQL('glAcct',  [BIZUNO_DB_PREFIX.'journal_main.gl_acct_id', BIZUNO_DB_PREFIX.'journal_item.gl_account']);
				$temp = explode(':', $this->defaults['glAcct']);
				$html = ['label'=>pullTableLabel('journal_main', 'gl_acct_id'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$temp[0]]];
				$data['source']['filters']['glAcct']    = ['order'=>75, 'sql'=>$sql, 'html'=>$html];
				$data['source']['filters']['glAcctMin'] = ['order'=>76, 'html'=>  ['attr'=>  ['value'=>$temp[1]]]];
				$data['source']['filters']['glAcctMax'] = ['order'=>77, 'html'=>  ['break'=>true, 'attr'=>  ['value'=>$temp[2]]]];

				$sql = $this->searchCriteriaSQL('rID', BIZUNO_DB_PREFIX.'journal_main.id');
				$temp = explode(':', $this->defaults['rID']);
				$html = ['label'=>lang('users_admin_id'), 'values'=>selChoices(), 'attr'=>  ['type'=>'select','value'=>$temp[0]]];
				$data['source']['filters']['rID']    = ['order'=>78, 'sql'=>$sql, 'html'=>$html];
				$data['source']['filters']['rIDMin'] = ['order'=>79, 'html'=>  ['attr'=>  ['value'=>$temp[1]]]];
				$data['source']['filters']['rIDMax'] = ['order'=>80, 'html'=>  ['break'=>true, 'attr'=>  ['value'=>$temp[2]]]];
				unset($data['source']['filters']['period']);
				break;
			case 2:
				unset($data['columns']['closed']);
				break;
			case  3:
			case  4:
			case  6:
			case  7:
                if (getUserCache('security', 'j20_mgr') < 2) { unset($data['columns']['action']['actions']['payment']); }
				$data['columns']['invoice_num']['events'] = ['styler'=>"function(value,row,index) { 
						if      (row.journal_id=='3') { return {style:'background-color:lightblue'}; } 
						else if (row.journal_id=='4') { return {style:'background-color:orange'}; } 
						else if (row.journal_id=='6') { return {style:'background-color:lightgreen'}; } 
						else if (row.journal_id=='7') { return {style:'background-color:pink'}; } 
					}"];
				$data['columns']['closed']['events']['formatter'] = " function(value,row,index){
						if      (row.journal_id=='3') { return value=='1' ? '".jsLang('closed')."' : ''; }
						else if (row.journal_id=='4') { return value=='1' ? '".jsLang('closed')."' : ''; }
						else if (row.journal_id=='6') { return value=='1' ? '".jsLang('paid')."' : ''; }
						else if (row.journal_id=='7') { return value=='1' ? '".jsLang('paid')."' : ''; }
					}";
				$data['columns']['closed']['events']['styler'] = "function(value,row,index) {
						if      (row.journal_id=='4' && row.waiting==1) { return {style:'background-color:yellowgreen'}; }
						else if (row.journal_id=='6' && row.waiting==1) { return {class:'journal-waiting'}; }
					}";
				$data['footnotes'] = ['status'=>lang('journal_main_journal_id').': 
						<span style="background-color:lightgreen">'.lang('journal_main_journal_id_6').'</span>
						<span style="background-color:orange">'    .lang('journal_main_journal_id_4').'</span>
						<span style="background-color:lightblue">' .lang('journal_main_journal_id_3').'</span>
						<span style="background-color:pink">'      .lang('journal_main_journal_id_7').'</span>',
					'jType'=>'<br />'.lang('status').': 
						<span style="background-color:yellowgreen">'.lang('confirmed').'</span>
						<span class="journal-waiting">'.lang('journal_main_waiting').'</span>',
                    ];
				break;
			case  9:
			case 10:
			case 12:
			case 13:
                if (getUserCache('security', 'j18_mgr') < 2) { unset($data['columns']['action']['actions']['payment']); }
				$data['columns']['invoice_num']['events'] = ['styler'=>"function(value,row,index) { 
						if      (row.journal_id== '9') { return {style:'background-color:lightblue'}; } 
						else if (row.journal_id=='10') { return {style:'background-color:orange'}; } 
						else if (row.journal_id=='12') { return {style:'background-color:lightgreen'}; } 
						else if (row.journal_id=='13') { return {style:'background-color:pink'}; } 
					}"];
				$data['columns']['closed']['events']['formatter'] = " function(value,row,index){
						if      (row.journal_id=='9')  { return value=='1' ? '".jsLang('closed')."' : ''; }
						else if (row.journal_id=='10') { return value=='1' ? '".jsLang('closed')."' : ''; }
						else if (row.journal_id=='12') { return value=='1' ? '".jsLang('paid')."' : ''; }
						else if (row.journal_id=='13') { return value=='1' ? '".jsLang('paid')."' : ''; }
					}";
				$data['columns']['closed']['events']['styler'] = "function(value,row,index) {
                    if      (row.journal_id=='10' && row.waiting==1) { return {style:'background-color:yellowgreen'}; }
					else if (row.journal_id=='12' && row.waiting==1) { return {class:'journal-waiting'}; }
					}";
				$data['footnotes'] = ['status'=>lang('journal_main_journal_id').': 
						<span style="background-color:lightgreen">'.lang('journal_main_journal_id_12').'</span>
						<span style="background-color:orange">'    .lang('journal_main_journal_id_10').'</span>
						<span style="background-color:lightblue">' .lang('journal_main_journal_id_9') .'</span>
						<span style="background-color:pink">'      .lang('journal_main_journal_id_13').'</span>',
					'jType'=>'<br />'.lang('status').':
                        <span style="background-color:yellowgreen">'.lang('confirmed').'</span>
                        <span class="journal-waiting">'.lang('unshipped').'</span>',
                    ];
				break;
			case 14:
				unset($data['columns']['closed']);
				break;
			case 17:
			case 18:
			case 20:
			case 22:
				$data['columns']['closed']['events']['formatter'] = "function(value) { return value=='1'?'".jsLang('reconciled')."':''; }";
				break;
		}
        if (getUserCache('profile', 'restrict_user', false, 0)) { // see if user restrictions are in place
            $uID = getUserCache('profile', 'contact_id', false, 0);
            $data['source']['filters']['restrict_user'] = ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."journal_main.rep_id='$uID'"];
        }
		return $data;
	}

	/**
     * Creates the datagrid structure for general ledger items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    private function dgLedger($name)
    {
		$data = [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => ['toolbar'=>"#{$name}Toolbar",'rownumbers'=> true,'idField'=>'id'],
			'events' => [
                'data'         => "datagridData",
				'onLoadSuccess'=> "function(row) { totalUpdate(); }",
				'onClickCell'  => "function(rowIndex) {
					switch (icnAction) {
						case 'trash': jq('#$name').edatagrid('destroyRow', rowIndex); break;
					}
					icnAction = '';
				}",
				'onClickRow'   => "function(rowIndex, row) { curIndex = rowIndex; }",
				'onBeginEdit'  => "function(rowIndex, row) { glEditing(rowIndex); }",
				'onDestroy'    => "function(rowIndex, row) { totalUpdate(); curIndex = undefined; }",
                ],
			'source' => ['actions'=>['newItem'=>['order'=>10,'html'=>['icon'=>'add','size'=>'large','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns'=> [
                'id'  => ['order'=>0, 'attr'=>  ['hidden'=>true]],
				'qty' => ['order'=>0, 'attr'=>  ['hidden'=>true, 'value'=>1]],
				'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>40],
					'events'  => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions' => ['delete' => ['icon'=>'trash', 'size'=>'small', 'order'=>20, 'events'=>  ['onClick'=>"icnAction='trash';"]]]],
				'gl_account' => ['order'=>20, 'label'=>pullTableLabel('journal_item', 'gl_account', $this->journalID),
					'attr' => ['width'=>120, 'resizable'=>true, 'align'=>'center'],
					'events'=>  ['editor'=>dgHtmlGLAcctData()]],
				'description' => ['order'=>30, 'label'=>lang('description'), 'attr'=>  ['width'=>400, 'editor'=>'text', 'resizable'=>true]],
				'debit_amount' => ['order'=>40, 'label'=>pullTableLabel('journal_item', 'debit_amount'),
					'attr'  => ['width'=>150, 'resizable'=>true, 'align'=>'right'],
					'events'=> ['editor'=>"{type:'numberbox',value:0,options:{onChange:function(){ glCalc('debit'); } } }",
					'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'credit_amount' => ['order'=>50, 'label'=>pullTableLabel('journal_item', 'credit_amount'),
					'attr'  => ['width'=>150, 'resizable'=>true, 'align'=>'right'],
					'events'=> ['editor'=>"{type:'numberbox',value:0,options:{onChange:function(){ glCalc('credit'); } } }",
					'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'notes' => ['order'=>90, 'label'=>lang('notes'), 'attr'=>  ['width'=>150, 'resizable'=>true],
					'events' => ['editor'=>"{type:'text'}"]]],
            ];
		return $data;
	}
	
	/**
     * Creates the datagrid structure for customer/vendor order items
     * @param string $name - DOM field name
     * @param char $type - choices are c (customers) or v (vendors)
     * @return array - datagrid structure
     */
	public function dgOrders($name, $type) {
		$on_hand    = pullTableLabel('inventory', 'qty_stock');
		$gl_account = $type=='v' ? 'gl_inv'    : 'gl_sales';
		$inv_field  = $type=='v' ? 'item_cost' : 'full_price';
		$inv_title  = $type=='v' ? lang('cost'): lang('price');
		$hideItemTax= true;
        foreach ($this->totals as $methID) { if ($methID == 'tax_item') { $hideItemTax = false; } }
		$data = [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => [
                'toolbar'     => "#{$name}Toolbar",
				'rownumbers'  => true,
				'idField'     => 'id',
				'singleSelect'=> true,
				'fitColumns'  => true],
			'events' => [
                'data'         => "datagridData",
				'onLoadSuccess'=> "function(row) { totalUpdate(); }",
				'onClickCell'  => "function(rowIndex) {
					switch (icnAction) {
						case 'trash':    jq('#$name').edatagrid('destroyRow', rowIndex); break;
						case 'price':    inventoryGetPrice(rowIndex, '$type'); break;
						case 'settings': inventoryProperties(rowIndex);        break;
					}
					icnAction = '';
				}",
				'onClickRow'   => "function(rowIndex, row) { curIndex = rowIndex; }",
                'onBeforeEdit' => "function(rowIndex) {
    var edtURL = jq(this).edatagrid('getColumnOption','sku');
    edtURL.editor.options.url = '".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&bID='+jq('#store_id').val();
}",
				'onBeginEdit'  => "function(rowIndex) { ordersEditing(rowIndex); }",
				'onDestroy'    => "function(rowIndex) { totalUpdate(); curIndex = undefined; }",
				'onAdd'        => "function(rowIndex) { setFields(rowIndex); }",
//				'view'         => "detailview", //breaks edatagrid 'edit', may be a sequencing issue, otherwise reproduce on easyui website for them to look at
        ],
			'source' => ['actions'=>['newItem'=>['order'=>10,'html'=>['icon'=>'add','size'=>'large','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns'=> [
                'id'            => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'ref_id'        => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'item_ref_id'   => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'pkg_length'    => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'pkg_width'     => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'pkg_height'    => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'inventory_type'=> ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'item_weight'   => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'qty_stock'     => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'trans_code'    => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'attach'        => ['order'=>0, 'attr'=>  ['hidden'=>'true', 'value'=>'0']],
				'date_1'        => ['order'=>0, 'attr'=>  ['hidden'=>'true']],
				'action'        => ['order'=>1, 'attr'=>  ['width'=>80], 'label'=>lang('action'),
					'events'  => ['formatter'=>"function(value,row,index) { return {$name}Formatter(value,row,index); }"],
					'actions' => [
                        'trash'   => ['icon'=>'trash',   'order'=>20,'size'=>'small','events'=>  ['onClick'=>"icnAction='trash';"],
							'display'=>"typeof row.item_ref_id==='undefined' || row.item_ref_id=='0' || row.item_ref_id==''"],
						'price'   => ['icon'=>'price',   'order'=>40,'size'=>'small','events'=>  ['onClick'=>"icnAction='price';"]],
						'settings'=> ['icon'=>'settings','order'=>60,'size'=>'small','events'=>  ['onClick'=>"icnAction='settings';"]]]],
				'sku'=> ['order'=>30, 'label'=>pullTableLabel('journal_item', 'sku', $this->journalID),
					'attr' => ['width'=>150, 'sortable'=>true, 'resizable'=>true, 'align'=>'center', 'value'=>''],
					'events'=>  ['editor'=>"{type:'combogrid',options:{ url:'".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1',
						width:150, panelWidth:550, delay:500, idField:'sku', textField:'sku', mode:'remote',
                        onLoadSuccess: function () {
                            var skuEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'sku'});
                            var g = jq(skuEditor.target).combogrid('grid');
                            var r=g.datagrid('getData');
							if (r.rows.length==1) { var cbValue = jq(skuEditor.target).combogrid('getValue');
                                if (!cbValue) { return; }
								if (r.rows[0].sku==cbValue || r.rows[0].upc_code==cbValue) { 
                                    jq(skuEditor.target).combogrid('hidePanel'); orderFill(r.rows[0], '$type');
                                }
							}
						},
						onClickRow: function (idx, data) { orderFill(data, '$type'); },
						columns:[[{field:'sku', title:'".jsLang('sku')."', width:100},
							{field:'description_short',title:'".jsLang('description')."', width:200},
							{field:'qty_stock', title:'$on_hand', width:90,align:'right'},
							{field:'$inv_field', title:'$inv_title', width:90,align:'right'},
							{field:'$gl_account', hidden:true}, {field:'item_weight', hidden:true}]]}}"]],
				'description' => ['order'=>40, 'label'=>lang('description'),'attr'=>['width'=>400,'editor'=>'text','resizable'=>true]],
				'gl_account' => ['order'=>50, 'label'=>pullTableLabel('journal_item', 'gl_account', $this->journalID),
					'attr'  => ['width'=>100, 'resizable'=>true, 'align'=>'center'],
					'events'=>  ['editor'=>dgHtmlGLAcctData()]],
				'tax_rate_id' => ['order'=>60, 'label'=>pullTableLabel('journal_main', 'tax_rate_id', $this->type), 'hidden'=>$hideItemTax,
					'attr'  =>  ['width'=>150, 'resizable'=>true, 'align'=>'center'],
					'events'=>  ['editor'=>dgHtmlTaxData($name, 'tax_rate_id', $type, 'totalUpdate();'),
					'formatter'=>"function(value,row){ return getTextValue(bizDefaults.taxRates.$type.rows, value); }"]],
				'price' => ['order'=>70, 'label'=>lang('price'), 'format'=>'currency',
					'attr'  => ['width'=>80, 'resizable'=>true, 'align'=>'right'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{onChange:function(){ ordersCalc('price'); } } }",
					'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'total' => ['order'=>80, 'label'=>lang('total'), 'format'=>'currency',
					'attr' => ['width'=>80, 'resizable'=>true, 'align'=>'right', 'value'=>'0'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{onChange:function(){ ordersCalc('total'); } } }",
					'formatter'=>"function(value,row){ return formatCurrency(value); }"]]],
            ];
		switch ($this->journalID) {
			case  3: $qty1 = lang('qty');      $qty2 = lang('received'); $ord1 = 20; $ord2 = 25; break;
			case  4: $qty1 = lang('qty');      $qty2 = lang('received'); $ord1 = 20; $ord2 = 25; break;
			case  6: $qty1 = lang('received'); $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
			case  7: $qty1 = lang('returned'); $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
			case  9: $qty1 = lang('qty');      $qty2 = lang('invoiced'); $ord1 = 20; $ord2 = 25; break;
			case 10: $qty1 = lang('qty');      $qty2 = lang('invoiced'); $ord1 = 20; $ord2 = 25; break;
			default:
			case 12: $qty1 = lang('qty');      $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
			case 13: $qty1 = lang('returned'); $qty2 = lang('shipped');  $ord1 = 25; $ord2 = 20; break;
			case 19: $qty1 = lang('qty');      $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
			case 21: $qty1 = lang('qty');      $qty2 = lang('balance');  $ord1 = 25; $ord2 = 20; break;
		}
		$data['columns']['qty'] = ['order'=>$ord1, 'label'=>$qty1, 'attr'=>  ['value'=>1,'width'=>80,'resizable'=>true,'align'=>'center'],
			'events'=>  ['editor'=>"{type:'numberbox',options:{onChange:function(){ ordersCalc('qty'); } } }"]];
		$data['columns']['bal'] = ['order'=>$ord2, 'label'=>$qty2,
			'attr' => ['width'=>80,'resizable'=>true,'align'=>'center','hidden'=>($this->rID || $this->action=='inv')?false:true]];
		return $data;
	}
	
	/**
     * Creates the datagrid structure for banking line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
	private function dgBanking($name) {
		return [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => [
                'pageSize'     => getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
//				'idField'      => 'invoice_num', // if id field is not unique, breaks getChecked method
				'rownumbers'   => true,
				'checkOnSelect'=> false,
				'selectOnCheck'=> false],
			'events' => [
                'data'         => 'datagridData',
				'onLoadSuccess'=> "function(data){
					for (var i=0; i<data.rows.length; i++) if (data.rows[i].checked) jq('#$name').datagrid('checkRow', i);
					totalUpdate();
				}",  
				'onClickRow'  => "function(rowIndex) { curIndex = rowIndex; }",
				'onBeginEdit' => "function(rowIndex) { curIndex = rowIndex; jq('#$name').edatagrid('editRow', rowIndex); }",
				'onCheck'     => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked: true} }); totalUpdate(); }",
				'onCheckAll'  => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('checkRow',i); }",
				'onUncheck'   => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked:false} }); totalUpdate(); }",
				'onUncheckAll'=> "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('uncheckRow',i); }",
				'rowStyler'   => "function(idx, row) { if (row.waiting==1) { return {class:'journal-waiting'}; }}"],
			'columns'=> [
                'id'         => ['order'=> 0, 'attr' =>['hidden'=>'true']],
				'ref_id'     => ['order'=> 0, 'attr' =>['hidden'=>'true']],
				'gl_account' => ['order'=> 0, 'attr' =>['hidden'=>'true']],
				'item_ref_id'=> ['order'=> 0, 'attr' =>['hidden'=>'true']],
				'invoice_num'=> ['order'=>10, 'label'=>pullTableLabel('journal_main', 'invoice_num', '12'),
					'attr' => ['width'=>100, 'sortable'=>true, 'resizable'=>true, 'align'=>'center']],
				'post_date'  => ['order'=>20, 'label'=>pullTableLabel('journal_main', 'post_date', '12'),
					'attr' => ['type'=>'date', 'width'=>100, 'resizable'=>true, 'align'=>'center']],
				'date_1'     => ['order'=>30, 'label'=>pullTableLabel('journal_item', 'date_1', $this->journalID),
					'attr' => ['type'=>'date','width'=>100, 'resizable'=>true, 'align'=>'center']],
				'description'=> ['order'=>40, 'label'=>lang('notes'), 'attr'=>  ['width'=>350,'resizable'=>true,'editor'=>'text']],
				'amount'     => ['order'=>50, 'label'=>lang('amount_due'),
					'attr' =>  ['type'=>'currency', 'width'=> 100,'resizable'=>true, 'align'=>'right']],
				'discount'   => ['order'=>60, 'label'=>lang('discount'), 'styles'=>  ['text-align'=>'right'],
					'attr' => ['width'=>100, 'resizable'=>true, 'align'=>'right'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{onChange:function(){ bankingCalc('disc'); } } }",
					'formatter'=>"function(value,row){ return formatCurrency(value); }"],
                    ],
				'total'      => ['order'=>70, 'label'=>lang('total'), 'styles'=>  ['text-align'=>'right'],
					'attr'  => ['width'=>100, 'resizable'=>true, 'align'=>'right'],
					'events'=> ['editor'=>"{type:'numberbox',options:{onChange:function(){ bankingCalc('direct'); } } }",
					'formatter'=>"function(value,row){ return formatCurrency(value); }"],
                    ],
				'pay' => ['order'=>90, 'attr'=>  ['checkbox'=>true]], // was 'attr'=>array() for paying bills but breaks customer receipts
            ]];
	}

    /**
     * Creates the datagrid structure for banking bulk pay line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    private function dgBankingBulk($name)
    {
		return [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => [
                'pageSize'     => getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'rownumbers'   => true,
				'checkOnSelect'=> false,
				'selectOnCheck'=> false,
				'multiSort'    => true, // this is cool as it allows multiple columns to be sorted but may become confusing
				'remoteSort'   => false],
			'events' => [
                'data'         => 'datagridData',
				'onLoadSuccess'=> "function(data){
					for (var i=0; i<data.rows.length; i++) if (data.rows[i].checked) jq('#$name').datagrid('checkRow', i);
			        jq('#$name').datagrid('fitColumns');
					totalUpdate();
				}",
				'onClickRow'   => "function(rowIndex) { curIndex = rowIndex; }",
				'onBeginEdit'  => "function(rowIndex) { bankingEditing(rowIndex); }",
				'onCheck'      => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked: true} }); totalUpdate(); }",
				'onCheckAll'   => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('checkRow',i); }",
				'onUncheck'    => "function(rowIndex) { jq('#$name').datagrid('updateRow',{index:rowIndex,row:{checked:false} }); totalUpdate(); }",
				'onUncheckAll' => "function(rows)     { for (var i=0; i<rows.length; i++) jq('#$name').datagrid('uncheckRow',i); }",
				'rowStyler'    => "function(idx, row) { if (row.waiting==1) { return {class:'journal-waiting'}; }}"],
			'columns'=> [
                'id'         => ['order'=>0, 'attr' =>  ['hidden'=>'true']],
				'item_ref_id'=> ['order'=>0, 'attr' =>  ['hidden'=>'true']],
				'contact_id' => ['order'=>0, 'attr' =>  ['hidden'=>'true']],
				'inv_date' => ['order'=>10, 'label'=>pullTableLabel('journal_main', 'post_date', '12'),
					'attr' => ['type'=>'date', 'width'=>90, 'sortable'=>true, 'resizable'=>true, 'align'=>'center']],
				'primary_name' => ['order'=>20, 'label'=>pullTableLabel('journal_main', 'primary_name_b', '12'),
					'attr' => ['width'=>220, 'sortable'=>true, 'resizable'=>true]],
				'inv_num'=> ['order'=>30, 'label'=>pullTableLabel('journal_main', 'invoice_num', '12'),
					'attr' => ['width'=>100, 'sortable'=>true, 'resizable'=>true, 'align'=>'center']],
				'amount' => ['order'=>40, 'label'=>lang('amount_due'),
					'attr' =>  ['type'=>'currency', 'width'=> 80,'resizable'=>true, 'align'=>'right']],
				'description'=> ['order'=>50, 'label'=>lang('notes'), 'attr'=>  ['width'=>220,'resizable'=>true,'editor'=>'text']],
				'date_1' => ['order'=>60, 'label'=>pullTableLabel('journal_item', 'date_1', $this->journalID),
					'attr' => ['type'=>'date', 'width'=>90, 'sortable'=>true, 'resizable'=>true, 'align'=>'center']],
				'discount' => ['order'=>70, 'label'=>lang('discount'), 'styles'=>  ['text-align'=>'right'],
					'attr' => ['width'=>80, 'resizable'=>true, 'align'=>'right'],
					'events'=>  ['editor'=>"{type:'numberbox'}", 'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'total' => ['order'=>80, 'label'=>lang('total'), 'styles'=>  ['text-align'=>'right'],
					'attr'  => ['width'=>80, 'resizable'=>true, 'align'=>'right'],
					'events'=> ['editor'=>"{type:'numberbox'}", 'formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'pay' => ['order'=>90, 'attr'=>  ['checkbox'=>true]]]];
	}
	
	/**
     * Creates the datagrid structure for inventory adjustments line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
	private function dgAdjust($name)
    {
		$on_hand  = jsLang('inventory', 'qty_stock');
		$on_order = jsLang('inventory', 'qty_po');
        $store_id = getUserCache('profile', 'store_id', false, 0);
		return [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => ['toolbar'=>"#{$name}Toolbar",'rownumbers'=>true,'singleSelect'=>true,'idField'=>'id'],
			'events' => [
                'data'         => "datagridData",
				'onLoadSuccess'=> "function(row) { totalUpdate(); }",
				'onClickCell'  => "function(rowIndex) {
					switch (icnAction) {
						case 'trash': jq('#$name').edatagrid('destroyRow', rowIndex); break;
					}
					icnAction = '';
				}",
				'onClickRow'   => "function(rowIndex) { curIndex = rowIndex; }",
                'onBeforeEdit' => "function(rowIndex) {
    var edtURL = jq(this).edatagrid('getColumnOption','sku');
    edtURL.editor.options.url = '".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&f0=a&bID='+jq('#store_id').val();
}",
				'onBeginEdit'  => "function(rowIndex) { curIndex = rowIndex; jq('#$name').edatagrid('editRow', rowIndex); }",
				'onDestroy'    => "function(rowIndex) { totalUpdate(); curIndex = undefined; }",
				'onAdd'        => "function(rowIndex) { setFields(rowIndex); }"],
			'source' => [
                'actions' => ['newItem' =>['order'=>10,'html'=>['icon'=>'add','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns'=> [
                'id'         => ['order'=>0, 'attr'=>  ['hidden'=>true]],
				'gl_account' => ['order'=>0, 'attr'=>  ['hidden'=>true]],
				'unit_cost'  => ['order'=>0, 'attr'=>  ['editor'=>'text', 'hidden'=>true]],
				'action'     => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>50],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> ['trash' => ['icon'=>'trash','order'=>20,'size'=>'small','events'=>  ['onClick'=>"icnAction='trash';"]]]],
				'sku'=> ['order'=>20, 'label'=>lang('sku'),'attr'=>['width'=>120,'sortable'=>true,'resizable'=>true,'align'=>'center'],
					'events'=>  ['editor'=>"{type:'combogrid',options:{
						width: 150, panelWidth: 540, delay: 500, idField: 'sku', textField: 'sku', mode: 'remote',
						url:        '".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&f0=a&bID=$store_id',
						onClickRow: function (idx, data) { adjFill(data); },
						columns:[[{field:'sku',              title:'".jsLang('sku')."',width:100},
								  {field:'description_short',title:'".jsLang('description')."',width:200},
								  {field:'qty_stock',        title:'$on_hand', align:'right',width:90},
								  {field:'qty_po',           title:'$on_order',align:'right',width:90}]]
					}}"]],
				'qty_stock' => ['order'=>30,'label'=>$on_hand,'attr'=>['width'=>100,'disabled'=>true,'resizable'=>true,'align'=>'center'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{disabled:true}}"]],
				'qty' => ['order'=>40,'label'=>lang('journal_item_qty', $this->journalID),'attr' =>['width'=>100,'resizable'=>true,'align'=>'center'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{onChange:function(){ adjCalc('qty'); } } }"]],
				'balance' => ['order'=>50, 'label'=>lang('balance'),'styles'=>['text-align'=>'right'],
					'attr' => ['width'=>100, 'disabled'=>true, 'resizable'=>true, 'align'=>'center'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{disabled:true}}"]],
				'total' => ['order'=>60, 'label'=>lang('total'),'format'=>'currency',
                    'attr'=>['width'=>120,'resizable'=>true,'align'=>'center'],
					'events'=>['editor'=>"{type:'numberbox'}"]],
				'description' => ['order'=>70,'label'=>lang('description'),'attr'=>['width'=>250,'editor'=>'text','resizable'=>true]]]];
	}

	/**
     * Creates the datagrid structure for inventory assembly line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
	private function dgAssy($name)
    {
		return [
            'id'   => $name,
			'attr' => ['rownumbers'=>true,'showFooter'=>true,'pagination'=>false], // override bizuno default
			'events' => [
                'rowStyler'    =>"function(index, row) { if (row.qty_stock-row.qty_required<0) return {class:'row-inactive'}; }",
				'onLoadSuccess'=>"function(row) { jq('#$name').datagrid('fitColumns', true); }"],
			'columns'=> [
                'qty'         => ['order'=> 0,'attr' =>  ['hidden'=>true]],
				'sku'         => ['order'=>20,'label'=>lang('sku'),         'attr'=>  ['width'=>100, 'align'=>'center']],
				'description' => ['order'=>30,'label'=>lang('description'), 'attr'=>  ['width'=>250]],
				'qty_stock'   => ['order'=>40,'label'=>pullTableLabel('inventory','qty_stock'),'attr'=>  ['width'=>100, 'align'=>'center']],
				'qty_required'=> ['order'=>50,'label'=>lang('qty_required'),'attr'=>  ['width'=>100, 'align'=>'center']],
                ],
            ];
	}

	/**
	 * This method builds the appropriate Save menu choices depending on the journal and state of the order 
     * @param integer $security - users security to set visibility
     * @return array - structure for save menu
	 */
	private function renderMenuSave($security=0)
    {
		msgDebug("\nbuilding renderMenuSave, security = $security, id = $this->rID");
		$type = false;
		$data = [];
        if ($security < 2) { return $data; } // Read-only, no operations allowed
		switch ($this->journalID) {
            case 2:
				$data = [
					'optSaveAs' => ['order'=>40,'label'=>lang('save_as'),'child'=>  [
                        'saveAsNew' => ['order'=>10,'label'=>lang('new'),'security'=>3,'events'=>['onClick'=>"saveAction('saveAs','2');"]]]]];
                break;
			case  3:
			case  4:
			case  6: $type = 'v';
			case  9:
			case 10:
            case 12: if (!$type) { $type = 'c'; }
				$data = [
                    'optPrint'   => ['order'=>10,'icon'=>'print','label'=>lang('save_print'),'security'=>3,
						'hidden' => !in_array($this->journalID, [6]) && $security>1?false:true,
						'events' => ['onClick'=>"jq('#xChild').val('print'); jq('#frmJournal').submit();"]],
					'optPayment' => ['order'=>20,'icon'=>'payment','label'=>lang('save_payment'),'security'=>3,
						'hidden' =>  in_array($this->journalID, [6,12]) && $security>1?false:true,
						'events' => ['onClick'=>"jq('#xAction').val('payment'); jq('#frmJournal').submit();"]],
					'optFill'    => ['order'=>30,'icon'=>'fill','label'=>lang('save_fill'),'security'=>2,
						'hidden' =>  in_array($this->journalID, [4,10]) && $security>1?false:true,
						'events' => ['onClick'=>"jq('#xAction').val('invoice'); jq('#frmJournal').submit();"]],
					'optSaveAs'  => ['order'=>40,'label'=>lang('save_as'),'child'=>  [
                        'saveAsQuote'=> ['order'=>10,'icon'=>'quote','label'=>lang('journal_main_journal_id', $type=='v'?3: 9),'security'=>3,
//							'disabled'=> !in_array($this->journalID, array(3,9)) ? false : true,
							'events'  => ['onClick'=>"saveAction('saveAs','".($type=='v'?3: 9)."');"]],
						'saveAsSO'   => ['order'=>20,'icon'=>'order','label'=>lang('journal_main_journal_id', $type=='v'?4:10),'security'=>3,
//							'disabled'=> !in_array($this->journalID, array(4,10)) ? false : true,
							'events'  => ['onClick'=>"saveAction('saveAs','".($type=='v'?4:10)."');"]],
						'saveAsInv'  => ['order'=>30,'icon'=>'sales','label'=>lang('journal_main_journal_id', $type=='v'?6:12),'security'=>3,
//							'disabled'=> !in_array($this->journalID, array(6,12)) ? false : true,
							'events'  => ['onClick'=>"saveAction('saveAs','".($type=='v'?6:12)."');"]],
                            ]],
					'optMoveTo'  => ['order'=>50,'label'=>lang('move_to'),'disabled'=>$this->rID?false:true,'child'=>  [
                        'MoveToQuote'=> ['order'=>10,'icon'=>'quote','label'=>lang('journal_main_journal_id', $type=='v'?3: 9),
							'disabled'=> !in_array($this->journalID, [3,9]) ? false : true,'security'=>3,
							'events'  => ['onClick'=>"saveAction('moveTo','".($type=='v'?3: 9)."');"]],
						'MoveToSO'   => ['order'=>20,'icon'=>'order','label'=>lang('journal_main_journal_id', $type=='v'?4:10),
							'disabled'=> !in_array($this->journalID, [4,10]) ? false : true,'security'=>3,
							'events'  => ['onClick'=>"saveAction('moveTo','".($type=='v'?4:10)."');"]],
						'MoveToInv'  => ['order'=>30,'icon'=>$type=='v'?'purchase':'sales','label'=>lang('journal_main_journal_id', $type=='v'?6:12),
							'disabled'=> !in_array($this->journalID, [6,12]) ? false : true,'security'=>3,
							'events'  => ['onClick'=>"saveAction('moveTo','".($type=='v'?6:12)."');"]],
                            ]],
                    ];
				break;
			case 20:
			case 22:
				$data = ['optPrint'=>['order'=>10,'label'=>lang('save_print'),'icon'=>'print','security'=>3,
                    'hidden'=>$security>1?false:true,'events'=>['onClick'=>"jq('#xChild').val('print'); jq('#frmJournal').submit();"]]];
			break;
			default:
		}
		return $data;
	}

	/**
     * Retrieves list of attachments for a given 
     * @param string $srcField - path to PhreeBooks uploads folder
     * @param integer $rID - record id used to create filename to search for attachments 
     * @param integer $refID - record id of reference journal entry (SO or PO)
     * @return null, saves files on success, message if fails
     */
    private function getAttachments($srcField, $rID, $refID=0)
    {
        $io = new \bizuno\io();
        if ($io->uploadSave($srcField, getModuleCache('phreebooks', 'properties', 'attachPath')."rID_{$rID}_")) {
            dbWrite(BIZUNO_DB_PREFIX.'journal_main', ['attach'=>'1'], 'update', "id=$rID");
        }
		if ($refID) { // if so_po_ref_id, check for attachment to copy over
            $files = glob(getModuleCache('phreebooks', 'properties', 'attachPath')."rID_{$refID}_*");
            if ($files === false || sizeof($files) == 0) { return; }
            foreach ($files as $oldFile) {
                $newFile = str_replace("rID_{$refID}_", "rID_{$rID}_", $oldFile);
                copy($oldFile, $newFile);
            }
            dbWrite(BIZUNO_DB_PREFIX.'journal_main', ['attach'=>'1'], 'update', "id=$rID");
		}
	}

	/**
     * Creates the structure for the delivery dates pop up for user entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function deliveryDates(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', 'j'.$this->journalID.'_mgr', 3)) { return; }
		$rID = clean('rID', 'integer', 'get');
		$result = dbGetMulti(BIZUNO_DB_PREFIX.'journal_item', "ref_id=$rID");
		$items = [];
		foreach ($result as $row) {
            if ($row['gl_type'] != 'itm') { continue; }
			$items[] = ['id'=>$row['id'],'qty'=>$row['qty'],'sku'=>$row['sku'],'description'=>$row['description'],'date_1'=>$row['date_1']];
		}
		$data = ['type'=>'divHTML',
			'fields' => [
                'items'  => $items,
				'delSave'=> ['icon'=>'save','events'=>['onClick'=>"divSubmit('phreebooks/main/deliveryDatesSave&jID=$this->journalID', 'winDelDates');"]]],
			'divs' => ['divRate' => ['order'=>50, 'src'=>BIZUNO_LIB."view/module/phreebooks/winDelivery.php"]]];
		msgDebug("\nitems to list is: ".print_r($data['fields']['items'], true));
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Saves the user entered delivery dates (typically for PO's and SO's
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function deliveryDatesSave(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 3)) { return; }
        $request = $_POST;
		foreach ($request as $key => $value) {
			if (strpos($key, "rID_") === 0) {
				$rID = str_replace("rID_", "", $key);
				dbWrite(BIZUNO_DB_PREFIX."journal_item", ['date_1'=>clean($value, 'date')], 'update', "id=$rID");
			}
		}
		msgAdd(lang('msg_database_write'), 'success');
		$layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"jq('#winDelDates').window('close');"]]);
	}

    /**
     * This method takes the line items from a transfer operation and builds the new 'effective' line items
     * @param array $item - the list of items to transfer, after initial processing
     */
    private function journalTransfer(&$item)
    {
        msgDebug("\nAdding rows for Inventory Store Transfer");
        $srcStoreID = clean('so_po_ref_id','integer', 'post');
        $destStoreID= clean('store_id',    'integer', 'post');
        if ($srcStoreID == $destStoreID) { return msgAdd($this->lang['err_gl_xfr_same_store']); }
        // take the line items and create a negative list for the receiving store
        $output = [];
        foreach ($item as $row) {
            unset($row['id']); // when reposting, this causes duplicate ID errors if not cleared
            $row['qty'] = -$row['qty'];
            $row['gl_type'] = 'xfr';
            $tmp = $row['credit_amount']; // swap debits and credits
            $row['credit_amount'] = $row['debit_amount'];
            $row['debit_amount'] = $tmp;
            $output[] = $row;
        }
        $item = array_merge($item, $output);
        return true;
    }

	/**
     * Determines the balance owed for a journal order line item
     * @param type $layout
     * @return type
     */
    public function journalBalance(&$layout=[])
    {
		$rID       = clean('rID',      'integer', 'get');
		$post_date = clean('postDate', ['format'=>'date','default'=>date('Y-m-d')], 'get');
		$gl_account= clean('glAccount',['format'=>'text','default'=>getModuleCache('phreebooks', 'settings', 'vendors', 'gl_cash')], 'get');
		$row = dbGetRow(BIZUNO_DB_PREFIX."journal_periods", "start_date<='$post_date' AND end_date>='$post_date'");
        if (!$row) { return msgAdd(sprintf(lang('err_gl_post_date_invalid'), $post_date)); }
		$balance  = dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'beginning_balance', "period={$row['period']} AND gl_account='$gl_account'");
		$balance += dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'SUM(debit_amount - credit_amount) as balance', "gl_account='$gl_account' AND post_date>='{$row['start_date']}' AND post_date<='$post_date'", false);
		// add back the record amount if editing
        if ($rID) { $balance += dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'credit_amount', "ref_id=$rID AND gl_type='ttl'"); }
		msgDebug("returning balance = $balance");
		$layout = array_replace_recursive($layout, ['content' => ['balance'=>$balance]]);
	}

	/**
     * Builds the list of totals from the session cache for a particular journal
     * @param integer $jID - journal ID used as index
     * @return array - list of totals in order of priority
     */
    public function loadTotals($jID)
    {
        $totals = [];
        $methods = sortOrder(getModuleCache('phreebooks', 'totals')); // re-sort by users order preferences
        foreach ($methods as $methID => $settings) {
            if (!isset($settings['status']) || !$settings['status']) { continue; }
            if (!isset($settings['settings']['journals'])) { $settings['settings']['journals'] = []; }
            if (is_string($settings['settings']['journals'])) { $settings['settings']['journals'] = json_decode($settings['settings']['journals']); }
            if (in_array($jID, $settings['settings']['journals'])) { $totals[] = $methID; }
        }
        return $totals;
	}

    /**
     * Adds the notes to a general ledger entry to show if the journal balance will increase or decrease
     * @param array $items - line items from the general ledger datagrid
     */
    private function addGLNotes(&$items)
    {
        foreach ($items as $idx => $row) {
            $found = false;
            foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $acct) {
                if ($acct['id'] != $row['gl_account']) { continue; }
                $found = true;
                $asset = in_array($acct['type'], $this->assets) ? 1 : 0;
                if ($row['debit_amount']  &&  $asset) { $arrow = 'inc'; }
                if ($row['debit_amount']  && !$asset) { $arrow = 'dec'; }
                if ($row['credit_amount'] &&  $asset) { $arrow = 'dec'; }
                if ($row['credit_amount'] && !$asset) { $arrow = 'inc'; }
                break;
            }
            $incdec = '';
            if ($found && $arrow=='inc')      { $incdec = json_decode('"\u21e7"').' '.$this->lang['bal_increase']; }
            else if ($found && $arrow=='dec') { $incdec = json_decode('"\u21e9"').' '.$this->lang['bal_decrease']; }
            $items[$idx]['notes'] = $incdec;
        }
	}

    /**
     * Sets the sql criteria to limit the list of journals to search in the db
     * @param array $jIDs - list of journal ID within scope
     * @return string - sql condition for allowed journals
     */
    private function setAllowedJournals($jIDs=[])
    {
        if (sizeof($jIDs) == 0) { return ''; }
        $valids = [];
        while ($i = array_shift($jIDs)) {
			if (getUserCache('security', "j{$i}_mgr", false, 0)) { $valids[] = $i; }
        }
        if (sizeof($valids) == 0) { return BIZUNO_DB_PREFIX."journal_main.journal_id=-1"; } // return with no results
        $output = sizeof($valids) == 1 ? " = ".array_shift($valids) : " IN (".implode(',', $valids).")";
        return BIZUNO_DB_PREFIX."journal_main.journal_id".$output;
    }
    
	/**
	 * Builds the drop down of available journals for searching
	 * @return array drop down formatted list of available journals filtered by security
	 */
	private function selJournals()
    {
		$blocked= [];
		$output = [['id'=>0, 'text'=>lang('all')]];
		for ($i = 1; $i < 30; $i++) {
			if (getUserCache('security', "j{$i}_mgr", false, 0)) {
				$output[] = ['id'=>$i, 'text'=>lang("journal_main_journal_id_$i")];
            } else { $blocked[] = $i; }
		}
		$this->blocked_journals = sizeof($blocked) > 0 ? implode(',', $blocked) : false;
		return $output;
	}

	/**
     * Toggles the waiting flag (and database field) for a given journal record
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function toggleWaiting(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 1)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('err_copy_name_prompt')); }
		$waiting = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'waiting', "id=$rID");
		$state = $waiting=='0' ? '1' : '0';
		dbWrite(BIZUNO_DB_PREFIX."journal_main", ['waiting'=>$state], 'update', "id=$rID");
		$layout = array_replace_recursive($layout,['content'=>['action'=>'eval','actionData'=>"jq('#dgPhreeBooks').datagrid('reload');"]]);
	}

	/**
     * creates the HTML for a pop up to set the recur parameters of a journal entry
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function popupRecur(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', "j{$this->journalID}_mgr", 2)) { return; }
		$presets= clean('data','text', 'get');
		$vals = explode(':', $presets);
		$freq = isset($vals[0]) && clean($vals[0], 'integer') ? clean($vals[0], 'integer') : 1;
		$times= isset($vals[1]) && clean($vals[1], 'integer') ? clean($vals[1], 'integer') : 2;
		$html  = "<p>".$this->lang['recur_desc']."</p>";
		$html .= html5('rcrTimes',  ['label'=>$this->lang['recur_times'],    'position'=>'after', 'attr'=>  ['value'=>$times, 'size'=>'3', 'maxlength'=>'2']]);
		$html .= "<p>".$this->lang['recur_frequency']."</p>";
		$html .= html5('radioRecur',['label'=>lang('dates_weekly'),   'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'1', 'checked'=>$freq==1?true:false]])."<br />";
		$html .= html5('radioRecur',['label'=>lang('dates_bi_weekly'),'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'2', 'checked'=>$freq==2?true:false]])."<br />";
		$html .= html5('radioRecur',['label'=>lang('dates_monthly'),  'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'3', 'checked'=>$freq==3?true:false]])."<br />";
		$html .= html5('radioRecur',['label'=>lang('dates_quarterly'),'position'=>'after','attr'=>  ['type'=>'radio', 'value'=>'4', 'checked'=>$freq==4?true:false]])."<br />";
		$html .= html5('iconGO',    ['icon'=>'next','events'=>['onClick'=>"jq('#recur_id').val(jq('#rcrTimes').val()); jq('#recur_frequency').val(jq('input[name=radioRecur]:checked').val()); jq('#winRecur').window('close');"]]);
		$layout = array_replace_recursive($layout, ['type'=>'divHTML','divs'=>['winRecur'=>['order'=>50, 'type'=>'html', 'html'=>$html]]]);
	}
}
