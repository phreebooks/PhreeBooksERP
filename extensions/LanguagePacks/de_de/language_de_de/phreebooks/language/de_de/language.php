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
//  Path: /modules/phreebooks/language/en_us/language.php
//

// Seitentitel
define ('TEXT_GENERAL_JOURNAL_ENTRY', 'Allgemeine Journaleintrag');
define ('GENERAL_JOURNAL_18_C_DESC', 'Customer Receipts Eintrag');
define ('GENERAL_JOURNAL_18_V_DESC', 'Hersteller Receipts Eintrag');
define ('GENERAL_JOURNAL_20_V_DESC', 'Hersteller Zahlung Eintrag');
define ('GENERAL_JOURNAL_20_C_DESC', 'Customer Payment Eintrag');
define ('BNK_DEP_20_V_WINDOW_TITLE', BOX_BANKING_VENDOR_DEPOSITS);
define ('BNK_DEP_18_C_WINDOW_TITLE', BOX_BANKING_CUSTOMER_DEPOSITS);
define ('TEXT_BAR_CODE_ENTRY', 'Bar Code-Eingabe');
define ('TEXT_SEARCH_FOR_JOURNAL_ENTRIES', 'Suche nach Journal Entries');
define ('ORD_RECUR_WINDOW_TITLE', 'Rezidiven Eigenschaften');

// Allgemeiner Text
define ('BNK_CASH_ACCOUNT', 'Cash Konto ');
define ('TEXT_DISCOUNT_ACCOUNT', 'Discount-Konto');
define ('TEXT_AMOUNT_DUE', 'Amount Due');
define ('TEXT_DUE_DATE', 'Due Date');
define ('BNK_INVOICE_NUM', 'Invoice #');
define ('TEXT_CHECK_ALL_CHECKBOXES', 'Alles überprüfen ');
define ('TEXT_DEPOSIT_ID', 'Kaution Ticket-ID');
define ('TEXT_PAYMENT_ID', 'Die Zahlung Ref #');
define ('TEXT_WITHDRAWAL', 'Rücktritt');
define ('TEXT_SAVE_PAYMENT_INFO', 'Save Zahlungsinformationen');
define ('ORD_ADD_UPDATE', 'Hinzufügen / Aktualisieren');
define ('TEXT_AP_ACCOUNT', 'A / P-Konto ');
define ('TEXT_AR_ACCOUNT', 'A / R-Konto ');
define ('ORD_CASH_ACCOUNT', 'Cash Konto ');
define ('ORD_CLOSED', 'Closed');
define ('ORD_COPY_BILL', 'Kopieren -->');
define ('ORD_CUSTOMER_NAME', 'Customer Name');
define ('ORD_DELETE_ALERT', 'Sind Sie sicher, dass Sie diese Bestellung löschen möchten?');
define ('TEXT_DELIVERY_DATES', 'Liefertermine');
define ('TEXT_DISCOUNT_PERCENT', 'Nachlass Prozent');
define ('TEXT_DROP_SHIP', 'Drop Ship');
define ('TEXT_EXPECTED_DELIVERY_DATES', 'Erwartete Liefertermine');
define ('TEXT_FREIGHT', 'Verkehr');
define ('ORD_FREIGHT_ESTIMATE', 'Freight Estimate ');
define ('ORD_FREIGHT_SERVICE', 'Service');
define ('TEXT_INVOICE_TOTAL', 'Rechnungsbetrag');
define ('ORD_MANUAL_ENTRY', 'Manuelle Eingabe');
define ('ORD_NA', 'N / A');
define ('ORD_NEW_DELIVERY_DATES', 'Neue Liefertermine');
define ('ORD_ROW_DELETE_ALERT', 'Sind Sie sicher, dass Sie diese Zeile löschen?');
define ('ORD_SHIP_CARRIER', 'Carrier');
define ('TEXT_SHIP_TO ',' Ship to');
define ('ORD_SHIPPED', 'ausgeliefert');
define ('ORD_SUBTOTAL', 'Zwischensumme');
define ('ORD_TAX_RATE', 'Tax Rate');
define ('TEXT_TAXABLE', 'Steuerpflichtige');
define ('TEXT_WEEKLY', 'Weekly');
define ('TEXT_BIWEEKLY', 'zweiwöchigen');
define ('TEXT_MONTHLY', 'Monthy');
define ('TEXT_QUARTERLY', 'Quarterly');
define ('TEXT_YEARLY', 'Jahresgehalt');
define ('ORD_VENDOR_NAME', 'Vendor Name');
define ('TEXT_VOID_SHIPMENT', 'Sendung stornieren');
define ('TEXT_WAITING_FOR_INVOICE', 'Waiting for Invoice');
define ('TEXT_WAITING', 'Waiting');
define ('ORD_SO_INV_MESSAGE', 'Lassen Sie die Anzahl leer zu lassen, das System ordnen Sie die nächste Nummer in der aktuellen Reihenfolge.');
define ('ORD_CONVERT_TO_PO', 'Convert to PO');
define ('TEXT_CONVERT_TO_SALES_ORDER_SHORT', 'Convert to SO');
define ('TEXT_CONVERT_TO_INVOICE', 'Convert to Invoice ');
define ('ORD_PO_MESSAGE', 'Lassen Sie die Bestellnummer leer zu lassen, das System mit einer Nummer versehen.');
define ('TEXT_CONVERT_TO_PURCHASE_ORDER', 'Convert to Purchase Order');
define ('TEXT_CONVERT_TO_SALES_ORDER', 'Convert to Kundenauftrag');
define ('TEXT_CONVERT_TO_PURCHASE_ORDER_SHORT', 'Auto Generate Bestellung');
define ('TEXT_TRANSACTION_AMOUNT', 'Transaktionsbetrag');
define ('TEXT_REFERENCE_NUMBER', 'Rechnung / Bestellnummer');
define ('TEXT_CUSTOMER_OR_VENDOR_CONTACT', 'Debitor / Kreditor Kontakt');
define ('TEXT_INVENTORY_ITEM', 'Inventory Item');
define ('TEXT_GENERAL_LEDGER_ACCOUNT', 'Hauptbuch Konto');
define ('TEXT_JOURNAL_RECORD_ID', 'Journal Record-ID');
define ('TEXT_NOT_SPECIFIED', 'Nicht angegeben');
define ('TEXT_COST_OF_GOODS_SOLD', 'Cost of Goods Sold');
define ('TEXT_TOTALS', 'Gesamt');
define ('TEXT_ACCOUNT_WILL_BE_INCREASED', 'Konto erhöht werden');
define ('TEXT_ACCOUNT_WILL_BE_DECREASED', 'Konto wird verringert');

