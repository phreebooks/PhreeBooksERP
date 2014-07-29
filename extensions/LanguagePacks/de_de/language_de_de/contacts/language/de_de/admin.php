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
//  Path: /modules/contacts/language/en_us/admin.php
//

// Modul-Informationen
define ('TEXT_CONTACTS_MODULE', 'Modul Kontakte');
define ('MODULE_CONTACTS_DESCRIPTION', 'Die Kontakte Modul verwaltet alle Kunden, Lieferanten, Mitarbeiter, Niederlassungen und Projekten in den PhreeSoft Business Toolkit verwendet <b>. HINWEIS:! Dies ist ein Kernmodul und sollte nicht entfernt werden </ b>') ;
// Überschriften
define ('TEXT_CONTACTS_ADMINISTRATION', 'Kontakte Administration');
define ('TEXT_BILLING_PREFS', 'Billing Address Book Einstellungen');
define ('TEXT_SHIPPING_ADDRESS_BOOK_SETTINGS', 'Versandkosten Adressbuch Einstellungen');
// Allgemeine
define ('COST_TYPE_LBR', 'Arbeit');
define ('TEXT_MATERIALS', 'Material');
define ('TEXT_CONTRACTORS', 'Contractors');
define ('TEXT_EQUIPMENT', 'Ausrüstung');
define ('TEXT_OTHER', 'Andere');
/************************** (Adressbuch Defaults) ****************** *****************************/
define ('CONTACT_BILL_FIELD_REQ', 'Ob zu verlangen Feld: %s für einen neuen Haupt-/ Rechnungsadresse eingegeben werden (für Lieferanten, Kunden und Mitarbeiter)');
define ('CONTACT_SHIP_FIELD_REQ', 'Ob zu verlangen Feld: %s, um eine neue Versandadresse eingegeben werden');
/************************** (Abteilungen) ******************** ***************************/
define ('HR_POPUP_WINDOW_TITLE', 'Kategorien');
define ('TEXT_SUB_DEPARTMENT', 'Lehrstuhlgruppe');
define ('HR_EDIT_INTRO', 'Bitte führen Sie alle notwendigen Änderungen durch');
define ('TEXT_DEPARTMENT_ID', 'Department ID');
define ('HR_INFO_SUBACCOUNT', 'Ist diese Abteilung eine Unterabteilung?');
define ('TEXT_YES_ALSO_SELECT_PRIMARY_DEPARTMENT', 'Ja, auch wählen primären Abteilung');
define ('HR_INFO_ACCOUNT_TYPE', 'Abteilung Art');
define ('HR_INFO_ACCOUNT_INACTIVE', 'Abteilung inaktiv');
define ('HR_INFO_INSERT_INTRO', 'Bitte geben Sie die neue Abteilung mit ihren Eigenschaften');
define ('HR_INFO_NEW_ACCOUNT', 'Neue Abteilung');
define ('HR_INFO_EDIT_ACCOUNT', 'Edit: Ministerium');
define ('HR_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese Abteilung wirklich löschen?');
define ('HR_DISPLAY_NUMBER_OF_DEPTS', TEXT_DISPLAY_NUMBER . "Abteilungen");
define ('HR_DEPARTMENT_REF_ERROR', 'Die Abteilung kann nicht dieselbe sein wie dieser Unterabteilung gerettet!');
define ('HR_LOG_DEPARTMENTS', 'Kategorien -');
/************************** (Abteilung Typen) ******************* ****************************/
define ('TEXT_DEPARTMENT_TYPES', 'Abteilung Typen');
define ('SETUP_INFO_DEPT_TYPES_NAME', 'Abteilung Typ Name');
define ('TEXT_PLEASE_ENTER_THE_NEW_DEPARTMENT_TYPE', 'Bitte geben Sie die neue Abteilung Art');
define ('SETUP_DEPT_TYPES_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese Abteilung Typ löschen?');
define ('SETUP_DEPT_TYPES_DELETE_ERROR', 'kann nicht gelöscht werden dieser Abteilung geben, sie wird die Nutzung durch eine Abteilung.');
define ('SETUP_INFO_HEADING_NEW_DEPT_TYPES', 'Neue Abteilung Typ');
define ('SETUP_INFO_HEADING_EDIT_DEPT_TYPES', 'Edit Abteilung Typ');
define ('TEXT_DISPLAY_NUMBER_OF_DEPT_TYPES', TEXT_DISPLAY_NUMBER . "department Arten");
define ('SETUP_DEPT_TYPES_LOG', 'Dept Typen -');
/************************** (Project Costs) ******************* ****************************/
define ('SETUP_TITLE_PROJECTS_COSTS', 'Project Costs');
define ('TEXT_SHORT_NAME', 'Short Name');
define ('TEXT_COST_TYPE', 'Kostenart');
define ('SETUP_INFO_DESC_SHORT', 'Short Name (16 Zeichen max)');
define ('SETUP_INFO_DESC_LONG', 'Lange Beschreibung (64 Zeichen max)');
define ('SETUP_PROJECT_COSTS_INSERT_INTRO', 'Bitte geben Sie das neue Projekt kosten mit seinen Eigenschaften');
define ('SETUP_PROJECT_COSTS_DELETE_INTRO', 'Sind Sie sicher, dass Sie dieses Projekt kosten löschen?');
define ('SETUP_INFO_HEADING_NEW_PROJECT_COSTS', 'Neues Projekt Kosten');
define ('SETUP_INFO_HEADING_EDIT_PROJECT_COSTS', 'Edit Project Cost');
define ('SETUP_INFO_COST_TYPE', 'Kostenart');
define ('SETUP_PROJECT_COSTS_LOG', 'Projektkosten -');
define ('SETUP_PROJECT_COSTS_DELETE_ERROR', 'kann nicht gelöscht werden dieses Projekt kosten, sie wird die Verwendung in einem Tagebucheintrag.');
define ('SETUP_DISPLAY_NUMBER_OF_PROJECT_COSTS', TEXT_DISPLAY_NUMBER . "Projektkosten");
/************************** (Projektphasen) ******************* ****************************/
define ('TEXT_PROJECT_PHASES', 'Projektphasen');
define ('TEXT_COST_BREAKDOWN', 'Cost Breakdown');
define ('TEXT_USE_COST_BREAKDOWNS_FOR_THIS_PHASE', 'Verwenden Sie Kostenaufstellungen für diese Phase?');
define ('SETUP_PROJECT_PHASES_INSERT_INTRO', 'Bitte geben Sie die neue Projektphase mit seinen Eigenschaften');
define ('SETUP_PROJECT_PHASES_DELETE_INTRO', 'Sind Sie sicher, dass Sie dieser Projektphase löschen?');
define ('SETUP_INFO_HEADING_NEW_PROJECT_PHASES', 'Neues Projekt Phase');
define ('SETUP_INFO_HEADING_EDIT_PROJECT_PHASES', 'Edit Project Phase');
define ('SETUP_PROJECT_PHASESS_LOG', 'Projektphasen -');
define ('SETUP_PROJECT_PHASESS_DELETE_ERROR', 'kann nicht gelöscht werden dieser Projektphase ist es sein Einsatz in einem Tagebucheintrag.');
define ('SETUP_DISPLAY_NUMBER_OF_PROJECT_PHASES', TEXT_DISPLAY_NUMBER . "Projektphasen");
?>