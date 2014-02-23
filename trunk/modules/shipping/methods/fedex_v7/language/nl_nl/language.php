<?php
// +-----------------------------------------------------------------+
// Phreedom Language Translation File
// Generated: 2011-11-27 05:02:37
// Module/Method: shipping-fedex_v7
// ISO Language: nl_nl
// Version: 3.2
// +-----------------------------------------------------------------+
// Path: /modules/shipping/methods/fedex_v7/language/nl_nl/language.php

define('MODULE_SHIPPING_FEDEX_V7_TEXT_TITLE','Federal Express');
define('MODULE_SHIPPING_FEDEX_V7_TEXT_DESCRIPTION','Federal Express');
define('MODULE_SHIPPING_FEDEX_V7_TITLE_DESC','De titel welke gebruikt moet worden in weergaven. ');
define('MODULE_SHIPPING_FEDEX_V7_ACCOUNT_NUMBER_DESC','Voer het FedEx-klantnummer te gebruiken voor tarief schattingen');
define('MODULE_SHIPPING_FEDEX_V7_LTL_ACCOUNT_NUMBER_DESC','Voer de FedEx LTL rekeningnummer te gebruiken voor tarief schattingen. Laat dit veld leeg als er geen LTL zal worden gebruikt.');
define('MODULE_SHIPPING_FEDEX_V7_NAT_ACCOUNT_NUMBER_DESC','Voer de FedEx Nationale LTL klantnummer in te gebruiken voor tarief schattingen. Laat dit veld leeg als er geen LTL Freight zal worden gebruikt.');
define('MODULE_SHIPPING_FEDEX_V7_AUTH_KEY_DESC','Voer de FedEx-ontwikkelaar sleutel in welke u geleverd is door FedEx.');
define('MODULE_SHIPPING_FEDEX_V7_AUTH_PWD_DESC','Voer de FedEx-ontwikkelaar wachtwoord in afgegeven door FedEx.');
define('MODULE_SHIPPING_FEDEX_V7_METER_NUMBER_DESC','Voer de meter nummer in welke aan u is geleverde door FedEx.');
define('MODULE_SHIPPING_FEDEX_V7_GND','Grond');
define('MODULE_SHIPPING_FEDEX_V7_GDR','Thuis Bezorging');
define('MODULE_SHIPPING_FEDEX_V7_1DM','Eerste Klas Overnacht');
define('MODULE_SHIPPING_FEDEX_V7_1DA','Priority Overnacht');
define('MODULE_SHIPPING_FEDEX_V7_1DP','Standaard Overnacht');
define('MODULE_SHIPPING_FEDEX_V7_2DP','Express 2 Dagen');
define('MODULE_SHIPPING_FEDEX_V7_3DS','Express Goedkoop');
define('MODULE_SHIPPING_FEDEX_V7_XDM','Int. Eerste Klas');
define('MODULE_SHIPPING_FEDEX_V7_XPR','Int. Priority');
define('MODULE_SHIPPING_FEDEX_V7_XPD','Int. Economie');
define('MODULE_SHIPPING_FEDEX_V7_1DF','1 dagen Vracht');
define('MODULE_SHIPPING_FEDEX_V7_2DF','2 dagen Vracht');
define('MODULE_SHIPPING_FEDEX_V7_3DF','3 dagen Vracht');
define('MODULE_SHIPPING_FEDEX_V7_GDF','Grond LTL Vracht');
define('MODULE_SHIPPING_FEDEX_V7_ECF','Gnd Economy Vracht');
define('SHIPPING_FEDEX_V7_VIEW_REPORTS','Bekijk rapporten voor');
define('SHIPPING_FEDEX_V7_CLOSE_REPORTS','Eind Rapport');
define('SHIPPING_FEDEX_V7_MULTIWGHT_REPORTS','Multigewicht Rapport');
define('SHIPPING_FEDEX_V7_HAZMAT_REPORTS','Hazmat Rapport');
define('SHIPPING_FEDEX_CURL_ERROR','Er is een fout in de communicatie met de FedEx-server:');
define('SHIPPING_FEDEX_V7_RATE_ERROR','FedEx tarief respons fout:');
define('SHIPPING_FEDEX_V7_RATE_CITY_MATCH','Stad komt niet overeen met de postcode.');
define('SHIPPING_FEDEX_V7_RATE_TRANSIT','Dag (en) onderweg, arriveert');
define('SHIPPING_FEDEX_V7_TNT_ERROR','FedEx Tijd onderweg Error # ');
define('SHIPPING_FEDEX_V7_DEL_ERROR','FedEx Verwijder Label Error: ');
define('SHIPPING_FEDEX_V7_DEL_SUCCESS','Succesvol verwijderd van de FedEx-label. Traceer #');
define('SHIPPING_FEDEX_V7_TRACK_ERROR','FedEx Pakket Traceer Error:');
define('SHIPPING_FEDEX_V7_TRACK_SUCCESS','Succesvol getraceerd Pakket Referentie #');
define('SHIPPING_FEDEX_V7_TRACK_STATUS','Het pakket referentie:%s is niet geleverd, de status is: (Code %s) %s.');
define('SHIPPING_FEDEX_V7_TRACK_FAIL','De volgende pakket referentienummer werd afgeleverd na de verwachte datum / tijd:');
define('SHIPPING_FEDEX_V7_CLOSE_SUCCESS','Succesvol de FedEx zendingen voor vandaag afgesloten.');
define('SRV_TRACK_FEDEX_V7','Traceer FedEx verzendingen van vandaag');
define('ONE_DAY','Een dag onderweg');
define('TWO_DAYS','Twee dagen onderweg');
define('THREE_DAYS','Drie dagen onderweg');
define('FOUR_DAYS','Vier dagen onderweg');
define('FIVE_DAYS','Vijf dagen onderweg');
define('SIX_DAYS','Zes dagen onderweg');
define('SEVEN_DAYS','Zeven dagen onderweg');
define('EIGHT_DAYS','Acht dagen onderweg');
define('NINE_DAYS','Negen dagen onderweg');
define('TEN_DAYS','Tien dagen onderweg');
define('ELEVEN_DAYS','Elf dagen onderweg');
define('TWELVE_DAYS','Twaalf dagen onderweg');
define('THIRTEEN_DAYS','Dertien dagen onderweg');
define('FOURTEEN_DAYS','Veertien dagen onderweg');
define('FIFTEEN_DAYS','Vijftien dagen onderweg');
define('SIXTEEN_DAYS','Zestien dagen onderweg');
define('SEVENTEEN_DAYS','Zeventien dagen onderweg');
define('EIGHTEEN_DAYS','Achttien dagen onderweg');
define('NINETEEN_DAYS','Negentien dagen onderweg');
define('TWENTY_DAYS','Twintig dagen onderweg');
define('UNKNOWN','Onbekend aantal dage onderweg');
define('TEXT_PRINT_PDF_DOCUMENTS','Print PDF documents (after label printing is complete)');
define('TEXT_PRINT_PAPER','Print Paper Documents');
define('SHIPPING_FEDEX_ERROR_POSTAL_CODE','Postcode is vereist om de FedEx module te gebruiken');
define('SHIPPING_FEDEX_RECON_TITLE','FedEx Verzoenings-Rapport gegeneerd: ');
define('SHIPPING_FEDEX_RECON_INTRO','Factuur Nummer: %s gedateerd %s');
define('SHIPPING_FEDEX_RECON_NO_RECORDS','Verzenddatum :%s Referentie %s, traceer # %s - Er zijn geen records gevonden, verzending van %s tot %s');
define('SHIPPING_FEDEX_RECON_TOO_MANY','Verzenddatum :%s Referentie %s, traceer # %s - Er zijn te veel records gevonden, verzending van %s tot %s');
define('SHIPPING_FEDEX_RECON_COST_OVER','Verzenddatum :%s Referentie %s, traceer # %s - Gefactureerde kosten: (%s) geoffreerde kosten: (%s)');
define('SHIPPING_FEDEX_RECON_SUMMARY','Totaal aantal records verzoend: %s');

?>
