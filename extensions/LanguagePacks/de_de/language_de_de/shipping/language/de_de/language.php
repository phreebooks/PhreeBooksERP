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
//  Path: /modules/shipping/language/en_us/language.php
//

// Überschriften
define ('TEXT_SHIPPING_SERVICES', 'Shipping Services');
define ('SHIPPING_HEADING_SHIP_MGR', 'Versandkosten Module Manager');
define ('TEXT_SHIPPING_MODULES_AVAILABLE', 'Versand-Methoden verfügbar');

// Allgemeine Definiert
define ('TEXT_PRODUCTION', 'Produktion');
define ('TEXT_TEST', 'Test');
define ('TEXT_PDF', 'PDF');
define ('TEXT_GIF', 'GIF');
define ('TEXT_THERMAL', 'Thermal');
define ('TEXT_PACKAGE_DEFAULTS', 'Paket Defaults');
define ('TEXT_SHIPMENT_DEFAULTS', 'Versand Defaults');
define ('TEXT_REMOVE_MESSAGE', 'Sind Sie sicher, dass Sie diese Versandart entfernen?');

define ('TEXT_CREATE_A_SHIPMENT_ENTRY', 'Sendung erstellen Eintrag');
define ('TEXT_SET_BY_SYSTEM', 'Durch das System');

define ('TEXT_SHIPPING_RATE_ESTIMATOR', 'Liefer-Rate Estimator');
define ('SHIPPING_POPUP_WINDOW_RATE_TITLE', 'Versandkosten Estimator - Preise');
define ('SHIPPING_ESTIMATOR_OPTIONS', 'Versandkosten Estimator - Versand-Optionen');
define ('TEXT_SHIPPER', 'Absender');
define ('TEXT_SHIPMENT_DATE', 'Versand Datum');
define ('TEXT_SHIP_FROM_CITY', 'Schiff aus der Stadt');
define ('TEXT_SHIP_TO_CITY "," Ship to City:');
define ('TEXT_RESIDENTIAL_ADDRESS', 'Wohn-Adresse');
define ('TEXT_SHIP_FROM_STATE', 'Schiff Vom Staat');
define ('TEXT_SHIP_TO_STATE', 'Ship To Staat');
define ('TEXT_SHIP_FROM_POSTAL_CODE', 'Ship Von PLZ');
define ('TEXT_SHIP_TO_POSTAL_CODE', 'Ship To PLZ');
define ('TEXT_SHIP_FROM_COUNTRY', 'Ship Von Land');
define ('TEXT_SHIP_TO_COUNTRY', 'Schiff zu Land');
define ('TEXT_PACKAGE_INFORMATION', 'Paket-Informationen');
define ('TEXT_TYPE_OF_PACKAGING', 'Art der Verpackung');
define ('TEXT_PICKUP_SERVICE', 'Pickup-Service ');
define ('TEXT_DIMENSIONS', 'Maße');
define ('SHIPPING_ADDITIONAL_HANDLING', 'Zusätzliche Handhabung Gilt (Oversize)');
define ('SHIPPING_INSURANCE_AMOUNT', 'Versicherung: Anzahl');
define ('TEXT_SPLIT_LARGE_SHIPMENTS_FOR_SMALL_PKG_CARRIER', 'Split große Sendungen für kleine Träger pkg');
define ('TEXT_PER_BOX', 'pro Schachtel');
define ('TEXT_DELIVERY_CONFIRM', 'Zustellbestätigung');
define ('TEXT_SPECIAL_OPTIONS', 'Spezielle Optionen');
define ('TEXT_SERVICE_TYPE', 'Service-Typ');
define ('SHIPPING_HANDLING_CHARGE', 'Handling Charge: Menge');
define ('TEXT_CASH_ON_DELIVERY_AMOUNT', 'COD: Sammeln');
define ('TEXT_SATURDAY_PICKUP', 'Saturday Pickup');
define ('TEXT_SATURDAY_DELIVERY', 'Saturday Delivery');
define ('SHIPPING_HAZARDOUS_MATERIALS', 'Hazardous Material');
define ('TEXT_DRY_ICE', 'Dry Ice');
define ('TEXT_RETURN_SERVICES', 'Return Services');
define ('TEXT_SHIPPING_METHODS', 'Versand-Methoden');
define ('TEXT_TOTAL_SHIPMENT_WEIGHT', 'Total Versand Gewicht');
define ('TEXT_TOTAL_SHIPMENT_VALUE', 'Total Versand Value');
define ('TEXT_E-MAIL_SENDER', 'E-Mail-Absender');
define ('TEXT_E-MAIL_RECIPIENT', 'E-Mail-Empfänger');
define ('TEXT_SENDER_E-MAIL_ADDRESS', 'Absender E-Mail-Adresse');
define ('TEXT_RECIPIENT_E-MAIL_ADDRESS', 'Empfänger E-Mail-Adresse');
define ('TEXT_EXCEPTION', 'Exception');
define ('TEXT_DELIVER', 'Deliver');
define ('SHIPPING_PRINT_LABEL', 'Print Label');
define ('TEXT_BILL_CHARGES_TO', 'Bill Gebühren für');
define ('SHIPPING_THIRD_PARTY', 'Recpt / Third Party Account #');
define ('TEXT_THIRD_PARTY_POSTAL_CODE', 'Third Party PLZ');
define ('TEXT_LTL_FREIGHT_CLASS', 'LTL Freight Klasse');
define ('SHIPPING_DEFAULT_LTL_CLASS', '125 ');
define ('TEXT_SHIPMENT_SUMMARY', 'Versand Zusammenfassung');
define ('TEXT_SHIPMENT_DETAILS', 'Versand Details');
define ('TEXT_PACKAGE_DETAILS', 'Paket-Details');
define ('SHIPPING_VOID_SHIPMENT', 'Sendung stornieren');

