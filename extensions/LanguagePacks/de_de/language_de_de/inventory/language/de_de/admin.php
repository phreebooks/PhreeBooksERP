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
//  Path: /modules/inventory/language/en_us/admin.php
//

// Modul-Informationen
define ('MODULE_INVENTORY_TITLE', 'Inventory-Modul');
define ('MODULE_INVENTORY_DESCRIPTION', 'Das Inventar Modul enthält alle Funktionen zum Speichern und manipulted Produkt-und Service Produkte in Ihrem Unternehmen eingesetzt Dazu gehören Begriffe für interne und externe Verwendung wie auch die Produkte die Sie verkaufen <b> Hinweis:.. Diese ist ein Kernmodul und sollte nicht entfernt werden </ b> ');
// Überschriften
define ('BOX_INV_ADMIN', 'Inventory-Administration');
define ('INV_TABS_HEADING_TITLE', 'Custom Tabs');
define ('INV_FIELDS_HEADING_TITLE', 'Custom Fields');
define ('INV_HEADING_FIELD_PROPERTIES', 'Feld Typ und Eigenschaften (eine Auswahl)');
// Allgemeine Definiert
define ('TEXT_DEFAULT_GL_ACCOUNTS', 'Default GL-Konten');
define ('TEXT_INVENTORY_TYPES', 'Inventory Typ');
define ('TEXT_SALES_ACCOUNT', 'Sales FIBU-Konto ');
define ('TEXT_INVENTORY_ACCOUNT', 'Inventory FIBU-Konto ');
define ('TEXT_COGS_ACCOUNT', 'Cost of Sales Account');
define ('TEXT_COST_METHOD', 'Cost Method');
define ('TEXT_STOCK_ITEMS', 'Stock');
define ('TEXT_MS_ITEMS', 'Master Stock');
define ('TEXT_ASSEMBLIES', 'Versammlungen');
define ('TEXT_SERIALIZED', 'Serialisierte');
define ('TEXT_NON-STOCK', 'Non-Stock');
define ('TEXT_SRV_ITEMS', 'Service');
define ('TEXT_LABOR_ITEMS', 'Arbeit');
define ('TEXT_ACT_ITEMS', 'Activity');
define ('TEXT_CHARGE_ITEMS', 'Laden');
// Install-Nachrichten
define ('MODULE_INVENTORY_NOTES_1', 'PRIORITÄT: Set Default Sachkonten für Inventar-Typen, nach dem Laden FIBU-Konten (Unternehmen -> Inventory-Modul Eigenschaften -> Registerkarte Inventar)');
/************************** (Inventar Defaults) ******************* ****************************/
define ('CD_05_50_DESC', 'Bestimmt die Standard-Mehrwertsteuersatz bei jeder Zugabe von Bestandsdaten an. HINWEIS: Dieser Wert zu inventarisieren Auto angewendet wird-Add kann aber im Inventar => Pflegen Bildschirm verändert werden Die Steuersätze werden von den ausgewählten. Tabelle tax_rates und muss durch Setup Setup => Umsatzsteuer Tarife werden ');
define ('CD_05_52_DESC', 'Bestimmt die Standard-Kauf Steuersatz zu verwenden, wenn du Inventargegenstände. HINWEIS: Dieser Wert wird angewendet Inventar Auto-Add kann aber im Inventar => Pflegen Bildschirm verändert werden Die Steuersätze werden von den ausgewählten. Tabelle tax_rates und muss durch Setup Setup => Kauf Steuersätze werden ');
define ('CD_05_55_DESC', 'Erlaubt die automatische Erstellung von Inventar Elemente in der Reihenfolge Bildschirme. SKUs sind nicht in PhreeBooks für Nicht-verfolgbaren Inventar Arten erforderlich. Diese Funktion ermöglicht die automatische Erstellung von SKUs in der Inventar-Datenbank-Tabelle. Das Inventar Art . verwendet werden Lagerbestände werden die FIBU-Konten verwendet wird die Standard-Konten und Kalkulation Methode für Lagerware werden ');
define ('CD_05_60_DESC', 'Erlaubt ein Ajax-Aufruf in Wahlmöglichkeiten wie Daten zu füllen ist in die SKU-Feld eingegeben. Diese Funktion ist hilfreich, wenn die SKUs sind bekannt und beschleunigt, um Formulare auszufüllen. Mai verlangsamen SKU Einträge als Barcode-Scanner verwendet werden. ');
define ('CD_05_65_DESC', 'Wenn aktiviert, sucht PhreeBooks für eine SKU Länge im Bestellformular gleich dem Bar-Code-Wert und Dauer, wenn die Länge erreicht ist, versucht, mit einem Gegenstand im Inventar übereinstimmen. Diese ermöglichen eine schnelle Eingabe der Begriffe bei der Verwendung von Barcode-Leser ');
define ('CD_05_70_DESC', '.. Legt die Anzahl der Zeichen zu erwarten, wenn das Lesen Inventar Barcode Werte PhreeBooks sucht nur, wenn die Anzahl der Zeichen erreicht wurde Typische Werte sind 12 und 13 Zeichen lang sein.');
define ('CD_05_75_DESC', 'Wenn aktiviert, wird das Element PhreeBooks Kosten im Inventar Tabelle entweder mit dem PO Preis oder Kauf Update / Empfangen Preis. Usefule für "on the fly PO / Einkauf und Aktualisierung Preise aus dem Auftrag Bildschirm ohne Update das Inventar Tabellen erste ');

