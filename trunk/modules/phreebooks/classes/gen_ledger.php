<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/phreebooks/classes/gen_ledger.php
//

class journal {
  function __construct($id=0, $verbose=true) {
  	$this->affected_accounts= array();
  	$this->post_ids         = array();
  	$this->unpost_ids       = array();
  	$this->cogs_entry       = array();
  	if ($id) $this->journal($id);
  }
	
  function journal($id=0, $verbose=true) {
	global $db, $messageStack;
	$result = $db->Execute("select * from " . TABLE_JOURNAL_MAIN . " where id = " . (int)$id);
	// make sure we have a record or die (there's a problem that needs to be fixed)
	if ($result->RecordCount() == 0) return $this->fail_message(GL_ERROR_DIED_CREATING_RECORD . $id);
	foreach ($result->fields as $key => $value) $this->$key = $value;
	$this->journal_main_array = $this->build_journal_main_array();	// build ledger main record
	$result = $db->Execute("select * from " . TABLE_JOURNAL_ITEM . " where ref_id = " . (int)$id);
	$this->journal_rows = array();
	$i = 0;
	while (!$result->EOF) {
	  foreach ($result->fields as $key => $value) $this->journal_rows[$i][$key] = $value;
	  $i++;
	  $result->MoveNext();
	}
  }

/*******************************************************************************************************************/
// START Post Journal Function
/*******************************************************************************************************************/
  function Post($action = 'insert', $skip_balance = false) {
	global $messageStack;
	if (!isset($this->id) || $this->id == '') $this->id = 0;
	$this->unpost_ids = $this->check_for_re_post();
	if ($action == 'edit') {
		$orig_post = new journal($this->id); // read in the original journal entry to get post order
		$idx = substr($orig_post->post_date, 0, 10).':'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
		$this->unpost_ids[$idx] = $this->id;
	}
	$idx = substr($this->post_date, 0, 10).':'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
	$this->post_ids[$idx] = clone $this; // save variables for later to post
	$this->first_period = $this->period;
	// start unposting all affected records
	if (sizeof($this->unpost_ids) > 0) {
	  krsort($this->unpost_ids); // unpost in reverse order
	  $messageStack->debug("\nStarting to unPost reverse sorted id array = " . arr2string($this->unpost_ids));
	  while (true) {
		if (!$id = array_shift($this->unpost_ids)) break; // no more to unPost, exit loop
		$messageStack->debug("\n/********* unPosting journal_main id = $id");
		$unPost = new journal($id, false);
		if (!isset($unPost->id)) continue; // already has been unposted, skip
		if ($this->id <> $id) { // re-queue to post if not current entry
			$idx = substr($unPost->post_date, 0, 10).':'.str_pad($id, 8, '0', STR_PAD_LEFT);
			$this->post_ids[$idx] = clone $unPost;
		}
		if (!$unPost->unPost('edit', true)) return false;
		$this->first_period      = min($this->first_period, $unPost->period);
		$this->affected_accounts = gen_array_key_merge($this->affected_accounts, $unPost->affected_accounts);
		// add the new post_ids to the arrays, one for now, one for re-post loop later
		$this->unpost_ids += $unPost->unpost_ids;
		$messageStack->debug("\n  unPosting array now looks like = " . arr2string($this->unpost_ids));
		$messageStack->debug("\n  re-Posting array keys now looks like = " . arr2string(array_keys($this->post_ids)));
//		$unPost->post_ids = array(); // clear nested unPost to zero, so it doesn't re-post
	  }
	}
	// Post entry and rePost any journal entries unPosted
	ksort($this->post_ids); // re-post in post_date/record_id ascending order
	$messageStack->debug("\nStarting to Post indexes to be Posted = " . arr2string(array_keys($this->post_ids)));
	while ($glEntry = array_shift($this->post_ids)) {
		$messageStack->debug("\n/********* Posting Journal main ... id = $glEntry->id and journal_id = $glEntry->journal_id");
		$this->repost_ids = $glEntry->check_for_re_post();
		$glEntry->remove_cogs_rows(); // they will be regenerated during the post
		$messageStack->debug("\n  journal_main array = " . arr2string($glEntry->journal_main_array));
		db_perform(TABLE_JOURNAL_MAIN, $glEntry->journal_main_array, 'insert');
		if (!$glEntry->id) $this->id = $glEntry->id = db_insert_id();
		// post journal rows
		$messageStack->debug("\n  Posting Journal rows ...");
		for ($i = 0; $i < count($glEntry->journal_rows); $i++) {
		  $messageStack->debug("\n  journal_rows = " . arr2string($glEntry->journal_rows[$i]));
		  $glEntry->journal_rows[$i]['ref_id'] = $glEntry->id;	// link the rows to the journal main id
		  db_perform(TABLE_JOURNAL_ITEM, $glEntry->journal_rows[$i], 'insert');
		  if (!$glEntry->journal_rows[$i]['id']) $glEntry->journal_rows[$i]['id'] = db_insert_id();
		}
		$messageStack->debug("\nStarting auxilliary post functions ...");
	  	// Inventory needs to be posted first because function may add additional journal rows for COGS
		if (!$glEntry->Post_inventory())               return false; 
		if (!$glEntry->Post_chart_balances())          return false;
		if (!$glEntry->Post_account_sales_purchases()) return false;
		$this->affected_accounts = gen_array_key_merge($this->affected_accounts, $glEntry->affected_accounts);
		$this->first_period = min($this->first_period, $glEntry->period);
		if (sizeof($this->repost_ids) > 0) {
			ksort($this->repost_ids); // repost by post date
			$messageStack->debug("\nStarting to rePost entries queued from first pass, sorted id array = ".arr2string($this->repost_ids));
			while (true) {
				if (!$id = array_shift($this->repost_ids)) break; // no more to unPost, exit loop
				$messageStack->debug("\n/********* rePosting journal_main id = $id");
				if ($this->id == $id) continue; // don't repost current post
				$rePost = new journal($id, false);
				if (!$rePost->Post('edit', true)) return false;
				// add the new post_ids to the arrays, one for now, one for re-post loop later
				$this->repost_ids += $rePost->repost_ids;
				$messageStack->debug("\n  rePosting array now looks like = " . arr2string($this->repost_ids));
			}
		}
		if (!$glEntry->check_for_closed_po_so('Post')) return false;
		$messageStack->debug("\n*************** end Posting Journal ******************* id = $this->id\n");
	}
	if (!$skip_balance) if (!$this->update_chart_history_periods($this->first_period)) return false;
	return true;
  }

  function unPost($action = 'delete', $skip_balance = false) {
	global $db, $messageStack;
	$messageStack->debug("\nunPosting Journal... id = $this->id and action = $action and journal_id = $this->journal_id");
	$this->unpost_ids = $this->check_for_re_post();
	if (!$this->unPost_account_sales_purchases()) return false;	// unPost the customer/vendor history
	// unPost_chart_balances needs to be unPosted before inventory because inventory may remove journal rows (COGS)
	if (!$this->unPost_chart_balances()) return false;	// unPost the chart of account values
	if (!$this->unPost_inventory()) return false;
	$messageStack->debug("\n  Deleting Journal main and rows as part of unPost ...");
	$result = $db->Execute("delete from " . TABLE_JOURNAL_MAIN . " where id = " . $this->id);		
	if ($result->AffectedRows() <> 1) return $this->fail_message(GL_ERROR_CANNOT_DELETE_MAIN);
	$result = $db->Execute("delete from " . TABLE_JOURNAL_ITEM . " where ref_id = " . $this->id);
	if ($result->AffectedRows() == 0 ) return $this->fail_message(printf(GL_ERROR_CANNOT_DELETE_ITEM, $this->id));
	if ($action == 'delete') { // re-post affected entries unless edited (which is after the entry is reposted)
	  if (is_array($this->unpost_ids)) { // rePost any journal entries unPosted to rollback COGS calculation
	  	ksort($this->unpost_ids);
		while ($id = array_shift($this->unpost_ids)) {
		  $messageStack->debug("\nRe-posting as part of unPost - Journal main id = " . $id);
		  $rePost = new journal($id, false);
		  if (!isset($rePost->id)) continue; // already has been unposted, skip
		  $rePost->remove_cogs_rows(); // they will be regenerated during the re-post
		  if (!$rePost->Post('edit', true)) return false;
		  $this->affected_accounts = gen_array_key_merge($this->affected_accounts, $rePost->affected_accounts);
		  $this->first_period = min($this->first_period, $rePost->first_period);
		}
	  }
	}
	if (!$skip_balance) if (!$this->update_chart_history_periods($this->period)) return false;
	if (!$this->check_for_closed_po_so('unPost')) return false; // check to re-open predecessor entry
	$messageStack->debug("\nend unPosting Journal.\n\n");
	return true;
  }

/*******************************************************************************************************************/
// END Post Journal Function
/*******************************************************************************************************************/
// START re-post Functions
/*******************************************************************************************************************/
  function check_for_re_post() {
	global $db, $messageStack;
	$messageStack->debug("\n  Checking for re-post records ... ");
	$repost_ids = array();
	$gl_type = NULL;
	switch ($this->journal_id) {
	  case  6: // Purchase/Receive Journal
		$skus = array();
	  	foreach ($this->journal_rows as $row) if ($row['sku'] <> '') $skus[] = $row['sku'];
	  	if (sizeof($skus) > 0) {
	  	  $result = $db->Execute("SELECT sku FROM ".TABLE_INVENTORY." WHERE sku IN ('".implode("', '", $skus)."') AND cost_method='a'");
	  	  $askus = array();
	  	  while (!$result->EOF) {
	  		$askus[] = $result->fields['sku'];
	  		$result->MoveNext();
	  	  }
	  	  if (sizeof($askus) > 0) {
		    $messageStack->debug("\n    Finding re-post ids for average sku list = ".arr2string($askus)." and post_date after $this->post_date");
	  	  	$result = $db->Execute("SELECT ref_id, post_date FROM ".TABLE_JOURNAL_ITEM." WHERE sku IN ('".implode("', '", $askus)."') AND post_date > '$this->post_date'");
	  	  	while (!$result->EOF) {
		  	  $messageStack->debug("\n    check_for_re_post is queing for average cost record id = ".$result->fields['ref_id']);
		  	  $idx = substr($result->fields['post_date'], 0, 10).':'.str_pad($result->fields['ref_id'], 8, '0', STR_PAD_LEFT);
	  	  	  $repost_ids[$idx] = $result->fields['ref_id'];
	  	  	  $result->MoveNext();
	  	  	}
	  	  }
	  	}
	  	// continue with more tests
	  case  7: // Purchase Credit Memo Journal
	  case 12: // Sales/Invoice Journal
	  case 13: // Sales Credit Memo Journal
	  case 14: // Inventory Assembly Journal
	  case 16: // Inventory Adjustment Journal
	  case 19: // POS Journal
	  case 21: // Inventory Direct Purchase Journal
	  	if ($this->id) for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
			// check to see if any future postings relied on this record, queue to re-post if so.
			$sql = "SELECT id FROM ".TABLE_INVENTORY_HISTORY." WHERE ref_id=$this->id AND sku='".$this->journal_rows[$i]['sku']."'";
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$sql = "SELECT journal_main_id FROM ".TABLE_INVENTORY_COGS_USAGE." WHERE inventory_history_id=".$result->fields['id'];
				$result = $db->Execute($sql);
				while (!$result->EOF) {
				  if ($result->fields['journal_main_id'] <> $this->id) {
			  		$messageStack->debug("\n    check_for_re_post is queing for cogs usage id = " . $result->fields['journal_main_id']);
				  	$p_date = $db->Execute("SELECT post_date FROM ".TABLE_JOURNAL_MAIN." WHERE id=".$result->fields['journal_main_id']);
			  		$idx = substr($p_date->fields['post_date'], 0, 10).':'.str_pad($result->fields['journal_main_id'], 8, '0', STR_PAD_LEFT);
				  	$repost_ids[$idx] = $result->fields['journal_main_id'];
				  }
				  $result->MoveNext();
				}
			}
	  	}
	  	// find if any COGS owed for items
	  	foreach ($this->journal_rows as $row) if ($row['sku']) {
	  		if (($row['qty']>0 && in_array($this->journal_id, array(6, 13, 14, 16))) || ($row['qty'] < 0 && in_array($this->journal_id, array(7, 12)))) {
			  	$sql = "SELECT id, journal_main_id, qty, post_date FROM ".TABLE_INVENTORY_COGS_OWED." WHERE sku='".$row['sku']."'";
			  	if (ENABLE_MULTI_BRANCH) $sql .= " AND store_id = " . $this->store_id;
			  	$sql .= " ORDER BY post_date, id";
			  	$result = $db->Execute($sql);
			  	$inv_qoh = $db->Execute("SELECT SUM(remaining) as remaining FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='".$row['sku']."' AND remaining>0");
			  	$working_qty = $row['qty'] + $inv_qoh->fields['remaining'];
			  	while (!$result->EOF) {
			  		if ($working_qty >= $result->fields['qty']) { // repost this journal entry and remove the owed record since we will repost all the negative quantities necessary
			  			if ($result->fields['journal_main_id'] <> $this->id) { // prevent infinite loop
			  				$messageStack->debug("\n    check_for_re_post is queing for cogs owed, id = " . $result->fields['journal_main_id'] . " to re-post.");
			  				$idx = substr($result->fields['post_date'], 0, 10).':'.str_pad($result->fields['journal_main_id'], 8, '0', STR_PAD_LEFT);
			  				$repost_ids[$idx] = $result->fields['journal_main_id'];
			  			}
			  			$db->Execute("DELETE FROM " . TABLE_INVENTORY_COGS_OWED . " WHERE id = " . $result->fields['id']);
			  		}
			  		$working_qty -= $result->fields['qty'];
			  		if ($working_qty <= 0) break;
			  		$result->MoveNext();
			  	}
	  		}
	  	}
		// Check for payments or receipts made to this record that will need to be re-posted.
		if ($this->id) {
		  	$sql = "SELECT ref_id, post_date FROM ".TABLE_JOURNAL_ITEM." WHERE so_po_item_ref_id = $this->id AND gl_type in ('chk', 'pmt')";
			$result = $db->Execute($sql);
			while(!$result->EOF) {
			  $messageStack->debug("\n    check_for_re_post is queing for payment id = " . $result->fields['ref_id']);
		  	  $idx = substr($result->fields['post_date'], 0, 10).':'.str_pad($result->fields['ref_id'], 8, '0', STR_PAD_LEFT);
			  $repost_ids[$idx] = $result->fields['ref_id'];
			  $result->MoveNext();
			}
		}
		$messageStack->debug(" end Checking for Re-post.");
		break;
	  case  2: // General Journal
	  case  3: // Purchase Quote Journal
	  case  4: // Purchase Order Journal
	  case  9: // Sales Quote Journal
	  case 10: // Sales Order Journal
	  case 18: // Cash Receipts Journal
	  case 20: // Cash Distribution Journal
	  default: $messageStack->debug(" end check for Re-post with no action.");
	}
    return $repost_ids;
  }

