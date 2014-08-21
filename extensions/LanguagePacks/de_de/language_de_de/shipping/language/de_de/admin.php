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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/shipping/language/en_us/admin.php
//

// Modul-Informationen
define ('MODULE_SHIPPING_TITLE', 'Versandkosten Modul');
define ('MODULE_SHIPPING_DESCRIPTION', 'Die Versandkosten Modul ist ein Wrapper für Benutzer konfigurierbare Versandarten Einige Methoden mit dem Kern Paket enthalten sind und andere sind zum Download auf der Website zur Verfügung PhreeSoft..');

/************************** (Versand Defaults) ******************* ****************************/
define ('CD_10_01_DESC', '. Legt die Standard-Maßeinheit für alle Pakete Gültige Werte sind: Pfund, Kilogramm');
define ('CD_10_02_DESC', '. Standardwährung für Sendungen nutzen Gültige Werte sind: US-Dollar, Euro');
define ('CD_10_03_DESC', '. Paket Maßeinheit Gültige Werte sind: Zoll, Zentimeter');
define ('CD_10_04_DESC', 'Default Wohnschiff Box (ungeprüft - Kommerzielle, geprüft - Wohn-)');
define ('TEXT_DEFAULT_PACKAGE_TYPE_TO_USE_FOR_SHIPPING', 'Default-Paket Typ für den Versand verwenden');
define ('TEXT_DEFAULT_TYPE_OF_PICKUP_SERVICE_FOR_YOUR_PACKAGE_SERVICE', 'Default Art der Pick-up Service für Ihr Paket Dienst');
define ('TEXT_DEFAULT_PACKAGE_DIMENSIONS_TO_USE_FOR_A_STANDARD_SHIPMENT', 'Default-Paket Dimensionen für ein Standard-Versand verwenden (in Einheiten oben angegebenen ).');
define ('TEXT_ADDITIONAL_HANDLING_CHARGE_CHECKBOX', 'Zusätzliche Bearbeitungsgebühr Checkbox');
define ('TEXT_SHIPMENT_INSURANCE_SELECTION_OPTION', 'Versicherung der Sendung Auswahlmöglichkeit.');
define ('TEXT_ALLOW_HEAVY_SHIPMENTS_TO_BE_BROKEN_DOWN_TO_USE_SMALL_PACKAGE_SERVICE', 'Erlaube schwere Sendungen zu untergliedern, um kleine Paket-Dienst verwenden');
define ('TEXT_DELIVERY_CONFIRMATION_CHECKBOX', 'Lieferbestätigung Checkbox');
define ('CD_10_32_DESC', 'Zusätzliche Bearbeitungsgebühr Checkbox');
define ('TEXT_ENABLE_THE_COD_CHECKBOX_AND_OPTIONS', 'Aktivieren Sie die Checkbox und CSB-Optionen');
define ('TEXT_SATURDAY_PICKUP_CHECKBOX', 'Saturday Pickup Checkbox');
define ('TEXT_SATURDAY_DELIVERY_CHECKBOX', 'Samstagszustellung Checkbox');
define ('TEXT_HAZARDOUS_MATERIAL_CHECKBOX', 'Gefährliche Stoffe Checkbox');
define ('TEXT_DRY_ICE_CHECKBOX', 'Trockeneis Checkbox');
define ('TEXT_RETURN_SERVICES_CHECKBOX', 'Return Service Checkbox');

define ('SHIPPING_METHOD', 'Select "-Methode:');
define ('SHIPPING_MONTH', 'Wählen Sie Monat:');
define ('SHIPPING_YEAR', 'Wählen Jahr:');
define ('SHIPPING_TOOLS_TITLE', 'Versandkosten Label File Maintenance');
define ('SHIPPING_TOOLS_CLEAN_LOG_DESC', 'Dieser Vorgang erstellt eine Sicherungskopie Ihrer heruntergeladen Versandetikett Dateien. Dies wird helfen, die Server-Speicher Größe herunter und reduzieren Unternehmen Backup-Dateien. Sichern dieser Dateien ist vor der Reinigung aus alten Akten zu PhreeBooks Transaktion erhalten empfohlen Geschichte <br /> INFORMATIONEN: Reinigen Sie die Versandetiketten werden die aktuellen Datensätze in der Datenbank Versand Manager verlassen und logs ');
?>