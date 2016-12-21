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
// +-----------------------------------------------------------------+
//  Path: /includes/classes/language.php
//
namespace core\classes;
class language {
	public $language_code = "en_us";
	public $phrases;
	public $languages;
	public $translate;
	public $countries;

	/**
	 * sets the current language and sets it in the Session variable.
	 *
	 */
	function __construct(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__);
		if( isset($_REQUEST['language']) && $_REQUEST['language'] != '') {
			\core\classes\messageStack::debug_log("language requested {$_REQUEST['language']}");
			$this->language_code = $_REQUEST['language'];
		} else {
			if( !empty($_COOKIE['pb_language'])){
				\core\classes\messageStack::debug_log("language cookie set {$_COOKIE['pb_language']}");
				$this->language_code = $_COOKIE['pb_language'];
			}else if( !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) == 5){
				\core\classes\messageStack::debug_log("language accept Language set {$_SERVER['HTTP_ACCEPT_LANGUAGE']}");
				$this->language_code = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			}else if(defined('DEFAULT_LANGUAGE') && DEFAULT_LANGUAGE !== '') {
				\core\classes\messageStack::debug_log("default language defined". DEFAULT_LANGUAGE);
				$this->language_code = DEFAULT_LANGUAGE;
			}
		}
		if (sizeof($this->languages) == 0) $this->get_languages();
		if (sizeof($this->phrases)   == 0) $this->get_translations();
		if (sizeof($this->countries) == 0) $this->get_countries();
	}

	public function __wakeup() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__);
		if (sizeof($this->languages) == 0) $this->get_languages();
		if( isset($_REQUEST['language']) && $_REQUEST['language'] != '') {
			$this->language_code = $_REQUEST['language'];
			$this->get_translations();
			$this->get_countries();
			
		}else{
			if (sizeof($this->phrases)   == 0) $this->get_translations();
			if (sizeof($this->countries) == 0) $this->get_countries();
		}
		foreach ($this->phrases as $key => $value ) define($key, $value);
		
	}
	
	/**
	 * function will get all language constants.
	 * @throws \core\classes\userException
	 */
	private function get_translations() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." finding language code " .$this->language_code);
		//fetch default language phrases
		$lang_path = DIR_FS_INCLUDES."language/translations.xml";
		if (!file_exists($lang_path)) throw new \core\classes\userException("can't find your language file {$lang_path} ");
		$xml = new \DomDocument();
		$xml->load($lang_path);
		$phrases = $xml->getElementsByTagName($this->language_code);
		foreach  ($phrases as $phrase) {
			$this->phrases[$phrase->parentNode->getAttribute('xml:id')] = $phrase->nodeValue;
		}
		if (sizeof($this->phrases) == 0) throw new \core\classes\userException("there are no translations for your language {$this->language_code} ");
		//fetch custom language phrases
		$custom_path = DIR_FS_INCLUDES."language/custom/translations.xml";
		if (file_exists($custom_path)) {
			$xml = new \DomDocument();
			$xml->load($custom_path);
			//phrases
			$phrases = $xml->getElementsByTagName($this->language_code);
			foreach  ($phrases as $phrase) {
				$this->phrases[$phrase->parentNode->getAttribute('xml:id')] = $phrase->nodeValue;
			}
		}
		foreach ($this->phrases as $key => $value ) define($key, $value);
