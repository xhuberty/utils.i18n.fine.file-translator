<?php
/*
 * Copyright (c) 2012-2015 Marc TEYSSIER
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\Validator\MoufValidatorResult;

use Mouf\Validator\MoufValidatorInterface;

use Mouf\MoufManager;

use Mouf\Utils\I18n\Fine\FineMessageLanguage;

use Mouf\Utils\I18n\Fine\LanguageDetectionInterface;
use Mouf\Utils\I18n\Fine\Common\Ui\EditTranslationHelperTrait;
use Mouf\Utils\I18n\Fine\LanguageTranslationInterface;

/**
 * Used to save all translation in a file.
 * 
 * @author Marc TEYSSIER
 */
class FileTranslator implements LanguageTranslationInterface, MoufValidatorInterface  {
	use EditTranslationHelperTrait {EditTranslationHelperTrait::setTranslations as unused;}
	
	/**
	 * Message list
	 * 
	 * @var array
	 */
	private $msg = null;
	
	/**
	 * The path to the directory storing the translations.
	 * <p>The directory path should end with a "/".</p>
	 * <p>If the path start with / or c:/ is the real path of file, otherwise, this must be start without / to root path of application.</p>
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
	private $i18nMessagePath = "resources/";

	/**
	 * Set the language detection
	 *
	 * @Property
	 * @var LanguageDetectionInterface
	 */
	private $languageDetection;

	public function __construct($i18nMessagePath = "resources/", $languageDetection = null) {
		$this->i18nMessagePath = $i18nMessagePath;
		$this->languageDetection = $languageDetection;
	}
	
	/**
	 * This function return the real path set in parameter.
	 * @return string
	 */
	private function getPath() {
		if(strpos($this->i18nMessagePath, '/') === 0 || strpos($this->i18nMessagePath, ':/') === 1) {
			return $this->i18nMessagePath;;
		}
		return ROOT_PATH.$this->i18nMessagePath;
	}
	
	/**
	 * Retrieve the translation of code or message.
	 * Check in the $msg variable if the key exist to return the value. This function check all the custom file if the translation is not in the main message_[language].php
	 * If this message doesn't exist, it return a link to edit it.
	 * 
	 */
	public function getTranslation($message, array $parameters = [], LanguageDetectionInterface $languageDetectionInterface = null) {
		echo $message;
		if(!$languageDetectionInterface) {
			var_dump($this->languageDetection->getLanguage());
			$lang = $this->languageDetection->getLanguage();
		}
		else {
			$lang = $languageDetectionInterface->getLanguage();
		}
		//Load the main file
		if($this->msg[$lang] === null) {
			$this->retrieveMessages($lang);
		}
		if (isset($this->msg[$lang][$message])) {
			// build a replacement array with braces around the context keys
			$replace = array();
			foreach ($parameters as $key => $val) {
				$replace['{' . $key . '}'] = $val;
			}
			
			// interpolate replacement values into the message and return
			return strtr($this->msg[$lang][$message], $replace);
		}
		
		return null;
	}
	
	/**
	 * Retrieve array variable store in the language file.
	 * This function include the message resource by default and the language file if the language code is set.
	 * The file contain an array with translation, we retrieve it in a private array msg. 
	 * 
	 * @param string $language Language code
	 * @return boolean
	 */
	private function retrieveMessages($language) {
		$this->msg = array();
		/*
		if($this->defaultLanguage) {
			if (file_exists($this->getPath().'messages_'.$this->defaultLanguage.'.php')){
				$this->msg = require_once $this->getPath().'messages_'.$this->defaultLanguage.'.php';
			}
		}
		*/
		if($language) {
			error_log('ici '.__LINE__);
			if (file_exists($this->getPath().'messages_'.$language.'.php')){
				error_log('exist '.__LINE__);
				//$this->msg = array_merge(require_once $this->getPath().'message_'.$language.'.php', $this->msg);
				$this->msg[$language] = require_once $this->getPath().'messages_'.$language.'.php';
				error_log(var_export($this->msg, true));
			}
		}
	}
	
	
	private function loadAllMessages() {
		error_log('ici '.__LINE__);
		$files = glob($this->getPath().'messages_*.php');
		error_log('test');
		foreach ($files as $file) {
			$base = basename($file);
			$phpPos = strpos($base, '.php');
			$language = substr($base, 9, $phpPos-9);
			error_log('-'.$language.'-');
			$this->retrieveMessages($language);
		}
		error_log('ici '.__LINE__);
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
		$messageLanguage->loadForLanguage($this->getPath(), $language);
		
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
		$files = glob($this->getPath().'messages*.php');
		
		$languages = array();
		//$defaultFound = false;
		foreach ($files as $file) {
			$base = basename($file);
			$languages[] = substr($base, 9, 9 - strrpos($base, '.php'));
		}
		return $languages;
	}
}