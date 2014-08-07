<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
// +-----------------------------------------------------------------+
//  Path: /modules/shipping/language/en_us/language.php
//

// Headings
define('TEXT_SHIPPING_SERVICES','Shipping Services');
define('SHIPPING_HEADING_SHIP_MGR','Shipping Module Manager');
// General Defines
define('TEXT_PRODUCTION','Production');
define('TEXT_TEST','Test');
define('TEXT_PDF','PDF');
define('TEXT_GIF','GIF');
define('TEXT_THERMAL','Thermal');
define('TEXT_PACKAGE_DEFAULTS','Package Defaults');
define('TEXT_SHIPMENT_DEFAULTS','Shipment Defaults');
define('TEXT_SHIPMENTS_ON','Shipments On');
define('TEXT_REMOVE_MESSAGE','Are you sure you want to remove this shipping method?');
define('TEXT_CREATE_A_SHIPMENT_ENTRY','Create a Shipment Entry');
define('TEXT_SET_BY_SYSTEM','Set by the system');
define('TEXT_SHIPPING_RATE_ESTIMATOR','Shipping Rate Estimator');
define('SHIPPING_POPUP_WINDOW_RATE_TITLE','Shipping Estimator - Rates');
define('SHIPPING_ESTIMATOR_OPTIONS','Shipping Estimator - Shipment Options');
define('TEXT_SHIPPER','Shipper');
define('TEXT_SHIPMENT_DATE','Shipment Date');
define('TEXT_SHIP_FROM_CITY','Ship From City');
define('TEXT_SHIP_TO_CITY','Ship To City: ');
define('TEXT_RESIDENTIAL_ADDRESS','Residential Address');
define('TEXT_SHIP_FROM_STATE','Ship From State');
define('TEXT_SHIP_TO_STATE','Ship To State');
define('TEXT_SHIP_FROM_POSTAL_CODE','Ship From Postal Code');
define('TEXT_SHIP_TO_POSTAL_CODE','Ship To Postal Code');
define('TEXT_SHIP_FROM_COUNTRY','Ship From Country');
define('TEXT_SHIP_TO_COUNTRY','Ship To Country');
define('TEXT_PACKAGE_INFORMATION','Package Information');
define('TEXT_TYPE_OF_PACKAGING','Type of Packaging ');
define('TEXT_PICKUP_SERVICE','Pickup Service ');
define('TEXT_DIMENSIONS','Dimensions');
define('SHIPPING_ADDITIONAL_HANDLING','Additional Handling Applies (Oversize)');
define('SHIPPING_INSURANCE_AMOUNT','Insurance: Amount ');
define('TEXT_SPLIT_LARGE_SHIPMENTS_FOR_SMALL_PKG_CARRIER','Split large shipments for small pkg carrier ');
define('TEXT_PER_BOX',' per box');
define('TEXT_DELIVERY_CONFIRM','Delivery Confirmation ');
define('TEXT_SPECIAL_OPTIONS','Special Options');
define('TEXT_SERVICE_TYPE','Service Type');
define('SHIPPING_HANDLING_CHARGE','Handling Charge: Amount ');
define('TEXT_CASH_ON_DELIVERY_AMOUNT','COD: Collect ');
define('TEXT_SATURDAY_PICKUP','Saturday Pickup');
define('TEXT_SATURDAY_DELIVERY','Saturday Delivery');
define('TEXT_HAZARDOUS_MATERIALS','Hazardous Material');
define('TEXT_DRY_ICE','Dry Ice');
define('TEXT_RETURN_SERVICES','Return Services ');
define('TEXT_SHIPPING_METHODS','Shipping Methods');
define('TEXT_TOTAL_SHIPMENT_WEIGHT','Total Shipment Weight');
define('TEXT_TOTAL_SHIPMENT_VALUE','Total Shipment Value');
define('TEXT_E-MAIL_SENDER','E-mail Sender');
define('TEXT_E-MAIL_RECIPIENT','E-mail Recipient');
define('TEXT_SENDER_E-MAIL_ADDRESS','Sender E-mail Address');
define('TEXT_RECIPIENT_E-MAIL_ADDRESS','Recipient E-mail Address');
define('TEXT_EXCEPTION','Exception');
define('TEXT_DELIVER','Deliver');
define('TEXT_BILL_CHARGES_TO','Bill charges to');
define('SHIPPING_THIRD_PARTY','Recpt/Third Party Acct #');
define('TEXT_THIRD_PARTY_POSTAL_CODE','Third Party Postal Code');
define('TEXT_LTL_FREIGHT_CLASS','LTL Freight Class');
define('TEXT_NUMBER_OF_PIECES','Number of Pieces');
define('TEXT_SHIPMENT_SUMMARY','Shipment Summary');
define('TEXT_SHIPMENT_DETAILS','Shipment Details');
define('TEXT_PACKAGE_DETAILS','Package Details');
define('SHIPPING_VOID_SHIPMENT','Void Shipment');