/*******************************************************************************************************************/
// START Chart of Accout Functions
/*******************************************************************************************************************/
  function Post_chart_balances() {
	global $db, $messageStack, $currencies;
	$messageStack->debug("\n  Posting Chart Balances...");
	switch ($this->journal_id) {
	  case  2: // General Journal
	  case  6: // Purchase/Receive Journal
	  case  7: // Purchase Credit Memo Journal
	  case 12: // Sales/Invoice Journal
	  case 13: // Sales Credit Memo Journal
	  case 14: // Inventory Assembly Journal
	  case 16: // Inventory Adjustment Journal
	  case 18: // Cash Receipts Journal
	  case 19: // POS Journal
	  case 20: // Cash Distribution Journal
	  case 21: // Inventory Direct Purchase Journal
		$accounts = array();
		$precision = $this->currencies[DEFAULT_CURRENCY]['decimal_places'] + 2;
	    if (sizeof($this->journal_rows) > 0) foreach ($this->journal_rows as $value) {
		  $credit_amount = ($value['credit_amount']) ? $value['credit_amount'] : '0';
		  $debit_amount  = ($value['debit_amount'])  ? $value['debit_amount']  : '0';
		  if  (round($credit_amount, $precision) <> 0 || round($debit_amount, $precision) <> 0) {
			$accounts[$value['gl_account']]['credit'] += $credit_amount;
			$accounts[$value['gl_account']]['debit']  += $debit_amount;
		    $this->affected_accounts[$value['gl_account']] = 1;
		  }
		}
		if (sizeof($accounts) > 0) foreach ($accounts as $gl_acct => $values) {
		  if  (round($values['credit'], $precision) <> 0 || round($values['debit'], $precision) <> 0) {
		    $sql = "UPDATE " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " SET 
			  credit_amount = credit_amount + ".$values['credit'].", debit_amount = debit_amount + ".$values['debit'].", 
			  last_update = '{$this->post_date}' WHERE account_id = '$gl_acct' AND period = $this->period";
		    $messageStack->debug("\n    Post chart balances: credit_amount = ".$values['credit'].", debit_amount = ".$values['debit'].", acct = $gl_acct, period = $this->period");
		    $result = $db->Execute($sql);
		    if ($result->AffectedRows() <> 1) return $this->fail_message(GL_ERROR_POSTING_CHART_BALANCES . ($gl_acct ? $gl_acct : TEXT_NOT_SPECIFIED));
		  }
		}
		$messageStack->debug("\n  end Posting Chart Balances.");
		break;
	  case  3: // Purchase Quote Journal
	  case  4: // Purchase Order Journal
	  case  9: // Sales Quote Journal
	  case 10: // Sales Order Journal
	  default: $messageStack->debug(" end Posting Chart Balances with no action.");
	}
	return true;
  }

  function unPost_chart_balances() {
	global $db, $messageStack;
	$messageStack->debug("\n  unPosting Chart Balances...");
	switch ($this->journal_id) {
	  case  2: // General Journal
	  case  6: // Purchase/Receive Journal
	  case  7: // Purchase Credit Memo Journal
	  case 12: // Sales/Invoice Journal
	  case 13: // Sales Credit Memo Journal
	  case 14: // Inventory Assembly Journal
	  case 16: // Inventory Adjustment Journal
	  case 18: // Cash Receipts Journal
	  case 19: // POS Journal
	  case 20: // Cash Distribution Journal
	  case 21: // Inventory Direct Purchase Journal
		for ($i=0; $i<count($this->journal_rows); $i++) {
		  // Update chart of accounts history 
		  $sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " set 
			credit_amount = credit_amount - " . $this->journal_rows[$i]['credit_amount'] . ", 
			debit_amount = debit_amount - " . $this->journal_rows[$i]['debit_amount'] . " 
			where account_id = '" . $this->journal_rows[$i]['gl_account'] . "' and period = " . $this->period;
		  $messageStack->debug("\n    unPost chart balances: credit_amount = " . $this->journal_rows[$i]['credit_amount'] . ", debit_amount = " . $this->journal_rows[$i]['debit_amount'] . ", acct = " . $this->journal_rows[$i]['gl_account'] . ", period = " . $this->period);
		  $coa_update = $db->Execute($sql);
		  $this->affected_accounts[$this->journal_rows[$i]['gl_account']] = 1;
		}
		$messageStack->debug("\n  end unPosting Chart Balances.");
		break;
	  case  3: // Purchase Quote Journal
	  case  4: // Purchase Order Journal
	  case  9: // Sales Quote Journal
	  case 10: // Sales Order Journal
	  default:
		$messageStack->debug(" end unPosting Chart Balances with no action.");
	}
	return true;
  }