/************************** (Inventar Tabs / Fields) ***************** ******************************/
define ('INV_INFO_CATEGORY_DESCRIPTION', 'Tab Beschreibung');
define ('INV_INFO_CATEGORY_NAME', 'Inventory Tab Name <br /> Name sollte kurz sein (10) mit keine Sonderzeichen oder Leerzeichen enthalten.');
define ('INV_INFO_INSERT_INTRO', 'Bitte geben Sie die neue Registerkarte mit seinen Eigenschaften');
define ('INV_INFO_HEADING_EDIT_CATEGORY', 'Register bearbeiten');
define ('INV_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese Registerkarte löschen \n Tabs können nicht gelöscht, wenn es eine Bestandsaufnahme Feld innerhalb der Registerkarte werden?.');
define ('INV_INFO_DELETE_ERROR', 'Diese Registerkarte Name bereits vorhanden ist, verwenden Sie bitte einen anderen Namen.');
define ('TEXT_DISPLAY_NUMBER_OF_TABS', TEXT_DISPLAY_NUMBER . "Inventar ein");
define ('TEXT_DISPLAY_NUMBER_OF_FIELDS', TEXT_DISPLAY_NUMBER . "Inventar Felder");
define ('INV_TABS_LOG', 'Inventory Tabs -');
define ('TEXT_TAB_MEMBER', 'Tab Mitglied');
define ('INV_FIELD_NAME', 'Field Name:');
define ('TEXT_SINGLE_PRECISION', 'Single Precision');
define ('TEXT_DBL_PREC', 'Double Precision');

define ('TEXT_DEFAULT_VALUE', 'Default-Wert');
define ('INV_LABEL_MAX_NUM_CHARS', 'Maximale Anzahl der Zeichen (Länge)');
define ('INV_LABEL_FIXED_255_CHARS', 'Fixed auf maximal 255 Zeichen');
define ('INV_LABEL_MAX_255', '(für Längen von weniger als 256 Zeichen)');
define ('INV_LABEL_CHOICES', 'Auswahl String eingeben');
define ('TEXT_TEXT_FIELD', 'Textfeld');
define ('TEXT_HTML_TEXT_FIELD', 'HTML-Code');
define ('INV_LABEL_HYPERLINK', 'Hyper-Link');
define ('INV_LABEL_IMAGE_LINK', 'Name der Bilddatei');
define ('INV_LABEL_INVENTORY_LINK', 'Inventory Link (Link zeigt auf eine andere Inventargegenstand (URL ))');
define ('INV_LABEL_INTEGER_FIELD', 'integer Anzahl');
define ('TEXT_INTEGER_RANGE', 'Integer Range');
define ('INV_LABEL_DECIMAL_FIELD', 'Dezimalklassifikation');
define ('TEXT_DECIMAL_RANGE', 'Dezimalbereich');
define ('INV_LABEL_DEFAULT_DISPLAY_VALUE', 'Anzeigeformat (Max, Decimal)');
define ('INV_LABEL_DROP_DOWN_FIELD', 'Dropdown-Liste');
define ('TEXT_RADIO_BUTTON', 'Radio-Button');
define ('INV_LABEL_DATE_TIME_FIELD', 'Datum und Zeit');
define ('TEXT_CHECK_BOX_FIELD', 'Check Box (Ja oder Nein Choice)');
define ('TEXT_TIME_STAMP', 'Time Stamp');
define ('INV_LABEL_TIME_STAMP_VALUE', 'Feld System zur letzten Datum und Uhrzeit eine Änderung an einem bestimmten Gegenstand im Inventar wurde zum Titel.');
define ('INV_FIELD_NAME_RULES', 'Feldnamen nicht enthalten können Leer-oder Sonderzeichen und muss 64 Zeichen lang sein.');
define ('INV_CATEGORY_CANNOT_DELETE', 'kann nicht gelöscht Kategorie Es wird von Feld verwendet.');
define ('INV_CANNOT_DELETE_SYSTEM', 'Felder in der Kategorie System kann nicht gelöscht werden!');
define ('INV_IMAGE_PATH_ERROR', 'Fehler in den Pfad für den Upload Bild angegeben!');
define ('INV_IMAGE_FILE_TYPE_ERROR', 'Fehler in der hochgeladenen Bild-Datei nicht akzeptabel Dateityp..');
define ('INV_IMAGE_FILE_WRITE_ERROR', 'Es wurde ein Problem beim Schreiben der Image-Datei in das angegebene Verzeichnis.');
define ('INV_FIELD_RESERVED_WORD', 'Der Name des Feldes eingegeben ist ein reserviertes Wort Bitte wählen Sie ein neues Feld Name..');

