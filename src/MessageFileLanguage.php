<?php
/*
 * Copyright (c) 2012-2015 Marc TEYSSIER
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\MoufException;
/**
 * The FineMessageLanguage class represents a PHP resource file that can be loaded / saved / modified.
 * There are many files for on language. Files are write with the start information of the key. Function used the separator ., - or _. 
 */
class MessageFileLanguage {

	/**
	 * The path to the folder to be loaded
	 * @var string
	 */
	private $folder;

	/**
	 * The array of messages in the folder loaded.
	 * @var array<string, string>
	 */
	private $msg = [];

	/**
	 * Language load
	 * @var string
	 */
	private $language = null;
	
	/**
	 * Loads all message for a language
	 * @var $folder The path to the folder to be loaded
	 * @var $language Language of messages loaded
	 */
	public function __construct($folder, $language) {
		$this->folder = $folder;
		$this->language = $language;

		if(file_exists($folder."messages_".$language.".php")) {
			$this->msg = @include($folder."messages_".$language.".php");
		}
	}

	/**
	 * Saves the file for current language
	 */
	public function save() {
//TODO si pas de message supprimer le fichier !
		ksort($this->msg);
		
		$file = $this->folder."messages_".$this->language.".php";
		
		$old = umask(00002);
		$fp = fopen($file, "w");
		fwrite($fp, "<?php\n");
		fwrite($fp, 'return ');
		fwrite($fp, var_export($this->msg, true));
		fwrite($fp, ";\n");
		fwrite($fp, "?>\n");
		
		fclose($fp);
		umask($old);
	}

	/**
	 * Delete file of language (Delete language too)
	 * @param $language string Language to delete
	 */
	private function deleteFile() {
		unlink($this->folder."messages_".$this->language.".php");
	}
	
	/**
	 * Set message for a key
	 * @param $key string Message key
	 * @param $message string Message
	 */
	public function setMessage($key, $message) {
		$this->msg[$key] = $message;
	}

	/**
	 * Sets messages
	 * 
	 * @param $translations array<string, string> To set many messages in one time. The array is key message.
	 */
	public function setMessages(array $translations) {
		foreach ($translations as $key => $message) {
			if($message) {
				$this->msg[$key] = $message;
			}
		}
	}
	
	/**
	 * Returns a message for the key $key.
	 * @var $key string Key of translation search.
	 */
	public function getMessage($key) {
		if (isset($this->msg[$key])) {
			return $this->msg[$key];
		} else {
			return null;
		}
	}

	/**
	 * Sets the message
	 * @var $key Remove a message for key
	 */
	public function deleteMessage($key) {
		if(isset($this->msg[$key])) {
			unset($this->msg[$key]);
		}
	}
	
	/**
	 * Returns all messages for this file.
	 * @return array<string, string> Return the array of message (key, value)
	 */
	public function getAllMessages() {
		return $this->msg;
	}
}

?>