// Formular-Nachrichten
define ('GENERAL_JOURNAL_0_DESC', 'Import Inventory Anfang Guthaben Eintrag');
define ('GL_MSG_IMPORT_0_SUCCESS', 'erfolgreich eingeführt Inventory Anfang Salden Die Anzahl der Datensätze importiert wurde.');
define ('GL_MSG_IMPORT_0', 'Importierte Inventory Anfang Balances');
define ('BNK_BAD_CASH_ACCOUNT', 'Sie haben einen Eintrag in ein Cash-Konto, das shouldn \' t da sein. ');
define ('BNK_NO_GL_ENTRIES', 'Keine offenen Posten in diesem FIBU gefunden!');

// Javascript-Nachrichten
define ('ORD_JS_RECUR_NO_INVOICE', 'Für eine wiederkehrende Transaktion muss ein Start Rechnungsnummer eingegeben werden PhreeBooks erhöht sich diese für jede wiederkehrende Eintrag..');
define ('ORD_JS_RECUR_ROLL_REQD', 'Dies ist ein immer wiederkehrendes Eintrag Wollen Sie künftig Einträge sowie (Drücken Sie auf Abbrechen aktualisieren, um nur diesen Eintrag aktualisieren).?');
define ('ORD_JS_RECUR_DEL_ROLL_REQD', 'Dies ist ein immer wiederkehrendes Eintrag Wollen Sie künftig Einträge sowie (Drücken Sie auf Abbrechen löschen, um nur diesen Eintrag zu löschen).?');
define ('ORD_JS_WAITING_FOR_PAYMENT', 'Waiting for Invoice Entweder muss überprüft werden oder eine Rechnungsnummer muss eingegeben werden.');
define ('ORD_JS_SERIAL_NUM_PROMPT', 'Geben Sie die Seriennummer für diese Position HINWEIS: Die quantiy muss 1 für serialisierte Begriffe sein..');
define ('ORD_JS_NO_STOCK_A', 'Achtung Es ist nicht genug von Artikel SKU!');
define ('ORD_JS_NO_STOCK_B', '. vorrätig, die Bestellung füllen  \n Die Zahl der Artikel auf Lager ist:');
define ('ORD_JS_NO_STOCK_C', ' \n   \n Klicken OK, um fortzufahren oder auf Abbrechen, um zum Bestellformular zurückzukehren.');
define ('ORD_JS_INACTIVE_A', 'Achtung SKU:');
define ('ORD_JS_INACTIVE_B', 'ist eine inaktive Element  \n   \n Klicken OK, um fortzufahren oder auf Abbrechen, um zum Bestellformular zurückzukehren.');
define ('ORD_JS_CANNOT_CONVERT_QUOTE', 'Ein un-Umsatz Zitat kann nicht auf Verkäufe umgewandelt werden oder ein Auftrag Verkauf / Rechnung');
define ('ORD_JS_CANNOT_CONVERT_SO', 'Ein un-Umsatz Auftrag nicht zu einer Bestellung umgewandelt werden können');

// Audit-Log-Nachrichten
define ('ORD_DELIVERY_DATES', 'PO / SO Liefertermine -');

// Tooltip Nachrichten
define ('ORD_TT_PURCH_INV_NUM', 'Wenn Sie dieses Feld leer lassen, wird automatisch Phreebooks mit einer Nummer versehen.');

// Zeitschriften
define ('GEN_ADM_TOOLS_J02', 'General Journal (2)');
define ('GEN_ADM_TOOLS_J03', 'Bestellen Zitat Journal (3)');
define ('GEN_ADM_TOOLS_J04', 'Bestellung Journal (4)');
define ('GEN_ADM_TOOLS_J06', 'Käufe Journal (6)');
define ('GEN_ADM_TOOLS_J07', 'Hersteller CM Journal (7)');
define ('GEN_ADM_TOOLS_J08', 'Lohnjournal (8)');
define ('GEN_ADM_TOOLS_J09', 'Sales Zitat Journal (9)');
define ('GEN_ADM_TOOLS_J10', 'Sales Order Nr. (10)');
define ('GEN_ADM_TOOLS_J12', 'Sales / Rechnung Nr. (12)');
define ('GEN_ADM_TOOLS_J13', 'Customer CM Journal (13)');
define ('GEN_ADM_TOOLS_J14', 'Inv Assy Journal (14)');
define ('GEN_ADM_TOOLS_J16', 'Inv Anpassung Journal (16)');
define ('GEN_ADM_TOOLS_J18', 'Kasseneinnahmen Journal (18)');
define ('GEN_ADM_TOOLS_J19', 'POS Journal (19)');
define ('GEN_ADM_TOOLS_J20', 'Cash Dist Journal (20)');
define ('GEN_ADM_TOOLS_J21', 'POP Journal (21)');

// Kontakt Kontostatus
define ('ACT_ERROR_NO_ACCOUNT_ID', 'Beim Hinzufügen einer neuen Debitor / Kreditor, das ID-Feld ist erforderlich, bitte geben Sie eine eindeutige ID.');
define ('TEXT_CUSTOMER_STATUS', 'Kundenstatus');
define ('TEXT_VENDOR_STATUS', 'Vendor Status');
define ('TEXT_ACCOUNT_IN_GOOD_STANDING', 'Benutzerkonto in Good Standing');
define ('TEXT_ACCOUNT_IS_OVER_CREDIT_LIMIT', 'Account is Over Kreditlimit');
define ('TEXT_ACCOUNT_HAS_PAST_DUE_BALANCE', 'Konto hat überfällig Balance');
define ('TEXT_SAVE_OPEN_PREVIOUS', 'Save - Offene Rechnung zurück');
define ('TEXT_SAVE_OPEN_NEXT', 'Save - Open nächsten Rechnung');
define ('ORD_WARN_FORM_MODIFIED', 'Es scheint, um Daten in dieser Form bereits Wollen Sie für einen vorhandenen Kontakt suchen.?');
define ('ORD_ERROR_NOT_CUR_PERIOD', 'Deine Berechtigungen verhindern, dass Sie von der Buchung zu einer anderen Zeit als der aktuellen Periode');
define ('ORD_ERROR_DEL_NOT_CUR_PERIOD', 'Deine Berechtigungen verhindern, dass Sie das Löschen eines Auftrags aus einer anderen Zeit als der aktuellen Periode');
define ('TEXT_DISCOUNT_GL_ACCOUNT', 'Nachlass FIBU-Konto ');
define ('TEXT_FREIGHT_GL_ACCOUNT', 'Freight FIBU-Konto ');
define ('ORD_JS_NO_CID', 'Der Kontakt Information muss in dieser Form geladen werden, bevor die Eigenschaften abgerufen werden kann.');
define ('TEXT_ENTER_THE_QUANTITY_AND_SCAN_THE_ITEM', 'Geben Sie die Menge und scannen Sie das Element.');
define ('TEXT_BAR_CODE', 'Bar-Code');

