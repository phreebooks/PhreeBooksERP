<?php
//
//  Path: /modules/phreeform/config_phreeform.php
//
/*
$objLocales = $_SESSION['language']->load_countries();
$arrLocales = array();
foreach ($objLocales->country as $country) $arrLocales[$country->iso3] = array('iso2' => $country->iso2, 'name' => $country->name);
$FormProcessing['cvt_iso2']  = 'Convert ISO2';
$FormProcessing['cvt_cntry'] = 'Convert Name';
function pf_process_phreeform($strData, $Process) {
  global $arrLocales;
  switch ($Process) {
	case "cvt_iso2":  return isset($arrLocales[$strData]['iso2']) ? $arrLocales[$strData]['iso2'] : $strData; break;
	case "cvt_cntry": return isset($arrLocales[$strData]['name']) ? $arrLocales[$strData]['name'] : $strData; break;
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}
*/
?>