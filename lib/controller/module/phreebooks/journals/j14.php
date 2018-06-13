<?php
/*
 * PhreeBooks journal class for Journal 14, Inventory Assembly
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
 * @version    2.x Last Update: 2018-05-24
 * @filesource /lib/controller/module/phreebooks/journals/j14.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreebooks/journals/common.php");

class j14 extends jCommon
{
    protected $journalID = 14;

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
        // handled later when rest of unique fields have been set
    }

    /**
     * Tailors the structure for the specific journal
     * @param array $data - current working structure
     * @param integer $rID - Database record id of the journal main record
     * @param integer $security - Users security level
     */
    public function getDataItem(&$data, $rID=0, $cID=0)
    {
        $data['datagrid']['item'] = $this->dgAssy('dgJournalItem'); // place different as this dg is on the right, not bottom
        unset($data['fields']['item']['sku']['attr']['size']);
        $data['fields']['item']['sku']['classes']['combogrid'] = 'easyui-combogrid';
        $data['fields']['item']['sku']['attr']['data-options'] = "url:'".BIZUNO_AJAX."&p=inventory/main/managerRows&filter=assy&clr=1&bID='+jq('#store_id').val(),
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
                    {field:'qty_po',           title:'".jsLang('inventory_qty_po')."',   width:100,align:'right'}]]";
        unset($data['toolbars']['tbPhreeBooks']['icons']['print']);
        unset($data['toolbars']['tbPhreeBooks']['icons']['recur']);
        unset($data['toolbars']['tbPhreeBooks']['icons']['payment']);
        $data['fields']['main']['gl_acct_id'] = ['attr'=>['type'=>'hidden']];
        $data['fields']['item']['gl_account']['attr']['type'] = 'hidden';
        $data['fields']['item']['qty']['label']  = lang('qty_to_assemble');
        $data['fields']['item']['qty']['events'] = ['onChange'=>"assyUpdateBalance()"];
        $data['fields']['item']['qty']['styles'] = ['text-align'=>'right'];
        $data['qty_stock']= ['label'=>pullTableLabel('inventory', 'qty_stock'), 'styles'=>['text-align'=>'right'], 'attr'=>['size'=>'10', 'readonly'=>'readonly']];
        $data['balance']  = ['label'=>lang('balance'), 'styles'=>['text-align'=>'right'], 'attr'=>['size'=>'10', 'readonly'=>'readonly']];
        $isWaiting = isset($data['fields']['main']['waiting']['attr']['checked']) && $data['fields']['main']['waiting']['attr']['checked'] ? '1' : '0';
        $data['fields']['main']['waiting'] = ['attr'=>['type'=>'hidden', 'value'=>$isWaiting]];
        if ($rID) { // merge the data
            $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id='$rID'");
            $data['fields']['main']['id']['attr']['value']          = $rID;
            $data['fields']['main']['store_id']['attr']['value']    = $dbData['store_id'];
            $data['fields']['main']['post_date']['attr']['value']   = $dbData['post_date'];
            $data['fields']['main']['invoice_num']['attr']['value'] = $dbData['invoice_num'];
            $dbData = dbGetRow(BIZUNO_DB_PREFIX.'journal_item', "ref_id='$rID' AND gl_type='asy'");
            $data['fields']['item']['gl_account']['attr']['value']  = $dbData['gl_account'];
            $data['fields']['item']['sku']['attr']['value']         = $dbData['sku'];
            $data['fields']['item']['qty']['attr']['value']         = $dbData['qty'];
            $data['fields']['item']['trans_code']['attr']['value']  = $dbData['trans_code'];
            $data['fields']['item']['description']['attr']['value'] = $dbData['description'];
            $stock = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'qty_stock', "sku='{$dbData['sku']}'");
            $data['qty_stock']['attr']['value'] = $stock - $dbData['qty'];
            $data['balance']['attr']['value']   = $stock;
        }
        $data['divs']['divDetail']= ['order'=>50,'type'=>'divs','classes'=>['areaView'],'attr'=>['id'=>'pbDetail'],'divs'=>[
            'props'  => ['order'=>40,'type'=>'fields','classes'=>['blockView'], 'attr'=>['id'=>'pbProps'], 'fields'=>$this->getProps($data)],
            'totals' => ['order'=>50,'type'=>'totals','classes'=>['blockViewR'],'attr'=>['id'=>'pbTotals'],'content'=>$data['totals_methods']]]];
        $data['divs']['dgItems']  = ['order'=>60,'type'=>'datagrid','key'=>'item'];
        $data['jsBody']['frmVal'] = "function preSubmit() {
	var item = {sku:jq('#sku').combogrid('getValue'),qty:jq('#qty').val(),description:jq('#description').val(),total:0,gl_account:jq('#gl_account').val()};
	var items = {total:1,rows:[item]};
	var serializedItems = JSON.stringify(items);
	jq('#item_array').val(serializedItems);
	if (!formValidate()) return false;
	return true;
}";
        $data['jsReady']['divInit'] = "ajaxForm('frmJournal'); jq('#sku').next().find('input').focus();";
    }

    /**
     * Configures the journal entry properties (other than address and items)
     * @param array $data - current working structure
     * @return array - List of fields to show with the structure
     */
    private function getProps($data)
    {
        return ['id'     => $data['fields']['main']['id'],
            'journal_id' => $data['fields']['main']['journal_id'],
            'gl_account' => $data['fields']['item']['gl_account'],
            'gl_acct_id' => $data['fields']['main']['gl_acct_id'],
            'recur_id'   => $data['fields']['main']['recur_id'],
            'item_array' => $data['item_array'],
            'store_id'   => array_merge(['break'=>true], $data['fields']['main']['store_id']),
            'sku'        => array_merge(['break'=>true], $data['fields']['item']['sku']),
            'post_date'  => array_merge(['break'=>true], $data['fields']['main']['post_date']),
            'trans_code' => array_merge(['break'=>true], $data['fields']['item']['trans_code']),
            'description'=> array_merge(['break'=>true], $data['fields']['item']['description']),
            'invoice_num'=> array_merge(['break'=>true], $data['fields']['main']['invoice_num']),
            'qty_stock'  => array_merge(['break'=>true], $data['qty_stock']),
            'qty'        => array_merge(['break'=>true], $data['fields']['item']['qty']),
            'balance'    => array_merge(['break'=>true], $data['balance'])];
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
     * Get re-post records - applies to journals 6, 7, 12, 13, 14, 15, 16, 19, 21
     * @return array - journal id's that need to be re-posted as a result of this post
     */
    public function getRepostData()
    {
		msgDebug("\n  j14 - Checking for re-post records ... ");
        $out1 = [];
        $out2 = array_merge($out1, $this->getRepostInv());
        $out3 = array_merge($out2, $this->getRepostInvCOG());
//      $out4 = array_merge($out3, $this->getRepostInvAsy());
        $out5 = array_merge($out3, $this->getRepostPayment());
        msgDebug("\n  j14 - End Checking for Re-post.");
        return $out5;
	}

	/**
     * Post journal item array to journal history table
     * applies to journal 2, 6, 7, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22
     * @return boolean - true
     */
    private function postJournalHistory()
    {
		msgDebug("\n  Posting Chart Balances...");
        if ($this->setJournalHistory()) { return true; }
	}

	/**
     * unPosts journal item array from journal history table
     * applies to journal 2, 6, 7, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22
     * @return boolean - true
     */
	private function unPostJournalHistory() {
		msgDebug("\n  unPosting Chart Balances...");
        if ($this->unSetJournalHistory()) { return true; }
	}

	/**
     * Post inventory
     * @return boolean true on success, null on error
     */
	private function postInventory()
    {
		msgDebug("\n  Posting Inventory ...");
		$ref_field = false;
        $ref_closed= false;
		$str_field = 'qty_stock';
		// adjust inventory stock status levels (also fills inv_list array)
		$item_rows_to_process = count($this->item); // NOTE: variable needs to be here because $this->item may grow within for loop (COGS)
// the cogs rows are added after this loop ..... the code below needs to be rewritten
		for ($i = 0; $i < $item_rows_to_process; $i++) {
            if (!in_array($this->item[$i]['gl_type'], ['itm','adj','asy','xfr'])) { continue; }
			if (isset($this->item[$i]['sku']) && $this->item[$i]['sku'] <> '') {
				$inv_list = $this->item[$i];
                $inv_list['price'] = $this->item[$i]['qty'] ? (($this->item[$i]['debit_amount'] + $this->item[$i]['credit_amount']) / $this->item[$i]['qty']) : 0;
				$assy_cost = $this->setAssyCost($inv_list); // for assembly parts list
                if ($assy_cost === false) { return; }// there was an error
			}
		}
		// update inventory status
		foreach ($this->item as $row) {
            if (!isset($row['sku']) || !$row['sku']) { continue; } // skip all rows without a SKU
			$item_cost = $full_price = 0;
            // commented out as there is a tool now plus this is not the latest cost but based on COGS which may be very different from current price.
//          if ($row['gl_type'] == 'asy' && $row['qty'] > 0) { $item_cost = $row['debit_amount'] / $row['qty']; } // only for the item being assembled
            if (!$this->setInvStatus($row['sku'], $str_field, $row['qty'], $item_cost, $row['description'], $full_price)) { return false; }
		}
		// build the cogs item rows
        $this->setInvCogItems();
		msgDebug("\n  end Posting Inventory.");
		return true;
	}

	/**
     * unPost inventory
     * @return boolean true on success, null on error
     */
	private function unPostInventory()
    {
		msgDebug("\n  unPosting Inventory ...");
        if (!$this->rollbackCOGS()) { return false; }
		for ($i = 0; $i < count($this->item); $i++) {
            if (!isset($this->item[$i]['sku']) || !$this->item[$i]['sku']) { continue; }
            if (!$this->setInvStatus($this->item[$i]['sku'], 'qty_stock', -$this->item[$i]['qty'])) { return; }
        }
        if ($this->main['so_po_ref_id'] > 0) { $this->setInvRefBalances($this->main['so_po_ref_id'], false); }
		dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory_history WHERE ref_id = {$this->main['id']}");
		dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_cogs_usage WHERE journal_main_id={$this->main['id']}");
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."journal_cogs_owed  WHERE journal_main_id={$this->main['id']}");
		msgDebug("\n  end unPosting Inventory.");
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

    /**
     * Creates the datagrid structure for inventory assembly line items
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
	private function dgAssy($name)
    {
		return ['id' => $name,
			'attr'   => ['rownumbers'=>true,'showFooter'=>true,'pagination'=>false], // override bizuno default
			'events' => [
                'rowStyler'    => "function(index, row) { if (row.qty_stock-row.qty_required<0) return {class:'row-inactive'}; }",
				'onLoadSuccess'=> "function(row) { jq('#$name').datagrid('fitColumns', true); }"],
			'columns'=> [
                'qty'          => ['order'=> 0,'attr' =>['hidden'=>true]],
				'sku'          => ['order'=>20,'label'=>lang('sku'),         'attr'=>['width'=>100, 'align'=>'center']],
				'description'  => ['order'=>30,'label'=>lang('description'), 'attr'=>['width'=>250]],
				'qty_stock'    => ['order'=>40,'label'=>pullTableLabel('inventory','qty_stock'),'attr'=>['width'=>100, 'align'=>'center']],
				'qty_required' => ['order'=>50,'label'=>lang('qty_required'),'attr'=>['width'=>100, 'align'=>'center']]]];
	}
}
