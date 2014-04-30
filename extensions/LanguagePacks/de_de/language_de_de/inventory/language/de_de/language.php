<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008, 2009, 2010 PhreeSoft, LLC                   |
// | http://www.PhreeSoft.com                                        |
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
//  Path: /modules/inventory/language/en_us/language.php
//
define ('INV_HEADING_NEW_ITEM', 'Neue Inventory Item');

define ('INV_TYPES_SI', 'Stock Item');
define ('INV_TYPES_SR', 'Serialisierte Artikel');
define ('INV_TYPES_MS', 'Master Stock Item');
define ('INV_TYPES_AS', 'Artikel-Versammlung');
define ('INV_TYPES_SA', 'Serialisierte Versammlung');
define ('INV_TYPES_NS', 'Keine Lagerware');
define ('INV_TYPES_LB', 'Arbeit');
define ('INV_TYPES_SV', 'Service');
define ('INV_TYPES_SF', 'Flat Rate - Service');
define ('INV_TYPES_CI', 'Charge Artikel');
define ('INV_TYPES_AI', 'Activity Artikel');
define ('INV_TYPES_DS', 'Beschreibung');
define ('INV_TYPES_IA', 'Artikel-Versammlung Teil');
define ('INV_TYPES_MI', 'Master Stock Unterpunkt');

define ('TEXT_FIFO', 'FIFO');
define ('TEXT_LIFO', 'LIFO');
define ('TEXT_AVERAGE', 'Durchschnittliche');
define ('TEXT_GREATER_THAN', 'Größer als');
define ('TEXT_DIR_ENTRY', 'Direct-Entry');
define ('TEXT_ITEM_COST', 'Artikel-Kosten ');
define ('TEXT_RETAIL_PRICE', 'Ladenpreis');
define ('TEXT_PRICE_LVL_1', 'Preis der Stufe 1');
define ('TEXT_DEC_AMT', 'Abnahme von Anzahl');
define ('TEXT_DEC_PCNT', 'Abnahme in Prozent');
define ('TEXT_INC_AMT', 'Erhöhung von Anzahl');
define ('TEXT_INC_PCNT', 'Erhöhung in Prozent');
define ('TEXT_NEXT_WHOLE', 'Next-Dollar');
define ('TEXT_NEXT_FRACTION', 'Constant Cents');
define ('TEXT_NEXT_INCREMENT', 'Weiter Inkrement');
define ('INV_XFER_SUCCESS', 'erfolgreich übertragen %s Stücke sku %s');
define ('TEXT_INV_MANAGED', 'Kontrollierte Lieferung');
define ('TEXT_FILTERS', 'Filter');
define ('TEXT_SHOW_INACTIVE', 'Zeige Inaktiv');
define ('TEXT_APPLY', 'Anwenden');
define ('INV_DATE_ACCOUNT_CREATION', 'Datum');
define ('INV_DATE_LAST_UPDATE', 'Letzte Aktualisierung');
define ('INV_DATE_LAST_JOURNAL_DATE', 'Letzter Eintrag Datum');
define ('TEXT_SKU_HISTORY', 'SKU Geschichte');
define ('INV_OPEN_PO', 'offenen Bestellungen');
define ('INV_OPEN_SO', 'Öffnen Kundenauftrag');
define ('INV_PURCH_BY_MONTH', 'Käufe Nach Monat');
define ('TEXT_SALES_BY_MONTH', 'Sales By Month');
define ('INV_NO_RESULTS', 'Keine Ergebnisse gefunden');
define ('INV_PO_NUMBER', 'Bestellnummer');
define ('INV_SO_NUMBER', 'SO Anzahl');
define ('TEXT_PO_DATE', 'Bestelldatum');
define ('TEXT_SO_DATE', 'SO Datum');
define ('INV_PO_RCV_DATE', 'Receive Datum');
define ('INV_SO_SHIP_DATE', 'Ship Date');
define ('INV_PURCH_COST', 'Kaufpreis');
define ('TEXT_SALES_INCOME', 'Umsatz Ergebnis');
define ('TEXT_MONTH', 'This Month');
define ('INV_ENTRY_PURCH_TAX', 'Default Umsatzsteuer-');
define ('TEXT_LAST_MONTH', 'Letzter Monat');
define ('TEXT_3_MONTHS', '3 Monate ');
define ('TEXT_6_MONTHS', '6 Monate ');
define ('TEXT_LAST_12_MONTH',' 12 Monate ');
define ('TEXT_WHERE_USED', 'Wo benutzt');
define ('TEXT_CURRENT_COST', 'Current Cost Versammlung');
define ('JS_INV_TEXT_ASSY_COST', 'Der aktuelle Preis zu montieren diese SKU ist:');
define ('JS_INV_TEXT_USAGE', 'Diese SKU ist in den folgenden Baugruppen verwendet:');
define ('JS_INV_TEXT_USAGE_NONE', 'Diese SKU ist nicht in irgendeiner Baugruppen verwendet.');
define ('TEXT_UPC_CODE', 'UPC Code');
define ('TEXT_SKU_ACTIVITY', 'SKU Activity');
define ('TEXT_SALES_DESCRIPTION', 'Sales Beschreibung');
define ('INV_ASSY_HEADING_TITLE', 'Montage / Demontage Inventory');
define ('TEXT_INVENTORY_REVALUATION', 'Inventory Neubewertung');
define ('INV_POPUP_WINDOW_TITLE', 'Inventory Items');
define ('INV_POPUP_PRICE_MGR_WINDOW_TITLE', 'Inventory Manager Preis');
define ('TEXT_INVENTORY_ADJUSTMENTS', 'Inventory Anpassungen');
define ('TEXT_ADJUSTMENT_ACCOUNT', 'Einstellung Konto');
define ('INV_POPUP_PRICES_WINDOW_TITLE', 'Artikelnummer Preis-Liste');
define ('INV_BULK_SKU_ENTRY_TITLE', 'Bulk SKU Pricing Eintrag');
define ('INV_POPUP_XFER_WINDOW_TITLE', 'Transfer Inventory Zwischen Werkzeug');

