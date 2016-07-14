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
	private $locales = array();

	/**
	 * sets the current language and sets it in the Session variable.
	 *
	 */
	function __construct(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__);
		if( isset($_REQUEST['language']) && $_REQUEST['language'] != '') {
			$this->language_code = $_REQUEST['language'];
		} else {
			if(defined('DEFAULT_LANGUAGE')) {
				$this->language_code = DEFAULT_LANGUAGE;
			}else if( isset($_COOKIE['pb_language'])){
				$this->language_code = $_COOKIE['pb_language'];
			}else if( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) == 5){
				$this->language_code = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			}
		}
		if (sizeof($this->languages) == 0) $this->get_languages();
		if (sizeof($this->phrases)   == 0) $this->get_translations();
		if (!file_exists(DIR_FS_INCLUDES."language/custom/locals.xml")) $this->create_countries();
	}

	public function __wakeup() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__);
		if( isset($_REQUEST['language']) && $_REQUEST['language'] != '') $this->language_code = $_REQUEST['language'];
		foreach ($this->phrases as $key => $value ) define($key, $value);
		if (!file_exists(DIR_FS_INCLUDES."language/custom/locals.xml")) $this->create_countries();
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
	
	function load_countries() {
		if (sizeof($this->locales) == 0) {
			\core\classes\messageStack::debug_log("executing ".__METHOD__ );
			if (file_exists(DIR_FS_MODULES . "phreedom/language/{$this->language_code}/locales.xml")) {
				if (($xmlStr = @file_get_contents(DIR_FS_MODULES . "phreedom/language/{$this->language_code}/locales.xml")) === false) 	throw new \core\classes\userException(sprintf(ERROR_READ_FILE, "phreedom/language/{$this->language_code}/locales.xml"));
			} else {
				if (($xmlStr = @file_get_contents(DIR_FS_MODULES . "phreedom/language/en_us/locales.xml")) === false) 					throw new \core\classes\userException(sprintf(ERROR_READ_FILE, "phreedom/language/en_us/locales.xml"));
			}
			$this->locales =  simplexml_load_string ($xmlStr);
		}
		if (isset($this->locales->data)) return $this->locales->data;
		return $this->locales;
	}
}
?>