define ('INV_TOOLS_VALIDATE_SO_PO', 'Validate Bestandsmenge auf Auftrag Werte');
define ('INV_TOOLS_VALIDATE_SO_PO_DESC', 'Diese Operation durchführen, um sicherzustellen eurem Inventar Menge auf Bestellung und Menge der Sales Order Übereinstimmung mit der Journaleinträge Die berechneten Werte aus der Zeitschrift Einträge überschreiben den Wert in der Tabelle inventory..');
define ('INV_TOOLS_REPAIR_SO_PO', 'Test und Reparatur Bestandsmenge auf Auftrag Werte');
define ('INV_TOOLS_BTN_SO_PO_FIX', 'Begin Testen und Reparieren');
define ('INV_TOOLS_PO_ERROR', 'Artikel-Nr: %s hatte eine Menge auf Bestellung von%s und%s sein sollte Das Inventar Tabelle Gleichgewicht fixiert wurde.');
define ('INV_TOOLS_SO_ERROR', 'Artikel-Nr: %s hatte eine Menge auf Kundenauftrag von%s und%s sein sollte Das Inventar Tabelle Gleichgewicht fixiert wurde.');
define ('INV_TOOLS_SO_PO_RESULT', '. Fertige Verarbeitung Inventory Bestellmengen Die Gesamtzahl der verarbeiteten Artikel wurde%s. Die Anzahl der Datensätze mit Fehlern war%s.');
define ('INV_TOOLS_AUTDIT_LOG_SO_PO', 'Inv Tools - Repair SO / PO Menge (%s)');
define ('INV_TOOLS_VALIDATE_INVENTORY', 'Validate Inventar angezeigt Stock');
define ('INV_TOOLS_VALIDATE_INV_DESC', 'Diese Operation durchführen, um sicherzustellen eurem Inventar Mengen in der Inventar-Datenbank aufgelistet und in der Inventar-Bildschirmen sind die gleichen wie die Mengen im Inventar-Datenbank als Geschichte von PhreeBooks berechnet, wenn Lagerbewegungen auftreten. Der einzige Artikel getestet sind diejenigen, die in den Kosten der verkauften Waren Berechnung Reparieren Inventar Salden der Lagerbestand korrekt und lassen Sie das Inventar historischer Daten allein verfolgt werden ');
define ('INV_TOOLS_REPAIR_TEST', 'Test Inventory Guthaben bei COGS Geschichte');
define ('INV_TOOLS_REPAIR_FIX', 'Repair Inventory Guthaben bei COGS Geschichte');
define ('INV_TOOLS_REPAIR_CONFIRM', 'Sind Sie sicher, dass Sie das Inventar Lagerbestand Reparatur der PhreeBooks COGS Geschichte berechneten Werten überein?');
define ('TEXT_VERIFY_STOCK_BALANCES', 'Überprüfen Bestandsaufnahme');
define ('INV_TOOLS_BTN_REPAIR', 'Sync Menge auf Lager');
define ('INV_TOOLS_OUT_OF_BALANCE', 'Artikel-Nr: %s -> stock gibt%s auf der Hand, aber COGS History-Liste%s verfügbar');
define ('INV_TOOLS_IN_BALANCE', 'Ihr Inventar Guthaben sind in Ordnung.');
define ('INV_TOOLS_STOCK_ROUNDING_ERROR', '. SKU: %s -> Stock gibt%s auf der Hand, ist aber weniger als Ihre Präzision Bitte reparieren Ihr Inventar Salden, wird der Lagerbestand um %s gerundet .');
define ('INV_TOOLS_BALANCE_CORRECTED', 'Artikel-Nr: %s -> Der Lagerbestand auf der Hand hat, um %s angepasst.');
?>