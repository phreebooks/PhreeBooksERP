<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/reportwriter/language/en_us/classes/entry_builder.php
//

// Release 1.8 Sprache ändert
define ('TEXT_SO_POST_DATE', 'Sales Order Post Date');
define ('RW_EB_PAYMENT_DETAIL', 'Die Zahlung Detail');

//
define ('RW_EB_RECORD_ID', 'Record-ID');
define ('RW_EB_JOURNAL_ID', 'Journal-ID');
define ('RW_EB_STORE_ID', 'Store ID');
define ('RW_EB_JOURNAL_DESC', 'Journal Beschreibung');
define ('RW_EB_CLOSED', 'Closed');
define ('RW_EB_FRT_TOTAL', 'Frachtbetrag');
define ('RW_EB_FRT_CARRIER', 'Frachtführer');
define ('RW_EB_FRT_SERVICE', 'Güterverkehr');
define ('RW_EB_TERMS', 'Allgemeine');
define ('RW_EB_INV_DISCOUNT', 'Invoice Discount ');
define ('RW_EB_SALES_TAX', 'Sales Tax');
define ('RW_EB_TAX_AUTH', 'Finanzamt');
define ('RW_EB_TAX_DETAILS', 'MwSt. Details');
define ('RW_EB_INV_SUBTOTAL', 'Invoice Zwischensumme');
define ('RW_EB_INV_TOTAL', 'Rechnungsbetrag');
define ('RW_EB_CUR_CODE', 'Währung Code');
define ('RW_EB_CUR_EXC_RATE', 'Währung Exc bewerten.');
define ('RW_EB_SO_NUM', 'Sales Order Number');
define ('RW_EB_INV_NUM', 'Invoice Number');
define ('RW_EB_PO_NUM', 'Bestellnummer');
define ('RW_EB_SALES_REP', 'Sales Rep');
define ('RW_EB_AR_ACCT', 'A / R-Konto ');
define ('RW_EB_BILL_ACCT_ID', 'Bill Acct ID');
define ('RW_EB_BILL_ADD_ID', 'Bill Adresse ID');
define ('RW_EB_BILL_PRIMARY_NAME', 'Bill Primary Name');
define ('RW_EB_BILL_CONTACT', 'Bill Kontakt');
define ('RW_EB_BILL_ADDRESS1', 'Bill Adresse 1');
define ('RW_EB_BILL_ADDRESS2', 'Bill Adresse 2');
define ('RW_EB_BILL_CITY', 'Bill Stadt');
define ('RW_EB_BILL_STATE', 'Bill Bundesland');
define ('RW_EB_BILL_ZIP', 'Bill PLZ');
define ('RW_EB_BILL_COUNTRY', 'Bill Land');
define ('RW_EB_BILL_TELE1', 'Bill Telefon 1');
define ('RW_EB_BILL_TELE2', 'Bill Telefon 2');
define ('RW_EB_BILL_FAX', 'Bill Fax');
define ('RW_EB_BILL_TELE4', 'Bill Mobile');
define ('RW_EB_BILL_EMAIL', 'Bill E-Mail');
define ('RW_EB_BILL_WEBSITE', 'Bill Webseite');
define ('RW_EB_SHIP_ACCT_ID', 'Ship Acct ID');
define ('RW_EB_SHIP_ADD_ID', 'Ship Adresse ID');
define ('RW_EB_SHIP_PRIMARY_NAME', 'Ship Primary Name');
define ('RW_EB_SHIP_CONTACT', 'Schiff Kontakt');
define ('RW_EB_SHIP_ADDRESS1', 'Ship Adresse 1');
define ('RW_EB_SHIP_ADDRESS2', 'Ship Adresse 2');
define ('RW_EB_SHIP_CITY', 'Ship Stadt');
define ('RW_EB_SHIP_STATE', 'Ship Bundesland');
define ('RW_EB_SHIP_ZIP', 'Ship PLZ');
define ('RW_EB_SHIP_COUNTRY', 'Ship Country');
define ('RW_EB_SHIP_TELE1', 'Ship Telefon 1 ');
define ('RW_EB_SHIP_TELE2', 'Ship Telefon 2');
define ('RW_EB_SHIP_FAX', 'Ship Fax');
define ('RW_EB_SHIP_TELE4', 'Ship Mobile');
define ('RW_EB_SHIP_EMAIL', 'E-Mail Ship ');
define ('RW_EB_SHIP_WEBSITE', 'Ship Webseite');
define ('RW_EB_CUSTOMER_ID', 'Kunden-ID');
define ('RW_EB_ACCOUNT_NUMBER', 'Account Number ');
define ('RW_EB_GOV_ID_NUMBER', 'Gov-ID-Nummer ');
define ('RW_EB_SHIP_DATE', 'Ship Date');
define ('RW_EB_TOTAL_PAID', 'Amount Paid');
define ('RW_EB_PAYMENT_DATE', 'Zahltag');
define ('RW_EB_PAYMENT_DUE_DATE', 'Fälligkeitsdatum');
define ('RW_EB_PAYMENT_METHOD', 'Zahlungsweise');
define ('RW_EB_PAYMENT_REF', 'Die Zahlung Reference');
define ('RW_EB_PAYMENT_DEP_ID', 'Die Zahlung Kaution ID');
define ('RW_EB_BALANCE_DUE', 'Balance Wegen');

// Daten Tabelle definiert
define ('RW_EB_SO_DESC', 'order_description');
define ('RW_EB_SO_QTY', 'order_qty');
define ('RW_EB_SO_TOTAL_PRICE', 'order_price');
define ('RW_EB_SO_UNIT_PRICE', 'order_unit_price');
define ('RW_EB_SO_SKU', 'order_sku');
define ('RW_EB_SO_SERIAL_NUM', 'order_serial_num');
define ('RW_EB_SHIPPED_PRIOR', 'qty_shipped_prior');
define ('RW_EB_BACKORDER_QTY', 'qty_on_backorder');
define ('RW_EB_INV_DESC', 'invoice_description');
define ('RW_EB_INV_QTY', 'invoice_qty');
define ('RW_EB_INV_TOTAL_PRICE', 'invoice_full_price');
define ('RW_EB_INV_UNIT_PRICE', 'invoice_unit_price');
define ('RW_EB_INV_DISCOUNT', 'invoice_discount');
define ('RW_EB_INV_PRICE', 'invoice_price');
define ('RW_EB_INV_SKU', 'invoice_sku');
define ('RW_EB_INV_SERIAL_NUM', 'invoice_serial_num');

?>