<?php
/*
 * PhreeBooks Banking - Reconciliation methods
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
 * @version    2.x Last Update: 2018-03-08
 * @filesource /lib/controller/module/phreebooks/reconcile.php
 */

namespace bizuno;

class phreebooksReconcile
{
    /**
     * Creates structure for main entry of banking reconciliation 
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
		$dgName = 'dgReconcile';
        if (!$security = validateSecurity('phreebooks', 'recon', 3)) { return; }
		$js = "
lastIndex = -1;
var pauseTotal = true;
function preSubmit() {
    var items = jq('#$dgName').treegrid('getData');
    var rowsChk = [];
    for (var i=0; i<items.length; i++) {
        if (items[i].id.substr(0, 4) == 'pID_') {
            var node = jq('#$dgName').treegrid('getChildren', items[i].id);
            for (var j=0; j<node.length; j++) rowsChk.push(node[j]);
        } else {
            rowsChk.push(items[i]);
        }
    }
    var serializedItems = JSON.stringify(rowsChk);
    jq('#item_array').val(serializedItems);
    return true;
}
function reconInit(row, data) {
    var stmtBal = formatCurrency(data.footer[0].total);
    jq('#stmt_balance').val(stmtBal);
    pauseTotal = true;
    for (var i=0; i<data.rows.length; i++) {
        if (data.rows[i]['rowChk'] > 0) { 
            jq('#$dgName').treegrid('checkRow', data.rows[i].id);
            reconCheck(data.rows[i]);
        } else {
            jq('#$dgName').treegrid('uncheckRow', data.rows[i].id); // this slows down load but necesary to clear parents during period or acct change
//            reconUncheck(data.rows[i]); // this causes EXTREMELY SLOW page loads, should not be necessary
        }
    }
    pauseTotal = false;
    reconTotal();
}
function reconCheck(row) {
    jq('#$dgName').treegrid('update',{ id:row.id, row:{rowChk: true} });
    if (row.id.substr(0, 4) == 'pID_') {
        var node = jq('#$dgName').treegrid('getChildren', row.id);
        for (var j=0; j<node.length; j++) {
            jq('#$dgName').treegrid('update',{ id:node[j].id, row:{rowChk: true} });
            jq('#$dgName').treegrid('checkRow', node[j].id);
        }
    } else if (typeof row._parentId !== 'undefined') {
        reconCheckChild(row._parentId);
    }
}
function reconCheckChild(parentID) {
    var node = jq('#$dgName').treegrid('getChildren', parentID);
    var allChecked = true;
    for (var j=0; j<node.length; j++) if (!node[j].rowChk) { allChecked = false; }
    if (allChecked) jq('#$dgName').treegrid('update',{ id:parentID, row:{rowChk: true} });
}
function reconUncheck(row) {
    jq('#$dgName').treegrid('update',{ id:row.id, row:{rowChk: false} });
    if (row.id.substr(0, 4) == 'pID_') {
        var node = jq('#$dgName').treegrid('getChildren', row.id);
        for (var j=0; j<node.length; j++) {
            jq('#$dgName').treegrid('update',{ id:node[j].id, row:{rowChk: false} });
            jq('#$dgName').treegrid('uncheckRow', node[j].id);
        }
    } else if (typeof row._parentId !== 'undefined') {
        jq('#$dgName').treegrid('update',{ id:row._parentId, row:{rowChk: false} });
    }
}
function reconTotal() {
    if (pauseTotal) { return; }
    var openTotal  = 0;
    var closedTotal= 0;
    var items = jq('#$dgName').treegrid('getData');
    for (var i=0; i<items.length; i++) {
        if (isNaN(items[i]['total'])) alert('error in total = '+items[i]['total']);
        if (items[i]['id'].substr(0, 4) == 'pID_') {
            var node = jq('#$dgName').treegrid('getChildren', items[i]['id']);
            for (var j=0; j<node.length; j++) {
                ttl = parseFloat(node[j]['deposit']) - parseFloat(node[j]['withdrawal']);
                if (node[j]['rowChk']) { closedTotal += ttl; }
                else                    { openTotal += ttl; }
            }
        } else {
            if (items[i]['rowChk']) { closedTotal += parseFloat(items[i]['total']); }
            else                    { openTotal   += parseFloat(items[i]['total']); }
        }
    }
    var stmt  = cleanCurrency(jq('#stmt_balance').val());
    var footer= jq('#$dgName').treegrid('getFooterRows');
    var gl    = parseFloat(footer[3]['total']);
    footer[0]['total'] = stmt;
    footer[1]['total'] = closedTotal;
    footer[2]['total'] = openTotal;
    footer[4]['total'] = stmt + openTotal - gl;
    jq('#$dgName').datagrid('reloadFooter');
}
ajaxForm('frmReconcile')";
		$htmlHead = html5('frmReconcile', ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreebooks/reconcile/save"]]).
                    html5('item_array', ['attr'=>['type'=>'hidden']]);
		$data = [
            'pageTitle'=> lang('phreebooks_recon'),
			'toolbar'  => ['tbRecon'=>['icons'=>['save'=>['order'=>40,'events'=>['onClick'=>"jq('#frmReconcile').submit();"]]]]],
			'datagrid' => ['manager'=>$this->tgReconcile($dgName)],
			'divs'     => [
                'submenu'  => ['order'=>10, 'type'=>'html', 'html'=>viewSubMenu('banking')],
                'toolbar'  => ['order'=>20, 'type'=>'toolbar','key' =>'tbRecon'],
				'headRecon'=> ['order'=>40, 'type'=>'html',   'html'=>$htmlHead],
				'dgRecon'  => ['order'=>60, 'label'=>lang('phreebooks_recon'), 'type'=>'datagrid', 'key'=>'manager'],
				'footRecon'=> ['order'=>90, 'type'=>'html', 'html'=>'</form>']],
            'javascript' => ['reconcile'=>$js],
            ];
		$layout = array_replace_recursive($layout, viewMain(), $data);
	}

	/**
     * List of un-reconciled records for a give gl account
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('phreebooks', 'recon', 3)) { return; }
		$sort   = clean('sort',  'text',   'post');
		$order  = clean('order', 'text',   'post');
		$period = clean('period','integer','post');
		$glAcct = clean('glAcct', 'text',  'post');
        if (!$period) { $period = getModuleCache('phreebooks', 'fy', 'period'); }
        if (!$glAcct) { $glAcct = getModuleCache('phreebooks', 'settings', 'customers', 'gl_cash'); }
		// load the payments and deposits that are open
		$sql = "SELECT i.id, m.post_date, m.journal_id, m.invoice_num, m.primary_name_b, m.description, i.debit_amount, i.credit_amount, i.reconciled
			FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id = i.ref_id
			WHERE i.gl_account='$glAcct' AND (i.reconciled=$period OR (i.reconciled=0 AND m.post_date<='".getModuleCache('phreebooks', 'fy', 'period_end')."'))";
        if ($sort=='' || $sort=='reference') { $sql .= " ORDER BY invoice_num"; }
        if ($sort=='post_date') { $sql .= " ORDER BY post_date"; }
        if ($order=='desc')     { $sql .= " DESC"; }
		$stillOpen  = dbGetResult($sql);
		$result= $stillOpen->fetchAll(\PDO::FETCH_ASSOC);
		$bank_list = [];
		foreach ($result as $row) {
			$reference = $row['journal_id']==19 ? $row['post_date'] : $row['invoice_num'];
			$bank_list[$reference][] = [
                'id'         => "rID_".$row['id'],
				'reference'  => $row['invoice_num'],
				'post_date'  => viewFormat($row['post_date'], 'date'),
				'title'      => $row['primary_name_b'] ? $row['primary_name_b'] : $row['description'],
				'deposit'    => $row['debit_amount']  != 0 ? $row['debit_amount']  : 0,
				'withdrawal' => $row['credit_amount'] != 0 ? $row['credit_amount'] : 0,
				'reconciled' => $row['reconciled'] ? $row['reconciled'] : 0, // period when reconciled or false if still open
				'rowChk'     => $row['reconciled'] ? 1 : 0];
		}
		// build treegrid structure
		$rows= [];
		$cnt   = 0;
		foreach ($bank_list as $entry) {
			if (sizeof($entry) > 1) { // make parent and link rows
				$total = 0;
				$allClear = true;
				foreach ($entry as $row) {
                    if (!$row['reconciled']) { $allClear = false; }
					$total           += $row['deposit'] - $row['withdrawal'];
					$row['_parentId'] = "pID_$cnt";
					$rows[]           = $row;
				}
				// build the parent
				$rows[] = [
                    'id'        => "pID_$cnt",
					'state'     => 'closed',
					'reference' => $row['reference'],
					'post_date' => $row['post_date'],
					'title'     => lang('multiple_entries'),
					'total'     => $total,
					'reconciled'=> $allClear ? $period : 0,
					'rowChk'    => $allClear ? 1 : 0];
				$cnt++;
			} else { // reformat to display as parent
				$entry[0]['total']= $entry[0]['deposit'] - $entry[0]['withdrawal'];
				$rows[]           = $entry[0];
			}
		}
		// Sort total here
		if ($sort=='total') {
			$temp = [];
            foreach ($rows as $key => $value) { $temp[$key] = isset($value['total']) ? $value['total'] : 0; }
			array_multisort($temp, $order=='desc' ? SORT_DESC : SORT_ASC, $rows);
		}
		// build the footer
		$stmt_balance= dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'stmt_balance', "gl_account='$glAcct' AND period=$period", false);
		$gl_balance  = dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'beginning_balance+debit_amount-credit_amount AS gl', "gl_account='$glAcct' AND period=".getModuleCache('phreebooks', 'fy', 'period'), false);
		$sql = "SELECT SUM(i.debit_amount - i.credit_amount) AS total
			FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id = i.ref_id
					WHERE i.gl_account='$glAcct' AND i.reconciled>$period AND i.reconciled<>0";
		$stmt = dbGetResult($sql);
		$not_in_period = $stmt->fetch(\PDO::FETCH_ASSOC);
		$footer = [
            ['title'=>lang('statement_balance'),  'total'=>$stmt_balance],
			['title'=>lang('cleared_this_period'),'total'=>0],
			['title'=>lang('oustanding_total'),   'total'=>''],
			['title'=>lang('gl_balance'),         'total'=>$gl_balance - $not_in_period['total']],
			['title'=>lang('unrecon_total'),      'total'=>'']];
		msgDebug("\nSending ".sizeof($rows)." rows! with rows = ".print_r($rows, true));
		$layout = array_replace_recursive($layout, ['content'=>['total'=>sizeof($rows),'rows'=>$rows,'footer'=>$footer]]);
	}

	/**
     * Creates treegrid structure for reconciliation
     * @param string $name - DOM field name
     * @return array - treegrid ready to render
     */
    private function tgReconcile($name)
    {
		$this->defaults = [
            'sort'  => 'reference',
			'order' => '',
			'period'=> getModuleCache('phreebooks', 'fy', 'period'),
			'glAcct'=> getModuleCache('phreebooks', 'settings', 'customers', 'gl_cash')];
		$stmt_balance = dbGetValue(BIZUNO_DB_PREFIX."journal_history", 'stmt_balance', "period='{$this->defaults['period']}' AND gl_account='{$this->defaults['glAcct']}'");
		return [
            'type' => 'treegrid',
			'id'    => $name,
			'title' => lang('phreebooks_recon'),
			'attr'  => [
                'url'          => BIZUNO_AJAX."&p=phreebooks/reconcile/managerRows",
				'toolbar'      => "#{$name}Toolbar",
				'singleSelect' => false,
				'showFooter'   => true,
				'animate'      => true,
				'pagination'   => false,
				'width'        => 1000,
//				'height'       => 600,
				'idField'      =>'id',
				'treeField'    =>'title'],
			'events' => [
                'onCheck'      => "function(row) { reconCheck(row); reconTotal(); }",
                'onCheckAll'   => "function(rows){ for (var i=0; i<rows.length; i++) { reconCheck(rows[i]); } reconTotal(); }",
                'onUncheck'    => "function(row) { reconUncheck(row); reconTotal(); }",
                'onUncheckAll' => "function(rows){ for (var i=0; i<rows.length; i++) { reconUncheck(rows[i]); } reconTotal(); }",
                'onLoadSuccess'=> "reconInit"],
			'source' => [
                'filters' => [
                    'period'=> ['order'=>10,
						'html'=> ['label'=>lang('period'), 'values'=>dbPeriodDropDown(false), 'attr'=>['type'=>'select','value'=>$this->defaults['period']]]],
					'glAcct'=> ['order'=>20,
						'html'=> ['label'=>pullTableLabel('journal_main', 'gl_acct_id'), 'values'=>dbGLDropDown(false,['0']), 'attr'=>['type'=>'select','value'=>$this->defaults['glAcct']]]]],
				'fields' => [
                    'stmt_balance' => ['order'=>70,
						'html'=> ['label'=>lang('statement_balance'),'styles'=>  ['text-align'=>'right'], 'attr'=>  ['value'=>viewFormat($stmt_balance, 'currency')]]]]],
			'columns' => [
                'id'         => ['order'=> 0,'attr'=>['hidden'=>true]],
				'reconciled' => ['order'=> 0,'attr'=>['hidden'=>true]],
				'reference'  => ['order'=>20,'label'=>lang('reference'),  'attr'=>['width'=>120,'resizable'=>true,'sortable'=>true]],
				'post_date'  => ['order'=>30,'label'=>lang('date'),       'attr'=>['width'=> 80,'resizable'=>true,'sortable'=>true]],
				'deposit'    => ['order'=>40,'label'=>lang('deposit'),    'attr'=>['width'=>100,'resizable'=>true,'align'=>'right'],
					'events'=>  ['formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'withdrawal' => ['order'=>50,'label'=>lang('withdrawal'), 'attr'=>['width'=>100,'resizable'=>true,'align'=>'right'],
					'events'=>  ['formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'title'      => ['order'=>60,'label'=>lang('description'),'attr'=>['width'=>275,'resizable'=>true]],
				'total'      => ['order'=>70,'label'=>lang('total'),      'attr'=>['width'=>100,'resizable'=>true,'sortable'=>true,'align'=>'right'],
					'events'=>  ['formatter'=>"function(value,row){ return formatCurrency(value); }"]],
				'cleared'    => ['order'=>80,'attr'=>['checkbox'=>true]]]];
	}
	
	/**
     * Saves the user selections for banking reconciliation
     * @return user message with status
     */
    public function save()
    {
        if (!$security = validateSecurity('phreebooks', 'recon', 3)) { return; }
		$balance  = clean('stmt_balance', 'currency', 'post');
		$period   = clean('period', 'integer', 'post');
		$dates    = dbGetFiscalDates($period);
		$glAcct   = clean('glAcct', 'text', 'post');
		$cleared  = clean('item_array', 'json', 'post');
		$rowsChk  = [];
		$rowsUnchk= [];
		foreach ($cleared as $row) {
            if (  isset($row['rowChk']) &&  $row['rowChk']  && strpos($row['id'], 'rID_')===0) { $rowsChk[]  = substr($row['id'], 4); } // not summary folder
            if ((!isset($row['rowChk']) || !$row['rowChk']) && strpos($row['id'], 'rID_')===0) { $rowsUnchk[]= substr($row['id'], 4); }
		}
		msgDebug("\nChecked rows = ".print_r($rowsChk, true));
		$result = dbGetMulti(BIZUNO_DB_PREFIX."journal_item", "gl_account='$glAcct' AND (reconciled=0 OR reconciled>=$period) AND post_date<='{$dates['end_date']}'", '', ['id', 'ref_id', 'reconciled']);
        if (sizeof($result) == 0) { return; } // nothing to do
		$toOpen     = [];
		$toOpenMain = [];
		$toClose    = [];
		$toCloseMain= [];
		foreach ($result as $row) {
			if (array_search($row['id'], $rowsChk) !== false && !$row['reconciled']) { // row has been checked, just queue if not already closed
				$toClose[]    = $row['id'];
				$toCloseMain[]= $row['ref_id'];
			}
			if (array_search($row['id'], $rowsUnchk) !== false && $row['reconciled']) { // row has been unchecked, just queue if not already open
				$toOpen[]    = $row['id'];
				$toOpenMain[]= $row['ref_id'];
			}
		}
		// Save the statement ending balance
		dbWrite(BIZUNO_DB_PREFIX."journal_history", ['stmt_balance'=>$balance, 'last_update'=>date('Y-m-d')], 'update', "period=$period AND gl_account='$glAcct'");
		msgDebug("\nClearing the following records: ".print_r($toClose, true));
        if (count($toClose)) { dbWrite(BIZUNO_DB_PREFIX."journal_item", ['reconciled'=>$period], 'update', "id IN (".implode(',', $toClose).")"); }
		msgDebug("\nUn-Clearing the following records: ".print_r($toOpen, true));
        if (count($toOpen)) { dbWrite(BIZUNO_DB_PREFIX."journal_item", ['reconciled'=>0], 'update', "reconciled=$period AND id IN (".implode(',', $toOpen).")"); }
		// load all cash gl accounts
		$glCash = [];
        foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $glAcct => $settings) { if ($settings['type'] == 0) { $glCash[] = $glAcct; } }
		msgDebug("\nUpdating cash accounts: ".print_r($glCash, true));
		// closes if any cash records within the journal main that are reconciled
		msgDebug("\nClose cash main records: ".print_r($toCloseMain, true));
		if (sizeof($toCloseMain)) { 
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id SET m.closed='1' 
                WHERE m.id IN (".implode(",", $toCloseMain).") AND i.reconciled>0 AND i.gl_account IN ('".implode("','", $glCash)."')");
        }
		// re-opens if any cash records within the journal main that are not reconciled
		msgDebug("\nOpen cash main records: ".print_r($toOpenMain, true));
		if (sizeof($toOpenMain)) {
            dbGetResult("UPDATE ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id SET m.closed='0' 
                WHERE m.id IN (".implode(",", $toOpenMain).") AND i.reconciled=0 AND i.gl_account IN ('".implode("','", $glCash)."')");
        }
		msgAdd(lang('msg_database_write'),'success');
		msgLog(lang('phreebooks_recon').' - '.lang('save')." ($glAcct)");
	}
}