// Erneut auftreten Transaktionen
define ('ORD_RECUR_INTRO', 'Diese Transaktion kann in der Zukunft, indem Sie die Anzahl der Einträge erstellt werden und die Frequenz, für die sie geschrieben vervielfältigt Der aktuelle Eintrag wird der erste Wiederholung angesehen..');
define ('ORD_RECUR_ENTRIES', 'Geben Sie die Anzahl der Einträge zu erstellen');
define ('ORD_RECUR_FREQUENCY', 'Wie oft Einträge post');
define ('ORD_PAST_LAST_PERIOD', 'Die Transaktion kann nicht gebucht Vergangenheit der letzten Periode in das System wiederkehren!');
define ('ORD_HEADING_NUMBER_3', 'Zitat Anzahl');
define ('ORD_HEADING_NUMBER_4', 'Bestellnummer');
define ('ORD_HEADING_NUMBER_6', 'Invoice #');
define ('ORD_HEADING_NUMBER_7', 'Credit Memo Nummer');
define ('ORD_HEADING_NUMBER_9', 'Zitat Anzahl');
define ('ORD_HEADING_NUMBER_10', 'SO Anzahl');
define ('ORD_HEADING_NUMBER_12', 'Invoice #');
define ('ORD_HEADING_NUMBER_13', 'Credit Memo Nummer');
define ('ORD_HEADING_NUMBER_19', 'Quittung Anzahl');
define ('ORD_HEADING_NUMBER_21', 'Die Zahlung Anzahl');

