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

	/**
	 * sets the current language and sets it in the Session variable.
	 *
	 */
	function __construct(){
		if( isset($_SESSION['language']) && $_SESSION['language'] != '') {
			 $this->language_code = $_SESSION['language'];
		} else if( isset($_REQUEST['language']) && $_REQUEST['language'] != '') {
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
//		$this->find_language_constants();
		if (sizeof($this->languages) == 0) $this->get_languages();
		if (sizeof($this->phrases)   == 0) $this->get_translations();
	}

	public function __wakeup() {
		if( isset($_SESSION['language']) && $_SESSION['language'] != '') {
			$this->language_code = $_SESSION['language'];
		} else if( isset($_REQUEST['language']) && $_REQUEST['language'] != '') {
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
		//		$this->find_language_constants();
		if (sizeof($this->languages) == 0) $this->get_languages();
		if (sizeof($this->phrases)   == 0) $this->get_translations();
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
		$phrases = $xml->getElementsByTagName('translation');
		foreach ($phrases as $phrase) {
			foreach ($phrase->childNodes as $language) {
				if ($language->tagName == $this->language_code) $this->phrases[$phrase->getAttribute('id')] = $language->nodeValue;
			}
		}
		if (sizeof($this->phrases) == 0) throw new \core\classes\userException("there are no translations for your language {$this->language_code} ");
		//fetch custom language phrases
		$custom_path = DIR_FS_INCLUDES."language/custom/translations.xml";
		if (file_exists($custom_path)) {
			$xml = new \DomDocument();
			$xml->load($custom_path);
			//phrases
			$phrases = $xml->getElementsByTagName('translation');
			if ($phrases->length != 0) {
				foreach ($phrases as $phrase) {
					foreach ($phrase->childNodes as $language) {
						if ($language->tagName == $this->language_code) $this->phrases[$phrase->getAttribute('id')] = $language->nodeValue;
					}
				}
			}
		}
		foreach ($this->phrases as $key => $value ) define($key, $value);
	}

	/**
	 * function will get all availeble languages provided that the id "Language" is translatated.
	 * @throws \core\classes\userException
	 */
	private function get_languages(){
		//fetch all languages
		$lang_path = (DIR_FS_INCLUDES."language/translations.xml");
		if (!file_exists($lang_path)) throw new \core\classes\userException("can't find your language file {$lang_path} ");
		$xml = new \DomDocument();
		$xml->load($lang_path);
		$phrases = $xml->getElementsByTagName('translation');
		foreach ($phrases as $phrase) {
			if (strtoupper($phrase->getAttribute('id')) == 'LANGUAGE') {
				foreach ($phrase->childNodes as $language) {
					if ($language->tagName != ''){
						$this->languages[$language->tagName]= array(
							'id'   => $language->tagName,
				  	  		'text' => $language->nodeValue,
						);
					}
				}
				break;
			}
		}
	}

	/**
	 * function will read language files and add contants to xml language file.
	 * @todo should be deleted before release 4.0
	 */
	function find_language_constants(){
		$dirs = @scandir ( DIR_FS_MODULES );
		foreach ( $dirs as $dir ) {
			if ($dir == '.' || $dir == '..') continue;
			$lang_dir = DIR_FS_MODULES . $dir . "/language";
			if (is_dir ( $lang_dir )) {
				$language_folders = @scandir ( $lang_dir );
				foreach ( $language_folders as $language_folder ) {
					if ($language_folder == '.' || $language_folder == '..') continue;
					$language_files = @scandir ( $lang_dir .'/'. $language_folder );
					foreach ( $language_files as $language_file ) {
						if ($language_file == '.' || $language_file == '..') continue;
						if (is_dir ( $language_file )) {
							$language_sub_folders = @scandir ( "{$lang_dir}/{$language_folder}/{$language_file}");
							foreach ( $language_sub_folders as $language_sub_folder ) {
								if ($language_sub_folder == '.' || $language_sub_folder == '..') continue;
								$handle = fopen("{$lang_dir}/{$language_folder}/{$language_file}/{$language_sub_folder}", "r");
								if ($handle) {
									while (($line = fgets($handle)) !== false) {
										// process the line read.
										if (false !== strpos ($line, "define(")) {
											$string = ltrim($line);
											$string = ltrim($string,"define(");
											$string = rtrim($string);
											$string = rtrim($string,");");
											$string = explode(",", $string);
											$this->translate[$string[0]][$language_folder] = $string[1];
										}
									}
									fclose($handle);
								}
							}
						}else{
							$handle = fopen("{$lang_dir}/{$language_folder}/{$language_file}", "r");
							if ($handle) {
								while (($line = fgets($handle)) !== false) {
									// process the line read.
									if (false !== strpos ($line, "define(")) {
										$string = ltrim($line);
										$string = ltrim($string,"define(");
										$string = rtrim($string);
										$string = rtrim($string,");");
										$string = explode(",", $string);
										$this->translate[$string[0]][$language_folder] = $string[1];
									}
								}
								fclose($handle);
							}
						}
					}
				}
			}
			foreach(array('dashboards','methods') as $type) {
				$method_dir = DIR_FS_MODULES ."{$dir}/{$type}";
				if (is_dir ( $method_dir )) {
					$methods = @scandir ( $method_dir );
					foreach ( $methods as $method ) {
						if ($method == '.' || $method == '..') continue;
						$language_folders = @scandir ( "{$method_dir}/{$method}/language" );
						foreach ( $language_folders as $language_folder ) {
							if ($language_folder == '.' || $language_folder == '..') continue;
							$language_files = @scandir ( "{$method_dir}/{$method}/language/{$language_folder}" );
							foreach ( $language_files as $language_file ) {
								if ($language_file == '.' || $language_file == '..') continue;
								$handle = fopen("{$method_dir}/{$method}/language/{$language_folder}/{$language_file}", "r");
								if ($handle) {
									while (($line = fgets($handle)) !== false) {
										// process the line read.
										if (false !== strpos ($line, "define(")) {
											$string = ltrim($line);
											$string = ltrim($string,"define(");
											$string = rtrim($string);
											$string = rtrim($string,");");
											$string = explode(",", $string);
											$this->translate[$string[0]][$language_folder] = $string[1];
										}
									}
									fclose($handle);
								}
							}
						}
					}
				}
			}
		}
		//controleren
		foreach ($this->translate as $key => $string){
			if(!isset($string['en_us'])){
				unset($this->translate[$key]);
			}else{
				foreach($string as $language => $translation){
					if ($language == 'en_us') continue;
					if ($string['en_us'] == $translation) unset($this->translate[$key][$language]);
				}
			}
		}

		ksort($this->translate);
		//store in xml.
		$doc = new \DOMDocument('1.0', 'utf-8');
		$doc->formatOutput = true;
		$root_element = $doc->createElement('translations');
		$root = $doc->appendChild($root_element);
		foreach($this->translate as $key => $value) {
			$first_element = $doc->createElement('translation');
			$string = ltrim($key);
			$string = ltrim($string,"'");
			$string = ltrim($string,'"');
			$string = rtrim($string);
			$string = rtrim($string, "'");
			$string = rtrim($string, '"');
			$first_element->setAttribute('id', $string);
			$second = $root->appendChild($first_element);
			foreach($value as $language => $translation) {
				$string = ltrim($translation);
				$string = ltrim($string,"'");
				$string = ltrim($string,'"');
				$string = rtrim($string);
				$string = rtrim($string, "'");
				$string = rtrim($string, '"');
				$temp = $doc->createElement($language, $string);
				$second->appendChild($temp);
			}
		}
		$custom_path = DIR_FS_INCLUDES."language/custom/translations.xml";
		$doc->save($custom_path);
	}

	static function add_constant($constant){
		$lang_path = (DIR_FS_INCLUDES."language/translations.xml");
		if (!file_exists($lang_path)) throw new \core\classes\userException("can't find your language file {$lang_path} ");
		$doc = new \DOMDocument('1.0', 'utf-8');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->load($lang_path);
		if ($doc->getElementById($constant) != null) break;
		$root = $doc->documentElement;
		$first_element = $doc->createElement('translation');
		$first_element->setAttribute('id', $constant);
		$second = $root->appendChild($first_element);
		$string = strtolower(str_replace(array('TEXT_', '_', 'ARGS'), array('', ' ', '%'),$constant));
		$temp = $doc->createElement('en_us', $string);
		$second->appendChild($temp);
		$doc->normalizeDocument();
		$doc->save($lang_path);
		define($constant, $string);
	}

	function __destruct(){
		$_SESSION['language'] = $this->language_code;
	}
}
?>
