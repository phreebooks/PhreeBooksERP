<?php
// +-----------------------------------------------------------------+
// Phreedom Language Translation File
// Generated: 2011-11-27 05:02:31
// Module/Method: inventory
// ISO Language: nl_nl
// Version: 3.3
// +-----------------------------------------------------------------+
// Path: /modules/inventory/language/nl_nl/admin.php

define('MODULE_INVENTORY_TITLE','Voorraad Module');
define('MODULE_INVENTORY_DESCRIPTION','Deze voorraad module bevat alle functionaliteiten om product en service items gebruikt in uw bedrijf te bewerken en bewaren.<br/> Hier vallen ook dingen onder voor intern en extern gebruik.');
define('BOX_INV_ADMIN','Voorraad Administratie');
define('INV_HEADING_FIELD_PROPERTIES','Veld type en eigenschappen (Selecteer een)');
define('TEXT_DEFAULT_GL_ACCOUNTS','Standaard Grookboek rekeningen');
define('TEXT_INVENTORY_TYPES','Voorraad Type');
define('TEXT_SALES_ACCOUNT','Grootboek Verkopen');
define('TEXT_INVENTORY_ACCOUNT','Grootboek Voorraad');
define('TEXT_COGS_ACCOUNT','Grootboek Kostprijs van verkopen');
define('TEXT_COST_METHOD','Kosten Methode');
define('TEXT_STOCK_ITEMS','Voorraad');
define('TEXT_MS_ITEMS','Hoofd Voorraad');
define('TEXT_ASSY_ITEMS','Assemblages');
define('TEXT_SERIAL_ITEMS','Serie');
define('TEXT_NS_ITEMS','Niet-voorraad');
define('TEXT_SRV_ITEMS','Service');
define('TEXT_LABOR_ITEMS','Arbeid');
define('TEXT_ACT_ITEMS','Activiteit');
define('TEXT_CHARGE_ITEMS','Kosten');
define('MODULE_INVENTORY_NOTES_1','MEDIUM BELANGRIJK: Stel standaard grootboekrekeningen in voor voorraad types, Later kunt u gaan naar (Bedrijf -> Voorraad Module Eigenschappen -> Voorraad tabblad)');
define('CD_05_50_DESC','Bepaalt het standaard belasting tarief wanneer nieuwe voorraad wordt toegevoegd.<br /><br /> OPMERKING: Deze waarde wordt toegepast op de Auto-voorraad toevoegen maar kan gewijzigd worden in de Voorraad => Onderhoud scherm. De belastingtarieven zijn geselecteerd uit de tabel tax_rates en moet instellen via Setup => Verkoop belastingtarieven.');
define('CD_05_52_DESC','Bepaalt het standaard belasting tarief wanneer nieuwe voorraad wordt toegevoegd.<br /><br /> OPMERKING: Deze waarde wordt toegepast op de Auto-voorraad toevoegen maar kan gewijzigd worden in de Voorraad => Onderhoud scherm. De belastingtarieven zijn geselecteerd uit de tabel tax_rates en moet instellen via Setup => Inkoop belastingtarieven.');
define('CD_05_55_DESC','Hiermee kan automatisch Voorraad gemaakt worden in de order schermen. <br /> <br /> Artikelnummers zijn niet vereist voor niet-traceerbare voorraad typen. Deze functie zorgt voor de automatisch maken van artikelen in de Voorraad database tabel. Het voorraad type gebruikt wordt is: voorraad-item. De grootboekrekeningen gebruikt zullen worden zijn de standaard rekeningen zo ook met de kostprijsberekening Methode.');
define('CD_05_60_DESC','Hiermee kan een ajax call de mogelijke keuzen in vullen, als gegevens worden ingevoerd in het artikelnummer veld. Deze functie is handig wanneer de Artikelnummer bekend zijn en het versnelt het invullen van bestelformulieren. Kan vertragen wanneer barcode scanners worden gebruikt.');
define('CD_05_65_DESC','Wanneer deze is ingeschakeld, zoekt PhreeBooks naar een artikelnummer lengte in de bestelbon gelijk is aan de Bar Code lengte wanneer de lengte is bereikt, pogingen om overeenstemming te brengen met een voorraadartikel. Dit zorgt voor snel invoeren van items met behulp van barcode-lezers.');
define('CD_05_70_DESC','Stelt het aantal tekens te verwachten bij het lezen van voorraad streepjescode. PhreeBooks zoekt alleen wanneer het aantal tekens is bereikt. Typische waarden zijn 12 en 13 tekens.');
define('CD_05_75_DESC','Wanneer ingeschakeld, wordt item kostenprijs in de voorraad tabel bijgewerkt met de InkoopOrder prijs of Aankoop/ontvangen prijs. Handig om vanuit een inkoopOrder / Aankoop de kostprijs te updaten zonder dat de voorraad tabellen eerst geüpdatet moeten worden.');
define('INV_TOOLS_VALIDATE_SO_PO','Valideer voorraad aantallen in orders');
define('INV_TOOLS_VALIDATE_SO_PO_DESC','Deze actie test of de voorraad aantallen op inkoop orders en verkoop Orders overeenkomen met de journaalposten. De berekende waarden uit de journaalposten hebben voorrang op de waarde in de inventaris tabel.');
define('INV_TOOLS_REPAIR_SO_PO','Test en repareer Voorraad aantallen in Orders');
define('INV_TOOLS_BTN_SO_PO_FIX','Begin Test en reparatie');
define('INV_TOOLS_PO_ERROR','Art.nr.: %s had een hoeveelheid op Inkoop Orders van %s en dient te worden %s. De voorraad tabel is gecorrigeerd.');
define('INV_TOOLS_SO_ERROR','Art.nr.: %s had een hoeveelheid op Verkoop Orders van %s en dient te worden %s. De voorraad tabel is gecorrigeerd.');
define('INV_TOOLS_SO_PO_RESULT','Klaar met verwerken van voorraad aantallen. Het totaal aantal verwerkte items was %s. Het aantal records met fouten was %s.');
define('INV_TOOLS_AUTDIT_LOG_SO_PO','Voorraad Gereedschappen - Repareer Verkoop-InkoopOrder Aantallen (%s)');
define('INV_TOOLS_VALIDATE_INVENTORY','Valideer Getoonde Voorraad');
define('INV_TOOLS_VALIDATE_INV_DESC','Deze operatie tests om te controleren of uw inventaris hoeveelheden zijn opgenomen in de inventaris database en weergegeven in de inventaris schermen zijn dezelfde als de hoeveelheden die in de inventaris geschiedenis database, zoals berekend door PhreeBooks wanneer de voorraad bewegingen voordoen. De geteste alleen items zijn degenen die worden bijgehouden in de kostprijs van verkochte goederen berekening. Herstellen inventaris saldi zal corrigeren de hoeveelheid in voorraad en laat de inventaris geschiedenis alleen. ');
define('INV_TOOLS_REPAIR_TEST','Test Voorraad Balans met Art. Historie');
define('INV_TOOLS_REPAIR_FIX','Repareer Voorraad Balans met Art. Historie');
define('INV_TOOLS_REPAIR_CONFIRM','Weet u zeker dat u de Voorraad wil repareren aan de hand van PhreeBooks Art. historie berekende waardes?');
define('INV_TOOLS_BTN_TEST','Controleer de voorraad aantallen');
define('INV_TOOLS_BTN_REPAIR','Synchroniseer aantallen op voorraad');
define('INV_TOOLS_OUT_OF_BALANCE','Art.nr.: %s -> op voorraad %s maar volgens de artikel historie zijn/is er %s beschikbaar');
define('INV_TOOLS_IN_BALANCE','Uw voorraad balans is in orde.');
define('INV_TOOLS_STOCK_ROUNDING_ERROR','Art.nr: %s -> Voorraad geeft aan %s dit is minder dan uw precisie. Repareer uw voorraad balans de voorraad zal worden afgerond naar %s.');
define('INV_TOOLS_BALANCE_CORRECTED','Art.nr.: %s -> De aanwezige voorraad is veranderd naar %s.');
define('NEXT_SKU_NUM_DESC','Volgend Artikel Nummer');

?>
