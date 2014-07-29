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
//  Path: /modules/rma/language/en_us/language.php
//

// Überschriften
define ('TEXT_RETURN_MATERIAL_AUTHORIZATIONS', 'Return Material Berechtigungen');
define ('MENU_HEADING_NEW_RMA', 'Create New RMA');

// Allgemeine Definiert
define ('TEXT_RMAS', 'RMAs');
define ('TEXT_RMA_ID', 'RMA Num');
define ('TEXT_ASSIGNED_BY_SYSTEM', '(vom System)');
define ('TEXT_CREATION_DATE', 'Erstellt');
define ('TEXT_PURCHASE_INVOICE_ID', 'Sales / Invoice #');
define ('TEXT_CALLER_NAME', 'Anrufer-Name');
define ('TEXT_CLOSED', 'Closed');
define ('TEXT_TELEPHONE', 'Telefon');
define ('TEXT_DETAILS', 'Details');
define ('TEXT_REASON_FOR_RETURN', 'Grund für die Rückgabe');
define ('TEXT_ENTERED_BY', 'Eingetragen von');
define ('TEXT_DATE_RECEIVED', 'Date Received');
define ('TEXT_RECEIVED_BY', 'Received von');
define ('TEXT_SHIPMENT_CARRIER', 'Versand Carrier');
define ('TEXT_RECEIVE_TRACKING_NUM', 'Sendungsverfolgung #');
define ('TEXT_RECEIVE_NOTES', 'Empfangen Notes');
// Fehlermeldungen
define ('TEXT_THERE_WAS_AN_ERROR_CREATING_OR_UPDATING_THE_RMA', 'Es wurde ein Fehler beim Erstellen / Aktualisieren der RMA.');
define ('RMA_MESSAGE_SUCCESS_ADD', 'erfolgreich RMA # erstellt');
define ('RMA_MESSAGE_SUCCESS_UPDATE', 'erfolgreich RMA # aktualisiert');
define ('RMA_MESSAGE_DELETE', 'Die RMA wurde erfolgreich gelöscht.');
define ('TEXT_THERE_WAS_AN_ERROR_DELETING_THE_RMA', 'Es war ein Fehler beim Löschen der RMA.');
// Definiert Javascrpt
define ('RMA_MSG_DELETE_RMA', 'Sind Sie sicher, dass Sie diese RMA löschen?');
define ('RMA_ROW_DELETE_ALERT', 'Sind Sie sicher, dass Sie diesen Artikel Zeile löschen?');
// Audit-Log-Einträge
define ('RMA_LOG_USER_ADD', 'RMA Erstellt - RMA #');
define ('RMA_LOG_USER_UPDATE', 'RMA Aktualisiert - RMA #');
// Codes für Status-und RMA-Grund
define ('RMA_STATUS_0', 'Wählen Sie Status ...');
define ('TEXT_RMA_CREATED_AND_WAITING_FOR_PARTS', 'RMA Erstellt / Warten auf Teile');
define ('TEXT_PARTS_RECEIVED', 'Teile erhalten');
define ('TEXT_RECEIVING_INSPECTION', 'In Inspection');
define ('TEXT_IN_DISPOSITION', 'In Disposition');
define ('TEXT_IN_TEST_OR_EVALUATION', 'Im Test');
define ('TEXT_WAITING_FOR_CREDIT', 'Waiting for Credit');
define ('TEXT_CLOSED_AND_REPLACED', 'Closed - Ersetzt');
define ('RMA_STATUS_90', 'geschlossen - nicht empfangen');
define ('RMA_STATUS_99', 'Closed');

define ('RMA_REASON_0', 'Wählen Grund für RMA ...');
define ('TEXT_DID_NOT_NEED', 'nicht nötig');
define ('TEXT_ORDERED_WRONG_PART', 'Z falschen Teil');
define ('TEXT_DID_NOT_FIT', 'passte nicht');
define ('TEXT_DEFECTIVE_OR_SWAP_OUT', 'defekt / Swap out');
define ('TEXT_DAMAGED_IN_SHIPPING', 'während des Versands beschädigt');
define ('TEXT_WRONG_CONNECTOR', 'Falsche Connector');
define ('TEXT_OTHER_SPECIFY_IN_NOTES', 'Sonstige (bitte angeben in Notes)');

define ('RMA_ACTION_0', 'Aktion auswählen ...');
define ('TEXT_RETURN_TO_STOCK', 'Return to Stock ');
define ('TEXT_RETURN_TO_CUSTOMER', 'Return to Customer');
define ('TEXT_TEST_AND_REPLACE', 'Test & Ersetzen');
define ('TEXT_WARRANTY_REPLACE', 'Garantie Ersetzen');
define ('TEXT_SCRAP', 'Schrott');
define ('RMA_ACTION_99', 'Sonstige (bitte angeben in Notes)');

?>