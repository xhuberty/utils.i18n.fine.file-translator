<?php
/*
 * Copyright (c) 2012 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Utils\I18n\Fine\Translate;

use Mouf\Validator\MoufValidatorResult;

use Mouf\Validator\MoufValidatorInterface;

use Mouf\MoufManager;

use Mouf\Utils\I18n\Fine\FineMessageLanguage;

use Mouf\Utils\I18n\Fine\Language\LanguageDetectionInterface;
use Mouf\Utils\I18n\TranslationInterface;
use Mouf\Utils\I18n\EditTranslationHelperTrait;

/**
 * Used to save all translation in a php array.
 * 
 * @Component
 * @author Marc Teyssier
 */
class FineTranslator implements TranslationInterface, MoufValidatorInterface  {
	use EditTranslationHelperTrait {EditTranslationHelperTrait::setTranslations as toto;}
	
	/**
	 * Detection language object
	 * 
	 * @var LanguageDetectionInterface
	 */
	private $msg = null;
	
	/**
	 * The path to the directory storing the translations.
	 * <p>The directory path should end with a "/".</p>
	 * <p>Each file in this directory is a PHP file containing an array variable named $msg. The key is the code or message id, the value is translation.<br/>
	 * Example :
	 * </p>
	 * <pre class="brush:php">$msg["home.title"] = "Hello world";<br />
	 * $msg["home.text"] = "News 1, news 2 and news 3";</pre>
	 * 
	 * 
	 * @Property
	 * @Compulsory
	 * @var string
	 */
	public $i18nMessagePath = "resources/";

	/**
	 * Set the language.
	 *
	 * @Property
	 * @var LanguageDetectionInterface
	 */
	public $language;
	
	/**
	 * Set the language by default. It must be exist on activate language. 
	 * 
	 * @Property
	 * @var string
	 */
	public $defaultLanguage;
	