define ('TEXT_QUANTITY_ON_HAND_SHORT', 'Anzahl an Hand');
define ('INV_QTY_ON_HAND', 'Menge an Hand');
define ('INV_HEADING_SERIAL_NUMBER', 'Serial Number');
define ('INV_HEADING_QTY_TO_ASSY', 'Anzahl zu montieren');
define ('TEXT_QUANTITY_ON_ORDER_SHORT', 'Anz auf Bestellung');
define ('TEXT_QUANTITY_IN_STOCK_SHORT', 'Menge auf Lager');
define ('TEXT_QTY_THIS_STORE', 'Anzahl dieser Branch');
define ('INV_HEADING_QTY_ON_SO', 'Anzahl an Sales Order');
define ('INV_QTY_ON_SALES_ORDER', 'Menge auf Sales Order');
define ('TEXT_PREFERRED_VENDOR', 'Preferred Vendor ');
define ('TEXT_LEAD_TIME_DAYS', 'Lead Zeit (Tage)');
define ('INV_QTY_ON_ORDER', 'Menge auf Bestellung');
define ('INV_ASSY_PARTS_REQUIRED', 'Komponenten, die für diese Versammlung');
define ('TEXT_QTY_REMAINING', 'Anz Verbleibende');
define ('INV_TEXT_UNIT_COST', 'Unit Cost ');
define ('TEXT_CURRENT_VALUE', 'Current Value');
define ('INV_TEXT_NEW_VALUE', 'New Value');

define ('INV_ADJ_QUANTITY', 'Einstellung Menge');
define ('TEXT_REASON_FOR_ADJUSTMENT', 'Grund für die Einstellung');
define ('INV_ADJ_VALUE', 'Adj-Wert.');
define ('TEXT_ROUNDING', 'Rundung');
define ('INV_RND_VALUE', 'Rnd-Wert.');
define ('TEXT_BILL_OF_MATERIALS', 'Bill of Materials');
define ('INV_ADJ_DELETE_ALERT', 'Sind Sie sicher, dass Sie diese Einstellung Inventory löschen?');
define ('INV_MSG_DELETE_INV_ITEM', 'Sind Sie sicher, dass Sie dieses Verzeichnis löschen?');

define ('INV_XFER_FROM_STORE', 'Transfer vom Store ID');
define ('INV_XFER_TO_STORE', 'Um Store ID');
define ('INV_XFER_QTY', 'Transfer Menge');
define ('INV_XFER_ERROR_NO_COGS_REQD', 'Dieses Inventar Artikel ist nicht auf den Kosten der verkauften Waren daher die Übertragung dieser Punkt zwischen Geschäften nicht erforderlich es eingeschickt werden verfolgt.');
define ('INV_XFER_ERROR_QTY_ZERO', '! Dieser Inventargegenstand Menge kann nicht kleiner als Null sein Wiederholen Sie die Übertragung der anderen Richtung mit einer positiven Menge.');
define ('INV_XFER_ERROR_SAME_STORE_ID', 'Der Quell-und Ziel speichern ID \' s sind die gleichen, die Übertragung nicht durchgeführt wurde! ');
define ('INV_XFER_ERROR_NOT_ENOUGH_SKU', 'Kann nicht übertragen Inventar, nicht genügend in Anspruch zu übertragen!');