define('SHIPPING_TEXT_CARRIER','Carrier');
define('SHIPPING_TEXT_SERVICE','Service');
define('TEXT_FREIGHT_QUOTE','Freight Quote');
define('TEXT_BOOK_PRICE','Book Price');
define('TEXT_COST','Cost');
define('SHIPPING_TEXT_NOTES','Notes');
define('TEXT_PRINT_LABEL','Print Label');
define('SHIPPING_TEXT_CLOSE_DAY','Daily Close');
define('SHIPPING_TEXT_DELETE_LABEL','Delete Shipment');
define('TEXT_SHIPMENT_ID','Shipment ID');
define('TEXT_REFERENCE_ID','Reference ID');
define('TEXT_TRACKING_NUMBER','Tracking Number');
define('TEXT_EXPECTED_DELIVERY_DATE','Expected Delivery Date');
define('TEXT_ACTUAL_DELIVERY_DATE','Actual Delivery Date');
define('TEXT_DOWNLOAD_THERMAL_LABEL','Download Thermal Label');
define('SHIPPING_THERMAL_INST','<br />The file is pre-formatted for thermal label printers. To print the label:<br /><br />
		1. Click the Download button to start the download.<br />
		2. Click on \'Save\' on the confirmation popup to save the file to you local machine.<br />
		3. Copy the file directly to the printer port. (the file must be copied in raw format)');
define('TEXT_NO_LABEL_FOUND','No Label Found!');
define('SHIPPING_NO_PACKAGES','There were no packages to ship, either the total quantity or weight was zero.');
define('SHIPPING_DELETE_ERROR','Error - Cannot delete the shipment, not enough information provided.');
define('SHIPPING_CANNOT_DELETE','Error - Cannot delete a shipment whose label was generated prior to today.');
define('TEXT_LABEL_DELETED','Label Deleted');
define('SHIPPING_END_OF_DAY','End of Day Close - %s');

define('TEXT_SHIPMENT_WEIGHT_CANNOT_BE_ZERO','Shipment weight cannot be zero.');
define('SHIPPING_DELETE_CONFIRM', 'Are you sure you want to delete this package?');
define('TEXT_THERE_ARE_NO_SHIPMENTS_FROM_THIS_CARRIER_TODAY', 'There are no shipments from this carrier today!');
define('SHIPPING_ERROR_CONFIGURATION', '<strong>Shipping Configuration errors!</strong>');
define('SHIPPING_BAD_QUOTE_DATE','The Shipment date cannot be before today for a quote. The ship date has been changed to today for quoting purposes.');
// Audit log messages
define('TEXT_LABEL_GENERATED','Label Generated');
// shipping options
define('SHIPPING_1DEAM','1 Day Early a.m.');
define('SHIPPING_1DAM','1 Day a.m.');
define('SHIPPING_1DPM','1 Day p.m.');
define('SHIPPING_1DFRT','1 Day Freight');
define('SHIPPING_2DAM','2 Day a.m.');
define('SHIPPING_2DPM','2 Day p.m.');
define('SHIPPING_2DFRT','2 Day Freight');
define('SHIPPING_3DPM','3 Day');
define('SHIPPING_3DFRT','3 Day Freight');
define('TEXT_GROUND','Ground');
define('TEXT_GROUND_RESIDENTIAL','Ground Residential');
define('SHIPPING_GNDFRT','Ground LTL Freight');
define('TEXT_WORLDWIDE_EARLY_EXPRESS','Worldwide Early Express');
define('TEXT_WORLDWIDE_EXPRESS','Worldwide Express');
define('TEXT_WORLDWIDE_EXPEDITED','Worldwide Expedited');
define('SHIPPING_IGND','Ground (Canada)');

define('TEXT_SHIP_A_PACKAGE','Ship a Package');
define('SHIPPING_CREATE_ENTRY','Create a Shipment Entry');
define('TEXT_RECONCILE_BILL','Reconcile Bill');
define('TEXT_RECEPIENT_INFORMATION','Recepient Information');
define('TEXT_EMAIL_NOTIFICATIONS','Email Notifications');
define('TEXT_BILLING_DETAILS','Billing Details');
define('TEXT_LTL_FREIGHT','LTL Freight');
define('TEXT_FREIGHT_CLASS','Freight Class');
define('TEXT_CONFIRM_DELIVERY','Confirm Delivery');

define('TEXT_DAILY_PICKUP','Daily Pickup');
define('TEXT_CARRIER_CUSTOMER_COUNTER','Carrier Customer Counter');
define('TEXT_REQUEST_ONE_TIME_PICKUP','Request/One Time Pickup');
define('TEXT_ON_CALL_AIR','On Call Air');
define('TEXT_SUGGESTED_RETAIL_RATES','Suggested Retail Rates');
define('TEXT_DROP_BOX_OR_CENTER','Drop Box/Center');
define('TEXT_AIR_SERVICE_CENTER','Air Service Center');

define('TEXT_POUND_SHORT','lbs');
define('TEXT_KILOGRAMS_SHORT','kgs');

define('TEXT_INCHES_SHORT','in');
define('TEXT_CENTIMETERS_SHORT','cm');

define('TEXT_ENVELOPE_OR_LETTER','Envelope/Letter');
define('TEXT_CUSTOMER_SUPPLIED','Customer Supplied');
define('TEXT_CARRIER_TUBE','Carrier Tube');
define('TEXT_CARRIER_PAK','Carrier Pak');
define('TEXT_CARRIER_BOX','Carrier  Box');
define('SHIPPING_25KG','25kg Box');
define('SHIPPING_10KG','10kg Box');

define('SHIPPING_CASH','Cash');
define('TEXT_CHECK','Check');
define('TEXT_CASHIERS_CHECK','Cashier\'s Check');
define('TEXT_MONEY_ORDER','Money Order');
define('TEXT_ANY','Any');

define('TEXT_NO_DELIVERY_CONFIRMATION','No delivery confirmation');
define('TEXT_NO_SIGNATURE_REQUIRED','No Signature Required');
define('TEXT_SIGNATURE_REQUIRED','Signature Required');
define('TEXT_ADULT_SIGNATURE_REQUIRED','Adult Signature Required');

define('TEXT_CARRIER_RETURN_LABEL','Carrier Return Label');
define('TEXT_PRINT_LOCAL_RETURN_LABEL','Print Local Return Label');
define('TEXT_CARRIER_PRINTS_AND_MAILS_RETURN_LABEL','Carrier Prints and Mails Return Label');

define('TEXT_SENDER','Sender');
define('TEXT_RECEIPIENT','Receipient');
define('TEXT_THIRD_PARTY','Third Party');
define('TEXT_COLLECT','Collect');

// Key descriptions
define('SHIPPING_TITLE_DESC', 'Title to use for display purposes on shipping rate estimator');
define('TEXT_WHAT_IS_THE_SHIPPING_COST', 'What is the Shipping cost');
define('TEXT_HANDLING_FEE_FOR_THIS_SHIPPING_METHOD', 'Handling fee for this shipping method.');
define('SORT_ORDER_DESC', 'Sort order of display. Determines the order which this method appears on all generted lists.');
define('SHIPPING_PRINTER_TYPE_DESC','Type of printer to use for printing labels. PDF for plain paper, Thermal for Eltron/Zebra Label Printer');
define('SHIPPING_PRINTER_NAME_DESC', 'Sets then name of the printer to use for printing labels as defined in the printer preferences for the local workstation');
define('TEXT_SELECT_THE_SERVICES_TO_BE_OFFERED_BY_DEFAULT','Select the services to be offered by default');
define('SHIPPING_TEST_MODE_DESC','Test/Production mode used for testing shipping labels');
?>