	/**
	 * Runs the validation of the instance.
	 * Returns a MoufValidatorResult explaining the result.
	 *
	 * @return MoufValidatorResult
	 */
	public function validateInstance() {
		$instanceName = MoufManager::getMoufManager()->findInstanceName($this);
			
		if($this->defaultLanguage) {
			if (!file_exists(ROOT_PATH.$this->i18nMessagePath."messages_".$this->defaultLanguage.".php")) {
				return new MoufValidatorResult(MoufValidatorResult::ERROR, "<b>Fine: </b>Unable to find default translation file for instance: <code>".ROOT_PATH.$this->i18nMessagePath."messages_".$this->defaultLanguage.".php</code>.<br/>"
						."You should create the following files:<br/>"
						.$this->i18nMessagePath."message.php <a href='".ROOT_URL."vendor/mouf/mouf/editLabels/createMessageFile?name=".$instanceName."&selfedit=false&language=".$this->defaultLanguage."'>(create this file)</a>");
			}
		}
		else {
			$this->loadAllMessages();
			
			// The array of messages by message, then by language:
			// array(message_key => array(language => message))
			$keys = $this->getAllKeys();
			if($instanceName == 'defaultTranslationService')
			foreach ($keys as $key) {
				$msgs = $this->getMessageForAllLanguages($key);
				if($instanceName == 'defaultTranslationService')
				if (!isset($msgs['default'])) {
					$missingDefaultKeys[$instanceName][] = $key;
				}
			}
			if (empty($missingDefaultKeys)) {
				return new MoufValidatorResult(MoufValidatorResult::SUCCESS, "<b>Fine: </b>Default translation file found in instance <code>$instanceName</code>.<br />
																				Default translation is available for all messages.");
			} else {
				$html = "";
				foreach ($missingDefaultKeys as $instanceName=>$missingKeys) {
					$html .= "<b>Fine: </b>A default translation in '".$instanceName."' is missing for these messages: ";
					foreach ($missingKeys as $missingDefaultKey) {
						$html .= "<a href='".ROOT_URL."vendor/mouf/mouf/editLabels/editLabel?key=".urlencode($missingDefaultKey)."&language=default&backto=".urlencode(ROOT_URL)."mouf/&msginstancename=".urlencode($instanceName)."'>".$missingDefaultKey."</a> ";
					}
					$html .= "<hr/>";
				}
				return new MoufValidatorResult(MoufValidatorResult::WARN, $html);
			}
			
		}
	}
	
	/**
	 * Retrieve the translation of code or message.
	 * Check in the $msg variable if the key exist to return the value. This function check all the custom file if the translation is not in the main message_[language].php
	 * If this message doesn't exist, it return a link to edit it.
	 * 
	 */
	public function getTranslation($message, array $parameters = array()) {
		
		//Load the main file
		if($this->msg === null)
			$this->retrieveMessages($this->language->getLanguage());
			
		if (isset($this->msg[$message])) {
			// build a replacement array with braces around the context keys
			$replace = array();
			foreach ($parameters as $key => $val) {
				$replace['{' . $key . '}'] = $val;
			}
			
			// interpolate replacement values into the message and return
			return strtr($this->msg[$message], $replace);
		}
		
		return null;
	}
	
	/**
	 * Returns true if a translation is available for the $message key, false otherwise.
	 *
	 * @param string $message Key of the message
	 * @return bool
	 */
	public function hasTranslation($message) {
		
		//Load the main file
		if($this->msg === null) {
			$this->retrieveMessages($this->language->getLanguage());
		}
		
		if (isset($this->msg[$message])) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Retrieve array variable store in the language file.
	 * This function include the message resource by default and the language file if the language code is set.
	 * The file contain an array with translation, we retrieve it in a private array msg. 
	 * 
	 * @param string $language Language code
	 * @return boolean
	 */
	private function retrieveMessages($language = null) {
		$this->msg = array();
		if($this->defaultLanguage) {
			if (file_exists(ROOT_PATH.$this->i18nMessagePath.'messages_'.$this->defaultLanguage.'.php')){
				$this->msg = require_once ROOT_PATH.$this->i18nMessagePath.'messages_'.$this->defaultLanguage.'.php';
			}
		}
		if($language) {
			if (file_exists(ROOT_PATH.$this->i18nMessagePath.'message_'.$language.'.php')){
				$this->msg = array_merge(require_once ROOT_PATH.$this->i18nMessagePath.'message_'.$language.'.php', $this->msg);
			}
		}
	}

	/***************************/
	/****** Edition mode *******/
	/***************************/

	/**
	 * The list of all messages in all languages
	 * @var array<string, FineMessageLanguage>
	 */
	private $messages = array();
	
	/**
	 * Return a list of all message for a language.
	 * 
	 * @param string $language Language
	 * @return array<string, string> List with key value of translation
	 */
	public function getTranslationForLanguage($language) {
		if (isset($this->messages[$language])) {
			return $this->messages[$language];
		}
		
		$messageLanguage = new FineMessageLanguage();
		$messageLanguage->loadForLanguage(ROOT_PATH.$this->i18nMessagePath, $language);
		
		$this->messages[$language] = $messageLanguage;
		return $messageLanguage;
	}
	
	/**
	 * Delete a translation for a language. If the language is not set or null, this function deletes the translation for all language.
	 * 
	 * @param string $key Key to remove
	 * @param string|null $language Language to remove key or null for all
	 */
	public function deleteTranslation($key, $language = null) {
		if($language === null) {
			$languages = $this->getLanguageList();
		}
		else {
			$languages = array($language);
		}
		foreach ($languages as $language) {
			$messageFile = $this->getTranslationForLanguage($language);
			$messageFile->deleteMessage($key);
			$messageFile->save();
			
			unset($this->messages[$language][$key]);
		}
	}
	
	/**
	 * Add or change a translation
	 * 
	 * @param string $key Key of translation
	 * @param string $value Message of translation
	 * @param string $language Language to add translation
	 */
	public function setTranslation($key, $value, $language) {
		$messageFile = $this->getTranslationForLanguage($language);
		$messageFile->setMessage($key, $value);
		$messageFile->save();

		$this->messages[$language][$key] = $value;
	}
	
	/**
	 * Add or change many translations in one time
	 * 
	 * @param array<string, string> $messages List with key value of translation 
	 * @param string $language Language to add translation
	 */
	public function setTranslations(array $messages, $language) {
		$messageFile = $this->getMessageLanguageForLanguage($language);
		$messageFile->setMessages($messages);
		$messageFile->save();

		$this->messages[$language] = array_merge($this->messages[$language], $messages);
	}
	
	/**
	 * Liste of all language supported
	 * 
	 * @return array<string>
	 */
	public function getLanguageList() {
		$files = glob(ROOT_PATH.$this->i18nMessagePath.'messages*.php');
		
		$languages = array();
		//$defaultFound = false;
		foreach ($files as $file) {
			$base = basename($file);
			$languages[] = substr($base, 9, 9 - strrpos($base, '.php'));
		}
		return $languages;
	}
}