define ('SHIPPING_TEXT_CARRIER', 'Carrier');
define ('SHIPPING_TEXT_SERVICE', 'Service');
define ('TEXT_FREIGHT_QUOTE', 'Freight Zitat');
define ('TEXT_BOOK_PRICE', 'Book Price');
define ('TEXT_COST', 'Kosten');
define ('SHIPPING_TEXT_NOTES', 'Notizen');
define ('TEXT_PRINT_LABEL', 'Print Label');
define ('SHIPPING_TEXT_CLOSE_DAY', 'Daily schließen');
define ('SHIPPING_TEXT_DELETE_LABEL', 'Lösche Versand');
define ('TEXT_SHIPMENT_ID', 'Versand-ID');
define ('TEXT_REFERENCE_ID', 'Referenz-ID');
define ('TEXT_TRACKING_NUMBER', 'Tracking-Nummer');
define ('TEXT_EXPECTED_DELIVERY_DATE', 'Voraussichtlicher Liefertermin');
define ('TEXT_ACTUAL_DELIVERY_DATE', 'tatsächlichem Lieferdatum');
define ('TEXT_DOWNLOAD_THERMAL_LABEL', 'Download Thermotransfer-Etikettendrucker');
define ('SHIPPING_THERMAL_INST', '<br/> Die Datei wird vorformatiert für Thermo-Etikettendrucker Um das Etikett zu drucken. <br/> <br/>
1. Klicken Sie auf die Schaltfläche Download, um den Download zu starten. <br/>
2. Klicken Sie auf  "Speichern" auf der Bestätigungs-Popup, um die Datei zu sparen lokalen Rechner. <br/>
3. Kopieren Sie die Datei direkt an den Drucker-Port. (Die Datei muss im RAW-Format kopiert werden) ');
define ('TEXT_NO_LABEL_FOUND', 'No Label gefunden!');

define ('TEXT_SHIPMENT_WEIGHT_CANNOT_BE_ZERO', 'Versand Gewicht kann nicht Null sein.');
define ('SHIPPING_DELETE_CONFIRM', 'Sind Sie sicher, dass Sie dieses Paket wirklich löschen?');
define ('TEXT_THERE_ARE_NO_SHIPMENTS_FROM_THIS_CARRIER_TODAY', 'Es gibt keine Lieferungen aus diesem Träger noch heute!');
define ('SHIPPING_ERROR_CONFIGURATION', '<strong> Versandkosten Konfigurationsfehler </ strong>');

// Audit Log-Meldungen
define ('SHIPPING_LOG_FEDEX_LABEL_PRINTED', 'Label Erstellt');

// Versandoptionen
define ('SHIPPING_1DEAM', '1 Tag früher a.m. ');
define ('SHIPPING_1DAM', '1 Tag a.m. ');
define ('SHIPPING_1DPM', '1 Tag p.m. ');
define ('SHIPPING_1DFRT', '1 Day Freight ');
define ('SHIPPING_2DAM', '2 Day a.m. ');
define ('SHIPPING_2DPM', '2 Day p.m. ');
define ('SHIPPING_2DFRT', '2 Day Freight ');
define ('SHIPPING_3DPM', '3 Tage ');
define ('SHIPPING_3DFRT', '3 Day Freight ');
define ('TEXT_GROUND', 'Ground');
define ('TEXT_GROUND_RESIDENTIAL', 'Ground Residential');
define ('SHIPPING_GNDFRT', 'Ground LTL Freight ');
define ('TEXT_WORLDWIDE_EARLY_EXPRESS', 'Worldwide Express Frühe');
define ('TEXT_WORLDWIDE_EXPRESS', 'Worldwide Express');
define ('TEXT_WORLDWIDE_EXPEDITED', 'Worldwide Expedited');
define ('SHIPPING_IGND', 'Ground (Kanada)');

define ('TEXT_DAILY_PICKUP', 'Daily Pickup');
define ('TEXT_CARRIER_CUSTOMER_COUNTER', 'Carrier Customer Counter');
define ('TEXT_REQUEST_ONE_TIME_PICKUP', 'Request / One Time Pickup');
define ('TEXT_ON_CALL_AIR', 'On Call Air');
define ('TEXT_SUGGESTED_RETAIL_RATES', 'unverb. Preise');
define ('TEXT_DROP_BOX_OR_CENTER', 'Drop Box / Center');
define ('TEXT_AIR_SERVICE_CENTER', 'Air Service Center ');

define ('TEXT_POUND_SHORT', 'lbs');
define ('TEXT_KILOGRAMS_SHORT', 'kg');

define ('TEXT_INCHES_SHORT', 'in');
define ('TEXT_CENTIMETERS_SHORT', 'cm');

define ('TEXT_ENVELOPE_OR_LETTER', 'Envelope / Letter');
define ('TEXT_CUSTOMER_SUPPLIED', 'Kunde angegeben');
define ('TEXT_CARRIER_TUBE', 'Carrier Tube');
define ('TEXT_CARRIER_PAK', 'Carrier Pak');
define ('TEXT_CARRIER_BOX', 'Carrier-Box');
define ('SHIPPING_25KG', '25kg Box ');
define ('SHIPPING_10KG', '10kg Box ');

define ('SHIPPING_CASH', 'Cash');
define ('TEXT_CHECK', 'Check');
define ('TEXT_CASHIERS_CHECK', 'Kassierer\'s Check ');
define ('TEXT_MONEY_ORDER', 'Money Order');
define ('TEXT_ANY', 'Jeder');

define ('TEXT_NO_DELIVERY_CONFIRMATION', 'Keine Empfangsbestätigung');
define ('TEXT_NO_SIGNATURE_REQUIRED', 'Keine Unterschrift erforderlich');
define ('TEXT_SIGNATURE_REQUIRED', 'Signatur');
define ('TEXT_ADULT_SIGNATURE_REQUIRED', 'Adult Signature Required ');

define ('TEXT_CARRIER_RETURN_LABEL', 'Carrier Return Label');
define ('TEXT_PRINT_LOCAL_RETURN_LABEL', 'Print Lokale Rückholaufkleber');
define ('TEXT_CARRIER_PRINTS_AND_MAILS_RETURN_LABEL', 'Carrier Drucke und Mails Rückholaufkleber');

define ('TEXT_SENDER', 'Absender');
define ('TEXT_RECEIPIENT', 'Empfängername');
define ('TEXT_THIRD_PARTY', 'Third Party');
define ('TEXT_COLLECT', 'Collect');
?>