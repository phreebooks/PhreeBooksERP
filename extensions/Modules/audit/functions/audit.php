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
//  Path: /modules/audit/functions/audit.php
//

require_once(DIR_FS_MODULES . 'phreebooks/defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
gen_pull_language('phreebooks','admin');
gen_pull_language('phreebooks');
gen_pull_language('contacts');
function build_audit_xml($date_from, $date_to, $select){
	global $db, $messageStack, $coa_types_list, $currencies;
	$tax_auths      = gen_build_tax_auth_array();
	$dates = gen_get_dates($date_from);
  	$output  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .chr(10);
	$output .= '<auditfile>' .chr(10);
	$output .= '<header>' .chr(10);
		$output .= xmlEntry('auditfileVersion',		'CLAIR2.00.00'						,true);
		$output .= xmlEntry('companyID',			substr(htmlspecialchars(AUDIT_DEBIT_NUMBER),0,20)		,true);
		$output .= xmlEntry('taxRegistrationNr',	substr(htmlspecialchars(TAX_ID),0,15)					,true);
		$output .= xmlEntry('companyName',			substr(htmlspecialchars(COMPANY_NAME),0,50)				,true);
		$output .= xmlEntry('companyAddress',		substr(htmlspecialchars(COMPANY_ADDRESS1),0,50)			,true);
		$output .= xmlEntry('companyCity',			substr(htmlspecialchars(COMPANY_CITY_TOWN),0,50)		,true);
		$output .= xmlEntry('companyPostalCode',	substr(htmlspecialchars(COMPANY_POSTAL_CODE),0,10)		,true);
		$output .= xmlEntry('fiscalYear',			$dates['ThisYear']					,true);
		$output .= xmlEntry('startDate',			$date_from							,true);
		$output .= xmlEntry('endDate',				$date_to							,true);
		$output .= xmlEntry('currencyCode',			DEFAULT_CURRENCY					,true);
		$output .= xmlEntry('dateCreated',			date('Y-m-d' )						,true);
		$output .= xmlEntry('productID',			'Phreebooks'						,true);
		$output .= xmlEntry('productVersion',		'Phreebooks ='.MODULE_PHREEBOOKS_STATUS.' audit='.MODULE_AUDIT_STATUS	,true);
		//$output .= xmlEntry('',);
	$output .= '</header>' .chr(10);
	$output .= '<generalLedger>' .chr(10);//all general ledger account
	    $income_types = array(30,32,34);
		//$output .= xmlEntry('taxonomy','',true); //Zie toelichting *)
		$result = $db->Execute("select * from " . TABLE_CHART_OF_ACCOUNTS. " where heading_only = '0'");
		while (!$result->EOF) {
			$temp = $coa_types_list[$result->fields['account_type']]['text'];
			$output .= "\t" . '<ledgerAccount>'  .chr(10);
			$output .= "\t" . xmlEntry('accountID',			$result->fields['id'],						true); //generalLedger id
			$output .= "\t" . xmlEntry('accountDesc',		substr(htmlspecialchars($result->fields['description']),0,50),true); //generalLedger description
			$output .= "\t" . xmlEntry('accountType',		(in_array($result->fields['account_type'],$income_types))? TEXT_INCOME_STATEMENT : TEXT_BALANCE_SHEET ,true); //generalLedger Type balance or income
			$output .= "\t" . xmlEntry('leadCode',			$result->fields['account_type'],						 			true); //gl account type id *)
			$output .= "\t" . xmlEntry('leadDescription',	constant($coa_types_list[$result->fields['account_type']]['text']),	true); //GL account Type description *)
			$output .= "\t" . '</ledgerAccount>' .chr(10);	
			$result->MoveNext();	
		}
	$output .= '</generalLedger>'.chr(10);
	$output .= '<customersSuppliers>'.chr(10);// all contacts
	$contacts = array();
		$result = $db->Execute("select * from " . TABLE_CONTACTS . " where inactive = '0' and type in('v','c') ");
		while (!$result->EOF) {
			$contacts[$result->fields['id']] = $result->fields['short_name'];
			$output .= "\t" . '<customerSupplier>'  .chr(10);
			$output .= "\t" . xmlEntry('custSupID',				$result->fields['short_name'],			true); // vendor- of customer id
			$output .= "\t" . xmlEntry('type',					($result->fields['type']=='v') ? ACT_V_TYPE_NAME : ACT_C_TYPE_NAME,	true); // type Vendor or customer
			$output .= "\t" . xmlEntry('taxRegistrationNr',		htmlspecialchars($result->fields['gov_id_number']),true); //tax id
			$output .= "\t" . xmlEntry('taxVerificationDate',	$result->fields['gov_id_number_date'],true); //tax verification date (not present in phreedom) maybe in custom fields
			$address = $db->Execute("select * from " . TABLE_ADDRESS_BOOK . " where ref_id = '".$result->fields['id']."'");
			while (!$address->EOF) {
				if(substr($address->fields['type'],1,2) == 'm') {
					$output .= "\t" . xmlEntry('companyName',			htmlspecialchars($address->fields['primary_name']),true); //company name
					$output .= "\t" . xmlEntry('contact',				htmlspecialchars($address->fields['contact']),true); //contact person
					$output .= "\t" . xmlEntry('telephone',				htmlspecialchars($address->fields['telephone1']),true); //company telephone
					$output .= "\t" . xmlEntry('fax',					htmlspecialchars($address->fields['telephone3']),true); //company fax
					$output .= "\t" . xmlEntry('email',					htmlspecialchars($address->fields['email']),true); //company email
					$output .= "\t" . xmlEntry('website',				htmlspecialchars($address->fields['website']),true); //company URL website
					//company billing address
					$output .= "\t\t" . '<postalAddress>' .chr(10);
					$output .= "\t\t" . xmlEntry('address',			substr(htmlspecialchars($address->fields['address1'] . ' ' . $address->fields['address2']),0,50)		,true); 	
					$output .= "\t\t" . xmlEntry('city',			htmlspecialchars($address->fields['city_town'])			,true);
					$output .= "\t\t" . xmlEntry('postalCode',		htmlspecialchars($address->fields['postal_code'])		,true);
					$output .= "\t\t" . xmlEntry('region',			htmlspecialchars($address->fields['state_province'])	,true);
					$output .= "\t\t" . xmlEntry('country',			$address->fields['country_code']						,true);
					$output .= "\t\t" . '</postalAddress>' .chr(10);
				}else if(substr($address->fields['type'],1,2) == 's') {//company shipping address
					$output .= "\t\t" . '<streetAddress>' .chr(10);
					$output .= "\t\t" . xmlEntry('address',			substr(htmlspecialchars($address->fields['address1'] . ' ' . $address->fields['address2']),0,50)		,true); 	
					$output .= "\t\t" . xmlEntry('city',			htmlspecialchars($address->fields['city_town'])			,true);
					$output .= "\t\t" . xmlEntry('postalCode',		htmlspecialchars($address->fields['postal_code'])		,true);
					$output .= "\t\t" . xmlEntry('region',			htmlspecialchars($address->fields['state_province'])	,true);
					$output .= "\t\t" . xmlEntry('country',			$address->fields['country_code']						,true);
					$output .= "\t\t" . '</streetAddress>' .chr(10);  	
				}	
				$address->MoveNext();
			}
			$output .= "\t" . '</customerSupplier>' .chr(10);	
			$result->MoveNext();	
		}
	$output .= '</customersSuppliers>'.chr(10);
	$output .= '<transactions>'.chr(10);// all journal lines.
	    if($select == '1') $where = " and journal_id not in ('3','4','9','10') and waiting = '0' ";
		$totals = $db->Execute("select sum(i.debit_amount) as totalDebit, sum(i.credit_amount) as totalCredit from " . TABLE_JOURNAL_MAIN . " m join " .TABLE_JOURNAL_ITEM . " i on m.id=i.ref_id where m.post_date >= '" . $date_from . "' and m.post_date<='" . $date_to . "'" .$where);
		$result = $db->Execute("select * from " . TABLE_JOURNAL_MAIN . " where post_date >= '" . $date_from . "' and post_date<='" . $date_to . "' " . $where . " order by journal_id ASC");
		$output .= xmlEntry('numberEntries',		$result->RecordCount()			,true);
		$total_credit = 0;
		$total_credit = 0;
		//$output .= xmlEntry('totalDedit',			$totals->fields['totalDebit']	,true);
		//$output .= xmlEntry('totalCredit',			$totals->fields['totalCredit']	,true);
		//if(number_format($totals->fields['totalDebit'],2) <> number_format($totals->fields['totalCredit'],2)) return false;
		$previous_journal_id = '';
		$output .= "\t" . '<journal>' .chr(10);
		while (!$result->EOF) {
			$line_debit  = 0;
			$line_credit = 0;
			if ($previous_journal_id <> $result->fields['journal_id']){
				if($previous_journal_id <> ''){
					$output .= "\t" . '</journal>' .chr(10);
					$output .= "\t" . '<journal>' .chr(10);
				} 
				$output .= "\t" . xmlEntry('journalID',		$result->fields['journal_id']			,true);//the journal id
				$output .= "\t" . xmlEntry('description',	constant('GEN_ADM_TOOLS_J' . str_pad($result->fields['journal_id'], 2, '0', STR_PAD_LEFT))		,true);//the journal description
				$output .= "\t" . xmlEntry('type',			''			,true);//type of journal
			}
			$output .= "\t\t" . '<transaction>' .chr(10);
			$output .= "\t\t" . xmlEntry('transactionID',	$result->fields['id']			,true);
			$output .= "\t\t" . xmlEntry('description',		htmlspecialchars($result->fields['description'])	,true);
			$output .= "\t\t" . xmlEntry('period',			$result->fields['period']		,true);
			$output .= "\t\t" . xmlEntry('transactionDate',	$result->fields['post_date']	,true);
			$output .= "\t\t" . xmlEntry('sourceID',		$result->fields['admin_id']		,true);
			$line = $db->Execute("select id, gl_account, post_date, description, ROUND(debit_amount,".$currencies->currencies[DEFAULT_CURRENCY]['decimal_places'].") as debit_amount, ROUND(credit_amount,".$currencies->currencies[DEFAULT_CURRENCY]['decimal_places'].") as credit_amount, taxable  from " . TABLE_JOURNAL_ITEM . " where ref_id= '" . $result->fields['id']. "'");
			while (!$line->EOF) {
				$output .= "\t\t\t" . '<line>' .chr(10);
				$output .= "\t\t\t" . xmlEntry('recordID',					$line->fields['id']						,true);// Uniek regelnummer
				$output .= "\t\t\t" . xmlEntry('accountID',					$line->fields['gl_account']				,true);// Grootboekrekeningcode (zie hiervoor)
				$output .= "\t\t\t" . xmlEntry('custSupID',					$contacts[$result->fields['bill_acct_id']]		,true);// Debiteuren- of crediteurennummer (zie hiervoor)
				$output .= "\t\t\t" . xmlEntry('documentID',				$result->fields['purchase_invoice_id']	,true);// Boekstuknummer (verwijzing naar brondocument)
				$output .= "\t\t\t" . xmlEntry('effectiveDate',				$line->fields['post_date']				,true);// Mutatiedatum *)
				$output .= "\t\t\t" . xmlEntry('description',				htmlspecialchars($line->fields['description'])			,true);// Omschrijving
				$line_debit  += $line->fields['debit_amount'];
				$output .= "\t\t\t" . xmlEntry('debitAmount',				$line->fields['debit_amount']			,true);// Debetbedrag in lokale valuta (zie hiervoor)
				$line_credit += $line->fields['credit_amount'];
				$output .= "\t\t\t" . xmlEntry('creditAmount',				$line->fields['credit_amount']			,true);// Creditbedrag in lokale valuta (zie hiervoor)
//				$output .= "\t\t\t" . xmlEntry('costDesc',					$line->fields['']						,true);// Kostenplaats
//				$output .= "\t\t\t" . xmlEntry('productDesc',				$line->fields['']						,true);// Kostendrager
				$output .= "\t\t\t" . xmlEntry('projectDesc',				$line->fields['project_id']				,true);// Projectcode (i.p.v. kostensoort)
				//De BTW (vat) wordt als volgt uitgesplitst:
				if($line->fields['taxable'] != '0'){
					$output .= "\t\t\t\t" . '<vat>' .chr(10);
					$output .= "\t\t\t\t" . xmlEntry('vatCode',					$line->fields['taxable']				,true);// BTW-code (leeg betekent geen BTW)
					$output .= "\t\t\t\t" . xmlEntry('vatPercentage',			$tax_auths[$line->fields['taxable']]['tax_rate'] / 100	,true);// BTW-percentage, of in plaats daarvan BTW-bedrag
//					$output .= "\t\t\t\t" . xmlEntry('vatAmount',				$line->fields['']				,true);// BTW-bedrag (bij bijzondere transacties)
					$output .= "\t\t\t\t" . '</vat>' .chr(10);
				}
				//De valuta (currency) wordt vervolgens als volgt weergegeven:
				if($result->fields['currencies_code']<> DEFAULT_CURRENCY){
					$output .= "\t\t\t\t" . '<currency>' .chr(10);
					$output .= "\t\t\t\t" . xmlEntry('currencyCode',			$result->fields['currencies_code']		,true);// Valutacode (leeg betekent lokale valuta)
					$output .= "\t\t\t\t" . xmlEntry('currencyDebitAmount',		$result->fields['currencies_value']		,true);// Debetbedrag in vreemde valuta (i.p.v. koers)
//					$output .= "\t\t\t\t" . xmlEntry('currencyCreditAmount',	$result->fields['currencies_value']		,true);// Creditbedrag in vreemde valuta (i.p.v. koers)
					$output .= "\t\t\t\t" . '</currency>' .chr(10);
				}
				$output .= "\t\t\t" . '</line>' .chr(10);
				$previous_journal_id = $result->fields['journal_id'];
				$line->MoveNext();	
			}
			if((float)(string)$line_debit != (float)(string)$line_credit) {
				if(DEBUG){
					$output .= '<lineError>' .chr(10);
					$output .= xmlEntry('recordID',	  $result->fields['id']	,true);// Uniek regelnummer
					$output .= xmlEntry('lineDebit',  $line_debit	        ,true);
					$output .= xmlEntry('lineCredit', $line_credit	        ,true);
					$output .= '</lineError>' .chr(10);
				}
				$error = $messageStack->add('The journal with id ' . $result->fields['id'] . ' is out of balance total Debit = ' . $line_debit . ' total Credit = ' . $line_credit, 'error');
			}
			$total_debit  += $line_debit;
			$total_credit += $line_credit;
			$output .= "\t\t" . '</transaction>' .chr(10);
			$result->MoveNext();	
		}
		$output .= "\t" . '</journal>' .chr(10);
		if((float)(string)$total_debit != (float)(string)$total_credit){
			$error = $messageStack->add('Totals are out of balance total Debit = ' . $total_debit . ' total Credit = ' . $total_credit, 'error');
		}
		$output .= xmlEntry('totalDedit',			$total_debit	,true);
		$output .= xmlEntry('totalCredit',			$total_credit	,true);
	$output .= '</transactions>'.chr(10);
  	$output .= '</auditfile>'.chr(10);
  	if ($error) return false;
    return $output;
}