define ('INV_ENTER_SKU','Geben Sie die Artikelnummer, Typ und kostengünstige Methode und drücken Sie dann Weiter <br /> Maximale Länge SKU ist ' . MAX_INVENTORY_SKU_LENGTH . ' Zeichen (' . (MAX_INVENTORY_SKU_LENGTH - 5) . ' für Master Stock)');
define ('INV_MS_ATTRIBUTES', 'Master Stock Attribute');
define ('TEXT_ATTRIBUTE_1', 'Attribut 1');
define ('TEXT_ATTRIBUTE_2', 'Attribut 2');
define ('INV_TEXT_ATTRIBUTES', 'Attribute');
define ('INV_MS_CREATED_SKUS', 'Die followng SKUs erstellt werden');

define ('INV_ENTRY_INVENTORY_TYPE', 'Inventory Typ');
define ('INV_ENTRY_INVENTORY_DESC_SHORT', 'Kurzbeschreibung');
define ('INV_ENTRY_INVENTORY_DESC_PURCHASE', 'Bestellen Beschreibung');
define ('INV_ENTRY_IMAGE_PATH', 'Relativ Image Path');
define ('TEXT_SELECT_IMAGE', 'Bild auswählen');
define ('TEXT_SALES_INCOME_ACCOUNT', 'Sales / Income Account ');
define ('INV_ENTRY_ACCT_INV', 'Inventory / Lohn-Konto ');
define ('INV_ENTRY_ACCT_COS', 'Cost of Sales Account');
define ('INV_ENTRY_INV_ITEM_COST', 'Artikel-Kosten ');
define ('TEXT_FULL_PRICE', 'Full Price');
define ('TEXT_ITEM_WEIGHT', 'Artikelgewicht');
define ('TEXT_MINIMUM_STOCK_LEVEL', 'Mindestbestand');
define ('TEXT_REORDER_QUANTITY', 'Reorder Menge');
define ('INV_ENTRY_INVENTORY_COST_METHOD', 'Cost Method');
define ('INV_ENTRY_INVENTORY_SERIALIZE', 'Serialize Artikel');
define ('INV_MASTER_STOCK_ATTRIB_ID', 'ID (Max 2 Chars)');

define ('TEXT_DISPLAY_NUMBER_OF_ITEMS', TEXT_DISPLAY_NUMBER . 'Artikel');
define ('INV_MSG_COPY_INTRO', 'Bitte geben Sie einen neuen SKU-ID, um zu kopieren:');
define ('INV_MSG_RENAME_INTRO', 'Bitte geben Sie einen neuen SKU-ID, um diese SKU umbenennen:');
define ('INV_ERROR_DUPLICATE_SKU', 'Das neue Inventar Element kann nicht erstellt werden, da die sku wird bereits verwendet werden.');
define ('INV_ERROR_CANNOT_DELETE', 'Das Verzeichnis kann nicht gelöscht, weil es Journaleinträge in das System passenden werden die sku');
define ('INV_ERROR_BAD_SKU', 'Es gab einen Fehler mit dem Element Montage Liste, bitte bestätigen sku Werte und Mengen zu überprüfen Andernfalls sku war.');
define ('INV_ERROR_SKU_INVALID', 'SKU ungültig Bitte überprüfen Sie die sku Wert und Bestand machen Fehler..');
define ('INV_ERROR_SKU_BLANK', 'Der SKU Feld war leer Bitte geben Sie einen Wert sku und erneut versuchen..');
define ('INV_ERROR_FIELD_BLANK', 'Der Name des Feldes war leer Bitte geben Sie den Namen eines Feldes, und wiederholen Sie..');
define ('INV_ERROR_FIELD_DUPLICATE', 'Das Feld eingegeben wird, ein Duplikat, ändern den Namen des Feldes und erneut vorlegen.');
define ('INV_ERROR_NEGATIVE_BALANCE', 'Fehler unbuilding Bestandsaufnahme, die nicht genügend Lagerbestand unbuild die angeforderte Menge!');
define ('INV_DESCRIPTION', 'Beschreibung:');
define ('TEXT_USE_DEFAULT_PRICE_SHEET', 'Use Default Preisblatt Einstellungen');
define ('INV_POST_SUCCESS', 'erfolgreich gebucht Inventory Anpassung Ref #');
define ('INV_POST_ASSEMBLY_SUCCESS', 'Erfolgreich montiert SKU:');
define ('INV_NO_PRICE_SHEETS', 'Keine Preislisten definiert worden!');
define ('INV_DEFINED_PRICES', 'definierten Preisen für Artikel-Nr:');
define ('ORD_INV_STOCK_LOW', 'Nicht genügend Lagerbestand von diesem Artikel.');
define ('ORD_INV_STOCK_BAL', 'Die Anzahl der Einheiten auf Lager ist:');
define ('ORD_INV_OPEN_POS', 'Die folgenden offenen Bestellungen in das System:');
define ('ORD_INV_STOCK_STATUS', 'Store: %s PO: %s Menge: %s Wegen: %s');
define ('ORD_JS_SKU_NOT_UNIQUE', 'Keine eindeutigen Ergebnisse für diese sku gefunden werden konnte Entweder die SKU Suchfeld führte mehrere Übereinstimmungen oder keine Übereinstimmungen gefunden..');
define ('SRVCS_DUPLICATE_SHEET_NAME', 'Das Preisblatt Namen existiert bereits Bitte geben Sie ein neues Blatt Namen trägt..');
define ('INV_ERROR_DELETE_HISTORY_EXISTS', 'kann nicht gelöscht werden Inventargegenstand da ein Datensatz in der Tabelle inventory_history.');
define ('INV_ERROR_DELETE_ASSEMBLY_PART', 'kann nicht gelöscht werden Inventar Punkt, da es Teil einer Baugruppe ist.');