// Allgemeine
define ('BNK_ERROR_NO_ENCRYPT_KEY', 'Es ist Zahlungsinformationen gespeichert, aber den Schlüssel nicht festgelegt wurde!');
define ('BNK_REPOST_PAYMENT', 'Die Zahlungsmoral wurde neu geschrieben, kann dies die vorherige Zahlung mit dem Prozessor doppelt!');
define ('TEXT_CCVAL_ERROR_INVALID_DATE', 'Das angegebene Verfallsdatum der Kreditkarte ist ungültig Bitte überprüfen Sie das Datum und versuchen Sie es erneut..');
define ('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'Die Kreditkartennummer ist ungültig Bitte überprüfen Sie die Nummer und versuchen Sie es erneut..');
define ('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'Die Kreditkartennummer beginnend mit %s  wurde nicht korrekt eingegeben, oder wir akzeptieren nicht, dass solche Karte Bitte versuchen Sie es erneut oder benutzen Sie eine andere Kreditkarte..');
define ('BNK_BULK_PAY_NOT_POSITIVE', 'Die Zahlung an Lieferanten: %s  wurde übersprungen, da die vollständige Zahlung belief sich auf weniger als oder gleich Null!');
define ('BNK_PAYMENT_NOT_SAVED', 'Save Zahlungsinformationen Box wurde überprüft, aber die ecryption Schlüssel nicht vorhanden ist der Eingang verarbeitet wurde aber die Zahlung Informationen wurde nicht gespeichert.');

// Audit-Log-Nachrichten
define ('BNK_LOG_ACCT_RECON', 'Konto-Versöhnung, Zeitraum:');

// Konto Versöhnung
define ('BANKING_HEADING_RECONCILIATION', 'Konto-Versöhnung');
define ('TEXT_STATEMENT_ENDING_BALANCE', 'Statement Ending Balance');
define ('TEXT_OUTSTANDING_CHECKS', 'Hervorragende Checks');
define ('TEXT_DEPOSITS_IN_TRANSIT', 'Einlagen in Transit');
define ('TEXT_ACCOUNT_BALANCE', 'GL Kontostand');
define ('TEXT_UNRECONCILED_DIFFERENCE', 'Unversöhnte Difference');
define ('BNK_DEPOSIT_CREDIT', 'Ein-/ Credit');
define ('TEXT_CHECK_OR_PAYMENT', 'Überprüfen / Payment ');
define ('TEXT_MULTIPLE_DEPOSITS', 'Kunde Einlagen');
define ('TEXT_VENDOR_PAYMENTS', 'Hersteller Zahlungen');
define ('TEXT_CASH_ACCOUNT', 'Cash Konto ');
define ('TEXT_ERROR_PERIOD_CAN_NOT_BE_ALL', 'Rechnungswesen ausgewählten Zeitraum kann nicht sein \' all \' für Kontenabstimmung Betrieb.');
define ('BNK_RECON_POST_SUCCESS', 'erfolgreich gespeichert Veränderungen.');

// Bank-Konto registrieren
define ('TEXT_CASH_ACCOUNT_REGISTER', 'Cash Account Registrieren');
define ('TEXT_BEGINNING_BALANCE', 'Beginning Balance');
define ('TEXT_ENDING_BALANCE', 'Ending Balance');
define ('TEXT_DEPOSIT ',' Einzahlung ');

// CVV Zeug für Kreditkarten
define ('TEXT_WHAT_IS_CVV', 'Was ist CVV?');
define ('TEXT_CVV_HELP1', 'Visa, Mastercard, Discover 3-stellige Kartenprüfnummer <br /> <br />
                    Für Ihre Sicherheit und die Sicherheit verlangen wir, dass Sie Ihre Karte eingeben \' s Prüfnummer. <br /> <br />
                    Die Prüfnummer ist eine 3-stellige Nummer auf der Rückseite Ihrer Karte aufgedruckt.
                    Es scheint nach und nach rechts neben der Kreditkartennummer angegeben. <br /> '.
                    html_image (DIR_WS_IMAGES . 'cvv2visa.gif '));

define ('TEXT_CVV_HELP2', 'American Express 4-stellige Kartenprüfnummer <br /> <br />
                    Für Ihre Sicherheit und die Sicherheit verlangen wir, dass Sie Ihre Karte eingeben  \' s Prüfnummer. <br /> <br />
                    Die American Express Prüfnummer ist eine 4-stellige Nummer auf der Vorderseite Ihrer Karte aufgedruckt.
                    Es scheint nach und nach rechts neben der Kreditkartennummer angegeben. <br /> '.
                    html_image (DIR_WS_IMAGES . 'cvv2amex.gif '));

// ******************* Gen_ledger Überschriften ******************/
define ('GL_BUDGET_HEADING_TITLE', 'Budget-Manager');
define ('GL_BUDGET_INTRO_TEXT', 'Dieses Tool stellt die Haushaltspläne für Sachkonten <br /> Hinweis: Der Speichern-Symbol nach Dateneingabe, bevor das Konto oder das Geschäftsjahr gedrückt werden muss geändert wird.');
define ('GL_COPY_ACTUAL_CONFIRM', 'Sind Sie sicher, dass Sie das Budget beläuft sich in allen Sachkonten für das ausgewählte Geschäftsjahr ersetzen Werte aus der vorherigen Geschäftsjahres Dieser Vorgang nicht rückgängig gemacht werden kann?');
define ('TEXT_COPY_ACTUALS_FROM_PRIOR_FINANCIAL_YEAR', 'Copy-Werte aus Vor FY');
define ('GL_CLEAR_ACTUAL_CONFIRM', 'Sind Sie sicher, dass Sie das Budget beläuft sich in allen Sachkonten für das ausgewählte Geschäftsjahr klar Dieser Vorgang nicht rückgängig gemacht werden kann?');
define ('TEXT_CLEAR_ALL_BUDGETS_FOR_THIS_FINANCIAL_YEAR', 'Clear All Budgets für dieses Geschäftsjahr');
define ('TEXT_LOAD_ACCT_PRIOR', 'Load-Werte aus Vor FY');
define ('ERROR_NO_GL_ACCT_INFO', 'Es gibt keine Daten für das vorangegangene Geschäftsjahr ausgewählt!');
define ('TEXT_PERIOD_DATES', 'Zeitraum / Termine');
define ('TEXT_PRIOR_FY', 'Vor FY');
define ('TEXT_BUDGET', 'Budget');
define ('TEXT_NEXT_FY', 'Next FY');
define ('TEXT_COPY_PRIOR_BUDGET_TO_CURRENT_BUDGET', 'Kopieren Vor Budget zu Aktuelles Budget');
define ('TEXT_ALLOCATE_TOTAL_THROUGH_FISCAL_YEAR', 'Total Allocate Durch Geschäftsjahr');
define ('TEXT_COPY_NEXT_BUDGET_TO_CURRENT_BUDGET', 'Kopieren nächsten Haushalt zu Aktuelles Budget');
define ('GL_JS_CANNOT_COPY', 'Dieser Datensatz kann nicht, da sie noch nicht gespeichert kopiert werden!');
Define ('GL_JS_COPY_CONFIRM', 'Sie haben sich entschieden, diese Zeitschrift Aktenexemplar Dadurch wird eine Kopie des aktuellen Datensatzes mit dem modifizierten Felder erstellen Hinweis:... Die Kennzeichnung muss anders oder diese Operation fehlschlagen Entsendung Weiter mit OK oder Abbrechen , um zum Formular zurückzukehren. ');

// ************************************************ ************/
// Allgemeine

define ('TEXT_SELECT_FILE' , 'Datei zu importieren : ');

// Audit-Log-Nachrichten
define ('GL_LOG_ADD_JOURNAL', 'Allgemeine Journaleintrag -');
define ('TEXT_GENERAL_JOURNAL_FISCAL_YEAR', 'General Journal Geschäftsjahr');
define ('GL_LOG_PURGE_DB', 'General Journal - Purge Database');

// Spezielle Tasten
define ('TEXT_PURGE_JOURNAL_ENTRIES', 'Purge Journal Entries');
define ('TEXT_CHANGE_CURRENT_ACCOUNTING_PERIOD', 'Change aktuellen Berichtsperiode');
define ('TEXT_GENERATE_NEXT_FISCAL_YEAR', 'Generate nächsten Geschäftsjahr');
define ('TEXT_UPDATE_FISCAL_YEAR_CHANGES', 'Update Geschäftsjahr Änderungen');

// General Ledger Übersetzungen
define ('GL_ERROR_JOURNAL_BAD_ACCT', 'General Ledger Kontonummer kann nicht gefunden werden!');
define ('GL_ERROR_OUT_OF_BALANCE', 'Der Eintrag kann nicht gebucht, weil die Be-und Entlastungen nicht ausgeglichen sein!');
define ('GL_ERROR_BAD_ACCOUNT', 'Eine oder mehrere der GL Kontonummern sind ungültig Bitte korrigieren und erneut einzureichen..');
define ('GL_ERROR_NO_REFERENCE', '. Für wiederkehrende Transaktionen, ein Start-Referenzperiode Zahl eingegeben werden muss PhreeBooks erhöht sich diese für jede wiederkehrende Eintrag.');
define ('GL_ERROR_RECUR_ROLL_REQD', 'Dies ist ein immer wiederkehrendes Eintrag Wollen Sie künftig Einträge sowie (Drücken Sie auf Abbrechen aktualisieren, um nur diesen Eintrag aktualisieren).?');
define ('GL_ERROR_RECUR_DEL_ROLL_REQD', 'Dies ist ein immer wiederkehrendes Eintrag Wollen Sie künftig Einträge sowie (Drücken Sie auf Abbrechen löschen, um nur diesen Eintrag zu löschen).?');
define ('GL_ERROR_NO_ITEMS', 'Es wurden keine Artikel geschrieben, um für ein Element registriert zu sein, die Menge muss nicht leer sein.');
define ('GL_ERROR_NO_POST', 'Es wurden Fehler bei der Verarbeitung, der Rekord wurde nicht gebucht.');
define ('GL_ERROR_NO_DELETE', 'Es wurden Fehler bei der Verarbeitung, der Rekord wurde nicht gelöscht.');
define ('GL_ERROR_CANNOT_FIND_NEXT_ID', 'Konnte nicht lesen der nächsten Bestellung / Rechnung Zahl aus der Tabelle: ' . TABLE_CURRENT_STATUS);
define ('GL_ERROR_CANNOT_DELETE_MAIN', 'Keine Streichung der Zeitschrift wichtigsten Eintrag #');
define ('GL_ERROR_CANNOT_DELETE_ITEM', 'Keine Streichung der Zeitschrift Posten vom Eintrag # %d wurden keine Zeilen gefunden!');
define ('TEXT_CANNOT_DELETE_THIS_ENTRY_BECAUSE_IT_WAS_NEVER_POSTED', 'kann nicht gelöscht werden diesen Eintrag, weil es nie geschrieben wurde.');
define ('GL_DELETE_GL_ROW', 'Sind Sie sicher, dass Sie diese Zeitschrift Zeile löschen?');
define ('GL_DELETE_ALERT', 'Sind Sie sicher, dass Sie diese Zeitschrift Eintrag löschen?');
define ('GL_ERROR_DIED_CREATING_RECORD', 'starben bei dem Versuch, eine Journalbuchung mit id = bauen');
define ('GL_ERROR_POSTING_CHART_BALANCES', 'Fehler Entsendung Diagramm über Kontoguthaben auf Konto-ID:');
define ('GL_ERROR_OUT_OF_BALANCE_A', 'Trial Balance aus dem Gleichgewicht geraten ist Lastschriften.');
define ('GL_ERROR_OUT_OF_BALANCE_B', 'und Kredite:');
define ('GL_ERROR_OUT_OF_BALANCE_C', 'in der Periode');
define ('GL_ERROR_NO_GL_ACCT_NUMBER', 'Keine Kontonummer / gen_ledger.php Funktion versehen:');
define ('GL_ERROR_UPDATING_ACCOUNT_HISTORY', 'Fehler beim Aktualisieren der Debitor / Kreditor-Konto der Geschichte.');
define ('TEXT_ERROR_DELETING_CUSTOMER_OR_VENDOR_ACCOUNT_HISTORY_RECORD', 'Fehler beim Löschen der Debitor / Kreditor-Konto Geschichte aufzeichnen');
define ('GL_ERROR_UPDATING_INVENTORY_STATUS', 'Aktualisiere Inventar Status erfordert die sku in der Datenbank werden die andernfalls SKU war.');
define ('GL_ERROR_CALCULATING_COGS', 'Die Berechnung der Kosten der verkauften Waren erfordert die sku in der Datenbank sein, habe die Operation.');
define ('TEXT_ERROR_POSTING_INVENTORY_HISTORY', 'Fehler Entsendung Inventar Geschichte.');
define ('GL_ERROR_UNPOSTING_COGS', 'Fehler ein Rollback für die Kosten der verkauften Waren-Nr:.');
define ('TEXT_THE_SKU_ENTERED_COULD_NOT_BE_FOUND', 'Die eingegebenen Artikelnummer konnte nicht gefunden werden keine Maßnahmen ergriffen wurden..');
define ('GL_ERROR_SKU_NOT_ASSY', 'Kann nicht montieren ein Gegenstand im Inventar hat, dass keine Komponenten-Nr:.');
define ('GL_ERROR_NOT_ENOUGH_PARTS', 'Nicht genügend Teile, um die angeforderte Anzahl von Baugruppen bauen Artikel-Nr:.');
define ('GL_ERROR_POSTING_NEG_INVENTORY', 'Fehler Entsendung Kosten der gut für einen Anbieter Kredit verkauft wird Inventar gehen negative und Rädchen kann nicht berechnet werden Betroffene SKU ist.');
define ('GL_ERROR_SERIALIZE_QUANTITY', 'Fehler bei der Berechnung Herstellungskosten für eine serialisierte Punkt war die Menge nicht gleich 1 pro Posten abgegeben.');
define ('GL_ERROR_SERIALIZE_EMPTY', 'Fehler bei der Berechnung Herstellungskosten für eine serialisierte Punkt trat die Seriennummer war leer.');
define ('GL_ERROR_SERIALIZE_COGS', '. COGS Fehler entweder nicht gefunden Seriennummer oder mehr als einen Artikel mit passenden Seriennummern gefunden wurden.');
define ('GL_ERROR_NO_RETAINED_EARNINGS_ACCOUNT', 'Zero oder mehr als ein Bilanzgewinn Konten gefunden Es muss eine und nur eine Rechnung in die Gewinnrücklagen PhreeBooks ordnungsgemäß funktionieren werden.');
define ('GL_DISPLAY_NUMBER_OF_ENTRIES', TEXT_DISPLAY_NUMBER . 'GL Einträge');

// Bulk Rechnungen bezahlen
define ('TEXT_CHECK_DATE', 'Überprüfen Datum');
define ('TEXT_FIRST_CHECK_NUMBER', 'First Check-Nummer');
define ('TEXT_TOTAL_OF_ALL_PAYMENTS', 'Total aller Zahlungen');
define ('TEXT_INVOICES_DUE_BY', 'fälligen Rechnungen von');
define ('TEXT_DISCOUNTS_LOST_BY', 'Lost Rabatte von');
define ('BNK_INVOICE_DATE', 'Invoice Datum');
define ('TEXT_VENDOR_NAME', 'Vendor Name');
define ('TEXT_BALANCE_BEFORE_PAYMENTS', 'Balance vor Zahlungen');
define ('TEXT_BALANCE_AFTER_PAYMENTS', 'Bilanz nach Zahlung');

// Journal bestimmte Einträge
define ('GENERAL_JOURNAL_2_DESC', 'Allgemeine Journaleintrag');
define ('GENERAL_JOURNAL_2_ERROR_2', 'GL - Die allgemeine Zeitschrift Referenz eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Referenznummer!');
// Kauf Zitat Spezifische
define ('ORD_TEXT_3_BILL_TO', 'Auftrag,:');
define ('ORD_TEXT_3_REF_NUM', 'Reference #');
define ('ORD_TEXT_3_WINDOW_TITLE', 'Online Anfrage');
define ('ORD_TEXT_3_EXPIRES', 'Verfallsdatum');
define ('ORD_TEXT_3_NUMBER', 'Zitat Anzahl');
define ('ORD_TEXT_3_TEXT_REP', 'Käufer');
define ('ORD_TEXT_3_ITEM_COLUMN_1', 'Menge');
define ('ORD_TEXT_3_ITEM_COLUMN_2', 'rcvd');
define ('ORD_TEXT_3_ERROR_NO_VENDOR', 'Kein Anbieter ausgewählt wurde Entweder wählen Sie einen Lieferanten aus dem Popup-Menü oder geben Sie die Informationen und wählen: ' . ORD_ADD_UPDATE);
define ('ORD_TEXT_3_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Verkäufers zitiert.');
define ('ORD_TEXT_3_CLOSED_TEXT', TEXT_CLOSE);
define ('GENERAL_JOURNAL_3_DESC', 'Bestellen Zitat Eintrag');
define ('GENERAL_JOURNAL_3_ERROR_2', 'PQ - Der Kauf von Ihnen eingegebenen Inhalte, ist eine doppelte, geben Sie bitte eine neue Inhalte Kauf angeben!');
define ('GENERAL_JOURNAL_3_ERROR_5', 'PQ - Fehler Inkrementieren der Kauf Inhalte Zahl!');
define ('GENERAL_JOURNAL_3_LEDGER_DISCOUNT', 'Bestellen Quote Rabatt Menge');
define ('GENERAL_JOURNAL_3_LEDGER_FREIGHT', 'Bestellen Zitat Frachtbetrag');
define ('GENERAL_JOURNAL_3_LEDGER_HEADING', 'Bestellen Zitat Total');
// Bestellung Spezifische
define ('ORD_TEXT_4_BILL_TO', 'Auftrag,:');
define ('ORD_TEXT_4_REF_NUM', 'Reference #');
define ('ORD_TEXT_4_WINDOW_TITLE', 'Bestellung');
define ('ORD_TEXT_4_EXPIRES', 'Verfallsdatum');
define ('ORD_TEXT_4_NUMBER', 'Bestellnummer');
define ('ORD_TEXT_4_TEXT_REP', 'Käufer');
define ('ORD_TEXT_4_ITEM_COLUMN_1', 'Menge');
define ('ORD_TEXT_4_ITEM_COLUMN_2', 'rcvd');
define ('ORD_TEXT_4_ERROR_NO_VENDOR', 'Kein Anbieter ausgewählt wurde Entweder wählen Sie einen Lieferanten aus dem Popup-Menü oder geben Sie die Informationen und wählen: ' . ORD_ADD_UPDATE);
define ('ORD_TEXT_4_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Bestellungen');
define ('ORD_TEXT_4_CLOSED_TEXT', TEXT_CLOSE);
define ('GENERAL_JOURNAL_4_DESC', 'Bestellung Eintrag');
define ('GENERAL_JOURNAL_4_ERROR_2', 'PO - Die Bestellung eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Bestellung angeben!');
define ('GENERAL_JOURNAL_4_ERROR_5', 'PO - Fehler Inkrementieren der Bestellung angeben!');
define ('GENERAL_JOURNAL_4_ERROR_6', 'PO - Eine Bestellung kann nicht gelöscht werden, wenn es Elemente, die empfangen wurden!');
define ('GENERAL_JOURNAL_4_LEDGER_DISCOUNT', 'Bestellung Rabatt Menge');
define ('GENERAL_JOURNAL_4_LEDGER_FREIGHT', 'Bestellung Frachtbetrag');
define ('GENERAL_JOURNAL_4_LEDGER_HEADING', 'Bestellung Total');
define ('GL_MSG_IMPORT_4_SUCCESS', 'erfolgreich eingeführt Bestellungen Die Anzahl der Datensätze importiert wurde.');
define ('GL_MSG_IMPORT_4', 'Importierte Bestellungen');
// Kauf / Empfangen Spezifische
define ('ORD_TEXT_6_BILL_TO', 'Auftrag,:');
define ('ORD_TEXT_6_REF_NUM', 'Reference #');
define ('ORD_TEXT_6_WINDOW_TITLE', 'Kauf / Empfangen Inventory');
define ('ORD_TEXT_6_ERROR_NO_VENDOR', 'Kein Anbieter ausgewählt wurde Entweder wählen Sie einen Lieferanten aus dem Popup-Menü oder geben Sie die Informationen und wählen: ' . ORD_ADD_UPDATE);
define ('ORD_TEXT_6_NUMBER', 'Invoice Number');
define ('ORD_TEXT_6_TEXT_REP', 'Käufer');
define ('ORD_TEXT_6_ITEM_COLUMN_1', 'PO Bal');
define ('ORD_TEXT_6_ITEM_COLUMN_2', 'rcvd');
define ('ORD_TEXT_6_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Element Einnahmen. ');
define ('ORD_TEXT_6_CLOSED_TEXT', 'Rechnung bezahlt');
define ('GENERAL_JOURNAL_6_DESC', 'Kauf / Empfangen Eintrag');
define ('GENERAL_JOURNAL_6_ERROR_2', 'P / R - Die Rechnung eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Rechnung Zahl!');
define ('GENERAL_JOURNAL_6_ERROR_6', 'P / R - Ein Kauf kann nicht gelöscht werden, wenn es eine Gutschrift oder Zahlung angewandt worden sein!');
define ('GENERAL_JOURNAL_6_LEDGER_DISCOUNT', 'Kauf / Empfangen Rabatt Menge');
define ('GENERAL_JOURNAL_6_LEDGER_FREIGHT', 'Kauf / Empfangen Frachtbetrag');
define ('GENERAL_JOURNAL_6_LEDGER_HEADING', 'Kauf / Empfangen Inventory Total');
define ('GL_MSG_IMPORT_6_SUCCESS', 'erfolgreich eingeführt Kreditorenbuchhaltung Einträge Die Anzahl der Datensätze importiert wurde.');
define ('GL_MSG_IMPORT_6', 'Importierte Kreditorenbuchhaltung');
// Hersteller Gutschrift Spezifische
define ('ORD_TEXT_7_BILL_TO', 'Auftrag,:');
define ('ORD_TEXT_7_REF_NUM', 'Reference #');
define ('ORD_TEXT_7_WINDOW_TITLE', 'Vendor Credit Memo');
define ('ORD_TEXT_7_ERROR_NO_VENDOR', 'Kein Anbieter ausgewählt wurde Entweder wählen Sie einen Lieferanten aus dem Popup-Menü oder geben Sie die Informationen und wählen: ' . ORD_ADD_UPDATE);
define ('ORD_TEXT_7_NUMBER', 'Credit Memo Nummer');
define ('ORD_TEXT_7_TEXT_REP', 'Käufer');
define ('ORD_TEXT_7_ITEM_COLUMN_1', 'Received');
define ('ORD_TEXT_7_ITEM_COLUMN_2', 'ergab');
define ('ORD_TEXT_7_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Lieferantenrechnungen. ');
define ('ORD_TEXT_7_CLOSED_TEXT', 'Credit Gewinner');
define ('GENERAL_JOURNAL_7_DESC', 'Hersteller Gutschrift Eintrag');
define ('GENERAL_JOURNAL_7_ERROR_2', 'VCM - Die Gutschrift eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Gutschrift Zahl!');
define ('GENERAL_JOURNAL_7_ERROR_5', 'VCM - Fehler Inkrementieren der Gutschrift Zahl!');
define ('GENERAL_JOURNAL_7_ERROR_6', 'VCM - Eine Gutschrift kann nicht gelöscht werden, wenn es wurde eine Zahlung angewendet werden!');
define ('GENERAL_JOURNAL_7_LEDGER_DISCOUNT', 'Hersteller Gutschrift Rabatt Menge');
define ('GENERAL_JOURNAL_7_LEDGER_FREIGHT', 'Hersteller Gutschrift Frachtbetrag');
define ('GENERAL_JOURNAL_7_LEDGER_HEADING', 'Hersteller Gutschrift Total');
// Customer Specific Zitat
define ('ORD_TEXT_9_BILL_TO', 'Bill');
define ('ORD_TEXT_9_REF_NUM', 'Auftragsnummer');
define ('ORD_TEXT_9_WINDOW_TITLE', 'Customer Zitat');
define ('ORD_TEXT_9_EXPIRES', 'Verfallsdatum');
define ('ORD_TEXT_9_NUMBER', 'Zitat Anzahl');
define ('ORD_TEXT_9_TEXT_REP', 'Sales Rep');
define ('ORD_TEXT_9_ITEM_COLUMN_1', 'Menge');
define ('ORD_TEXT_9_ITEM_COLUMN_2', 'Rechnungsbetrag');
define ('ORD_TEXT_9_ERROR_NO_VENDOR', 'Keine Kunden ausgewählt wurde Entweder wählen Sie einen Kunden aus dem Popup-Menü oder geben Sie die Informationen und wählen: ' . ORD_ADD_UPDATE);
define ('ORD_TEXT_9_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Kundenmeinungen');
define ('ORD_TEXT_9_CLOSED_TEXT', TEXT_CLOSE);
define ('GENERAL_JOURNAL_9_DESC', 'Sales Entry Zitat');
define ('GENERAL_JOURNAL_9_ERROR_2', 'SQ - Der Umsatz eingegebene Nummer ist ein Duplikat zu finden sind, geben Sie bitte eine neue Vertriebs-Inhalte Zahl!');
define ('GENERAL_JOURNAL_9_ERROR_5', 'SQ - Fehler Inkrementieren der Umsatz Inhalte Zahl!');
define ('GENERAL_JOURNAL_9_LEDGER_DISCOUNT', 'Sales Quote Rabatt Menge');
define ('GENERAL_JOURNAL_9_LEDGER_FREIGHT', 'Sales Frachtbetrag Zitat');
define ('GENERAL_JOURNAL_9_LEDGER_HEADING', 'Sales Quote insgesamt');
// Sales Order Spezifische
define ('ORD_TEXT_10_BILL_TO', 'Bill');
define ('ORD_TEXT_10_REF_NUM', 'Auftragsnummer');
define ('ORD_TEXT_10_WINDOW_TITLE', 'Sales Order');
define ('ORD_TEXT_10_EXPIRES', 'Schiff nach Datum');
define ('ORD_TEXT_10_NUMBER', 'SO Anzahl');
define ('ORD_TEXT_10_TEXT_REP', 'Sales Rep');
define ('ORD_TEXT_10_ITEM_COLUMN_1', 'Menge');
define ('ORD_TEXT_10_ITEM_COLUMN_2', 'Rechnungsbetrag');
define ('ORD_TEXT_10_ERROR_NO_VENDOR', 'Keine Kunden ausgewählt wurde Entweder wählen Sie einen Kunden aus dem Popup-Menü oder geben Sie die Informationen und wählen: ' . ORD_ADD_UPDATE);
define ('ORD_TEXT_10_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Kundenaufträge. ');
define ('ORD_TEXT_10_CLOSED_TEXT', TEXT_CLOSE);
define ('GENERAL_JOURNAL_10_DESC', 'Sales Order Entry');
define ('GENERAL_JOURNAL_10_ERROR_2', 'SO - Der Kundenauftrag eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Auftragsnummer');
define ('GENERAL_JOURNAL_10_ERROR_5', 'SO - Failed Inkrementieren der Auftragsnummer');
define ('GENERAL_JOURNAL_10_ERROR_6', 'SO - Ein Kundenauftrag kann nicht gelöscht werden, wenn es Elemente, die versandt worden sind!');
define ('GENERAL_JOURNAL_10_LEDGER_DISCOUNT', 'Sales Order Rabatt Menge');
define ('GENERAL_JOURNAL_10_LEDGER_FREIGHT', 'Sales Order Frachtbetrag');
define ('GENERAL_JOURNAL_10_LEDGER_HEADING', 'Sales Order Total ');
define ('GL_MSG_IMPORT_10_SUCCESS', 'erfolgreich eingeführt Kundenaufträge Die Anzahl der Datensätze importiert wurde.');
define ('GL_MSG_IMPORT_10', 'Importierte Kundenauftrag');
// Vertrieb / Invoice Spezifische
define ('ORD_TEXT_12_BILL_TO', 'Bill');
define ('ORD_TEXT_12_REF_NUM', 'Auftragsnummer');
define ('ORD_TEXT_12_WINDOW_TITLE', 'Sales / Rechnung');
define ('ORD_TEXT_12_EXPIRES', 'Schiff nach Datum');
define ('ORD_TEXT_12_ERROR_NO_VENDOR', 'Keine Kunden ausgewählt wurde!');
define ('ORD_TEXT_12_NUMBER', 'Invoice Number');
define ('ORD_TEXT_12_TEXT_REP', 'Sales Rep');
define ('ORD_TEXT_12_ITEM_COLUMN_1', 'SO Bal');
define ('ORD_TEXT_12_ITEM_COLUMN_2', 'Menge');
define ('ORD_TEXT_12_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Rechnungen');
define ('ORD_TEXT_12_CLOSED_TEXT', 'Paid in Full ');
define ('GENERAL_JOURNAL_12_DESC', 'Sales / Rechnungserfassung');
define ('GENERAL_JOURNAL_12_ERROR_2', 'S / I - Die Rechnung eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Rechnung Zahl!');
define ('GENERAL_JOURNAL_12_ERROR_5', 'S / I - Failed Inkrementieren der Rechnungsnummer!');
define ('GENERAL_JOURNAL_12_ERROR_6', 'S / I - Eine Rechnung kann nicht gelöscht werden, wenn es eine Gutschrift oder Zahlung angewandt worden sein!');
define ('GENERAL_JOURNAL_12_LEDGER_DISCOUNT', 'Sales / Invoice Rabatt Menge');
define ('GENERAL_JOURNAL_12_LEDGER_FREIGHT', 'Sales / Invoice Frachtbetrag');
define ('GENERAL_JOURNAL_12_LEDGER_HEADING', 'Sales / Invoice Total');
define ('GL_MSG_IMPORT_12', 'Importierte Debitoren Eintrag');
define ('GL_MSG_IMPORT_12_SUCCESS', 'erfolgreich eingeführt Debitorenbuchhaltung Die Anzahl der Datensätze importiert wurde.');
define ('GL_MSG_IMPORT_12', 'Importierte Rechnungen');
// Customer Specific Gutschrift
define ('ORD_TEXT_13_BILL_TO', 'Bill');
define ('ORD_TEXT_13_REF_NUM', 'Referenz');
define ('ORD_TEXT_13_WINDOW_TITLE', 'Customer Credit Memo');
define ('ORD_TEXT_13_EXPIRES', 'Schiff nach Datum');
define ('ORD_TEXT_13_ERROR_NO_VENDOR', 'Keine Kunden ausgewählt wurde!');
define ('ORD_TEXT_13_NUMBER', 'Credit Memo Nummer');
define ('ORD_TEXT_13_TEXT_REP', 'Sales Rep');
define ('ORD_TEXT_13_ITEM_COLUMN_1', 'ausgeliefert');
define ('ORD_TEXT_13_ITEM_COLUMN_2', 'ergab');
define ('ORD_TEXT_13_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Rechnungen');
define ('ORD_TEXT_13_CLOSED_TEXT', 'Kredit bezahlt');
define ('GENERAL_JOURNAL_13_DESC', 'Customer Credit Memo Eintrag');
define ('GENERAL_JOURNAL_13_ERROR_2', 'CCM - Die Gutschrift eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Gutschrift Zahl!');
define ('GENERAL_JOURNAL_13_ERROR_5', 'CCM - Fehler Inkrementieren der Gutschrift Zahl!');
define ('GENERAL_JOURNAL_13_ERROR_6', 'CCM - Eine Gutschrift kann nicht gelöscht werden, wenn es wurde eine Zahlung angewendet werden!');
define ('GENERAL_JOURNAL_13_LEDGER_DISCOUNT', 'Customer Credit Memo Rabatt Menge');
define ('GENERAL_JOURNAL_13_LEDGER_FREIGHT', 'Customer Credit Memo Frachtbetrag');
define ('GENERAL_JOURNAL_13_LEDGER_HEADING', 'Customer Credit Memo Total');
// Inventory Versammlung Spezifische
define ('GENERAL_JOURNAL_14_DESC', 'Inventory Versammlung Eintrag');
// Inventory Anpassung Spezifische
define ('GENERAL_JOURNAL_16_DESC', 'Inventory Anpassung Eintrag');
// Zahlungseingänge spezifische
define ('BNK_18_ERROR_NO_VENDOR', 'Keine Kunden ausgewählt wurde!');
define ('BNK_18_ENTER_BILLS', 'Geben Kasseneinnahmen');
define ('BNK_18_ENTER_DEPOSIT', 'Customer Kaution');
define ('BNK_18_DELETE_BILLS', 'Lösche Kasseneinnahmen');
define ('BNK_18_C_WINDOW_TITLE', 'Customer Receipts ');
define ('BNK_18_V_WINDOW_TITLE', 'Hersteller Receipts ');
define ('BNK_18_BILL_TO', 'Receive From:');
define ('BNK_18_POST_SUCCESSFUL', 'erfolgreich gebucht Erhalt #');
define ('BNK_18_POST_DELETED', 'erfolgreich gelöscht Erhalt #');
define ('BNK_18_AMOUNT_PAID', 'Amt rcvd');
define ('BNK_18_DELETE_ALERT', 'Sind Sie sicher, dass Sie diesen Beleg löschen?');
define ('BNK_18_NEGATIVE_TOTAL', 'Der Erhalt Betrag kann nicht kleiner als Null sein!');
define ('ORD_TEXT_18_BILL_TO', 'Verkauf an:');
define ('ORD_TEXT_18_REF_NUM', 'Auftragsnummer');
define ('ORD_TEXT_18_WINDOW_TITLE', 'Kasseneinnahmen');
define ('ORD_TEXT_18_EXPIRES', 'Schiff nach Datum');
define ('ORD_TEXT_18_ERROR_NO_VENDOR', 'Keine Kunden ausgewählt wurde!');
define ('ORD_TEXT_18_NUMBER', 'Quittung Anzahl');
define ('ORD_TEXT_18_TEXT_REP', 'Sales Rep');
define ('ORD_TEXT_18_ITEM_COLUMN_1', 'SO Bal');
define ('ORD_TEXT_18_ITEM_COLUMN_2', 'Menge');
define ('ORD_TEXT_18_NUMBER_OF_ORDERS', TEXT_DISPLAY_NUMBER . 'Einnahmen');
define ('GENERAL_JOURNAL_18_DESC', 'Customer Receipts Eintrag');
define ('GENERAL_JOURNAL_18_ERROR_2', 'C / R - Der Empfang eingegebene Nummer ist ein Duplikat, geben Sie bitte eine neue Belegnummer!');


?>