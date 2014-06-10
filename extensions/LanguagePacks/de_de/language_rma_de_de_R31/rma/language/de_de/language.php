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
define ('TEXT_RECEIVE_CARRIER', 'Versand Carrier');
define ('TEXT_RECEIVE_TRACKING_NUM', 'Sendungsverfolgung #');
define ('TEXT_RECEIVE_NOTES', 'Empfangen Notes');
// Fehlermeldungen
define ('RMA_MESSAGE_ERROR', 'Es wurde ein Fehler beim Erstellen / Aktualisieren der RMA.');
define ('RMA_MESSAGE_SUCCESS_ADD', 'erfolgreich RMA # erstellt');
define ('RMA_MESSAGE_SUCCESS_UPDATE', 'erfolgreich RMA # aktualisiert');
define ('RMA_MESSAGE_DELETE', 'Die RMA wurde erfolgreich gelöscht.');
define ('RMA_ERROR_CANNOT_DELETE', 'Es war ein Fehler beim Löschen der RMA.');
// Definiert Javascrpt
define ('RMA_MSG_DELETE_RMA', 'Sind Sie sicher, dass Sie diese RMA löschen?');
define ('RMA_ROW_DELETE_ALERT', 'Sind Sie sicher, dass Sie diesen Artikel Zeile löschen?');
// Audit-Log-Einträge
define ('RMA_LOG_USER_ADD', 'RMA Erstellt - RMA #');
define ('RMA_LOG_USER_UPDATE', 'RMA Aktualisiert - RMA #');
// Codes für Status-und RMA-Grund
define ('RMA_STATUS_0', 'Wählen Sie Status ...');
define ('RMA_STATUS_1', 'RMA Erstellt / Warten auf Teile');
define ('RMA_STATUS_2', 'Teile erhalten');
define ('RMA_STATUS_3', 'In Inspection');
define ('RMA_STATUS_4', 'In Disposition');
define ('RMA_STATUS_5', 'Im Test');
define ('RMA_STATUS_6', 'Waiting for Credit');
define ('RMA_STATUS_7', 'Closed - Ersetzt');
define ('RMA_STATUS_90', 'geschlossen - nicht empfangen');
define ('RMA_STATUS_99', 'Closed');

define ('RMA_REASON_0', 'Wählen Grund für RMA ...');
define ('RMA_REASON_1', 'nicht nötig');
define ('RMA_REASON_2', 'Z falschen Teil');
define ('RMA_REASON_3', 'passte nicht');
define ('RMA_REASON_4', 'defekt / Swap out');
define ('RMA_REASON_5', 'während des Versands beschädigt');
define ('RMA_REASON_80', 'Falsche Connector');
define ('RMA_REASON_99', 'Sonstige (bitte angeben in Notes)');

define ('RMA_ACTION_0', 'Aktion auswählen ...');
define ('RMA_ACTION_1', 'Return to Stock ');
define ('RMA_ACTION_2', 'Return to Customer');
define ('RMA_ACTION_3', 'Test & Ersetzen');
define ('RMA_ACTION_4', 'Garantie Ersetzen');
define ('RMA_ACTION_5', 'Schrott');
define ('RMA_ACTION_99', 'Sonstige (bitte angeben in Notes)');

?>