// *********  chart of account support functions  **********
  function update_chart_history_periods($period = CURRENT_ACCOUNTING_PERIOD) {
	global $db, $messageStack;
	switch ($this->journal_id) {
	  case  3: // Purchase Quote
	  case  4: // Purchase Order
	  case  9: // Sales Quote
	  case 10: // Sales Order
		$messageStack->debug("\n    Returning from Update Chart History Periods with no action required.");
		return true;
	  default:
	}
	// first find out the last period with data in the system from the current_status table
	$sql = "select fiscal_year from " . TABLE_ACCOUNTING_PERIODS . " where period = " . $period;
	$result = $db->Execute($sql);
	if ($result->EOF) return $this->fail_message(GL_ERROR_BAD_ACCT_PERIOD);
	$fiscal_year = $result->fields['fiscal_year'];

	$sql = "select max(period) as period from " . TABLE_ACCOUNTING_PERIODS . " where fiscal_year = " . $fiscal_year;
	$result = $db->Execute($sql);
	$max_period = $result->fields['period'];
	$affected_acct_string = (is_array($this->affected_accounts)) ? implode("', '", array_keys($this->affected_accounts)) : '';
	$messageStack->debug("\n  Updating chart history for fiscal year: " . $fiscal_year . " and period: " . $period . " for accounts: ('" . $affected_acct_string . "')");
	for ($i = $period; $i <= $max_period; $i++) {
	  if (!$this->validate_balance($i)) return false;
	  // update future months
	  $sql = "select account_id, beginning_balance + debit_amount - credit_amount as beginning_balance 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		where account_id in ('" . $affected_acct_string . "') and period = " . $i;
	  $result = $db->Execute($sql);
	  while (!$result->EOF) {
		$sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		  set beginning_balance = " . $result->fields['beginning_balance'] . " 
		  where period = " . ($i + 1) . " and account_id = '" . $result->fields['account_id'] . "'";
		$db->Execute($sql);
		$result->MoveNext();
	  }
	}
	// see if there is another fiscal year to roll into
	$sql = "select fiscal_year from " . TABLE_ACCOUNTING_PERIODS . " where period = " . ($max_period + 1);
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) { // close balances for end of this fiscal year and roll post into next fiscal year
	  // select retained earnings account
	  $sql = "select id from " . TABLE_CHART_OF_ACCOUNTS . " where account_type = 44";
	  $result = $db->Execute($sql);
	  if ($result->RecordCount() <> 1) $this->fail_message(GL_ERROR_NO_RETAINED_EARNINGS_ACCOUNT);
	  $retained_earnings_acct = $result->fields['id'];
	  $this->affected_accounts[$retained_earnings_acct] = 1;
	  // select list of accounts that need to be closed, adjusted
	  $sql = "select id from " . TABLE_CHART_OF_ACCOUNTS . " where account_type in (30, 32, 34, 42, 44)";
	  $result = $db->Execute($sql);
	  $acct_list = array();
	  while(!$result->EOF) {
		$acct_list[] = $result->fields['id'];
		$result->MoveNext();
	  }
	  $acct_string = implode("','",$acct_list);
	  // fetch the totals for the closed accounts
	  $sql = "select sum(beginning_balance + debit_amount - credit_amount) as retained_earnings 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		where account_id in ('" . $acct_string . "') and period = " . $max_period;
	  $result = $db->Execute($sql);
	  $retained_earnings = $result->fields['retained_earnings'];
	  // clear out the expense, sales, cogs, and other year end accounts that need to be closed
	  // needs to be before writing retained earnings account, since retained earnings is part of acct_string
	  $sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		set beginning_balance = 0 
		where account_id in ('" . $acct_string . "') and period = " . ($max_period + 1);
	  $result = $db->Execute($sql);
	  // update the retained earnings account
	  $sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		set beginning_balance = " . $retained_earnings . " 
		where account_id = '" . $retained_earnings_acct . "' and period = " . ($max_period + 1);
	  $result = $db->Execute($sql);
	  // now continue rolling in current post into next fiscal year
	  if (!$this->update_chart_history_periods($max_period + 1)) return false;
	}
	// all historical chart of account balances from period on should be OK at this point.
	$messageStack->debug("\n  end Updating chart history periods. Fiscal Year: " . $fiscal_year);;
	return true;
  }

  function validate_balance($period = CURRENT_ACCOUNTING_PERIOD) {
	global $db, $currencies, $messageStack;
	$messageStack->debug("\n    Validating trial balance for period: " . $period . " ... ");
	$sql = "select sum(debit_amount) as debit, sum(credit_amount) as credit 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where period = " . $period;
	$result = $db->Execute($sql);
	// check to see if we are still in balance, round debits and credits and compare
	$messageStack->debug(" debits = " . $result->fields['debit'] . " and credits = " . $result->fields['credit']);
	$debit_total  = round($result->fields['debit'],  $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
	$credit_total = round($result->fields['credit'], $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
	if ($debit_total <> $credit_total) { // Trouble in paradise, fraction of cents adjustment next
	  $tolerance = 2 * (1 / pow(10, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places'])); // i.e. 2 cents in USD
	  $adjustment = $result->fields['credit'] - $result->fields['debit'];
	  if (abs($adjustment) > $tolerance) {
		return $this->fail_message(sprintf(GL_ERROR_TRIAL_BALANCE, $result->fields['debit'], $result->fields['credit'], $period));
	  }
	  // find the adjustment account
	  if (!defined('ROUNDING_GL_ACCOUNT') || ROUNDING_GL_ACCOUNT == '') {
		$result = $db->Execute("select id from " . TABLE_CHART_OF_ACCOUNTS . " where account_type = 44 limit 1");
		if ($result->RecordCount() == 0) {
		  return $this->fail_message('Failed trying to locate retained earnings account to make rounding adjustment. There must be one and only one Retained Earnings account in the chart of accounts!');
		}
		$adj_gl_account = $result->fields['id'];
	  } else {
		$adj_gl_account = ROUNDING_GL_ACCOUNT;
	  }
	  $messageStack->debug("\n      Adjusting balance, adjustment = " . $adjustment . " and gl account = " . $adj_gl_account);
	  $sql = "update " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
		set debit_amount = debit_amount + " . $adjustment . " 
		where period = " . $period . " and account_id = '" . $adj_gl_account . "'";
	  $result = $db->Execute($sql);
	}
	$messageStack->debug(" ... End Validating trial balance.");
	return true;
  }

/*******************************************************************************************************************/
// END Chart of Accout Functions
/*******************************************************************************************************************/
// START Customer/Vendor Account Functions
/*******************************************************************************************************************/
// Post the customers/vendors sales/purchases values for the given period
  function Post_account_sales_purchases() {
	global $db, $messageStack;
	$messageStack->debug("\n  Posting account sales and purchases ...");
	switch ($this->journal_id) {
	  case 19:
	  case 21: if (!$this->bill_acct_id) return true; // no sales history in POS if no bill account id, else continue
	  case  6:
	  case  7:
	  case 12:
	  case 13:
	  case 18:
	  case 20:
		if (!$this->bill_acct_id) return $this->fail_message(GL_ERROR_NO_GL_ACCT_NUMBER . 'post_account_sales_purchases.');
		$purchase_invoice_id = $this->purchase_invoice_id ? $this->purchase_invoice_id : $this->journal_main_array['purchase_invoice_id'];
		$history_array = array(
		  'ref_id'              => $this->id, 
		  'so_po_ref_id'        => $this->so_po_ref_id,
		  'acct_id'             => $this->bill_acct_id, 
		  'journal_id'          => $this->journal_id, 
		  'purchase_invoice_id' => $purchase_invoice_id,
		  'amount'              => $this->total_amount,
		  'post_date'           => $this->post_date,
		);
		$result = db_perform(TABLE_ACCOUNTS_HISTORY, $history_array, 'insert');
		if ($result->AffectedRows() <> 1 ) return $this->fail_message(GL_ERROR_UPDATING_ACCOUNT_HISTORY);
		$messageStack->debug(" end Posting account sales and purchases.");
		break;
	  case  2:
	  case  3:
	  case  4:
	  case  9:
	  case 10:
	  case 14:
	  case 16:
	  default: // nothing required to do
		$messageStack->debug(" end Posting account sales and purchases with no action.");
	}
	return true;
  }

  function unPost_account_sales_purchases() {
	global $db, $messageStack;
	$messageStack->debug("\n  unPosting account sales and purchases ...");
	switch ($this->journal_id) {
	  case 19:
	  case 21: if (!$this->bill_acct_id) return true; // no sales history in POS if no bill account id, else continue
	  case  6:
	  case  7:
	  case 12:
	  case 13:
	  case 18:
	  case 20:
		if (!$this->bill_acct_id) return $this->fail_message(GL_ERROR_NO_GL_ACCT_NUMBER . 'unPost_account_sales_purchases.');
		$result = $db->Execute("delete from " . TABLE_ACCOUNTS_HISTORY . " where ref_id = " . $this->id);		
		if ($result->AffectedRows() <> 1) return $this->fail_message(GL_ERROR_DELETING_ACCOUNT_HISTORY);
		$messageStack->debug(" end unPosting account sales and purchases.");
		break;
	  case  2:
	  case  3:
	  case  4:
	  case  9:
	  case 10:
	  case 14:
	  case 16:
	  default: // nothing required to do
		$messageStack->debug(" end unPosting account sales and purchases with no action.");
	}
	return true;
  }

/*******************************************************************************************************************/
// END Customer/Vendor Account Functions
/*******************************************************************************************************************/
// START Inventory Functions
/*******************************************************************************************************************/
  function Post_inventory() {
	global $db, $messageStack;
	$messageStack->debug("\n  Posting Inventory ...");
	switch ($this->journal_id) { // Pre-posting particulars that are journal dependent
	  case  4:
		$str_field       = 'quantity_on_order';
		$item_array      = $this->load_so_po_balance($this->id);
		break;
	  case  6: 
		$str_field       = 'quantity_on_hand';
		$so_po_str_field = 'quantity_on_order';
		$item_array      = $this->load_so_po_balance($this->so_po_ref_id, $this->id);
		break;
	  case 10:
		$str_field       = 'quantity_on_sales_order';
		$item_array      = $this->load_so_po_balance($this->id);
		break;
	  case 12:
	  case 19:
		$str_field       = 'quantity_on_hand'; 
		$so_po_str_field = 'quantity_on_sales_order';
		$item_array      = $this->load_so_po_balance($this->so_po_ref_id, $this->id);
		break;
	  case  7: 
	  case 13:
	  case 14:
	  case 16:
	  case 21:
		$str_field       = 'quantity_on_hand';
		break;
	  case  2:
	  case  3:
	  case  9:
	  case 18:
	  case 20:
	  default: 
		$messageStack->debug(" end Posting Inventory not requiring any action.");
		return true;
	}
	// adjust inventory stock status levels (also fills inv_list array)
	$item_rows_to_process = count($this->journal_rows); // NOTE: variable needs to be here because journal_rows may grow within for loop (COGS)
	for ($i = 0; $i < $item_rows_to_process; $i++) {
	  if ($this->journal_rows[$i]['sku']) {
		if ($this->journal_rows[$i]['debit_amount'])  $price = $this->journal_rows[$i]['debit_amount']  / $this->journal_rows[$i]['qty'];
		if ($this->journal_rows[$i]['credit_amount']) $price = $this->journal_rows[$i]['credit_amount'] / $this->journal_rows[$i]['qty'];
		$inv_list = array(
		  'id'                => $this->journal_rows[$i]['id'],
		  'gl_type'           => $this->journal_rows[$i]['gl_type'],
		  'so_po_item_ref_id' => $this->journal_rows[$i]['so_po_item_ref_id'],
		  'sku'               => $this->journal_rows[$i]['sku'], 
		  'description'       => $this->journal_rows[$i]['description'], 
		  'serialize_number'  => $this->journal_rows[$i]['serialize_number'], 
		  'qty'               => $this->journal_rows[$i]['qty'], 
		  'price'             => $price, 
		  'store_id'          => $this->store_id,
		  'post_date'         => $this->post_date,
		);
		switch ($this->journal_id) {
		  case 4:
		  case 10:
			$adjustment = ($item_array[$inv_list['id']]['processed'] > 0) ? $item_array[$inv_list['id']]['processed'] : 0;
			if ($this->closed) $adjustment = $this->journal_rows[$i]['qty'];
			$item_cost  = ($this->journal_id ==  4) ? $inv_list['price'] : 0;
			$full_price = ($this->journal_id == 10) ? $inv_list['price'] : 0;
			if (!$this->update_inventory_status($inv_list['sku'], $str_field, -$adjustment, $item_cost, $inv_list['description'], $full_price)) return false;							
			break;
		  case 12: // a sale so make quantity negative (pulling from inventory) and continue
		  case 19:
			$inv_list['qty'] = -$inv_list['qty']; 
		  case  6:
		  case 21:
			if (!$this->calculate_COGS($inv_list)) return false;
			if ($inv_list['so_po_item_ref_id']) { // check for reference to po/so to adjust qty on order/sales order
			  // do not allow qty on order to go below zero.
			  $bal_before_post = $item_array[$inv_list['so_po_item_ref_id']]['ordered'] - $item_array[$inv_list['so_po_item_ref_id']]['processed'] + $this->journal_rows[$i]['qty'];
			  $adjustment = -(min($this->journal_rows[$i]['qty'], $bal_before_post));
			  if (!$this->update_inventory_status($inv_list['sku'], $so_po_str_field, $adjustment)) return false;
			}
			break;
		  case 14:
			$assy_cost = $this->calculate_assembly_list($inv_list); // for assembly parts list
			if ($assy_cost === false) return false; // there was an error
			break;
		  case  7: // a vendor credit memo, negate the quantity and process same as customer credit memo
			$inv_list['qty'] = -$inv_list['qty']; 
		  case 13: // a customer credit memo, qty stays positive
		  case 16:
			if (!$this->calculate_COGS($inv_list)) return false;
			break;
		  default: // nothing
		}
	  }
	}
	// build the cogs rows
	if (sizeof($this->cogs_entry) > 0) foreach ($this->cogs_entry as $gl_acct => $values) {
	  $temp_array = array(
		'ref_id'        => $this->id,
		'gl_type'       => 'cog',		// code for cost of goods charges
		'description'   => GL_JOURNAL_ENTRY_COGS,
		'gl_account'    => $gl_acct,
		'credit_amount' => $values['credit'] ? $values['credit'] : 0,
		'debit_amount'  => $values['debit']  ? $values['debit']  : 0,
		'post_date'     => $this->post_date,
	  );
	  db_perform(TABLE_JOURNAL_ITEM, $temp_array, 'insert');
	  $temp_array['id']     = db_insert_id();
	  $this->journal_rows[] = $temp_array;
	}
	// update inventory status
	for ($i = 0; $i < count($this->journal_rows); $i++) {
	  $post_qty   = $this->journal_rows[$i]['qty'];
	  $item_cost  = 0;
	  $full_price = 0;
	  switch ($this->journal_id) {
		case  4:
		  if (ENABLE_AUTO_ITEM_COST == 'PO' && $this->journal_rows[$i]['qty']) $item_cost = $this->journal_rows[$i]['debit_amount'] / $this->journal_rows[$i]['qty'];
		  break;
		case  6:
		case 21:
		  if (ENABLE_AUTO_ITEM_COST == 'PR' && $this->journal_rows[$i]['qty']) $item_cost = $this->journal_rows[$i]['debit_amount'] / $this->journal_rows[$i]['qty'];
		  break;
		case 12:
		  if ($this->journal_rows[$i]['qty']) $full_price = $this->journal_rows[$i]['credit_amount'] / $this->journal_rows[$i]['qty'];
		case  7:
		case 19:
		  $post_qty = -$post_qty;
		  break;
		case 14:
		  if ($i == 0 && $this->journal_rows[$i]['qty'] > 0) { // only for the item being assembled
			$item_cost = $this->journal_rows[$i]['debit_amount'] / $this->journal_rows[$i]['qty'];
		  }
		  break;
		default:
	  }
	  if (!$this->update_inventory_status($this->journal_rows[$i]['sku'], $str_field, $post_qty, $item_cost, $this->journal_rows[$i]['description'], $full_price)) return false;
	}
	$messageStack->debug("\n  end Posting Inventory.");
	return true;
  }

  function unPost_inventory() {
	global $db, $messageStack;
	$messageStack->debug("\n  unPosting Inventory ...");
	// if remaining <> qty then some items have been sold; reduce qty and remaining by original qty (qty will be 0) 
	// and keep record. Quantity may go negative because it was used in a COGS calculation but will be corrected when
	// new inventory has been received and the associated cost applied. If the quantity is changed, the new remaining
	// value will be calculated when the updated purchase/receive is posted.
	switch ($this->journal_id) {  // journals that don't affect inventory, return now
	  case  2:
	  case  3:
	  case  9:
	  case 18:
	  case 20:
		$messageStack->debug(" end unPosting Inventory with no action.");
		return true;
	  case  6:
	  case  7:
	  case 12:
	  case 13:
	  case 14:
	  case 16:
	  case 19:
	  case 21:
		// Delete all owed cogs entries (will be re-added during post)
		$db->Execute("delete from " . TABLE_INVENTORY_COGS_OWED . " where journal_main_id = " . $this->id);
		if (!$this->rollback_COGS()) return false;
		break;
	  default:  // continue to unPost inventory
	}
	// prepare some variables
	switch ($this->journal_id) {
	  case  4:
	  case  6:
	  case 21:
	  case  7:
		$db_field = 'quantity_on_order';
		break;
	  default:
		$db_field = 'quantity_on_sales_order';
	}
	for ($i = 0; $i < count($this->journal_rows); $i++) if ($this->journal_rows[$i]['sku']) {
	  switch ($this->journal_id) {
		case  4:
		case 10:
		  $item_array = $this->load_so_po_balance($this->id, '', false);
		  $bal_before_post = $item_array[$this->journal_rows[$i]['id']]['ordered'] - $item_array[$this->journal_rows[$i]['id']]['processed'];
		  if (!$this->closed && $bal_before_post > 0) {
			if (!$this->update_inventory_status($this->journal_rows[$i]['sku'], $db_field, -$bal_before_post)) return false;
		  }
		  break;
		case  6:
		case  7:
		case 12:
		case 13:
		case 14:
		case 16:
		case 19:
		case 21:
		  switch ($this->journal_id) {
			case  7: // vendor credit memo - negate qty
			case 12: // customer sales - negate quantity
			case 19: // customer POS - negate quantity
			  $qty = -$this->journal_rows[$i]['qty'];
			  break;
			default:
			  $qty = $this->journal_rows[$i]['qty'];
		  }
		  if (!$this->update_inventory_status($this->journal_rows[$i]['sku'], 'quantity_on_hand', -$qty)) return false;
		  // adjust po/so inventory, if necessary, based on min of qty on ordered and qty shipped/received
		  if ($this->journal_rows[$i]['so_po_item_ref_id']) {
			$item_array = $this->load_so_po_balance($this->so_po_ref_id, $this->id, false);
			$bal_before_post = $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['ordered'] - $item_array[$this->journal_rows[$i]['so_po_item_ref_id']]['processed'];
			// do not allow qty on order to go below zero.
			$adjustment = min($this->journal_rows[$i]['qty'], $bal_before_post);
			if (!$this->update_inventory_status($this->journal_rows[$i]['sku'], $db_field, $adjustment)) return false;
		  }
		  break;
	    default:
	  }
	}
	// remove the inventory history records 
	$db->Execute("delete from " . TABLE_INVENTORY_HISTORY . " where ref_id = " . $this->id);
	$db->Execute("delete from " . TABLE_INVENTORY_COGS_USAGE . " where journal_main_id = " . $this->id);
	// remove cost of goods sold records (will be re-calculated if re-posting)
	$this->remove_journal_COGS_entries();
	$messageStack->debug("\n  end unPosting Inventory.");
	return true;
  }


// *********  inventory support functions  **********
  function update_inventory_status($sku, $field, $adjustment, $item_cost=0, $desc='', $full_price=0) {
	global $db, $messageStack;
	if (!$sku || $adjustment == 0) return true;
	$messageStack->debug("\n    update_inventory_status, SKU = $sku, field = $field, adjustment = $adjustment, and item_cost = $item_cost");
	// catch sku's that are not in the inventory database but have been requested to post
	$result = $db->Execute("SELECT id, inventory_type FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
	if ($result->RecordCount() == 0) {
	  if (!INVENTORY_AUTO_ADD) {
		return $this->fail_message(GL_ERROR_UPDATING_INVENTORY_STATUS . $sku);
	  } else {
	    $id = $this->inventory_auto_add($sku, $desc, $item_cost, $full_price);
		$result->fields['inventory_type'] = 'si';
	  }
	}
	$type = $result->fields['inventory_type'];
	// only update items that are to be tracked in inventory (non-stock are tracked for PO/SO only)
	if (strpos(COG_ITEM_TYPES, $type) !== false || ($type == 'ns' && $field <> 'quantity_on_hand')) {
	  $sql = "UPDATE ".TABLE_INVENTORY." SET $field=$field+$adjustment, ";
	  if ($item_cost) $sql .= "item_cost=$item_cost, ";
	  $sql .= "last_journal_date = now() WHERE sku='$sku'";
	  $result = $db->Execute($sql);
	  if ($item_cost) {
	  	$sql = "UPDATE ".TABLE_INVENTORY_PURCHASE." SET item_cost=$item_cost WHERE sku='$sku' AND vendor_id='$this->bill_acct_id'";
	  	$result = $db->Execute($sql);
	  }
	}
	return true;
  }

  function calculate_COGS($item, $return_cogs = false) {
	global $db, $messageStack;
	$messageStack->debug("\n    Calculating COGS, SKU = " . $item['sku'] . ' and QTY = ' . $item['qty']);
	$cogs = 0;
	// fetch the additional inventory item fields we need
	$sql = "select inactive, inventory_type, account_inventory_wage, account_cost_of_sales, item_cost, cost_method, quantity_on_hand, serialize  
	  from " . TABLE_INVENTORY . " where sku = '" . $item['sku'] . "'";
	$result = $db->Execute($sql);
	// catch sku's that are not in the inventory database but have been requested to post, error
	if ($result->RecordCount() == 0) {
	  if (!INVENTORY_AUTO_ADD) return $this->fail_message(sprintf(GL_ERROR_CALCULATING_COGS, $item['sku']));
	  $item_cost  = 0;
	  $full_price = 0;
	  switch ($this->journal_id) {
		case  6:
		case  7:
		  $item_cost  = $item['price']; break;
		case 12:
		case 13:
		  $full_price = $item['price']; break;
		default:
		  return $this->fail_message(sprintf(GL_ERROR_CALCULATING_COGS, $item['sku']));
	  }
	  $id = $this->inventory_auto_add($item['sku'], $item['description'], $item_cost, $full_price);
	  $result = $db->Execute($sql); // re-load now that item was created
	}
	// only calculate cogs for certain inventory_types
	if (strpos(COG_ITEM_TYPES, $result->fields['inventory_type']) === false) {
	  $messageStack->debug(". Exiting COGS, no work to be done with this SKU.");
	  return true;
	}
	$defaults = $result->fields;
	if (ENABLE_MULTI_BRANCH) $defaults['quantity_on_hand'] = $this->branch_qty_on_hand($item['sku'], $defaults['quantity_on_hand']);
	// catch sku's that are serialized and the quantity is not one, error
	if ($defaults['serialize'] && abs($item['qty']) <> 1) return $this->fail_message(GL_ERROR_SERIALIZE_QUANTITY);
	if ($defaults['serialize'] && !$item['serialize_number']) return $this->fail_message(GL_ERROR_SERIALIZE_EMPTY);

	if ($item['qty'] > 0) { // for positive quantities, inventory received, customer credit memos, unbuild assembly
	  // if insert, enter SYSTEM ENTRY COGS cost only if inv on hand is negative
	  // update will never happen because the entries are removed during the unpost operation.
	  switch ($this->journal_id) {
	  	case  6: 
		  if ($defaults['cost_method'] == 'a') $item['avg_cost'] = $this->calculate_avg_cost($item['sku'], $item['price'], $item['qty']);
	  	  break;
		case 12: // for negative sales/invoices and customer credit memos the price needs to be the last unit_cost, 
		case 13: // not the invoice price (customers price)
		  $item['price'] = $this->calculateCost($item['sku'], 1, $item['serialize_number']);
		  $cogs = -($item['qty'] * $item['price']);
		  break;
		case 14: // for un-build assemblies cogs will not be zero
		  $cogs = -($item['qty'] * $this->calculateCost($item['sku'], 1, $item['serialize_number'])); // use negative last cost (unbuild assy)
		  break;
		default: // for all other journals, use the cost as entered to calculate added inventory
	  }
	  // adjust remaining quantities for inventory history since stock was negative
	  $history_array = array(
		'ref_id'     => $this->id,
		'store_id'   => $this->store_id,
		'journal_id' => $this->journal_id,
		'sku'        => $item['sku'],
		'qty'        => $item['qty'],
		'remaining'  => $item['qty'],
		'unit_cost'  => $item['price'],
		'avg_cost'   => $item['avg_cost'],
	  	'post_date'  => $this->post_date,
	  );
	  if ($defaults['serialize']) { // check for duplicate serial number
	    $sql = "select id, remaining, unit_cost from " . TABLE_INVENTORY_HISTORY . " 
		  where sku = '" . $item['sku'] . "' and remaining > 0 and serialize_number = '" . $item['serialize_number'] . "'";
		$result = $db->Execute($sql);
		if ($result->RecordCount() <> 0) return $this->fail_message(GL_ERROR_SERIALIZE_COGS); 
	  	$history_array['serialize_number'] = $item['serialize_number'];
	  }
	  $messageStack->debug("\n      Inserting into inventory history = " . arr2string($history_array));
	  $result = db_perform(TABLE_INVENTORY_HISTORY, $history_array, 'insert');
	  if ($result->AffectedRows() <> 1) return $this->fail_message(GL_ERROR_POSTING_INV_HISTORY);
	} else { // for negative quantities, i.e. sales, negative inv adjustments, assemblies, vendor credit memos
	  // if insert, calculate COGS pulling from one or more history records (inv may go negative)
	  // update should never happen because COGS is backed out during the unPost inventory function
	  $working_qty = -$item['qty']; // quantity needs to be positive
	  $history_ids = array(); // the id's used to calculated cogs from the inventory history table
	  $queue_sku = false;
	  if ($defaults['cost_method'] == 'a') {
		$sql = "SELECT SUM(remaining) as remaining FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='".$item['sku']."' AND remaining > 0";
		if (ENABLE_MULTI_BRANCH) $sql .= " AND store_id='$this->store_id'";
		$result = $db->Execute($sql);
	    if ($result->fields['remaining'] < $working_qty) $queue_sku = true; // not enough of this SKU so just queue it up until stock arrives
	  	$avg_cost = $this->fetch_avg_cost($item['sku'], $working_qty);
	  }
	  if ($defaults['serialize']) { // there should only be one record with one remaining quantity
	    $sql = "SELECT id, remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY." 
		  WHERE sku='".$item['sku']."' AND remaining > 0 AND serialize_number='".$item['serialize_number']."'";
		$result = $db->Execute($sql);
		if ($result->RecordCount() <> 1) return $this->fail_message(GL_ERROR_SERIALIZE_COGS); 
	  } else {
		$sql = "SELECT id, remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY." 
		  WHERE sku='".$item['sku']."' AND remaining > 0"; // AND post_date <= '$this->post_date 23:59:59'"; // causes re-queue to owed table for negative inventory posts and rcv after sale date
		if (ENABLE_MULTI_BRANCH) $sql .= " AND store_id='$this->store_id'";
		$sql .= " ORDER BY ".($defaults['cost_method']=='l' ? 'post_date DESC, id DESC' : 'post_date, id');
		$result = $db->Execute($sql);
	  }
	  if (!$queue_sku) while (!$result->EOF) { // loops until either qty is zero and/or inventory history is exhausted
		if ($defaults['cost_method'] == 'a') { // Average cost
		  switch ($this->journal_id) {
			case  7: // vendor credit memo, just need the difference in return price from average price
			case 14: // assembly, just need the difference in assemble price from piece price
			  $cost = $avg_cost - $item['price'];
			  break;
			default:
			  $cost = $avg_cost;
		  }
		} else {  // FIFO, LIFO
		  switch ($this->journal_id) {
			case  7: // vendor credit memo, just need the difference in return price from purchase price
			case 14: // assembly, just need the difference in assemble price from piece price
			  $cost = $result->fields['unit_cost'] - $item['price'];
			  break;
			default:
			  $cost = $result->fields['unit_cost']; // for the specific history record
		  }
		}
		// 	Calculate COGS and adjust remaining levels based on costing method and history
		// 	  there are two possibilities, inventory is in stock (deduct from inventory history)
		// 	  or inventory is out of stock (balance goes negative, COGS to be calculated later)
		if ($working_qty <= $result->fields['remaining']) { // this history record has enough to fill request
		  $cost_qty = $working_qty;
		  $working_qty = 0;
		  $exit_loop = true;
		} else { // qty will span more than one history record, just calculate for this record
		  $cost_qty = $result->fields['remaining'];
		  $working_qty -= $result->fields['remaining'];
		  $exit_loop = false;
		}
		// save the history record id used along with the quantity for roll-back purposes
		$history_ids[] = array('id' => $result->fields['id'], 'qty' => $cost_qty); // how many from what id
		$cogs += $cost * $cost_qty;
		$sql = "UPDATE ".TABLE_INVENTORY_HISTORY." SET remaining = remaining - $cost_qty WHERE id=".$result->fields['id'];
		$db->Execute($sql);
		if ($exit_loop) break;
		$result->MoveNext();
	  }
	  for ($i = 0; $i < count($history_ids); $i++) {
		$sql_data_array = array(
		  'inventory_history_id' => $history_ids[$i]['id'],
		  'qty'                  => $history_ids[$i]['qty'],
		  'journal_main_id'      => $this->id,
		);
		db_perform(TABLE_INVENTORY_COGS_USAGE, $sql_data_array, 'insert');
	  }
	  // see if there is quantity left to account for but nothing left in inventory (less than zero inv balance)
	  if ($working_qty > 0) {
	    if (!ALLOW_NEGATIVE_INVENTORY) return $this->fail_message(GL_ERROR_POSTING_NEGATIVE_INV);
		// for now, estimate the cost based on the unit_price of the item, will be re-posted (corrected) when product arrives
		switch ($this->journal_id) {
		  case  7: // vendor credit memo, just need the difference in return price from purchase price
		  case 14: // assembly, just need the difference in assemble price from piece price
			$cost = $defaults['cost_method']=='a' ? ($avg_cost - $item['price']) : ($defaults['item_cost'] - $item['price']);
			break;
		  default:
			$cost = $defaults['cost_method']=='a' ? $avg_cost : $defaults['item_cost']; // for the specific history record
		}
		$cogs += $cost * $working_qty;
		// queue the journal_main_id to be re-posted later after inventory is received
		$sql_data_array = array(
		  'journal_main_id' => $this->id,
		  'sku'             => $item['sku'],
		  'qty'             => $working_qty,
		  'post_date'       => $this->post_date,
		  'store_id'        => $this->store_id,
		);
		$messageStack->debug("\n    Adding inventory_cogs_owed, SKU = " . $item['sku'] . ", qty = " . $working_qty);
		db_perform(TABLE_INVENTORY_COGS_OWED, $sql_data_array, 'insert');
	  }
	}

	$this->sku_cogs = $cogs;
	if ($return_cogs) return $cogs; // just calculate cogs and adjust inv history
	$messageStack->debug("\n    Adding COGS to array (if not zero), sku = ".$item['sku']." with calculated value = $cogs");
	if ($cogs) {
	  // credit inventory cost of inventory
	  $cogs_acct = $defaults['account_inventory_wage'];
	  if ($cogs >= 0 ) {
		$this->cogs_entry[$cogs_acct]['credit'] += $cogs;
	  } else {
		$this->cogs_entry[$cogs_acct]['debit']  += -$cogs;
	  }
	  // debit cogs account for income statement
	  $cogs_acct = $this->override_cogs_acct ? $this->override_cogs_acct : $defaults['account_cost_of_sales'];
	  if ($cogs >= 0 ) {
		$this->cogs_entry[$cogs_acct]['debit']  += $cogs;
	  } else {
		$this->cogs_entry[$cogs_acct]['credit'] += -$cogs;
	  }
	}
	$messageStack->debug(" ... Finished calculating COGS.");
	return true;
  }

  function calculateCost($sku, $qty=1, $serial_num='') {
  	global $db, $messageStack;
  	$messageStack->debug("\n    Calculating SKU cost, SKU = $sku and QTY = $qty");
  	$cogs = 0;
  	$defaults = $db->Execute("SELECT inventory_type, item_cost, cost_method, serialize FROM ".TABLE_INVENTORY." WHERE sku='$sku'");
	if ($defaults->RecordCount() == 0) return $cogs; // not in inventory, return no cost
	if (strpos(COG_ITEM_TYPES, $defaults->fields['inventory_type']) === false) return $cogs; // this type not tracked in cog, return no cost
	if ($defaults->fields['cost_method'] == 'a') return $qty * $this->fetch_avg_cost($sku, $qty);
	if ($defaults->fields['serialize']) { // there should only be one record
		$result = $db->Execute("SELECT unit_cost FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND serialize_number='$serial_num'");
		return $result->fields['unit_cost'];
	}
	$sql = "SELECT remaining, unit_cost FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND remaining>0";
	if (ENABLE_MULTI_BRANCH) $sql .= " AND store_id='$this->store_id'";
	$sql .= " ORDER BY id" . ($defaults->fields['cost_method'] == 'l' ? ' DESC' : '');
	$result = $db->Execute($sql);
	$working_qty = abs($qty);
	while (!$result->EOF) { // loops until either qty is zero and/or inventory history is exhausted
		if ($working_qty <= $result->fields['remaining']) { // this history record has enough to fill request
			$cogs += $result->fields['unit_cost'] * $working_qty;
			$working_qty = 0;
			break; // exit loop
		}
		$cogs += $result->fields['unit_cost'] * $result->fields['remaining'];
		$working_qty -= $result->fields['remaining'];
		$result->MoveNext();
	}
	if ($working_qty > 0) $cogs += $defaults->fields['item_cost'] * $working_qty; // leftovers, use default cost
	$messageStack->debug(" ... Finished calculating cost: $cogs");
	return $cogs;
  }

  function calculate_avg_cost($sku = '', $price = 0, $qty = 1) {
  	global $db, $messageStack;
	$sql = "SELECT id, avg_cost, remaining FROM ".TABLE_INVENTORY_HISTORY." 
		WHERE ref_id<>$this->id AND sku='$sku' AND remaining>0 AND post_date<='$this->post_date'";
  	if ($this->store_id > 0) $sql .= " AND store_id='$this->store_id'";
	$sql .= " ORDER BY post_date, id";
  	$result = $db->Execute($sql);
  	$total_stock = 0;
  	$last_cost   = 0;
  	while (!$result->EOF) {
  		$total_stock += $result->fields['remaining'];
  		$last_cost    = $result->fields['avg_cost']; // just keep the cost from the last record as this keeps the avg value of the post date
  		$result->MoveNext();
  	}
  	if ($total_stock == 0 && $qty == 0) return 0;
  	$avg_cost = (($last_cost * $total_stock) + ($price * $qty)) / ($total_stock + $qty);
  	return $avg_cost;
  }

  function fetch_avg_cost($sku, $qty=1) {
	global $db, $messageStack;
	$messageStack->debug("\n      Entering fetch_avg_cost for sku: $sku and qty: $qty ... ");
	$sql = "SELECT avg_cost, remaining, post_date FROM ".TABLE_INVENTORY_HISTORY." WHERE sku='$sku' AND remaining>0";
	if (ENABLE_MULTI_BRANCH) $sql .= " AND store_id='$this->store_id'";
	$sql .= " ORDER BY post_date";
	$result = $db->Execute($sql);
	$last_cost = isset($result->fields['avg_cost']) ? $result->fields['avg_cost'] : 0;
	$last_qty = 0;
	$ready_to_exit = false;
	while (!$result->EOF) {
		$qty -= $result->fields['remaining'];
		$post_date = substr($result->fields['post_date'], 0, 10);
		if ($qty <= 0) $ready_to_exit = true;
		if ($ready_to_exit && $post_date > $this->post_date) { // will get the last purchase cost before the sale post date
			$messageStack->debug("Exiting early with history post_date = $post_date fetch_avg_cost with cost = ".($last_qty > 0 ? $result->fields['avg_cost'] : $last_cost));
			return $last_qty > 0 ? $result->fields['avg_cost'] : $last_cost;
		}
		$last_cost = $result->fields['avg_cost'];
		$last_qty = $qty;
		$result->MoveNext(); // not finished yet, get next average cost
	}
	$messageStack->debug("Exiting fetch_avg_cost with cost = $last_cost");
	return $last_cost;
  }

	// Rolling back cost of goods sold required to unpost an entry involves only re-setting the inventory history.
	// The cogs records and costing is reversed in the unPost_chart_balances function.
  function rollback_COGS() {
	global $db, $messageStack;
	$messageStack->debug("\n    Rolling back COGS ... ");
	// only calculate cogs for certain inventory_types
	$sql = "select id, qty, inventory_history_id from " . TABLE_INVENTORY_COGS_USAGE . " where journal_main_id = " . $this->id;
	$result = $db->Execute($sql);
	if ($result->EOF) {
	  $messageStack->debug(" ...Exiting COGS, no work to be done.");
	  return true;
	}
	while(!$result->EOF) {
	  $sql = "update " . TABLE_INVENTORY_HISTORY . " 
		set remaining = remaining + " . $result->fields['qty'] . " 
		where id = " . $result->fields['inventory_history_id'];
	  $db->Execute($sql);
	  $result->MoveNext();
	}
	$messageStack->debug(" ... Finished rolling back COGS");
	return true;
  }

  function load_so_po_balance($ref_id, $id = '', $post = true) {
	global $db, $messageStack;
	$messageStack->debug("\n    Starting to load SO/PO balances ...");
	$item_array = array();
	if ($ref_id) {
	  switch ($this->journal_id) {
		case  4:
		case  6:
		case  7:
		case 21: $gl_type = 'poo'; $proc_type = 'por'; break;
		case 10:
		case 12:
		case 13:
		case 19: $gl_type = 'soo'; $proc_type = 'sos'; break;
		default: return $this->fail_message('Error in classes/gen_ledger, function load_so_po_balance. Bad $journal_id for this function.');
	  }
	  // start by retrieving the po/so item list
	  $sql = "select id, sku, qty from " . TABLE_JOURNAL_ITEM . " 
		where ref_id = " . $ref_id . " and gl_type = '" . $gl_type . "'"; 
	  $result = $db->Execute($sql);
	  while(!$result->EOF) {
		if ($result->fields['sku']) $item_array[$result->fields['id']]['ordered'] = $result->fields['qty'];
		$result->MoveNext();
	  }
	  // retrieve the total number of units processed (received/shipped) less this order (may be multiple sales/purchases)
	  $sql = "select i.so_po_item_ref_id as id, i.sku, i.qty 
		from " . TABLE_JOURNAL_MAIN . " m left join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id
		where m.so_po_ref_id = " . $ref_id . " and i.gl_type = '" . $proc_type . "'";
	  if (!$post && $id) $sql .= " and m.id <> " . $id; // unposting so don't include current id (journal_id = 6 or 12)
	  $result = $db->Execute($sql);
	  while(!$result->EOF) {
		if ($result->fields['sku']) $item_array[$result->fields['id']]['processed'] += $result->fields['qty'];
		$result->MoveNext();
	  }
	}
	$this->so_po_balance_array = $item_array;
	$messageStack->debug(" Finished loading SO/PO balances = " . arr2string($item_array));
	return $item_array;
  }

  function remove_journal_COGS_entries() {
	$temp_array = $this->journal_rows;
	$this->journal_rows = array();
	for ($i=0; $i<count($temp_array); $i++) {
	  if ($temp_array[$i]['gl_type'] == 'cog') continue; // skip row - they are re-calculated later
	  if ($temp_array[$i]['gl_type'] == 'asi') continue; // skip row - they are re-calculated later
	  $this->journal_rows[] = $temp_array[$i];
	}
  }

  function calculate_assembly_list($inv_list) {
	global $db, $messageStack;
	$messageStack->debug("\n    Calculating Assembly item list, SKU = " . $inv_list['sku']);
	$sku = $inv_list['sku'];
	$qty = $inv_list['qty'];
	$result = $db->Execute("select id from " . TABLE_INVENTORY . " where sku = '" . $sku . "'");
	if ($result->RecordCount() == 0) return $this->fail_message(GL_ERROR_BAD_SKU_ENTERED);

	$sku_id = $result->fields['id'];
	$sql = "select a.sku, a.description, a.qty, i.inventory_type, i.quantity_on_hand, i.account_inventory_wage, i.item_cost as price 
	  from " . TABLE_INVENTORY_ASSY_LIST . " a inner join " . TABLE_INVENTORY . " i on a.sku = i.sku
	  where a.ref_id = " . $sku_id;
	$result = $db->Execute($sql);
	if ($result->RecordCount() == 0) return $this->fail_message(GL_ERROR_SKU_NOT_ASSY . $sku);

	$assy_cost = 0;
	while (!$result->EOF) {
	  if ($result->fields['quantity_on_hand'] < ($qty * $result->fields['qty']) && strpos(COG_ITEM_TYPES, $result->fields['inventory_type']) !== false) {
		$messageStack->debug("\n    Not enough of SKU = " . $result->fields['sku'] . " needed " . ($qty * $result->fields['qty']) . " and had " . $result->fields['quantity_on_hand']);
		return $this->fail_message(GL_ERROR_NOT_ENOUGH_PARTS . $result->fields['sku']);
	  }
	  $result->fields['qty'] = -($qty * $result->fields['qty']);
	  $result->fields['id']  = $this->journal_rows[0]['id'];  // placeholder ref_id
	  if (strpos(COG_ITEM_TYPES, $result->fields['inventory_type']) === false) {
	    $item_cost = -$result->fields['qty'] * $result->fields['price'];
	  } else {
	    if ($qty > 0) $result->fields['price'] = 0; // remove unit_price for builds, leave for unbuilds (to calc delta COGS)
	    $item_cost = $this->calculate_COGS($result->fields, true);
	  }
	  if ($item_cost === false) return false; // error in cogs calculation
	  $assy_cost += $item_cost;
	  // generate inventory assembly part record and insert into db
	  $temp_array = array(
		'ref_id'      => $this->id,
		'gl_type'     => 'asi',	// assembly item code
		'sku'         => $result->fields['sku'],
		'qty'         => $result->fields['qty'],
		'description' => $result->fields['description'],
		'gl_account'  => $result->fields['account_inventory_wage'],
		'post_date'   => $this->post_date);
	  if ($qty < 0) {
		$temp_array['debit_amount'] = -$item_cost;
	  } else {
		$temp_array['credit_amount'] = $item_cost;
	  }
	  db_perform(TABLE_JOURNAL_ITEM, $temp_array, 'insert');
	  $temp_array['id'] = db_insert_id();
	  $this->journal_rows[] = $temp_array;
	  if ($qty < 0) { // unbuild assy, update ref_id pointer in inventory history record of newly added item (just like a receive)
		$db->Execute("update " . TABLE_INVENTORY_HISTORY . " set ref_id = " . $temp_array['id'] . " 
			where sku = '" . $temp_array['sku'] . "' and ref_id = " . $result->fields['id']);
	  }
	  $result->MoveNext();
	}

	// update assembled item with total cost
	$id = $this->journal_rows[0]['id'];
	if ($qty < 0) { // the item to assemble should be the first item record
	  $this->journal_rows[0]['credit_amount'] = -$assy_cost;
	  $fields = array('credit_amount' => -$assy_cost);
	} else {
	  $this->journal_rows[0]['debit_amount'] = $assy_cost;
	  $fields = array('debit_amount' => $assy_cost);
	}
	$result = db_perform(TABLE_JOURNAL_ITEM, $fields, 'update', "id = " . (int)$id);
	$inv_list['price'] = $assy_cost / $qty; // insert the assembly cost of materials - unit price
	// Adjust inventory levels for assembly, if unbuild, also calcuate COGS differences
	if ($this->calculate_COGS($inv_list, $return_cogs = ($qty < 0) ? false : true) === false) return false;
	return true;
  }

  function branch_qty_on_hand($sku, $current_qty_in_stock = 0) {
	global $db;
	$sql = "select sum(remaining) as remaining from " . TABLE_INVENTORY_HISTORY . " 
		where store_id = " . $this->store_id . " and sku = '" . $sku . "'";
	$result = $db->Execute($sql);
	$store_bal = $result->fields['remaining'];
	$sql = "select sum(qty) as qty from " . TABLE_INVENTORY_COGS_OWED . " 
		where store_id = " . $this->store_id . " and sku = '" . $sku . "'";
	$result = $db->Execute($sql);
	$qty_owed = $result->fields['qty'];
	return ($store_bal - $qty_owed);
  }

  function inventory_auto_add($sku, $desc, $item_cost = 0, $full_price = 0) {
	$sql_array = array(
	  'sku'                    => $sku, 
	  'inventory_type'         => 'si',
	  'description_short'      => $desc, 
	  'description_purchase'   => $desc, 
	  'description_sales'      => $desc, 
	  'account_sales_income'   => INV_STOCK_DEFAULT_SALES,
	  'account_inventory_wage' => INV_STOCK_DEFAULT_INVENTORY,
	  'account_cost_of_sales'  => INV_STOCK_DEFAULT_COS,
	  'item_taxable'           => INVENTORY_DEFAULT_TAX,
	  'purch_taxable'          => INVENTORY_DEFAULT_PURCH_TAX,
	  'item_cost'              => $item_cost,
	  'cost_method'            => INV_STOCK_DEFAULT_COSTING,
	  'full_price'             => $full_price,
	  'creation_date'          => date('Y-m-d h:i:s'),
	);
	$result = db_perform(TABLE_INVENTORY, $sql_array, 'insert');
	return db_insert_id();
  }

/*******************************************************************************************************************/
// END Inventory Functions
/*******************************************************************************************************************/
// START General Functions
/*******************************************************************************************************************/
  function build_journal_main_array() { // maps/prepares the fields to the journal_main fields
	$main_record = array();
	if (isset($this->id)) if ($this->id)   $main_record['id']                  = $this->id; // retain id if known for re-post references
	if (isset($this->period))              $main_record['period']              = $this->period;
	if (isset($this->journal_id))          $main_record['journal_id']          = $this->journal_id;
	if (isset($this->post_date))           $main_record['post_date']           = $this->post_date;
	if (isset($this->store_id))            $main_record['store_id']            = $this->store_id;
	$main_record['description'] = (isset($this->description)) ? $this->description : sprintf(TEXT_JID_ENTRY, constant('ORD_TEXT_' . JOURNAL_ID . '_WINDOW_TITLE'));
	if (isset($this->closed))              $main_record['closed']              = $this->closed;
	if (isset($this->closed_date))         $main_record['closed_date']         = $this->closed_date;
	if (isset($this->freight))             $main_record['freight']             = $this->freight;
	if (isset($this->discount))            $main_record['discount']            = $this->discount;
	if (isset($this->shipper_code))        $main_record['shipper_code']        = $this->shipper_code;
	if (isset($this->terms))               $main_record['terms']               = $this->terms;
	if (isset($this->sales_tax))           $main_record['sales_tax']           = $this->sales_tax;
	if (isset($this->total_amount))        $main_record['total_amount']        = $this->total_amount;
	if (isset($this->currencies_code))     $main_record['currencies_code']     = $this->currencies_code;
	if (isset($this->currencies_value))    $main_record['currencies_value']    = $this->currencies_value;
	if (isset($this->so_po_ref_id))        $main_record['so_po_ref_id']        = $this->so_po_ref_id;
	if (isset($this->purchase_invoice_id)) $main_record['purchase_invoice_id'] = $this->purchase_invoice_id;
	if (isset($this->purch_order_id))      $main_record['purch_order_id']      = $this->purch_order_id;
	if (isset($this->admin_id))            $main_record['admin_id']            = $this->admin_id;
	if (isset($this->rep_id))              $main_record['rep_id']              = $this->rep_id;
	if (isset($this->waiting))             $main_record['waiting']             = $this->waiting;
	if (isset($this->gl_acct_id))          $main_record['gl_acct_id']          = $this->gl_acct_id;
	if (isset($this->bill_acct_id))        $main_record['bill_acct_id']        = $this->bill_acct_id;
	if (isset($this->bill_address_id))     $main_record['bill_address_id']     = $this->bill_address_id;
	if (isset($this->bill_primary_name))   $main_record['bill_primary_name']   = $this->bill_primary_name;
	if (isset($this->bill_contact))        $main_record['bill_contact']        = $this->bill_contact;
	if (isset($this->bill_address1))       $main_record['bill_address1']       = $this->bill_address1;
	if (isset($this->bill_address2))       $main_record['bill_address2']       = $this->bill_address2;
	if (isset($this->bill_city_town))      $main_record['bill_city_town']      = $this->bill_city_town;
	if (isset($this->bill_state_province)) $main_record['bill_state_province'] = $this->bill_state_province;
	if (isset($this->bill_postal_code))    $main_record['bill_postal_code']    = $this->bill_postal_code;
	if (isset($this->bill_country_code))   $main_record['bill_country_code']   = $this->bill_country_code;
	if (isset($this->bill_telephone1))     $main_record['bill_telephone1']     = $this->bill_telephone1;
	if (isset($this->bill_email))          $main_record['bill_email']          = $this->bill_email;
	if (isset($this->ship_acct_id))        $main_record['ship_acct_id']        = $this->ship_acct_id;
	if (isset($this->ship_address_id))     $main_record['ship_address_id']     = $this->ship_address_id;
	if (isset($this->ship_primary_name))   $main_record['ship_primary_name']   = $this->ship_primary_name;
	if (isset($this->ship_contact))        $main_record['ship_contact']        = $this->ship_contact;
	if (isset($this->ship_address1))       $main_record['ship_address1']       = $this->ship_address1;
	if (isset($this->ship_address2))       $main_record['ship_address2']       = $this->ship_address2;
	if (isset($this->ship_city_town))      $main_record['ship_city_town']      = $this->ship_city_town;
	if (isset($this->ship_state_province)) $main_record['ship_state_province'] = $this->ship_state_province;
	if (isset($this->ship_postal_code))    $main_record['ship_postal_code']    = $this->ship_postal_code;
	if (isset($this->ship_country_code))   $main_record['ship_country_code']   = $this->ship_country_code;
	if (isset($this->ship_telephone1))     $main_record['ship_telephone1']     = $this->ship_telephone1;
	if (isset($this->ship_email))          $main_record['ship_email']          = $this->ship_email;
	if (isset($this->terminal_date))       $main_record['terminal_date']       = $this->terminal_date;
	if (isset($this->drop_ship))           $main_record['drop_ship']           = $this->drop_ship;
	if (isset($this->recur_id))            $main_record['recur_id']            = $this->recur_id;
	return $main_record;
  }

  function remove_cogs_rows() {
	global $messageStack;
	$messageStack->debug("\n  Removing system generated gl rows. Started with " . count($this->journal_rows) . " rows ");
	// remove these types of rows since they are regenerated as part of the Post
	$removal_gl_types = array('cog', 'asi');
	$temp_rows = array();
	foreach ($this->journal_rows as $key => $value) {
	  if (!in_array($value['gl_type'], $removal_gl_types)) $temp_rows[] = $value;
	}
	$this->journal_rows = $temp_rows;
	$messageStack->debug(" and ended with " . count($this->journal_rows) . " rows.");
  }

  function check_for_closed_po_so($action = 'Post') {
	global $db, $currencies, $messageStack;
	// closed can occur many ways including:
	//   forced closure through so/po form (from so/po journal - adjust qty on so/po)
	//   all quantities are reduced to zero (from so/po journal - should be deleted instead but it's possible)
	//   editing quantities on po/so to match the number received (from po/so journal)
	//   receiving all (or more) po/so items through one or more purchases/sales (from purchase/sales journal)
	$messageStack->debug("\n  Checking for closed entry. action = " . $action);
	switch ($this->journal_id) {
	  case  4: $gl_type = 'poo';
		// continue like sales order
	  case 10: if (!$gl_type) $gl_type = 'soo';
		// determine if shipped/received items are still outstanding
		$ordr_diff = 0;
		if (is_array($this->so_po_balance_array)) {
		  foreach($this->so_po_balance_array as $counts) {
			if ($counts['ordered'] > $counts['processed']) $ordr_diff = 1;
		  }
		}
		// determine if all items quantities have been entered as zero
		$item_rows_all_zero = 1;
		for ($i = 0; $i < count($this->journal_rows); $i++) {
		  if ($this->journal_rows[$i]['qty'] && $this->journal_rows[$i]['gl_type'] == $gl_type) $item_rows_all_zero = 0; // at least one qty is non-zero
		}
		// also close if the 'Close' box was checked
		if (!$ordr_diff || $item_rows_all_zero || $this->closed) $this->close_so_po($this->id, true);
		break;
	  case  6:
	  case 12:
	  case 19:
	  case 21:
		if ($this->so_po_ref_id) {	// make sure there is a reference po/so to check
		  $ordr_diff = false;
		  if (is_array($this->so_po_balance_array)) {
			foreach($this->so_po_balance_array as $key => $counts) {
			  if ($counts['ordered'] > $counts['processed']) $ordr_diff = true;
			}
		  } else {
			$ordr_diff = true; // force open since balance array is empty
		  }
		  if ($ordr_diff) { // open it, there are still items to be processed
			$this->close_so_po($this->so_po_ref_id, false);
		  } else { // close the order
			$this->close_so_po($this->so_po_ref_id, true);
		  }
		}
		// close if the invoice/inv receipt total is zero
		if (round($this->total_amount, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']) == 0) {
		  $this->close_so_po($this->id, true);
		}
		break;
	  case 18: //$gl_type = 'pmt';
		// continue like payment
	  case 20: //if (!$gl_type) $gl_type = 'chk';
		if ($action == 'Post') {
		  $temp = array();
		  for ($i = 0; $i < count($this->journal_rows); $i++) { // fetch the list of paid invoices
			if ($this->journal_rows[$i]['so_po_item_ref_id']) {
			  $temp[$this->journal_rows[$i]['so_po_item_ref_id']] = true;
			}
		  }
		  $invoices = array_keys($temp);
		  for ($i = 0; $i < count($invoices); $i++) {
			$result = $db->Execute("select sum(i.debit_amount) as debits, sum(i.credit_amount) as credits 
			  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
			  where m.id = {$invoices[$i]} and i.gl_type <> 'ttl'");
			$total_billed = $currencies->format($result->fields['credits'] - $result->fields['debits']);
			$result = $db->Execute("select sum(i.debit_amount) as debits, sum(i.credit_amount) as credits 
			  from " . TABLE_JOURNAL_MAIN . " m inner join " . TABLE_JOURNAL_ITEM . " i on m.id = i.ref_id 
			  where i.so_po_item_ref_id = {$invoices[$i]} and i.gl_type in ('pmt', 'chk')");
			$total_paid = $currencies->format($result->fields['credits'] - $result->fields['debits']);
			$messageStack->debug("\n    total_billed = " . $total_billed . ' and total_paid = ' . $total_paid);
			if ($total_billed == $total_paid) {
			  	$this->close_so_po($invoices[$i], true);
			}else{//invoice could be closed but has to be opend again.
				$this->close_so_po($invoices[$i], false);
			}
		  }
		} else { // unpost - re-open the purchase/invoices affected
		  for ($i = 0; $i < count($this->journal_rows); $i++) {
			if ($this->journal_rows[$i]['so_po_item_ref_id']) {
			  $this->close_so_po($this->journal_rows[$i]['so_po_item_ref_id'], false);
			}
		  }
		}
		break;
	  case  2:
	  case  3:
	  case  7:
	  case  9:
	  case 13:
	  case 14:
	  case 16:
	  default:
	}		
	return true;
  }

  function close_so_po($id, $closed) {
    global $db, $messageStack;
	$sql_data_array = array(
	  'closed'      => ($closed) ? '1' : '0',
	  'closed_date' => ($closed) ? $this->post_date : '0000-00-00',
	);
	db_perform(TABLE_JOURNAL_MAIN, $sql_data_array, 'update', 'id = ' . $id);
	$messageStack->debug("\n  Record ID: " . $this->id . " " . (($closed) ? "Closed Record ID: " : "Opened Record ID: ") . $id);
	return;
  }

  function validate_purchase_invoice_id() {
	global $db, $messageStack;
	$messageStack->debug("\n  Start validating purchase_invoice_id ... ");
	if ($this->purchase_invoice_id <> '') {	// entered a so/po/invoice value, check for dups
	  switch ($this->journal_id) { // allow for duplicates in the following journals
		case 18: 
		case 19: // for the deposit part of POS 
		  $messageStack->debug(" specified ID and dups allowed, returning OK.");
		  return true; // allow for duplicate deposit ticket ID's
		default: // continue
	  }
	  $sql = "select purchase_invoice_id from " . TABLE_JOURNAL_MAIN . " 
		where purchase_invoice_id = '{$this->purchase_invoice_id}' and journal_id = '{$this->journal_id}'";
	  if ($this->id) $sql .= " and id <> " . $this->id;
	  if (in_array($this->journal_id, array(6,7))) $sql .= " and bill_acct_id = " . $this->bill_acct_id;
	  $result = $db->Execute($sql);
	  if ($result->RecordCount() > 0) return $this->fail_message(sprintf(GL_ERROR_2, constant('ORD_HEADING_NUMBER_' . $this->journal_id)));
	  $this->journal_main_array['purchase_invoice_id'] = $this->purchase_invoice_id;
	  $messageStack->debug(" specified ID but no dups, returning OK. ");
	} else {	// generate a new order/invoice value
	  switch ($this->journal_id) { // select the field to fetch the next number
		case  3: $str_field = 'next_ap_quote_num'; break;
		case  4: $str_field = 'next_po_num';       break;
		case  6: $str_field = false;               break; // not applicable
		case  7: $str_field = 'next_vcm_num';      break;
		case  9: $str_field = 'next_ar_quote_num'; break;
		case 10: $str_field = 'next_so_num';       break;
		case 12:
		case 19: $str_field = 'next_inv_num';      break;
		case 13: $str_field = 'next_cm_num';       break;
		case 18: $str_field = 'next_deposit_num';  break;
		case 20:
		case 21: $str_field = 'next_check_num';    break;
	  }
	  if ($str_field) {
		$result = $db->Execute("select $str_field from " . TABLE_CURRENT_STATUS . " limit 1");
		if (!$result) return $this->fail_message(sprintf(GL_ERROR_CANNOT_FIND_NEXT_ID, TABLE_CURRENT_STATUS));
		$this->journal_main_array['purchase_invoice_id'] = $result->fields[$str_field];
	  } else {
		$this->journal_main_array['purchase_invoice_id'] = '';
	  }
	  $messageStack->debug(" generated ID, returning ID# " . $this->journal_main_array['purchase_invoice_id']);
	}
	return true;
  }

  function increment_purchase_invoice_id($force = false) {
	global $db;
	if ($this->purchase_invoice_id == '' || $force) { // increment the po/so/invoice number
	  switch ($this->journal_id) { // select the field to increment the number
		case  3: $str_field = 'next_ap_quote_num'; break;
		case  4: $str_field = 'next_po_num';       break;
		case  6: $str_field = false;               break; // not applicable
		case  7: $str_field = 'next_vcm_num';      break;
		case  9: $str_field = 'next_ar_quote_num'; break;
		case 10: $str_field = 'next_so_num';       break;
		case 12:
		case 19: $str_field = 'next_inv_num';      break;
		case 13: $str_field = 'next_cm_num';       break;
		case 18: $str_field = 'next_deposit_num';  break;
		case 20:
		case 21: $str_field = 'next_check_num';    break;
	  }
	  if ($str_field) {
		$next_id = string_increment($this->journal_main_array['purchase_invoice_id']);
		$sql = "update " . TABLE_CURRENT_STATUS . " set " . $str_field . " = '" . $next_id . "'";
		if (!$force) $sql .= " where " . $str_field . " = '" . $this->journal_main_array['purchase_invoice_id'] . "'";
		$result = $db->Execute($sql);
		if ($result->AffectedRows() <> 1) return $this->fail_message(sprintf(GL_ERROR_5, constant('ORD_HEADING_NUMBER_' . $this->journal_id)));
	  }
	}
	$this->purchase_invoice_id = $this->journal_main_array['purchase_invoice_id'];
	return true;
  }

  function add_account($type, $acct_id = 0, $address_id = 0, $allow_overwrite = false) {
	global $db;
	$acct_type = substr($type, 0, 1);
	switch (substr($type, 1, 1)) {
	  case 'b':
	  case 'm': $add_type = 'bill'; break;
	  case 's': $add_type = 'ship'; break;
	  default: return $this->fail_message('Bad account type: ' . $type . ' passed to gen_ledger/classes/gen_ledger.php (add_account)');
	}
	if ($add_type == 'bill' || $this->drop_ship) { // update or insert new account record, else skip to add address
	  $short_name = ($add_type == 'bill') ? $this->short_name : $this->ship_short_name;
	  $auto_type      = false;
	  $auto_field     = '';
	  if (!$short_name && (AUTO_INC_CUST_ID || AUTO_INC_VEND_ID)) {
		switch ($acct_type) {
		  case 'c': // customers
			$auto_type      = AUTO_INC_CUST_ID;
			$auto_field     = 'next_cust_id_num';
			break;
		  case 'v': // vendors
			$auto_type      = AUTO_INC_VEND_ID;
			$auto_field     = 'next_vend_id_num';
			break;
		}
		if ($auto_type) {
			$result = $db->Execute("select " . $auto_field . " from " . TABLE_CURRENT_STATUS);
			$short_name = $result->fields[$auto_field];
		}
	  }
	  if (!$short_name) return $this->fail_message(ACT_ERROR_NO_ACCOUNT_ID);
	  // it id exists, fetch the data, else check for duplicates
	  $sql = "select id, store_id, dept_rep_id from " . TABLE_CONTACTS . " where "; 
	  $sql .= ($acct_id) ? ("id = " . (int)$acct_id) : ("short_name = '" . $short_name . "' and type = '" . $acct_type . "'");
	  $result = $db->Execute($sql);
	  if (!$acct_id && $result->RecordCount() > 0 && !$allow_overwrite) {  // duplicate ID w/o allow_overwrite
		return $this->fail_message(ACT_ERROR_DUPLICATE_ACCOUNT);
	  }
	  $acct_id = $result->fields['id']; // will only change if no id was passed and allow_overwrite is true
	  $sql_data_array = array();
	  $sql_data_array['last_update'] = 'now()';
	  $sql_data_array['store_id']    = isset($this->store_id) ? $this->store_id : $result->fields['store_id'];
	  $sql_data_array['dept_rep_id'] = isset($this->dept_rep_id) ? $this->dept_rep_id : $result->fields['dept_rep_id'];

	  if ($result->RecordCount() == 0) { // new account
		$sql_data_array['type']            = $acct_type;
		$sql_data_array['short_name']      = $short_name;
		$sql_data_array['gl_type_account'] = DEF_INV_GL_ACCT;
		$sql_data_array['first_date']      = 'now()';
		db_perform(TABLE_CONTACTS, $sql_data_array, 'insert');
		$acct_id = db_insert_id();
		$force_mail_address = true;
		if ($auto_type) {
		  $contact_id = $db->Execute("select " . $auto_field . " from " . TABLE_CURRENT_STATUS);
		  $auto_id = $contact_id->fields[$auto_field];
		  if ($auto_id == $short_name) { // increment the ID value
			$next_id = string_increment($auto_id);
			$db->Execute("update " . TABLE_CURRENT_STATUS . " set " . $auto_field . " = '" . $next_id . "'");
		  }
		}
	  } else { // duplicate ID with allow_overwrite
		db_perform(TABLE_CONTACTS, $sql_data_array, 'update', 'id = ' . (int)$acct_id);
		$force_mail_address = false;
	  }
	}

	// address book fields
	$sql_data_array = array();
	if (!$address_id) { // check for the address already there using criteria_fields to match
		$criteria_fields = array('primary_name', 'address1', 'postal_code');

		$sql = "select address_id from " . TABLE_ADDRESS_BOOK . " where ";
		foreach ($criteria_fields as $name) {
			$field_to_test = $add_type . '_' . $name;
			$sql .= $name . " = '" . db_input($this->$field_to_test) . "' and ";
		}
		$sql .= "ref_id = " . $acct_id;
		$result = $db->Execute($sql);
		$address_id = ($result->RecordCount() > 0) ? $result->fields['address_id'] : '';
	}

	$add_fields = array('primary_name', 'contact', 'address1', 'address2', 'city_town', 
		'state_province', 'postal_code', 'country_code', 'telephone1', 'telephone2', 
		'telephone3', 'telephone4', 'email', 'website');
	foreach ($add_fields as $name) {
		$field_to_test = $add_type . '_' . $name;
		if (isset($this->$field_to_test)) $sql_data_array[$name] = $this->$field_to_test;
	}

	$sql_data_array['ref_id'] = $acct_id;
	if (!$address_id) { // create new address
	  $sql_data_array['type'] = ($force_mail_address) ? ($acct_type . 'm') : $type;
	  db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'insert');
	  $address_id = db_insert_id();
	} else { // then update address
	  db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', 'address_id = ' . (int)$address_id);
	}
	// update the journal_main array since we could have new id's
	switch ($add_type) {
	  case 'mail':
	  case 'bill':
		$this->journal_main_array['bill_acct_id']    = $acct_id;
		$this->journal_main_array['bill_address_id'] = $address_id;
		break;
	  case 'ship':
		$this->journal_main_array['ship_acct_id']    = $acct_id;
		$this->journal_main_array['ship_address_id'] = $address_id;
		break;
	  default:
	}
	return $acct_id; // should be either passed id or new id if record was created
  }

  function get_recur_ids($recur_id, $id) {
	global $db;
	// special case when re-posting and the post date is changed, need to fetch original post date
	// from orginal record to include in original transaction
	$result = $db->Execute("select post_date from " . TABLE_JOURNAL_MAIN . " where id = " . $id);
	$post_date = $result->fields['post_date'];
	$output = array();
	$result = $db->Execute("select id, post_date, purchase_invoice_id, terminal_date from " . TABLE_JOURNAL_MAIN . " 
	  where recur_id = " . $recur_id . " and post_date >= '" . $post_date . "' order by post_date");
	while (!$result->EOF) {
	  $output[] = array(
		'id'                  => $result->fields['id'],
		'post_date'           => $result->fields['post_date'],
		'purchase_invoice_id' => $result->fields['purchase_invoice_id'],
		'terminal_date'       => $result->fields['terminal_date'],
	  );
	  $result->MoveNext();
	}
	return $output;
  }

  function fail_message($message) {
	global $db, $messageStack;
	$db->transRollback();
	$messageStack->add($message, 'error');
	return false;
  }

  function session_message($message, $level = 'error') {
	global $messageStack;
	$messageStack->add($message, $level);
  }

} // end class journal
?>