//		\core\classes\messageStack::debug_log("end ".__METHOD__ ." finding languages " .print_r($this->phrases,true));
	}

	/**
	 * function will get all availeble languages provided that the id "Language" is translatated.
	 * @throws \core\classes\userException
	 */
	private function get_languages(){
		//fetch all languages
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$lang_path = (DIR_FS_INCLUDES."language/translations.xml");
		if (!file_exists($lang_path)) throw new \core\classes\userException("can't find your language file {$lang_path} ");
		$xml = new \DomDocument();
		$xml->load($lang_path);
		$phrases = $xml->getElementById('LANGUAGE');
		foreach ($phrases->childNodes as $phrase) {
			if ($phrase->tagName != ''){
				$this->languages[$phrase->tagName]= array(
					'id'   => $phrase->tagName,
		  	  		'text' => $phrase->nodeValue,
				);
			}
		}
	}

	static function add_constant($constant){
		$lang_path = (DIR_FS_INCLUDES."language/translations.xml");
		if (!file_exists($lang_path)) throw new \core\classes\userException("can't find your language file {$lang_path} ");
		$doc = new \DOMDocument('1.0', 'utf-8');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->load($lang_path);
		$string = strtolower(str_replace(array('TEXT_', '_', 'ARGS'), array('', ' ', '%'),$constant));
		if ($doc->getElementById($constant) !== null){
			define($constant, $string);
			error_log("The constant $constant is not defined in your language ". PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
			return;
		}
		$root = $doc->documentElement;
		$first_element = $doc->createElement('translation');
		$first_element->setAttribute('xml:id', $constant);
		$second = $root->appendChild($first_element);
		$temp = $doc->createElement('en_us', $string);
		$second->appendChild($temp);
		$doc->normalizeDocument();
		$doc->save($lang_path);
		define($constant, $string);
		error_log("The constant $constant is added to your language file ". PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
	}
	
	/**
	 * function will get all country constants.
	 * @throws \core\classes\userException
	 */
	private function get_countries() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." finding language code " .$this->language_code);
		//fetch default language phrases
		$lang_path = DIR_FS_INCLUDES."language/locals.xml";
		if (!file_exists($lang_path)) throw new \core\classes\userException("can't find your locals file {$lang_path} ");
		$xml = new \DomDocument();
		$xml->load($lang_path);
		$countries = $xml->getElementsByTagName('country');
		foreach  ($countries as $country) {
			foreach($country->childNodes as $i) {
				if ($i->tagName == 'translations') {
					foreach($i->childNodes as $language) {
						if (!empty($language->tagName) ){
							$this->countries[$country->getAttribute('xml:id')]['translations'][$language->tagName] = $language->nodeValue;
							if ($language->tagName == $this->language_code) $this->countries[$country->getAttribute('xml:id')]['name'] = $language->nodeValue;
						}
					}
				}else if ($i->tagName == 'zones') {
					foreach($i->childNodes as $zones) {
						if (!empty($zones->tagName) ){
							foreach($zones->childNodes as $zone) {
								if ($zone->tagName == 'translations') {
									foreach($zone->childNodes as $language) {
										if (!empty($language->tagName) ){//@todo check
											$this->countries[$country->getAttribute('xml:id')]['zones'][$zones->getAttribute('xml:id')]['translations'][$language->tagName] = $language->nodeValue;
											if ($language->tagName == $this->language_code) $this->countries[$country->getAttribute('xml:id')]['zones'][$zones->getAttribute('xml:id')]['name'] = $language->nodeValue;
										}
									}
								}else{
									if (!empty($zone->tagName)) $this->countries[$country->getAttribute('xml:id')]['zones'][$zones->getAttribute('xml:id')][$zone->tagName] = $zone->nodeValue;
								}
							}
						}
					}
				}else{
					if (!empty($i->tagName)) $this->countries[$country->getAttribute('xml:id')][$i->tagName] = $i->nodeValue;
				}
			}
		}
		if (sizeof($this->countries) == 0) throw new \core\classes\userException("there are no countries for your language {$this->language_code} ");
		uasort ( $this->countries, array ( $this, 'arangeObjectByNameValue') );
	}
	
	/**
	 * this method is for sorting a array of objects by the sort_order variable
	 */
	function arangeObjectByNameValue($a, $b) {
		return strcmp ( $a['name'], $b['name'] );
	}
	
	
	function load_countries(){
		if (sizeof($this->countries) == 0) $this->get_countries();
		return $this->countries;
	}
	
	function get_country_iso_2_from_3($iso3 = COMPANY_COUNTRY) {
		if (!isset($this->countries[$iso3])) throw new \core\classes\userException ( sprintf(TEXT_COULDNT_FIND_ISO3, $iso3));
		$this->countries[$iso3]->iso2;
	}
	
	function get_country_iso_3_from_2($iso2) {
		foreach ($this->countries as $iso3 => $value) if ($value->iso2 == $iso2) return $value->iso3;
		if (!isset($this->countries[$iso2])) throw new \core\classes\userException ( sprintf(TEXT_COULDNT_FIND_ISO2, $iso2));
	}
	
	/**
	 * @param unknown $search_country
	 * @param unknown $search_zone
	 * @return iso2
	 */
	
	function get_country_codes($search_country, $search_zone) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$codes = array('country' => $search_country, 'state' => $search_zone);
		foreach ($this->countries as $iso3 => $country) {
			if ($country->name == $search_country){
				$codes['country'] = $country->iso2;
				foreach ($country->zones as $key => $zone) {
					if ($zone->name == $search_zone){
						$codes['state'] = $zone->iso2;
					}
				}
			}
		}
		return $codes;
	}
	
	function get_countries_dropdown($choose = false) {
		$output = array();
		if ($choose) $output[] = array('id' => '0', 'text' => TEXT_PLEASE_SELECT);
		foreach ($this->countries as $iso3 => $value) $output[] = array('id' => $iso3, 'text' => $value['name']);
		return $output;
	}
	
}
	
?>