// Java Script Fehler und Meldungen
define ('AJAX_INV_NO_INFO', 'Nicht genügend Informationen übergeben wurde, um das Element Details abrufen');
define ('JS_SKU_BLANK', "* Der neue Eintrag muss eine SKU oder UPC Code \n ");
define ('JS_COGS_AUTO_CALC', 'Stückpreis wird durch das System berechnet werden.');
define ('JS_NO_SKU_ENTERED', "Ein SKU Wert ist erforderlich. \n ");
define ('JS_ADJ_VALUE_ZERO', "Ein Nicht-Null-Anpassung Menge erforderlich ist. \n");
define ('JS_ASSY_VALUE_ZERO', "Ein Nicht-Null-Montage Menge erforderlich ist. \n");
define ('JS_NOT_ENOUGH_PARTS', "Nicht genügend Bestand auf die gewünschte Menge zusammenbauen");
define ('JS_MS_INVALID_ENTRY', '. Beide ID und Beschreibung sind Pflichtfelder Bitte geben Sie beide Werte und drücken Sie OK.');
define ('JS_ERROR_NO_SHEET_NAME', 'Der Preis Blattname darf nicht leer sein.');

// Audit-Log-Einträge
define ('INV_LOG_ADJ', 'Inventory Einstellung -');
define ('TEXT_INVENTORY_ASSEMBLY', 'Inventory Versammlung');
define ('INV_LOG_FIELDS', 'Inventory Fields -');
define ('INV_LOG_INVENTORY', 'Inventory Item -');
define ('INV_LOG_PRICE_MGR', 'Inventory Manager Preis -');
define ('INV_LOG_TRANSFER', 'Inv Transfer von %s %s');
define ('PRICE_SHEETS_LOG', 'Preisblatt -');
define ('PRICE_SHEETS_LOG_BULK', 'Bulk Preis Manager -');

// Preis Blätter definiert
define ('PRICE_SHEET_HEADING_TITLE', 'Preisblatt Manager');
define ('PRICE_SHEET_NEW_TITLE', 'Create a New Preisblatt');
define ('PRICE_SHEET_EDIT_TITLE', 'Edit Preisblatt -');
define ('TEXT_PRICE_SHEET_NAME', 'Preis Blattname');
define ('TEXT_USE_AS_DEFAULT', 'Als Standard verwenden');
define ('TEXT_PRICE_SHEETS', 'Preis Sheets');
define ('TEXT_SHEET_NAME', 'Blattname');
define ('TEXT_REVISE', 'Neue Version');
define ('TEXT_REVISION', 'Rev. Level');
define ('TEXT_EFFECTIVE_DATE', 'Effective Date');
define ('TEXT_EXPIRATION_DATE', 'Verfallsdatum');
define ('TEXT_BULK_EDIT', 'Load Pricing Artikel');
define ('TEXT_SPECIAL_PRICING', 'Special Pricing');
define ('PRICE_SHEET_MSG_DELETE', 'Sind Sie sicher, dass Sie diesen Preis Blatt löschen?');
?>